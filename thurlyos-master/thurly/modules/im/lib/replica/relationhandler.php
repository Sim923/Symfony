<?php
namespace Thurly\Im\Replica;

class RelationHandler extends \Thurly\Replica\Client\BaseHandler
{
	protected $tableName = "b_im_relation";
	protected $moduleId = "im";
	protected $className = "\\Thurly\\Im\\Model\\RelationTable";
	protected $primary = array(
		"ID" => "auto_increment",
	);
	protected $predicates = array(
		"CHAT_ID" => "b_im_chat.ID",
		"USER_ID" => "b_user.ID",
	);
	protected $translation = array(
		"ID" => "b_im_relation.ID",
		"CHAT_ID" => "b_im_chat.ID",
		"USER_ID" => "b_user.ID",
		"START_ID" => "b_im_message.ID",
		"LAST_ID" => "b_im_message.ID",
		"LAST_SEND_ID" => "b_im_message.ID",
	);
	protected $fields = array(
		"LAST_READ" => "datetime",
	);

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
		if (
			isset($newRecord["MESSAGE_TYPE"])
			&& $newRecord["MESSAGE_TYPE"] === "S"
		)
		{
			$chatList = \Thurly\Im\Model\RelationTable::getList(array(
				"filter" => array(
					"=USER_ID" => $newRecord["USER_ID"],
					"=CHAT_ID" => $newRecord["CHAT_ID"],
					"=MESSAGE_TYPE" => "S",
				),
			));
			$oldRecord = $chatList->fetch();
			if ($oldRecord)
			{
				return $oldRecord;
			}
		}
		return null;
	}

	/**
	 * Called before update operation log write. You may return false and not log write will take place.
	 *
	 * @param array $record Database record.
	 *
	 * @return boolean
	 */
	public function beforeLogUpdate(array $record)
	{
		if ($record["MESSAGE_TYPE"] === "S")
			return false;
		else
			return true;
	}

	/**
	 * Called before log write. You may return false and not log write will take place.
	 *
	 * @param array $record Database record.
	 * @return boolean
	 */
	public function beforeLogInsert(array $record)
	{
		if (\Thurly\Im\User::getInstance($record["USER_ID"])->isBot())
		{
			return false;
		}
		else
		{
			return true;
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
			$newRecord["MESSAGE_TYPE"] === "P"
			&& intval($oldRecord["LAST_ID"]) < intval($newRecord["LAST_ID"])
		)
		{
			$oldLastRead = $oldRecord["LAST_READ"] instanceof \Thurly\Main\Type\DateTime? $oldRecord["LAST_READ"]: false;
			$newLastRead = $newRecord["LAST_READ"] instanceof \Thurly\Main\Type\DateTime? $newRecord["LAST_READ"]: false;
			if ($oldLastRead < $newLastRead)
			{
				if (\Thurly\Main\Loader::includeModule('pull'))
				{
					$relationList = \Thurly\IM\Model\RelationTable::getList(array(
						"select" => array("ID", "USER_ID"),
						"filter" => array(
							"=CHAT_ID" => $newRecord["CHAT_ID"],
							"!=USER_ID" => $newRecord["USER_ID"],
						),
					));
					if ($relation = $relationList->fetch())
					{
						\Thurly\Pull\Event::add($relation['USER_ID'], Array(
							'module_id' => 'im',
							'command' => 'readMessageOpponent',
							'expiry' => 3600,
							'params' => Array(
								'dialogId' => intval($newRecord['USER_ID']),
								'chatId' => intval($newRecord['CHAT_ID']),
								'userId' => intval($newRecord['USER_ID']),
								'chatMessageStatus' => ''
							),
							'extra' => Array(
								'im_revision' => IM_REVISION,
								'im_revision_mobile' => IM_REVISION_MOBILE,
							),
						));
					}
				}
			}
		}
	}
}