<?php
namespace Thurly\Crm\Agent\Timeline;

use Thurly\Main;
use Thurly\Crm\Timeline\LeadController;

class LeadTimelineBuildAgent extends EntityTimelineBuildAgent
{
	const ITERATION_LIMIT = 500;
	/** @var LeadTimelineBuildAgent|null */
	private static $instance = null;
	/**
	 * @return LeadTimelineBuildAgent|null
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new LeadTimelineBuildAgent();
		}
		return self::$instance;
	}
	public static function activate()
	{
		\CAgent::AddAgent(
			__CLASS__.'::run();',
			'crm',
			'Y',
			2,
			'',
			'Y',
			ConvertTimeStamp(time() + \CTimeZone::GetOffset(), 'FULL')
		);
	}
	//region EntityTimelineBuildAgent
	public function build(array $itemIDs)
	{
		$itemIDs = $this->filterEntityIDs($itemIDs, \CCrmOwnerType::Lead);
		if(empty($itemIDs))
		{
			return;
		}

		$options = array('EXISTS_CHECK' => false);
		foreach($itemIDs as $itemID)
		{
			LeadController::getInstance()->register($itemID, $options);
		}
	}
	protected function getOptionName()
	{
		return '~CRM_BUILD_LEAD_TIMELINE';
	}
	protected function getProgressOptionName()
	{
		return '~CRM_BUILD_LEAD_TIMELINE_PROGRESS';
	}
	protected function getTotalEntityCount()
	{
		return \CCrmLead::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}
	protected function getEnityIDs($offsetID, $limit)
	{
		$filter = array('CHECK_PERMISSIONS' => 'N');
		if($offsetID > 0)
		{
			$filter['>ID'] = $offsetID;
		}

		$dbResult = \CCrmLead::GetListEx(
			array('ID' => 'ASC'),
			$filter,
			false,
			array('nTopCount' => $limit),
			array('ID')
		);

		$results = array();

		if(is_object($dbResult))
		{
			while($fields = $dbResult->Fetch())
			{
				$results[] = (int)$fields['ID'];
			}
		}
		return $results;
	}
	protected function getIterationLimit()
	{
		return self::ITERATION_LIMIT;
	}
	//endregion
}