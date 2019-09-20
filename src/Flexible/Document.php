<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Flexible Document object
*/

namespace HackerBoy\JsonApi\Flexible;

use HackerBoy\JsonApi\Document as BaseDocument;
use HackerBoy\JsonApi\Helpers\Validator;
use Exception;

class Document extends BaseDocument {
    
    protected $isFlexible = true;

    /**
    * Load data from string
    *
    * @param string JSONAPI document string
    * @return object new Document object
    */
    public static function parseFromString($data, $config = [])
    {
        // Check data valid
        if (!Validator::isValidResponseString($data) and !Validator::isValidRequestString($data)) {
            throw new Exception('Invalid JSON:API data');
        }

        return self::parseFromArray(json_decode($data, true));
    }

    /**
    * Load data from array
    *
    * @param array JSONAPI document as array
    * @return Document
    */
    public static function parseFromArray(array $data, $config = [])
    {
        // Check data valid
        if (!Validator::isValidResponseString(json_encode($data)) and !Validator::isValidRequestString(json_encode($data))) {
            throw new Exception('Invalid JSON:API data');
        }

        // New document
        $document = new Document($config);

        // Parse document data
        if (isset($data['data']) and is_array($data['data'])) {
            
            // Single resource
            if (isset($data['data']['type'])) {
                $document->setData($document->newResourceFromArray($data['data']));
            } else {

                $documentData = [];

                foreach ($data['data'] as $resourceData) {
                    $documentData[] = $document->newResourceFromArray($resourceData);
                }

                $document->setData($documentData);

            }

        }

        // Parse document included
        if (isset($data['included']) and is_array($data['included'])) {

            $documentIncluded = [];

            foreach ($data['included'] as $resourceData) {
                $documentIncluded[] = $document->newResourceFromArray($resourceData);
            }

            $document->setIncluded($documentIncluded);

        }

        // Parse document meta
        if (isset($data['meta'])) {
            $document->setMeta($data['meta']);
        }

        // Parse document link
        if (isset($data['links'])) {
            $document->setLinks($data['links']);
        }

        // Parse document errors
        if (isset($data['errors'])) {
            $document->setErrors($data['errors']);
        }

        return $document;
    }

    public function __construct($config = [])
    {
        // If has no config
        if (!array_key_exists('resource_map', $config)) {
            $config['resource_map'] = [0];
        }
    
        return parent::__construct($config);
    }

    /**
    * Make flexible resource
    *
    * @return Resource
    */
    public function makeFlexibleResource()
    {
        return new Resource($this);
    }

    /**
    * Make new resource from array
    *
    * @param array $data
    * @return Resource
    */
    public function newResourceFromArray(array $data)
    {
        $resource = $this->makeFlexibleResource();

        if (!isset($data['type'])) {
            throw new Exception('Resource type is not defined');
        }

        $resource->setType($data['type']);

        if (isset($data['id'])) {
            $resource->setId($data['id']);
        }

        if (isset($data['attributes'])) {
            $resource->setAttributes($data['attributes']);
        }

        if (isset($data['relationships'])) {
            $resource->setRelationships($data['relationships']);
        }

        if (isset($data['links'])) {
            $resource->setLinks($data['links']);
        }

        if (isset($data['meta'])) {
            $resource->setMeta($data['meta']);
        }

        return $resource;

    }

}