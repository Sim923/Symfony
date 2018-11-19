<?php
namespace Thurly\Crm\Widget\Data\Company;

use Thurly\Crm\Statistics\Entity\CompanyGrowthStatisticsTable;
use Thurly\Crm\Widget\Data\DataContext;
use Thurly\Main;
use Thurly\Main\DB\SqlExpression;
use Thurly\Main\Entity\Query;
use Thurly\Main\Entity\ExpressionField;

use Thurly\Crm\Widget\Filter;

class GrowthStatistics extends DataSource
{
	const TYPE_NAME = 'COMPANY_GROWTH_STATS';
	const GROUP_BY_USER = 'USER';
	const GROUP_BY_DATE = 'DATE';
	private static $messagesLoaded = false;

	protected static $entityListPath = null;

	/**
	 * @return string
	 */
	public function getTypeName()
	{
		return self::TYPE_NAME;
	}

	/**
	 * @param array $params
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\InvalidOperationException
	 * @throws Main\ObjectNotFoundException
	 */
	public function getList(array $params)
	{
		/** @var Filter $filter */
		$filter = isset($params['filter']) ? $params['filter'] : null;
		if(!($filter instanceof Filter))
		{
			throw new Main\ObjectNotFoundException("The 'filter' is not found in params.");
		}

		$group = isset($params['group']) ? strtoupper($params['group']) : '';
		if($group !== '' && $group !== self::GROUP_BY_USER && $group !== self::GROUP_BY_DATE)
		{
			$group = '';
		}

		//only TOTAL_COUNT and aggregate by COUNT supported yet.
		$name = 'TOTAL_COUNT';

		$permissionSql = '';
		if($this->enablePermissionCheck)
		{
			$permissionSql = $this->preparePermissionSql();
			if($permissionSql === false)
			{
				//Access denied;
				return array();
			}
		}

		$period = $filter->getPeriod();
		$periodStartDate = $period['START'];
		$periodEndDate = $period['END'];

		$query = new Query(CompanyGrowthStatisticsTable::getEntity());

		$query->registerRuntimeField('', new ExpressionField($name, "COUNT(*)"));
		$query->addSelect($name);

		$query->addFilter('>=CREATED_DATE', $periodStartDate);
		$query->addFilter('<=CREATED_DATE', $periodEndDate);

		if($this->enablePermissionCheck && is_string($permissionSql) && $permissionSql !== '')
		{
			$query->addFilter('@OWNER_ID', new SqlExpression($permissionSql));
		}

		$responsibleIDs = $filter->getResponsibleIDs();
		if(!empty($responsibleIDs))
		{
			$query->addFilter('@RESPONSIBLE_ID', $responsibleIDs);
		}

		$cntQuery = new Query(CompanyGrowthStatisticsTable::getEntity());
		$cntQuery->registerRuntimeField('', new ExpressionField($name, "COUNT(*)"));
		$cntQuery->addSelect($name);
		$cntQuery->addFilter('<CREATED_DATE', $periodStartDate);

		if($this->enablePermissionCheck && is_string($permissionSql) && $permissionSql !== '')
		{
			$cntQuery->addFilter('@OWNER_ID', new SqlExpression($permissionSql));
		}

		$responsibleIDs = $filter->getResponsibleIDs();
		if(!empty($responsibleIDs))
		{
			$cntQuery->addFilter('@RESPONSIBLE_ID', $responsibleIDs);
		}

		$cntValue = 0;
		if ($group !== self::GROUP_BY_USER)
		{
			$cntRow = $cntQuery->exec()->fetch();
			$cntValue = $cntRow[$name];
		}
		else
		{
			$cntQuery->addSelect('RESPONSIBLE_ID');
			$cntQuery->addGroup('RESPONSIBLE_ID');
			$cntResult = $cntQuery->exec();
			$cntResponsibleValue = array();
			while ($cntRow = $cntResult->fetch())
			{
				$cntResponsibleValue[$cntRow['RESPONSIBLE_ID']] = $cntRow[$name];
			}
		}

		$sort = isset($params['sort']) && is_array($params['sort']) && !empty($params['sort']) ? $params['sort'] : null;
		if($sort)
		{
			foreach($sort as $sortItem)
			{
				if(isset($sortItem['name']))
				{
					$sortName = $sortItem['name'];
					$query->addOrder($sortName, isset($sortItem['order']) ? $sortItem['order'] : 'ASC');
				}
			}
		}

		if($group !== '')
		{
			if($group === self::GROUP_BY_USER)
			{
				$query->addSelect('RESPONSIBLE_ID');
				$query->addGroup('RESPONSIBLE_ID');
			}
			else //if($groupBy === self::GROUP_BY_DATE)
			{
				$query->addSelect('CREATED_DATE');
				if(!$sort)
				{
					$query->addOrder('CREATED_DATE', 'ASC');
				}
			}
		}

		$dbResult = $query->exec();
		$result = array();
		if($group === self::GROUP_BY_DATE)
		{
			while($ary = $dbResult->fetch())
			{
				$date = $ary['CREATED_DATE']->format('Y-m-d');
				if (isset($result[$date]))
				{
					++$result[$date][$name];
					continue;
				}

				$ary['DATE'] = $date;
				unset($ary['CREATED_DATE']);

				$ary[$name] += $cntValue;
				$cntValue = $ary[$name];
				$result[$date] = $ary;
			}
			$result = array_values($result);
		}
		elseif($group === self::GROUP_BY_USER)
		{
			$userIDs = array();
			while($ary = $dbResult->fetch())
			{
				$userID = $ary['RESPONSIBLE_ID'] = (int)$ary['RESPONSIBLE_ID'];
				if($userID > 0 && !isset($userIDs[$userID]))
				{
					$userIDs[$userID] = true;
				}

				$result[] = $ary;
			}
			$userNames = self::prepareUserNames(array_keys($userIDs));
			foreach($result as &$item)
			{
				$userID = $item['RESPONSIBLE_ID'];
				$item[$name] += isset($cntResponsibleValue[$userID]) ? $cntResponsibleValue[$userID] : 0;
				$item['USER_ID'] = $userID;
				$item['USER'] = isset($userNames[$userID]) ? $userNames[$userID] : "[{$userID}]";
				unset($item['RESPONSIBLE_ID']);
			}
			unset($item);
		}
		else
		{
			while($ary = $dbResult->fetch())
			{
				$result[] = $ary;
			}
		}

		return $result;
	}
	/**
	 * Get current data context
	 * @return DataContext
	 */
	public function getDataContext()
	{
		return DataContext::ENTITY;
	}
	public function prepareEntityListFilter(array $filterParams)
	{
		$filter = self::internalizeFilter($filterParams);
		$period = $filter->getPeriod();

		//fix date -> datetime
		$periodStart = Main\Type\DateTime::createFromTimestamp($period['START']->getTimestamp());
		$periodStart->setTime(0, 0, 0);
		$periodEnd = Main\Type\DateTime::createFromTimestamp($period['END']->getTimestamp());
		$periodEnd->setTime(23, 59, 59);

		$result = array(
			'>=DATE_CREATE' => $period['START'],
			'<=DATE_CREATE' => $periodEnd,
		);

		$responsibleIDs = $filter->getResponsibleIDs();
		if(!empty($responsibleIDs))
		{
			$result['@ASSIGNED_BY_ID'] = $responsibleIDs;
		}

		return $result;
	}
	/**
	 * Get entity list path.
	 * @static
	 * @return string
	 */
	protected static function getEntityListPath()
	{
		if(self::$entityListPath === null)
		{
			self::$entityListPath = \CComponentEngine::MakePathFromTemplate(
				Main\Config\Option::get('crm', 'path_to_company_list', '/crm/company/list/', false),
				array()
			);
		}
		return self::$entityListPath;
	}
	/**
	 * @return array Array of arrays
	 */
	public static function getPresets()
	{
		self::includeModuleFile();
		return array(
			array(
				'entity' => \CCrmOwnerType::CompanyName,
				'title' => GetMessage('CRM_COMPANY_GROWTH_STAT_PRESET_TOTAL_COUNT'),
				'name' => self::TYPE_NAME.'::TOTAL_COUNT',
				'source' => self::TYPE_NAME,
				'select' => array('name' => 'TOTAL_COUNT', 'aggregate' => 'COUNT'),
				'context' => DataContext::ENTITY
			)
		);
	}

	/**
	 * @return void
	 */
	protected static function includeModuleFile()
	{
		if(self::$messagesLoaded)
		{
			return;
		}

		Main\Localization\Loc::loadMessages(__FILE__);
		self::$messagesLoaded = true;
	}
}