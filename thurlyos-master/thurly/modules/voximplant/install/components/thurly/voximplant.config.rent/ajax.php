<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);
define("DisableEventsCheck", true);
define("NO_AGENT_CHECK", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if (!CModule::IncludeModule("voximplant"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'VI_MODULE_NOT_INSTALLED'));
	CMain::FinalActions();
	die();
}

if (!check_thurly_sessid())
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));
	CMain::FinalActions();
	die();
}

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_LINE, \Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'AUTHORIZE_ERROR'));
	CMain::FinalActions();
	die();
}

if ($_POST['VI_GET_COUNTRY'] == 'Y')
{
	$arSend['ERROR'] = '';
	$result = CVoxImplantPhone::GetPhoneCategories();
	if (!empty($result))
	{
		$arSend['RESULT'] = $result;
	}
	else
	{
		$arSend['ERROR'] = 'ERROR';
	}
	echo CUtil::PhpToJsObject($arSend);
}
else if ($_POST['VI_GET_STATE'] == 'Y')
{
	$arSend['ERROR'] = '';
	$result = CVoxImplantPhone::GetPhoneCountryStates($_POST['COUNTRY_CODE'], $_POST['COUNTRY_CATEGORY']);
	if ($result !== false)
	{
		$arSend['RESULT'] = $result;
	}
	else
	{
		$arSend['ERROR'] = 'ERROR';
	}
	echo CUtil::PhpToJsObject($arSend);
}
else if ($_POST['VI_GET_REGION'] == 'Y')
{
	$arSend['ERROR'] = '';
	$result = CVoxImplantPhone::GetPhoneRegions($_POST['COUNTRY_CODE'], $_POST['COUNTRY_STATE'], $_POST['COUNTRY_CATEGORY']);
	if ($result !== false)
	{
		$arSend['RESULT'] = $result;
	}
	else
	{
		$arSend['ERROR'] = 'ERROR';
	}
	echo CUtil::PhpToJsObject($arSend);
}
else if ($_POST['VI_GET_PHONE_NUMBERS'] == 'Y')
{
	$arSend['ERROR'] = '';
	$result = CVoxImplantPhone::GetPhoneNumbers($_POST['COUNTRY_CODE'], $_POST['COUNTRY_REGION'], $_POST['COUNTRY_CATEGORY']);
	if ($result !== false)
	{
		$arSend['RESULT'] = $result;
	}
	else
	{
		$arSend['ERROR'] = 'ERROR';
	}
	echo CUtil::PhpToJsObject($arSend);
}
else if ($_POST['VI_RENT_NUMBER'] == 'Y')
{
	$arSend['SUCCESS'] = 'N';

	$ViAccount = new CVoxImplantAccount();
	$accountBalance = $ViAccount->GetAccountBalance(true);

	$orm = Thurly\Voximplant\ConfigTable::getList(Array(
		'filter'=>Array(
			'=SEARCH_ID' => $_POST['CURRENT_NUMBER']
		)
	));
	if(!\Thurly\Voximplant\Limits::canRentNumber())
	{
		$arSend['ERRORS'][] = array(
			'CODE' => 'LIMIT_REACHED',
		);
	}
	else if ($orm->fetch())
	{
		$arSend['ERRORS'][] = array(
			'CODE' => 'ATTACHED',
		);
	}
	else if (floatval($_POST['PRE_MONEY_CHECK']) <= $accountBalance)
	{
		$orm = Thurly\Voximplant\ConfigTable::getList(Array(
			'filter'=>Array(
				'=PHONE_COUNTRY_CODE' => $_POST['COUNTRY_CODE'],
				'=PHONE_VERIFIED' => 'N'
			)
		));
		if ($orm->fetch())
		{
			$arSend['ERRORS'][] = array(
				'CODE' => 'NOT_VERIFIED',
			);
		}
		else
		{
			$result = CVoxImplantPhone::AttachPhoneNumber($_POST['COUNTRY_CODE'], $_POST['REGION_ID'], $_POST['CURRENT_NUMBER'], $_POST['COUNTRY_STATE'], $_POST['COUNTRY_CATEGORY'], $_POST['ADDRESS_VERIFICATION']);
			$arSend = $result->toArray();
		}
	}
	else
	{
		$arSend['ERRORS'][] = array(
			'CODE' => 'NO_MONEY',
		);
	}

	echo CUtil::PhpToJsObject($arSend);
}
else if ($_POST['VI_UNLINK_NUMBER'] == 'Y')
{
	$arSend['ERROR'] = '';

	$_POST['NUMBER_ID'] = intval($_POST['NUMBER_ID']);
	if ($_POST['NUMBER_ID'] > 0)
	{
		$result = Thurly\Voximplant\ConfigTable::getById($_POST['NUMBER_ID']);
		if ($row = $result->fetch())
		{
			$result = CVoxImplantPhone::EnqueueDeactivatePhoneNumber($row['SEARCH_ID']);
			if (!$result)
			{
				$arSend['ERROR'] = 'ERROR';
			}
		}
	}

	echo CUtil::PhpToJsObject($arSend);
}
else if ($_POST['VI_CANCEL_UNLINK_NUMBER'] == 'Y')
{
	$arSend['ERROR'] = '';

	$_POST['NUMBER_ID'] = intval($_POST['NUMBER_ID']);
	if ($_POST['NUMBER_ID'] > 0)
	{
		$result = Thurly\Voximplant\ConfigTable::getById($_POST['NUMBER_ID']);
		if ($row = $result->fetch())
		{
			$result = CVoxImplantPhone::CancelDeactivatePhoneNumber($row['SEARCH_ID']);
			if (!$result)
			{
				$arSend['ERROR'] = 'ERROR';
			}
		}
	}

	echo CUtil::PhpToJsObject($arSend);
}
else if($_POST['VI_GET_VERIFIED_ADDRESSES'] == 'Y')
{
	$arSend['ERROR'] = '';
	$addressVerification = new \Thurly\VoxImplant\AddressVerification();
	$result = $addressVerification->getAvailableVerifications($_POST['COUNTRY_CODE'], $_POST['COUNTRY_CATEGORY'], $_POST['COUNTRY_REGION']);
	if ($result !== false)
	{
		$arSend['RESULT'] = $result;
	}
	else
	{
		$arSend['ERROR'] = $addressVerification->getError();
	}

	echo \Thurly\Main\Web\Json::encode($arSend);
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'UNKNOWN_ERROR'));
}
CMain::FinalActions();
die();