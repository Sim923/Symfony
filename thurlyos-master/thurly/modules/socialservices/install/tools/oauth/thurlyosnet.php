<?
/*
This is callback page for ThurlyOS.Net OAuth 2.0 authentication.
ThurlyOS.Net redirects only to specific back url set in the OAuth application.
The page opens in popup window after user authorized on ThurlyOS.Net.
*/

/**
 * Thurly vars
 *
 * @global \CUser $USER
 *
 */

define("NOT_CHECK_PERMISSIONS", true);

$arState = array();

if(isset($_REQUEST["state"]) && is_string($_REQUEST["state"]))
{
	parse_str($_REQUEST["state"], $arState);

	if(isset($arState['site_id']) && is_string($arState['site_id']))
	{
		$site = substr(preg_replace("/[^a-z0-9_]/i", "", $arState['site_id']), 0, 2);
		define("SITE_ID", $site);
	}
	elseif(isset($arState['admin']))
	{
		define('ADMIN_SECTION', true);
	}
}

require_once($_SERVER['DOCUMENT_ROOT']."/thurly/modules/main/include/prolog_before.php");

if(isset($_REQUEST["update_broadcast"]))
{
	\Thurly\Main\Config\Option::set("socialservices", "network_last_update", time());
}
else
{
	if(CModule::IncludeModule("socialservices"))
	{
		if(isset($_REQUEST['apcode']))
		{
			if($USER->IsAuthorized())
			{
				if(\Thurly\Socialservices\ApManager::receive($USER->GetID(), $_REQUEST['apcode']))
				{
					if(isset($arState['backurl']))
					{
						LocalRedirect($arState['backurl']);
					}
					elseif(defined('ADMIN_SECTION'))
					{
						LocalRedirect('/thurly/admin/');
					}
					else
					{
						LocalRedirect('/');
					}
				}
			}
		}
		else
		{
			$oAuthManager = new CSocServAuthManager();
			$oAuthManager->Authorize("ThurlyOSNet");
		}
	}
}

require_once($_SERVER['DOCUMENT_ROOT']."/thurly/modules/main/include/epilog_after.php");
?>