<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var @global CMain $APPLICATION */
global $APPLICATION;
$APPLICATION->AddHeadScript("/thurly/js/main/dd.js");
CJSCore::Init(array("ajax", "access"));

if(\Thurly\Main\Loader::includeModule('rest'))
{
	CJSCore::Init(array("marketplace"));
}
?>