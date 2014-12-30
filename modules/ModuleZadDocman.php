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
 * Class ModuleZadDocman
 *
 * Parent class for DocMan modules.
 *
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
abstract class ModuleZadDocman extends \Module {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'zaddm_message';

	/**
	 * Document Archive table data
	 *
	 * @var \ZadDocmanArchiveModel
	 */
	protected $docman = null;


  /**
	 * Create a base URL without some parameters
	 *
	 * @param bool $encode  True to $encode "&" in URL, False otherwise
	 *
	 * @return string  The new URL
	 */
	protected function createBaseUrl($encode=true) {
    $params = array('zdA', 'zdP', 'zdD', 'zdF', 'zdG');
    $base = explode('?', \Environment::get('request'));
    $q = '';
    if (isset($base[1])) {
      // delete parameters
  		$queries = preg_split('/&(amp;)?/i', $base[1]);
  		foreach ($queries as $k=>$v) {
  		  $explode = explode('=', $v);
  			if (in_array($explode[0], $params)) {
  				unset($queries[$k]);
  			}
  		}
      if (!empty($queries)) {
  			$q = '?' . implode($encode ? '&amp;' : '&', $queries);
  		}
    }
    return $base[0] . $q;
  }

  /**
	 * Create a new URL with some parameters
	 *
	 * @param array $params  List of couples key=>value to be added
	 * @param string $base  The base url
	 * @param bool $encode  True to $encode "&" in URL, False otherwise
	 *
	 * @return string  The new URL
	 */
	protected function createUrl($params, $base='', $encode=true) {
    if ($base == '') {
      $base = $this->createBaseUrl();
    }
    // create query list
    $queries = array();
		foreach ($params as $k=>$v) {
      $queries[] = "$k=$v";
    }
    $q = implode($encode ? '&amp;' : '&', $queries);
    return $base . ((strpos($base, '?') === false) ? '?' : ($encode ? '&amp;' : '&')) . $q;
  }

  /**
	 * Create an array with all field data
	 *
	 * @return array  The a rray with field data
	 */
	protected function getFields() {
    $values = array();
    // read data
    $sortable = unserialize($this->zad_docman_list);
    $fields = \ZadDocmanFieldsModel::findByPid($this->docman->id);
    if ($fields !== null) {
      // put data in an array indexed by field name
      foreach ($sortable as $fldsort) {
        // search field
        $fld_data = null;
        foreach ($fields as $fld) {
          if ($fld->name == $fldsort['name']) {
            $fld_data = $fld;
            break;
          }
        }
        if (!$fld_data) {
          // error, jump to next field
          continue;
        }
        $values[$fld_data->name] = array(
          'label'=>$fld_data->label,
          'type'=>$fld_data->type,
          'mandatory'=>$fld_data->mandatory,
          'defaultNow'=>$fld_data->defaultNow,
          'defaultValue'=>$fld_data->defaultValue,
          'list'=>$fld_data->list,
          'listOther'=>$fld_data->listOther,
          'autofield'=>$fld_data->autofield,
          'sort'=>$fldsort['sort'],
          'show'=>(isset($fldsort['show']) && $fldsort['show']) ? '1' : '');
      }
    }
    // return fields
    return $values;
  }

	/**
	 * Format field value for text output
	 *
	 * @param string $value  The field value
	 * @param array $field  The field structure
	 *
	 * @return string  The formatted field
	 */
	protected function formatFieldText($value, $field) {
    $data = '';
    switch ($field['type']) {
      case 't_text':
      case 't_number':
      case 't_sequence':
        $data = $value;
        break;
      case 't_date':
        $date = new \Date($value);
        $data = $date->date;
        break;
      case 't_time':
        $date = new \Date($value);
        $data = $date->time;
        break;
      case 't_datetime':
        $date = new \Date($value);
        $data = $date->datim;
        break;
      case 't_choice':
        if ($field['listOther'] && substr($value,0,10) == '__OTHER__:') {
          // other option
          $data = substr($value, 10);
        } else {
          // listed option
          $list = unserialize($field['list']);
          foreach ($list as $l) {
            if ($l['value'] == $value) {
              $data = $l['label'];
              break;
            }
          }
        }
        break;
      case 't_mchoice':
        $list = unserialize($field['list']);
        $vlist = unserialize($value);
        foreach ($vlist as $vl) {
          foreach ($list as $l) {
            if ($l['value'] == $vl) {
              $data .= (($data == '') ? '' : ', ') . $l['label'];
              break;
            }
          }
        }
        if ($field['listOther'] && in_array('__OTHER__', $vlist)) {
          // other option
          $data .= (($data == '') ? '' : ', ') . substr(end($vlist), 10);
        }
        break;
      case 't_auto':
        if ($field['autofield'] == 'af_timestamp') {
          $date = new \Date($value);
          $data = $date->datim;
        } elseif ($field['autofield'] == 'af_user') {
          $user = \MemberModel::findByPk($value);
          if ($user !== null) {
            $data = $user->lastname.' '.$user->firstname;
          }
        }
        break;
    }
    // return formatted value
    return $data;
  }

  /**
	 * Download a file
	 *
	 * @param string $uuid  The file uuid
	 * @param bool $doCheck  If True check the user owner of the document
	 */
	protected function fileDownload($uuid, $doCheck=true) {
    // set base url
    $base_url = $this->createBaseUrl();
    // get field data
    $fields = $this->getFields();
    if (empty($fields)) {
      // error, no fields
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofields'], $base_url);
      return;
    }
    // get file
    $file = \FilesModel::findByUuid($uuid);
    if ($file === null) {
      // error no file
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofile'], $base_url);
      return;
    }
    // get document owner
    $doc = \ZadDocmanModel::findByFile($uuid);
    if ($doc === null) {
      // error no file
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
      return;
    }
    // check owner
    if ($doCheck) {
      if (!$this->isAdmin && !$this->docman->enableOthers && $doc->sentBy != $this->userId) {
        // error, user can't show document
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
        return;
      }
    }
    // get name
    $filename = null;
    if ($doc->document == \String::uuidToBin($uuid)) {
      // get document file name
      $filename = $this->zad_docman_docname;
    } elseif ($this->docman->enableAttach) {
      // get attachment file name
      $attaches = unserialize($doc->attach);
      foreach ($attaches as $katt=>$att) {
        if ($att == \String::uuidToBin($uuid)) {
          // attach found
          $filename = str_replace('{{attachnum}}', $katt+1, $this->zad_docman_attachname);
        }
      }
    }
    if ($filename === null) {
      // error, document not found
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofile'], $base_url);
      return;
    }
    // get document data
    $data = \ZadDocmanDataModel::findByPid($doc->id);
    if ($data === null) {
      // error, invalid id
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
      return;
    }
    while ($data->next()) {
      if (strpos($filename, '{{field:'.$data->field.'}}') !== false) {
        // replace placeholder
        $value = $this->formatFieldText($data->value, $fields[$data->field]);
        $filename = str_replace('{{field:'.$data->field.'}}', $value, $filename);
      }
    }
		// normalize the file name
		$filename = utf8_romanize($filename);
		$filename = preg_replace('/[^A-Za-z0-9_-]/', '-', $filename);
    $filename .= '.' . $file->extension;
    // send file end exit
    $fileobj = new \File($file->path, true);
    $fileobj->sendToBrowser($filename);
  }

	/**
	 * Show an error message and terminate module
	 *
	 * @param string $message  Message to show
	 * @param string $url  URL to go back
	 */
	protected function errorMessage($message, $url) {
    $this->Template = new \FrontendTemplate('zaddm_message');
    $this->Template->active = true;
  	$this->Template->referer = $url;
		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
    $this->Template->message = $message;
  }

}

