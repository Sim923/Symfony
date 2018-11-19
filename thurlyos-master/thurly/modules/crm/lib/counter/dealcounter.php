<?php
namespace Thurly\Crm\Counter;
use Thurly\Crm\ActivityBindingTable;
use Thurly\Main;
use Thurly\Main\Type\DateTime;
use Thurly\Main\DB\SqlExpression;
use Thurly\Main\Entity\Query;
use Thurly\Main\Entity\ExpressionField;
use Thurly\Main\Entity\ReferenceField;

use Thurly\Crm\DealTable;
use Thurly\Crm\ActivityTable;
use Thurly\Crm\UserActivityTable;
use Thurly\Crm\Pseudoactivity\Entity\WaitTable;

class DealCounter extends EntityCounter
{
	/**
	 * @param int $typeID Type ID (see EntityCounterType).
	 * @param int $entityTypeID Entity Type ID (see \CCrmOwnerType).
	 * @param int $userID User ID.
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function __construct($typeID, $userID = 0, array $params = null)
	{
		parent::__construct(\CCrmOwnerType::Deal, $typeID, $userID, $params);
	}
	/**
	 * Get details page URL.
	 * @param string $url Base URL.
	 * @return string
	 */
	public function prepareDetailsPageUrl($url = '')
	{
		$urlParams = array('counter' => strtolower($this->getTypeName()), 'clear_nav' => 'Y');

		//We may ignore DEAL_CATEGORY_ID parameter - it will be supplied by the crm.deal.list component.
		self::externalizeExtras(array_diff_key($this->extras, array('DEAL_CATEGORY_ID' => true)), $urlParams);

		if($url === '')
		{
			$url = self::getEntityListPath();
		}
		return \CHTTP::urlAddParams($url, $urlParams);
	}
	/**
	 * Prepare queries
	 * @param array|null $options
	 * @return Query[]
	 */
	protected function prepareQueries(array $options = null)
	{
		if(!is_array($options))
		{
			$options = array();
		}

		if(is_array($options) && isset($options['USER_IDS']) && is_array($options['USER_IDS']))
		{
			$userID = $options['USER_IDS'];
		}
		else
		{
			$userID = array($this->userID);
		}

		$select = isset($options['SELECT']) ? $options['SELECT'] : '';
		if($select !== 'QTY' && $select !== 'ENTY')
		{
			$select = 'QTY';
		}

		$distinct = true;
		if($select === 'ENTY' && isset($options['DISTINCT']))
		{
			$distinct = $options['DISTINCT'];
		}

		$results = array();
		$categoryID = $this->getIntegerExtraParam('DEAL_CATEGORY_ID', -1);
		$typeIDs = EntityCounterType::splitType($this->typeID);

		foreach($typeIDs as $typeID)
		{
			//echo EntityCounterType::resolveName($typeID), "<br>";
			if($typeID === EntityCounterType::IDLE)
			{
				if(!\CCrmUserCounterSettings::GetValue(\CCrmUserCounterSettings::ReckonActivitylessItems, true))
				{
					continue;
				}

				$query = new Query(DealTable::getEntity());

				if($select === 'ENTY')
				{
					$query->addSelect('ID', 'ENTY');
				}
				else
				{
					$query->registerRuntimeField('', new ExpressionField('QTY', 'COUNT(%s)', 'ID'));
					$query->addSelect('QTY');
				}

				$query->registerRuntimeField(
					'',
					new ReferenceField('UA',
						UserActivityTable::getEntity(),
						array(
							'=ref.OWNER_ID' => 'this.ID',
							'=ref.OWNER_TYPE_ID' => new SqlExpression(\CCrmOwnerType::Deal),
							'=ref.USER_ID' => new SqlExpression(0)
						),
						array('join_type' => 'LEFT')
					)
				);

				$query->registerRuntimeField(
					'',
					new ReferenceField('W',
						WaitTable::getEntity(),
						array(
							'=ref.OWNER_ID' => 'this.ID',
							'=ref.OWNER_TYPE_ID' => new SqlExpression(\CCrmOwnerType::Deal),
							'=ref.COMPLETED' => new SqlExpression('?s', 'N')
						),
						array('join_type' => 'LEFT')
					)
				);

				if(is_array($userID))
				{
					$userCount = count($userID);
					if($userCount > 1)
					{
						$query->addFilter('@ASSIGNED_BY_ID', $userID);
					}
					elseif($userCount === 1)
					{
						$query->addFilter('=ASSIGNED_BY_ID', $userID[0]);
					}
				}
				elseif($userID > 0)
				{
					//Strongly required for counter design. We manage counters in user-oriented manner.
					$query->addFilter('=ASSIGNED_BY_ID', $userID);
				}

				if($categoryID >= 0)
				{
					//HACK: use SqlExpression to avoid filter sql like (CATEGORY_ID IS NULL OR CATEGORY_ID = 0), that cause the filesort.
					$query->addFilter('=CATEGORY_ID', new SqlExpression('?i', $categoryID));
				}

				$query->addFilter('=STAGE_SEMANTIC_ID', 'P');
				$query->addFilter('=IS_RECURRING', 'N');
				$query->addFilter('==UA.OWNER_ID', null);
				$query->addFilter('==W.OWNER_ID', null);

				//echo '<pre>', $query->getQuery(), "</pre>";
				$results[] = $query;
			}
			else if($typeID === EntityCounterType::PENDING || $typeID === EntityCounterType::OVERDUE)
			{
				$query = new Query(DealTable::getEntity());

				if($categoryID >= 0)
				{
					//HACK: use SqlExpression to avoid filter sql like (CATEGORY_ID IS NULL OR CATEGORY_ID = 0), that cause the filesort.
					$query->addFilter('=CATEGORY_ID', new SqlExpression('?i', $categoryID));
				}

				if($this->userID > 0)
				{
					//Strongly required for counter design. We manage counters in user-oriented manner.
					$query->addFilter('=ASSIGNED_BY_ID', $this->userID);
				}

				$query->registerRuntimeField(
					'',
					new ReferenceField('B',
						ActivityBindingTable::getEntity(),
						array(
							'=ref.OWNER_ID' => 'this.ID',
							'=ref.OWNER_TYPE_ID' => new SqlExpression(\CCrmOwnerType::Deal)
						),
						array('join_type' => 'INNER')
					)
				);

				$query->registerRuntimeField(
					'',
					new ReferenceField('A',
						ActivityTable::getEntity(),
						array('=ref.ID' => 'this.B.ACTIVITY_ID'),
						array('join_type' => 'INNER')
					)
				);

				//Replace by IS_HANDLEABLE for ignore pseudo activities (like WAIT)
				//$query->addFilter('=A.IS_HANDLEABLE', 'Y');
				$query->addFilter('=A.COMPLETED', 'N');

				if(is_array($userID))
				{
					$userCount = count($userID);
					if($userCount > 1)
					{
						$query->addFilter('@ASSIGNED_BY_ID', $userID);
					}
					elseif($userCount === 1)
					{
						$query->addFilter('=ASSIGNED_BY_ID', $userID[0]);
					}
				}
				elseif($userID > 0)
				{
					//Strongly required for counter design. We manage counters in user-oriented manner.
					$query->addFilter('=ASSIGNED_BY_ID', $userID);
				}

				if($typeID === EntityCounterType::PENDING)
				{
					$lowBound = new DateTime();
					$lowBound->setTime(0, 0, 0);
					$query->addFilter('>=A.DEADLINE', $lowBound);

					$highBound = new DateTime();
					$highBound->setTime(23, 59, 59);
					$query->addFilter('<=A.DEADLINE', $highBound);
				}
				elseif($typeID === EntityCounterType::OVERDUE)
				{
					$highBound = new DateTime();
					$highBound->setTime(0, 0, 0);
					$query->addFilter('<A.DEADLINE', $highBound);
				}

				if($select === 'ENTY')
				{
					$query->addSelect('B.OWNER_ID', 'ENTY');
					if($distinct)
					{
						$query->addGroup('B.OWNER_ID');
					}
				}
				else
				{
					$query->registerRuntimeField('', new ExpressionField('QTY', 'COUNT(DISTINCT %s)', 'ID'));
					$query->addSelect('QTY');
				}

				$query->addFilter('=STAGE_SEMANTIC_ID', 'P');
				$query->addFilter('=IS_RECURRING', 'N');
				//echo '<pre>', $query->getQuery(), "</pre>";
				$results[] = $query;
			}
			else
			{
				$typeName = EntityCounterType::resolveName($typeID);
				throw new Main\NotSupportedException("The '{$typeName}' is not supported in current context");
			}
		}
		return $results;
	}
}