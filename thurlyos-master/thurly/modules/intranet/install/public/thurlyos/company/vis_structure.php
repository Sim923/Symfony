<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/company/vis_structure.php");
$APPLICATION->SetTitle(GetMessage("TITLE2"));
$APPLICATION->SetPageProperty("BodyClass", "page-one-column");

$APPLICATION->IncludeComponent("thurly:intranet.structure.visual", ".default", array(
	"DETAIL_URL" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PROFILE_URL" => "/company/personal/user/#ID#/",
	"PM_URL" => "/company/personal/messages/chat/#ID#/",
	"NAME_TEMPLATE" => "",
	"USE_USER_LINK" => "Y"
	),
	false
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>