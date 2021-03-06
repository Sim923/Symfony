<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2014 Thurly
 */

namespace Thurly\Main\Entity;

/**
 * Entity field class for enum data type
 * @package thurly
 * @subpackage main
 */
class FloatField extends ScalarField
{
	/** @var int|null */
	protected $scale;

	public function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		if(isset($parameters['scale']))
		{
			$this->scale = intval($parameters['scale']);
		}
	}

	/**
	 * @return int|null
	 */
	public function getScale()
	{
		return $this->scale;
	}

	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbFloat($value);
	}
}