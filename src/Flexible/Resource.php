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
        $this->resource = $this;
    }

    /**
    * Get resource ID
    *
    * @param object Resource object
    * @return string|integer Resource ID
    */
    public function getId($resource)
    {
        return $resource->id;
    }

    /**
    * Map resource attributes
    *
    * @param object Resource object
    * @return array
    */
    public function getAttributes($resource)
    {
        return $resource->attributes ? $resource->attributes : [];
    }

    /**
    * Define resource relationships
    *
    * @param object
    * @return array
    */
    public function getRelationships($resource)
    {
        return $resource->relationships ? $resource->relationships : [];
    }

    /**
    * Define resource links
    *
    * @param object
    * @return array
    */
    public function getLinks($resource)
    {
        return $resource->links ? $resource->links : [];
    }

    /**
    * Define resource meta data
    *
    * @param array
    * @return this
    */
    public function getMeta($resource)
    {
        return $resource->meta ? $resource->meta : [];
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
    * Set relationships
    *
    * @param array|object
    * @return this
    */
    public function setRelationships($relationships)
    {
        $this->relationships = ($relationships instanceof Element\Relationships) ? $relationships : $this->document->makeRelationships($relationships);
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