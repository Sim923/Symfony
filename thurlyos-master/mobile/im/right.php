<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
define('MOBILE_TEMPLATE_CSS', "/im_styles.css");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

CModule::IncludeModule('im');

CMobile::getInstance()->setLargeScreenSupport(false);
CMobile::getInstance()->setScreenCategory("NORMAL");

$APPLICATION->IncludeComponent("thurly:mobile.im.recent", ".default", array('POSITION' => 'RIGHT'), false, Array("HIDE_ICONS" => "Y"));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>