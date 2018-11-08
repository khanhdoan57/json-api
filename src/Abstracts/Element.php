<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Element abstract class
*/

namespace HackerBoy\JsonApi\Abstracts;

abstract class Element implements \JsonSerializable {

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
    * Element structure to be encoded with json_encode()
    *
    * @access public
    * @param void
    * @return array
    */
    abstract public function jsonSerialize();
}