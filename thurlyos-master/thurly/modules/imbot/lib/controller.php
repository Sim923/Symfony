<?php
namespace Thurly\ImBot;

class Controller
{
	public static function sendToBot($botName, $command, $params)
	{
		$result = null;

		$botName = trim(preg_replace("/[^a-z]/","", strtolower($botName)));
		if (!$botName)
			return $result;

		foreach ($params as $key => $value)
		{
			if ($value == '#ZERO#')
				$value = '0';
			else if ($value == '#EMPTY#')
				$value = '';

			$params[$key] = $value;
		}

		if (class_exists('\\Thurly\\ImBot\\Bot\\'.ucfirst($botName)) && method_exists('\\Thurly\\ImBot\\Bot\\'.ucfirst($botName), 'onAnswerAdd'))
		{
			return call_user_func_array(array('\\Thurly\\ImBot\\Bot\\'.ucfirst($botName), 'onAnswerAdd'), Array($command, $params));
		}
		return $result;
	}

	public static function sendToService($serviceName, $command, $params)
	{
		$result = null;

		$serviceName = trim(preg_replace("/[^a-z]/","", strtolower($serviceName)));
		if (!$serviceName)
			return $result;

		foreach ($params as $key => $value)
		{
			if ($value == '#ZERO#')
				$value = '0';
			else if ($value == '#EMPTY#')
				$value = '';

			$params[$key] = $value;
		}

		if (class_exists('\\Thurly\\ImBot\\Service\\'.ucfirst($serviceName)) && method_exists('\\Thurly\\ImBot\\Service\\'.ucfirst($serviceName), 'onReceiveCommand'))
		{
			return call_user_func_array(array('\\Thurly\\ImBot\\Service\\'.ucfirst($serviceName), 'onReceiveCommand'), Array($command, $params));
		}
		return $result;
	}
}