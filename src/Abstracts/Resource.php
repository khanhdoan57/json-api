<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Resource abstract class
*/

namespace HackerBoy\JsonApi\Abstracts;

use HackerBoy\JsonApi\Elements\Relationships;
use HackerBoy\JsonApi\Elements\Relationship;
use HackerBoy\JsonApi\Elements\Meta;
use HackerBoy\JsonApi\Traits\AbstractDataConvert;
use Illuminate\Support\Str;

abstract class Resource implements \JsonSerializable, \ArrayAccess {
    
    use AbstractDataConvert;

    /**
    * Resource constructor
    *
    * @param object
    */
    public function __construct($model, Document $document)
    {
        $this->model = $model;
        $this->document = $document;
    }

    /**
    * Model object
    *
    * @var object
    * @access protected
    */
    protected $model;

    /**
    * Document object
    *
    * @var object
    * @access protected
    */
    protected $document;

    /**
    * Resource type
    *
    * @var string
    */
    protected $type;

    /**
    * Get document
    *
    * @param void
    * @return \HackerBoy\JsonApi\Document
    */
    public function getDocument()
    {
        return $this->document;
    }

    /**
    * Get resource ID
    *
    * @param void
    * @return string|integer Resource ID
    */
    abstract public function getId();

    /**
    * Get resource type
    */
    final public function getType()
    {
        return $this->type;
    }

    /**
    * Get attribute value
    *
    * @param string Attribute name
    * @param mixed Default value - Default null
    */
    final public function getAttribute($name, $default = null)
    {
        $attributes = $this->getAttributes();
        return array_key_exists($name, $attributes) ? $attributes[$name] : $default;
    }

    /**
    * Get resource model object
    */
    final public function getModelObject()
    {
        return $this->model;
    }

    /**
    * Map resource attributes
    *
    * @param void
    * @return array
    */
    abstract public function getAttributes();

    /**
    * Define resource relationships
    *
    * @param void
    * @return array
    */
    public function getRelationships()
    {
        return [];
    }

    /**
    * Define resource links
    *
    * @param void
    * @return array
    */
    public function getLinks()
    {
        if (!$this->getDocument()->getConfig('auto_set_links')) {
            return [];
        }

        return [
            'self' => $this->getDocument()->getUrl($this->getType().'/'.$this->getId())
        ];
    }

    /**
    * Define resource meta data
    *
    * @param void
    * @return this
    */
    public function getMeta()
    {
        return [];        
    }

    /**
    * Get relationship data using $document->getQuery()
    *
    * @param string Relationship name
    * @return mixed Resource or a collection of Resources
    */
    public function getRelationshipData($relationshipName)
    {
        $relationships = $this->getAbstractRelationships();

        if (!$relationships) {
            return null;
        }

        $relationships = $relationships->getData();

        if (!isset($relationships[$relationshipName])) {
            return null;
        }

        $relationshipData = $relationships[$relationshipName]['data'];

        if ($relationshipData instanceof Relationship) {

            return $this->getDocument()
                        ->getQuery()
                        ->where('type', $relationshipData->getData()->getType())
                        ->where('id', $relationshipData->getData()->getId())
                        ->first();

        } elseif (is_iterable($relationshipData)) {

            $type = '';
            $ids = [];

            foreach ($relationshipData as $relationship) {
                $type = $relationship->getData()->getType();
                $ids[] = $relationship->getData()->getId();
            }

            return $this->getDocument()
                        ->getQuery()
                        ->where('type', $type)
                        ->whereIn('id', $ids);

        }

        return null;
    }

    /**
    * Convert meta data to abstract object
    *
    * @param void
    * @return object|null
    */
    final protected function getAbstractMeta()
    {
        $meta = $this->getMeta();

        if (!$meta) {
            return null;
        }

        return ($meta instanceof Meta) ? $meta : $this->getDocument()->makeMeta($meta);
    }

    /**
    * Convert relationships data to relationships abstract object
    *
    * @access protected
    * @param void
    * @return object|null
    */
    final protected function getAbstractRelationships()
    {
        if ($relationships = $this->getRelationships()) {

            $relationshipsArray = is_array($relationships) ? $relationships : $relationships->toArray();
            if (array_key_exists('id', $relationshipsArray) or array_key_exists('type', $relationshipsArray)) {
                throw new \Exception('JSON-API resources cannot have an attribute or relationship named type or id. Check https://jsonapi.org/format/#document-resource-object-fields');
            }

            if (is_object($relationships) and $relationships instanceof Relationships) {
                return $relationships;
            } 

            if (is_array($relationships) and count($relationships)) {
                return new Relationships($relationships, $this->getDocument(), $this);
            }
            
        }
        
        return null;
    }

    /**
    * Resource structure to be encoded with json_encode
    *
    * @access public
    * @param void
    * @return array
    */
    final public function jsonSerialize()
    {
        $attributes = $this->getAttributes();

        if (array_key_exists('id', $attributes) or array_key_exists('type', $attributes)) {
            throw new \Exception('JSON-API resources cannot have an attribute or relationship named type or id. Check https://jsonapi.org/format/#document-resource-object-fields');
        }

        $resource = [
            'type' => (string) $this->type,
        ];

        if ($attributes and count($attributes)) {
            $resource['attributes'] = $attributes;
        }

        if ($id = (string) $this->getId()) {
            $resource['id'] = $id;
        }

        if ($relationships = $this->getAbstractRelationships()) {
            $resource['relationships'] = $relationships;
        }

        if ($links = $this->getLinks()) {
            $resource['links'] = $links;
        }

        if ($meta = $this->getAbstractMeta()) {
            $resource['meta'] = $meta;
        }

        return $resource;
    }

    /**
    * {@inheritdoc}
    */
    public function offsetExists($offset)
    {
        return in_array($offset, ['id', 'type', 'attributes', 'meta', 'links', 'relationships']);
    }

    /**
    * {@inheritdoc}
    */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        if (method_exists($this, 'get'.Str::camel($offset))) {
            return $this->{'get'.Str::camel($offset)}();
        }

        return null;
    }

    /**
    * {@inheritdoc}
    */
    public function offsetUnset($offset)
    {
        return;
    }

    /**
    * {@inheritdoc}
    */
    public function offsetSet($offset, $value)
    {
        if (!$this->offsetExists($offset)) {
            return;
        }

        if (in_array($offset, ['id', 'type', 'attributes', 'meta', 'links', 'relationships']) and method_exists($this, 'set'.Str::camel($offset))) {
            $this->{'set'.Str::camel($offset)}($value);
        }

        return;
    }
}