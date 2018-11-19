<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;

$APPLICATION->IncludeComponent(
	'thurly:main.interface.filter',
	'popup',
	array(
		'GRID_ID'=>$arParams['~GRID_ID'],
		'FILTER'=>$arParams['~FILTER'],
		"FILTER_PRESETS"=>$arParams["~FILTER_PRESETS"],
		'FILTER_ROWS'=>$arParams['~FILTER_ROWS'],
		'FILTER_FIELDS'=>$arParams['~FILTER_FIELDS'],
		'OPTIONS'=>$arParams['~OPTIONS'],
		'FILTER_INFO'=>$arResult['FILTER_INFO'],
		'RENDER_FILTER_INTO_VIEW'=>isset($arParams['~RENDER_FILTER_INTO_VIEW']) ? $arParams['~RENDER_FILTER_INTO_VIEW'] : ''
	),
	$component,
	array('HIDE_ICONS'=>true)
);
