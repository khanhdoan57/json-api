<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Flexible Document object
*/

namespace HackerBoy\JsonApi\Flexible;

use HackerBoy\JsonApi\Document as BaseDocument;

class Document extends BaseDocument {
    
    protected $isFlexible = true;

    public function __construct($config = [])
    {
        // If has no config
        if (!$config) {
            return;
        }

        if (!array_key_exists('resource_map', $config)) {
            $config['resource_map'] = [0];
        }
    
        return parent::__construct($config);
    }

    /**
    * Make flexible resource
    */
    public function makeFlexibleResource()
    {
        return new Resource($this);
    }

}