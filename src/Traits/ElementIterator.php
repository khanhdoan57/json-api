<?php

namespace HackerBoy\JsonApi\Traits;

trait ElementIterator {

	/**
	* Current index
	*/
	private $iteratorIndex;

	/**
	* Current element
	*
	* @param void
	* @return mixed
	*/
	public function current()
	{
		$data = $this->toArray();

		if (!count($data)) {
			return null;
		}

		if ($this->iteratorIndex) {
			return $data[$this->iteratorIndex];
		}

		foreach ($data as $index => $value) {
			$this->iteratorIndex = $index;
			return $this->current();
		}

		return null;
	}

	/**
	* Return the current index
	*
	* @param void
	* @return mixed
	*/
	public function key()
	{
		return $this->iteratorIndex;
	}

	/**
	*
	*/
	public function next()
	{
		if (!$this->iteratorIndex and !$this->current()) {
			return;
		}

		$data = $this->toArray();

		$isNext = false;
		foreach ($data as $index => $value) {

			if ($isNext) {
				$this->iteratorIndex = $index;
				break;
			}

			if ($index !== $this->iteratorIndex) {
				continue;
			}

			$isNext = true;
		}
	}

	/**
	* Rewind
	*
	* @param void
	* @return void
	*/
	public function rewind()
	{
		$this->iteratorIndex = null;
		$this->current();
	}

	/**
	* Check valid
	*
	* @param void
	* @return bool
	*/
	public function valid()
	{
		$data = $this->toArray();
		return $this->iteratorIndex and array_key_exists($this->iteratorIndex, $data);
	}

}