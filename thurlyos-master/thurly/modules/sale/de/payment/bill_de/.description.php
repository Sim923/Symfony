<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$langFile = GetLangFileName(dirname(__FILE__)."/", "/bill.php");

if(file_exists($langFile))
	include($langFile);

$psDescription = GetMessage("SBLP_DDESCR");

$isAffordPdf = true;

include \Thurly\Main\Application::getDocumentRoot().'/thurly/modules/sale/handlers/paysystem/billde/.description.php';