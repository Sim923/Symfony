<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

$APPLICATION->IncludeComponent(
	'thurly:crm.quote.payment',
	'',
	array(
		'QUOTE_ID' => $arResult['VARIABLES']['quote_id']
	),
	$component
);
