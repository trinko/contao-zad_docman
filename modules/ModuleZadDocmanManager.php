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
 *
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class ModuleZadDocmanManager extends \ModuleZadDocman {

	/**
	 * ID of the logged user
	 *
	 * @var int
	 */
	protected $userId = 0;

	/**
	 * True if user is a document administrator
	 *
	 * @var bool
	 */
	protected $isAdmin = false;


	/**
	 * Display a wildcard in the back end
	 *
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
    // get data
    $this->docman = \ZadDocmanArchiveModel::findByPk($this->zad_docman_archive);
    if ($this->docman === null || !$this->docman->active) {
      // no data: exit without any output
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = false;
      return;
    }
    // check if a member is logged
    if (!FE_USER_LOGGED_IN) {
      // error, no member logged
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nologged'], $this->createBaseUrl());
      return;
    }
    // check logged member
    $this->import('FrontendUser', 'User');
    $this->userId = $this->User->id;
    $groups = deserialize($this->docman->groups);
    $this->isAdmin = false;
   	if (in_array($this->docman->manager, $this->User->groups)) {
      // member is document administrator
      $this->isAdmin = true;
    } elseif (!is_array($groups) || empty($groups) || !count(array_intersect($groups, $this->User->groups))) {
      // error, member not allowed
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $this->createBaseUrl());
      return;
    }
    // get action info
    $action = \Input::get('zdA');
    switch ($action) {
      case 'upload':  // upload files
        $id = intval(\Input::get('zdD'));
        $this->fileUpload($id);
        break;
      case 'cancel':  // cancel uploaded files
        $id = intval(\Input::get('zdD'));
        $this->fileCancel($id);
        break;
      case 'add':  // add a document
        $this->documentEdit();
        break;
      case 'addx':  // add a document - execute
        $this->documentEditExec();
        break;
      case 'edit':  // edit a document
        $id = intval(\Input::get('zdD'));
        $this->documentEdit($id);
        break;
      case 'editx':  // edit a document - execute
        $id = intval(\Input::get('zdD'));
        $this->documentEditExec($id);
        break;
      case 'delete':  // delete a document
        $id = intval(\Input::get('zdD'));
        $this->documentDelete($id);
        break;
      case 'deletex':  // delete a document - execute
        $id = intval(\Input::get('zdD'));
        $this->documentDeleteExec($id);
        break;
      case 'publish':  // publish a document
        $id = intval(\Input::get('zdD'));
        $this->documentPublish($id);
        break;
      case 'unpublish':  // unpublish a document
        $id = intval(\Input::get('zdD'));
        $this->documentPublish($id, false);
        break;
      case 'show':  // show a document
        $id = intval(\Input::get('zdD'));
        $this->documentShow($id);
        break;
      case 'download':  // download a file
        $id = \Input::get('zdF');
        $this->fileDownload($id);
        break;
      default:  // list documents
        $id = intval(\Input::get('zdP'));
        $this->documentList($id);
        break;
    }
	}

	/**
	 * Upload a file
	 *
	 * @param int $id  ID of the document owner (0=a new one)
	 */
	protected function fileUpload($id) {
    if ($id > 0) {
      // check if document exists
      $doc = \ZadDocmanModel::findByPk($id);
      if ($doc === null) {
        // error, invalid id
        $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_id']);
      }
      // check owner
      if (!$this->isAdmin && $doc->sentBy != $this->userId) {
        // error, user can't edit document
        $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth']);
      }
    }
    if (empty($_FILES)) {
      // return files already existent
      $result  = array();
      // get file parameter name
      $pname = \Input::post('pname');
      if (isset($_SESSION['zad_docman'][$pname])) {
        // get data from session
        $result = $_SESSION['zad_docman'][$pname];
      } elseif ($id > 0 && $pname == 'document') {
        // get document file info
        $file = \FilesModel::findByUuid($doc->document);
        if ($file === null) {
          // upload error
          $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_file']);
        }
        $obj = array(
          'type' => 'existent',
          'uuid' => \String::binToUuid($doc->document),
          'name' => $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document'].'.'.$file->extension,
          'ext' => $file->extension,
          'size' => filesize(TL_ROOT . '/' . $file->path));
        $result[] = $obj;
        // save files in session
        $_SESSION['zad_docman'][$pname][] = $obj;
      } elseif ($id > 0 && $pname == 'attach') {
        // get document file info
        $attach = unserialize($doc->attach);
        $i = 1;
        foreach ($attach as $att) {
          $file = \FilesModel::findByUuid($att);
          if ($file === null) {
            // upload error
            $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_file']);
          }
          $obj = array(
            'type' => 'existent',
            'uuid' => \String::binToUuid($att),
            'name' => sprintf($GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attachnum'].'.'.$file->extension, $i),
            'ext' => $file->extension,
            'size' => filesize(TL_ROOT . '/' . $file->path));
          $i++;
          $result[] = $obj;
          // save files in session
          $_SESSION['zad_docman'][$pname][] = $obj;
        }
      }
      // send back file info and exit
      header('Content-type: application/json');
      echo(json_encode($result));
      die();
    } elseif (isset($_FILES['document'])) {
      // upload document
      $this->saveUploadedFiles($id, 'document', $_FILES['document']);
    } elseif (isset($_FILES['attach'])) {
      // upload attachments
      $this->saveUploadedFiles($id, 'attach', $_FILES['attach']);
    } else {
      // upload error
      $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_file']);
    }
  }

	/**
	 * Cancel an uploaded a file
	 *
	 * @param int $id  ID of the document to edit (0=a new one)
	 */
	protected function fileCancel($id) {
    if ($id > 0) {
      // check if document exists
      $doc = \ZadDocmanModel::findByPk($id);
      if ($doc === null) {
        // error, invalid id
        $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_id']);
      }
      // check owner
      if (!$this->isAdmin && $doc->sentBy != $this->userId) {
        // error, user can't edit document
        $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth']);
      }
    }
    // delete uploaded files
    $pname = \Input::post('pname');
    $file = \Input::post('file');
    if ($file) {
      // remove files from session
      $this->import('Files');
      foreach ($_SESSION['zad_docman'][$pname] as $kfl=>$fl) {
        if ($fl['uuid'] == $file['uuid']) {
          // found: remove
          if ($file['type'] == 'uploaded') {
            // delete uploaded file
            $this->Files->delete('system/tmp/' . $file['uuid']);
            unset($_SESSION['zad_docman'][$pname][$kfl]);
          } elseif ($file['type'] == 'existent') {
            // remove later
            $_SESSION['zad_docman'][$pname][$kfl]['type'] = 'removed';
          }
          break;
        }
      }
    }
  }

	/**
	 * Show the form for creating/editing a document
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
    // get field data
    $fields = $this->getFields();
    if (empty($fields)) {
      // error, no fields
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofields'], $base_url);
      return;
    }
    if ($id > 0) {
      // edit a document
      $param['zdA'] = 'editx';
      $param['zdD'] = $id;
      $this->Template->href_action = $this->createUrl($param, $base_url);
      $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentedit'];
      // get document
      $doc = \ZadDocmanModel::findByPk($id);
      if ($doc === null) {
        // error, invalid id
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
        return;
      }
      // check owner
      if (!$this->isAdmin && $doc->sentBy != $this->userId) {
        // error, user can't edit document
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
        return;
      }
      // check if published
      if (!$this->isAdmin) {
        $waiting_time = intval(substr($this->docman->waitingTime, 3)) * 3600;
        if ($doc->published && ($doc->publishedTimestamp + $waiting_time) < time()) {
          // error, user can't change a published document
          $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
          return;
        }
      }
      // data
      $data = array();
      $dataobj = \ZadDocmanDataModel::findByPid($id);
      if ($dataobj !== null) {
        // get data
        while ($dataobj->next()) {
          $data[$dataobj->field] = $dataobj->value;
        }
      }
      foreach ($fields as $kfld=>$fld) {
        if (!isset($data[$kfld])) {
          // reset value
          $data[$kfld] = '';
        } else {
          // value
          $data[$kfld] = $this->formatFieldForm($kfld, $data[$kfld], $fld);
        }
      }
    } else {
      // add a new document
      $param['zdA'] = 'addx';
      $this->Template->href_action = $this->createUrl($param, $base_url);
      $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentadd'];
      // data
      $data = array();
      foreach ($fields as $kfld=>$fld) {
        // set defualt
        $data[$kfld] = $this->formatFieldForm($kfld, null, $fld);
      }
    }
    // set dropzone css and javascript
    $GLOBALS['TL_CSS'][] = 'system/modules/zad_docman/vendor/dropzone-3.10.2/css/dropzone.min.css';
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/zad_docman/vendor/dropzone-3.10.2/js/dropzone.min.js';
    // set dropzone urls
    $param = array();
    $param['zdA'] = 'upload';
    $param['zdD'] = $id;
    $this->Template->href_dropzone = $this->createUrl($param, $base_url, false);
    $param['zdA'] = 'cancel';
    $this->Template->href_dropzone_cancel = $this->createUrl($param, $base_url, false);
    // set other template vars
    $this->Template->isAdmin = $this->isAdmin;
    $this->Template->error = array();
    $this->Template->fields = $fields;
    $this->Template->data = $data;
    $this->Template->attach = $this->docman->enableAttach;
    $this->Template->editing = $this->docman->editing;
    $this->Template->maxFilesize = intVal(\Config::get('maxFileSize') / (1024 * 1024));
    $this->Template->acceptedFiles = implode(',', array_map(function($a) { return '.'.$a; }, trimsplit(',', strtolower($this->docman->fileTypes))));
    $this->Template->lbl_document = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document'];
    $this->Template->lbl_attach = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach'];
    $this->Template->lbl_mandatory = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory'];
    $this->Template->lbl_dropzone = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_dropzone'];
    $this->Template->lbl_listother = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_listother'];
    $this->Template->but_save = $GLOBALS['TL_LANG']['tl_zad_docman']['but_save'];
    $this->Template->but_cancel = $GLOBALS['TL_LANG']['tl_zad_docman']['but_cancel'];
    $this->Template->but_removefile = $GLOBALS['TL_LANG']['tl_zad_docman']['but_removefile'];
    $this->Template->but_cancelupload = $GLOBALS['TL_LANG']['tl_zad_docman']['but_cancelupload'];
    $this->Template->wrn_cancelupload = $GLOBALS['TL_LANG']['tl_zad_docman']['wrn_cancelupload'];
    $this->Template->err_filetype = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filetype'];
    $this->Template->err_filesize = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filesize'];
    $this->Template->err_filecount = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filecount'];
    $this->Template->err_dropzone = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dropzone'];
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
    // get field data
    $fields = $this->getFields();
    if (empty($fields)) {
      // error, no fields
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofields'], $base_url);
      return;
    }
    if ($id > 0) {
      // get document
      $doc = \ZadDocmanModel::findByPk($id);
      if ($doc === null) {
        // error, invalid id
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
        return;
      }
      // check owner
      if (!$this->isAdmin && $doc->sentBy != $this->userId) {
        // error, user can't edit document
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
        return;
      }
      // check if published
      if (!$this->isAdmin) {
        $waiting_time = intval(substr($this->docman->waitingTime, 3)) * 3600;
        if ($doc->published && ($doc->publishedTimestamp + $waiting_time) < time()) {
          // error, user can't change a published document
          $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
          return;
        }
      }
    }
    if (strlen(\Input::post('_save')) == 0) {
      // cancel button pressed: remove uploaded files and exit
      $this->import('Files');
      if (isset($_SESSION['zad_docman']['document'])) {
        foreach ($_SESSION['zad_docman']['document'] as $file) {
          if ($file['type'] == 'uploaded') {
            // delete uploaded file
            $this->Files->delete('system/tmp/' . $file['uuid']);
          }
        }
      }
      if (isset($_SESSION['zad_docman']['attach'])) {
        foreach ($_SESSION['zad_docman']['attach'] as $file) {
          if ($file['type'] == 'uploaded') {
            // delete uploaded file
            $this->Files->delete('system/tmp/' . $file['uuid']);
          }
        }
      }
      $this->redirect($base_url);
    }
    // validate data
    $data = array();
    $sentBy = null;
    foreach ($fields as $kfld=>$fld) {
      $data[$kfld] = \Input::post('field_'.$kfld);
      if (!is_array($data[$kfld])) {
        $data[$kfld] = trim($data[$kfld]);
      }
      switch ($fld['type']) {
        case 't_number':
        case 't_sequence':
          // type number
          $data[$kfld] = intval($data[$kfld]);
          if ($fld['mandatory'] && $data[$kfld] == 0) {
            // no data
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
          }
          break;
        case 't_date':
          // type date
          if ($fld['mandatory'] && empty($data[$kfld])) {
            // no data
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
          } elseif (!empty($data[$kfld])) {
            // check format
            try {
              $date = new \Date($data[$kfld], $GLOBALS['TL_CONFIG']['dateFormat']);
            } catch (\Exception $e) {
              // invalid format
              $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dateformat'];
            }
            if ($date && $date->date != $data[$kfld]) {
              // invalid format
              $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dateformat'];
            } else {
              // ok, save it as timestamp
              $data[$kfld] = $date->timestamp;
            }
          }
          break;
        case 't_time':
          // type time
          if ($fld['mandatory'] && empty($data[$kfld])) {
            // no data
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
          } elseif (!empty($data[$kfld])) {
            // check format
            try {
              $date = new \Date($data[$kfld], $GLOBALS['TL_CONFIG']['timeFormat']);
            } catch (\Exception $e) {
              // invalid format
              $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_timeformat'];
            }
            if ($date && $date->time != $data[$kfld]) {
              // invalid format
              $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_timeformat'];
            } else {
              // ok, save it as timestamp
              $data[$kfld] = $date->timestamp;
            }
          }
          break;
        case 't_datetime':
          // type datetime
          if ($fld['mandatory'] && empty($data[$kfld])) {
            // no data
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
          } elseif (!empty($data[$kfld])) {
            // check format
            try {
              $date = new \Date($data[$kfld], $GLOBALS['TL_CONFIG']['datimFormat']);
            } catch (\Exception $e) {
              // invalid format
              $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_datimformat'];
            }
            if ($date && $date->datim != $data[$kfld]) {
              // invalid format
              $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_datimformat'];
            } else {
              // ok, save it as timestamp
              $data[$kfld] = $date->timestamp;
            }
          }
          break;
        case 't_choice':
          // type single choice
          if ($fld['mandatory'] && empty($data[$kfld])) {
            // no data
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
          } elseif ($fld['listOther'] && $data[$kfld] == '__OTHER__' && \Input::post('field_'.$kfld.'__OTHER__') == null) {
            // other option is void
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_listother'];
          }
          break;
        case 't_mchoice':
          // type multi choice
          if ($fld['mandatory'] && (empty($data[$kfld]) || count($data[$kfld]) == 0)) {
            // no data
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
          } elseif ($fld['listOther'] && !empty($data[$kfld]) && in_array('__OTHER__', $data[$kfld]) && \Input::post('field_'.$kfld.'__OTHER__') == null) {
            // other option is void
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_listother'];
          }
          break;
        case 't_auto':
          // type auto
          if ($fld['autofield'] == 'af_timestamp') {
            // timestamp automatic field
            if ($this->isAdmin) {
              // admin can change timestamp
              if (empty($data[$kfld])) {
                // no data
                $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_autovoid'];
              } else {
                // check format
                try {
                  $date = new \Date($data[$kfld], $GLOBALS['TL_CONFIG']['datimFormat']);
                } catch (\Exception $e) {
                  // invalid format
                  $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_datimformat'];
                }
                if ($date && $date->datim != $data[$kfld]) {
                  // invalid format
                  $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_datimformat'];
                } else {
                  // ok, save it as timestamp
                  $data[$kfld] = $date->timestamp;
                }
              }
            } else {
              // no admin, set automatic value
              $data[$kfld] = time();
            }
          } elseif ($fld['autofield'] == 'af_user') {
            // user field
            if ($this->isAdmin) {
              // admin can change user
              if (empty($data[$kfld])) {
                // no data
                $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_autovoid'];
              } else {
                // check user
                $cond = array("concat(tl_member.lastname,' ',tl_member.firstname)='".$data[$kfld]."'");
                $options = array('limit' => 1);
                $user = \MemberModel::findBy($cond, null, $options);
                if ($user === null) {
                  // invalid format
                  $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_userformat'];
                } else {
                  // ok, save it as userId
                  $data[$kfld] = $user->id;
                  $sentBy = $user->id;
                }
              }
            } else {
              // no admin, set automatic value
              $data[$kfld] = $this->userId;
              $sentBy = $this->userId;
            }
          }
        default:
          // type text
          if ($fld['mandatory'] && empty($data[$kfld])) {
            // no data
            $error['field_'.$kfld] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
          }
          break;
      }
    }
    // check file document
    if (!isset($_SESSION['zad_docman']['document'])) {
      // no document
      $error['document'] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
    } else {
      $cnt = 0;
      foreach ($_SESSION['zad_docman']['document'] as $fl) {
        if ($fl['type'] != 'removed') {
          $cnt++;
          break;
        }
      }
      if ($cnt == 0) {
        // no document
        $error['document'] = $GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'];
      }
    }
    // save or show errors
    if (empty($error)) {
      // save document
      if ($id == 0) {
        // new document
  		  $doc = new \ZadDocmanModel();
      }
      $doc->pid = $this->docman->id;
      $doc->tstamp = time();
      $doc->save(); // create new id
      $uuid_list = $this->storeFiles($_SESSION['zad_docman']['document'], $doc->id);
      if ($uuid_list == NULL) {
        // error, can't store file
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_store'], $base_url);
        return;
      }
      $doc->document = $uuid_list[0];
      $uuid_list = array();
      if ($this->docman->enableAttach && isset($_SESSION['zad_docman']['attach'])) {
        $cnt = 0;
        foreach ($_SESSION['zad_docman']['attach'] as $fl) {
          if ($fl['type'] != 'removed') {
            $cnt++;
            break;
          }
        }
        $uuid_list = $this->storeFiles($_SESSION['zad_docman']['attach'], $doc->id);
        if ($uuid_list == NULL && $cnt > 0) {
          // error, can't store file
          $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_store'], $base_url);
          return;
        }
      }
      $doc->attach = serialize($uuid_list);
      $doc->published = '';
      $doc->publishedTimestamp = 0;
      $doc->sentBy = ($sentBy != NULL) ? $sentBy : $this->userId;
      $doc->save();
      // save data
      foreach ($fields as $kfld=>$fld) {
        if ($id > 0) {
          // existent data
          $cond[0] = "tl_zad_docman_data.pid=$id";
          $cond[1] = "tl_zad_docman_data.field='$kfld'";
          $options = array('limit' => 1);
          $info = \ZadDocmanDataModel::findBy($cond, null, $options);
          if ($info === null) {
            // on error, create a new one
            $info = new \ZadDocmanDataModel();
          }
        } else {
          // new data
  		    $info = new \ZadDocmanDataModel();
        }
        $info->pid = $doc->id;
        $info->tstamp = $doc->tstamp;
        $info->field = $kfld;
        if ($fld['type'] == 't_choice' && $fld['listOther'] && $data[$kfld] == '__OTHER__') {
          // other option selected in list
          $info->value = '__OTHER__:' . \Input::post('field_'.$kfld.'__OTHER__');
        } elseif ($fld['type'] == 't_mchoice') {
          // multi choice list
          if ($fld['listOther'] && in_array('__OTHER__', $data[$kfld])) {
            // other option selected in list
            if (substr(end($data[$kfld]), 0, 10) == '__OTHER__:') {
              unset($data[$kfld][key($data[$kfld])]);
            }
            $data[$kfld][] = '__OTHER__:' . \Input::post('field_'.$kfld.'__OTHER__');
          }
          $info->value = serialize($data[$kfld]);
        } else {
          // save data
          $info->value = $data[$kfld];
        }
        $info->save();
      }
      // go to document list
      $this->redirect($base_url);
    } else {
      // show errors
      $this->Template = new \FrontendTemplate('zaddm_edit');
      if ($id > 0) {
         // edit a document
        $param['zdA'] = 'editx';
        $param['zdD'] = $id;
        $this->Template->href_action = $this->createUrl($param, $base_url);
        $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentedit'];
      } else {
         // add a document
        $param['zdA'] = 'addx';
        $this->Template->href_action = $this->createUrl($param, $base_url);
        $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentadd'];
      }
      // change format to show data
      foreach ($fields as $kfld=>$fld) {
        if (!isset($error['field_'.$kfld])) {
          if ($fld['type'] == 't_choice' && $fld['listOther'] && $data[$kfld] == '__OTHER__') {
            // other option selected in list
            $data[$kfld] = '__OTHER__:' . \Input::post('field_'.$kfld.'__OTHER__');
          } elseif ($fld['type'] == 't_mchoice' && $fld['listOther'] && in_array('__OTHER__', $data[$kfld])) {
            // other option selected in list
            if (substr(end($data[$kfld]), 0, 10) == '__OTHER__:') {
              unset($data[$kfld][key($data[$kfld])]);
            }
            $data[$kfld][] = '__OTHER__:' . \Input::post('field_'.$kfld.'__OTHER__');
          } elseif ($fld['type'] == 't_mchoice') {
            // change data
            $data[$kfld] = $this->formatFieldForm($kfld, serialize($data[$kfld]), $fld);
          } else {
            // change data
            $data[$kfld] = $this->formatFieldForm($kfld, $data[$kfld], $fld);
          }
        }
      }
      // set dropzone css and javascript
      $GLOBALS['TL_CSS'][] = 'system/modules/zad_docman/vendor/dropzone-3.10.2/css/dropzone.min.css';
      $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/zad_docman/vendor/dropzone-3.10.2/js/dropzone.min.js';
      // set dropzone urls
      $param = array();
      $param['zdA'] = 'upload';
      $param['zdD'] = $id;
      $this->Template->href_dropzone = $this->createUrl($param, $base_url, false);
      $param['zdA'] = 'cancel';
      $this->Template->href_dropzone_cancel = $this->createUrl($param, $base_url, false);
      // set other template vars
      $this->Template->isAdmin = $this->isAdmin;
      $this->Template->error = $error;
      $this->Template->fields = $fields;
      $this->Template->data = $data;
      $this->Template->attach = $this->docman->enableAttach;
      $this->Template->editing = $this->docman->editing;
      $this->Template->maxFilesize = intVal(\Config::get('maxFileSize') / (1024 * 1024));
      $this->Template->acceptedFiles = implode(',', array_map(function($a) { return '.'.$a; }, trimsplit(',', strtolower($this->docman->fileTypes))));
      $this->Template->lbl_document = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document'];
      $this->Template->lbl_attach = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach'];
      $this->Template->lbl_mandatory = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory'];
      $this->Template->lbl_dropzone = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_dropzone'];
      $this->Template->lbl_listother = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_listother'];
      $this->Template->but_save = $GLOBALS['TL_LANG']['tl_zad_docman']['but_save'];
      $this->Template->but_cancel = $GLOBALS['TL_LANG']['tl_zad_docman']['but_cancel'];
      $this->Template->but_removefile = $GLOBALS['TL_LANG']['tl_zad_docman']['but_removefile'];
      $this->Template->but_cancelupload = $GLOBALS['TL_LANG']['tl_zad_docman']['but_cancelupload'];
      $this->Template->wrn_cancelupload = $GLOBALS['TL_LANG']['tl_zad_docman']['wrn_cancelupload'];
      $this->Template->err_filetype = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filetype'];
      $this->Template->err_filesize = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filesize'];
      $this->Template->err_filecount = $GLOBALS['TL_LANG']['tl_zad_docman']['err_filecount'];
      $this->Template->err_dropzone = $GLOBALS['TL_LANG']['tl_zad_docman']['err_dropzone'];
      $this->Template->err_listother = $GLOBALS['TL_LANG']['tl_zad_docman']['err_listother'];
    }
  }

	/**
	 * Show the confirm for deleting a document
	 *
	 * @param int $id  ID of the document to delete
	 */
	protected function documentDelete($id) {
    // set base url
    $base_url = $this->createBaseUrl();
    // get field data
    $fields = $this->getFields();
    if (empty($fields)) {
      // error, no fields
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofields'], $base_url);
      return;
    }
    // get document
    $doc = \ZadDocmanModel::findByPk($id);
    if ($doc === null) {
      // error, invalid id
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
      return;
    }
    // check owner
    if (!$this->isAdmin && $doc->sentBy != $this->userId) {
      // error, user can't delete document
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
      return;
    }
    // check if published
    if (!$this->isAdmin) {
      $waiting_time = intval(substr($this->docman->waitingTime, 3)) * 3600;
      if ($doc->published && ($doc->publishedTimestamp + $waiting_time) < time()) {
        // error, user can't change a published document
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
        return;
      }
    }
    // get document data
    $data = \ZadDocmanDataModel::findByPid($id);
    if ($data === null) {
      // error, invalid id
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
      return;
    }
    // create a data array structure
    $data_array = array();
    while ($data->next()) {
      $data_array[$data->field] = $data->value;
    }
    // set data values
    $values = array();
    foreach ($fields as $kfld=>$fld) {
      if ($fld['show']) {
        $values[] = array(
          'label' => $fld['label'],
          'value' => $this->formatFieldText($data_array[$kfld], $fld));
      }
    }
    // set template
    $this->Template = new \FrontendTemplate('zaddm_confirm');
    // set template vars
    $param['zdA'] = 'deletex';
    $param['zdD'] = $id;
    $this->Template->href_action = $this->createUrl($param, $base_url);
    $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentdelete'];
    $this->Template->values = $values;
    $this->Template->but_confirm = $GLOBALS['TL_LANG']['tl_zad_docman']['but_confirm'];
    $this->Template->but_cancel = $GLOBALS['TL_LANG']['tl_zad_docman']['but_cancel'];
  }

  /**
	 * Delete a document
	 *
	 * @param int $id  ID of the document to delete
	 */
	protected function documentDeleteExec($id) {
    // set base url
    $base_url = $this->createBaseUrl();
    // get document
    $doc = \ZadDocmanModel::findByPk($id);
    if ($doc === null) {
      // error, invalid id
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
      return;
    }
    // check owner
    if (!$this->isAdmin && $doc->sentBy != $this->userId) {
      // error, user can't delete document
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
      return;
    }
    // check if published
    if (!$this->isAdmin) {
      $waiting_time = intval(substr($this->docman->waitingTime, 3)) * 3600;
      if ($doc->published && ($doc->publishedTimestamp + $waiting_time) < time()) {
        // error, user can't change a published document
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
        return;
      }
    }
    if (strlen(\Input::post('_confirm')) == 0) {
      // cancel button pressed
      $this->redirect($base_url);
    }
    // delete files
		$this->import('Files');
    $file = \FilesModel::findByUuid($doc->document);
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
    // delete data
    $data = \ZadDocmanDataModel::findByPid($id);
    if ($data !== null) {
      while ($data->next()) {
        // delete data
        $data->delete();
      }
    }
    // delete document
    $doc->delete();
    // go to document list
    $this->redirect($base_url);
  }

  /**
	 * Publish/Unpublish a document
	 *
	 * @param int $id  ID of the document to delete
	 * @param bool $to_publish  If true publish the document, otherwise unpublish it
	 */
	protected function documentPublish($id, $to_publish=true) {
    // set base url
    $base_url = $this->createBaseUrl();
    // get document
    $doc = \ZadDocmanModel::findByPk($id);
    if ($doc === null) {
      // error, invalid id
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
      return;
    }
    // check owner
    if (!$this->isAdmin && $doc->sentBy != $this->userId) {
      // error, user can't publish/unpublish document
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
      return;
    }
    if ($to_publish && !$doc->published) {
      // publish the document
      if ($this->docman->enablePdf) {
  		  // convert to PDF
        $doc->document = $this->convertToPdf($doc->document);
        if ($doc->document === null) {
          // error, no file
          $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofile'], $base_url);
          return;
        }
        if ($this->docman->enableAttach) {
          $files = unserialize($doc->attach);
          foreach ($files as $kfile=>$file) {
            $file_pdf = $this->convertToPdf($file);
            if ($file_pdf === null) {
              // error, no file
              $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofile'], $base_url);
              return;
            }
            $files[$kfile] = $file_pdf;
          }
          $doc->attach = serialize($files);
        }
      }
      // set published fields
      $doc->published = '1';
      $doc->publishedTimestamp = time();
      $doc->save();
			// add a log entry for published file
      $this->log('ZAD DocMan - Published document with ID '.$id, __METHOD__, 'ZAD_DOCMAN');
    } elseif (!$to_publish && $doc->published) {
      // unpublish the document
      $waiting_time = intval(substr($this->docman->waitingTime, 3)) * 3600;
      // check document status
      if (($doc->publishedTimestamp + $waiting_time) < time() && !$this->isAdmin) {
        // error, user can't unpublish document
        $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
        return;
      }
      // set published fields
      $doc->published = '';
      $doc->publishedTimestamp = 0;
      $doc->save();
			// add a log entry for published file
      $this->log('ZAD DocMan - Unpublished document with ID '.$id, __METHOD__, 'ZAD_DOCMAN');
    }
    // go to document list
    $this->redirect($base_url);
  }

	/**
	 * Show the preview of a document
	 *
	 * @param int $id  ID of the document to show
	 */
	protected function documentShow($id) {
    // set base url
    $base_url = $this->createBaseUrl();
    // get field data
    $fields = $this->getFields();
    if (empty($fields)) {
      // error, no fields
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofields'], $base_url);
      return;
    }
    // get document
    $doc = \ZadDocmanModel::findByPk($id);
    if ($doc === null) {
      // error, invalid id
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
      return;
    }
    // check owner
    if (!$this->isAdmin && !$this->docman->enableOthers && $doc->sentBy != $this->userId) {
      // error, user can't show document
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'], $base_url);
      return;
    }
    // get document data
    $data = \ZadDocmanDataModel::findByPid($id);
    if ($data === null) {
      // error, invalid id
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_id'], $base_url);
      return;
    }
    // create a data array structure
    $data_array = array();
    while ($data->next()) {
      $data_array[$data->field] = $data->value;
    }
    // set data values
    $values = array();
    foreach ($fields as $kfld=>$fld) {
      $values[] = array(
        'label' => $fld['label'],
        'value' => $this->formatFieldText($data_array[$kfld], $fld));
    }
    // set status
    $waiting_time = intval(substr($this->docman->waitingTime, 3)) * 3600;
    if ($doc->published && ($doc->publishedTimestamp + $waiting_time) >= time()) {
      $date = new \Date($doc->publishedTimestamp);
      $status = sprintf($GLOBALS['TL_LANG']['tl_zad_docman']['lbl_waitingtm'], $date->datim);
    } elseif ($doc->published) {
      // published document
      $date = new \Date($doc->publishedTimestamp);
      $status = sprintf($GLOBALS['TL_LANG']['tl_zad_docman']['lbl_publishedtm'], $date->datim);
    } else {
      // draft document
      $date = new \Date($doc->tstamp);
      $status = sprintf($GLOBALS['TL_LANG']['tl_zad_docman']['lbl_drafttm'], $date->datim);
    }
    $values[] = array(
      'label' => $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_status'],
      'value' => $status);
    // download document href
    $param['zdA'] = 'download';
    $param['zdF'] = \String::binToUuid($doc->document);
    $href_document = $this->createUrl($param, $base_url);
    $attach = array();
    if ($this->docman->enableAttach) {
      // attach files
      $attaches = unserialize($doc->attach);
      foreach ($attaches as $katt=>$att) {
        $param['zdA'] = 'download';
        $param['zdF'] = \String::binToUuid($att);
        $href = $this->createUrl($param, $base_url);
        $attach[] = array(
          'href' => $href,
          'label' => sprintf($GLOBALS['TL_LANG']['tl_zad_docman']['lbl_downloadattach'], $katt+1)
          );
      }
    }
    // set template
    $this->Template = new \FrontendTemplate('zaddm_show');
    // set template vars
    $this->Template->header = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentshow'];
    $this->Template->values = $values;
  	$this->Template->referer = $base_url;
		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
		$this->Template->href_document = $href_document;
  	$this->Template->attach = $attach;
    $this->Template->lbl_document = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document'];
    $this->Template->lbl_attach = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach'];
    $this->Template->lbl_downloaddocument = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_downloaddocument'];
  }

	/**
	 * Show document list
	 *
	 * @param int $page  Page number
	 */
	protected function documentList($page=0) {
    // set template
    $this->Template = new \FrontendTemplate('zaddm_list');
    // set base url
    $base_url = $this->createBaseUrl();
    // get field data
    $fields = $this->getFields();
    if (empty($fields)) {
      // error, no fields
      $this->errorMessage($GLOBALS['TL_LANG']['tl_zad_docman']['err_nofields'], $base_url);
      return;
    }
    // pagination
    $limit = null;
    $offset = null;
    $show_others = ($this->isAdmin || $this->docman->enableOthers) ? null : $this->userId;
    $total = \ZadDocmanModel::countDocuments($this->docman->id, $show_others);
		if ($this->perPage > 0 && $total > $this->perPage) {
      // adjust page number
      if ($page < 1) {
        // first page
        $page = 1;
      } elseif ($page > ceil($total / $this->perPage)) {
        // last page
        $page = ceil($total / $this->perPage);
      }
			// set limit and offset
			$limit = $this->perPage;
			$offset = ($page - 1) * $this->perPage;
			if ($offset + $limit > $total) {
				$limit = $total - $offset;
			}
			// add the pagination menu
			$pagination = new \Pagination($total, $this->perPage, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], 'zdP');
			$this->Template->pagination = $pagination->generate("\n  ");
    }
    // get docs
    $data = array();
    $docs = \ZadDocmanModel::findDocuments($this->docman->id, $show_others, $fields, $offset, $limit);
    if ($docs !== null) {
      $index = 0;
      $waiting_time = intval(substr($this->docman->waitingTime, 3)) * 3600;
      while ($docs->next()) {
        $param = array();
        $data[$index]['id'] = $docs->id;
        // set status
        if ($docs->published && ($docs->publishedTimestamp + $waiting_time) >= time()) {
          $data[$index]['status'] = 'st_waiting';
        } elseif ($docs->published) {
          // published document
          $data[$index]['status'] = 'st_published';
        } else {
          // draft document
          $data[$index]['status'] = 'st_draft';
        }
        // field values
        $values = array();
        foreach ($fields as $kfld=>$fld) {
          if ($fld['show']) {
            $fname = 'field_'.$kfld;
            $values[$kfld] = $this->formatFieldText($docs->$fname, $fld);
          }
        }
        $data[$index]['values'] = $values;
        // buttons
        $data[$index]['href_edit'] = null;
        $data[$index]['href_delete'] = null;
        $data[$index]['href_publish'] = null;
        $data[$index]['href_unpublish'] = null;
        if ($this->isAdmin || ($docs->sentBy == $this->userId && $data[$index]['status'] != 'st_published')) {
          // user can edit/delete document
          $param = array();
          $param['zdD'] = $docs->id;
          $param['zdA'] = 'edit';
          $data[$index]['href_edit'] = $this->createUrl($param, $base_url);
          $param['zdA'] = 'delete';
          $data[$index]['href_delete'] = $this->createUrl($param, $base_url);
        }
        if ($data[$index]['status'] == 'st_draft' && ($this->isAdmin || $docs->sentBy == $this->userId)) {
          // user can publish document
          $param['zdA'] = 'publish';
          $data[$index]['href_publish'] = $this->createUrl($param, $base_url);
        } elseif ($data[$index]['status'] == 'st_waiting' && ($this->isAdmin || $docs->sentBy == $this->userId)) {
          $param['zdA'] = 'unpublish';
          $data[$index]['href_unpublish'] = $this->createUrl($param, $base_url);
        } elseif ($data[$index]['status'] == 'st_published' && $this->isAdmin) {
          $param['zdA'] = 'unpublish';
          $data[$index]['href_unpublish'] = $this->createUrl($param, $base_url);
        }
        // preview
        $param = array();
        $param['zdD'] = $docs->id;
        $param['zdA'] = 'show';
        $data[$index]['href_show'] = $this->createUrl($param, $base_url);
        $index++;
      }
    }
    // set add url
    $param = array();
    $param['zdA'] = 'add';
    $this->Template->href_add = $this->createUrl($param, $base_url);
    // set other template vars
    $this->Template->fields = $fields;
    $this->Template->docs = $data;
    $this->Template->isAdmin = $this->isAdmin;
    $this->Template->wrn_nodata = $GLOBALS['TL_LANG']['tl_zad_docman']['wrn_nodata'];
    $this->Template->lbl_documentlist = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentlist'];
    $this->Template->lbl_status = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_status'];
    $this->Template->st_published = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_published'];
    $this->Template->st_draft = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_draft'];
    $this->Template->st_waiting = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_waiting'];
    $this->Template->but_add = $GLOBALS['TL_LANG']['tl_zad_docman']['but_add'];
    $this->Template->but_show = $GLOBALS['TL_LANG']['tl_zad_docman']['but_show'];
    $this->Template->but_publish = $GLOBALS['TL_LANG']['tl_zad_docman']['but_publish'];
    $this->Template->but_unpublish = $GLOBALS['TL_LANG']['tl_zad_docman']['but_unpublish'];
    $this->Template->but_edit = $GLOBALS['TL_LANG']['tl_zad_docman']['but_edit'];
    $this->Template->but_delete = $GLOBALS['TL_LANG']['tl_zad_docman']['but_delete'];
	}

	/**
	 * Show an error message and send an HTTP error code to signal an aborted upload
	 *
	 * @param string $message  Message to show
	 */
	private function errorUpload($message) {
    header("HTTP/1.1 500 Internal Server Error");
    header('Content-type: text/plain');
    echo $message;
    die();
  }

	/**
	 * Save uploaded files to temp folder and send back file info
	 *
	 * @param int $id  ID of the document owner (0=a new one)
	 * @param string $pname  Parameter name used in $_FILES array
	 * @param array $files  $_FILES array for this upload
	 */
	private function saveUploadedFiles($id, $pname, $files) {
    // init return value
    $result = array();
    // allowed file types
    $allowed_types = array_intersect(
      trimsplit(',', strtolower($this->docman->fileTypes)),
      trimsplit(',', strtolower(\Config::get('uploadTypes'))));
    // save files
    for ($i = 0; $i < count($files['name']); $i++) {
      $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
      // check
      if (!is_uploaded_file($files['tmp_name'][$i])) {
		    // file was not uploaded
        if ($files['error'][$i] == 1 || $files['error'][$i] == 2) {
          // fatal: file size error
          $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_filesize']);
        } else {
          // fatal: generic upload error
          $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_file']);
        }
      } elseif ($files['size'][$i] > \Config::get('maxFileSize')) {
        // fatal: file size error
        $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_filesize']);
	    } elseif (!in_array($ext, $allowed_types)) {
        // fatal: file type error
        $this->errorUpload($GLOBALS['TL_LANG']['tl_zad_docman']['err_filetype']);
	    } else {
        // ok, file uploaded
        $this->import('Files');
        $name = 'zad_docman-'.$id.'-'.uniqid(rand());
        $path = 'system/tmp/'.$name;
        $this->Files->move_uploaded_file($files['tmp_name'][$i], $path);
	      $this->Files->chmod($path, \Config::get('defaultFileChmod'));
        $obj = array(
          'type' => 'uploaded',
          'uuid' => $name,
          'name' => $files['name'][$i],
          'ext' => $ext,
          'size' => $files['size'][$i]);
        $result[] = $obj;
        // store info in session
        $_SESSION['zad_docman'][$pname][] = $obj;
      }
    }
    // send back file info and exit
    header('Content-type: application/json');
    echo(json_encode($result));
    die();
  }

	/**
	 * Store files previously saved in SESSION
	 *
	 * @param array $files  List of uploaded file
	 * @param array $doc_id  The document owner ID
	 *
	 * @return array|NULL  The UUID list of files stored in the database, or NULL on error
	 */
	private function storeFiles($files, $doc_id) {
		$this->import('Files');
    $uuid_list = array();
    foreach ($files as $file) {
      if ($file['type'] == 'uploaded') {
        // add a new uploaded file
				$folder = \FilesModel::findByUuid($this->docman->dir);
				if ($folder === NULL) {
				  // error, upload folder not found
          return NULL;
				}
        $pathname = $folder->path . '/zad_docman-' . $doc_id . '-' . uniqid(rand()) . '.' . strtolower($file['ext']);
				$this->Files->rename('system/tmp/' . $file['uuid'], $pathname);
        $this->Files->chmod($pathname, \Config::get('defaultFileChmod'));
				// generate the DB entries
				$fileobj = \FilesModel::findByPath($pathname);
    		// existing file is being replaced
    	  if ($fileobj !== null) {
          // update file info
    			$fileobj->tstamp = time();
    			$fileobj->path = $pathname;
    			$fileobj->hash = md5_file(TL_ROOT.'/'.$pathname);
    			$fileobj->save();
      		// update the hash of the target folder
      		\Dbafs::updateFolderHashes($folder->path);
    		} else {
          // new file info
    		  $fileobj = \Dbafs::addResource($pathname);
    		}
        // save UUID
        $uuid_list[] = $fileobj->uuid;
  			// add a log entry for new file
  			$this->log('ZAD DocMan - File "'.$file['uuid'].'" has been moved to "'.$folder->path.'"', __METHOD__, TL_FILES);
      } elseif ($file['type'] == 'removed') {
        // remove an existent file
				$fileobj = \FilesModel::findByUuid(\String::uuidToBin($file['uuid']));
				if ($fileobj === NULL) {
				  // error, file to be removed not found
          return NULL;
				}
        // remove file
        $this->Files->delete($fileobj->path);
        \Dbafs::deleteResource($fileobj->path);
  			// add a log entry for removed file
  			$this->log('ZAD DocMan - File "'.$fileobj->path.'" has been removed', __METHOD__, TL_FILES);
      } else {
        // existent file
				$fileobj = \FilesModel::findByUuid(\String::uuidToBin($file['uuid']));
				if ($fileobj === NULL) {
				  // error, existent file not found
          return NULL;
				}
        // save UUID
        $uuid_list[] = $fileobj->uuid;
      }
    }
    // return UUID list of stored files
    return $uuid_list;
  }

	/**
	 * Format field value for html form
	 *
	 * @param string $name  The field name
	 * @param string $value  The field value
	 * @param array $field  The field structure
	 *
	 * @return string  The formatted field
	 */
	private function formatFieldForm($name, $value, $field) {
    $data = $value;
    switch ($field['type']) {
      case 't_text':
        if ($value === null) {
          // default: text
          $data = $field['defaultValue'];
        }
        break;
      case 't_number':
        if ($value === null) {
          // default: number
          $data = $field['defaultValue'];
        }
        break;
      case 't_sequence':
        if ($value === null) {
          // default: next value in sequence
          $data = \ZadDocmanDataModel::nextSequence($this->docman->id, $name);
        }
        break;
      case 't_date':
        if ($value === null && $field['defaultNow']) {
          // default: date of timestamp
          $date = new \Date();
          $data = $date->date;
        } elseif ($value !== null) {
          // format value
          $date = new \Date($value);
          $data = $date->date;
        }
        break;
      case 't_time':
        if ($value === null && $field['defaultNow']) {
          // default: time of timestamp
          $date = new \Date();
          $data = $date->time;
        } elseif ($value !== null) {
          // format value
          $date = new \Date($value);
          $data = $date->time;
        }
        break;
      case 't_datetime':
        if ($value === null && $field['defaultNow']) {
          // default: date/time of timestamp
          $date = new \Date();
          $data = $date->datim;
        } elseif ($value !== null) {
          // format value
          $date = new \Date($value);
          $data = $date->datim;
        }
        break;
      case 't_choice':
        if ($value === null) {
          // default: choice
          $list = unserialize($field['list']);
          foreach ($list as $l) {
            if (isset($l['default']) && $l['default']) {
              $data = $l['value'];
              break;
            }
          }
        }
        break;
      case 't_mchoice':
        if ($value === null) {
          // default: multi choice
          $data_list = array();
          $list = unserialize($field['list']);
          foreach ($list as $l) {
            if (isset($l['default']) && $l['default']) {
              $data_list[] = $l['value'];
            }
          }
          $data = $data_list;
        } else {
          // data list
          $data = unserialize($value);
        }
        break;
      case 't_auto':
        if ($value === null) {
          // default: automatic field
          if ($field['autofield'] == 'af_timestamp') {
            $date = new \Date();
            $data = $date->datim;
          } elseif ($field['autofield'] == 'af_user') {
            $data = $this->User->lastname.' '.$this->User->firstname;
          }
        } else {
          // format value
          if ($this->isAdmin && $field['autofield'] == 'af_timestamp') {
            $date = new \Date($value);
            $data = $date->datim;
          } elseif ($this->isAdmin && $field['autofield'] == 'af_user') {
            $user = \MemberModel::findByPk($value);
            $data = $user->lastname.' '.$user->firstname;
          }
        }
        break;
    }
    // return formatted value
    return $data;
  }

	/**
	 * Convert document to PDF format
	 *
	 * @param string $uuid  The UUID of the file
	 *
	 * @return string  The UUID of the converted file
	 */
	private function convertToPdf($uuid) {
	  // get file
    $file = \FilesModel::findByUuid($uuid);
    if ($file === null) {
      // error, no file
      return null;
    }
    if (strtolower($file->extension) != 'pdf') {
      // convert
      $filename = $file->path;
      $filename_pdf = substr($filename, 0, - strlen($file->extension)) . 'pdf';
      $cmd = 'python "' . TL_ROOT . '/system/modules/zad_docman/vendor/pyodconverter-1.9/main.py" ' .
             '"' . TL_ROOT . '/' . $filename . '" "' . TL_ROOT . '/' . $filename_pdf . '"';
      $res = exec($cmd);
      if (strlen($res) == 0 && file_exists(TL_ROOT . '/' . $filename_pdf)) {
        // PDF file created, remove original file
        unlink(TL_ROOT . '/' . $filename);
        $file->delete();
        // add to database the new file
  	    $fileobj = \Dbafs::addResource($filename_pdf);
        $uuid = $fileobj->uuid;
        $this->log('ZAD DocMan - File "'.$filename.'" converted to PDF', __METHOD__, 'ZAD_DOCMAN');
      } else {
        // error: no PDF conversion
        $this->log('ZAD DocMan - Can\'t convert to PDF the file "'.$filename.'"', __METHOD__, TL_ERROR);
      }
    }
    return $uuid;
  }

}

