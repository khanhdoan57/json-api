<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Element abstract class
*/

namespace HackerBoy\JsonApi\Abstracts;

use HackerBoy\JsonApi\Traits\AbstractDataConvert;

abstract class Element implements \JsonSerializable {

    use AbstractDataConvert;

    /**
    * Constructor
    *
    * @param mixed Input data
    * @param object Document
    */
    abstract public function __construct($data, Document $document);

    /**
    * Element data
    *
    * @access protected
    * @var array
    */
    protected $data;

    /**
    * Get data for public access
    *
    * @access public
    * @return array
    */
    public function getData()
    {
        return $this->data;
    }

}