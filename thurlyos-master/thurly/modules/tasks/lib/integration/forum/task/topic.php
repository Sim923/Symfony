<?
/**
 * Class implements all further interactions with "forum" module considering "task comment" entity
 *
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Thurly\Tasks\Integration\Forum\Task;

use Thurly\Main\DB\SqlExpression;
use Thurly\Main\Entity\ExpressionField;
use Thurly\Main\Entity\Query;
use Thurly\Main\Entity\ReferenceField;
use Thurly\Main\Loader;

use Thurly\Disk\Driver;
use Thurly\Disk\Internals\AttachedObjectTable;
use Thurly\Tasks\Item\Task;

final class Topic extends \Thurly\Tasks\Integration\Forum
{
	/**
	 * Fires when new topic was added for the task
	 *
	 * @param $entityType
	 * @param $entityId
	 * @param $arPost
	 * @param $arTopic
	 * @return bool|void
	 */
	public static function onBeforeAdd($entityType, $entityId, $arPost, &$arTopic)
	{
		// 'TK' is our entity type
		if ($entityType !== 'TK')
		{
			return;
		}

		if(!(\CTaskAssert::isLaxIntegers($entityId) && ((int) $entityId >= 1)))
		{
			\CTaskAssert::logError('[0xb6324222] Expected integer $entityId >= 1');
			return;
		}

		$taskId = (int) $entityId;

		$task = Task::getInstance($taskId);
		if($task["TITLE"]) // it means you can read the task
		{
			$arTopic["TITLE"] = $task["TITLE"];
			$arTopic["MESSAGE"] = trim($task["TITLE"]."\n".$task["DESCRIPTION"]);
			$arTopic["AUTHOR_ID"] = $task["CREATED_BY"];
		}

		return true;
	}

	public static function onAfterAdd($entityType, $entityId, $topicId)
	{
		// 'TK' is our entity type
		if ($entityType !== 'TK')
		{
			return;
		}

		if(!(\CTaskAssert::isLaxIntegers($entityId) && ((int) $entityId >= 1)))
		{
			\CTaskAssert::logError('[0xb6324222] Expected integer $entityId >= 1');
			return;
		}

		$entityId = (int) $entityId;

		if ($entityType === 'TK')
		{
			// todo: probably use low-level orm here
			$oTask = new \CTasks();
			$oTask->update($entityId, array('FORUM_TOPIC_ID' => $topicId));
		}

		return true;
	}

	/**
	 * Get file count for a topic
	 *
	 * @param $topicId
	 * @param int $forumId
	 * @return int
	 * @throws \Thurly\Main\LoaderException
	 */
	public static function getFileCount($topicId, $forumId = 0)
	{
		$count = 0;
		$topicId = intval($topicId);
		$forumId = intval($forumId);
		if(!$forumId)
		{
			$forumId = static::getForumId();
		}

		if($forumId && $topicId && static::includeModule() && Loader::includeModule("disk"))
		{
			$userFieldManager = Driver::getInstance()->getUserFieldManager();
			list($connectorClass, $moduleId) = $userFieldManager->getConnectorDataByEntityType("forum_message");

			$countQuery = new Query(AttachedObjectTable::getEntity());
			$totalCnt = $countQuery
				->setFilter(array(
					"=ENTITY_TYPE" => $connectorClass,
					"=MODULE_ID" => $moduleId,
					"=VERSION_ID" => null,
				))
				->addSelect(new ExpressionField("CNT", "COUNT(1)"))
				->registerRuntimeField("",
					new ReferenceField(
						"M",
						"Thurly\\Forum\\MessageTable",
						array(
							"=this.ENTITY_ID" => "ref.ID",
							"=ref.TOPIC_ID" => new SqlExpression("?i", $topicId),
							"=ref.FORUM_ID" => new SqlExpression("?i", $forumId),
						),
						array(
							"join_type" => "INNER"
						)
					)
				)
				->setLimit(null)
				->setOffset(null)
				->exec()
				->fetch();

			$count = intval($totalCnt["CNT"]);
		}

		return $count;
	}

	public static function delete($id)
	{
		$id = intval($id);

		if($id && static::includeModule())
		{
			\CForumTopic::Delete($id);
		}
	}
}