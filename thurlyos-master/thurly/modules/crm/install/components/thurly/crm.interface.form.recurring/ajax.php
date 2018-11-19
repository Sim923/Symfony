<?php

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

use \Thurly\Crm\Recurring\Calculator,
	\Thurly\Crm\Recurring\Manager,
	Thurly\Crm\Recurring\DateType,
	\Thurly\Main\Type\Date;

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

if(!function_exists('__CrmRecurringFieldEditEndResponse'))
{
	function __CrmRecurringFieldEditEndResponse($result)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		if(!empty($result))
		{
			echo CUtil::PhpToJSObject($result);
		}
		require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');
		die();
	}
}

$request = Thurly\Main\Application::getInstance()->getContext()->getRequest();

if (!CModule::IncludeModule('crm'))
{
	__CrmRecurringFieldEditEndResponse(array('ERROR' => 'Could not include crm module.'));
}

if (!CCrmSecurityHelper::IsAuthorized() || !check_thurly_sessid())
{
	__CrmRecurringFieldEditEndResponse(array('ERROR' => 'Access denied.'));
}
if (!$request->isPost())
{
	__CrmRecurringFieldEditEndResponse(array('ERROR' => 'Request method is not allowed.'));
}
$request = $request->toArray();
if (strlen($request['PARAMS']['DEAL_DATEPICKER_BEFORE']) > 0)
{
	$request['START_DATE'] = $request['PARAMS']['DEAL_DATEPICKER_BEFORE'];
}

if ($request['ACTION'] === "GET_DEAL_HINT")
{
	if (!Manager::isAllowedExpose(Manager::DEAL))
		__CrmRecurringFieldEditEndResponse(array('DATA' => array('HINT' => $hint)));

	$dealDataRaw = Thurly\Crm\DealRecurTable::getlist(
		array(
			'filter' => array(
				'LOGIC' => 'OR',
				array(
					'=DEAL_ID' => (int)$request['PARAMS']['ID'],
					'!=NEXT_EXECUTION' => NULL
				),
				'=BASED_ID' => (int)$request['PARAMS']['ID'],
			),
			'select' => array('NEXT_EXECUTION', 'DEAL_ID', 'BASED_ID')
		)
	);
	while ($dealData = $dealDataRaw->fetch())
	{
		if ((int)($dealData['DEAL_ID']) === (int)$request['PARAMS']['ID'])
		{
			$hint = GetMessage('NEXT_EXECUTION_DEAL_HINT', array("#DATE_EXECUTION#" => $dealData['NEXT_EXECUTION']));
		}
		elseif ((int)($dealData['BASED_ID']) === (int)$request['PARAMS']['ID'])
		{
			$dealIdList[] = $dealData['DEAL_ID'];
		}
	}

	if (!empty($dealIdList))
	{
		if (count($dealIdList) === 1)
		{
			$hint = GetMessage('NEXT_BASED_ON_DEAL_ONCE', array("#ID#" => $dealIdList[0]));
		}
		else
		{
			$idLine = "";
			foreach ($dealIdList as $id)
			{
				$idLine .= \Thurly\Main\Localization\Loc::getMessage('SIGN_NUM_WITH_DEAL_ID', array("#DEAL_ID#" => $id)).", ";
			}
			$idLine = substr($idLine, 0, -2);
			$hint = \Thurly\Main\Localization\Loc::getMessage('NEXT_BASED_ON_DEAL_MULTY', array("#ID_LINE#" => $idLine));
		}
	}

	if (empty($hint))
	{
		$hint = \Thurly\Main\Localization\Loc::getMessage('NEXT_DEAL_EMPTY');
	}

	__CrmRecurringFieldEditEndResponse(array('DATA' => array('HINT' => $hint)));
}

$startDate = (strlen($request['START_DATE']) > 0) ? $request['START_DATE'] : null;
$startDate = new Date($startDate);
$today = new Date();
if (strlen($request['LAST_EXECUTION']) > 0 && (int)$request['ENTITY_TYPE'] !== \CCrmOwnerType::Deal)
{
	$lastExecution = new Date($request['LAST_EXECUTION']);
	$startDate = $startDate->getTimestamp() > $lastExecution->getTimestamp() ? $startDate : $lastExecution->add('+1 day');
}
if ($today->getTimestamp() > $startDate->getTimestamp())
{
	$startDate = clone($today);
}

$nextExecuteDate = Calculator::getNextDate($request['PARAMS'], $startDate);

if ($nextExecuteDate instanceof Date && $today->getTimestamp() > $nextExecuteDate->getTimestamp())
{
	$nextExecuteDate = $today;
}

__CrmRecurringFieldEditEndResponse(array('RESULT' => array('NEXT_DATE' => $nextExecuteDate)));

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');
?>