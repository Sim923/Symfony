<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * UI rendering for a generic user field type
 *
 * @access private
 */

namespace Thurly\Tasks\Util\UserField;

use Thurly\Tasks\Util\Collection;
use Thurly\Tasks\Util\UserField;

abstract class UI
{
	public static function getClass($dataType)
	{
		$dataType = trim((string)$dataType);
		if ($dataType == '')
		{
			throw new \Thurly\Main\ArgumentException('$dataType could not be empty');
		}
		$dataType = str_replace('_', '', $dataType);

		$className = __NAMESPACE__ . '\\ui\\' . $dataType;
		if (!class_exists($className))
		{
			return __CLASS__;
		}

		return $className;
	}

	public static function showEdit(array $field, array $parameters = array(), $component = null)
	{
		if($field['EDIT_IN_LIST'] === 'Y')
		{
			static::showUI('thurly:system.field.edit', $field, $parameters, $component);
		}
		else
		{
			static::showView($field, $parameters);
		}
	}

	public static function showView($field, array $parameters = array())
	{
		static::showUI('thurly:system.field.view', $field, $parameters);
	}

	/**
	 * Check whether userfield is recommended to have user interface
	 *
	 * @param array $field
	 * @return bool
	 */
	public static function isSuitable(array $field)
	{
		// the following fields are obsolete and\or have no purpose of using with tasks module
		if(in_array($field['USER_TYPE_ID'], array('file', 'vote', 'video', 'disk_version', 'string_formatted', 'url_preview')))
		{
			return false;
		}

		// the following combinations of type\multiple works too far unstable to be presented to a user
		if($field['MULTIPLE'] == 'Y')
		{
			if(in_array($field['USER_TYPE_ID'], array('employee', 'hlblock', 'boolean', 'iblock_section')))
			{
				return false;
			}
		}

		return true;
	}

	private static function showUI($componentName, array $field, array $parameters = array(), $parentComponentInstance = null)
	{
		if(!intval($field['ENTITY_VALUE_ID']))
		{
			$useDefault = false;
			$valueFilled = UserField::isValueEmpty($field['VALUE']);

			if(($parameters['PREFER_DEFAULT'] || $field['MANDATORY'] == 'Y') && !$valueFilled)
			{
				$useDefault = true;
			}

			// just to make uf logic work
			$field['ENTITY_VALUE_ID'] = !$useDefault;
		}

		if(Collection::isA($field['VALUE']))
		{
			$field['VALUE'] = $field['VALUE']->toArray();
		}

		$parameters = array_merge($parameters, array(
			"bVarsFromForm" => false,
			"arUserField" => $field,
		));

		$GLOBALS['APPLICATION']->IncludeComponent(
			$componentName,
			$field["USER_TYPE"]["USER_TYPE_ID"],
			$parameters,
			$parentComponentInstance,
			array("HIDE_ICONS" => "Y")
		);
	}
}