<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/telephony/editrole.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("VI_PAGE_EDIT_ROLE_TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:voximplant.settings.perms.role.edit", "", array());?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
