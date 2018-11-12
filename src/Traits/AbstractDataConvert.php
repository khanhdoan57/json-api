<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Object data convert standard
*/

namespace HackerBoy\JsonApi\Traits;

trait AbstractDataConvert {

    /**
    * Document structure to be encoded with json_encode()
    *
    * @access public
    * @param void
    * @return array
    */
    abstract public function jsonSerialize();

    /**
    * Document to array
    *
    * @param void
    * @return array
    */
    final public function toArray()
    {
        return json_decode($this->toJson(0), true);
    }

    /**
    * Document to json
    *
    * @param const json_encode option
    * @return string json
    */
    final public function toJson($option = JSON_PRETTY_PRINT)
    {
        return json_encode($this, $option);
    }

}