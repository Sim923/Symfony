<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage rest
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Rest\SessionAuth;

class Auth
{
	const AUTH_TYPE = 'sessionauth';

	protected static $authQueryParams = array(
		'sessid',
	);

	public static function onRestCheckAuth(array $query, $scope, &$res)
	{
		global $USER;

		$authKey = null;
		foreach(static::$authQueryParams as $key)
		{
			if(array_key_exists($key, $query))
			{
				$authKey = $query[$key];
				break;
			}
		}

		if($authKey !== null)
		{
			static::checkHttpAuth();

			if($authKey === thurly_sessid())
			{
				if($USER->isAuthorized())
				{
					$error = false;
					$res = array(
						'user_id' => $USER->GetID(),
						'scope' => implode(',', \CRestUtil::getScopeList()),
						'parameters_clear' => static::$authQueryParams,
						'auth_type' => static::AUTH_TYPE,
					);

					self::setLastActivityDate($USER->GetID(), $query);
				}
				else
				{
					$error = true;
					$res = array('error' => 'access_denied', 'error_description' => 'User not authorized', 'additional' => array('sessid' => thurly_sessid()));
				}
			}
			else
			{
				$error = true;
				$res = array('error' => 'session_failed', 'error_description' => 'Sessid check failed', 'additional' => array('sessid' => thurly_sessid()));
			}

			if($error)
			{
				static::requireHttpAuth();
			}

			return !$error;
		}

		return null;
	}

	private static function setLastActivityDate($userId, $query)
	{
		$query = array_change_key_case($query, CASE_UPPER);
		if (isset($query['BX_LAST_ACTIVITY']) && $query['BX_LAST_ACTIVITY'] == 'N')
		{
			return false;
		}

		$useCache = isset($query['BX_LAST_ACTIVITY_USE_CACHE']) && $query['BX_LAST_ACTIVITY_USE_CACHE'] == 'N'? false: true;

		if (isset($query['BX_MOBILE']) && $query['BX_MOBILE'] == 'Y')
		{
			if ($query['BX_MOBILE_BACKGROUND'] != 'Y' && \Thurly\Main\Loader::includeModule('mobile'))
			{
				\Thurly\Mobile\User::setOnline($userId, $useCache);
				\CUser::SetLastActivityDate($userId, $useCache);
			}
		}
		else
		{
			\CUser::SetLastActivityDate($userId, $useCache);
		}

		return true;
	}

	protected static function requireHttpAuth()
	{
		global $USER;
		$USER->RequiredHTTPAuthBasic('Thurly REST');
	}

	protected static function checkHttpAuth()
	{
		global $USER, $APPLICATION;

		if(!$USER->IsAuthorized())
		{
			$httpAuth = $USER->LoginByHttpAuth();
			if($httpAuth !== null)
			{
				$APPLICATION->SetAuthResult($httpAuth);
			}
		}
	}
}