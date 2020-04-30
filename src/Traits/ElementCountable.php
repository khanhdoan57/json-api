<?php

namespace HackerBoy\JsonApi\Traits;

trait ElementCountable {

	/**
	* Count
	*
	* @param void
	* @return integer
	*/
	public function count()
	{
		return count($this->toArray());
	}
}