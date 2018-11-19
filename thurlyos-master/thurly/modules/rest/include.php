<?
CModule::AddAutoloadClasses(
	"rest",
	array(
		"CRestServer" => "classes/general/rest.php",
		"CRestUtil" => "classes/general/rest_util.php",

		"CRestEvent" => "classes/general/rest_event.php",
		"CRestEventCallback" => "classes/general/rest_event.php",
		"CRestEventSession" => "classes/general/rest_event.php",

		"IRestService" => "classes/general/rest.php",
		"CRestProvider" => "classes/general/rest_provider.php",

		"CThurlyRestEntity" => "classes/general/restentity.php",

		"CRestServerBatchItem" => "classes/general/rest.php",
	)
);

class CRestEventHandlers
{
	public static function OnBeforeProlog()
	{
		if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
		{
			$p = COption::GetOptionString("rest", "server_path", "/rest")."/";
			if(substr(strtolower($_SERVER['REQUEST_URI']), 0, strlen($p)) === $p)
			{
				if(!defined('BX24_REST_SKIP_SEND_HEADERS'))
				{
					CRestUtil::sendHeaders();
				}

				die();
			}
		}
	}
}

CJSCore::registerExt('marketplace', array(
	'js' => '/thurly/js/rest/marketplace.js',
	'lang' => BX_ROOT.'/modules/rest/lang/'.LANGUAGE_ID.'/jsmarketplace.php',
	'rel' => array('ajax', 'popup', 'access', 'sidepanel'),
));

CJSCore::registerExt('applayout', array(
	'js' => '/thurly/js/rest/applayout.js',
	'css' => '/thurly/js/rest/css/applayout.css',
	'lang' => BX_ROOT.'/modules/rest/lang/'.LANGUAGE_ID.'/jsapplayout.php',
	'lang_additional' => array(
		'REST_APPLICATION_URL' => '/thurly/components/thurly/app.layout/lazyload.ajax.php'
	),
	'rel' => array('ajax', 'popup', 'sidepanel'),
));

CJSCore::registerExt('appplacement', array(
	'js' => '/thurly/js/rest/appplacement.js',
	'rel' => array('ajax', 'applayout'),
));

CJSCore::registerExt('restclient', array(
	'js' => '/thurly/js/rest/client.js',
));

CJSCore::registerExt('rest_userfield', array(
	'js' => '/thurly/js/rest/userfield.js',
	'rel' => array('applayout'),
));
?>