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
 * Class ModuleZadDocmanReader
 *
 * Front end module "Document Reader".
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class ModuleZadDocmanReader extends \Module {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'zaddm_reader';

	/**
	 * Document manager data
	 * @var DocmanModel
	 */
	protected $docman = null;


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate() {
		if (TL_MODE == 'BE') {
			$template = new \BackendTemplate('be_wildcard');
      $template->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['zad_docman_reader'][0]) . ' ###';
			$template->title = $this->headline;
			$template->id = $this->id;
			$template->link = $this->name;
			$template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
      return $template->parse();
		}
		return parent::generate();
	}

	/**
	 * Generate the module
	 */
	protected function compile() {
    $this->Template = new \FrontendTemplate('zaddm_message');
    // get data
    $this->docman = \ZadDocmanModel::findByPk($this->zad_docman);
    if ($this->docman === null || !$this->docman->active) {
      // no data
      $this->Template->active = false;
      return;
    }
    // get action info
    $action = \Input::get('zA');
    switch ($action) {
      case 'fd':  // file download
        $id = \Input::get('zF');
        return $this->fileDownload($id);
      default:  // list documents
        $id = \Input::get('zP');
        return $this->documentList($id);
    }
	}

	/**
	 * Show document list
	 *
	 * @param string $page  Page id
	 */
	protected function documentList($page) {
    // set template
    $this->Template = new \FrontendTemplate('zaddm_reader');
    // get info fields
    $fields = deserialize($this->docman->infoFields);
    // get groupby fields
    $groupby = deserialize($this->docman->groupbyList);
    // set base url
    $base_url = $this->createBaseUrl();
    // show data
    $menu = array();
    $data = array();
    $index = 0;
    if ($page === null && count($groupby) > 0) {
      // get menu pages
      $docs = \ZadDocmanDocModel::getMenus($this->docman->id, $fields, $groupby);
      if ($docs !== null) {
        while ($docs->next()) {
          $new_page = array();
          $menu[$index]['name'] = $this->docman->grouplabel;
          if ($menu[$index]['name'] == '') {
            // use field names
            foreach ($groupby as $key=>$value) {
              $new_page[] = $docs->$value;
              $fkey = substr($value, 6);
              if ($fields[$fkey]['type'] == 'choice') {
                // single choice
                $list = explode(',', $fields[$fkey]['list']);
                $fval = $list[$docs->$value];
              } elseif ($fields[$fkey]['type'] == 'mchoice') {
                // multi choice
                $values = array();
                $list = explode(',', $fields[$fkey]['list']);
                foreach (deserialize($docs->$value) as $v) {
                  $values[] = $list[$v];
                }
                $fval = implode(', ', $values);
              } elseif ($fields[$fkey]['type'] == 'date') {
                // date
                $list = explode('-', $docs->$value);
                $fval = $GLOBALS['TL_LANG']['MONTHS'][(int) $list[1] - 1].' '.$list[0];
              } elseif ($fields[$fkey]['type'] == 'auto' && $fields[$fkey]['auto_field'] == 'timestamp') {
                // auto:timestamp
                $list = explode('-', $docs->$value);
                $fval = $GLOBALS['TL_LANG']['MONTHS'][(int) $list[1] - 1].' '.$list[0];
              } else {
                // any other type
                $fval = $docs->$value;
              }
              $menu[$index]['name'] .= $fields[$fkey]['name'].': '.$fval.' - ';
            }
            $menu[$index]['name'] = substr($menu[$index]['name'], 0, -3);
          } else {
            // set new page
            foreach ($groupby as $key=>$value) {
              $new_page[] = $docs->$value;
            }
            // set menu name
            foreach ($fields as $key=>$value) {
              if (strpos($menu[$index]['name'], '{{'.($key+1).'}}') !== false) {
                $fkey = 'field_'.$key;
                if ($value['type'] == 'choice') {
                  // single choice
                  $list = explode(',', $value['list']);
                  $fval = $list[$docs->$fkey];
                } elseif ($value['type'] == 'mchoice') {
                  // multi choice
                  $values = array();
                  $list = explode(',', $value['list']);
                  foreach (deserialize($docs->$fkey) as $v) {
                    $values[] = $list[$v];
                  }
                  $fval = implode(', ', $values);
                } elseif ($value['type'] == 'date') {
                  // date
                  $list = explode('-', $docs->$fkey);
                  $fval = $GLOBALS['TL_LANG']['MONTHS'][(int) $list[1] - 1].' '.$list[0];
                } elseif ($value['type'] == 'auto' && $value['auto_field'] == 'timestamp') {
                  // auto:timestamp
                  $list = explode('-', $docs->$fkey);
                  $fval = $GLOBALS['TL_LANG']['MONTHS'][(int) $list[1] - 1].' '.$list[0];
                } else {
                  // any other type
                  $fval = $docs->$fkey;
                }
                $menu[$index]['name'] = str_replace('{{'.($key+1).'}}', $fval, $menu[$index]['name']);
              }
            }
          }
          // create page menu url
          $param = array();
          $param['zP'] = urlencode(serialize($new_page));
          $param['zL'] = urlencode($menu[$index]['name']);
          $param['zA'] = 'dl';
          $menu[$index]['href'] = $this->createUrl($param, $base_url);
          $index++;
        }
      }
    } else {
      // show documents
      $lbl_groupby = \Input::get('zL');
      $docs = \ZadDocmanDocModel::getDocuments($this->docman->id, $fields, $groupby, $page);
      if ($docs !== null) {
        while ($docs->next()) {
          $param = array();
          // create file download url
          $data[$index]['href_document'] = null;
          $file = \FilesModel::findByUuid($docs->document);
          if ($file !== null) {
            $param['zF'] = $file->name;
            $param['zA'] = 'fd';
            $data[$index]['href_document'] = $this->createUrl($param, $base_url);
          }
          if ($docs->attach) {
            foreach (deserialize($docs->attach) as $key=>$item) {
              $data[$index]['href_attach'.($key+1)] = null;
              $file = \FilesModel::findByUuid($item);
              if ($file !== null) {
                $param['zF'] = $file->name;
                $param['zA'] = 'fd';
                $data[$index]['href_attach'.($key+1)] = $this->createUrl($param, $base_url);
              }
            }
          }
          // info fields
          $data[$index]['fields'] = array();
          $options = array('order' => 'field ASC');
          $info = \ZadDocmanInfoModel::findByPid($docs->id, $options);
          $lbl_document = $this->docman->filelabel;
          if ($info !== null) {
            while ($info->next()) {
              if ($fields[$info->field]['type'] == 'choice') {
                // single choice
                $list = explode(',', $fields[$info->field]['list']);
                $value = $list[$info->value];
              } elseif ($fields[$info->field]['type'] == 'mchoice') {
                // multi choice
                $value = array();
                $list = explode(',', $fields[$info->field]['list']);
                foreach (deserialize($info->value) as $val) {
                  $value[] = $list[$val];
                }
                $value = implode(', ', $value);
              } elseif ($fields[$info->field]['type'] == 'date') {
                // date
                $date = new \Date($info->value);
                $value = $date->date;
              } elseif ($fields[$info->field]['type'] == 'auto') {
                // auto
                if ($fields[$info->field]['auto_field'] == 'timestamp') {
                  // parse datetime
                  $date = new \Date($info->value);
                  $value = $date->datim;
                } elseif ($fields[$info->field]['auto_field'] == 'user') {
                  // do nothing
                  $value = $info->value;
                }
              } else {
                // text/number
                $value = $info->value;
              }
              $data[$index]['fields'][$info->field] = $value;
              if (strpos($lbl_document, '{{'.($info->field+1).'}}') !== false) {
                $lbl_document = str_replace('{{'.($info->field+1).'}}', $value, $lbl_document);
              }
            }
          }
          $data[$index]['label_document'] = $lbl_document;
          $index++;
        }
      }
    }
    // referer url
    $this->Template->referer = $base_url;
    // set other template vars
    $this->Template->fields = $fields;
    $this->Template->menu = $menu;
    $this->Template->docs = $data;
    $this->Template->lbl_groupby = $lbl_groupby;
    $this->Template->msg_nodata = $GLOBALS['TL_LANG']['tl_zad_docman']['msg_nodata'];
    $this->Template->lbl_documentlist_alt = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentlist_alt'];
    $this->Template->lbl_attach1 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach1'];
    $this->Template->lbl_attach2 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach2'];
    $this->Template->lbl_attach3 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach3'];
    $this->Template->lbl_attach4 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach4'];
    $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
	}

  /**
	 * Download a file
	 *
	 * @param int $filename  The file name
	 */
	protected function fileDownload($filename) {
    $dir = \FilesModel::findByUuid($this->docman->dir);
    if ($dir === null) {
      // error no folder
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = true;
  		$this->Template->referer = $this->createBaseUrl();
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dlfile'];
      return;
    }
    $file = \FilesModel::findByPath($dir->path.'/'.$filename);
    if ($file === null) {
      // error no file
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = true;
  		$this->Template->referer = $this->createBaseUrl();
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dlfile'];
      return;
    }
    $doc = \ZadDocmanDocModel::findByFile($file->uuid);
    if ($doc === null) {
      // error no document
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = true;
  		$this->Template->referer = $this->createBaseUrl();
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dldoc'];
      return;
    }
    $fileobj = new \File($dir->path.'/'.$filename);
    if ($fileobj === null) {
      // error no file object
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = true;
  		$this->Template->referer = $this->createBaseUrl();
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dlfile'];
      return;
    }
    // create name of the file
    $name = $this->docman->filename;
    $fields = deserialize($this->docman->infoFields);
    $options = array('order' => 'field ASC');
    $info = \ZadDocmanInfoModel::findByPid($doc->id, $options);
    if ($info !== null) {
      while ($info->next()) {
        if (strpos($name, '{{'.($info->field+1).'}}') !== false) {
          if ($fields[$info->field]['type'] == 'choice') {
            // single choice
            $list = explode(',', $fields[$info->field]['list']);
            $value = str_replace(array(' '), '-', trim($list[$info->value]));
          } elseif ($fields[$info->field]['type'] == 'mchoice') {
            // multi choice
            $value = array();
            $list = explode(',', $fields[$info->field]['list']);
            foreach (deserialize($info->value) as $val) {
              $value[] = str_replace(array(' '), '-', trim($list[$val]));
            }
            $value = implode('-', $value);
          } elseif ($fields[$info->field]['type'] == 'date') {
            // date
            $date = new \Date($info->value);
            $value = str_replace(array('/','_','.',':',' '), '-', $date->date);
          } elseif ($fields[$info->field]['type'] == 'auto' && $fields[$info->field]['auto_field'] == 'timestamp') {
            // auto:timestamp
            $date = new \Date($info->value);
            $value = str_replace(array('/','_','.',':',' '), '-', $date->datim);
          } else {
            // text/number/auto:user
            $value = str_replace(array(' '), '-', trim($info->value));
          }
          $name = str_replace('{{'.($info->field+1).'}}', $value, $name);
        }
      }
    }
    // add attachment label
    if ($doc->document != $file->uuid) {
      foreach (deserialize($doc->attach) as $key=>$item) {
        if ($item == $file->uuid) {
          // add label
          $name .= '_' . $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_fname_attach'.($key+1)];
        }
      }
    }
		// normalize the file name
		$name = utf8_romanize($name);
		$name = preg_replace('/[^A-Za-z0-9_-]/', '', $name);
    // send file end exit
    $fileobj->sendToBrowser($name.'.'.$file->extension);
  }

  /**
	 * Create a base URL without some parameters
	 *
	 * @param array $params List of parameters to be deleted
	 *
	 * @return string  The new URL
	 */
	protected function createBaseUrl() {
    $params=array('zA', 'zP', 'zF', 'zL');
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
  			$q = '?' . implode('&amp;', $queries);
  		}
    }
    return $base[0] . $q;
  }

  /**
	 * Create a new URL with some parameters
	 *
	 * @param array $params List of couples key=>value to be added
	 * @param string $base The base url
	 *
	 * @return string  The new URL
	 */
	protected function createUrl($params, $base='') {
    if ($base == '') {
      $base = $this->createBaseUrl();
    }
    // create query list
    $queries = array();
		foreach ($params as $k=>$v) {
      $queries[] = "$k=$v";
    }
    $q = implode('&amp;', $queries);
    return $base . ((strpos($base, '?') === false) ? '?' : '&amp;') . $q;
  }

}

