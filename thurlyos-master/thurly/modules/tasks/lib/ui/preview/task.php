<?php

namespace Thurly\Tasks\Ui\Preview;

use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;
use Thurly\Tasks\Util\User;

Loc::loadLanguageFile(__FILE__);

class Task
{
	public static function buildPreview(array $params)
	{
		global $APPLICATION;

		ob_start();
		$APPLICATION->IncludeComponent(
			'thurly:tasks.task.preview',
			'',
			$params
		);
		return ob_get_clean();
	}

	public static function checkUserReadAccess(array $params)
	{
		$task = new \CTaskItem($params['taskId'], static::getUser()->GetID());
		$access = $task->checkCanRead();

		return !!$access;
	}

	public static function getImAttach(array $params)
	{
		if(!Loader::includeModule('im'))
			return false;

		$task = \CTasks::getById(
			$params['taskId'],
			false,
			array(
				'returnAsArray'  => true,
				'bSkipExtraData' => false
			)
		);
		if($task === false)
			return false;

		$task['LINK'] = \CTaskNotifications::getNotificationPath(array('ID' => $task['RESPONSIBLE_ID']), $task['ID']);

		$attach = new \CIMMessageParamAttach(1, '#E30000');
		$attach->AddUser(Array(
			'NAME' => \CTextParser::clearAllTags($task['TITLE']),
			//'AVATAR' => '', // todo: task icon
			'LINK' => $task['LINK']
		));
		$attach->AddDelimiter(Array('COLOR' => '#c6c6c6'));
		$grid = array();
		if($task['STATUS'] > 0)
		{
			$grid[] = Array(
				"NAME" => Loc::getMessage('TASK_PREVIEW_FIELD_STATUS') . ":",
				"VALUE" => Loc::getMessage('TASKS_TASK_STATUS_'.$task['STATUS']),
				"DISPLAY" => "COLUMN",
				"WIDTH" => 120,
			);
		}

		$grid[] = Array(
			"NAME" => Loc::getMessage('TASK_PREVIEW_FIELD_ASSIGNER') . ":",
			"VALUE" => htmlspecialcharsback(\Thurly\Im\User::getInstance($task['CREATED_BY'])->getFullName()),
			"USER_ID" => $task['CREATED_BY'],
			"DISPLAY" => "COLUMN",
			"WIDTH" => 120,
		);

		$grid[] = Array(
			"NAME" => Loc::getMessage('TASK_PREVIEW_FIELD_RESPONSIBLE') . ":",
			"VALUE" => htmlspecialcharsback(\Thurly\Im\User::getInstance($task['RESPONSIBLE_ID'])->getFullName()),
			"USER_ID" => $task['RESPONSIBLE_ID'],
			"DISPLAY" => "COLUMN",
			"WIDTH" => 120,
		);

		if($task['DEADLINE'] != '')
		{
			$grid[] = Array(
				"NAME" => Loc::getMessage('TASK_PREVIEW_FIELD_DEADLINE') . ":",
				"VALUE" => $task['DEADLINE'],
				"DISPLAY" => "COLUMN",
				"WIDTH" => 120,
			);
		}

		if($task['DESCRIPTION'] != '')
		{
			$description = \CTextParser::clearAllTags($task['DESCRIPTION']);
			if(strlen($description) > 100)
			{
				$description = substr($description, 0, 100).'...';
			}

			$grid[] = Array(
				"NAME" => Loc::getMessage('TASK_PREVIEW_FIELD_DESCRIPTION') . ":",
				"VALUE" => $description,
				"DISPLAY" => "COLUMN",
				"WIDTH" => 120,
			);
		}
		
		if($task['GROUP_ID'] > 0)
		{
			$groupId = $task['GROUP_ID'];
			$groupData = \Thurly\Tasks\Integration\SocialNetwork\Group::getData(array($groupId));
			if(is_array($groupData[$groupId]))
			{
				$groupName = $groupData[$groupId]['NAME'];
				$grid[] = Array(
					"NAME" => Loc::getMessage("TASK_PREVIEW_FIELD_GROUP") . ":",
					"VALUE" => $groupName,
					"DISPLAY" => "COLUMN",
					"WIDTH" => 120,
				);
			}
		}

		$attach->AddGrid($grid);
		return $attach;
	}

	protected function getUser()
	{
		return User::get();
	}
}