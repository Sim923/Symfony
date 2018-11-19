<?php

namespace Thurly\Disk\Internals;

use Thurly\Main\ArgumentTypeException;
use Thurly\Main\Entity;
use Thurly\Disk\Internals\Engine;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CallableValidator extends Entity\Validator\Base
{
	/**
	 * @var string
	 */
	protected $errorPhraseCode = 'DISK_CALLABLE_VALIDATOR_BASE';
	/**
	 * @var callable
	 */
	private $validateCallback;

	public function __construct($validateCallback, $errorPhrase = null)
	{
		if(!is_callable($validateCallback))
		{
			throw new ArgumentTypeException('validateCallback', 'callable');
		}

		$this->validateCallback = $validateCallback;

		parent::__construct($errorPhrase);
	}

	public function validate($value, $primary, array $row, Entity\Field $field)
	{
		$result = call_user_func($this->validateCallback, $value, $primary, $row, $field);

		if($result === null)
		{
			return true;
		}

		return $result;
	}
}
