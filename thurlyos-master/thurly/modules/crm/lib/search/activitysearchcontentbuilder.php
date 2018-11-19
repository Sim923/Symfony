<?php
namespace Thurly\Crm\Search;
use Thurly\Crm\ActivityTable;
class ActivitySearchContentBuilder extends SearchContentBuilder
{
	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Activity;
	}
	public function isFullTextSearchEnabled()
	{
		return ActivityTable::getEntity()->fullTextIndexEnabled('SEARCH_CONTENT');
	}
	protected function prepareEntityFields($entityID)
	{
		$dbResult = \CCrmActivity::GetList(
			array(),
			array('=ID' => $entityID, 'CHECK_PERMISSIONS' => 'N'),
			false,
			false,
			array('*'/*, 'UF_*'*/)
		);

		$fields = $dbResult->Fetch();
		return is_array($fields) ? $fields : null;
	}
	public function prepareEntityFilter(array $params)
	{
		$value = isset($params['SEARCH_CONTENT']) ? $params['SEARCH_CONTENT'] : '';
		if(!is_string($value) || $value === '')
		{
			return array();
		}

		$operation = $this->isFullTextSearchEnabled() ? '*' : '*%';
		return array("{$operation}SEARCH_CONTENT" => SearchEnvironment::prepareToken($value));
	}
	/**
	 * Prepare search map.
	 * @param array $fields Entity Fields.
	 * @return SearchMap
	 */
	protected function prepareSearchMap(array $fields)
	{
		$map = new SearchMap();

		$entityID = isset($fields['ID']) ? (int)$fields['ID'] : 0;
		if($entityID <= 0)
		{
			return $map;
		}

		$map->add($entityID);
		$map->addField($fields, 'ID');
		$map->addField($fields, 'SUBJECT');

		$description = isset($fields['DESCRIPTION']) ? trim($fields['DESCRIPTION']) : '';
		if($description !== '')
		{
			$descriptionType = isset($fields['DESCRIPTION_TYPE'])
				? (int)$fields['DESCRIPTION_TYPE'] : \CCrmContentType::PlainText;

			if($descriptionType === \CCrmContentType::Html)
			{
				$description = strip_tags(
					preg_replace("/<br(\s*\/\s*)?>/i", ' ', $description)
				);
			}
			elseif($descriptionType === \CCrmContentType::BBCode)
			{
				$parser = new \CTextParser();
				$parser->allow['SMILES'] = 'N';
				$description = strip_tags(
					preg_replace(
						"/<br(\s*\/\s*)?>/i",
						' ',
						$parser->convertText($description)
					)
				);
			}

			$map->addText($description, 256);
		}

		if(isset($fields['RESPONSIBLE_ID']))
		{
			$map->addUserByID($fields['RESPONSIBLE_ID']);
		}

		//region Bindings
		$bindings = \CCrmActivity::GetBindings($entityID);
		if(is_array($bindings))
		{
			foreach($bindings as $binding)
			{
				$ownerID = isset($binding['OWNER_ID'])
					? (int)$binding['OWNER_ID'] : 0;
				$ownerTypeID = isset($binding['OWNER_TYPE_ID'])
					? (int)$binding['OWNER_TYPE_ID'] : \CCrmOwnerType::Undefined;
				if($ownerID > 0 && \CCrmOwnerType::IsDefined($ownerTypeID))
				{
					$map->add(
						\CCrmOwnerType::GetCaption($ownerTypeID, $ownerID, false)
					);
				}
			}
		}
		//endregion

		//region Communications
		$communications = \CCrmActivity::GetCommunications($entityID);
		if(is_array($communications))
		{
			foreach($communications as $communication)
			{
				$value = isset($communication['VALUE'])
					? $communication['VALUE'] : '';

				if($value === '')
				{
					continue;
				}

				$typeID = isset($communication['TYPE'])
					? $communication['TYPE'] : '';

				if($typeID === \CCrmFieldMulti::EMAIL)
				{
					$map->addEmail($value);
				}
				elseif($typeID === \CCrmFieldMulti::PHONE)
				{
					$map->addPhone($value);
				}
			}
		}
		//endregion

		return $map;
	}
	protected function save($entityID, SearchMap $map)
	{
		ActivityTable::update($entityID, array('SEARCH_CONTENT' => $map->getString()));
	}
}