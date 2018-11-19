<?

namespace Thurly\Main\UI\Filter;


/**
 * Class Theme. Available default filter themes
 * @package Thurly\Main\UI\Filter
 */
class Theme
{
	const DEFAULT_FILTER = "DEFAULT";
	const BORDER = "BORDER";
	const ROUNDED = "ROUNDED";


	/**
	 * Gets themes list
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}