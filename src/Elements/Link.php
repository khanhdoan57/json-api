<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Link object
*/

namespace HackerBoy\JsonApi\Elements;

use HackerBoy\JsonApi\Abstracts\Document;
use HackerBoy\JsonApi\Abstracts\Element;
use Exception;

class Link extends Element {

    /**
    * @inheritdoc
    */
    public function __construct($link, Document $document)
    {
        if (!$link) {
            $link = null;
        }

        if ($link !== null) {

            if (!is_array($link)) {
                throw new Exception('Link data must be an array');
            }

            if (!array_key_exists('href', $link)) {
                throw new Exception('Link data must contain href as a valid URL');
            }

        }

        // Check data valid
        if (is_iterable($link)) {

            foreach ($link as $key => $value) {

                if (!is_string($key)) {
                    throw new Exception('Link data key must be string');
                }

                if ($key === 'href') {

                    // Check valid URL
                    if (!preg_match('/\//', $value) and !filter_var($value, FILTER_VALIDATE_URL) and $value !== null) {
                        throw new Exception($value.' is not a valid url.');
                    }

                } elseif ($key === 'meta') {

                    // Parse meta object
                    $link[$key] = new Meta($value, $document);

                } else {
                    throw new Exception('Link data cannot contain key: '.$key);
                }

            }

        }

        // All good? Save data
        $this->data = $link;
    }

    /**
    * @inheritdoc
    */
    public function jsonSerialize()
    {
        return $this->data;
    }

}
