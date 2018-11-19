<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

if (!$USER->CanDoOperation('seo_tools'))
	die(GetMessage("ACCESS_DENIED"));

use Thurly\Seo\Engine;
use Thurly\Main\IO\Path;

IncludeModuleLangFile(__FILE__);
\Thurly\Main\Loader::includeModule('seo');
\Thurly\Main\Loader::includeModule('socialservices');

CUtil::JSPostUnescape();

$engine = new Engine\Google();

if(isset($_REQUEST['action']) && check_thurly_sessid())
{
	$res = array();

	$arDomain = null;
	if(isset($_REQUEST['domain']) && strlen($_REQUEST['domain']) > 0)
	{
		$bFound = false;
		$arDomains = \CSeoUtils::getDomainsList();
		foreach($arDomains as $arDomain)
		{
			if($arDomain['DOMAIN'] == $_REQUEST['domain'] && rtrim($arDomain['SITE_DIR'], '/') == rtrim($_REQUEST['dir'], '/'))
			{
				$bFound = true;
				break;
			}
		}

		if(!$bFound)
		{
			$res = array('error' => 'Unknown site!');
		}
	}

	if(!$res['error'])
	{
		try
		{
			switch($_REQUEST['action'])
			{
				case 'nullify_auth':
					$engine->clearAuthSettings();
					$res = array("result" => true);
				break;

				case 'sites_feed':
					$res = $engine->getFeeds();
				break;

				case 'site_add':
					$res = $engine->addSite($arDomain['DOMAIN'], $arDomain['SITE_DIR']);
					$res['_domain'] = $arDomain['DOMAIN'];
				break;

				case 'site_verify':
					$res = array('error' => 'Unknown domain');

					if(is_array($arDomain))
					{
						$sitesInfo = $engine->getFeeds();
						if($sitesInfo[$arDomain['DOMAIN']]['verified'] == false)
						{
							$filename = $engine->verifyGetToken($arDomain['DOMAIN'], $arDomain['SITE_DIR']);

							// paranoia?
							$filename = preg_replace("/^(.*?)\..*$/", "\\1.html", $filename);

							if(strlen($filename) > 0)
							{
								$path = Path::combine((
									strlen($arDomain['SITE_DOC_ROOT']) > 0
										? $arDomain['SITE_DOC_ROOT']
										: $_SERVER['DOCUMENT_ROOT']
									), $arDomain['SITE_DIR'], $filename);

								$obFile = new \Thurly\Main\IO\File($path);

								if($obFile->isExists())
								{
									$obFile->delete();
								}

								$obFile->putContents('google-site-verification: '.$filename);

								$res = $engine->verifySite($arDomain['DOMAIN'], $arDomain['SITE_DIR']);
							}

							$res = $engine->getFeeds();

							$res['_domain'] = $arDomain['DOMAIN'];
						}
						elseif($siteInfo[$arDomain['DOMAIN']]['verified'] == 'true')
						{
							$res = $siteInfo;
							$res['_domain'] = $arDomain['DOMAIN'];
						}
					}
					else
					{
						$res = array('error' => 'No domain');
					}
				break;

				default:
					$res = array('error' => 'unknown action');
				break;
			}
		}
		catch(Exception $e)
		{
			$res = array(
				'error' => $e->getMessage()
			);
		}
	}

	Header('Content-type: application/json');
	echo \Thurly\Main\Web\Json::encode($res);
}
?>