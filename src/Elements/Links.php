<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Links element
*/

namespace HackerBoy\JsonApi\Elements;

use HackerBoy\JsonApi\Abstracts\Document;
use HackerBoy\JsonApi\Abstracts\Element;
use Exception;

class Links extends Element {

    /**
    * @inheritdoc
    */
    public function __construct($links, Document $document)
    {
        // Validate links
        if (!is_array($links)) {
            throw new Exception('Links data must be an array');
        }

        // Check links are valid
        foreach ($links as $key => $link) {
            
            if (!is_string($key)) {
                throw new Exception('Links data key must be a string');
            }

            if (is_string($link)) {

                // Check valid URL
                if (!preg_match('/\//', $link) and !filter_var($link, FILTER_VALIDATE_URL)) {
                    throw new Exception($link.' is not a valid url.');
                }

            } else {

                // Parse link
                $links[$key] = new Link($link, $document);

            }

        }

        // Save data
        $this->data = $links;
    }

    /**
    * @inheritdoc
    */
    public function jsonSerialize()
    {
        return $this->data;
    }

}