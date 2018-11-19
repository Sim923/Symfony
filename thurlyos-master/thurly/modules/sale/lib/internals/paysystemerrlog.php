<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale\Internals;

use Thurly\Main;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PaySystemErrLogTable extends Main\Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_pay_system_err_log';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('PAY_SYSTEM_ENTITY_ID_FIELD'),
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('PAY_SYSTEM_ENTITY_DATE_ADD_FIELD'),
			),
			'MESSAGE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateMessage'),
				'title' => Loc::getMessage('PAY_SYSTEM_ENTITY_LID_FIELD'),
				'required' => true
			),
			'ACTION' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('PAY_SYSTEM_ENTITY_CURRENCY_FIELD'),
				'required' => true
			)
		);
	}

	public static function validateMessage()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2000),
		);
	}
}