<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Thurly Framework
 * @package thurly
 * @subpackage mobile
 * @copyright 2001-2015 Thurly
 */

class ThurlyMobileTasksViewComponent extends CThurlyComponent
{
	/**
	 * Function implements all the life cycle of our component
	 * @return void
	 */
	public function executeComponent()
	{
		$this->arResult['DATA'] = is_array($this->arParams['DATA']) ? $this->arParams['DATA'] : array();

		$this->includeComponentTemplate();
	}

	public static function addPathTemplateParameter($template, $parameters = array())
	{
		$template = (string) $template;
		if(!is_array($parameters) || empty($parameters))
			return $template;

		$hasSign = strpos($template, '?') !== false;

		foreach($parameters as $param)
		{
			$template .= ($hasSign ? '?' : '&').$parameter.'=#'.$parameter.'#';
			if(!$hasSign)
				$hasSign = true;
		}

		return $template;
	}
}