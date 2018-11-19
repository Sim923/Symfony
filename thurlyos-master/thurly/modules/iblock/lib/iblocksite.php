<?php
namespace Thurly\Iblock;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class IblockSiteTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> SITE_ID char(2) mandatory
 * <li> IBLOCK reference to {@link \Thurly\Iblock\IblockTable}
 * <li> SITE reference to {@link \Thurly\Main\SiteTable}
 * </ul>
 *
 * @package Thurly\Iblock
 */
class IblockSiteTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_site';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_SITE_ENTITY_IBLOCK_ID_FIELD'),
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateSiteId'),
				'title' => Loc::getMessage('IBLOCK_SITE_ENTITY_SITE_ID_FIELD'),
			),
			'IBLOCK' => array(
				'data_type' => 'Thurly\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID')
			),
			'SITE' => array(
				'data_type' => 'Thurly\Main\Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID'),
			),
		);
	}

	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId()
	{
		return array(
			new Entity\Validator\Length(null, 2),
		);
	}
}