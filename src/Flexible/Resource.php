<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Flexible Resource object
*/

namespace HackerBoy\JsonApi\Flexible;

use Exception;
use HackerBoy\JsonApi\Abstracts;
use HackerBoy\JsonApi\Elements as Element;

class Resource extends Abstracts\Resource {

    protected $document;

    protected $type;
    protected $id;
    protected $attributes = [];
    protected $relationships;
    protected $links;
    protected $meta;

    /**
    * Constructor
    */
    public function __construct(Abstracts\Document $document)
    {
        $this->document = $document;
        $this->model = $this;
    }

    /**
    * Get resource ID
    *
    * @param void
    * @return string|integer Resource ID
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Map resource attributes
    *
    * @param void
    * @return array
    */
    public function getAttributes()
    {
        return $this->attributes ?: [];
    }

    /**
    * Define resource relationships
    *
    * @param object
    * @return array
    */
    public function getRelationships()
    {
        return $this->relationships ?: [];
    }

    /**
    * Define resource links
    *
    * @param void
    * @return array
    */
    public function getLinks()
    {
        return $this->links ?: [];
    }

    /**
    * Define resource meta data
    *
    * @param void
    * @return this
    */
    public function getMeta()
    {
        return $this->meta ?: [];
    }

    /**
    * Set id
    *
    * @param string|integer
    * @return this
    */
    public function setId($id)
    {
        if (!is_string($id) and !is_int($id)) {
            throw new Exception('Resource ID must be a string or integer');
        }

        $this->id = $id;
        return $this;
    }

    /**
    * Set type
    *
    * @param string
    * @return this
    */
    public function setType($type)
    {
        if (!is_string($type)) {
            throw new Exception('Resource type must be a string');
        }

        $this->type = $type;
        return $this;
    }

    /**
    * Set attributes
    *
    * @param array
    * @return this
    */
    public function setAttributes($data)
    {
        $this->attributes = $data;
        return $this;
    }

    /**
    * Set an attribute
    *
    * @param string Attribute name
    * @param mixed Attribute value
    * @return this
    */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
    * Set relationships
    *
    * @param array|object
    * @return this
    */
    public function setRelationships($relationships)
    {
        $this->relationships = ($relationships instanceof Element\Relationships) ? $relationships : (function() use ($relationships) {

            foreach ($relationships as $relationshipName => $relationshipData) {
                    
                // Formatting
                if (!is_array($relationshipData) or !isset($relationshipData['data'])) {
                    $_relationshipData = $relationshipData;
                    $relationshipData = [];
                    $relationshipData['data'] = $_relationshipData;
                    unset($_relationshipData);
                }

                // Skip if data is a valid resource or collection
                if(!in_array($this->document->checkResource($relationshipData['data']), [Document::INVALID_RESOURCE, Document::INVALID_COLLECTION])) {
                    continue;
                }

                // Data is raw array, make flexible resources then
                // To One relationship
                if (isset($relationshipData['data']['id'])) {

                    $resource = $this->document->makeFlexibleResource();
                    $resource->setType($relationshipData['data']['type']);
                    $resource->setId($relationshipData['data']['id']);
                    
                    $relationshipData['data'] = $resource;

                } else {

                    // To Many relationship
                    foreach ($relationshipData['data'] as $key => $relationship) {
                        
                        $resource = $this->document->makeFlexibleResource();
                        $resource->setType($relationship['type']);
                        $resource->setId($relationship['id']);

                        $relationshipData['data'][$key] = $resource;

                    }

                }

                $relationships[$relationshipName] = $relationshipData;

            }

            return $this->document->makeRelationships($relationships);

        })();

        return $this;

    }

    /**
    * Set links
    *
    * @param array|object
    * @return this
    */
    public function setLinks($links)
    {
        $this->links = ($links instanceof Element\Links) ? $links : $this->document->makeLinks($links);
        return $this;
    }

    /**
    * Set meta
    *
    * @param array|object
    * @return this
    */
    public function setMeta($meta)
    {
        $this->meta = ($meta instanceof Element\Meta) ? $meta : $this->document->makeMeta($meta);
        return $this;
    }

}