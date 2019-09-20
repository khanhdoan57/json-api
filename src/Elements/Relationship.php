<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Relationship object
*/

namespace HackerBoy\JsonApi\Elements;

use HackerBoy\JsonApi\Abstracts\Document;
use HackerBoy\JsonApi\Abstracts\Element;
use Exception;

class Relationship extends Element {

    /**
    * @inheritdoc
    */
    public function __construct($resource, Document $document)
    {
        $this->data = $document->getResource($resource);
    }

    /**
    * @inheritdoc
    */
    public function jsonSerialize()
    {
        return [
            'id' => (string) $this->data->getId(),
            'type' => (string) $this->data->getType()
        ];
    }

}