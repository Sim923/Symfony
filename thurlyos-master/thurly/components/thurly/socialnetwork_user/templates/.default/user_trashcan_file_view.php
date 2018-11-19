<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CThurlyComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CThurlyComponent $component */
?>

<?
$pageId = "user_files";
include("util_menu.php");
include("util_profile.php");
?>

<?php

$arResult['PATH_TO_FILE_VIEW'] = $arResult['PATH_TO_USER_TRASHCAN_FILE_VIEW'];
$APPLICATION->IncludeComponent(
	'thurly:disk.file.view',
	'',
	array_merge($arResult, array(
		'PATH_TO_TRASHCAN_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_TRASHCAN'], array('user_id' => $arResult['VARIABLES']['user_id'])),
		'PATH_TO_TRASHCAN_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_TRASHCAN_FILE_VIEW'], array('user_id' => $arResult['VARIABLES']['user_id'])),
		'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('user_id' => $arResult['VARIABLES']['user_id'])),
		'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
		'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
		'STORAGE' => $arResult['VARIABLES']['STORAGE'],
		'FILE_ID' => $arResult['VARIABLES']['FILE_ID'],
		'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
	)),
	$component
);?>