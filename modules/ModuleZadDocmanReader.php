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
 *
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class ModuleZadDocmanReader extends \ModuleZadDocman {

	/**
	 * Display a wildcard in the back end
	 *
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
    // get data
    $this->docman = \ZadDocmanArchiveModel::findByPk($this->zad_docman_archive);
    if ($this->docman === null || !$this->docman->active) {
      // no data: exit without any output
      $this->Template = new \FrontendTemplate('zaddm_message');
      $this->Template->active = false;
      return;
    }
    // get action info
    $action = \Input::get('zdA');
    switch ($action) {
      case 'download':  // download a file
        $id = \Input::get('zdF');
        $this->fileDownload($id, false);
        break;
      default:  // list documents
        $group = \Input::get('zdG');
        $page = intval(\Input::get('zdP'));
        $this->documentList($group, $page);
        break;
    }
	}

	/**
	 * Show document list
	 *
	 * @param int $page  Page number
	 */
	protected function documentList($group, $page) {
    // default template settings
    $this->Template = new \FrontendTemplate('zaddm_message');
    $this->Template->active = false;
    // set base url
    $base_url = $this->createBaseUrl();
    // get field data
    $fields = $this->getFields();
    if (empty($fields)) {
      // no data: exit without any output
      return;
    }
    // set timestamp for publishing
    $waiting_time = time() - intval(substr($this->docman->waitingTime, 3)) * 3600;
    // show data
    if ($group == '' && $this->zad_docman_groupby != '__NULL__') {
      // show group list
      if ($fields[$this->zad_docman_groupby]['type'] == 't_choice' || $fields[$this->zad_docman_groupby]['type'] == 't_mchoice') {
        // group for choice/mchoice
        $this->showDocumentGroups(null, $fields[$this->zad_docman_groupby], $base_url);
      } else {
        // group for other field types
        $groups = \ZadDocmanModel::getDocumentGroups($this->docman->id, $fields, $waiting_time,
                  $this->zad_docman_filter, $this->zad_docman_filtervalue, $this->zad_docman_groupby);
        if ($groups !== null) {
          // show groups
          $this->showDocumentGroups($groups, $fields[$this->zad_docman_groupby], $base_url);
        }
      }
    } else {
      // set template
      $this->Template = new \FrontendTemplate('zaddr_reader');
      // pagination
      $limit = null;
      $offset = null;
      $total = \ZadDocmanModel::countDocumentList($this->docman->id, $fields, $waiting_time,
               $this->zad_docman_filter, $this->zad_docman_filtervalue, $this->zad_docman_groupby, $group);
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
      $docs = \ZadDocmanModel::getDocumentList($this->docman->id, $fields, $waiting_time,
              $this->zad_docman_filter, $this->zad_docman_filtervalue, $this->zad_docman_groupby, $group,
              $offset, $limit);
      if ($docs !== null) {
        $index = 0;
        while ($docs->next()) {
          $param = array();
          // document href
          $param['zdA'] = 'download';
          $param['zdF'] = \String::binToUuid($docs->document);
          $data[$index]['href_document'] = $this->createUrl($param, $base_url);
          // attach href
          $attach = array();
          if ($this->docman->enableAttach) {
            // attach files
            $attaches = unserialize($docs->attach);
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
          $data[$index]['attach'] = $attach;
          // field values
          $values = array();
          foreach ($fields as $kfld=>$fld) {
            if ($fld['show']) {
              $fname = 'field_'.$kfld;
              $values[$kfld] = $this->formatFieldText($docs->$fname, $fld);
            }
          }
          $data[$index]['values'] = $values;
          $index++;
        }
      }
    }
    // set other template vars
    $this->Template->fields = $fields;
    $this->Template->docs = $data;
    $this->Template->wrn_nodata = $GLOBALS['TL_LANG']['tl_zad_docman']['wrn_nodata'];
    $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
    $this->Template->referer = $base_url;
    $this->Template->lbl_downloaddocument = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_downloaddocument'];
	}

	/**
	 * Show document groups
	 *
	 * @param \Database\Result $groups  A database result with document groups
	 * @param array $field  Array with field data
	 * @param string $url  The URL of the page
	 */
	private function showDocumentGroups($groups, $field, $url) {
    // set template
    $this->Template = new \FrontendTemplate('zaddr_groups');
    // set groups
    $group_list = array();
    $index = 0;
    $groupby = $this->zad_docman_groupby;
    $param = array();
    $param['zdA'] = 'list';
    $tag = 'p';
    if ($field['type'] == 't_choice' || $field['type'] == 't_mchoice') {
      // choice/mchoice
      foreach (unserialize($field['list']) as $opt) {
        $group_list[$index]['label'] = $opt['label'];
        if (isset($opt['group']) && $opt['group']) {
          // group
          $group_list[$index]['group'] = $opt['value'];
          $tag = 'li';
        } else {
          // option
          $param['zdG'] = $opt['value'];
          $group_list[$index]['href'] = $this->createUrl($param, $url);
        }
        $index++;
      }
      // other option
      if (isset($field['listOther']) && $field['listOther']) {
        // set other option
        $group_list[$index]['label'] = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_listother'];
        $param['zdG'] = '__OTHER__';
        $group_list[$index]['href'] = $this->createUrl($param, $url);
      }
    } else {
      // other types
      while ($groups->next()) {
        if ($field['type'] == 't_auto' && $field['autofield'] == 'af_user') {
          // auto:user
          $user = \MemberModel::findByPk($groups->{'field_'.$groupby});
          if ($user !== null) {
            $group_list[$index]['label'] = $user->lastname.' '.$user->firstname;
            $param['zdG'] = $groups->{'field_'.$groupby};
            $group_list[$index]['href'] = $this->createUrl($param, $url);
          }
        } elseif ($field['type'] == 't_date') {
          // date
          $list = explode('-', $groups->{'field_'.$groupby});
          $group_list[$index]['label'] = $GLOBALS['TL_LANG']['MONTHS'][(int) $list[1] - 1].' '.$list[0];
          $param['zdG'] = $groups->{'field_'.$groupby};
          $group_list[$index]['href'] = $this->createUrl($param, $url);
        } elseif ($field['type'] == 't_datetime' || ($field['type'] == 't_auto' && $field['autofield'] == 'af_timestamp')) {
          // datetime/auto:timestamp
          $list = explode('-', $groups->{'field_'.$groupby});
          $group_list[$index]['label'] = $list[2].' '.$GLOBALS['TL_LANG']['MONTHS'][(int) $list[1] - 1].' '.$list[0];
          $param['zdG'] = $groups->{'field_'.$groupby};
          $group_list[$index]['href'] = $this->createUrl($param, $url);
        } elseif ($field['type'] == 't_time') {
          // time
          $list = explode('-', $groups->{'field_'.$groupby});
          $group_list[$index]['label'] = $groups->{'field_'.$groupby};
          $param['zdG'] = $groups->{'field_'.$groupby};
          $group_list[$index]['href'] = $this->createUrl($param, $url);
        } else {
          // number/sequence/string
          $group_list[$index]['label'] = $groups->{'field_'.$groupby};
          $param['zdG'] = urlencode($groups->{'field_'.$groupby});
          $group_list[$index]['href'] = $this->createUrl($param, $url);
        }
        $index++;
      }
    }
    // set template vars
    $this->Template->tag = $tag;
    $this->Template->title = sprintf($GLOBALS['TL_LANG']['tl_zad_docman']['lbl_grouped'], $field['label']);
    $this->Template->groups = $group_list;
  }

}

