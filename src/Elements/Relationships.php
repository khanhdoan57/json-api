<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Relationships element
*/

namespace HackerBoy\JsonApi\Elements;

use HackerBoy\JsonApi\Abstracts\Document;
use HackerBoy\JsonApi\Abstracts\Element;
use Exception;

class Relationships extends Element {

    /**
    * @inheritdoc
    */
    public function __construct($relationships, Document $document)
    {
        $this->data = [];

        // Check valid relationships data
        if (!is_array($relationships)) {
            throw new Exception('Relationships data must be an array');
        }

        foreach ($relationships as $relationshipKey => $relationshipData) {
            
            if (!is_string($relationshipKey)) {
                throw new Exception('Relationship key must be a string');
            }

            $this->data[$relationshipKey] = [];

            // Relationship resource
            $resource = null;

            // If data has other information than resources
            if (is_array($relationshipData) and array_key_exists('data', $relationshipData)) {

                // Check and remove all un-accepted data
                foreach ($relationshipData as $key => $value) {
                        
                    if ($key === 'links') {

                        // Parse links object
                        $this->data[$relationshipKey][$key] = new Links($value, $document);

                    } elseif ($key === 'meta') {

                        // Parse meta object
                        $this->data[$relationshipKey][$key] = new Meta($value, $document);

                    } elseif ($key === 'data') {

                        // Attach resource
                        $resource = $relationshipData['data'];

                    } else {

                        // Invalid data - remove it
                        unset($relationships[$relationshipKey][$key]);
                    }

                }

            } else { // If data contain only resource

                $resource = $relationshipData;
            
            }

            // Check resource
            $data = null;
            $document->resourceHandler($resource, function($resource) use (&$data, $document) {

                $data = new Relationship($resource, $document);

            }, function($collection) use (&$data, $document) {

                $data = [];

                foreach ($collection as $resource) {
                    $data[] = new Relationship($resource, $document);
                }

            });

            // Save the data
            $this->data[$relationshipKey]['data'] = $data;
        }

    }

    /**
    * @inheritdoc
    */
    public function jsonSerialize()
    {
        return $this->data;
    }
}