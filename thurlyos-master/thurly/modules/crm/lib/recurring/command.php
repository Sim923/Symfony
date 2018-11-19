<?php
namespace Thurly\Crm\Recurring;

use Thurly\Main\Error,
	Thurly\Main\DB,
	Thurly\Main\Result;

class Command
{
	/**
	 * @param string $type
	 * @param string $operation
	 * @param $data
	 *
	 * @return Result|DB\Result
	 */
	public static function execute($type = "", $operation = "", array $data = array())
	{
		$result = new Result();

		/** @var Entity\Base $entity */
		$entity = static::loadEntity($type);
		if (!$entity)
		{
			$result->addError(new Error("Entity type is not allowed for recurring"));
			return $result;
		}

		if (!method_exists($entity, $operation))
		{
			$result->addError(new Error("Method is not allowed for recurring entity"));
		}

		return call_user_func_array(array($entity, $operation), $data);
	}

	/**
	 * @param $type
	 *
	 * @return Entity\Base | null
	 */
	public static function loadEntity($type)
	{
		$className = __NAMESPACE__."\\Entity\\".$type;
		if (!class_exists($className))
		{
			return null;
		}

		return new $className();
	}
}