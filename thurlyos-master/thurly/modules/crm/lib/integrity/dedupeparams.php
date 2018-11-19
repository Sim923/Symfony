<?php
namespace Thurly\Crm\Integrity;
use Thurly\Main;

class DedupeParams
{
	protected $typeID = DuplicateIndexType::UNDEFINED;
	protected $entityTypeID = \CCrmOwnerType::Undefined;
	protected $userID = 0;
	protected $enablePermissionCheck = false;
	protected $scope = DuplicateIndexType::DEFAULT_SCOPE;

	public function __construct($entityTypeID, $userID, $enablePermissionCheck = false,
								$scope = DuplicateIndexType::DEFAULT_SCOPE)
	{
		$this->setEntityTypeID($entityTypeID);
		$this->setUserID($userID);
		$this->enabledPermissionCheck($enablePermissionCheck);
		$this->setScope($scope);
	}

	public function getEntityTypeID()
	{
		return $this->entityTypeID;
	}
	public function setEntityTypeID($entityTypeID)
	{
		if(!is_integer($entityTypeID))
		{
			$entityTypeID = intval($entityTypeID);
		}
		if(!\CCrmOwnerType::IsDefined($entityTypeID))
		{
			$entityTypeID = \CCrmOwnerType::Undefined;
		}

		if($this->entityTypeID === $entityTypeID)
		{
			return;
		}

		$this->entityTypeID = $entityTypeID;
	}
	public function getUserID()
	{
		return $this->userID;
	}
	public function setUserID($userID)
	{
		if(!is_integer($userID))
		{
			$userID = intval($userID);
		}
		$userID = max($userID, 0);

		if($this->userID === $userID)
		{
			return;
		}

		$this->userID = $userID;
	}
	public function isPermissionCheckEnabled()
	{
		return $this->enablePermissionCheck;
	}
	public function enabledPermissionCheck($enable)
	{
		$this->enablePermissionCheck = is_bool($enable) ? $enable : (bool)$enable;
	}
	public function getScope()
	{
		return $this->scope;
	}
	public function setScope($scope)
	{
		if (DuplicateIndexType::checkScopeValue($scope))
			$this->scope = $scope;
	}
}