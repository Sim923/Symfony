<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/marketing/.left.menu.php");

$aMenuLinks = Array(
	Array(
		GetMessage('SERVICES_MENU_MARKETING_START'),
		"/marketing/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage('SERVICES_MENU_MARKETING_LETTERS'),
		"/marketing/letter/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage('SERVICES_MENU_MARKETING_ADS'),
		"/marketing/ads/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage('SERVICES_MENU_MARKETING_SEGMENTS'),
		"/marketing/segment/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage('SERVICES_MENU_MARKETING_TEMPLATES'),
		"/marketing/template/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage('SERVICES_MENU_MARKETING_BLACKLIST'),
		"/marketing/blacklist/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage('SERVICES_MENU_MARKETING_CONFIG'),
		"/marketing/config.php",
		Array(),
		Array(),
		""
	),
);