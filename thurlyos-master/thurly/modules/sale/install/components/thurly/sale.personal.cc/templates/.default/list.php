<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"thurly:sale.personal.cc.list",
	"",
	array(
		"PATH_TO_DETAIL" => $arResult["PATH_TO_DETAIL"],
		"PER_PAGE" => $arParams["PER_PAGE"],
		"SET_TITLE" =>$arParams["SET_TITLE"],
	),
	$component
);
?>

