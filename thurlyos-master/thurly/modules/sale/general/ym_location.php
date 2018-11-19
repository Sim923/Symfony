<?php

use Thurly\Sale\Location\ExternalTable;

IncludeModuleLangFile(__FILE__);

/**
 * Class CSaleYMLocation
 * Mapping yandex locations to thurly locations
 */
class CSaleYMLocation
{
	private $cityNames = null;
	private $cacheId = "CSaleYMLocations";

	const EXTERNAL_SERVICE_CODE = 'YAMARKET';

	public function __construct(){}

	/**
	 * returns locations data
	 */
	private function getData()
	{
		if($this->cityNames !== null)
			return $this->cityNames;

		$ttl = 2592000;
		$cacheManager = \Thurly\Main\Application::getInstance()->getManagedCache();

		if(false && $cacheManager->read($ttl, $this->cacheId))
		{
			$cityNames = $cacheManager->get($this->cacheId);
		}
		else
		{
			$cityNames = $this->loadDataToCache();
			$cacheManager->set($this->cacheId, $cityNames);
		}

		$this->cityNames = $cityNames;
		return $cityNames;
	}

	/**
	 * Loads data from base
	 */

	private function loadDataToCache()
	{
		$result = array();

		$res = \Thurly\Sale\Location\LocationTable::getList(array(
			'filter' => array(
				'=NAME.LANGUAGE_ID' => 'ru',
				'=TYPE.CODE' => array('CITY')
			),
			'select' => array(
				'ID',
				'NAME_NAME' => 'NAME.NAME'
			)
		));

		while($loc = $res->fetch())
			$result[$loc['ID']] = ToLower($loc['NAME_NAME']);

		return $result;
	}

	/**
	 * @param $cityName
	 * @return int location id
	 */
	public function getLocationByCityName($cityName)
	{
		$this->getData();
		$result =  array_search(ToLower($cityName), $this->cityNames);
		return $result;
	}

	protected function getLocationByExternalIds($yandexLocationsIds)
	{
		if(empty($yandexLocationsIds))
			return array();

		$result = array();

		$res = ExternalTable::getList(array(
			'filter' => array(
				'=XML_ID' => $yandexLocationsIds,
				'=SERVICE.CODE' => self::EXTERNAL_SERVICE_CODE
			)
		));

		while($loc = $res->fetch())
			$result[$loc['XML_ID']] = $loc['LOCATION_ID'];

		return $result;
	}

	protected function extractLocations($yandexLocation)
	{
		if(empty($yandexLocation) || !is_array($yandexLocation))
			return array();

		$result = array();
		$tmp = $yandexLocation;
		unset($tmp['parent']);
		$result[$tmp['id']] = $tmp;

		if(!empty($yandexLocation['parent']))
			$result = $result + $this->extractLocations($yandexLocation['parent']);

		return $result;
	}

	public function getLocationId($yandexLocation)
	{
		if(empty($yandexLocation) || !is_array($yandexLocation))
			return false;

		$locations = $this->extractLocations($yandexLocation);

		if(empty($locations))
			return false;

		$mapExternal = $this->getLocationByExternalIds(array_keys($locations));

		if(!empty($mapExternal))
			foreach($locations as $yLocId => $yLocParams)
				if(!empty($mapExternal[$yLocId]))
					return $mapExternal[$yLocId];

		if(empty($yandexLocation["name"]))
			return false;

		return $this->getLocationByCityName($yandexLocation["name"]);
	}
}