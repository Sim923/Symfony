<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
$APPLICATION->SetTitle("");?>
<?$APPLICATION->IncludeComponent("thurly:xdi.livefeed.point", ".default", Array());?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");
?>