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
 * Class ZadDocmanModel
 *
 * @copyright  Antonello Dessì 2014
 * @author     Antonello Dessì
 * @package    zad_docman
 */
class ZadDocmanModel extends \Model {

	/**
	 * Name of the table
	 *
	 * @var string
	 */
	protected static $strTable = 'tl_zad_docman';


  /**
	 * Count documents by document archive ID
	 *
	 * @param int $docmanId  The document archive ID
	 * @param int $userId  The user ID of the document owner, or null if not used
	 *
	 * @return int  Number of documents
	 */
	public static function countDocuments($docmanId, $userId) {
		$db = \Database::getInstance();
    // query base
    $sql = 'SELECT count(*) AS count FROM '.static::$strTable.' AS t1';
    // search all documents of this document manager
    $where = 't1.pid='.$docmanId;
    if ($userId) {
      // show only documents of this user
      $where .= ' AND t1.sentBy='.$userId;
    }
    // execute query
    $res = $db->execute($sql.' WHERE '.$where);
		return $res->count;
  }

  /**
	 * Find documents by document archive ID
	 *
	 * @param int $docmanId  The document archive ID
	 * @param int $userId  The user ID of the document owner or 0 if not used
	 * @param array $fields  Array with field data
	 * @param int $offset  Offset number of items, or null if not used
	 * @param int $limit  Max number of items, or null if not used
	 *
	 * @return \ZadDocmanModel|null  A collection of document models or null if there are no documents
	 */
	public static function findDocuments($docmanId, $userId, $fields, $offset, $limit) {
		$db = \Database::getInstance();
    // query base
    $select = 't.*';
    $from = static::$strTable.' AS t';
    // search all documents of this document archive
    $where = 't.pid='.$docmanId;
    if ($userId) {
      // show only documents of this user
      $where .= ' AND t.sentBy='.$userId;
    }
    // sort options
    $order = '';
    $table = 1;
    foreach ($fields as $fldname=>$fld) {
      if ($fld['show'] || $fld['sort'] == 'ASC' || $fld['sort'] == 'DESC') {
        // add table
        $from .= ', tl_zad_docman_data AS t'.$table;
        $where .= ' AND t'.$table.'.pid=t.id AND t'.$table.'.field=\''.$fldname.'\'';
        if ($fld['show']) {
          // add column
          $select .= ',t'.$table.'.value AS field_'.$fldname;
        }
        if ($fld['sort'] == 'ASC' || $fld['sort'] == 'DESC') {
          if ($fld['type'] == 't_number' || $fld['type'] == 't_sequence') {
            // number/sequence
            $order .= ',CAST(t'.$table.'.value AS UNSIGNED) '.$fld['sort'];
          } else {
            // string/date/time/datetime/choice/mchoice/auto:timestamp/auto:user
            $order .= ',t'.$table.'.value '.$fld['sort'];
          }
        }
        $table++;
      }
    }
    if ($order) {
      $order = ' ORDER BY '.substr($order, 1);
    }
    // limits
    $limits = '';
    if ($limit > 0) {
      $limits = ' LIMIT '.$offset.','.$limit;
    }
    // execute query
    $res = $db->execute('SELECT '.$select.' FROM '.$from.' WHERE '.$where.$order.$limits);
		return \Model\Collection::createFromDbResult($res, static::$strTable);
  }

	/**
	 * Find document by file UUID
	 *
	 * @param string $uuid  UUID of the file
	 *
	 * @return \ZadDocmanModel|null  A document model or null if no documents found
	 */
	public static function findByFile($uuid) {
		$db = \Database::getInstance();
    $hex = bin2hex(\String::uuidToBin($uuid));
    // set query
    $sql = "SELECT t.* FROM ".static::$strTable." AS t ".
           "WHERE t.document=unhex('$hex') OR t.attach LIKE concat('%;s:16:\"',unhex('$hex'),'\";%') ".
           "LIMIT 0,1";
    // execute query
    $res = $db->execute($sql);
		if ($res->numRows < 1) {
      // document not found
			return null;
		}
		return \Model\Collection::createFromDbResult($res, static::$strTable);
	}

}

