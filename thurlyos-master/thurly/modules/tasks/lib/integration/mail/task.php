<?
/**
 * Class implements all further interactions with "mail" module considering "task" entity
 *
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Thurly\Tasks\Integration\Mail;

use \Thurly\Main\Event;
use \Thurly\Main\Localization\Loc;
use Thurly\Main\Loader;

Loc::loadMessages(__FILE__);

final class Task extends \Thurly\Tasks\Integration\Mail
{
	public static function onReplyReceived(Event $event)
	{
		$taskId = intval($event->getParameter('entity_id'));
		$userId = intval($event->getParameter('from'));
		$message = trim($event->getParameter('content'));
		$attachments = $event->getParameter('attachments');
		if(!is_array($attachments))
		{
			$attachments = array();
		}

		// message can not be empty, so if only attachments supplied, use dummy message
		if (
			strlen($message) <= 0
			&& count($attachments) > 0
		)
		{
			$message = Loc::getMessage('TASKS_MAIL_ATTACHMENTS');
		}

		if (
			$taskId <= 0
			|| $userId <= 0
			|| strlen($message) <= 0
		)
		{
			return false;
		}

		list($message, $files) = static::processAttachments($message, $attachments, $userId);

		try
		{
			if (Loader::includeModule('disk'))
			{
				\Thurly\Disk\Uf\FileUserType::setValueForAllowEdit("FORUM_MESSAGE", true);
			}

			// forum will check rights, no need to worry
			\Thurly\Tasks\Integration\Forum\Task\Comment::add($taskId, array(
				'POST_MESSAGE' => $message,
				'AUTHOR_ID' => $userId,
				'UF_FORUM_MESSAGE_DOC' => $files,
			));
		}
		catch(\TasksException $e) // todo: get rid of this annoying catch by making \Thurly\Tasks\*Exception classes inherited from TasksException (dont forget about code)
		{
			if($e->checkOfType(\TasksException::TE_TASK_NOT_FOUND_OR_NOT_ACCESSIBLE))
			{
				return false;
			}
			else
			{
				throw $e; // let it log
			}
		}
		catch(\Thurly\Tasks\AccessDeniedException $e) // task not found or not accessible
		{
			return false;
		}

		return true; // required, dont remove
	}

	public static function onForwardReceived(Event $event)
	{
		$userId = intval($event->getParameter('from'));
		$message = trim($event->getParameter('content'));
		$subject = trim($event->getParameter('subject'));
		$attachments = $event->getParameter('attachments');
		$siteId = $event->getParameter('site_id');

		if (
			$userId <= 0
			|| strlen($siteId) <= 0
		)
		{
			return false;
		}

		list($message, $files) = static::processAttachments($message, $attachments, $userId);

		try
		{
			if (Loader::includeModule('disk'))
			{
				\Thurly\Disk\Uf\FileUserType::setValueForAllowEdit("TASKS_TASK", true);
			}

			$task = \CTaskItem::add(array(
				'TITLE' => $subject == '' ? Loc::getMessage('TASKS_MAIL_NEW_TASK') : $subject,
				'DESCRIPTION' => $message,
				'CREATED_BY' => $userId,
				'RESPONSIBLE_ID' => $userId,
				\Thurly\Tasks\Integration\Disk\UserField::getMainSysUFCode() => $files
			), $userId);
		}
		catch(\TasksException $e) // todo: get rid of this annoying catch by making \Thurly\Tasks\*Exception classes inherited from TasksException (dont forget about code)
		{
			if($e->checkOfType(\TasksException::TE_TASK_NOT_FOUND_OR_NOT_ACCESSIBLE))
			{
				return false;
			}
			else
			{
				throw $e; // let it log
			}
		}

		return $task->getId(); // required, dont remove
	}

	public static function getDefaultPublicPath($taskId)
	{
		return \Thurly\Tasks\UI\Task::makeActionUrl('/pub/task.php?task_id=#task_id#', $taskId);
	}

	public static function getReplyTo($userId, $taskId, $postUrl, $siteId, $backUrl = '')
	{
		$res = static::getLinks($userId, $taskId, $postUrl, $siteId, $backUrl);
		if (is_array($res))
		{
			list($replyTo, $newBackUrl) = $res;
			return (string) $replyTo;
		}

		return '';
	}

	public static function getBackUrl($userId, $taskId, $postUrl, $siteId, $backUrl = '')
	{
		$res = static::getLinks($userId, $taskId, $postUrl, $siteId, $backUrl);
		if (is_array($res))
		{
			list($replyTo, $newBackUrl) = $res;
			return (string) $newBackUrl;
		}

		return '';
	}

	private static function getLinks($userId, $taskId, $postUrl, $siteId, $backUrl = '')
	{
		if(!static::includeModule() || !$taskId)
		{
			return false;
		}

		return \Thurly\Mail\User::getReplyTo(
			$siteId,
			$userId,
			'TASKS_TASK',
			$taskId,
			$postUrl,
			$backUrl
		);
	}
}