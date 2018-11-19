<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Thurly\Main\Localization\Loc;

if(!\Thurly\Main\Loader::includeModule('rest') || !$USER->IsAuthorized())
{
	return;
}

$arParams['ID'] = intval($arParams['ID']);

InitBVar($arParams['SET_TITLE']);

$eventDictionary = new \Thurly\Rest\Dictionary\WebHook();
$arResult['EVENTS_DESC'] = array();

foreach($eventDictionary as $event)
{
	$arResult['EVENTS_DESC'][ToUpper($event['code'])] = $event;
}

if($arParams['ID'] > 0)
{
	$dbRes = \Thurly\Rest\EventTable::getById($arParams['ID']);
	$event = $dbRes->fetch();

	if(is_array($event))
	{
		if($event['USER_ID'] != $USER->GetID() || $event['APP_ID'] > 0)
		{
			$event = false;
		}
	}

	if(!$event)
	{
		ShowError(Loc::getMessage('REST_HEVE_NOT_FOUND'));
		return;
	}

	$arResult['INFO'] = array(
		'ID' => $event['ID'],
		"EVENT_NAME" => $event['EVENT_NAME'],
		"EVENT_HANDLER" => $event['EVENT_HANDLER'],
		"TITLE" => $event['TITLE'],
		"COMMENT" => $event['COMMENT'],
		"APPLICATION_TOKEN" => $event['APPLICATION_TOKEN'],
	);
}
else
{
	$arResult['INFO'] = array(
		'ID' => 0,
		'EVENT_NAME' => '',
		'EVENT_HANDLER' => '',
		'TITLE' => Loc::getMessage('REST_HEVE_TITLE_DEFAULT'),
		'COMMENT' => '',
	);
}

$request = \Thurly\Main\Context::getCurrent()->getRequest();

if($request->isPost() && check_thurly_sessid())
{
	$arResult['INFO']['EVENT_NAME'] = trim($request['EVENT_NAME']);
	$arResult['INFO']['EVENT_HANDLER'] = trim($request['EVENT_HANDLER']);
	$arResult['INFO']['TITLE'] = trim($request['TITLE']);
	$arResult['INFO']['COMMENT'] = trim($request['COMMENT']);

	$error = new \Thurly\Main\ErrorCollection();

	if(strlen($arResult['INFO']['EVENT_NAME']) > 0)
	{
		if(!isset($arResult['EVENTS_DESC'][$arResult['INFO']['EVENT_NAME']]))
		{
			$arResult['INFO']['EVENT_NAME'] = '';
		}
	}

	if(strlen($arResult['INFO']['EVENT_NAME']) <= 0)
	{
		$error->add(
			array(
				new \Thurly\Main\Error(Loc::getMessage('REST_HEVE_ERROR_EVENT_NAME'))
			)
		);
	}

	$uri = new \Thurly\Main\Web\Uri($arResult['INFO']['EVENT_HANDLER']);

	if(
		strlen($uri->getHost()) <= 0
		|| !($uri->getScheme() == 'http' || $uri->getScheme() == 'https')
	)
	{
		$error->add(
			array(
				new \Thurly\Main\Error(Loc::getMessage('REST_HEVE_ERROR_EVENT_HANDLER'))
			)
		);
	}

	$arResult['INFO']['EVENT_HANDLER'] = $uri->getLocator();

	if($error->count() <= 0)
	{
		if(!\Thurly\Rest\OAuthService::getEngine()->isRegistered())
		{
			try
			{
				\Thurly\Rest\OAuthService::register();
			}
			catch(\Thurly\Main\SystemException $e)
			{
			}
		}

		if(
			$arResult['INFO']['ID'] <= 0
			|| $request['APPLICATION_TOKEN_REGEN'] == 'Y'
		)
		{
			$arResult['INFO']['APPLICATION_TOKEN'] = \Thurly\Main\Security\Random::getString(32);
		}

		if($arResult['INFO']['ID'] > 0)
		{
			$result = \Thurly\Rest\EventTable::update(
				$arResult['INFO']['ID'],
				array(
					'EVENT_NAME' => $arResult['INFO']['EVENT_NAME'],
					'EVENT_HANDLER' => $arResult['INFO']['EVENT_HANDLER'],
					'APPLICATION_TOKEN' => $arResult['INFO']['APPLICATION_TOKEN'],
					'TITLE' => $arResult['INFO']['TITLE'],
					'COMMENT' => $arResult['INFO']['COMMENT'],
				)
			);
		}
		else
		{
			$result = \Thurly\Rest\EventTable::add(
				array(
					'USER_ID' => $USER->getId(),
					'DATE_CREATE' => new \Thurly\Main\Type\DateTime(),
					'EVENT_NAME' => $arResult['INFO']['EVENT_NAME'],
					'EVENT_HANDLER' => $arResult['INFO']['EVENT_HANDLER'],
					'TITLE' => $arResult['INFO']['TITLE'],
					'COMMENT' => $arResult['INFO']['COMMENT'],
					'APPLICATION_TOKEN' => $arResult['INFO']['APPLICATION_TOKEN'],
				)
			);

			$justCreated = true;
		}

		if($result->isSuccess())
		{
			if($justCreated)
			{
				$arResult['INFO']['ID'] = $result->getId();

				$url = new \Thurly\Main\Web\Uri(str_replace(
					'#id#', $arResult['INFO']['ID'], $arParams['EDIT_URL_TPL']
				));

				LocalRedirect(
					$url->addParams(array('success' => 1))
						->getLocator()
				);
			}
			else
			{
				LocalRedirect($arParams['LIST_URL']);
			}
		}
		else
		{
			$arResult['ERROR'] = implode('<br />', $result->getErrorMessages());
		}
	}
	else
	{
		$arResult['ERROR'] = implode('<br />', $error->toArray());
	}
}

$arResult["EVENTS"] = \CRestUtil::getEventList();

if($arParams['SET_TITLE'] == 'Y')
{
	if($arResult['INFO']['ID'] > 0)
	{
		$APPLICATION->SetTitle(Loc::getMessage('REST_HEVE_EDIT_TITLE'));
	}
	else
	{
		$APPLICATION->SetTitle(Loc::getMessage('REST_HEVE_ADD_TITLE'));
	}
}

$this->IncludeComponentTemplate();