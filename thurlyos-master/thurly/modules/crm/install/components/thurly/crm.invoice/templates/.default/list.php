<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/** @var CMain $APPLICATION */
$APPLICATION->IncludeComponent(
	'thurly:crm.control_panel',
	'',
	array(
		'ID' => 'INVOICE_LIST',
		'ACTIVE_ITEM_ID' => 'INVOICE',
		'PATH_TO_COMPANY_LIST' => isset($arResult['PATH_TO_COMPANY_LIST']) ? $arResult['PATH_TO_COMPANY_LIST'] : '',
		'PATH_TO_COMPANY_EDIT' => isset($arResult['PATH_TO_COMPANY_EDIT']) ? $arResult['PATH_TO_COMPANY_EDIT'] : '',
		'PATH_TO_CONTACT_LIST' => isset($arResult['PATH_TO_CONTACT_LIST']) ? $arResult['PATH_TO_CONTACT_LIST'] : '',
		'PATH_TO_CONTACT_EDIT' => isset($arResult['PATH_TO_CONTACT_EDIT']) ? $arResult['PATH_TO_CONTACT_EDIT'] : '',
		'PATH_TO_DEAL_LIST' => isset($arResult['PATH_TO_DEAL_LIST']) ? $arResult['PATH_TO_DEAL_LIST'] : '',
		'PATH_TO_DEAL_EDIT' => isset($arResult['PATH_TO_DEAL_EDIT']) ? $arResult['PATH_TO_DEAL_EDIT'] : '',
		'PATH_TO_LEAD_LIST' => isset($arResult['PATH_TO_LEAD_LIST']) ? $arResult['PATH_TO_LEAD_LIST'] : '',
		'PATH_TO_LEAD_EDIT' => isset($arResult['PATH_TO_LEAD_EDIT']) ? $arResult['PATH_TO_LEAD_EDIT'] : '',
		'PATH_TO_QUOTE_LIST' => isset($arResult['PATH_TO_QUOTE_LIST']) ? $arResult['PATH_TO_QUOTE_LIST'] : '',
		'PATH_TO_QUOTE_EDIT' => isset($arResult['PATH_TO_QUOTE_EDIT']) ? $arResult['PATH_TO_QUOTE_EDIT'] : '',
		'PATH_TO_INVOICE_LIST' => isset($arResult['PATH_TO_INVOICE_LIST']) ? $arResult['PATH_TO_INVOICE_LIST'] : '',
		'PATH_TO_INVOICE_EDIT' => isset($arResult['PATH_TO_INVOICE_EDIT']) ? $arResult['PATH_TO_INVOICE_EDIT'] : '',
		'PATH_TO_REPORT_LIST' => isset($arResult['PATH_TO_REPORT_LIST']) ? $arResult['PATH_TO_REPORT_LIST'] : '',
		'PATH_TO_DEAL_FUNNEL' => isset($arResult['PATH_TO_DEAL_FUNNEL']) ? $arResult['PATH_TO_DEAL_FUNNEL'] : '',
		'PATH_TO_EVENT_LIST' => isset($arResult['PATH_TO_EVENT_LIST']) ? $arResult['PATH_TO_EVENT_LIST'] : '',
		'PATH_TO_PRODUCT_LIST' => isset($arResult['PATH_TO_PRODUCT_LIST']) ? $arResult['PATH_TO_PRODUCT_LIST'] : ''
	),
	$component
);

if(!Thurly\Crm\Integration\ThurlyOSManager::isAccessEnabled(CCrmOwnerType::Invoice))
{
	$APPLICATION->IncludeComponent('thurly:thurlyos.business.tools.info', '', array());
}
else
{
	$isThurlyOSTemplate = SITE_TEMPLATE_ID === 'thurlyos';
	if($isThurlyOSTemplate)
	{
		$this->SetViewTarget('below_pagetitle', 0);
	}

	$APPLICATION->IncludeComponent(
		'thurly:crm.entity.counter.panel',
		'',
		array('ENTITY_TYPE_NAME' => CCrmOwnerType::InvoiceName)
	);

	if($isThurlyOSTemplate)
	{
		$this->EndViewTarget();
	}

	if ($arResult['RESTRICTED_RECURRING'] !== 'Y')
	{
		if($isThurlyOSTemplate)
		{
			$this->SetViewTarget('inside_pagetitle', 100);
		}

		$APPLICATION->IncludeComponent(
			'thurly:crm.entity.list.switcher',
			'',
			array(
				'ENTITY_TYPE' => \CCrmOwnerType::Invoice,
				'NAVIGATION_ITEMS' => array(
					array(
						'id' => 'list',
						'name' => GetMessage('CRM_INVOICE_LIST_SWITCHER_STANDART'),
						'active' =>$arResult['IS_RECURRING'] !== 'Y',
						'url' =>  $arResult['PATH_TO_INVOICE_LIST']
					),
					array(
						'id' => 'recur',
						'name' => GetMessage('CRM_INVOICE_LIST_SWITCHER_RECUR'),
						'active' => $arResult['IS_RECURRING'] === 'Y',
						'url' => $arResult['PATH_TO_INVOICE_RECUR']
					)
				)
			),
			$component
		);
		if($isThurlyOSTemplate)
		{
			$this->EndViewTarget();
		}
	}

	$APPLICATION->ShowViewContent('crm-grid-filter');

	$APPLICATION->IncludeComponent(
		'thurly:crm.invoice.menu',
		'',
		array(
			'PATH_TO_INVOICE_LIST' => $arResult['PATH_TO_INVOICE_LIST'],
			'PATH_TO_INVOICE_SHOW' => $arResult['PATH_TO_INVOICE_SHOW'],
			'PATH_TO_INVOICE_EDIT' => $arResult['PATH_TO_INVOICE_EDIT'],
			'ELEMENT_ID' => $arResult['VARIABLES']['invoice_id'],
			'IS_RECURRING' => $arResult['IS_RECURRING'],
			'TYPE' => 'list'
		),
		$component
	);

	if(\Thurly\Main\ModuleManager::isModuleInstalled('rest'))
	{
		$APPLICATION->IncludeComponent(
			'thurly:app.placement',
			'menu',
			array(
				'PLACEMENT' => "CRM_INVOICE_LIST_MENU",
				"PLACEMENT_OPTIONS" => array(),
				'INTERFACE_EVENT' => 'onCrmInvoiceListInterfaceInit',
				'MENU_EVENT_MODULE' => 'crm',
				'MENU_EVENT' => 'onCrmInvoiceListItemBuildMenu',
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}

	$APPLICATION->IncludeComponent(
		'thurly:crm.invoice.list',
		'',
		array(
			'INVOICE_COUNT' => '20',
			'IS_RECURRING' => $arResult['IS_RECURRING'],
			'PATH_TO_INVOICE_SHOW' => $arResult['PATH_TO_INVOICE_SHOW'],
			'PATH_TO_INVOICE_RECUR_SHOW' => $arResult['PATH_TO_INVOICE_RECUR_SHOW'],
			'PATH_TO_INVOICE_RECUR' => $arResult['PATH_TO_INVOICE_RECUR'],
			'PATH_TO_INVOICE_RECUR_EDIT' => $arResult['PATH_TO_INVOICE_RECUR_EDIT'],
			'PATH_TO_INVOICE_EDIT' => $arResult['PATH_TO_INVOICE_EDIT'],
			'PATH_TO_INVOICE_PAYMENT' => $arResult['PATH_TO_INVOICE_PAYMENT'],
			'PATH_TO_INVOICE_WIDGET' => $arResult['PATH_TO_INVOICE_WIDGET'],
			'PATH_TO_INVOICE_KANBAN' => $arResult['PATH_TO_INVOICE_KANBAN'],
			'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
			'NAVIGATION_CONTEXT_ID' => $arResult['NAVIGATION_CONTEXT_ID']
		),
		$component
	);
}
