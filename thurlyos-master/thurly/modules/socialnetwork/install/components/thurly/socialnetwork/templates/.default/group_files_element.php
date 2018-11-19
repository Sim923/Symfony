<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$arInfo = $APPLICATION->IncludeComponent("thurly:webdav.element.view", ".default", Array(
	"OBJECT"	=>	$arParams["OBJECT"], 
	"IBLOCK_TYPE"	=>	$arParams["FILES_GROUP_IBLOCK_TYPE"],
	"IBLOCK_ID"	=>	$arParams["FILES_GROUP_IBLOCK_ID"],
	"ROOT_SECTION_ID"	=>	$arResult["VARIABLES"]["ROOT_SECTION_ID"],
	"SECTION_ID"	=>	$arResult["VARIABLES"]["SECTION_ID"],
	"ELEMENT_ID"	=>	$arResult["VARIABLES"]["ELEMENT_ID"],
	"PERMISSION"	=>	$arResult["VARIABLES"]["PERMISSION"],
	"CHECK_CREATOR"	=>	$arResult["VARIABLES"]["CHECK_CREATOR"],
	"NAME_FILE_PROPERTY"	=>	$arParams["NAME_FILE_PROPERTY"],
	"ACTION"	=>	$arResult["VARIABLES"]["ACTION"],
	"REPLACE_SYMBOLS"	=>	$arParams["REPLACE_SYMBOLS"],
	
	"SECTIONS_URL" => $arResult["~PATH_TO_GROUP_FILES"],
	"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_SECTION_EDIT"],
	"ELEMENT_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT"],
	"ELEMENT_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_EDIT"],
	"ELEMENT_FILE_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_FILE"],
	"ELEMENT_HISTORY_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY"],
	"ELEMENT_HISTORY_GET_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY_GET"],
	"ELEMENT_VERSION_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSION"],
	"ELEMENT_VERSIONS_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSIONS"],
	"ELEMENT_UPLOAD" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_UPLOAD"],
	"HELP_URL" => $arResult["~PATH_TO_GROUP_FILES_HELP"],
	"USER_VIEW_URL" => $arResult["~PATH_TO_USER"],
	"WEBDAV_BIZPROC_HISTORY_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY"], 
	"WEBDAV_BIZPROC_HISTORY_GET_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY_GET"], 
	"WEBDAV_BIZPROC_LOG_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_LOG"], 
	"WEBDAV_BIZPROC_VIEW_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_VIEW"], 
	"WEBDAV_BIZPROC_WORKFLOW_ADMIN_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_ADMIN"], 
	"WEBDAV_BIZPROC_WORKFLOW_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_EDIT"], 
	"WEBDAV_START_BIZPROC_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_START_BIZPROC"], 
	"WEBDAV_TASK_LIST_URL" => $arResult["~PATH_TO_BIZPROC_TASK_LIST"], 
	"WEBDAV_TASK_URL" => $arResult["~PATH_TO_BIZPROC_TASK"], 
	
	"BIZPROC" => $arResult["VARIABLES"]["BIZPROC"], 
	
	"SET_TITLE"	=>	$arParams["SET_TITLE"],
	"STR_TITLE" => $arResult["VARIABLES"]["STR_TITLE"],
	"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"],
	"CACHE_TYPE"	=>	$arParams["CACHE_TYPE"],
	"CACHE_TIME"	=>	$arParams["CACHE_TIME"], 
	
	"SHOW_WORKFLOW" => "N"),
	$component,
	array("HIDE_ICONS" => "Y")
);
?><?
if ($arParams["FILES_USE_COMMENTS"]=="Y" && $arResult["GROUP"]["CLOSED"] != "Y" && 
	is_array($arInfo) && $arInfo["ELEMENT_ID"] && ($arParams['WORKFLOW'] == "bizproc" && $arInfo["ELEMENT"]["BP_PUBLISHED"] == "Y" || 
		$arParams['WORKFLOW'] != "bizproc" && $arInfo["ELEMENT"]["WF_STATUS_ID"] == 1) && IsModuleInstalled("forum")):?>
<hr />
<?$APPLICATION->IncludeComponent(
	"thurly:forum.topic.reviews",
	"",
	Array(
		"IBLOCK_TYPE"	=>	$arParams["FILES_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID"	=>	$arParams["FILES_GROUP_IBLOCK_ID"],
		"FORUM_ID" => $arParams["FILES_FORUM_ID"],
		"ELEMENT_ID" => $arInfo["ELEMENT_ID"],
		
		"URL_TEMPLATES_READ" => "",
		"URL_TEMPLATES_PROFILE_VIEW" => str_replace("#USER_ID#", "#UID#", $arResult["~PATH_TO_USER"]),
		"URL_TEMPLATES_DETAIL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT"],
		
		"SUBSCRIBE_AUTHOR_ELEMENT" => "Y", 
		"IMAGE_SIZE" => false, 
		"MESSAGES_PER_PAGE" => 20,
		"DATE_TIME_FORMAT" => false, 
		"USE_CAPTCHA" => $arParams["FILES_USE_CAPTCHA"],
		"PREORDER" => $arParams["FILES_PREORDER"],
		"PAGE_NAVIGATION_TEMPLATE" => false, 
		"DISPLAY_PANEL" => "N", 
		"SHOW_RATING" => $arParams["SHOW_RATING"],
		"RATING_TYPE" => $arParams["RATING_TYPE"],
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"PATH_TO_SMILE" => $arParams["FILES_PATH_TO_SMILE"],
		"SHOW_LINK_TO_FORUM" => "N",
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
<?
endif
?>
