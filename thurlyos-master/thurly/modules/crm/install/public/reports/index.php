<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle(GetMessage("CRM_PAGE_FUNNEL"));
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.deal.funnel",
	"",
	Array(
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>