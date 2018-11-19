<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2017 Thurly
 */

namespace Thurly\Main\Entity\Query;

use Thurly\Main\Entity\Query\Filter\ConditionTree as Filter;

/**
 * Class for nice description of join reference. Alias to Query::filter().
 * @package    thurly
 * @subpackage main
 */
class Join
{
	/**
	 * Short alias to init filter with whereColumn.
	 * @see Filter::whereColumn()
	 *
	 * @param array ...$condition
	 *
	 * @return Filter
	 */
	public static function on()
	{
		$condition = func_get_args();
		return call_user_func_array(array(new Filter, 'whereColumn'), $condition);
	}
}