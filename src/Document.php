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

            // Check valid URL
            if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid API URL');
            }

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
            } elseif ($type === 'relationship') {
                $document->data = $document->makeRelationship($resource);
            }

        }, function($collection) use (&$document, $type) {

            $document->data = [];

            foreach ($collection as $resource) {

                if ($type === 'resource') {
                    $_resource = $document->getResourceInstance($resource);
                } elseif ($type === 'relationship') {
                    $_resource = $document->makeRelationship($resource);
                }

                $document->data[] = $_resource;

            }

        });

        return $this;
    }

    /**
    * Add errors to document
    *
    * @param array|iterator|object Can be an instance or a collection of Element\Error, or simply an array of data
    * @return object this
    */
    public function setErrors($errors)
    {
        // If data is set - errors cant be set
        if ($this->data) {
            throw new Exception('The members data and errors MUST NOT coexist in the same document.');
        }

        $this->errors = [];

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
                    
                    $this->errors[] = $this->makeError($errors);
                    break;

                } elseif ($error instanceof Element\Error) {

                    // Error elements are object
                    $this->errors[] = $error;

                } else {

                    // Error elements are data
                    $this->errors[] = $this->makeError($error);
                }

            }
        }

        return $this;
    }

    /**
    * Add meta to document
    *
    * @param array|iterator|object
    * @return object this
    */
    public function setMeta($meta)
    {
        $this->meta = ($meta instanceof Element\Meta) ? $meta : $this->makeMeta($meta);
        return $this;
    }

    /**
    * Add links to document
    *
    * @param array|iterator|object
    * @return object this
    */
    public function setLinks($links)
    {
        $this->links = ($links instanceof Element\Links) ? $links : $this->makeLinks($links);
        return $this;
    }

    /**
    * Add objects to included
    *
    * @param object|iterator|array
    * @return object this
    */
    public function setIncluded($collection)
    {
        if (!$this->data) {
            throw new Exception('Document data is not set yet - included data must not be set');
        }

        // Check valid included
        $abstractCollection = [];
        if ($this->checkResource($collection) === self::IS_RESOURCE) {

            $abstractCollection[] = $this->getResourceInstance($collection);

        } elseif (is_iterable($collection) and $this->checkResource($collection, true) === self::IS_COLLECTION) {
            
            foreach ($collection as $resource) {
                $abstractCollection[] = $this->getResourceInstance($resource);
            }

        } else {
            throw new Exception('Included data must be a valid collection');
        }

        $this->included = $abstractCollection;

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