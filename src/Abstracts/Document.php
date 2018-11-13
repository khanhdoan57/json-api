<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Document abstract class
*/

namespace HackerBoy\JsonApi\Abstracts;

use Closure;
use Exception;
use HackerBoy\JsonApi\Traits\AbstractDataConvert;

abstract class Document implements \JsonSerializable {

    use AbstractDataConvert;

    /**
    * JSON API version
    */
    const VERSION = '1.0';

    /**
    * Resource check result
    */
    const IS_RESOURCE = 1; // Valid and is a resource 
    const IS_COLLECTION = 2; // Valid and is a collection
    const INVALID_RESOURCE = 3; // Invalid resource
    const INVALID_COLLECTION = 4; // Invalid collection
    const MIXED_COLLECTION = 5; // Collection contain mixed resources

    /**
    * Document configuration
    *
    * @access protected
    * @var array
    */
    protected $config;

    /**
    * Data to encode
    *
    * @access protected
    * @var object|array
    */
    protected $data;

    /**
    * Included data to encode
    *
    * @access protected
    * @var object|array
    */
    protected $included;

    /**
    * Document errors
    *
    * @access protected
    * @var array Collection of Error element
    */
    protected $errors;

    /**
    * Document meta
    *
    * @access protected
    * @var object Meta element
    */
    protected $meta;

    /**
    * Document links
    *
    * @access protected
    * @var object Meta element
    */
    protected $links;

    /**
    * Base API URL
    *
    * @access protected
    * @var string
    */
    protected $url;

    /**
    * Model => Resource map
    *
    * @access protected
    * @var array
    */
    protected $resourceMap;

    /**
    * Get document config
    *
    * @param string Config key
    * @return mixed
    */
    final public function getConfig($key = '')
    {
        if (!$key) {
            return $this->config;
        }

        return array_key_exists($key, $this->config) ? $this->config[$key] : null;
    }

    /**
    * Get API Url
    *
    * @param string|void
    * @return string
    */
    final public function getUrl($path = '')
    {
        return $this->url.'/'.$path;
    }

    /**
    * Check resource / collection is valid
    *
    * @param object|array Resource / Collection
    * @return constant
    */
    final public function checkResource($resource, $allowMixedCollection = false)
    {
        // Check if resource is valid
        if (is_object($resource) and array_key_exists(get_class($resource), $this->resourceMap) and is_subclass_of($this->resourceMap[get_class($resource)], Resource::class)) {
            return self::IS_RESOURCE;
        } 

        // Check if this is a valid collection
        if (!is_iterable($resource)) {
            
            // Not? This is an invalid resource
            return self::INVALID_RESOURCE;

        }

        $firstResource = null;
        foreach ($resource as $_resource) {

            // Check resource valid
            if ($this->checkResource($_resource) !== self::IS_RESOURCE) {
                    
                // Invalid collection
                return self::INVALID_COLLECTION;

            }
            
            // Save first resource
            if (!$firstResource) {

                $firstResource = $_resource;
                continue;

            }

            // Check other resources the same as first one
            if (!$allowMixedCollection and (get_class($_resource) !== get_class($firstResource))) {
                return self::MIXED_COLLECTION;
            }


        }

        // All good? 
        return self::IS_COLLECTION;
    
    }

    /**
    * Resource handler
    *
    * @param object|array Resource or collection
    * @param Closure Callback for resource
    * @param Closure Callback for collection
    * @return void
    */
    final public function resourceHandler($resource, Closure $resourceHandler, Closure $collectionHandler)
    {
        // Check resource
        $checkResource = $this->checkResource($resource);

        switch ($checkResource) {

            case self::IS_RESOURCE :
                $resourceHandler($resource);  
            break;

            case self::IS_COLLECTION :
                $collectionHandler($resource);
            break;

            case self::INVALID_RESOURCE :
                throw new Exception('Invalid resource');
            break;

            case self::INVALID_COLLECTION :
                throw new Exception('Invalid resource collection. Resource collection is not iterable or its containing invalid resource object.');
            break;

            case self::MIXED_COLLECTION :
                throw new Exception('Collection contains mixed resources');
            break;
            
            default:
                throw new Exception('Unknown error', 1);
            break;

        }
    }

    /**
    * Get resource handle for model object
    *
    * @param object Model object
    * @return object Resource
    */
    final public function getResourceInstance($resource)
    {
        if ($this->checkResource($resource) !== self::IS_RESOURCE) {
            throw new Exception('Invalid model object - cannot get resource instance');
        }

        return new $this->resourceMap[get_class($resource)]($resource, $this);
    }

    /**
    * Magic call to create element object
    *
    * @inheritdoc
    */
    final public function __call($method, $params)
    {
        if (preg_match('/make+/i', $method)) {
            
            $class = substr($method, 4);
            $class = '\\HackerBoy\\JsonApi\\Elements\\'.$class;
            $data = isset($params[0]) ? $params[0] : [];

            if (class_exists($class)) {
                return new $class($data, $this);
            }

        }

        throw new Exception('Method '.$method.' does not exist');
    }

    

}