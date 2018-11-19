<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sender
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Fileman\Block\Content;

use Thurly\Main\Web\Json;
use Thurly\Main\ArgumentException;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class JsonConverter implements IConverter
{
	/**
	 * Check string.
	 *
	 * @param string $string String.
	 * @return bool
	 */
	public static function isValid($string)
	{
		$string = trim($string);
		$char = substr($string, 0, 1);
		if(!in_array($char, array('{', '[')))
		{
			return false;
		}
		$char = substr($string, -1);
		if(!in_array($char, array('}', ']')))
		{
			return false;
		}

		try
		{
			$r = Json::decode($string);
			return is_array($r);
		}
		catch (ArgumentException $exception)
		{
			return false;
		}
	}

	/**
	 * Parse string of json content to an array of content blocks.
	 *
	 * @param string $string String.
	 * @return BlockContent
	 */
	public static function toArray($string)
	{
		$blockContent = new BlockContent();
		try
		{
			$list = Json::decode($string);
			if (!is_array($list))
			{
				return $blockContent;
			}
		}
		catch (ArgumentException $exception)
		{
			return $blockContent;
		}

		foreach ($list as $item)
		{
			$type = trim($item['type']);
			$place = trim($item['place']);
			$value = trim($item['value']);
			$blockContent->add($type, $place, $value);
		}

		return $blockContent;
	}
}