<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Error element
*/

namespace HackerBoy\JsonApi\Elements;

use HackerBoy\JsonApi\Abstracts\Document;
use HackerBoy\JsonApi\Abstracts\Element;
use Exception;

class Error extends Element {

    /**
    * @inheritdoc
    */
    public function __construct($data, Document $document)
    {
        if (!is_array($data)) {
            throw new Exception('Error data must be an array');
        }

        $this->data = [];

        foreach ($data as $key => $value) {

            if (in_array($key, ['id', 'code', 'status', 'title', 'detail'])) {
                
                $this->data[$key] = $key === 'id' ? $value : (string) $value;

            } elseif ($key === 'meta') {

                $this->data[$key] = $document->makeMeta($value);

            } elseif ($key === 'source') {

                $this->data[$key] = $document->makeErrorSource($value);

            }

        }

        if (!count($this->data)) {
            throw new Exception('Error data cannot be blank');
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