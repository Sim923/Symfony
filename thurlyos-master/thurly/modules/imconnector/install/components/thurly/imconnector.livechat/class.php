<?php
use \Thurly\Main\Loader,
	\Thurly\Main\Data\Cache,
	\Thurly\Main\LoaderException,
	\Thurly\Main\Localization\Loc;
use \Thurly\ImConnector\Status,
	\Thurly\ImConnector\Library,
	\Thurly\ImConnector\Connector;

class ImConnectorLiveChat extends CThurlyComponent
{
	private $cacheId;

	private $connector = 'livechat';
	private $error = array();
	private $messages = array();
	/** @var \Thurly\ImConnector\Status */
	private $status;

	protected $pageId = 'page_lc';

	/**
	 * Check the connection of the necessary modules.
	 * @return bool
	 * @throws LoaderException
	 */
	protected function checkModules()
	{
		if (Loader::includeModule('imconnector') && Loader::includeModule('imopenlines'))
		{
			return true;
		}
		else
		{
			if(!Loader::includeModule('imconnector'))
				ShowError(Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_MODULE_IMCONNECTOR_NOT_INSTALLED'));
			if(!Loader::includeModule('imopenlines'))
				ShowError(Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_MODULE_IMOPENLINES_NOT_INSTALLED'));
			
			return false;
		}
	}

	protected function initialization()
	{
		$this->status = Status::getInstance($this->connector, $this->arParams['LINE']);

		$this->arResult["STATUS"] = $this->status->isStatus();
		$this->arResult["ACTIVE_STATUS"] = $this->status->getActive();
		$this->arResult["CONNECTION_STATUS"] = $this->status->getConnection();
		$this->arResult["REGISTER_STATUS"] = $this->status->getRegister();
		$this->arResult["ERROR_STATUS"] = $this->status->getError();
		$this->arResult["PATH_TO_INDEX_OL"] = \Thurly\ImOpenLines\Common::getPublicFolder();
		$this->arResult["PUBLIC_TO_BUTTON_OL"] = $this->arResult["PATH_TO_INDEX_OL"] . 'button.php';

		$this->cacheId = Connector::getCacheIdConnector($this->arParams['LINE'], $this->connector);
	}

	/**
	 * Reset cache
	 */
	protected function cleanCache()
	{
		Connector::cleanCacheConnector($this->arParams['LINE'], $this->cacheId);
	}

	public function saveForm()
	{
		//If been sent the current form
		if (!\Thurly\Main\Loader::includeModule('imopenlines'))
		{
			$this->error[] = Loc::getMessage("IMCONNECTOR_COMPONENT_LIVECHAT_MODULE_IMOPENLINES_NOT_INSTALLED");
		}
		else if ($this->request->isPost() && !empty($this->request[$this->connector. '_form']))
		{
			//If the session actual
			if(check_thurly_sessid())
			{
				$liveChatManager = new \Thurly\ImOpenLines\LiveChatManager($this->arParams['LINE']);

				//Activation bot
				if($this->request[$this->connector. '_active'] && empty($this->arResult["ACTIVE_STATUS"]))
				{
					$this->status->setActive(true);
					$this->arResult["ACTIVE_STATUS"] = true;
					$this->status->setConnection(true);
					$this->arResult["CONNECTION_STATUS"] = true;
					$this->status->setRegister(true);
					$this->arResult["REGISTER_STATUS"] = true;
					$liveChatManager->add();
					$this->arResult["STATUS"] = true;

					//Reset cache
					$this->cleanCache();
				}

				if(!empty($this->arResult["ACTIVE_STATUS"]))
				{
					//If saving
					if($this->request[$this->connector. '_save'])
					{
						$this->messages[] = Loc::getMessage("IMCONNECTOR_COMPONENT_LIVECHAT_OK_SAVE");

						$backgroundImage = $this->processPostBackgroundImage('BACKGROUND_IMAGE');
						if ($backgroundImage !== false)
						{
							$update['BACKGROUND_IMAGE'] = $backgroundImage;
						}

						$update['URL_CODE_PUBLIC'] = $this->request->get('URL_CODE_PUBLIC');
						$update['TEMPLATE_ID'] = $this->request->get('TEMPLATE_ID');

						$update['CSS_ACTIVE'] = 'N';
						if ($this->request->get('CSS_ACTIVE'))
						{
							$update['CSS_ACTIVE'] = 'Y';
						}
						$update['CSS_PATH'] = $this->request->get('CSS_PATH');
						$update['CSS_TEXT'] = $this->request->get('CSS_TEXT');

						$update['COPYRIGHT_REMOVED'] = 'N';
						if (\Thurly\ImOpenLines\LiveChatManager::canRemoveCopyright() && $this->request->get('COPYRIGHT_REMOVED'))
						{
							$update['COPYRIGHT_REMOVED'] = 'Y';
						}

						$liveChatManager->update($update);

						//Reset cache
						$this->cleanCache();
					}

					if($this->request[$this->connector. '_del'])
					{
						$liveChatManager->delete();

						Status::delete($this->connector, $this->arParams['LINE']);
						$this->arResult["STATUS"] = false;
						$this->arResult["ACTIVE_STATUS"] = false;
						$this->arResult["CONNECTION_STATUS"] = false;
						$this->arResult["REGISTER_STATUS"] = false;
						$this->arResult["ERROR_STATUS"] = false;
						$this->arResult["PAGE"] = '';

						//Reset cache
						$this->cleanCache();
					}
				}
			}
			else
			{
				$this->error[] = Loc::getMessage("IMCONNECTOR_COMPONENT_LIVECHAT_SESSION_HAS_EXPIRED");
			}
		}
	}

	public function constructionForm()
	{
		global $APPLICATION;

		$this->arResult["NAME"] = Connector::getNameConnectorReal($this->connector);
		
		$this->arResult["URL"]["DELETE"] = $APPLICATION->GetCurPageParam("", array($this->pageId, "open_block"));
		$this->arResult["URL"]["SIMPLE_FORM"] = $APPLICATION->GetCurPageParam($this->pageId . "=simple_form", array($this->pageId, "open_block"));

		if($this->arResult["ACTIVE_STATUS"])
		{
			if($this->arResult["STATUS"])
			{
				$cache = Cache::createInstance();

				if ($cache->initCache(Library::CACHE_TIME_COMPONENT, $this->cacheId, Library::CACHE_DIR_COMPONENT))
				{
					$this->arResult["INFO_CONNECTION"] = $cache->getVars();
				}
				elseif ($cache->startDataCache())
				{
					$liveChatManager = new \Thurly\ImOpenLines\LiveChatManager($this->arParams['LINE']);
					if($config = $liveChatManager->get())
					{
						$this->arResult["INFO_CONNECTION"] = $config;
						$cache->endDataCache($this->arResult["INFO_CONNECTION"]);
					}
					else
					{
						$this->arResult["INFO_CONNECTION"] = array('CONFIG_ID' => $this->arParams['LINE']);
						$cache->abortDataCache();
					}
				}

				$liveChatManager = new \Thurly\ImOpenLines\LiveChatManager($this->arParams['LINE']);
				$this->arResult["INFO_CONNECTION"]["WIDGET_CODE"] = $liveChatManager->getWidget();

				$this->arResult["INFO_CONNECTION"]["BUTTON_INTERFACE"] = \Thurly\Main\ModuleManager::isModuleInstalled('crm') && class_exists('\Thurly\Crm\SiteButton\Button')? 'Y': 'N';
			}

			if(empty($this->request['open_block']))
				$this->arResult["OPEN_BLOCK"] = '';
			else
				$this->arResult["OPEN_BLOCK"] = 'Y';
		}

		$this->arResult["CONNECTOR"] = $this->connector;
	}

	public function processPostBackgroundImage($fieldName)
	{
		$needDeleteImage = $this->request->get($fieldName . '_del') == 'Y';
		$needUpdateImage = $needDeleteImage;

		$fileList = $this->request->getFile($fieldName);
		if (!$fileList['error'])
		{
			if(is_array($fileList['tmp_name']))
			{
				$fileListTmp = array();
				foreach($fileList as $fileKey => $fileValue)
				{
					foreach($fileValue as $valueIndex => $value)
					{
						$fileListTmp[$valueIndex][$fileKey] = $value;
					}
				}

				$fileList = $fileListTmp;
			}
			else
			{
				$fileList = array($fileList);
			}
		}
		else
		{
			$fileList = Array();
		}

		$fileId = 0;
		foreach($fileList as $file)
		{
			$file['MODULE_ID'] = "imopenlines";
			$fileId = CFile::SaveFile($file, 'imopenlines/livechat');
			if($fileId)
			{
				$needDeleteImage = true;
				$needUpdateImage = true;
			}
			break;
		}

		if($needDeleteImage)
		{
			$liveChatManager = new \Thurly\ImOpenLines\LiveChatManager($this->arParams['LINE']);
			$config = $liveChatManager->get();
			if($config[$fieldName] > 0)
			{
				CFile::Delete($config[$fieldName]);
			}
		}

		return $needUpdateImage? $fileId: false;
	}
	
	public function executeComponent()
	{
		$this->includeComponentLang('class.php');

		if($this->checkModules())
		{
			if(Connector::isConnector($this->connector))
			{
				$this->initialization();

				$this->arResult["PAGE"] = $this->request[$this->pageId];
				$this->saveForm();

				$this->constructionForm();

				if(!empty($this->error))
					$this->arResult['error'] = $this->error;

				if(!empty($this->messages))
					$this->arResult['messages'] = $this->messages;

				$this->includeComponentTemplate();
			}
			else
			{
				ShowError(Loc::getMessage("IMCONNECTOR_COMPONENT_LIVECHAT_NO_ACTIVE_CONNECTOR"));
			}
		}
	}
};
