<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage iblock
 */
namespace Thurly\Iblock\InheritedProperty;

class IblockValues extends BaseValues
{
	/**
	 * @param integer $iblockId Iblock identifier.
	 */
	public function __construct($iblockId)
	{
		parent::__construct($iblockId);
	}

	/**
	 * Returns the table name where values will be stored.
	 *
	 * @return string
	 */
	public function getValueTableName()
	{
		return "b_iblock_iblock_iprop";
	}

	/**
	 * Returns type of the entity which will be stored into DB.
	 *
	 * @return string
	 */
	public function getType()
	{
		return "B";
	}

	/**
	 * Returns unique identifier of the iblock.
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->iblockId;
	}

	/**
	 * Creates an entity which will be used to process the templates.
	 *
	 * @return \Thurly\Iblock\Template\Entity\Base
	 */
	public function  createTemplateEntity()
	{
		return new \Thurly\Iblock\Template\Entity\Iblock($this->iblockId);
	}

	/**
	 * Returns all the parents of the iblock which is empty array.
	 *
	 * @return array[]\Thurly\Iblock\InheritedProperty\BaseValues
	 */
	public function getParents()
	{
		return array();
	}

	/**
	 * Returns all calculated values of inherited properties
	 * for iblock.
	 *
	 * @return array[string]string
	 */
	public function queryValues()
	{
		$result = array();
		if ($this->hasTemplates())
		{
			$connection = \Thurly\Main\Application::getConnection();
			$query = $connection->query("
				SELECT
					P.ID
					,P.CODE
					,P.TEMPLATE
					,P.ENTITY_TYPE
					,P.ENTITY_ID
					,IP.VALUE
				FROM
					b_iblock_iblock_iprop IP
					INNER JOIN b_iblock_iproperty P ON P.ID = IP.IPROP_ID
				WHERE
					IP.IBLOCK_ID = ".$this->iblockId."
			");

			while ($row = $query->fetch())
			{
				$result[$row["CODE"]] = $row;
			}

			if (empty($result))
			{
				$result = parent::queryValues();
				foreach ($result as $row)
				{
					$connection->add("b_iblock_iblock_iprop", array(
						"IBLOCK_ID" => $this->iblockId,
						"IPROP_ID" => $row["ID"],
						"VALUE" => $row["VALUE"],
					), null);
				}
			}
		}
		return $result;
	}

	/**
	 * Clears iblock values DB cache
	 *
	 * @return void
	 */
	function clearValues()
	{
		$connection = \Thurly\Main\Application::getConnection();
		$connection->query("
			DELETE FROM b_iblock_element_iprop
			WHERE IBLOCK_ID = ".$this->iblockId."
		");
		$connection->query("
			DELETE FROM b_iblock_section_iprop
			WHERE IBLOCK_ID = ".$this->iblockId."
		");
		$connection->query("
			DELETE FROM b_iblock_iblock_iprop
			WHERE IBLOCK_ID = ".$this->iblockId."
		");
	}
}
