<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Element abstract class
*/

namespace HackerBoy\JsonApi\Abstracts;

use HackerBoy\JsonApi\Traits\AbstractDataConvert;
use HackerBoy\JsonApi\Traits\ElementIterator;
use HackerBoy\JsonApi\Traits\ElementCountable;

abstract class Element implements \JsonSerializable, \Iterator, \Countable {

    use AbstractDataConvert, ElementIterator, ElementCountable;

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