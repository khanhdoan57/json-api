<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Document object
*/

namespace HackerBoy\JsonApi\Abstracts;

use HackerBoy\JsonApi\Elements\Relationships;

abstract class Resource implements \JsonSerializable {
    
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
    * Pre-define resource relationships
    *
    * @param void
    * @return array
    */
    public function getRelationships($resource)
    {
        return [];
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
        if ($relationships = $this->getRelationships($this->resource) and is_array($relationships) and count($relationships)) {
            return new Relationships($relationships, $this->document);
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
    public function jsonSerialize()
    {
        $resource = [
            'type' => $this->type,
            'id' => $this->getId($this->resource),
            'attributes' => $this->getAttributes($this->resource)  
        ];

        if ($relationships = $this->getAbstractRelationships()) {
            $resource['relationships'] = $relationships;
        }

        return $resource;
    }

}