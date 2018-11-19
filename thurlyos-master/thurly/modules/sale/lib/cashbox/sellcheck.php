<?php

namespace Thurly\Sale\Cashbox;

use Thurly\Main;
use Thurly\Sale\Result;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class SellCheck
 * @package Thurly\Sale\Cashbox
 */
class SellCheck extends Check
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'sell';
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_INCOME;
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_SELL_NAME');
	}

	/**
	 * @return string
	 */
	public static function getSupportedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_ALL;
	}
}