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

        $requiredKeys = ['first', 'last'];

        foreach ($requiredKeys as $key) {
            
            if (!array_key_exists($key, $links)) {
                throw new Exception('Pagination data must have "'.$key.'" member');
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