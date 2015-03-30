<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   zad_docman
 * @author    Antonello Dessì
 * @license   LGPL
 * @copyright Antonello Dessì 2015
 */


/**
 * Namespace
 */
namespace zad_docman;


/**
 * Class ZadDocmanModel
 *
 * @copyright  Antonello Dessì 2015
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

  /**
	 * Count documents by document archive ID
	 *
   * @param int $docmanId  The document archive ID
	 * @param array $fields  Array with field data
   * @param int $waiting  Unix timestamp for publishing
   * @param string $filter  Field name for filtering data
   * @param string $filtervalue  Field value for filtering data
   * @param string $groupby  Field name for grouping data
   * @param string $groupbyvalue  Field value for grouping data
	 *
	 * @return int  Number of documents
	 */
	public static function countDocumentList($docmanId, $fields, $waiting, $filter, $filtervalue, $groupby, $groupbyvalue) {
		$db = \Database::getInstance();
    // query base
    $select = 'SELECT count(*) AS count';
    $from = ' FROM '.static::$strTable.' AS t';
    $where = ' WHERE t.pid='.$docmanId.'';
    // add filter
    if ($filter != '__NULL__') {
      // add table
      $from .= ', tl_zad_docman_data AS t1';
      $where .= ' AND t1.pid=t.id AND t1.field=\''.$filter.'\'';
      if ($fields[$filter]['type'] == 't_mchoice') {
        // mchoice
        $where .= ' AND t1.value LIKE \'%:"'.$filtervalue.'";%\'';
      } else {
        // number/sequence/string/auto:user/choice
        // date/time/datetime/auto:timestamp
        $where .= ' AND t1.value=\''.$filtervalue.'\'';
      }
    }
    // add group
    if ($groupby != '__NULL__' && $groupbyvalue != '') {
      // add table
      $from .= ', tl_zad_docman_data AS t2';
      $where .= ' AND t2.pid=t.id AND t2.field=\''.$groupby.'\'';
      if ($fields[$groupby]['type'] == 't_number' || $fields[$groupby]['type'] == 't_sequence') {
        // number/sequence
        $where .= ' AND CAST(t2.value AS UNSIGNED)='.intval($groupbyvalue);
      } elseif ($fields[$groupby]['type'] == 't_mchoice') {
        // mchoice
        $where .= ' AND t2.value LIKE \'%:"'.$groupbyvalue.'";%\'';
      } elseif ($fields[$groupby]['type'] == 't_choice' && $groupbyvalue == '__OTHER__') {
        // choice(other option)
        $where .= ' AND t2.value LIKE \'%:"__OTHER__:%\'';
      } elseif ($fields[$groupby]['type'] == 't_date') {
        // date
        $where .= ' AND FROM_UNIXTIME(t2.value ,\'%Y-%m\')=\''.$groupbyvalue.'\'';
      } elseif ($fields[$groupby]['type'] == 't_datetime' || ($fields[$groupby]['type'] == 't_auto' && $fields[$groupby]['autofield'] == 'af_timestamp')) {
        // datetime/auto:timestamp
        $where .= ' AND FROM_UNIXTIME(t2.value ,\'%Y-%m-%e\')=\''.$groupbyvalue.'\'';
      } elseif ($fields[$groupby]['type'] == 't_time') {
        // time
        $where .= ' AND FROM_UNIXTIME(t2.value ,\'%H\')=\''.$groupbyvalue.'\'';
      } else {
        // string/auto:user/choice(standard options)
        $where .= ' AND t2.value=\''.$groupbyvalue.'\'';
      }
    }
    // only published docs
    $where .= ' AND t.published AND t.publishedTimestamp < '.$waiting;
    // execute query
    $sql = $select.$from.$where;
    $res = $db->execute($sql);
		return $res->count;
  }

  /**
	 * Get a list of documents
	 *
   * @param int $docmanId  The document archive ID
	 * @param array $fields  Array with field data
   * @param int $waiting  Unix timestamp for publishing
   * @param string $filter  Field name for filtering data
   * @param string $filtervalue  Field value for filtering data
   * @param string $groupby  Field name for grouping data
   * @param string $groupbyvalue  Field value for grouping data
	 * @param int $offset  Offset number of items, or null if not used
	 * @param int $limit  Max number of items, or null if not used
	 *
	 * @return \ZadDocmanDocModel|null  A collection of document models or null if there are no documents
	 */
	public static function getDocumentList($docmanId, $fields, $waiting, $filter, $filtervalue, $groupby, $groupbyvalue, $offset, $limit) {
		$db = \Database::getInstance();
    // query base
    $select = 'SELECT t.*';
    $from = ' FROM '.static::$strTable.' AS t';
    $where = ' WHERE t.pid='.$docmanId.'';
    $order = '';
    // set query
    $table = 1;
    foreach ($fields as $fldname=>$fld) {
      // add table
      $select .= ',t'.$table.'.value AS field_'.$fldname;
      $from .= ', tl_zad_docman_data AS t'.$table;
      $where .= ' AND t'.$table.'.pid=t.id AND t'.$table.'.field=\''.$fldname.'\'';
      // add filter
      if ($filter == $fldname) {
        if ($fld['type'] == 't_mchoice') {
          // mchoice
          $where .= ' AND t'.$table.'.value LIKE \'%:"'.$filtervalue.'";%\'';
        } else {
          // number/sequence/string/auto:user/choice
          // date/time/datetime/auto:timestamp
          $where .= ' AND t'.$table.'.value=\''.$filtervalue.'\'';
        }
      }
      // add group
      if ($groupby == $fldname && $groupbyvalue != '') {
        if ($fld['type'] == 't_number' || $fld['type'] == 't_sequence') {
          // number/sequence
          $where .= ' AND CAST(t'.$table.'.value AS UNSIGNED)='.intval($groupbyvalue);
        } elseif ($fld['type'] == 't_mchoice') {
          // mchoice
          $where .= ' AND t'.$table.'.value LIKE \'%:"'.$groupbyvalue.'";%\'';
        } elseif ($fld['type'] == 't_choice' && $groupbyvalue == '__OTHER__') {
          // choice(other option)
          $where .= ' AND t'.$table.'.value LIKE \'%:"__OTHER__:%\'';
        } elseif ($fld['type'] == 't_date') {
          // date
          $where .= ' AND FROM_UNIXTIME(t'.$table.'.value ,\'%Y-%m\')=\''.$groupbyvalue.'\'';
        } elseif ($fld['type'] == 't_datetime' || ($fld['type'] == 't_auto' && $fld['autofield'] == 'af_timestamp')) {
          // datetime/auto:timestamp
          $where .= ' AND FROM_UNIXTIME(t'.$table.'.value ,\'%Y-%m-%e\')=\''.$groupbyvalue.'\'';
        } elseif ($fld['type'] == 't_time') {
          // time
          $where .= ' AND FROM_UNIXTIME(t'.$table.'.value ,\'%H\')=\''.$groupbyvalue.'\'';
        } else {
          // string/auto:user/choice(standard options)
          $where .= ' AND t'.$table.'.value=\''.$groupbyvalue.'\'';
        }
      }
      // add sort
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
    // only published docs
    $where .= ' AND t.published AND t.publishedTimestamp < '.$waiting;
    // set limits
    $limits = '';
    if ($limit > 0) {
      $limits = ' LIMIT '.$offset.','.$limit;
    }
    // execute query
    if ($order) {
      $order = ' ORDER BY '.substr($order, 1);
    }
    $sql = $select.$from.$where.$order.$limits;
    $res = $db->execute($sql);
		if ($res->numRows < 1) {
      // document not found
			return null;
		}
		return \Model\Collection::createFromDbResult($res, static::$strTable);
  }

  /**
	 * Get a list created grouping data by a field value
	 *
   * @param int $docmanId  The document archive ID
	 * @param array $fields  Array with field data
   * @param int $waiting  Unix timestamp for publishing
   * @param string $filter  Field name for filtering data
   * @param string $filtervalue  Field value for filtering data
   * @param string $groupby  Field name for grouping data
	 *
	 * @return \Database\Result|null  A database result or null if no data
	 */
	public static function getDocumentGroups($docmanId, $fields, $waiting, $filter, $filtervalue, $groupby) {
		$db = \Database::getInstance();
    // query base
    $select = 'SELECT DISTINCT ';
    $from = ' FROM '.static::$strTable.' AS t, tl_zad_docman_data AS t1';
    $where = ' WHERE t.pid='.$docmanId.' AND t1.pid=t.id AND t1.field=\''.$groupby.'\'';
    $order = ' ORDER BY ';
    // only published docs
    $where .= ' AND t.published AND t.publishedTimestamp < '.$waiting;
    // add filter
    if ($filter != '__NULL__') {
      $from .= ', tl_zad_docman_data AS t2';
      $where .= ' AND t2.pid=t.id AND t2.field=\''.$filter.'\'';
      if ($fields[$filter]['type'] == 't_mchoice') {
        // mchoice
        $where .= ' AND t2.value LIKE \'%:"'.$filtervalue.'";%\'';
      } else {
        // number/sequence/string/auto:user/choice
        // date/time/datetime/auto:timestamp
        $where .= ' AND t2.value=\''.$filtervalue.'\'';
      }
    }
    // add sort
    if ($fields[$groupby]['type'] == 't_number' || $fields[$groupby]['type'] == 't_sequence') {
      // number/sequence
      $select .= 'CAST(t1.value AS UNSIGNED) AS field_'.$groupby;
      $order .= 'CAST(t1.value AS UNSIGNED) ASC';
    } elseif ($fields[$groupby]['type'] == 't_date') {
      // date
      $select .= 'FROM_UNIXTIME(t1.value,\'%Y-%m\') AS field_'.$groupby;
      $order .= 'FROM_UNIXTIME(t1.value,\'%Y-%m\') ASC';
    } elseif ($fields[$groupby]['type'] == 't_datetime' || ($fields[$groupby]['type'] == 't_auto' && $fields[$groupby]['autofield'] == 'af_timestamp')) {
      // datetime/auto:timestamp
      $select .= 'FROM_UNIXTIME(t1.value,\'%Y-%m-%e\') AS field_'.$groupby;
      $order .= 'FROM_UNIXTIME(t1.value,\'%Y-%m-%e\') ASC';
    } elseif ($fields[$groupby]['type'] == 't_time') {
      // time
      $select .= 'FROM_UNIXTIME(t1.value,\'%H\') AS field_'.$groupby;
      $order .= 'FROM_UNIXTIME(t1.value,\'%H\') ASC';
    } else {
      // string/auto:user
      $select .= 't1.value AS field_'.$groupby;
      $order .= 't1.value ASC';
    }
    $sql = $select.$from.$where.$order;
    // execute query
    $res = $db->execute($sql);
    return $res;
  }

}

