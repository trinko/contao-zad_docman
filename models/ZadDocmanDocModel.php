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
 * Class ZadDocmanDocModel
 *
 * @copyright  Antonello Dessì 2014
 * @author     Antonello Dessì
 * @package    zad_docman
 */
class ZadDocmanDocModel extends \Model {

	/**
	 * Name of the table
	 * @var string
	 */
	protected static $strTable = 'tl_zad_docman_doc';


  /**
	 * Find documents by document manager ID
	 *
	 * @param int $docmanId  The document manager ID
	 * @param int $userId  The user ID of the document owner or 0 if not used
	 * @param array $fields  Array with info fields
	 * @param int $offset  Offset number of items, or null if not used
	 * @param int $limit  Max number of items, or null if not used
	 *
	 * @return \ZadDocmanDocModel|null  A collection of document models or null if there are no documents
	 */
	public static function findDocuments($docmanId, $userId, $fields, $offset, $limit) {
		$db = \Database::getInstance();
    // query base
    $sql = 'SELECT t.* FROM '.static::$strTable.' AS t';
    // search all documents of this document manager
    $where = 't.pid='.$docmanId;
    if ($userId) {
      // show only documents of this user
      $where .= ' AND t.sentBy='.$userId;
    }
    // sort options
    $order = '';
    foreach ($fields as $key=>$item) {
      if ($item['sorting'] != 'none') {
        // add sorting
        $sql .= ', tl_zad_docman_info AS t'.$key;
        $where .= ' AND t'.$key.'.pid=t.id AND t'.$key.'.field='.$key;
        if ($item['type'] == 'number') {
          // number
          $order .= ',CAST(t'.$key.'.value AS UNSIGNED) '.$item['sorting'];
        } elseif ($item['type'] == 'auto' && $item['auto_field'] == 'user') {
          // auto:sentby
          $order .= ',t'.$key.'.value '.$item['sorting'];
        } else {
          // string/date/choice/mchoice/auto:timestamp
          $order .= ',t'.$key.'.value '.$item['sorting'];
        }
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
    $res = $db->execute($sql.' WHERE '.$where.$order.$limits);
		return \Model\Collection::createFromDbResult($res, static::$strTable);
  }

  /**
	 * Count documents by document manager ID
	 *
	 * @param int $docmanId  The document manager ID
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
	 * Find document by file UUID
	 *
	 * @param array $uuid  UUID of the file
	 *
	 * @return \ZadDocmanDocModel|null  A collection of document models or null if there are no documents
	 */
	public static function findByFile($uuid) {
		$t = static::$strTable;
    $hex = bin2hex($uuid);
    // search in documents and attachments
    $cond[] = "$t.document=UNHEX('$hex') OR $t.attach LIKE concat('%;s:16:\"',UNHEX('$hex'),'\";%')";
		return static::findOneBy($cond, null);
	}

  /**
	 * Get all menus
	 *
   * @param int $docmanId  The document manager ID
   * @param array $fields  Array with info fields
   * @param array $groupby  Array with groupby fields
	 *
	 * @return \Database\Result|null  A database result or null if there are no menus
	 */
	public static function getMenus($docmanId, $fields, $groupby) {
		$db = \Database::getInstance();
    // query base
    $select = 'SELECT DISTINCT ';
    $from = ' FROM '.static::$strTable.' AS t';
    $where = ' WHERE t.pid='.$docmanId;
    $order = ' ORDER BY ';
    // add group by fields
    foreach ($groupby as $value) {
      $key = substr($value, 6);
      if ($fields[$key]['type'] == 'number') {
        // number
        $select .= 't'.$key.'.value AS '.$value.',';
        $order .= 'CAST(t'.$key.'.value AS UNSIGNED) '.$fields[$key]['sorting'].',';
      } elseif ($fields[$key]['type'] == 'date') {
        // date
        $select .= 'from_unixtime(t'.$key.'.value,\'%Y-%m\') AS '.$value.',';
        $order .= 'from_unixtime(t'.$key.'.value,\'%Y-%m\') '.$fields[$key]['sorting'].',';
      } elseif ($fields[$key]['type'] == 'auto' && $fields[$key]['auto_field'] == 'timestamp') {
        // auto:timestamp
        $select .= 'from_unixtime(t'.$key.'.value,\'%Y-%m\') AS '.$value.',';
        $order .= 'from_unixtime(t'.$key.'.value,\'%Y-%m\') '.$fields[$key]['sorting'].',';
      } else {
        // any other type
        $select .= 't'.$key.'.value AS '.$value.',';
        $order .= 't'.$key.'.value '.$fields[$key]['sorting'].',';
      }
      $from .= ', tl_zad_docman_info AS t'.$key;
      $where .= ' AND t'.$key.'.pid=t.id AND t'.$key.'.field='.$key;
    }
    // execute query
    $sql = substr($select, 0, -1).$from.$where.substr($order, 0, -1);
    $res = $db->execute($sql);
    return $res;
  }

  /**
	 * Get all documents by page
	 *
   * @param int $docmanId  The document manager ID
   * @param array $fields  Array with info fields
   * @param array $groupby  Array with groupby fields
   * @param array $page  Array with groupby values
	 *
	 * @return \ZadDocmanDocModel|null  A collection of document models or null if there are no documents
	 */
	public static function getDocuments($docmanId, $fields, $groupby, $page) {
    $db = \Database::getInstance();
    // query base
    $select = 'SELECT t.*';
    $from = ' FROM '.static::$strTable.' AS t';
    $where = ' WHERE t.pid='.$docmanId;
    $order = '';
    if ($page !== null && is_array($values = unserialize($page))) {
      // add group by fields
      foreach ($groupby as $key=>$value) {
        $fkey = substr($value, 6);
        $fval = $values[$key];
        if ($fields[$fkey]['type'] == 'number') {
          // number
          $where .= ' AND t'.$fkey.'.value='.$fval;
        } elseif ($fields[$fkey]['type'] == 'date') {
          // date
          $where .= ' AND from_unixtime(t'.$fkey.'.value,\'%Y-%m\')=\''.$fval.'\'';
        } elseif ($fields[$fkey]['type'] == 'auto' && $fields[$fkey]['auto_field'] == 'timestamp') {
          // auto:timestamp
          $where .= ' AND from_unixtime(t'.$fkey.'.value,\'%Y-%m\')=\''.$fval.'\'';
        } else {
          // any other type
          $where .= ' AND t'.$fkey.'.value=\''.$fval.'\'';
        }
        $from .= ', tl_zad_docman_info AS t'.$fkey;
        $where .= ' AND t'.$fkey.'.pid=t.id AND t'.$fkey.'.field='.$fkey;
      }
    }
    // sort options
    foreach ($fields as $key=>$item) {
      if ($item['sorting'] != 'none') {
        // add sorting
        if ($item['type'] == 'number') {
          // number
          $order .= ',CAST(t'.$key.'.value AS UNSIGNED) '.$item['sorting'];
        } elseif ($item['type'] == 'auto' && $item['auto_field'] == 'user') {
          // auto:sentby
          $order .= ',t'.$key.'.value '.$item['sorting'];
        } else {
          // string/date/choice/mchoice/auto:timestamp
          $order .= ',t'.$key.'.value '.$item['sorting'];
        }
        if (!in_array('field_'.$key, $groupby)) {
          $from .= ', tl_zad_docman_info AS t'.$key;
          $where .= ' AND t'.$key.'.pid=t.id AND t'.$key.'.field='.$key;
        }
      }
    }
    if ($order) {
      $order = ' ORDER BY '.substr($order, 1);
    }
    // execute query
    $sql = $select.$from.$where.$order;
    $res = $db->execute($sql);
		return \Model\Collection::createFromDbResult($res, static::$strTable);
  }

}

