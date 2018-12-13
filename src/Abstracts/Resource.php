<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Resource abstract class
*/

namespace HackerBoy\JsonApi\Abstracts;

use HackerBoy\JsonApi\Elements\Relationships;
use HackerBoy\JsonApi\Elements\Meta;
use HackerBoy\JsonApi\Traits\AbstractDataConvert;

abstract class Resource implements \JsonSerializable {
    
    use AbstractDataConvert;

    /**
    * Resource constructor
    *
    * @param object
    */
    public function __construct($resource, Document $document)
    {
        $this->resource = $resource;
        $this->document = $document;
    }

    /**
    * Model object
    *
    * @var object
    * @access protected
    */
    protected $resource;

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
    * Get resource ID
    *
    * @param object Resource object
    * @return string|integer Resource ID
    */
    abstract public function getId($resource);

    /**
    * Get resource type
    */
    final public function getType()
    {
        return $this->type;
    }

    /**
    * Get resource model object
    */
    final public function getResourceObject()
    {
        return $this->resource;
    }

    /**
    * Map resource attributes
    *
    * @param object Resource object
    * @return array
    */
    abstract public function getAttributes($resource);

    /**
    * Define resource relationships
    *
    * @param object
    * @return array
    */
    public function getRelationships($resource)
    {
        return [];
    }

    /**
    * Define resource links
    *
    * @param object
    * @return array
    */
    public function getLinks($resource)
    {
        if (!$this->document->getConfig('auto_set_links')) {
            return [];
        }

        return [
            'self' => $this->document->getUrl($this->getType().'/'.$this->getId($resource))
        ];
    }

    /**
    * Define resource meta data
    *
    * @param array
    * @return this
    */
    public function getMeta($resource)
    {
        return [];        
    }

    /**
    * Convert meta data to abstract object
    *
    * @param void
    * @return object|null
    */
    final protected function getAbstractMeta()
    {
        $meta = $this->getMeta($this->resource);

        if (!$meta) {
            return null;
        }

        return ($meta instanceof Meta) ? $meta : $this->document->makeMeta($meta);
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
        if ($relationships = $this->getRelationships($this->resource)) {

            if (array_key_exists('id', $relationships) or array_key_exists('type', $relationships)) {
                throw new \Exception('JSON-API resources cannot have an attribute or relationship named type or id. Check https://jsonapi.org/format/#document-resource-object-fields');
            }

            if ($relationships instanceof Relationships) {
                return $relationships;
            } 

            if (is_array($relationships) and count($relationships)) {
                return new Relationships($relationships, $this->document, $this);
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
        $attributes = $this->getAttributes($this->resource);

        if (array_key_exists('id', $attributes) or array_key_exists('type', $attributes)) {
            throw new \Exception('JSON-API resources cannot have an attribute or relationship named type or id. Check https://jsonapi.org/format/#document-resource-object-fields');
        }

        $resource = [
            'type' => $this->type,
            'id' => $this->getId($this->resource),
            'attributes' => $attributes
        ];

        if ($relationships = $this->getAbstractRelationships()) {
            $resource['relationships'] = $relationships;
        }

        if ($links = $this->getLinks($this->resource)) {
            $resource['links'] = $links;
        }

        if ($meta = $this->getAbstractMeta()) {
            $resource['meta'] = $meta;
        }

        return $resource;
    }

}