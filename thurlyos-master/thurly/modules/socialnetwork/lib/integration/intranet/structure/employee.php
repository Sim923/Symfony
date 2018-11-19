<?php
/**
* Thurly Framework
* @package thurly
* @subpackage socialnetwork
* @copyright 2001-2017 Thurly
*/
namespace Thurly\Socialnetwork\Integration\Intranet\Structure;

use Thurly\Main\Event;
use Thurly\Main\EventResult;

class Employee
{
	public static function onEmployeeDepartmentsChanged(Event $event)
	{
		global $CACHE_MANAGER;

		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$oldDepartmentList = $event->getParameter('oldDepartmentList');
		$newDepartmentList = $event->getParameter('newDepartmentList');

		if (
			!is_array($oldDepartmentList)
			|| !is_array($newDepartmentList)
		)
		{
			return $result;
		}

		$oldExtranet = (empty($oldDepartmentList) || empty($oldDepartmentList[0]));
		$newExtranet = (empty($newDepartmentList) || empty($newDepartmentList[0]));

		if (
			defined("BX_COMP_MANAGED_CACHE")
			&& (
				($oldExtranet && !$newExtranet)
				|| (!$oldExtranet && $newExtranet)
			)
		)
		{
			$CACHE_MANAGER->clearByTag("sonet_extranet_user_list");
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(),
			'socialnetwork'
		);

		return $result;
	}
}
?>