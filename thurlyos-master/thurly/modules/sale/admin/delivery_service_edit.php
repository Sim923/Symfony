<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");

use Thurly\Sale\Delivery;
use Thurly\Main\Page\Asset;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Delivery\Services;
use Thurly\Sale\Delivery\ExtraServices;
use Thurly\Currency;

use \Thurly\Sale\Helpers\Admin\BusinessValueControl;

Loc::loadMessages(__FILE__);
\Thurly\Main\Loader::includeModule('sale');

/** @var  CMain $APPLICATION */
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_DSE_ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/prolog.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/sale/lib/helpers/admin/businessvalue.php');

$ID = isset($_REQUEST["ID"]) ? intval($_REQUEST["ID"]) : 0;
$srvStrError = "";
$fields = array();
$tabControlName = "tabControl";
$isItSavingProcess = ($_SERVER['REQUEST_METHOD'] == "POST" && (strlen($_POST["save"]) > 0 || strlen($_POST["apply"]) > 0)) ? true : false;
$isItReloadingProcess = ($_SERVER['REQUEST_METHOD'] == "POST" && (!isset($_POST["save"]) && !isset($_POST["apply"]))) ? true : false;
$isItViewProcess = $_SERVER['REQUEST_METHOD'] != "POST";
$classNamesList = Services\Manager::getHandlersList();
$disableButtonsFlag = false;
$backUrlReq = !empty($_REQUEST["back_url"]) ? str_replace("mode=list", "", $_REQUEST["back_url"]) : '';
$backUrl = urlencode($APPLICATION->GetCurPageParam("", array("mode")));

/*
 * Process form fields received via POST
 */
if (($isItReloadingProcess || $isItSavingProcess) && $saleModulePermissions == "W" && check_thurly_sessid())
{
	if(isset($_POST["ID"]))             $fields["ID"] = intval($_POST["ID"]);
	if(isset($_POST["CODE"]))           $fields["CODE"] = trim($_POST["CODE"]);
	if(isset($_POST["SORT"]))           $fields["SORT"] = intval($_POST["SORT"]);
	if(isset($_POST["NAME"]))           $fields["NAME"] = trim($_POST["NAME"]);
	if(isset($_POST["VAT_ID"]))         $fields["VAT_ID"] = intval($_POST["VAT_ID"]);
	if(isset($_POST["CONFIG"]))         $fields["CONFIG"] = $_POST["CONFIG"];
	if(isset($_POST["CURRENCY"]))       $fields["CURRENCY"] = trim($_POST["CURRENCY"]);
	if(isset($_POST["PARENT_ID"]))      $fields["PARENT_ID"] = intval($_POST["PARENT_ID"]);
	if(isset($_POST["CLASS_NAME"]))     $fields["CLASS_NAME"] = trim($_POST["CLASS_NAME"]);
	if(isset($_POST["DESCRIPTION"]))    $fields["DESCRIPTION"] = htmlspecialcharsback(trim($_POST["DESCRIPTION"]));

	if(isset($_POST["TRACKING_PARAMS"]) && is_array($_POST["TRACKING_PARAMS"]))
		$fields["TRACKING_PARAMS"] = $_POST["TRACKING_PARAMS"];
	else
		$fields["TRACKING_PARAMS"] = array();

	if(isset($_POST["CHANGED_FIELDS"]) && is_array($_POST["CHANGED_FIELDS"]))
		$changedFields = $_POST["CHANGED_FIELDS"];
	else
		$changedFields = array();

	if(isset($_POST["ACTIVE"]) && $_POST["ACTIVE"] == "Y")
		$fields["ACTIVE"] = "Y";
	else
		$fields["ACTIVE"] = "N";

	if(isset($_POST["ALLOW_EDIT_SHIPMENT"]) && $_POST["ALLOW_EDIT_SHIPMENT"] == "Y")
		$fields["ALLOW_EDIT_SHIPMENT"] = "Y";
	else
		$fields["ALLOW_EDIT_SHIPMENT"] = "N";

	$needSaveLogo = false;

	if(!empty($_POST["LOGOTIP_del"]) && $_POST["LOGOTIP_del"] == 'Y')
	{
		$fields["LOGOTIP"]["del"] = trim($_POST["LOGOTIP_del"]);
		$needSaveLogo = true;
	}
	elseif(!empty($_FILES["LOGOTIP"]) && is_array($_FILES["LOGOTIP"]) && $_FILES["LOGOTIP"]["error"] == 0)
	{
		$imageFileError = CFile::CheckImageFile($_FILES["LOGOTIP"]);

		if (is_null($imageFileError))
		{
			$fields["LOGOTIP"] = $_FILES["LOGOTIP"];
			$needSaveLogo = true;
		}
		else
		{
			$srvStrError .= $imageFileError . ".<br>";
		}
	}

	if($needSaveLogo)
	{
		$fields["LOGOTIP"]["MODULE_ID"] = "sale";
		CFile::SaveForDB($fields, "LOGOTIP", "sale/delivery/logotip");
	}
	elseif(isset($_POST["LOGOTIP_FILE_ID"]) && intval($_POST["LOGOTIP_FILE_ID"]))
	{
		$fields["LOGOTIP"] = intval($_POST["LOGOTIP_FILE_ID"]);
	}

	if ($isItSavingProcess)
	{
		if(strlen($fields["NAME"]) <=0 )
			$srvStrError .= Loc::getMessage("SALE_DSE_ERROR_NO_NAME")."<br>";

		if(strlen($fields["CLASS_NAME"]) <=0 )
			$srvStrError .= Loc::getMessage("SALE_DSE_ERROR_NO_CLASS_NAME")."<br>";

		if($srvStrError == '')
		{
			try
			{
				$service = Services\Manager::createObject($fields);

				if($service)
					$fields = $service->prepareFieldsForSaving($fields);
				else
					$srvStrError = Loc::getMessage('SALE_DSE_DELIVERY_SERVICE_CREATE_ERROR');
			}
			catch(\Thurly\Main\SystemException $e)
			{
				$srvStrError = $e->getMessage();
			}

			if($srvStrError == '')
			{
				if(isset($fields["PARENT_ID"]) && $fields["PARENT_ID"] == "new" && strlen($_POST["GROUP_NAME"]) > 0)
				{
					$fields["PARENT_ID"] = Services\Manager::getGroupId($_POST["GROUP_NAME"]);

					if($fields["PARENT_ID"] <=0)
						$srvStrError .= Loc::getMessage("SALE_DSE_ERROR_GROUP_SAVE")."<br>";
				}

				unset($fields["ID"]);

				if ($ID > 0)
				{
					$res = Services\Manager::update($ID, $fields);

					if ($res->isSuccess())
					{
						// update some fields in children if need
						if(!empty($changedFields))
						{
							$fieldsList = array();

							if(in_array('ACTIVE', $changedFields))
							{
								if($fields['ACTIVE'] == 'Y')
									$fieldsList['ACTIVE'] = 'Y';
								else
									$fieldsList['ACTIVE'] = 'N';
							}

							if(!empty($fieldsList))
							{
								Services\Manager::setChildrenFieldsValues(
									$ID,
									$fieldsList
								);
							}
						}
					}
					else
					{
						$srvStrError .= Loc::getMessage("SALE_DSE_ERROR_EDIT_DELIVERY")."<br>".implode("<br>",$res->getErrorMessages());
					}
				}
				else
				{
					$res = Services\Manager::add($fields);

					if ($res->isSuccess())
					{
						$ID = $res->getId();

						if(!$fields["CLASS_NAME"]::isInstalled())
							$fields["CLASS_NAME"]::install();
					}
					else
					{
						$srvStrError .= Loc::getMessage("SALE_DSE_ERROR_ADD_DELIVERY")."<br>".implode("<br>",$res->getErrorMessages());
					}
				}

				if($res->isSuccess())
				{
					if($service && $consumers = $service->onGetBusinessValueConsumers())
					{
						$businessValueControl = new BusinessValueControl('DELIVERY_'.$service->getId());

						if ($businessValueControl->setMapFromPost())
						{
							if (!$businessValueControl->saveMap())
								$srvStrError .= 'Can\'t save business values';
						}
					}
				}

				if($ID > 0)
				{
					//stores
					unset($res);
					if(isset($_POST["STORES_SHOW"]) && $_POST["STORES_SHOW"] == "Y" && isset($_POST["STORES"]["PARAMS"]["STORES"]))
					{
						$res = ExtraServices\Manager::saveStores(
							$ID,
							Thurly\Sale\Delivery\ExtraServices\Store::getStoresIdsFromParams(
								$_POST["STORES"]["PARAMS"]
							)
						);
					}
					else
					{
						$res = ExtraServices\Manager::setStoresUnActive($ID);
					}

					if(!$res->isSuccess())
						$srvStrError .= implode("<br>\n", $res->getErrorMessages());
				}
			}
		}

		if(strlen($srvStrError) <= 0)
		{
			if (strlen($_POST["apply"]) > 0)
			{
				$paramsToKill = array("ID");

				if(!empty($_REQUEST["RESET_TARIF_SETTINGS"]))
					$paramsToKill[] = "RESET_TARIF_SETTINGS";

				$redirectUrl = $APPLICATION->GetCurPageParam(
					"ID=".$ID,
					$paramsToKill
				);

				if(isset($_REQUEST[$tabControlName."_active_tab"]))
					$redirectUrl .= "&".$tabControlName."_active_tab=".$_REQUEST[$tabControlName."_active_tab"];

				LocalRedirect($redirectUrl);
			}
			elseif(strlen($_POST["save"]) > 0)
			{
				LocalRedirect((!empty($backUrlReq) ? $backUrlReq : "sale_delivery_service_list.php?lang=".LANG."&filter_group=".$fields["PARENT_ID"]));
			}
		}
	}
}

/*
 * If errors or !$_POST
 * Fill form fields by data from table
 */
if(empty($fields) && $ID <= 0)
{
	$fields["PARENT_ID"] = $_REQUEST["PARENT_ID"] ? intval($_REQUEST["PARENT_ID"]) : 0;
	$fields["PROFILE_ID"] = $_REQUEST["PROFILE_ID"] ? htmlspecialcharsbx($_REQUEST["PROFILE_ID"]) : "";
	$fields["SERVICE_TYPE"] = $_REQUEST["SERVICE_TYPE"] ? htmlspecialcharsbx($_REQUEST["SERVICE_TYPE"]) : "";
	$fields["CURRENCY"] = COption::GetOptionString("sale", "default_currency", "RUB");
	$fields["RIGHTS"] = "YYY"; //Admin Manager Client
	$fields["ACTIVE"] = "Y";

	if(!empty($_REQUEST["CLASS_NAME"]))
	{
		if(!is_subclass_of($_REQUEST["CLASS_NAME"], 'Thurly\Sale\Delivery\Services\Base'))
			throw new \Thurly\Main\SystemException('Class"'.$_REQUEST["CLASS_NAME"].'" is not a child of Thurly\Sale\Delivery\Services\Base');

		$fields["CLASS_NAME"] = $_REQUEST["CLASS_NAME"];
	}
}

$serviceConfig = array();
$canHasProfiles = false;
$showRestrictions = true;
$showExtraServices = false;
$additionalTabs = array();
$parentService = null;
$showFieldsList = \Thurly\Sale\Delivery\Services\Table::getMap();

/* saving or updating Extra service & restrictions */
if($ID > 0 && ($_SERVER['REQUEST_METHOD'] != "POST" || $isItSavingProcess))
{
	$dbRes = \Thurly\Sale\Delivery\Services\Table::getById($ID);

	if(!$fields = $dbRes->fetch())
		$srvStrError .= str_replace("#ID#", $ID, Loc::getMessage("SALE_DSE_ERROR_ID"))."<br>";
}

/* If action is copying */
if($_REQUEST["action"] == "copy")
{
	$ID = 0;
	unset($fields["ID"]);
}
elseif($_REQUEST["action"] == "profile_delete")
{
	$idProf = isset($_REQUEST["ID_PROF"]) ? intval($_REQUEST["ID_PROF"]) : 0;

	if($idProf > 0)
	{
		$res = Services\Manager::delete($idProf);

		if(!$res->isSuccess())
			$srvStrError .= implode("<br>\n", $res->getErrorMessages())."<br>";
	}
	else
	{
		$srvStrError .= Loc::getMessage("SALE_DSE_PROFILE_DEL_ERROR", array("#ID#" => $idProf))."<br>";
	}
}

/* Ask parent service witch class_names for children are allowed */
if(intval($fields["PARENT_ID"]) > 0)
{
	$parentService = Services\Manager::getObjectById($fields["PARENT_ID"]);

	if($parentService)
		$classNamesList = $parentService->getChildrenClassNames();
}
else /* get all available */
{
	$classesToExclude = array(
		'\Thurly\Sale\Delivery\Services\AutomaticProfile',
		'\Thurly\Sale\Delivery\Services\Group'
	);

	foreach($classesToExclude as $class)
	{
		$key = array_search($class, $classNamesList);

		if($key !== false)
			unset($classNamesList[$key]);
	}
}

/* if we have only one class - let's fix it */
if(empty($fields["CLASS_NAME"]) && count($classNamesList) == 1)
	$fields["CLASS_NAME"] = current($classNamesList);

$isGroup = $fields["CLASS_NAME"] == '\Thurly\Sale\Delivery\Services\Group';

$service = null;

if((isset($fields["CLASS_NAME"]) && strlen($fields["CLASS_NAME"]) > 0) || $parentService)
{
	/* We must convert handler config from post as it was taken from database */
	if($isItSavingProcess && strlen($srvStrError) > 0)
	{
		try
		{
			$service = Services\Manager::createObject($fields);

			if($service)
				$fields = $service->prepareFieldsForSaving($fields);
		}
		catch(\Thurly\Main\SystemException $e){}
	}

	if($parentService && get_class($parentService) != 'Thurly\Sale\Delivery\Services\Group')
	{
		$service = $parentService->createProfileObject($fields);
		$fields['CLASS_NAME'] = get_class($service);
	}
	else
	{
		$service = Services\Manager::createObject($fields);
	}

	if($service)
	{
		$res = $service->execAdminAction();

		if(!$res->isSuccess())
			$srvStrError = implode("<br>\n", $res->getErrorMessages())."<br>";

		$fields = $service->prepareFieldsForUsing($fields);

		try
		{
			$serviceConfig = $service->getConfig();
		}
		catch(\Thurly\Main\SystemException $e)
		{
			$srvStrError .= "<br>\n".$e->getMessage()."<br>";
			$disableButtonsFlag = true;
		}

		$showRestrictions = $service->whetherAdminRestrictionsShow();
		$showExtraServices = $service->whetherAdminExtraServicesShow();
		$additionalTabs = $service->getAdminAdditionalTabs();
		$showFieldsList = $service->getAdminFieldsList();
		$canHasProfiles = $service->canHasProfiles() && ($ID > 0);

		if($ID <= 0)
		{
			if(strlen($fields["PROFILE_ID"]) > 0 || strlen($fields["SERVICE_TYPE"]) > 0)
			{
				$fields["NAME"] = $service->getName();
				$fields["DESCRIPTION"] = $service->getDescription();
				$fields["LOGOTIP"] = $service->getLogotip();
			}

			if(strlen($fields["NAME"]) <= 0)
				$fields["NAME"] = $service->getClassTitle();

			if(strlen($fields["DESCRIPTION"]) <= 0)
				$fields["DESCRIPTION"] = $service->getClassDescription();
		}
	}
}

if(strlen($fields["DESCRIPTION"]) > 0)
{
	$CBXSanitizer = new \CBXSanitizer;
	$CBXSanitizer->SetLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
	$fields["DESCRIPTION"] = $CBXSanitizer->SanitizeHtml($fields["DESCRIPTION"]);
}

$serviceCurrency = $fields["CURRENCY"];
if(\Thurly\Main\Loader::includeModule('currency'))
{
	$currencyList = Currency\CurrencyManager::getCurrencyList();
	if (isset($currencyList[$fields["CURRENCY"]]))
		$serviceCurrency = $currencyList[$fields["CURRENCY"]];
	unset($currencyList);
}

$aTabs = array(
	array(
		"DIV" => "edit_main",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_GENERAL"),
		"ICON" => "sale",
		"TITLE" => $isGroup ? Loc::getMessage("SALE_DSE_TAB_GROUP_GENERAL") : Loc::getMessage("SALE_DSE_TAB_DELIVERY_GENERAL")
	)
);

/* from service config */
foreach($serviceConfig as $sectionKey => $serviceSection)
{
	$aTabs[] = array(
		"DIV" => "edit_".$sectionKey,
		"TAB" => $serviceSection["TITLE"],
		"ICON" => "sale",
		"TITLE" => $serviceSection["DESCRIPTION"]
	);
}

if($canHasProfiles)
{
	$aTabs[] = array(
		"DIV" => "edit_profiles",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_PROFILES"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_TAB_PROFILES_DESCR"),
	);
}

if($service && $showRestrictions && $ID > 0)
{
	$aTabs[] = array(
		"DIV" => "edit_restriction",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_RESTRICTIONS"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_TAB_RESTRICTIONS_DESCR")
	);
}

if($showExtraServices && $ID > 0)
{
	$aTabs[] = array(
		"DIV" => "edit_extraservices",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_EXTRA_SERVICES"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_TAB_EXTRA_SERVICES_DESCR"),
	);
}

$isTrackingTabShow = $service && $ID > 0 && strlen($service->getTrackingClass()) > 0 && !$service->isTrackingInherited();

if($isTrackingTabShow)
{
	$aTabs[] = array(
		"DIV" => "edit_tracking",
		"TAB" => Loc::getMessage("SALE_DSE_TAB_TRACKING"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_TAB_TRACKING_DESCR"),
	);
}

if($service && $ID > 0)
	$businessValueConsumers = $service->onGetBusinessValueConsumers();
else
	$businessValueConsumers = array();

if($service && $ID > 0 && !empty($businessValueConsumers))
{
	$aTabs[] = array(
		"DIV" => "edit_business_value",
		"TAB" => Loc::getMessage("SALE_DSE_BUSINESS_VALUES"),
		"ICON" => "sale",
		"TITLE" => Loc::getMessage("SALE_DSE_BUSINESS_VALUES")
	);
}

if($service && is_array($additionalTabs) && !empty($additionalTabs) && $ID > 0)
{
	$i = 0;

	foreach($additionalTabs as $tab)
	{
		if(!isset($tab["TAB"]))
			throw new \Thurly\Main\ArgumentNullException('additionalTabs["TAB"]');

		$aTabs[] = array(
			"DIV" => "edit_additional_tab_".$i++,
			"TAB" => $tab["TAB"],
			"ICON" => "sale",
			"TITLE" => $tab["TITLE"]
		);
	}
}
$tabControl = new CAdminTabControl("tabControl", $aTabs);

/* Profiles */
if($canHasProfiles)
{
	$sTableIDSubService = "tbl_sale_delivery_subservice";
	$oSortSubService = new CAdminSorting($sTableIDSubService);
	$lAdminSubServices = new CAdminList($sTableIDSubService, $oSortSubService);

	$dbSubServicesRes = \Thurly\Sale\Delivery\Services\Table::getList(array(
		"filter" => array(
			"PARENT_ID" => $ID
		),
		"select" => array(
			"ID", "NAME", "ACTIVE", "LOGOTIP", "PARENT_ID"
		),
		"order" => isset($_REQUEST["by"]) && isset($_REQUEST["order"]) ? array($_REQUEST["by"] => $_REQUEST["order"]) : array("NAME" => "ASC")
	));

	$profilesList = new CAdminResult($dbSubServicesRes, $sTableIDSubService);
	$profilesList->NavStart();
	$lAdminSubServices->NavText($profilesList->GetNavPrint(Loc::getMessage('SALE_DSE_TAB_PROFILES')));

	$profileHeader = array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
		array("id"=>"NAME", "content"=>Loc::getMessage("SALE_DSE_PROF_HEAD_NAME"), "sort"=>"NAME", "default"=>true),
		array("id"=>"ACTIVE", "content"=>Loc::getMessage("SALE_DSE_PROF_HEAD_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
		array("id"=>"LOGOTIP", "content"=>Loc::getMessage("SALE_DSE_PROF_HEAD_LOGOTIP"), "sort"=>"LOGOTIP", "default"=>true)
	);

	$lAdminSubServices->AddHeaders($profileHeader);

	while ($profileParams = $profilesList->NavNext(true, "f_"))
	{
		$actUrl = "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$f_PARENT_ID."&ID=".$f_ID.'&'.$tabControl->ActiveTabParam()."&back_url=".$backUrl;
		$row =& $lAdminSubServices->AddRow($f_ID, $profileParams, $actUrl, Loc::getMessage("SALE_DSE_EDIT_DESCR"));

		$row->AddField("NAME", '<a href="'.$actUrl.'" class="adm-list-table-icon-link">'.
				'<span class="adm-list-table-link">'.
					$f_NAME.
				'</span>'.
			'</a>');

		$row->AddField("ID", $f_ID);

		$logoHtml = intval($f_LOGOTIP) > 0 ? CFile::ShowImage(CFile::GetFileArray($f_LOGOTIP), 150, 150, "border=0", "", false) : "";
		$row->AddField("LOGOTIP", $logoHtml);
		$row->AddField("ACTIVE", (($f_ACTIVE=="Y") ? Loc::getMessage("SALE_DSE_YES") : Loc::getMessage("SALE_DSE_NO")));
		$row->AddField("CLASS_NAME", $f_CLASS_NAME);

		$arActions = Array();
		$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("SALE_DSE_COPY"), "ACTION"=>$lAdminSubServices->ActionRedirect("sale_delivery_service_edit.php?lang=".LANG."&ID=".$f_ID."&action=copy&back_url=".$backUrl), "DEFAULT"=>true);
		$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("SALE_DSE_EDIT_DESCR"), "ACTION"=>$lAdminSubServices->ActionRedirect("sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$f_PARENT_ID."&ID=".$f_ID."&back_url=".$backUrl), "DEFAULT"=>true);

		if ($saleModulePermissions >= "W")
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("SALE_DSE_DELETE"), "ACTION"=>"if(confirm('".Loc::getMessage('SALE_DSE_CONFIRM_DEL_PROFILE_MESSAGE')."')) ".$lAdminSubServices->ActionRedirect("sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$fields["PARENT_ID"]."&ID=".$ID."&action=profile_delete&ID_PROF=".$f_ID));
		}

		$row->AddActions($arActions);
	}

	if ($saleModulePermissions == "W")
	{
		foreach($service->getProfilesList() as $profileId => $profileName)
		{
			$menu[] = array(
				"TEXT" => $profileName,
				"LINK" => "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$ID."&PROFILE_ID=".htmlspecialcharsbx($profileId)."&back_url=".$backUrl,
			);
		}

		if(!empty($menu))
		{
			$aContext = array(
				array(
					"TEXT" => Loc::getMessage("SALE_DSE_ADD_NEW_PROFILE"),
					"LINK" => "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$ID."&back_url=".$backUrl,
					"TITLE" => Loc::getMessage("SALE_DSE_ADD_NEW_PROFILE_TITLE"),
					"MENU" => $menu,
					"ICON" => "btn_new"
				)
			);

			$lAdminSubServices->AddAdminContextMenu($aContext, false);
		}

	}

	if($_REQUEST["table_id"]==$sTableIDSubService)
		$lAdminSubServices->CheckListMode();
}
/* profiles end */

Asset::getInstance()->addJs("/thurly/js/sale/delivery.js");

if($parentService && get_class($parentService) != 'Thurly\Sale\Delivery\Services\Group')
{
	if($ID > 0)
	{
		$sDocTitle = str_replace(
			array("#NAME#", "#PARENT_NAME#"),
			array($fields["NAME"], $parentService->getName()),
			Loc::getMessage("SALE_DSE_EDIT_RECORD_PROFILE")
		);
	}
	else
	{
		reset($serviceConfig);
		$_REQUEST[$tabControl->name."_active_tab"] = 'edit_'.key($serviceConfig);

		$sDocTitle = str_replace(
			"#PARENT_NAME#",
			$parentService->getName(),
			Loc::getMessage("SALE_DSE_NEW_RECORD_PROFILE")
		);
	}
}
else
{
	if($isGroup)
	{
		if($ID > 0)
			$sDocTitle = str_replace("#NAME#", $fields["NAME"], Loc::getMessage("SALE_DSE_EDIT_GROUP"));
		else
			$sDocTitle = Loc::getMessage("SALE_DSE_NEW_GROUP");
	}
	else
	{
		if($ID > 0)
			$sDocTitle = str_replace("#NAME#", $fields["NAME"], Loc::getMessage("SALE_DSE_EDIT_RECORD"));
		else
			$sDocTitle = Loc::getMessage("SALE_DSE_NEW_RECORD");
	}
}

$APPLICATION->SetTitle($sDocTitle);

if($service && $showRestrictions && $ID > 0)
{
	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/admin/delivery_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();
}
else
{
	$restrictionsHtml = "";
}

if($showExtraServices && $ID > 0)
{
	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/admin/delivery_eservice_list.php");
	$extraServicesHtml = ob_get_contents();
	ob_end_clean();
}
else
{
	$extraServicesHtml = "";
}

$vatList = array(
	0 => Loc::getMessage('SALE_DSE_FORM_NO_VAT')
);

if(\Thurly\Main\Loader::includeModule('catalog'))
{
	$dbRes = \Thurly\Catalog\VatTable::getList(array(
		'filter' => array('ACTIVE' => 'Y'),
		'order' => array('SORT' => 'ASC')
	));

	while($vat = $dbRes->fetch())
		$vatList[$vat['ID']] = $vat['NAME'];
}

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_after.php");

\Thurly\Sale\Internals\Input\Manager::initJs();

?>
<script language="JavaScript">
	BX.message({
		SALE_DSE_GROUP_NAME: '<?=Loc::getMessage("SALE_DSE_GROUP_NAME")?>',
		SALE_DSE_GROUP_CREATE: '<?=Loc::getMessage("SALE_DSE_GROUP_CREATE")?>',
		SALE_DSE_GROUP_CREATE_G: '<?=Loc::getMessage("SALE_DSE_GROUP_CREATE_G")?>',
		SALE_RDL_RESTRICTION: '<?=Loc::getMessage("SALE_RDL_RESTRICTION")?>',
		SALE_RDL_SAVE: '<?=Loc::getMessage("SALE_RDL_SAVE")?>'
	});

	BX.addCustomEvent('onDeliveryServiceNameChange', function(params){

		if(typeof params.name != 'undefined')
		{
			var iname = BX('adm-sale-delivery-name');

			if(iname)
				 iname.value = params.name;
		}

		if(typeof params.description != 'undefined')
		{
			if(window.JCLightHTMLEditor.items['hndl_dscr'])
				window.JCLightHTMLEditor.items['hndl_dscr'].SetEditorContent(params.description);
		}
	});

</script>
<?
if(!empty($backUrlReq))
	$link = $backUrlReq;
elseif($isGroup)
	$link = "/thurly/admin/sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&filter_class_name=".urlencode('\Thurly\Sale\Delivery\Services\Group');
else
	$link = "/thurly/admin/sale_delivery_service_list.php?lang=".LANGUAGE_ID."&filter_group=".$fields["PARENT_ID"];

if($isGroup)
	$linkText = Loc::getMessage("SALE_DSE_2GLIST");
elseif($service && $service->isProfile())
	$linkText = Loc::getMessage("SALE_DSE_2DS_EDIT");
else
	$linkText = Loc::getMessage("SALE_DSE_2DLIST");

$aMenu = array(
	array(
		"TEXT" => $linkText,
		"LINK" => $link,
		"ICON" => "btn_list"
	)
);

if ($ID > 0 && $saleModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	if($isGroup)
		$link = "/thurly/admin/sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&CLASS_NAME=".urlencode('\Thurly\Sale\Delivery\Services\Group');
	else
		$link = "/thurly/admin/sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&PARENT_ID=".$fields["PARENT_ID"];

	$aMenu[] = array(
		"TEXT" => $isGroup ? Loc::getMessage("SALE_DSE_NEW_GROUP") : Loc::getMessage("SALE_DSE_NEW_DELIVERY"),
		"LINK" => $link,
		"ICON" => "btn_new"
	);

	$aMenu[] = array(
		"TEXT" => $isGroup ? Loc::getMessage("SALE_DSE_DELETE_GROUP") : Loc::getMessage("SALE_DSE_DELETE_DELIVERY"),
		"LINK" => "javascript:if(confirm('".
			($isGroup ? Loc::getMessage("SALE_DSE_DELETE_GROUP_CONFIRM") : Loc::getMessage("SALE_DSE_DELETE_DELIVERY_CONFIRM")).
			"')) window.location='/thurly/admin/sale_delivery_service_list.php?lang=".LANGUAGE_ID."&filter_group=".$fields["PARENT_ID"]."&ID=".$ID."&action=delete&".thurly_sessid_get()."#tb';",
		"ICON" => "btn_delete"
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

//warn about unfilled required fields
if($ID > 0)
{
	if(is_array($serviceConfig) && !empty($serviceConfig))
	{
		foreach($serviceConfig as $sectionKey => $configSection)
		{
			if(is_array($configSection["ITEMS"]) && !empty($configSection["ITEMS"]))
			{
				foreach($configSection["ITEMS"] as $name => $params)
				{
					if(!empty($params['REQUIRED']) && $params['REQUIRED'] == true && strlen($params['VALUE']) <= 0)
						$srvStrError .= Loc::getMessage('SALE_DSE_REQUIRED_FIELD').' "'.$params['NAME'].'".<br>';
				}
			}
		}
	}
}

if(strlen($srvStrError) > 0)
{
	$m = Array("DETAILS"=>$srvStrError, "TYPE"=>"ERROR", "HTML"=>true);

	if($isItSavingProcess)
		$m["MESSAGE"] = Loc::getMessage("SALE_DSE_ERROR");

	$adminMessage = new CAdminMessage($m);
	echo $adminMessage->Show();
}

if($service)
	$serviceMessage = $service->getAdminMessage();

if(!empty($serviceMessage))
{
	$adminMessage = new CAdminMessage($serviceMessage);
	echo $adminMessage->Show();
}

?>
<form method="POST" action="<?=$APPLICATION->GetCurPageParam("",array("RESET_HANDLER_SETTINGS", "action"))?>" name="form1" enctype="multipart/form-data">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID; ?>">
<input type="hidden" name="ID" value="<?=$ID ?>">
<input type="hidden" name="CODE" value="<?=(isset($fields["CODE"]) ? htmlspecialcharsbx($fields["CODE"]) : "" )?>">
<input type="hidden" name="PARENT_ID" value="<?=(isset($fields["PARENT_ID"]) ? $fields["PARENT_ID"] : "0" )?>">
<?=thurly_sessid_post()?>

<?if(is_array($fields)):?>
	<?foreach($fields as $fieldName => $fieldValue): /* if fields don't show let's make them hidden */?>
		<?if(!is_array($fieldValue) && strlen($fieldValue) > 0 && !array_key_exists($fieldName, $showFieldsList)):?>
			<input type="hidden" name="<?=$fieldName?>" value="<?=$fieldValue?>">
		<?endif;?>
	<?endforeach;?>
<?endif;?>

<?
$tabControl->Begin();
/* General settings */
$tabControl->BeginNextTab();
	if($ID>0 && array_key_exists("ID", $showFieldsList)):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("NAME", $showFieldsList)):?>
		<tr class="adm-detail-required-field">
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_NAME")?>:</td>
			<td width="60%"><input id='adm-sale-delivery-name' type="text" name="NAME" value="<?=(isset($fields["NAME"]) ? htmlspecialcharsbx($fields["NAME"]) : "" )?>" size="40"></td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("CLASS_NAME", $showFieldsList)):?>
		<tr class="adm-detail-required-field">
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_CLASS_NAME")?>:</td>
			<td width="60%">
				<?if(count($classNamesList) > 1 && (strlen($fields["CLASS_NAME"]) <= 0 )):?>
					<select name="CLASS_NAME" onchange="if(this.value == '') return; top.BX.showWait(); this.form.submit(); /*elements.apply.click();*/">
						<option value=""></option>
						<?foreach($classNamesList as $className):?>
							<option value="<?=$className?>" <?=(isset($fields["CLASS_NAME"]) && $className == $fields["CLASS_NAME"] ? " selected" : "" )?>><?=$className::getClassTitle()." [".$className."]"?></option>
						<?endforeach;?>
					</select>
				<?else:?>
					<?=class_exists($fields["CLASS_NAME"]) ? $fields["CLASS_NAME"]::getClassTitle() : $fields["CLASS_NAME"]?>
					<input type="hidden" name="CLASS_NAME" value="<?=$fields["CLASS_NAME"]?>">
				<?endif;?>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("ACTIVE", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_ACTIVE")?>:</td>
			<td width="60%"><input type="checkbox" name="ACTIVE" value="Y" <?if ($fields["ACTIVE"]=="Y") echo "checked";?> onclick="BX.Sale.Delivery.createFlagFieldChanged('ACTIVE', this);"></td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("SORT", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_SORT")?>:</td>
			<td width="60%"><input type="text" name="SORT" value="<?=(isset($fields["SORT"]) ? $fields["SORT"] : "100" )?>" size="5"></td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("DESCRIPTION", $showFieldsList)):?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><?=Loc::getMessage("SALE_DSE_FORM_DESCRIPTION")?>:</td>
			<td width="60%">
				<?=wrapDescrLHE(
					'DESCRIPTION',
					isset($fields["DESCRIPTION"]) ? $fields["DESCRIPTION"] : '',
					'hndl_dscr');?>
				<script language="JavaScript">BX.Sale.Delivery.setLHEClass('bxlhe_frame_hndl_dscr'); </script>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("PARENT_ID", $showFieldsList)):?>
		<?if($parentService && get_class($parentService) != 'Thurly\Sale\Delivery\Services\Group'):?>
			<tr>
				<td width="40%">
					<?=Loc::getMessage("SALE_DSE_FORM_PARENT_ID")?>
					:</td>
				<td width="60%">
					<a href="?LANG=<?=LANGUAGE_ID?>&PARENT_ID=<?=$parentService->getParentId()?>&ID=<?=$parentService->getId()?>"><?=htmlspecialcharsbx($parentService->getName())?></a>
				</td>
			</tr>
		<?else:?>
			<tr>
				<td width="40%">
					<?=Loc::getMessage("SALE_DSE_FORM_GROUP_ID")?>
					:</td>
				<td width="60%">
					<?=\Thurly\Sale\Delivery\Helper::getGroupChooseControl($fields["PARENT_ID"], "PARENT_ID")?> &nbsp;
					<a
						href="javascript:void(0);"
						style="border-bottom: 1px dashed; cursor: pointer; text-decoration: none;"
						onclick="BX.Sale.Delivery.createGroup();"
					>
						<?=Loc::getMessage("SALE_DSE_ADD")?>
					</a>
					<input type="hidden" name="GROUP_NAME" id="GROUP_NAME" value="">
				</td>
			</tr>
		<?endif;?>
	<?endif;?>

	<?if(array_key_exists("LOGOTIP", $showFieldsList)):?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><?=Loc::getMessage("SALE_DSE_FORM_LOGO")?>:</td>
			<td width="60%">
				<div><input type="file" name="LOGOTIP"><input type="hidden" name="LOGOTIP_FILE_ID" value="<?=$fields["LOGOTIP"]?>"></div>
				<?if(isset($fields["LOGOTIP"]) && intval($fields["LOGOTIP"]) > 0):?>
					<br>
					<?
						$arLogotip = CFile::GetFileArray($fields["LOGOTIP"]);
						echo CFile::ShowImage($arLogotip, 150, 150, "border=0", "", false);
					?>
					<br />
					<div>
						<input type="checkbox" name="LOGOTIP_del" value="Y" id="LOGOTIP_del" >
						<label for="LOGOTIP_del"><?=Loc::getMessage("SALE_DSE_LOGOTIP_DEL");?></label>
					</div>
				<?endif;?>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("CURRENCY", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_CURRENCY")?>:</td>
			<td width="60%">
				<?=CCurrency::SelectBox("CURRENCY", $fields["CURRENCY"], "", true, "");?>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("STORES", $showFieldsList)):?>
		<?$stores = ExtraServices\Manager::getStoresFields($ID, false);?>
		<?$storeClassName = ExtraServices\Manager::STORE_PICKUP_CLASS;?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_STORES_SHOW")?>:</td>
			<td width="60%">
				<input type="checkbox" name="STORES_SHOW" value="Y" <?=!empty($stores) && $stores["ACTIVE"] == "Y" ? " checked" : ""?> onchange="BX.Sale.Delivery.toggleStores();">
			</td>
		</tr>
		<tr id="sale-admin-delivery-stores"<?=!empty($stores) && $stores["ACTIVE"] == "Y" ? '' : ' style="display: none;"'?>>
			<td width="40%">
				<?=Loc::getMessage("SALE_DSE_FORM_STORES")?>:
			</td>
			<td width="60%">
				<?=$storeClassName::getAdminParamsControl("STORES", $stores)?>
			</td>
		</tr>
	<?endif;?>

	<?if(array_key_exists("ALLOW_EDIT_SHIPMENT", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_ALLOW_EDIT_SHIPMENT")?>:</td>
			<td width="60%">
				<input type="checkbox" name="ALLOW_EDIT_SHIPMENT" value="Y"<?=(isset($fields["ALLOW_EDIT_SHIPMENT"]) && $fields["ALLOW_EDIT_SHIPMENT"] == 'N' ? '' : " checked" )?>>
			</td>
		</tr>
	<?endif;?>
	<?if(array_key_exists("VAT_ID", $showFieldsList)):?>
		<tr>
			<td width="40%"><?=Loc::getMessage("SALE_DSE_FORM_VAT_ID")?>:</td>
			<td width="60%">
				<select name="VAT_ID">
					<?foreach($vatList as $vatId => $vatName):?>
						<option value="<?=$vatId?>" <?=(isset($fields["VAT_ID"]) && $vatId == $fields["VAT_ID"] ? " selected" : "" )?>><?=htmlspecialcharsbx($vatName)?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
	<?endif;?>
	<?$hiddensConfigHtml = "";?>
	<?if(is_array($serviceConfig) && !empty($serviceConfig)):?>
		<?foreach($serviceConfig as $sectionKey => $configSection):?>
			<?$tabControl->BeginNextTab();?>
			<?if(isset($configSection["ITEMS"]) && is_array($configSection["ITEMS"]) && !empty($configSection["ITEMS"])):?>
				<?foreach($configSection["ITEMS"] as $name => $params):?>
					<?if($params["TYPE"] == "DELIVERY_SECTION"):?>
						<tr class="heading">
							<td colspan="2"><?=$params["NAME"]?></td>
						</tr>
					<?elseif(isset($params['HIDDEN']) && $params['HIDDEN'] == true):?>
						<?$hiddensConfigHtml .= \Thurly\Sale\Internals\Input\Manager::getEditHtml("CONFIG[".$sectionKey."][".$name."]", $params)?>
					<?else:?>
						<tr<?=(!empty($params['REQUIRED']) && $params['REQUIRED'] == true ? ' class= "adm-detail-required-field"' : '')?>>
							<td width="40%" class="adm-detail-valign-top"><?=$params["NAME"]?>:</td>
							<td width="60%" class="adm-detail-valign-top">
								<?=\Thurly\Sale\Internals\Input\Manager::getEditHtml("CONFIG[".$sectionKey."][".$name."]", $params)?>
							</td>
						</tr>
					<?endif;?>
				<?endforeach;?>
			<?endif;?>
		<?endforeach;?>
	<?endif;?>

	<?if($canHasProfiles):?>
		<?$tabControl->BeginNextTab();?>
		<tr>
			<td colspan="2">
				<?$lAdminSubServices->DisplayList(array("FIX_HEADER" => false, "FIX_FOOTER" => false));?>
			</td>
		</tr>
	<?endif;?>

	<?if(strlen($restrictionsHtml) > 0):?>
		<?$tabControl->BeginNextTab();?>
		<tr><td id="sale-delivery-restriction-container"><?=$restrictionsHtml?></td></tr>
	<?endif;?>

	<?if($service && $showExtraServices && $ID > 0):?>
		<?$tabControl->BeginNextTab();?>
		<tr><td><?=$extraServicesHtml?></td></tr>
	<?endif;?>

	<?if($isTrackingTabShow):?>
		<?$tabControl->BeginNextTab();
			$tManager = Delivery\Tracking\Manager::getInstance();
			$tracking = $tManager->getTrackingObjectByDeliveryId($ID);
			$trackingParamsStructure = $tracking->getParamsStructure()
			?><tr>
			<td width="40%" class="adm-detail-valign-top"><?=Loc::getMessage("SALE_DSE_FORM_DESCRIPTION")?>:</td>
			<td width="60%">
				<?=$tracking->getClassDescription()?>
				<?=(empty($trackingParamsStructure) ? '<br>'.Loc::getMessage('SALE_DSE_TAB_TRACKING_PARAMS_EMPTY') : '')?>
			</td></tr>
			<?if(!empty($trackingParamsStructure)):?>
				<tr class="heading"><td colspan="2"><?=Loc::getMessage("SALE_DSE_TAB_TRACKING_PARAMS")?></td></tr>
				<?foreach($trackingParamsStructure as $id => $params):?>
					<tr>
						<td width="40%"><?=$params["LABEL"]?>:</td>
						<td width="60%">
							<?=$tracking->getEditHtml($id,"TRACKING_PARAMS[".$id."]")?>
						</td>
					</tr>
				<?endforeach;?>
			<?endif;?>
	<?endif;?>

	<?if($service && $ID > 0 && !empty($businessValueConsumers)):?>
		<?$tabControl->BeginNextTab();?>
			<tr>
				<td colspan="2">
					<?
						require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/sale/lib/helpers/admin/businessvalue.php');
						$businessValueControl = new BusinessValueControl('DELIVERY_'.$service->getId());
						$businessValueControl->renderMap(
							array(
								'CONSUMER_KEY' => 'DELIVERY_'.$service->getId(),
								'HIDE_FILLED_CODES' => false
							)
						);
					?>
				</td>
			</tr>
	<?endif;?>

	<?if(is_array($additionalTabs) && !empty($additionalTabs) && $ID > 0):?>
		<?foreach($additionalTabs as $addTab):?>
			<?$tabControl->BeginNextTab();?>
			<?if(!isset($addTab["CONTENT"])) throw new \Thurly\Main\ArgumentNullException('additionalTabs["CONTENT"]');?>
			<?=$addTab["CONTENT"]?>
		<?endforeach;?>
	<?endif;

$tabControl->Buttons(
	array(
		"disabled" => ($disableButtonsFlag || $saleModulePermissions < "W"),
		"back_url" => !empty($backUrlReq) ? $backUrlReq : ("/thurly/admin/sale_delivery_service_list.php?lang=".LANGUAGE_ID)
	)
);

$tabControl->End();
?>
<?=$hiddensConfigHtml?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_admin.php");

function wrapDescrLHE($inputName, $content = '', $divId = false)
{
	ob_start();
	$ar = array(
		'inputName' => $inputName,
		'height' => '160',
		'width' => '320',
		'content' => $content,
		'bResizable' => true,
		'bManualResize' => true,
		'bUseFileDialogs' => false,
		'bFloatingToolbar' => false,
		'bArisingToolbar' => false,
		'bAutoResize' => true,
		'bSaveOnBlur' => true,
		'toolbarConfig' => array(
			'Bold', 'Italic', 'Underline', 'Strike',
			'CreateLink', 'DeleteLink',
			'Source', 'BackColor', 'ForeColor'
		)
	);

	if($divId)
		$ar['id'] = $divId;

	\Thurly\Main\Loader::includeModule('fileman');

	$LHE = new CLightHTMLEditor;
	$LHE->Show($ar);
	$sVal = ob_get_contents();
	ob_end_clean();

	return $sVal;
}