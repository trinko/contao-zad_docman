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
 * Class ModuleZadDocmanManager
 *
 * Front end module "Document Manager".
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class ModuleZadDocmanManager extends \Module {

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'zaddm_message';

	/**
	 * ID of the logged user
	 * @var int
	 */
	protected $userId = 0;

	/**
	 * True if user is a manager
	 * @var bool
	 */
	protected $isManager = false;

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
      $template->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['zad_docman_manager'][0]) . ' ###';
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
    // check if a member is logged
    if (!FE_USER_LOGGED_IN) {
      // error no member logged
      $this->Template->active = true;
  		$this->Template->referer = $this->createBaseUrl();
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_nologged'];
      return;
    }
    // check logged member
    $this->import('FrontendUser', 'User');
    $this->userId = $this->User->id;
    $groups = deserialize($this->docman->groups);
    $this->isManager = false;
   	if (in_array($this->docman->manager, $this->User->groups)) {
      // member is manager
      $this->isManager = true;
    } elseif (!is_array($groups) || empty($groups) || !count(array_intersect($groups, $this->User->groups))) {
      // member not allowed
      $this->Template->active = true;
  		$this->Template->referer = $this->createBaseUrl();
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'];
      return;
    }
    // get action info
    $action = \Input::get('zA');
    switch ($action) {
      case 'da':  // add document
        return $this->documentEdit();
      case 'dax':  // add document exec
        return $this->documentEditExec();
      case 'de':  // edit document
        $id = intval(\Input::get('zD'));
        return $this->documentEdit($id);
      case 'dex':  // edit document exec
        $id = intval(\Input::get('zD'));
        return $this->documentEditExec($id);
      case 'dd':  // delete document
        $id = intval(\Input::get('zD'));
        return $this->documentDelete($id);
      case 'ddx':  // delete document exec
        $id = intval(\Input::get('zD'));
        return $this->documentDeleteExec($id);
      case 'fd':  // file download
        $id = \Input::get('zF');
        return $this->fileDownload($id);
      default:  // list documents
        $id = intval(\Input::get('zP'));
        return $this->documentList($id);
    }
	}

	/**
	 * Show document list
	 *
	 * @param int $page  Page number
	 */
	protected function documentList($page=0) {
    // set template
    $this->Template = new \FrontendTemplate('zaddm_list');
    // get info fields
    $fields = deserialize($this->docman->infoFields);
    // set base url
    $base_url = $this->createBaseUrl();
    // set add url
    $param['zA'] = 'da';
    $this->Template->href_add = $this->createUrl($param, $base_url);
    // pagination
    $limit = null;
    $offset = null;
    $show_others = (!$this->isManager && !$this->docman->enableOthers) ? $this->userId : null;
    $total = \ZadDocmanDocModel::countDocuments($this->docman->id, $show_others);
		if ($this->docman->perPage > 0 && $total > $this->docman->perPage) {
      // adjust page number
      if ($page < 1) {
        // first page
        $page = 1;
      } elseif ($page > ceil($total / $this->docman->perPage)) {
        // last page
        $page = ceil($total / $this->docman->perPage);
      }
			// set limit and offset
			$limit = $this->docman->perPage;
			$offset = ($page - 1) * $this->docman->perPage;
			if ($offset + $limit > $total) {
				$limit = $total - $offset;
			}
			// add the pagination menu
			$pagination = new \Pagination($total, $this->docman->perPage, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], 'zP');
			$this->Template->pagination = $pagination->generate("\n  ");
    }
    // get docs
    $data = array();
    $docs = \ZadDocmanDocModel::findDocuments($this->docman->id, $show_others, $fields, $offset, $limit);
    if ($docs !== null) {
      $index = 0;
      while ($docs->next()) {
        $param = array();
        $data[$index]['id'] = $docs->id;
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
          }
        }
        // buttons
        if (!$this->isManager && $this->docman->enableOthers && $docs->sentBy != $this->userId) {
          // user can't edit/delete document
          $data[$index]['href_edit'] = null;
          $data[$index]['href_delete'] = null;
        } else {
          // user can edit/delete document
          $param = array();
          $param['zD'] = $docs->id;
          $param['zA'] = 'de';
          $data[$index]['href_edit'] = $this->createUrl($param, $base_url);
          $param['zA'] = 'dd';
          $data[$index]['href_delete'] = $this->createUrl($param, $base_url);
        }
        $index++;
      }
    }
    // set other template vars
    $this->Template->fields = $fields;
    $this->Template->docs = $data;
    $this->Template->isManager = $this->isManager;
    $this->Template->msg_nodata = $GLOBALS['TL_LANG']['tl_zad_docman']['msg_nodata'];
    $this->Template->lbl_documentlist_alt = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentlist_alt'];
    $this->Template->lbl_documentlist = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentlist'];
    $this->Template->lbl_document = ($this->docman->doclabel ?: $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document']);
    $this->Template->lbl_attach1 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach1'];
    $this->Template->lbl_attach2 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach2'];
    $this->Template->lbl_attach3 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach3'];
    $this->Template->lbl_attach4 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach4'];
    $this->Template->lbl_add = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_add'];
    $this->Template->lbl_edit = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_edit'];
    $this->Template->lbl_delete = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_delete'];
	}

	/**
	 * Show the form for editing a document
	 *
	 * @param int $id  ID of the document to edit (0=add a new one)
	 */
	protected function documentEdit($id=0) {
    // init session
    unset($_SESSION['zad_docman']);
    // set template
    $this->Template = new \FrontendTemplate('zaddm_edit');
    // set base url
    $base_url = $this->createBaseUrl();
    // get info fields
    $fields = deserialize($this->docman->infoFields);
    if ($id > 0) {
      // edit a document
      $param['zA'] = 'dex';
      $param['zD'] = $id;
      $this->Template->href_action = $this->createUrl($param, $base_url);
      $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentedit'];
      // get doc
      $doc = \ZadDocmanDocModel::findByPk($id);
      if ($doc === null) {
        // invalid id
        $this->Template = new \FrontendTemplate('zaddm_message');
        $this->Template->active = true;
    		$this->Template->referer = $base_url;
    		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_id'];
        return;
      }
      if (!$this->isManager && $doc->sentBy != $this->userId) {
        // user can't edit document
        $this->Template = new \FrontendTemplate('zaddm_message');
        $this->Template->active = true;
    		$this->Template->referer = $base_url;
    		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'];
        return;
      }
      // info fields
      $options = array('order' => 'field ASC');
      $info = \ZadDocmanInfoModel::findByPid($doc->id, $options);
      if ($info !== null) {
        // get data
        while ($info->next()) {
          if ($fields[$info->field]['type'] == 'date') {
            // date
            $date = new \Date($info->value);
            $fields[$info->field]['value'] = $date->date;
          } elseif ($fields[$info->field]['type'] == 'auto' && $fields[$info->field]['auto_field'] == 'timestamp') {
            // auto:timestamp
            $date = new \Date($info->value);
            $fields[$info->field]['value'] = $date->datim;
          } else {
            // string/number/choice/mchoice
            $fields[$info->field]['value'] = $info->value;
          }
        }
      } else {
        // reset values on error
        foreach ($fields as $key=>$value) {
          $fields[$key]['value'] = null;
        }
      }
      // document
      $this->Template->lbl_document_exists = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document_exists'];
      $_SESSION['zad_docman']['document']['uuid'] = $doc->document;
      // file attach
      if ($this->docman->enableAttach && !empty($doc->attach)) {
        foreach (deserialize($doc->attach) as $key=>$item) {
          $fieldname = 'lbl_attach'.($key+1).'_exists';
          $this->Template->$fieldname = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach'.($key+1).'_exists'];
          $_SESSION['zad_docman']['attach'.($key+1)]['uuid'] = $item;
        }
      }
    } else {
      // add a new document
      $param['zA'] = 'dax';
      $this->Template->href_action = $this->createUrl($param, $base_url);
      $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentadd'];
      // info fields
      foreach ($fields as $key=>$value) {
        $fields[$key]['value'] = null;
      }
    }
    // set other template vars
    $this->Template->isManager = $this->isManager;
    $this->Template->error = array();
    $this->Template->fields = $fields;
    $this->Template->attach = $this->docman->enableAttach;
    $this->Template->lbl_document = ($this->docman->doclabel ?: $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document']);
    $this->Template->lbl_attach1 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach1'];
    $this->Template->lbl_attach2 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach2'];
    $this->Template->lbl_attach3 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach3'];
    $this->Template->lbl_attach4 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach4'];
    $this->Template->lbl_remove_attach1 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_remove_attach1'];
    $this->Template->lbl_remove_attach2 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_remove_attach2'];
    $this->Template->lbl_remove_attach3 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_remove_attach3'];
    $this->Template->lbl_remove_attach4 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_remove_attach4'];
    $this->Template->lbl_mandatory = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory'];
    $this->Template->lbl_save = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_save'];
    $this->Template->lbl_cancel = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_cancel'];
  }

	/**
	 * Edit or create a document
	 *
	 * @param int $id  ID of the document to edit (0=add a new one)
	 */
	protected function documentEditExec($id=0) {
    // init
    $error = array();
    // set base url
    $base_url = $this->createBaseUrl();
    // get info fields
    $fields = deserialize($this->docman->infoFields);
    // security check
    if ($id > 0) {
      // edit a document
      $doc = \ZadDocmanDocModel::findByPk($id);
      if ($doc === null) {
        // invalid id
        $this->Template = new \FrontendTemplate('zaddm_message');
        $this->Template->active = true;
    		$this->Template->referer = $base_url;
    		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_id'];
        return;
      }
      if (!$this->isManager && $doc->sentBy != $this->userId) {
        // user can't edit document
        $this->Template = new \FrontendTemplate('zaddm_message');
        $this->Template->active = true;
    		$this->Template->referer = $base_url;
    		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'];
        return;
      }
    }
    if (strlen(\Input::post('_save')) == 0) {
      // cancel button pressed
      $this->redirect($base_url);
    }
    // check info data
    foreach ($fields as $key=>$item) {
      $fields[$key]['value'] = \Input::post('field_'.$key);
      if (!is_array($fields[$key]['value'])) {
        $fields[$key]['value'] = trim($fields[$key]['value']);
      }
      if ($item['type'] == 'number') {
        // type number
        $fields[$key]['value'] = intval($fields[$key]['value']);
        if ($item['mandatory'] && $fields[$key]['value'] == 0) {
          // no data
          $error['field_'.$key] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
        }
      } elseif ($item['type'] == 'date') {
        // type date
        if ($item['mandatory'] && empty($fields[$key]['value'])) {
          // no data
          $error['field_'.$key] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
        } elseif (!empty($fields[$key]['value'])) {
          // check format
          $date = new \Date($fields[$key]['value'], $GLOBALS['TL_CONFIG']['dateFormat']);
          if (!$date || $date->date != $fields[$key]['value']) {
            // invalid format
            $error['field_'.$key] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dateformat'];
          } else {
            // ok, save as timestamp
            $fields[$key]['value'] = $date->timestamp;
          }
        }
      } elseif ($item['type'] == 'choice') {
        // type single choice
        $fields[$key]['value'] = intval($fields[$key]['value']);
      } elseif ($item['type'] == 'mchoice') {
        // type multi choice
        if ($item['mandatory'] && (empty($fields[$key]['value']) || count($fields[$key]['value']) == 0)) {
          // no data
          $error['field_'.$key] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
        } else {
          // serialize array
          $fields[$key]['value'] = serialize($fields[$key]['value']);
        }
      } elseif ($item['type'] == 'auto') {
        // type auto
        if ($item['auto_field'] == 'timestamp') {
          // timestamp field
          if (empty($fields[$key]['value']) || !$this->isManager || !$item['auto_editable']) {
            // default
            $fields[$key]['value'] = time();
          } else {
            // check format
            $date = new \Date($fields[$key]['value'], $GLOBALS['TL_CONFIG']['datimFormat']);
            if (!$date || $date->datim != $fields[$key]['value']) {
              // invalid format, default value
              $fields[$key]['value'] = time();
            } else {
              // valid format, store as timestamp
              $fields[$key]['value'] = $date->timestamp;
            }
          }
        } elseif ($item['auto_field'] == 'user') {
          // user field
          $user = \MemberModel::findByPk($this->userId);
          $username = ($user === null) ? '?' : $user->lastname.' '.$user->firstname;
          if (empty($fields[$key]['value']) || !$this->isManager || !$item['auto_editable']) {
            // default
            $fields[$key]['value'] = $username;
          } else {
            // check user
            $cond_fields = array("concat(tl_member.lastname,' ',tl_member.firstname)='".$fields[$key]['value']."'");
            $options = array('limit' => 1);
            $user = \MemberModel::findBy($cond_fields, null, $options);
            $fields[$key]['value'] = ($user === null) ? $username : $user->lastname.' '.$user->firstname;
          }
        }
      } else {
        // type text
        $fields[$key]['value'] = trim($fields[$key]['value']);
        if ($item['mandatory'] && empty($fields[$key]['value'])) {
          // no data
          $error['field_'.$key] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
        }
      }
    }
    // check file document
    if (!isset($_SESSION['zad_docman']['document']) && (!isset($_FILES['document']) || empty($_FILES['document']['name']))) {
      // no document
      $error['document'] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
    } elseif (isset($_FILES['document']) && !empty($_FILES['document']['name'])) {
      // save document
      $err = $this->saveFileAttachment('document');
      if ($err !== null) {
        // upload error
        $error['document'] = $err;
      }
    }
    // check file attach 1
    if ($this->docman->enableAttach && isset($_FILES['attach1']) && !empty($_FILES['attach1']['name'])) {
      // save file
      $err = $this->saveFileAttachment('attach1');
      if ($err !== null) {
        // upload error
        $error['attach1'] = $err;
      }
    } elseif (isset($_SESSION['zad_docman']['attach1']) && \Input::post('remove_attach1')) {
        // remove file
        $this->deleteFileAttachment('attach1');
    }
    // check file attach 2
    if ($this->docman->enableAttach && isset($_FILES['attach2']) && !empty($_FILES['attach2']['name'])) {
      // save file
      $err = $this->saveFileAttachment('attach2');
      if ($err !== null) {
        // upload error
        $error['attach2'] = $err;
      }
    } elseif (isset($_SESSION['zad_docman']['attach2']) && \Input::post('remove_attach2')) {
        // remove file
        $this->deleteFileAttachment('attach2');
    }
    // check file attach 3
    if ($this->docman->enableAttach && isset($_FILES['attach3']) && !empty($_FILES['attach3']['name'])) {
      // save file
      $err = $this->saveFileAttachment('attach3');
      if ($err !== null) {
        // upload error
        $error['attach3'] = $err;
      }
    } elseif (isset($_SESSION['zad_docman']['attach3']) && \Input::post('remove_attach3')) {
        // remove file
        $this->deleteFileAttachment('attach3');
    }
    // check file attach 4
    if ($this->docman->enableAttach && isset($_FILES['attach4']) && !empty($_FILES['attach4']['name'])) {
      // save file
      $err = $this->saveFileAttachment('attach4');
      if ($err !== null) {
        // upload error
        $error['attach4'] = $err;
      }
    } elseif (isset($_SESSION['zad_docman']['attach4']) && \Input::post('remove_attach4')) {
        // remove file
        $this->deleteFileAttachment('attach4');
    }
    // save or show errors
    if (empty($error)) {
      // save document
      if ($id == 0) {
        // new document
  		  $doc = new \ZadDocmanDocModel();
      }
      $doc->pid = $this->docman->id;
      $doc->tstamp = time();
      $doc->document = $this->storefile('document');
      $attach_ids = array();
      for ($key = 1; $key <= 4; $key++) {
        if (isset($_SESSION['zad_docman']['attach'.$key])) {
          $attach_ids[] = $this->storefile('attach'.$key);
        }
      }
      $doc->attach = (count($attach_ids) > 0) ? serialize($attach_ids) : null;
      $doc->sentBy = $this->userId;
      $doc->save();
      // save info
      foreach ($fields as $key=>$item) {
        if ($id > 0) {
          // existent data
          $cond[0] = 'tl_zad_docman_info.pid='.$doc->id;
          $cond[1] = 'tl_zad_docman_info.field='.$key;
          $options = array('limit' => 1);
          $info = \ZadDocmanInfoModel::findBy($cond, null, $options);
          if ($info === null) {
            // on error, create a new one
            $info = new \ZadDocmanInfoModel();
          }
        } else {
          // new data
  		    $info = new \ZadDocmanInfoModel();
        }
        $info->pid = $doc->id;
        $info->tstamp = $doc->tstamp;
        $info->field = $key;
        $info->value = $item['value'];
        $info->save();
      }
      // remove old files
      $this->removeFiles();
      // go to document list
      $this->redirect($base_url);
    } else {
      // show errors
      $this->Template = new \FrontendTemplate('zaddm_edit');
      if ($id > 0) {
         // edit a document
        $param['zA'] = 'dex';
        $param['zD'] = $id;
        $this->Template->href_action = $this->createUrl($param, $base_url);
        $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentedit'];
      } else {
         // add a document
        $param['zA'] = 'dax';
        $this->Template->href_action = $this->createUrl($param, $base_url);
        $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentadd'];
      }
      // document
      if (isset($_SESSION['zad_docman']['document'])) {
        $this->Template->lbl_document_exists = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document_exists'];
      }
      // file attach
      for ($key = 1; $key <= 4; $key++) {
        if (isset($_SESSION['zad_docman']['attach'.$key])) {
          $fieldname = 'lbl_attach'.$key.'_exists';
          $this->Template->$fieldname = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach'.$key.'_exists'];
        }
      }
      // check data
      foreach ($fields as $key=>$item) {
        if ($item['type'] == 'date') {
          // type date
          $date = new \Date($fields[$key]['value']);
          $fields[$key]['value'] = $date->date;
        } elseif ($item['type'] == 'auto' && $item['auto_field'] == 'timestamp') {
          // type auto:timestamp
          $date = new \Date($fields[$key]['value']);
          $fields[$key]['value'] = $date->datim;
        }
      }
      // other template vars
      $this->Template->isManager = $this->isManager;
      $this->Template->error = $error;
      $this->Template->fields = $fields;
      $this->Template->attach = $this->docman->enableAttach;
      $this->Template->lbl_document = ($this->docman->doclabel ?: $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document']);
      $this->Template->lbl_attach1 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach1'];
      $this->Template->lbl_attach2 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach2'];
      $this->Template->lbl_attach3 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach3'];
      $this->Template->lbl_attach4 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach4'];
      $this->Template->lbl_remove_attach1 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_remove_attach1'];
      $this->Template->lbl_remove_attach2 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_remove_attach2'];
      $this->Template->lbl_remove_attach3 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_remove_attach3'];
      $this->Template->lbl_remove_attach4 = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_remove_attach4'];
      $this->Template->lbl_mandatory = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory'];
      $this->Template->lbl_save = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_save'];
      $this->Template->lbl_cancel = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_cancel'];
    }
  }

	/**
	 * Show the confirm for deleting a document
	 *
	 * @param int $id  ID of the document to delete
	 */
	protected function documentDelete($id) {
    // get info fields
    $fields = deserialize($this->docman->infoFields);
    // set base url
    $base_url = $this->createBaseUrl();
    // get doc
    $doc = \ZadDocmanDocModel::findByPk($id);
    if ($doc === null) {
      // invalid id
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = true;
  		$this->Template->referer = $base_url;
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_id'];
      return;
    }
    if (!$this->isManager && $doc->sentBy != $this->userId) {
      // user can't delete document
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = true;
  		$this->Template->referer = $base_url;
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'];
      return;
    }
    // info fields
    $options = array('order' => 'field ASC');
    $info = \ZadDocmanInfoModel::findByPid($doc->id, $options);
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
        $fields[$info->field]['value'] = $value;
      }
    }
    // set template
    $this->Template = new \FrontendTemplate('zaddm_confirm');
    // set template vars
    $param['zA'] = 'ddx';
    $param['zD'] = $id;
    $this->Template->href_action = $this->createUrl($param, $base_url);
    $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentdelete'];
    $this->Template->isManager = $this->isManager;
    $this->Template->fields = $fields;
    $this->Template->lbl_confirm = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_confirm'];
    $this->Template->lbl_cancel = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_cancel'];
  }

  /**
	 * Delete a document
	 *
	 * @param int $id  ID of the document to delete
	 */
	protected function documentDeleteExec($id) {
    // set base url
    $base_url = $this->createBaseUrl();
    // get doc
    $doc = \ZadDocmanDocModel::findByPk($id);
    if ($doc === null) {
      // invalid id
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = true;
  		$this->Template->referer = $base_url;
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_id'];
      return;
    }
    if (!$this->isManager && $doc->sentBy != $this->userId) {
      // user can't delete document
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = true;
  		$this->Template->referer = $base_url;
  		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
      $this->Template->message = $GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'];
      return;
    }
    if (strlen(\Input::post('_confirm')) == 0) {
      // cancel button pressed
      $this->redirect($base_url);
    }
    // delete files
    $file = \FilesModel::findByUuid($doc->document);
		$this->import('Files');
		$this->Files->delete($file->path);
    $file->delete();
    if ($this->docman->enableAttach && !empty($doc->attach)) {
      $attach = deserialize($doc->attach);
      foreach ($attach as $fl) {
        $file = \FilesModel::findByUuid($fl);
        $this->Files->delete($file->path);
        $file->delete();
      }
    }
    // delete info
    $info = \ZadDocmanInfoModel::findByPid($doc->id);
    if ($info !== null) {
      while ($info->next()) {
        // delete data
        $info->delete();
      }
    }
    // delete document
    $doc->delete();
    // go to document list
    $this->redirect($base_url);
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
    $params=array('zA', 'zD', 'zP', 'zF');
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

	/**
	 * Save file attachment
	 *
	 * @param string $name  Parameter name in "$_FILE" array
	 * @param string $filename  File name for the uploaded file, without extension
	 *
	 * @return string  An error messages or null
	 */
	protected function saveFileAttachment($name, $filename='') {
    // init
    $this->deleteFileAttachment($name);
    $error = null;
		$file = $_FILES[$name];
    $filename = ($filename == '') ? pathinfo($file['name'], PATHINFO_FILENAME) : $filename;
		$filename = utf8_romanize($filename);
    $fileext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_types = explode(',', strtolower($this->docman->fileTypes));
    $allowed_types = array_intersect($allowed_types, explode(',', strtolower($GLOBALS['TL_CONFIG']['uploadTypes'])));
    $folder = \FilesModel::findByUuid($this->docman->dir);
    // check
    if (!is_uploaded_file($file['tmp_name'])) {
		  // file was not uploaded
			if ($file['error'] == 1 || $file['error'] == 2) {
        // file size error
        $error = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filesize'];
			} else {
        // upload error
        $error = $GLOBALS['TL_LANG']['tl_zad_docman']['err_file'];
			}
	  } elseif ($file['size'] > $GLOBALS['TL_CONFIG']['maxFileSize']) {
  		// file is too big
      $error = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filesize'];
	  } elseif (!in_array($fileext, $allowed_types)) {
		  // file type is not allowed
      $error = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filetype'];
	  } elseif ($folder === null || $folder->path == '' || !is_dir(TL_ROOT . '/' . $folder->path)) {
      // no folder
      $error = $GLOBALS['TL_LANG']['tl_zad_docman']['err_folder'];
	  } else {
  		$this->import('Files');
      $path = 'system/tmp/'.pathinfo($file['tmp_name'], PATHINFO_BASENAME);
      $this->Files->move_uploaded_file($file['tmp_name'], $path);
	    $this->Files->chmod($path, $GLOBALS['TL_CONFIG']['defaultFileChmod']);
      $_SESSION['zad_docman'][$name]['tmp_name'] = $path;
      $_SESSION['zad_docman'][$name]['folder'] = $folder->path;
      $_SESSION['zad_docman'][$name]['name'] = $filename;
      $_SESSION['zad_docman'][$name]['ext'] = $fileext;
    }
    // return error message
    return $error;
  }

	/**
	 * Delete file previuosly saved in SESSION
	 *
	 * @param string $name  Parameter name in "$_SESSION" array
	 */
	protected function deleteFileAttachment($name) {
    // get data from session
    $file = $_SESSION['zad_docman'][$name];
    if (isset($file['uuid'])) {
      // save file to be removed
      $_SESSION['zad_docman']['__TO_REMOVE__'][] = $file['uuid'];
    }
    if (isset($file['tmp_name'])) {
      // delete temp file
  		$this->import('Files');
	    $this->Files->delete($file['tmp_name']);
    }
    // delete session data
    unset($_SESSION['zad_docman'][$name]);
  }

	/**
	 * Store file previously saved in SESSION
	 *
	 * @param string $name  Parameter name in "$_SESSION" array
	 *
	 * @return string  The file UUID stored in the database
	 */
	protected function storeFile($name) {
    // get data from session
    $file = $_SESSION['zad_docman'][$name];
    if (isset($file['uuid'])) {
      // file already exists
      return $file['uuid'];
    }
    // unique name
    $filename = $this->userId.'-'.str_replace('.', '-', microtime(true)).'-'.rand(0,999);
    // store file
    $path = $file['folder'].'/'.$filename.'.';
    $file['ext'] = strtolower($file['ext']);
    $filename_doc = $path.$file['ext'];
		$this->import('Files');
    $this->Files->rename($file['tmp_name'], $filename_doc);
	  // convert to PDF
	  if ($this->docman->enablePdf && $file['ext'] != 'pdf') {
	    $filename_pdf = $path.'pdf';
      $cmd = 'python ' . TL_ROOT . '/system/modules/zad_docman/vendor/pyodconverter/DocumentConverter.py "' .
        $filename_doc . '" "' . $filename_pdf . '"';
      $res = exec($cmd);
      if (strlen($res) == 0 && file_exists($filename_pdf)) {
        // PDF file created, remove original one
        unlink($filename_doc);
        $filename_doc = $filename_pdf;
      }
    }
		// generate the DB entries
	  $fileobj = \FilesModel::findByPath($filename_doc);
		// existing file is being replaced
	  if ($fileobj !== null) {
      // update file info
			$fileobj->tstamp = time();
			$fileobj->path = $filename_doc;
			$fileobj->hash = md5_file(TL_ROOT.'/'.$filename_doc);
			$fileobj->save();
		} else {
      // new file info
		  $fileobj = \Dbafs::addResource($filename_doc);
		}
		// update the hash of the target folder
		\Dbafs::updateFolderHashes($file['folder']);
    // delete session data
    unset($_SESSION['zad_docman'][$name]);
    // return UUID
    return $fileobj->uuid;
  }

	/**
	 * Remove existent files marked in SESSION
	 */
	protected function removeFiles() {
    if (isset($_SESSION['zad_docman']['__TO_REMOVE__'])) {
		  $this->import('Files');
      foreach ($_SESSION['zad_docman']['__TO_REMOVE__'] as $id) {
        // delete file
        $file = \FilesModel::findByUuid($id);
		    $this->Files->delete($file->path);
        $file->delete();
      }
    }
  }

}

