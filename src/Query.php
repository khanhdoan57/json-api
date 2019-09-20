<?php

/**
* @author HackerBoy.com <admin@hackerboy.com>
* @package hackerboy/json-api
*
* Query object
*/

namespace HackerBoy\JsonApi;

use Illuminate\Support\Collection;

class Query extends Collection {

    /**
	* Map a resource
	*
	* @param string Resource type
	* @param string|integer Resource ID
	* @param object Resource model object
	* @return this
    */
    public function mapResource($resource)
    {
    	$hash = spl_object_hash($resource);

    	if (!$this->has($hash)) {
    		$this->offsetSet($hash, $resource);
    	}

    	return $this;
    }

}