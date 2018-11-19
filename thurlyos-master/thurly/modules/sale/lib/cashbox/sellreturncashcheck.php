<?php

namespace Thurly\Sale\Cashbox;

use Thurly\Main\Localization\Loc;
use Thurly\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

/**
 * Class SellReturnCashCheck
 * @package Thurly\Sale\Cashbox
 */
class SellReturnCashCheck extends SellCheck
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'sellreturncash';
	}

	/**
	 * @throws NotImplementedException
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_CONSUMPTION;
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Loc::getMessage('SALE_CASHBOX_SELL_RETURN_CASH_NAME');
	}


	/**
	 * @return array
	 */
	protected function extractDataInternal()
	{
		$result = parent::extractDataInternal();

		if (isset($result['PAYMENTS']))
		{
			foreach ($result['PAYMENTS'] as $i => $payment)
				$result['PAYMENTS'][$i]['IS_CASH'] = 'Y';

		}

		return $result;
	}

	/**
	 * @return string
	 */
	public static function getSupportedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_PAYMENT;
	}
}