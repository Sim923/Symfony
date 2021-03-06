<?php
namespace Thurly\Sale\Exchange;

/**
 * Interface IConverter
 * @package Thurly\Sale\Exchange
 * @deprecated
 */
interface IConverter
{
    /**
     * @param $documentImport
     * @return array
     */
    public function resolveParams($documentImport);

    /**
     * @param null $entity
     * @param array $fields
     * @return array
     */
    public function sanitizeFields($entity=null, array &$fields);

    /**
     * @param ISettings $settings
     */
    public function loadSettings(ISettings $settings);
}