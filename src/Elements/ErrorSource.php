<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Error source object
*/

namespace HackerBoy\JsonApi\Elements;

use HackerBoy\JsonApi\Abstracts\Document;
use HackerBoy\JsonApi\Abstracts\Element;
use Exception;

class ErrorSource extends Element {

    /**
    * @inheritdoc
    */
    public function __construct($data, Document $document)
    {
        if (!is_array($data)) {
            throw new Exception('Error source data must be an array');
        }

        $this->data = [];

        foreach ($data as $key => $value) {

            if (!is_string($value)) {
                throw new Exception('Error source '.$key.' must be a string');
            }

            if (in_array($key, ['pointer', 'parameter'])) {
                $this->data[$key] = $value;
            }

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