<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Pagination links element
*/

namespace HackerBoy\JsonApi\Elements;

use HackerBoy\JsonApi\Abstracts\Document;
use Exception;

class Pagination extends Links {

    /**
    * @inheritdoc
    */
    public function __construct($links, Document $document)
    {
        // Validate links
        if (!is_array($links)) {
            throw new Exception('Pagination data must be an array');
        }

        $validKeys = ['self', 'first', 'last', 'next', 'prev'];

        foreach ($links as $key => $value) {
            
            if (!in_array($key, $validKeys)) {
                throw new Exception('Invalid pagination key ('.$key.'). Pagination key must be: first, last, prev, next');
            }

        }

        parent::__construct($links, $document);
    }

    /**
    * @inheritdoc
    */
    public function jsonSerialize()
    {
        return $this->data;
    }

}