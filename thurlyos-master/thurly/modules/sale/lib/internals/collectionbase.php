<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale\Internals;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class CollectionBase
 * @package Thurly\Sale\Internals
 */
abstract class CollectionBase
	implements \ArrayAccess, \Countable, \IteratorAggregate
{
	protected $collection = array();

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->collection);
	}


	/**
	 * Whether a offset exists
	 */
	public function offsetExists($offset)
	{
		return isset($this->collection[$offset]) || array_key_exists($offset, $this->collection);
	}

	/**
	 * Offset to retrieve
	 */
	public function offsetGet($offset)
	{
		if (isset($this->collection[$offset]) || array_key_exists($offset, $this->collection))
		{
			return $this->collection[$offset];
		}

		return null;
	}

	/**
	 * Offset to set
	 */
	public function offsetSet($offset, $value)
	{
		if($offset === null)
		{
			$this->collection[] = $value;
		}
		else
		{
			$this->collection[$offset] = $value;
		}
	}

	/**
	 * Offset to unset
	 */
	public function offsetUnset($offset)
	{
		unset($this->collection[$offset]);
	}

	/**
	 * Count elements of an object
	 */
	public function count()
	{
		return count($this->collection);
	}

	/**
	 * Return the current element
	 */
	public function current()
	{
		return current($this->collection);
	}

	/**
	 * Move forward to next element
	 */
	public function next()
	{
		return next($this->collection);
	}

	/**
	 * Return the key of the current element
	 */
	public function key()
	{
		return key($this->collection);
	}

	/**
	 * Checks if current position is valid
	 */
	public function valid()
	{
		$key = $this->key();
		return $key !== null;
	}

	/**
	 * Rewind the Iterator to the first element
	 */
	public function rewind()
	{
		return reset($this->collection);
	}

	/**
	 * Checks if collection is empty.
	 *
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty($this->collection);
	}
}