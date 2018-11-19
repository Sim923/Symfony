<?
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

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
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_SETTINGS,\Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'AUTHORIZE_ERROR'));
	CMain::FinalActions();
	die();
}

$action = $_REQUEST['action'];

$sendResult = function(\Thurly\Main\Result $result)
{
	if($result->isSuccess())
	{
		echo \Thurly\Main\Web\Json::encode(array(
			'SUCCESS' => true,
			'DATA' => $result->getData()
		));
	}
	else
	{
		echo \Thurly\Main\Web\Json::encode(array(
			'SUCCESS' => false,
			'ERROR' => implode(';', $result->getErrorMessages())
		));
	}
};

if($action == 'save')
{
	CThurlyComponent::includeComponentClass('thurly:voximplant.ivr.edit');
	$result = CVoximplantIvrEditComponent::saveIvr(\Thurly\Main\Web\Json::decode($_POST['IVR']));
	$sendResult($result);
}
else if ($action == 'upload_file')
{
	CThurlyComponent::includeComponentClass('thurly:voximplant.ivr.edit');
	$result = CVoximplantIvrEditComponent::uploadFile($_POST);
	$sendResult($result);
}
else if ($action == 'group_settings')
{
	$APPLICATION->ShowAjaxHead();
	$APPLICATION->IncludeComponent(
		'thurly:voximplant.queue.edit',
		'',
		array(
			'ID' => (int)$_REQUEST['groupId'],
			'INLINE_MODE' => true,
			'EXTERNAL_REQUEST_ID' => (string)$_REQUEST['requestId']
		)
	);
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'UNKNOWN_ACTION'));
}

CMain::FinalActions();
die();