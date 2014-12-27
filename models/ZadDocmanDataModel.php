<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   zad_docman
 * @author    Antonello Dessì
 * @license   LGPL
 * @copyright Antonello Dessì 2014
 */


/**
 * Namespace
 */
namespace zad_docman;


/**
 * Class ZadDocmanDataModel
 *
 * @copyright  Antonello Dessì 2014
 * @author     Antonello Dessì
 * @package    zad_docman
 */
class ZadDocmanDataModel extends \Model {

	/**
	 * Name of the table
	 *
	 * @var string
	 */
	protected static $strTable = 'tl_zad_docman_data';


	/**
	 * Get next number in a sequence field
	 *
	 * @param int $docmanId  Identifier for the docman archive
	 * @param string $field  N ame of the sequence field
	 *
	 * @return int  The next number in sequence
	 */
	public static function nextSequence($docmanId, $field) {
		$db = \Database::getInstance();
    $sql = 'SELECT MAX(CAST(dd.value AS UNSIGNED)) AS num
            FROM tl_zad_docman_archive AS a,tl_zad_docman AS d,tl_zad_docman_data AS dd
            WHERE a.id=d.pid AND d.id=dd.pid
              AND a.id=? AND dd.field=?';
    $res = $db->prepare($sql)->execute($docmanId, $field);
    return $res->num + 1;
  }

}

