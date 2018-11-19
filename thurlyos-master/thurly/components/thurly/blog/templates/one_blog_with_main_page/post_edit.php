<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"thurly:blog.menu",
	"",
	Array(
			"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
			"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
			"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
			"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
			"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
			"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
			"PATH_TO_BLOG_EDIT"		=> $arResult["PATH_TO_BLOG_EDIT"],
			"PATH_TO_BLOG_INDEX"	=> $arResult["PATH_TO_BLOG_INDEX"],
			"PATH_TO_DRAFT"			=> $arResult["PATH_TO_DRAFT"],
			"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
			"PATH_TO_USER_FRIENDS"	=> $arResult["PATH_TO_USER_FRIENDS"],
			"PATH_TO_USER_SETTINGS"	=> $arResult["PATH_TO_USER_SETTINGS"],
			"PATH_TO_GROUP_EDIT"	=> $arResult["PATH_TO_GROUP_EDIT"],
			"PATH_TO_CATEGORY_EDIT"	=> $arResult["PATH_TO_CATEGORY_EDIT"],
			"PATH_TO_RSS_ALL"		=> $arResult["PATH_TO_RSS_ALL"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
		),
	$component
);
$componentPostEditParams = Array(
	"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
	"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
	"USER_VAR"			=> $arResult["ALIASES"]["user_id"],
	"PAGE_VAR"			=> $arResult["ALIASES"]["page"],
	"PATH_TO_BLOG"		=> $arResult["PATH_TO_BLOG"],
	"PATH_TO_POST"		=> $arResult["PATH_TO_POST"],
	"PATH_TO_USER"		=> $arResult["PATH_TO_USER"],
	"PATH_TO_POST_EDIT"	=> $arResult["PATH_TO_POST_EDIT"],
	"PATH_TO_DRAFT"		=> $arResult["PATH_TO_DRAFT"],
	"PATH_TO_SMILE"		=> $arResult["PATH_TO_SMILE"],
	"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
	"ID"				=> $arResult["VARIABLES"]["post_id"],
	"SET_TITLE"			=> $arResult["SET_TITLE"],
	"POST_PROPERTY"		=> $arParams["POST_PROPERTY"],
	"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
	"NAME_TEMPLATE" 	=> $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" 		=> $arParams["SHOW_LOGIN"],
	
	"PATH_TO_BLOG_POST" => $arResult["PATH_TO_POST"],
	"PATH_TO_BLOG_POST_EDIT" 	=> $arResult["PATH_TO_POST_EDIT"],
	"PATH_TO_BLOG_DRAFT" 		=> $arResult["PATH_TO_DRAFT"],
	"PATH_TO_BLOG_BLOG" 		=> $arResult["PATH_TO_BLOG"],
	"PATH_TO_USER_POST" 		=> $arParams["PATH_TO_USER_POST"],
	"PATH_TO_USER_POST_EDIT" 	=> $arParams["PATH_TO_USER_POST_EDIT"],
	"PATH_TO_USER_DRAFT" 		=> $arParams["PATH_TO_USER_DRAFT"],
	"PATH_TO_USER_BLOG" 		=> $arParams["PATH_TO_USER_BLOG"],
	"PATH_TO_GROUP_POST" 		=> $arParams["PATH_TO_GROUP_POST"],
	"PATH_TO_GROUP_POST_EDIT" 	=> $arParams["PATH_TO_GROUP_POST_EDIT"],
	"PATH_TO_GROUP_DRAFT" 		=> $arParams["PATH_TO_GROUP_DRAFT"],
	"PATH_TO_GROUP_BLOG" 		=> $arParams["PATH_TO_GROUP_BLOG"],
	"NAME_TEMPLATE" 			=> $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" 				=> $arParams["SHOW_LOGIN"],
	"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
	"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
	"EDITOR_RESIZABLE" => $arParams["EDITOR_RESIZABLE"],
	"EDITOR_DEFAULT_HEIGHT" => $arParams["EDITOR_DEFAULT_HEIGHT"],
	"EDITOR_CODE_DEFAULT" => $arParams["EDITOR_CODE_DEFAULT"],
	"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
	"USE_GOOGLE_CODE" => $arParams["USE_GOOGLE_CODE"],
);
if(isset($arParams["USER_CONSENT"]))
	$componentPostEditParams["USER_CONSENT"] = $arParams["USER_CONSENT"];
if(isset($arParams["USER_CONSENT_ID"]))
	$componentPostEditParams["USER_CONSENT_ID"] = $arParams["USER_CONSENT_ID"];
if(isset($arParams["USER_CONSENT_IS_CHECKED"]))
	$componentPostEditParams["USER_CONSENT_IS_CHECKED"] = $arParams["USER_CONSENT_IS_CHECKED"];
if(isset($arParams["USER_CONSENT_IS_LOADED"]))
	$componentPostEditParams["USER_CONSENT_IS_LOADED"] = $arParams["USER_CONSENT_IS_LOADED"];
$APPLICATION->IncludeComponent(
	"thurly:blog.post.edit",
	"",
	$componentPostEditParams,
	$component
);
?>