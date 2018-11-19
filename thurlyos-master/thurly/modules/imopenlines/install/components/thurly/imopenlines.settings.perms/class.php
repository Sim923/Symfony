<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Thurly\Main\Application;
use Thurly\Main\Error;
use Thurly\Main\ErrorCollection;
use Thurly\ImOpenlines\Model;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CImOpenlinesPermsComponent extends CThurlyComponent
{
	protected $errors;
	protected $saveMode = false;

	public function __construct($component)
	{
		parent::__construct($component);

		$this->errors = new ErrorCollection();
		$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();
		if($request['act'] === 'save' && check_thurly_sessid())
			$this->saveMode = true;
	}

	public function prepareData()
	{
		$roles = array();
		$cursor = Model\RoleTable::getList();
		while($row = $cursor->fetch())
		{
			$roles[$row['ID']] = array(
				'ID' => $row['ID'],
				'NAME' => $row['NAME'],
				'EDIT_URL' => \Thurly\ImOpenlines\Common::getPublicFolder().'editrole.php?ID='.$row['ID'],
			);
		}

		$roleAccessCodes = array();
		$accessCodesToResolve = array();
		$cursor = Model\RoleAccessTable::getList(array(
			'select' => array('ID', 'ROLE_ID', 'ROLE_NAME' => 'ROLE.NAME', 'ACCESS_CODE'),
		));
		while($row = $cursor->fetch())
		{
			$roleAccessCodes[$row['ID']] = array(
				'ID' => $row['ID'],
				'ROLE_ID' => $row['ROLE_ID'],
				'ROLE_NAME' => $row['ROLE_NAME'],
				'ACCESS_CODE' => $row['ACCESS_CODE']
			);
			$accessCodesToResolve[] = $row['ACCESS_CODE'];
		}

		$accessManager = new CAccess();
		$resolvedAccessCodes = $accessManager->GetNames($accessCodesToResolve);

		foreach($roleAccessCodes as $id => $roleAccessCode)
		{
			if(isset($resolvedAccessCodes[$roleAccessCode['ACCESS_CODE']]))
			{
				$codeDescription = $resolvedAccessCodes[$roleAccessCode['ACCESS_CODE']];
				$roleAccessCodes[$id]['ACCESS_PROVIDER'] = $codeDescription['provider'];
				$roleAccessCodes[$id]['ACCESS_NAME'] = $codeDescription['name'];
			}
			else
			{
				$roleAccessCodes[$id]['ACCESS_NAME'] = Loc::getMessage('IMOL_PERM_UNKNOWN_ACCESS_CODE');
			}
		}

		$this->arResult['ROLES'] = $roles;
		$this->arResult['ROLE_ACCESS_CODES'] = $roleAccessCodes;
		$this->arResult['ADD_URL'] = \Thurly\ImOpenlines\Common::getPublicFolder().'editrole.php?ID=0';
		$this->arResult['CAN_EDIT'] = \Thurly\ImOpenlines\Security\Helper::canUse();
		if(!$this->arResult['CAN_EDIT'])
		{
			$this->arResult['TRIAL'] = \Thurly\ImOpenlines\Security\Helper::getTrialText();
		}
	}

	public function save()
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$roleAccessCodes = $request['PERMS'];
		\Thurly\ImOpenlines\Security\Helper::clearMenuCache();

		Model\RoleAccessTable::truncate();

		if(!is_array($roleAccessCodes))
		{
			return true;
		}

		foreach ($roleAccessCodes as $roleAccessCode => $roleId)
		{
			$insertResult = Model\RoleAccessTable::add(array(
				'ROLE_ID' => $roleId,
				'ACCESS_CODE' => $roleAccessCode
			));
			if(!$insertResult->isSuccess())
			{
				$this->errors[] = new Error(Loc::getMessage('IMOL_PERM_UNKNOWN_SAVE_ERROR'));
				return false;
			}
		}

		return true;
	}
	
	public function executeComponent()
	{
		$permissions = \Thurly\ImOpenlines\Security\Permissions::createWithCurrentUser();
		if(!$permissions->canPerform(\Thurly\ImOpenlines\Security\Permissions::ENTITY_SETTINGS, \Thurly\ImOpenlines\Security\Permissions::ACTION_MODIFY))
		{
			ShowError(Loc::getMessage('IMOL_PERM_ACCESS_DENIED'));
			return false;
		}

		if($this->saveMode)
		{
			if(\Thurly\ImOpenlines\Security\Helper::canUse())
			{
				$this->save();
			}
			else
			{
				ShowError(Loc::getMessage('IMOL_PERM_LICENSING_ERROR'));
				return false;
			}
		}

		$this->prepareData();
		$this->includeComponentTemplate();
		return $this->arResult;
	}
}