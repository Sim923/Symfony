<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?
	if (!empty($arResult['BUTTONS']))
	{
		$APPLICATION->IncludeComponent(
			'thurly:main.interface.toolbar',
			'',
			array(
				'BUTTONS' => $arResult['BUTTONS']
			),
			$component,
			array(
				'HIDE_ICONS' => 'Y'
			)
		);
	}
?>