<?
define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_CHECK', true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('DisableEventsCheck', true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");

CComponentUtil::__IncludeLang(dirname($_SERVER["SCRIPT_NAME"]), "/ajax.php");

if (!CModule::IncludeModule('sale')) die(GetMessage("SMODE_SALE_NOT_INSTALLED"));
if (!CModule::IncludeModule('mobileapp')) die(GetMessage('SMODE_MOBILEAPP_NOT_INSTALLED'));

$arResult = array();
$arUserGroups = $USER->GetUserGroupArray();

$orderId = isset($_REQUEST['orderId']) ? trim($_REQUEST['orderId']) : 0;
$bUserCanDeductOrder = CSaleOrder::CanUserChangeOrderFlag($orderId, "PERM_DEDUCTION", $arUserGroups);

if($USER->IsAuthorized() && check_thurly_sessid() && $bUserCanDeductOrder)
{

	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "order_deduct":

			$deducted = isset($_REQUEST['deducted']) ? trim($_REQUEST['deducted']) : '';
			$useStores = isset($_REQUEST['useStores']) && trim($_REQUEST['useStores']) == 'Y' ? true : false;
			$undoReason = isset($_REQUEST['undoReason']) ? trim($_REQUEST['undoReason']) : '';
			$arProducts = isset($_REQUEST['products']) ? $_REQUEST['products'] : array();
			$arProducts = $APPLICATION->ConvertCharsetArray($arProducts, 'utf-8', SITE_CHARSET);
			$arStoreInfo = array();

			foreach ($arProducts as $prodId => $arProduct)
			{
				$arStoresTmp = array();
				if(isset($arProduct["STORES"]) && is_array($arProduct["STORES"]))
				{
					foreach ($arProduct["STORES"] as $arStore)
					{
						if($arProduct["BARCODE_MULTI"] == "N")
						{
							reset($arStore["BARCODE"]);
							$arStore["BARCODE"] = current($arStore["BARCODE"]);
							unset($arStore["BARCODE_FOUND"]);
						}

						if(isset($arStore["QUANTITY"]) && intval($arStore["QUANTITY"]) > 0)
							$arStoresTmp[] = $arStore;
					}
				}
				$arProducts[$prodId]["STORES"] = $arStoresTmp;
				$arStoreInfo[$prodId] = $arStoresTmp;
			}

			if ($deducted == "Y" && $useStores)
			{

				if(!CSaleOrderHelper::checkQuantity($arProducts))
				{
					if ($ex = $APPLICATION->GetException())
					{
							$arResult["ERROR"] = $ex->GetString();
							break;
					}

				}

				//check if barcodes are valid for deduction
				if(!CSaleOrderHelper::checkBarcodes($arProducts))
				{
					if ($ex = $APPLICATION->GetException())
					{
							$arResult["ERROR"] = $ex->GetString();
							break;
					}
				}

			}

			if(strlen($undoReason) > 0)
				$undoReason = $APPLICATION->ConvertCharset($undoReason, 'utf-8', SITE_CHARSET);

			if (!CSaleOrder::DeductOrder($orderId, $deducted, $undoReason, false, $arStoreInfo))
			{
				if ($ex = $APPLICATION->GetException())
				{
					if ($ex->GetID() != "ALREADY_FLAG")
						$arResult["ERROR"] = $ex->GetString();
				}
				else
					$arResult["ERROR"] = GetMessage("SMODE_ERROR_DEDUCT_ORDER");

			}

			break;
	}
}
else
{
	$arResult["ERROR"] = "Access denied!";
}

if(isset($arResult["ERROR"]))
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');
die(json_encode($arResult));
?>
