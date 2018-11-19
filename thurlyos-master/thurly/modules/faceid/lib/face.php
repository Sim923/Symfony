<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage faceid
 * @copyright  2001-2016 Thurly
 */

namespace Thurly\Faceid;

use Thurly\Main\Entity;

class FaceTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_faceid_face';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			new Entity\IntegerField('FILE_ID'),
			new Entity\IntegerField('CLOUD_FACE_ID')
		);
	}
}