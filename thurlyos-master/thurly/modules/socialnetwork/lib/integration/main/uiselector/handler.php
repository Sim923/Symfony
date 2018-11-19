<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage socialnetwork
 * @copyright 2001-2017 Thurly
 */
namespace Thurly\Socialnetwork\Integration\Main\UISelector;

use Thurly\Main\Localization\Loc;
use Thurly\Main\Event;
use Thurly\Main\EventResult;
use Thurly\Main\Loader;

Loc::loadMessages(__FILE__);

class Handler
{
	public static function isExtranetUser()
	{
		return (
			Loader::includeModule('extranet')
			&& !\CExtranet::isIntranetUser()
		);
	}

	public static function getNameTemplate($requestFields = array())
	{
		if (!empty($requestFields["nt"]))
		{
			preg_match_all("/(#NAME#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\\s|\\,/", urldecode($requestFields["nt"]), $matches);
			$result = implode("", $matches[0]);
		}
		else
		{
			$result = \CSite::getNameFormat(false);
		}

		return $result;
	}

	public static function OnUISelectorActionProcessAjax(Event $event)
	{
		$result = new EventResult(EventResult::UNDEFINED, null, 'socialnetwork');

		$action = $event->getParameter('action');

		$resultParams = false;

		if ($action == \Thurly\Main\UI\Selector\Actions::GET_DATA)
		{
			$resultParams = Entities::getData($event->getParameter('options'));
		}
		elseif ($action == \Thurly\Main\UI\Selector\Actions::SEARCH)
		{
			$resultParams = Search::process($event->getParameter('requestFields'));
		}
		elseif ($action == \Thurly\Main\UI\Selector\Actions::GET_DEPARTMENT_DATA)
		{
			$resultParams = Entities::getDepartmentData($event->getParameter('requestFields'));
		}

		if ($resultParams)
		{
			$result = new EventResult(
				EventResult::SUCCESS,
				array(
					'result' => $resultParams
				),
				'socialnetwork'
			);
		}

		return $result;
	}

	public static function OnUISelectorEntitiesGetList(Event $event)
	{
		$itemsSelected = $event->getParameter('itemsSelected');

		if (
			empty($itemsSelected)
			|| !is_array($itemsSelected)
		)
		{
			return new EventResult(EventResult::ERROR, null, 'socialnetwork');
		}

		$entities = Entities::getList(array('itemsSelected' => $itemsSelected));

		return new EventResult(
			EventResult::SUCCESS,
			array(
				'result' => $entities
			),
			'socialnetwork'
		);
	}
}
