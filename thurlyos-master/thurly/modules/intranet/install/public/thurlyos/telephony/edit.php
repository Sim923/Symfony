<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/telephony/edit.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("VI_PAGE_CONFIG_EDIT_TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:voximplant.config.edit", "", array());?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
