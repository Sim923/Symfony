<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/perms/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?> <?$APPLICATION->IncludeComponent(
	"thurly:crm.config.perms",
	"",
	Array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#crm/configs/perms/",
		"SEF_URL_TEMPLATES" => Array(
			"PATH_TO_ENTITY_LIST" => "",
			"PATH_TO_ROLE_EDIT" => "#role_id#/edit/"
		),
		"VARIABLE_ALIASES" => Array(
			"PATH_TO_ENTITY_LIST" => Array(),
			"PATH_TO_ROLE_EDIT" => Array(),
		)
	)
);?> <?


require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>