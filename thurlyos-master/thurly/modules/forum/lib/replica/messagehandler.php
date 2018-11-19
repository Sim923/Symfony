<?php
namespace Thurly\Forum\Replica;

class MessageHandler extends \Thurly\Replica\Client\BaseHandler
{
	protected $tasksForumId = 0;

	protected $tableName = "b_forum_message";
	protected $moduleId = "forum";

	protected $primary = array(
		"ID" => "auto_increment",
	);
	protected $predicates = array(
		"TOPIC_ID" => "b_forum_topic.ID",
	);
	protected $translation = array(
		"ID" => "b_forum_message.ID",
		"TOPIC_ID" => "b_forum_topic.ID",
		"AUTHOR_ID" => "b_user.ID",
		//TODO GUEST_ID
		"EDITOR_ID" => "b_user.ID",
	);
	protected $children = array(
	);

	protected $fileHandler = null;

	/**
	 * MessageHandler constructor.
	 *
	 */
	public function __construct()
	{
		$this->fileHandler = new \Thurly\Forum\Replica\ForumMessageAttachmentHandler();
		if (\Thurly\Main\Loader::includeModule('tasks'))
		{
			$this->tasksForumId = (int)\CTasksTools::GetForumIdForIntranet();
		}
	}

	/**
	 * Registers event handlers for database operations like add new, update or delete.
	 *
	 * @return void
	 */
	public function initDataManagerEvents()
	{
		parent::initDataManagerEvents();
		$this->fileHandler->initDataManagerEvents();
	}

	/**
	 * Forum event onAfterMessageAdd handler.
	 *
	 * @param integer &$id Forum message identifier.
	 * @param array $message Message record.
	 * @param array $topicInfo Forum topic fields.
	 * @param array $forumInfo Forum fields.
	 * @param array $fields Message record before add.
	 *
	 * @return void
	 * @see \CForumMessage::Add()
	 */
	public function onAfterMessageAdd(&$id, $message, $topicInfo, $forumInfo, $fields)
	{
		$op = \Thurly\Replica\Db\Operation::writeInsert($this->tableName, array('ID'), array('ID' => $id));
		$nodes = $op->getTableRecord()->getNodes(true, false);
		if ($nodes)
		{
			$this->fileHandler->onAfterAdd($id, $message);
		}
	}

	/**
	 * Forum event onBeforeMessageUpdate handler.
	 *
	 * @param integer &$id Forum message identifier.
	 * @param array &$fields Message record.
	 * @param string &$uploadDir Forum topic fields.
	 *
	 * @return void
	 * @see \CForumMessage::Update()
	 */
	public function onBeforeMessageUpdate(&$id, &$fields, &$uploadDir)
	{
		$mapper = \Thurly\Replica\Mapper::getInstance();
		$map = $mapper->getByPrimaryValue($this->tableName, array('ID'), array('ID' => $id));
		if ($map)
		{
			$this->fileHandler->onBeforeUpdate($id);
		}
	}

	/**
	 * Forum event onAfterMessageUpdate handler.
	 *
	 * @param integer &$id Forum message identifier.
	 * @param array &$newMessage Message record.
	 * @param array $oldMessage Message record before update.
	 *
	 * @return void
	 * @see \CForumMessage::Update()
	 */
	public function onAfterMessageUpdate(&$id, &$newMessage, $oldMessage)
	{
		$op = \Thurly\Replica\Db\Operation::writeUpdate($this->tableName, array('ID'), array('ID' => $id));
		$mapper = \Thurly\Replica\Mapper::getInstance();
		$map = $mapper->getByPrimaryValue($this->tableName, array('ID'), array('ID' => $id));
		if ($map)
		{
			$nodes = current($map);
			$this->fileHandler->onAfterUpdate($id, $newMessage, $nodes);
		}
	}

	/**
	 * Forum event onBeforeMessageDelete handler.
	 *
	 * @param integer $id Forum message identifier.
	 *
	 * @return void
	 * @see \CForumMessage::Delete()
	 */
	public function onBeforeMessageDelete($id)
	{
		$mapper = \Thurly\Replica\Mapper::getInstance();
		$map = $mapper->getByPrimaryValue($this->tableName, array('ID'), array('ID' => $id));
		if ($map)
		{
			$this->fileHandler->onBeforeDelete($id);
		}
	}

	/**
	 * Forum event onAfterMessageDelete handler.
	 *
	 * @param integer $id Forum message identifier.
	 * @param array $message Message record before delete.
	 *
	 * @return void
	 * @see \CForumMessage::Delete()
	 */
	public function onAfterMessageDelete($id, $message)
	{
		$this->fileHandler->onAfterDelete($id);
		$op = \Thurly\Replica\Db\Operation::writeDelete($this->tableName, array('ID'), array('ID' => $id));
	}

	/**
	 * Method will be invoked after writing an missed record.
	 *
	 * @param array $record All fields of the record.
	 *
	 * @return void
	 */
	public function afterWriteMissing(array $record)
	{
		//AddMessage2Log($record);
		$mapper = \Thurly\Replica\Mapper::getInstance();
		$map = $mapper->getByPrimaryValue($this->tableName, array('ID'), array('ID' => $record["ID"]));
		if ($map)
		{
			$this->fileHandler->onAfterAdd($record["ID"], $record);
		}
	}

	/**
	 * Method will be invoked before new database record inserted.
	 * When an array returned the insert will be cancelled and map for
	 * returned record will be added.
	 *
	 * @param array &$newRecord All fields of inserted record.
	 *
	 * @return null|array
	 */
	public function beforeInsertTrigger(array &$newRecord)
	{
		if ($this->tasksForumId > 0 && $newRecord['FORUM_ID'] === 'tasks_forum')
		{
			$mapper = \Thurly\Replica\Mapper::getInstance();
			$newRecord['FORUM_ID'] = $this->tasksForumId;

			if (preg_match("/^TASK_(.+)\$/", $newRecord["XML_ID"], $match))
			{
				$taskId = $mapper->resolveLogGuid(false, 'b_tasks.ID', $match[1]);
				if ($taskId)
				{
					$newRecord['XML_ID'] = 'TASK_'.$taskId;
					$newRecord['PARAM2'] = $taskId;
				}
			}

			if ($newRecord["PARAM1"] === "TK" && $newRecord["PARAM2"])
			{
				$taskId = $mapper->resolveLogGuid(false, 'b_tasks.ID', $newRecord["PARAM2"]);
				if ($taskId)
				{
					$newRecord['PARAM2'] = $taskId;
				}
			}

			$this->fileHandler->replaceGuidsWithFiles($newRecord);

			$fixed = preg_replace("/\\[USER=[0-9]+\\](.*?)\\[\\/USER\\]/", "\\1", $newRecord["POST_MESSAGE"]);
			if ($fixed != null)
			{
				$newRecord["POST_MESSAGE"] = $fixed;
			}

			$fixed = preg_replace("/\\[USER=[0-9]+\\](.*?)\\[\\/USER\\]/", "\\1", $newRecord["POST_MESSAGE_HTML"]);
			if ($fixed != null)
			{
				$newRecord["POST_MESSAGE_HTML"] = $fixed;
			}
		}
		return null;
	}

	/**
	 * Method will be invoked after new database record inserted.
	 *
	 * @param array $newRecord All fields of inserted record.
	 *
	 * @return void
	 */
	public function afterInsertTrigger(array $newRecord)
	{
		if (
			$this->tasksForumId > 0
			&& $newRecord['FORUM_ID'] == $this->tasksForumId
			&& $newRecord['PARAM1'] !== 'TK'
			&& $newRecord['PARAM2'] > 0
		)
		{
			$taskId = $newRecord['PARAM2'];
			if ($taskId > 0)
			{
				\Thurly\Tasks\Integration\Forum\Task\Comment::onAfterAdd(
					'TK',
					$taskId,
					array(
						"replica" => true, //will suppress ::fireEvent('Add',
						"TOPIC_ID" => $newRecord["TOPIC_ID"],
						"MESSAGE_ID" => $newRecord["ID"],
						"PARAMS" => array(
							"POST_MESSAGE" => $newRecord["POST_MESSAGE"],
							"AUTHOR_ID" => $newRecord["AUTHOR_ID"],
							"AUTHOR_NAME" => $newRecord["AUTHOR_NAME"],
							"AUTHOR_EMAIL" => $newRecord["AUTHOR_EMAIL"],
							"USE_SMILES" => $newRecord["USE_SMILES"],
							//TODO "FILES" => $params["FILES"]
							"AUTHOR_IP" => $newRecord["AUTHOR_IP"],
							"AUTHOR_REAL_IP" => $newRecord["AUTHOR_REAL_IP"],
							"GUEST_ID" => $newRecord["GUEST_ID"],
						),
						"MESSAGE" => $newRecord,
					)
				);
			}
		}
	}

	/**
	 * Method will be invoked after an database record updated.
	 *
	 * @param array $oldRecord All fields before update.
	 * @param array $newRecord All fields after update.
	 *
	 * @return void
	 */
	public function afterUpdateTrigger(array $oldRecord, array $newRecord)
	{
		if (
			$this->tasksForumId > 0
			&& $newRecord['FORUM_ID'] == 'tasks_forum'
			&& $newRecord['PARAM1'] !== 'TK'
		)
		{
			$taskId = 0;
			if (preg_match("/^TASK_(.+)\$/", $newRecord["XML_ID"], $match))
			{
				$mapper = \Thurly\Replica\Mapper::getInstance();
				$taskId = $mapper->resolveLogGuid(false, 'b_tasks.ID', $match[1]);
			}

			if ($taskId > 0)
			{
				\Thurly\Tasks\Integration\Forum\Task\Comment::onAfterUpdate(
					'TK',
					$taskId,
					array(
						"ACTION" => "EDIT",
						"replica" => true, //will suppress ::fireEvent('Add',
						"TOPIC_ID" => $newRecord["TOPIC_ID"],
						"MESSAGE_ID" => $newRecord["ID"],
						"PARAMS" => array(
							"POST_MESSAGE" => $newRecord["POST_MESSAGE"],
							"AUTHOR_ID" => $newRecord["AUTHOR_ID"],
							"AUTHOR_NAME" => $newRecord["AUTHOR_NAME"],
							"AUTHOR_EMAIL" => $newRecord["AUTHOR_EMAIL"],
							"USE_SMILES" => $newRecord["USE_SMILES"],
							//TODO "FILES" => $params["FILES"]
							"AUTHOR_IP" => $newRecord["AUTHOR_IP"],
							"AUTHOR_REAL_IP" => $newRecord["AUTHOR_REAL_IP"],
							"GUEST_ID" => $newRecord["GUEST_ID"],
						),
						"MESSAGE" => $newRecord,
					)
				);
			}
		}
	}

	/**
	 * Method will be invoked after an database record deleted.
	 *
	 * @param array $oldRecord All fields before delete.
	 *
	 * @return void
	 */
	public function afterDeleteTrigger(array $oldRecord)
	{
		if (
			$this->tasksForumId > 0
			&& $oldRecord['FORUM_ID'] == $this->tasksForumId
		)
		{
			$taskId = 0;
			if (preg_match("/^TASK_(.+)\$/", $oldRecord["XML_ID"], $match))
			{
				$taskId = intval($match[1]);
			}

			if ($taskId > 0)
			{
				\Thurly\Tasks\Integration\Forum\Task\Comment::onAfterUpdate(
					'TK',
					$taskId,
					array(
						"ACTION" => "DEL",
						"replica" => true, //will suppress ::fireEvent('Add',
						"TOPIC_ID" => $oldRecord["TOPIC_ID"],
						"MESSAGE_ID" => $oldRecord["ID"],
						"MESSAGE" => $oldRecord,
					)
				);
			}
		}
	}

	/**
	 * Method will be invoked before an database record updated.
	 *
	 * @param array $oldRecord All fields before update.
	 * @param array &$newRecord All fields after update.
	 *
	 * @return void
	 */
	public function beforeUpdateTrigger(array $oldRecord, array &$newRecord)
	{
		if ($this->tasksForumId > 0 && $newRecord['FORUM_ID'] === 'tasks_forum')
		{
			$newRecord['FORUM_ID'] = $this->tasksForumId;
			if (preg_match("/^TASK_(.+)\$/", $newRecord["XML_ID"], $match))
			{
				$mapper = \Thurly\Replica\Mapper::getInstance();
				$taskId = $mapper->resolveLogGuid(false, 'b_tasks.ID', $match[1]);
				if ($taskId)
				{
					$newRecord['XML_ID'] = 'TASK_'.$taskId;
					$newRecord['PARAM2'] = $taskId;
				}
			}

			$fixed = preg_replace("/\\[USER=[0-9]+\\](.*?)\\[\\/USER\\]/", "\\1", $newRecord["POST_MESSAGE"]);
			if ($fixed != null)
			{
				$newRecord["POST_MESSAGE"] = $fixed;
			}

			$fixed = preg_replace("/\\[USER=[0-9]+\\](.*?)\\[\\/USER\\]/", "\\1", $newRecord["POST_MESSAGE_HTML"]);
			if ($fixed != null)
			{
				$newRecord["POST_MESSAGE_HTML"] = $fixed;
			}

			$this->fileHandler->replaceGuidsWithFiles($newRecord);
		}
	}

	/**
	 * Called before insert operation log write. You may return false and not log write will take place.
	 *
	 * @param array $record Database record.
	 *
	 * @return boolean
	 */
	public function beforeLogInsert(array $record)
	{
		if ($this->tasksForumId > 0 && $record['FORUM_ID'] == $this->tasksForumId)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Called before record transformed for log writing.
	 *
	 * @param array &$record Database record.
	 *
	 * @return void
	 */
	public function beforeLogFormat(array &$record)
	{
		if ($this->tasksForumId > 0 && $record['FORUM_ID'] == $this->tasksForumId)
		{
			$mapper = \Thurly\Replica\Mapper::getInstance();
			$record['FORUM_ID'] = 'tasks_forum';

			if (preg_match("/^TASK_([0-9]+)\$/", $record["XML_ID"], $match))
			{
				$guid = $mapper->getLogGuid("b_tasks.ID", $match[1]);
				if ($guid)
					$record['XML_ID'] = 'TASK_'.$guid;
			}

			if ($record["PARAM1"] == "TK" && $record["PARAM2"] > 0)
			{
				$guid = $mapper->getLogGuid("b_tasks.ID", $record["PARAM2"]);
				if ($guid)
					$record["PARAM2"] = $guid;
			}

			$this->fileHandler->replaceFilesWithGuids($record["ID"], $record);
		}
	}
}
