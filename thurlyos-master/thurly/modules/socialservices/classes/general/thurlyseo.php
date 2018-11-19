<?
use \Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if(!defined("THURLY_CLOUD_ADV_URL"))
{
	define("THURLY_CLOUD_ADV_URL", 'https://cloud-adv.thurly.info');
}

if(!defined('THURLYSEO_URL'))
{
	define('THURLYSEO_URL', THURLY_CLOUD_ADV_URL);
}

class CThurlySeoOAuthInterface extends CThurlyServiceOAuthInterface
{
	const SERVICE_ID = "thurlyseo";

	const URL = THURLYSEO_URL;

	protected $transport = null;

	protected $scope = array(
		'seo'
	);

	public function __construct($appID = false, $appSecret = false, $code = false)
	{
		if($appID === false)
		{
			$appID = trim(CSocServAuth::GetOption("thurlyseo_id"));
		}

		if($appSecret === false)
		{
			$appSecret = trim(CSocServAuth::GetOption("thurlyseo_secret"));
		}

		parent::__construct($appID, $appSecret, $code);
	}

	public function getTransport()
	{
		if($this->transport === null)
		{
			$this->transport = new CThurlySeoTransport($this->getAppID(), $this->getAppSecret());
		}

		return $this->transport;
	}

	public function getClientInfo()
	{
		if($this->getAppID() && $this->getAppSecret())
		{
			$res = $this->getTransport()->getClientInfo();

			if(!isset($res['error']))
			{
				return $res['result'];
			}
		}

		return false;
	}

	public function clearClientAuth($engine)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->clearClientAuth($engine);

			if(!isset($res['error']))
			{
				return $res['result'];
			}
		}

		return false;
	}

	public function addCampaign($engine, array $campaignParams)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(
				CThurlySeoTransport::METHOD_CAMPAIGN_ADD,
				array(
					"engine" => $engine,
					"campaign" => $campaignParams
				)
			);

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function updateCampaign($engine, array $campaignParams)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(
				CThurlySeoTransport::METHOD_CAMPAIGN_UPDATE,
				array(
					"engine" => $engine,
					"campaign" => $campaignParams
				)
			);

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getCampaign($engine, array $campaignParams)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(
				CThurlySeoTransport::METHOD_CAMPAIGN_GET,
				array(
					"engine" => $engine,
					"campaign" => $campaignParams
				)
			);

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getCampaignList($engine)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_CAMPAIGN_LIST, array(
				'engine' => $engine,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function archiveCampaign($engine, $campaignId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_CAMPAIGN_ARCHIVE, array(
				"engine" => $engine,
				"campaign" => $campaignId
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function unArchiveCampaign($engine, $campaignId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_CAMPAIGN_UNARCHIVE, array(
				"engine" => $engine,
				"campaign" => $campaignId
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function resumeCampaign($engine, $campaignId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_CAMPAIGN_RESUME, array(
				"engine" => $engine,
				"campaign" => $campaignId
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function stopCampaign($engine, $campaignId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_CAMPAIGN_STOP, array(
				"engine" => $engine,
				"campaign" => $campaignId
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function deleteCampaign($engine, $campaignId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_CAMPAIGN_DELETE, array(
				"engine" => $engine,
				"campaign" => $campaignId
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function addBanner($engine, array $bannerParam)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(
				CThurlySeoTransport::METHOD_BANNER_ADD,
				array(
					"engine" => $engine,
					"banner" => $bannerParam
				)
			);

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function updateBanner($engine, array $bannerParam)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(
				CThurlySeoTransport::METHOD_BANNER_UPDATE,
				array(
					"engine" => $engine,
					"banner" => $bannerParam
				)
			);

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getBannerList($engine, $filter)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_BANNER_LIST, array(
				'engine' => $engine,
				'filter' => $filter
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function moderateBanners($engine, $filter)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_BANNER_MODERATE, array(
				'engine' => $engine,
				'filter' => $filter
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function stopBanners($engine, $filter)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_BANNER_STOP, array(
				'engine' => $engine,
				'filter' => $filter
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function resumeBanners($engine, $filter)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_BANNER_RESUME, array(
				'engine' => $engine,
				'filter' => $filter
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function archiveBanners($engine, $filter)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_BANNER_ARCHIVE, array(
				'engine' => $engine,
				'filter' => $filter
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function unArchiveBanners($engine, $filter)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_BANNER_UNARCHIVE, array(
				'engine' => $engine,
				'filter' => $filter
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function deleteBanners($engine, $filter)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_BANNER_DELETE, array(
				'engine' => $engine,
				'filter' => $filter
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getRegions($engine)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REGION_GET, array(
				'engine' => $engine
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}
	
	/**
	 * Get yandex direct clients settings and info
	 * @param $engine
	 * @return bool|mixed
	 */
	public function getClientsSettings($engine)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_CLIENTS_GET, array(
				'engine' => $engine
			));
			
			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}
		
		return false;
	}

	
	public function createWordstatReport($engine, $queryData)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REPORT_WORDSTAT_CREATE, array(
				'engine' => $engine,
				'query' => $queryData,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function deleteWordstatReport($engine, $reportId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REPORT_WORDSTAT_DELETE, array(
				'engine' => $engine,
				'reportId' => $reportId,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getWordstatReport($engine, $reportId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REPORT_WORDSTAT_GET, array(
				'engine' => $engine,
				'reportId' => $reportId,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getWordstatReportList($engine)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REPORT_WORDSTAT_LIST, array(
				'engine' => $engine,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}
	public function createForecastReport($engine, $queryData)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REPORT_FORECAST_CREATE, array(
				'engine' => $engine,
				'query' => $queryData,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function deleteForecastReport($engine, $reportId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REPORT_FORECAST_DELETE, array(
				'engine' => $engine,
				'reportId' => $reportId,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getForecastReport($engine, $reportId)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REPORT_FORECAST_GET, array(
				'engine' => $engine,
				'reportId' => $reportId,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getForecastReportList($engine)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_REPORT_FORECAST_LIST, array(
				'engine' => $engine,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}

	public function getBannerStats($engine, array $params)
	{
		if($this->getAppID() && $this->getAppSecret() && $engine)
		{
			$res = $this->getTransport()->call(CThurlySeoTransport::METHOD_STAT_GET, array(
				'engine' => $engine,
				'params' => $params,
			));

			if(!isset($res['error']))
			{
				return $res['result'];
			}
			else
			{
				return $res;
			}
		}

		return false;
	}
}

class CThurlySeoTransport extends CThurlyServiceTransport
{
	const METHOD_CLIENT_INFO = 'seo.client.info';
	const METHOD_CLIENT_AUTH_CLEAR = 'seo.client.auth.clear';

	const METHOD_CAMPAIGN_ADD = 'seo.campaign.add';
	const METHOD_CAMPAIGN_UPDATE = 'seo.campaign.update';
	const METHOD_CAMPAIGN_GET = 'seo.campaign.get';
	const METHOD_CAMPAIGN_LIST = 'seo.campaign.list';
	const METHOD_CAMPAIGN_ARCHIVE = 'seo.campaign.archive';
	const METHOD_CAMPAIGN_UNARCHIVE = 'seo.campaign.unarchive';
	const METHOD_CAMPAIGN_RESUME = 'seo.campaign.resume';
	const METHOD_CAMPAIGN_STOP = 'seo.campaign.stop';
	const METHOD_CAMPAIGN_DELETE = 'seo.campaign.delete';

	const METHOD_BANNER_ADD = 'seo.banner.add';
	const METHOD_BANNER_UPDATE = 'seo.banner.update';
	const METHOD_BANNER_LIST = 'seo.banner.list';
	const METHOD_BANNER_MODERATE = 'seo.banner.moderate';
	const METHOD_BANNER_ARCHIVE = 'seo.banner.archive';
	const METHOD_BANNER_UNARCHIVE = 'seo.banner.unarchive';
	const METHOD_BANNER_RESUME = 'seo.banner.resume';
	const METHOD_BANNER_STOP = 'seo.banner.stop';
	const METHOD_BANNER_DELETE = 'seo.banner.delete';

	const METHOD_REGION_GET = 'seo.region.get';
	const METHOD_CLIENTS_GET = 'seo.clients.get';

	const METHOD_REPORT_WORDSTAT_CREATE = 'seo.report.wordstat.create';
	const METHOD_REPORT_WORDSTAT_DELETE = 'seo.report.wordstat.delete';
	const METHOD_REPORT_WORDSTAT_GET = 'seo.report.wordstat.get';
	const METHOD_REPORT_WORDSTAT_LIST = 'seo.report.wordstat.list';

	const METHOD_REPORT_FORECAST_CREATE = 'seo.report.forecast.create';
	const METHOD_REPORT_FORECAST_DELETE = 'seo.report.forecast.delete';
	const METHOD_REPORT_FORECAST_GET = 'seo.report.forecast.get';
	const METHOD_REPORT_FORECAST_LIST = 'seo.report.forecast.list';

	const METHOD_STAT_GET = 'seo.stat.get';

	public function __construct($clientId, $clientSecret)
	{
		$this->setSeviceHost(CThurlySeoOAuthInterface::URL);
		return parent::__construct($clientId, $clientSecret);
	}

	public function getClientInfo()
	{
		return $this->call(self::METHOD_CLIENT_INFO);
	}

	public function clearClientAuth($engine)
	{
		return $this->call(self::METHOD_CLIENT_AUTH_CLEAR, array("engine" => $engine));
	}
}
