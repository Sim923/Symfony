<?php

namespace Thurly\Sale\Cashbox;

use Thurly\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class AdvancePaymentCheck
 * @package Thurly\Sale\Cashbox
 */

class AdvancePaymentCheck extends Check
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'advancepayment';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_ADVANCE_PAYMENT_NAME');
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_INCOME;
	}

	/**
	 * @return array
	 */
	protected function extractDataInternal()
	{
		$result = parent::extractDataInternal();

		unset($result['DELIVERY']);
		$result['PRODUCTS'] = array(
			array(
				'NAME' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ADVANCE_PAYMENT_ITEM_NAME'),
				'QUANTITY' => 1,
				'PRICE' => $result['TOTAL_SUM'],
				'SUM' => $result['TOTAL_SUM'],
				'BASE_PRICE' => $result['TOTAL_SUM'],
			)
		);

		return $result;
	}

}