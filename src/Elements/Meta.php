<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Meta element
*/

namespace HackerBoy\JsonApi\Elements;

use HackerBoy\JsonApi\Abstracts\Document;
use HackerBoy\JsonApi\Abstracts\Element;
use Exception;

class Meta extends Element {

    /**
    * @inheritdoc
    */
    public function __construct($data, Document $document)
    {
        if (!is_array($data)) {
            throw new Exception('Meta data must be a valid array');
        }

        // Check data
        foreach ($data as $key => $value) {
            
            if (!is_string($key)) {
                throw new Exception('Meta key must be a valid string');
            }

        }

        // Save data
        $this->data = $data;
    }

    /**
    * @inheritdoc
    */
    public function jsonSerialize()
    {
        return $this->data;
    }

}