<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Document object
*/

namespace HackerBoy\JsonApi;

use Exception;
use HackerBoy\JsonApi\Abstracts\Resource;
use HackerBoy\JsonApi\Elements as Element;

class Document extends Abstracts\Document {

    /**
    * Document object constructor
    *
    * @param array A map of Model => Resource classes
    * @param object|array A model object or a collection
    */
    public function __construct($config)
    {
        if (!is_array($config)) {
            throw new Exception('Config must be an array');
        }

        if (!array_key_exists('resource_map', $config)) {
            throw new Exception('Missing resource_map in config');
        }

        $resourceMap = $config['resource_map'];

        // Check resource map
        if (!is_array($resourceMap) or !count($resourceMap)) {
            throw new Exception('Resource Map must be an array containing at least 1 element');
        }

        // Save resource map
        $this->resourceMap = $resourceMap;

        if ($apiUrl = @$config['api_url']) {

            $apiUrl = rtrim($apiUrl, '/');

            $this->url = $apiUrl;
        }

        $this->config = $config;
    }

    /**
    * Doument data
    *
    * @access protected
    * @var array
    */
    protected $document = [];

    /**
    * Add resource to data
    *
    * @param object|array
    * @param string Data type (resource|relationship) - default is resource
    * @return object this
    */
    public function setData($resource, $type = 'resource')
    {
        // Check errors is set
        if ($this->errors) {
            throw new Exception('The members data and errors MUST NOT coexist in the same document.');
        }

        $document = $this;

        $this->resourceHandler($resource, function($resource) use (&$document, $type) {

            if ($type === 'resource') {
                $document->data = $document->getResourceInstance($resource);
            } elseif ($type === 'relationship' || $type === 'relationships') {
                $document->data = $document->makeRelationship($resource);
            }

        }, function($collection) use (&$document, $type) {

            $document->data = [];

            $preventDuplicatedResources = [];

            foreach ($collection as $resource) {

                if ($type === 'resource') {
                    $_resource = $document->getResourceInstance($resource);
                } elseif ($type === 'relationship' || $type === 'relationships') {
                    $_resource = $document->makeRelationship($resource);
                }

                // If data had this resource
                $resourceKey = ($type === 'resource') ? $_resource->getType().'-'.$_resource->getId($resource) : $_resource->getData()->getType().'-'.$_resource->getData()->getId($_resource->getData()->getResourceObject());
                if (in_array($resourceKey, $preventDuplicatedResources)) {
                    continue;
                }

                $preventDuplicatedResources[] = $resourceKey;

                $document->data[] = $_resource;

            }

        });

        return $this;
    }

    /**
    * @inheritdoc
    */
    public function setErrors($errors, $override = true)
    {
        // If data is set - errors cant be set
        if ($this->data) {
            throw new Exception('The members data and errors MUST NOT coexist in the same document.');
        }

        if ($override) {
            $this->errors = [];
        }

        // If errors is a single error
        if ($errors instanceof Element\Error) {
            $this->errors[] = $errors;
        } else {

            if (!is_iterable($errors)) {
                throw new Exception('Invalid errors data');
            }

            foreach ($errors as $key => $error) {
                
                // Single error data
                if (!is_integer($key)) {
                    
                    $_error = $this->makeError($errors);

                    if (!in_array($_error, $this->errors)) {
                        $this->errors[] = $_error;
                    }

                    break;

                } elseif ($error instanceof Element\Error) {

                    // Error elements are object
                    if (!in_array($_error, $this->errors)) {
                        $this->errors[] = $error;
                    }

                } else {

                    // Error elements are data
                    $_error = $this->makeError($error);

                    if (!in_array($_error, $this->errors)) {
                        $this->errors[] = $_error;
                    }

                }

            }
        }

        return $this;
    }

    /**
    * @inheritdoc
    */
    public function setMeta($meta, $override = true)
    {
        if ($override or !$this->meta) {
            $this->meta = ($meta instanceof Element\Meta) ? $meta : $this->makeMeta($meta);
        } else {

            if (!($meta instanceof Element\Meta)) {
                $meta = $this->makeMeta($meta);
            }

            $this->meta = $this->makeMeta(array_merge($this->meta->toArray(), $meta->toArray()));

        }
        
        return $this;
    }

    /**
    * @inheritdoc
    */
    public function setLinks($links, $override = true)
    {   
        if (!$override or !$this->links) {
            $this->links = ($links instanceof Element\Links) ? $links : $this->makeLinks($links);
        } else {

            if (!($links instanceof Element\Links)) {
                $links = $this->makeLinks($links);
            }

            $this->links = $this->makeLinks(array_merge($this->links->toArray(), $links->toArray()));
        }
        
        return $this;
    }

    /**
    * @inheritdoc
    */
    public function setIncluded($collection, $override = true)
    {
        // If null or empty array
        if (!$collection or (is_iterable($collection) and !count($collection))) {
            return $this;
        }

        if (!$this->data) {
            throw new Exception('Document data is not set yet - included data must not be set');
        }

        if (!$this->included) {
            $this->included = [];
        }

        // Check valid included
        $abstractCollection = [];
        if ($this->checkResource($collection) === self::IS_RESOURCE) {

            $abstractCollection[] = $this->getResourceInstance($collection);

        } elseif (is_iterable($collection) and in_array($this->checkResource($collection, true), [self::IS_COLLECTION, self::IS_FLEXIBLE_RESOURCE_COLLECTION])) {
            
            foreach ($collection as $resource) {
                $abstractCollection[] = $this->getResourceInstance($resource);
            }

        } else {
            throw new Exception('Included data must be a valid collection');
        }

        if ($override) {
            $this->included = $abstractCollection;
        } else {
            $this->included = array_merge($this->included, $abstractCollection);
        }

        // Check duplicated objects
        $continue = true;

        while ($continue) {

            $found = false;

            foreach ($this->included as $key => $resource) {

                foreach ($this->included as $_key => $_resource) {
                    
                    // Skip itself
                    if ($key === $_key) {
                        continue;
                    }

                    // Same object - check by id and type
                    if ($resource->getType() === $_resource->getType()
                        and $resource->getId($resource->getResourceObject()) === $_resource->getId($_resource->getResourceObject())
                    ) {
                        $found = true;
                        unset($this->included[$_key]);
                    }

                }

                if ($found) {
                    break;
                }

            }

            $continue = $found ? true : false;

        }

        // Re-sort included data
        $this->included = array_values($this->included);

        return $this;
    }

    /**
    * @inheritdoc
    */
    public function jsonSerialize()
    {
        $document =& $this->document;

        // If document has resource or collection
        if ($this->data) {
            
            $document['data'] = $this->data;

            // If has data, then it may has included
            if ($this->included) {
                $document['included'] = $this->included;
            }

        }

        // If document has errors
        if ($this->errors) {
            $document['errors'] = $this->errors;
        }

        // If document has meta
        if ($this->meta) {
            $document['meta'] = $this->meta;
        }

        // If document has links
        if ($this->links) {
            $document['links'] = $this->links;
        }

        // Add api info
        if (@$this->config['show_api_version']) {
            $document['jsonapi'] = [
                'version' => self::VERSION
            ];
        }
        
        return $document;

    }
}