<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/docs/bp_help.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));
echo GetMessage("CONTENT");?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>