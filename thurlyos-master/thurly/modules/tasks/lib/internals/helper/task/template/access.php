<?
namespace Thurly\Tasks\Internals\Helper\Task\Template;

use Thurly\Main\Entity;
//use Thurly\Main\DB\SqlExpression;
use Thurly\Main\TaskOperationTable;

use Thurly\Tasks\Internals\RunTime;
use Thurly\Tasks\Internals\Task\Template\AccessTable;
use Thurly\Tasks\Util\User;

final class Access
{
	/**
	 * Get list of available operations for templates specified by $ids under user specified by $parameters['USER_ID']
	 *
	 * @see \Thurly\Tasks\Internals\RunTime\Task\Template::getAccessCheck()
	 * @see \Thurly\Tasks\Item\Context\Access\Task\Template
	 *
	 * @param $ids
	 * @param array $parameters
	 * @return array
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function getAvailableOperations($ids, array $parameters = array())
	{
		// update b_user_access for the chosen user
		$acc = new \CAccess();
		$acc->updateCodes(array(
			'USER_ID' => $parameters['USER_ID'],
		));

		$res = AccessTable::getList(array(
			'runtime' => array(
				new Entity\ReferenceField(
					'T2OP',
					TaskOperationTable::getEntity(),
					array(
						'=this.TASK_ID' => 'ref.TASK_ID',
					),
					array('join_type' => 'inner')
				)
			),
			'filter' => array(
				// todo: we will need to join b_user_access here when making access via smth else than user
				'=GROUP_CODE' => 'U'.intval($parameters['USER_ID']),
				'=ENTITY_ID' => $ids,
			),
			'select' => array(
				'ENTITY_ID', 'OP_ID' => 'T2OP.OPERATION_ID'
			)
		));

		$result = array();
		while($item = $res->fetch())
		{
			$result[$item['ENTITY_ID']][] = $item['OP_ID'];
		}

		return $result;
	}
}