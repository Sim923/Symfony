<?

use \Thurly\Main\Config\Option;
/**
 * Thurly Framework
 * @global CMain $APPLICATION
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");

$result = array("ERROR" => "");

if (!\Thurly\Main\Loader::includeModule('sale'))
	$result["ERROR"] = "Error! Can't include module \"Sale\"";

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(strlen($result["ERROR"]) <= 0 && $saleModulePermissions >= "W" && check_thurly_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "set_map_type":
			$mapType = isset($_REQUEST['map_type']) ? trim($_REQUEST['map_type']): 'yandex';
			Option::set('sale', 'order_choose_comp_map_type', $mapType);
			break;
		default:
			$result["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if(strlen($result["ERROR"]) <= 0)
		$result["ERROR"] = "Error! Access denied";
}

if(strlen($result["ERROR"]) > 0)
	$result["RESULT"] = "ERROR";
else
	$result["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$result = \Thurly\Main\Text\Encoding::convertEncoding($result, SITE_CHARSET, 'utf-8');

$result = json_encode($result);
\CMain::FinalActions();
header('Content-Type: application/json');
die($result);