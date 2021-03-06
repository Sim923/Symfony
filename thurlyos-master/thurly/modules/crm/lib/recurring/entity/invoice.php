<?php
namespace Thurly\Crm\Recurring\Entity;

use Thurly\Main,
	Thurly\Main\Type\Date,
	Thurly\Main\Type\DateTime,
	Thurly\Main\Result,
	Thurly\Main\Localization\Loc,
	Thurly\Crm\Requisite\EntityLink,
	Thurly\Crm\InvoiceRecurTable,
	Thurly\Crm\Recurring\DateType,
	Thurly\Crm\Recurring\Calculator,
	Thurly\Crm\Recurring\Manager,
	\Thurly\Crm\EntityRequisite,
	Thurly\Crm\Recurring\Mail;

class Invoice extends Base
{
	const UNSET_DATE_PAY_BEFORE = 0;
	const SET_DATE_PAY_BEFORE = 1;

	public function getList(array $parameters = array())
	{
		return InvoiceRecurTable::getList($parameters);
	}

	/**
	 * Create recurring invoice
	 *
	 * @param array $invoiceFields
	 * @param array $recurParams
	 *
	 * @return Main\Result
	 * @throws \Exception
	 */
	public function createEntity(array $invoiceFields, array $recurParams)
	{
		$result = new Main\Result();
		$newInvoice = new \CCrmInvoice();
		unset($invoiceFields['ID'], $invoiceFields['ACCOUNT_NUMBER']);

		try
		{
			$invoiceFields['DATE_BILL'] = new Date();
			$invoiceFields['RECURRING_ID'] = null;
			$invoiceFields['IS_RECURRING'] = 'Y';
			$recalculate = false;

			$idRecurringInvoice = $newInvoice->Add($invoiceFields, $recalculate, SITE_ID, array('REGISTER_SONET_EVENT' => true, 'UPDATE_SEARCH' => true));

			if (!$idRecurringInvoice)
			{
				$result->addError(new Main\Error(Loc::getMessage("CRM_RECUR_INVOICE_ERROR_ADDITION")));
				return $result;
			}

			$this->linkInvoiceRequisite($invoiceFields, $idRecurringInvoice);
			$recurParams = $this->prepareDates($recurParams);
			$recurParams = $this->prepareActivity($recurParams);

			$recurParams['EMAIL_ID'] = ((int)$recurParams['EMAIL_ID'] > 0) ? (int)$recurParams['EMAIL_ID'] : null;

			if (is_null((int)$recurParams['EMAIL_ID']))
			{
				$recurParams['SEND_BILL'] = 'N';
			}

			$recurParams['INVOICE_ID'] = $idRecurringInvoice;

			$r = InvoiceRecurTable::add($recurParams);

			if ($r->isSuccess())
			{
				Manager::initCheckAgent(Manager::INVOICE);

				$result->setData(
					array(
						"INVOICE_ID" => $idRecurringInvoice,
						"ID" => $r->getId()
					)
				);
			}
			else
			{
				$result->addErrors($r->getErrors());
			}
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		return $result;
	}

	/**
	 * Update recurring invoice
	 *
	 * @param int $primary
	 * @param array $data
	 *
	 * @return Main\Result
	 * @throws \Exception
	 */
	public function update($primary, array $data)
	{
		$result = new Main\Result();
		
		$primary = (int)$primary;
		if ($primary <= 0)
		{
			$result->addError(new Main\Error("Wrong primary ID"));
			return $result;
		}

		$data['NEXT_EXECUTION'] = null;

		$recur = InvoiceRecurTable::getById($primary);
		$recurData = $recur->fetch();

		if (!$recurData)
		{
			$result->addError(new Main\Error("Wrong primary ID"));
			return $result;
		}

		$data = array_merge($recurData, $data);

		$recurringParams = $data['PARAMS'];

		if (is_array($recurringParams))
		{
			$today = new Date();

			if ($data['START_DATE'] instanceof Date)
			{
				$startDay = $today->getTimestamp() > $data['START_DATE']->getTimestamp() ? $today : $data['START_DATE'];
			}
			else
			{
				$startDay = $today;
			}

			if ($data['LAST_EXECUTION'] instanceof Date)
			{
				if ($data['LAST_EXECUTION']->getTimestamp() >= $startDay->getTimestamp())
				{
					$startDay->add('+1 day');
				}
			}

			$data['NEXT_EXECUTION'] = $this->getNextDate($recurringParams, $startDay);
		}

		$data = $this->prepareActivity($data);

		return InvoiceRecurTable::update($primary, $data);
	}

	/**
	 * Create new invoices from recurring invoices. Invoices's selection is from InvoiceRecurTable.
	 *
	 * @param $filter
	 * @param $limit
	 *
	 * @return Main\Result
	 * @throws Main\ArgumentException
	 */
	public function expose(array $filter, $limit = null)
	{
		$result = new Main\Result();

		$idInvoicesList = array();
		$recurParamsList = array();
		$linkEntityList = array();
		$newInvoiceIds = array();
		$emailList = array();
		$emailData = array();

		$getParams['filter'] = $filter;
		if ((int)$limit > 0)
		{
			$getParams['limit'] = (int)$limit;
		}

		$recurring = InvoiceRecurTable::getList($getParams);

		while ($recurData = $recurring->fetch())
		{
			$recurData['INVOICE_ID'] = (int)$recurData['INVOICE_ID'];
			$idInvoicesList[] = $recurData['INVOICE_ID'];
			$recurParamsList[$recurData['INVOICE_ID']] = $recurData;
		}

		if (empty($idInvoicesList))
		{
			return $result;
		}

		try
		{
			$newInvoice = new \CCrmInvoice(false);
			$today = new Date();
			$tomorrow = Date::createFromTimestamp(strtotime('tomorrow'));

			$linkData = EntityLink::getList(
				array(
					'filter' => array(
						'=ENTITY_TYPE_ID' => \CCrmOwnerType::Invoice,
						'=ENTITY_ID' => $idInvoicesList
					)
				)
			);

			while ($link = $linkData->fetch())
			{
				$linkEntityList[$link['ENTITY_ID']] = $link;
			}
			
			$idListChunks = array_chunk($idInvoicesList, 999);

			foreach ($idListChunks as $idList)
			{
				$products = array();
				$properties = array();

				$productRowData = \CCrmInvoice::GetProductRows($idList);

				foreach ($productRowData as $row)
				{
					if ($row['CUSTOM_PRICE'] === 'Y')
						$row['CUSTOMIZED'] = 'Y';

					$row['ID'] = 0;

					$products[$row['ORDER_ID']][] = $row;
				}

				$propertiesRowData = \CCrmInvoice::getPropertiesList($idList);

				foreach ($propertiesRowData as $invoiceId => $row)
				{
					$properties[$invoiceId] = $row;
				}

				unset($row);

				$invoicesData = \CCrmInvoice::GetList(
					array(),
					array(
						"=ID" => $idList,
						"CHECK_PERMISSIONS" => 'N'
					)
				);

				while ($invoice = $invoicesData->Fetch())
				{
					$recurData = $recurParamsList[$invoice['ID']];
					$invoice['RECURRING_ID'] = $invoice['ID'];
					$invoice['IS_RECURRING'] = 'N';
					$invoice['PRODUCT_ROWS'] = $products[$invoice['ID']];
					$newInvoiceProperties = array();

					if(is_array($properties[$invoice['ID']]))
					{
						foreach($properties[$invoice['ID']] as $invoiceProperty)
						{
							$value = $invoiceProperty['VALUE'];
							$newInvoiceProperties[$value['ORDER_PROPS_ID']] = $value['VALUE'];
						}
						$invoice['INVOICE_PROPERTIES'] = $newInvoiceProperties;
					}

					$recurParam = $recurData['PARAMS'];

					$invoice['DATE_BILL'] = $today;
					$invoiceTemplateId = $invoice['ID'];

					unset(
						$invoice['ID'], $invoice['ACCOUNT_NUMBER'], $invoice['DATE_STATUS'],
						$invoice['DATE_UPDATE'], $invoice['DATE_INSERT'], $invoice['DATE_PAY_BEFORE'],
						$invoice['PAY_VOUCHER_NUM'], $invoice['PAY_VOUCHER_DATE'],
						$invoice['REASON_MARKED_SUCCESS'], $invoice['DATE_MARKED'], $invoice['REASON_MARKED']
					);

					if (!empty($recurParam['DATE_PAY_BEFORE_TYPE']) && $recurParam['DATE_PAY_BEFORE_TYPE'] !== self::UNSET_DATE_PAY_BEFORE)
					{
						$datePayBefore = $this->getDatePayBefore($recurParam);
						if ($datePayBefore instanceof Date)
							$invoice['DATE_PAY_BEFORE'] = $datePayBefore;
					}

					$reCalculate = false;
					$resultInvoiceId = $newInvoice->Add($invoice, $reCalculate, $invoice['LID']);

					if ($resultInvoiceId)
					{
						$requisiteInvoice = $linkEntityList[$invoiceTemplateId];

						EntityLink::register(
							\CCrmOwnerType::Invoice,
							$resultInvoiceId,
							$requisiteInvoice['REQUISITE_ID'],
							$requisiteInvoice['BANK_DETAIL_ID'],
							$requisiteInvoice['MC_REQUISITE_ID'],
							$requisiteInvoice['MC_BANK_DETAIL_ID']
						);
						
						$newInvoiceIds[] = $resultInvoiceId;

						$nextData = $this->getNextDate($recurParam, $tomorrow);

						$recurData["LAST_EXECUTION"] = $today;
						$recurData["COUNTER_REPEAT"] = (int)$recurData['COUNTER_REPEAT'] + 1;
						$recurData["NEXT_EXECUTION"] = $nextData;

						$recurData = $this->prepareActivity($recurData);

						$updateData = array(
							"LAST_EXECUTION" => $recurData["LAST_EXECUTION"],
							"COUNTER_REPEAT" => $recurData["COUNTER_REPEAT"],
							"NEXT_EXECUTION" => $recurData["NEXT_EXECUTION"],
							"ACTIVE" => $recurData["ACTIVE"]
						);

						if ($recurData['SEND_BILL'] === 'Y' && (int)$recurData['EMAIL_ID'] > 0)
						{
							$emailList[] = (int)$recurData['EMAIL_ID'];
							$emailData[$resultInvoiceId] = array(
								'EMAIL_ID' => (int)$recurData['EMAIL_ID'],
								'TEMPLATE_ID' => (int)$recurParam['EMAIL_TEMPLATE_ID'] ? (int)$recurParam['EMAIL_TEMPLATE_ID'] : null,
								'INVOICE_ID' => $resultInvoiceId
							);
						}

						InvoiceRecurTable::update($recurData['ID'], $updateData);
					}
					else
					{
						$result->addError(new Main\Error(Loc::getMessage("CRM_RECUR_INVOICE_ERROR_GET_EMPTY")));
					}
				}
			}

			unset($idListChunks, $idList);

			if (!empty($emailList))
			{
				$result = $this->sendByMail($emailList, $emailData);
			}
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage(), $exception->getCode()));
		}

		if (!empty($newInvoiceIds))
		{
			$result->setData(array("ID" => $newInvoiceIds));
		}

		return $result;
	}

	/**
	 * @param $invoiceId
	 *
	 * @return Main\Entity\UpdateResult
	 * @throws Main\ArgumentException
	 */
	public function deactivate($invoiceId)
	{
		if ((int)$invoiceId > 0)
		{
			$invoiceId = (int)$invoiceId;
		}
		else
		{
			throw new Main\ArgumentException('Wrong invoice id.');
		}

		return InvoiceRecurTable::update(
			$invoiceId,
			array(
				"ACTIVE" => 'N',
				"NEXT_EXECUTION" => null
			)
		);
	}

	/**
	 * Activate recurring invoices
	 *
	 * @param $invoiceId
	 *
	 * @return Main\Entity\UpdateResult|Result
	 * @throws Main\ArgumentException
	 * @throws \Exception
	 */
	public function activate($invoiceId)
	{
		$result = new Result();

		if ((int)$invoiceId > 0)
		{
			$invoiceId = (int)$invoiceId;
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage('CRM_RECUR_WRONG_ID')));
			return $result;
		}

		$invoiceData = InvoiceRecurTable::getList(
			array(
				"filter" => array("INVOICE_ID" => $invoiceId)
			)
		);
		if ($invoice = $invoiceData->fetch())
		{
			$recurringParams = $invoice['PARAMS'];
			$invoice['NEXT_EXECUTION'] = $this->getNextDate($recurringParams);
			$invoice["COUNTER_REPEAT"] = (int)$invoice["COUNTER_REPEAT"] + 1;
			$isActive = $this->isActive($invoice);
			if ($isActive)
			{
				$result = InvoiceRecurTable::update(
					$invoiceId,
					array(
						"ACTIVE" => 'Y',
						"NEXT_EXECUTION" => $invoice['NEXT_EXECUTION'],
						"COUNTER_REPEAT" => $invoice['COUNTER_REPEAT']
					)
				);
			}
			else
			{
				if ((int)$invoice['COUNTER_REPEAT'] > (int)$invoice['LIMIT_REPEAT'])
				{
					$result->addError(new Main\Error(Loc::getMessage('CRM_RECUR_ACTIVATE_LIMIT_REPEAT')));
				}
				else
				{
					$result->addError(new Main\Error(Loc::getMessage('CRM_RECUR_ACTIVATE_LIMIT_DATA')));
				}
			}
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage('CRM_RECUR_WRONG_ID')));
		}
		return $result;
	}

	/**
	 * @param $invoiceId
	 * @param string $reason
	 *
	 * @throws Main\ArgumentException
	 * @throws \Exception
	 */
	public function cancel($invoiceId, $reason = "")
	{
		$invoiceId = (int)$invoiceId;
		if ($invoiceId <= 0)
		{
			throw new Main\ArgumentException('Wrong invoice id.');
		}

		$invoice =  new \CCrmInvoice();
		$invoice->Update(
			$invoiceId,
			array(
				"CANCELED" => "Y",
				"DATE_CANCELED" => new DateTime(),
				"REASON_CANCELED" => $reason
			)
		);

		$recurringData = InvoiceRecurTable::getList(
			array(
				"filter" => array("=INVOICE_ID" => $invoiceId)
			)
		);

		while ($recurring = $recurringData->fetch())
		{
			$this->update(
				$recurring['ID'],
				array(
					"ACTIVE" => "N",
					"NEXT_EXECUTION" => null
				)
			);
		}
	}

	/**
	 * @param array $invoiceFields
	 * @param $idRecurringInvoice
	 *
	 * @return void
	 * @throws Main\ArgumentException
	 */
	private function linkInvoiceRequisite(array $invoiceFields, $idRecurringInvoice)
	{
		$requisite = new EntityRequisite();

		$requisiteEntityList = array();
		$mcRequisiteEntityList = array();

		$requisiteIdLinked = 0;
		$bankDetailIdLinked = 0;
		$mcRequisiteIdLinked = 0;
		$mcBankDetailIdLinked = 0;

		if (isset($invoiceFields['UF_COMPANY_ID']) && $invoiceFields['UF_COMPANY_ID'] > 0)
			$requisiteEntityList[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Company, 'ENTITY_ID' => $invoiceFields['UF_COMPANY_ID']);
		if (isset($invoiceFields['UF_CONTACT_ID']) && $invoiceFields['UF_CONTACT_ID'] > 0)
			$requisiteEntityList[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Contact, 'ENTITY_ID' => $invoiceFields['UF_CONTACT_ID']);
		if (isset($invoiceFields['UF_MYCOMPANY_ID']) && $invoiceFields['UF_MYCOMPANY_ID'] > 0)
			$mcRequisiteEntityList[] = array('ENTITY_TYPE_ID' => \CCrmOwnerType::Company, 'ENTITY_ID' => $invoiceFields['UF_MYCOMPANY_ID']);
		$requisiteInfoLinked = $requisite->getDefaultRequisiteInfoLinked($requisiteEntityList);

		if (is_array($requisiteInfoLinked))
		{
			if (isset($requisiteInfoLinked['REQUISITE_ID']))
				$requisiteIdLinked = (int)$requisiteInfoLinked['REQUISITE_ID'];
			if (isset($requisiteInfoLinked['BANK_DETAIL_ID']))
				$bankDetailIdLinked = (int)$requisiteInfoLinked['BANK_DETAIL_ID'];
		}
		$mcRequisiteInfoLinked = $requisite->getDefaultMyCompanyRequisiteInfoLinked($mcRequisiteEntityList);

		if (is_array($mcRequisiteInfoLinked))
		{
			if (isset($mcRequisiteInfoLinked['MC_REQUISITE_ID']))
				$mcRequisiteIdLinked = (int)$mcRequisiteInfoLinked['MC_REQUISITE_ID'];
			if (isset($mcRequisiteInfoLinked['MC_BANK_DETAIL_ID']))
				$mcBankDetailIdLinked = (int)$mcRequisiteInfoLinked['MC_BANK_DETAIL_ID'];
		}
		unset($requisite, $requisiteEntityList, $mcRequisiteEntityList, $requisiteInfoLinked, $mcRequisiteInfoLinked);

		if ($requisiteIdLinked > 0 || $mcRequisiteIdLinked > 0)
		{
			EntityLink::register(
				\CCrmOwnerType::Invoice, $idRecurringInvoice,
				$requisiteIdLinked, $bankDetailIdLinked,
				$mcRequisiteIdLinked, $mcBankDetailIdLinked
			);
		}
	}

	/**
	 * @param $emailList
	 * @param $emailData
	 *
	 * @return Result
	 */
	private function sendByMail($emailList, $emailData)
	{
		$result = new Result();
		$emails = array();

		$emailFieldsData = \CCrmFieldMulti::GetListEx(
			array('ID' => 'asc'),
			array(
				'=ID' => $emailList,
				'=TYPE_ID' => 'EMAIL'
			)
		);
		while ($email = $emailFieldsData->Fetch())
		{
			$emails[$email['ID']] = $email;
		}

		if (!empty($emails)) 
		{
			$idListChunks = array_chunk(array_keys($emailData), 999);
			$mail = new Mail();
			foreach ($idListChunks as $idList) 
			{
				$newInvoiceData = \CCrmInvoice::GetList(
					array(),
					array(
						"=ID" => $idList,
						"CHECK_PERMISSIONS" => 'N'
					)
				);

				while ($invoice = $newInvoiceData->Fetch())
				{
					$emailId = $emailData[$invoice['ID']]['EMAIL_ID'];
					$templateId = $emailData[$invoice['ID']]['TEMPLATE_ID'];
					$r = $mail->setData($invoice, $emails[$emailId], $templateId);

					if ($r->isSuccess()) 
					{
						$mailResult = $mail->sendInvoice();
						if (!($mailResult->isSuccess())) 
						{
							$result->addErrors($mailResult->getErrors());
						}
					} 
					else 
						{
						$result->addErrors($r->getErrors());
					}
				}
			}
		}
		
		return $result;
	}

	/**
	 * @return bool
	 */
	public function isAllowedExpose()
	{
		if (Main\Loader::includeModule('thurlyos'))
			return !in_array(\CThurlyOS::getLicenseType(), array('project', 'tf'));

		return true;
	}

	/**
	 * Calculate payment date of a new invoice.
	 *
	 * @param array $params
	 *
	 * @return Date|null
	 */
	protected function getDatePayBefore(array $params)
	{
		$result['PERIOD'] = (int)$params['DATE_PAY_BEFORE_PERIOD'];

		if (empty($result['PERIOD']))
			return null;

		switch($result['PERIOD'])
		{
			case Calculator::SALE_TYPE_DAY_OFFSET:
			{
				$result['TYPE'] = DateType\Day::TYPE_A_FEW_DAYS_AFTER;
				$result['INTERVAL_DAY'] = (int)$params['DATE_PAY_BEFORE_COUNT'];
				break;
			}
			case Calculator::SALE_TYPE_WEEK_OFFSET:
			{
				$result['TYPE'] = DateType\Week::TYPE_A_FEW_WEEKS_AFTER;
				$result['INTERVAL_WEEK'] = (int)$params['DATE_PAY_BEFORE_COUNT'];
				break;
			}
			case Calculator::SALE_TYPE_MONTH_OFFSET:
			{
				$result['TYPE'] = DateType\Month::TYPE_A_FEW_MONTHS_AFTER;
				$result['INTERVAL_MONTH'] = (int)$params['DATE_PAY_BEFORE_COUNT'];
				break;
			}
			default:
				return null;
		}

		return parent::getNextDate($result);
	}

	/**
	 * Calculate next exposing date.
	 *
	 * @param array $params
	 * @param null $startDate
	 *
	 * @return Date
	 */
	protected function getNextDate(array $params, $startDate = null)
	{
		$result = array(
			"PERIOD" => (int)$params['PERIOD'] ? (int)$params['PERIOD'] : null
		);

		switch($result['PERIOD'])
		{
			case Calculator::SALE_TYPE_DAY_OFFSET:
				$result['INTERVAL_DAY'] = $params['DAILY_INTERVAL_DAY'];
				$result['IS_WORKDAY'] = $params['DAILY_WORKDAY_ONLY'];
				if (empty($params['DAILY_TYPE']))
				{
					$params['DAILY_TYPE'] = DateType\Day::TYPE_ALTERNATING_DAYS;
				}
				$result['TYPE'] = $params['DAILY_TYPE'];
				break;
			case Calculator::SALE_TYPE_WEEK_OFFSET:

				$result['WEEKDAYS'] = $params['WEEKLY_WEEK_DAYS'];
				$result['INTERVAL_WEEK'] = $params['WEEKLY_INTERVAL_WEEK'];
				if (!isset($params['WEEKLY_TYPE']))
				{
					$params['WEEKLY_TYPE'] = DateType\Week::TYPE_ALTERNATING_WEEKDAYS;
				}
				$result['TYPE'] = $params['WEEKLY_TYPE'];
				break;
			case Calculator::SALE_TYPE_MONTH_OFFSET:
				$result['INTERVAL_DAY'] = $params['MONTHLY_INTERVAL_DAY'];
				if ((int)$params['MONTHLY_TYPE'] === DateType\Month::TYPE_DAY_OF_ALTERNATING_MONTHS)
				{
					$result['INTERVAL_MONTH'] = $params['MONTHLY_MONTH_NUM_1'] - 1;
					$result['IS_WORKDAY'] = $params['MONTHLY_WORKDAY_ONLY'];
				}
				elseif ((int)$params['MONTHLY_TYPE'] === DateType\Month::TYPE_WEEKDAY_OF_ALTERNATING_MONTHS)
				{
					$result['INTERVAL_WEEK'] = $params['MONTHLY_WEEKDAY_NUM'];
					$result['INTERVAL_MONTH'] = $params['MONTHLY_MONTH_NUM_2'] - 1;
					$result['WEEKDAY'] = $params['MONTHLY_WEEK_DAY'];
				}
				$result['TYPE'] = $params['MONTHLY_TYPE'];
				break;
			case Calculator::SALE_TYPE_YEAR_OFFSET:
				$result['INTERVAL_DAY'] = $params['YEARLY_INTERVAL_DAY'];

				if ((int)$params['YEARLY_TYPE'] === DateType\Year::TYPE_DAY_OF_CERTAIN_MONTH)
				{
					$result['INTERVAL_DAY'] = $params['YEARLY_INTERVAL_DAY'];
					$result['INTERVAL_MONTH'] = $params['YEARLY_MONTH_NUM_1'];
					$result['IS_WORKDAY'] = $params['YEARLY_WORKDAY_ONLY'];
				}
				elseif ((int)$params['YEARLY_TYPE'] === DateType\Year::TYPE_WEEKDAY_OF_CERTAIN_MONTH)
				{
					$result['INTERVAL_WEEK'] = $params['YEARLY_WEEK_DAY_NUM'];
					$result['INTERVAL_MONTH'] = $params['YEARLY_MONTH_NUM_2'];
					$result['WEEKDAY'] = $params['YEARLY_WEEK_DAY'];
				}
				$result['TYPE'] = (int)$params['YEARLY_TYPE'];
		}

		return parent::getNextDate($result, $startDate);
	}
}
