<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/about/.left.menu.php");
$aMenuLinks = Array(
	Array(
		GetMessage("ABOUT_MENU_OFFICIAL"),
		"/about/index.php", 
		Array("/about/official.php"), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_LIFE"),
		"/about/life.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_ABOUT"),
		"/about/company/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_PHOTO"),
		"/about/gallery/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyPhoto')" 
	),
	Array(
		GetMessage("ABOUT_MENU_VIDEO"),
		"/about/media.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyVideo')" 
	),
	Array(
		GetMessage("ABOUT_MENU_CAREER"),
		"/about/career.php", 
		Array("/about/resume.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyCareer')" 
	),
	Array(
		GetMessage("ABOUT_MENU_NEWS"),
		"/about/business_news.php", 
		Array(), 
		Array(), 
		"" 
	),
);
?>