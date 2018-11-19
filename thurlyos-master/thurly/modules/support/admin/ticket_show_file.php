<?
/*
##############################################
# Thurly: SiteManager                        #
# Copyright (c) 2002 - 2011 Thurly           #
# http://www.thurlysoft.com                  #
# mailto:admin@thurlysoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
if(CModule::IncludeModule("support") && strlen($hash) > 0 && preg_match('/^[a-z0-9]{32}$/i', $hash))
{
	$rsFiles = CTicket::GetFileList($v1="s_id", $v2="asc", array("HASH"=>$hash), 'Y');
	if ($rsFiles && $arFile = $rsFiles->Fetch())
	{
		set_time_limit(0);

		$options = array();
		if ($_REQUEST["action"] == "download")
		{
			$options["force_download"] = true;
		}
		CFile::ViewByUser($arFile, $options);
	}
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
ShowError(GetMessage("SUP_ERROR_ATTACH_NOT_FOUND"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");?>