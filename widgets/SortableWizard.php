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
 * Contao namespace required
 */
namespace contao;


/**
 * Class SortableWizard
 *
 * Provide methods to handle info fields for document manager.
 *
 * @copyright Antonello Dessì 2015
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class SortableWizard extends \Widget {

	/**
	 * Submit user input
	 *
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * Add specific attributes
	 *
	 * @param string $key  Identifier for the attribute
	 * @param mixed $value  Value for the attribute
	 */
	public function __set($key, $value) {
		switch ($key) {
			case 'options':
				$this->arrOptions = deserialize($value);
				break;
			default:
				parent::__set($key, $value);
				break;
		}
	}

	/**
	 * Check for a valid option
	 */
	public function validate() {
		$value = $this->getPost($this->strName);
    $visible = false;
    $new_value = array();
    ksort($value);
    foreach ($value as $val) {
      if (isset($val['show']) && $val['show'] == 1) {
        $visible = true;
      }
      $new_value[] = $val;
    }
		if (!$visible && $this->mandatory) {
      // error, mandatory field
			$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
		}
		$this->varValue = $new_value;
	}

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate() {
    // init
		$this->import('Database');
		$buttons = array('up', 'down');
		$command = 'cmd_' . $this->strField;
		$options = array();
		if (!is_array($this->varValue) || count($this->varValue) == 0) {
		  // init values
      $this->varValue = array();
  		foreach ($this->arrOptions as $i=>$option) {
        $this->varValue[$i]['name'] = $option['value'];
        $this->varValue[$i]['show'] = '1';
        $this->varValue[$i]['sort'] = 'NO';
      }
		} else {
      // check values
      foreach ($this->arrOptions as $opt) {
        $found = false;
        foreach ($this->varValue as $val) {
          if ($opt['value'] == $val['name']) {
            // option found
            $found = true;
            break;
          }
        }
        if (!$found) {
          // add option
          $this->varValue[] = array('name'=>$opt['value'], 'show'=>'1','sort'=>'NO');
        }
      }
    }
		// exec commands
		if (\Input::get($command) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord) {
      $cid = \Input::get('cid');
  		switch (\Input::get($command)) {
				case 'up':
					$this->varValue = array_move_up($this->varValue, $cid);
					break;
				case 'down':
					$this->varValue = array_move_down($this->varValue, $cid);
					break;
			}
			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
						         ->execute(serialize($this->varValue), $this->currentRecord);
			$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($command, '/') . '=[^&]*/i', '', \Environment::get('request'))));
		}
		// generate options and add buttons
		foreach ($this->varValue as $i=>$value) {
      // search option value
      $option = '';
      foreach ($this->arrOptions as $opt) {
        if ($opt['value'] == $value['name']) {
          // option found
          $option = $opt;
          break;
        }
      }
      if ($option == '') {
        // error: option not found, go to next one
        continue;
      }
      // buttons
			$html_buttons = '';
			foreach ($buttons as $button) {
				$html_buttons .=
          '<a href="'.$this->addToUrl('&amp;'.$command.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['move_'.$button][1]).'" onclick="Backend.getScrollOffset()">'.
          \Image::getHtml($button.'.gif', $GLOBALS['TL_LANG']['MSC']['move_'.$button][0], 'class="tl_img" style="vertical-align:middle;"').
          '</a> ';
			}
      // option
      $id = 'opt_' . $this->strId . '_' . $i;
			$options[] =
        '<div>'.
        '<input type="hidden" name="'.$this->strName.'['.$i.'][name]" value="'.$value['name'].'">'.
        '<input type="checkbox" name="'.$this->strName.'['.$i.'][show]" '.
          'id="'.$id.'_show" '.
          'value="1"'.static::optionChecked($value['show'], '1').' '.
          'style="vertical-align:middle;" '.
          'onfocus="Backend.getScrollOffset()"> '.
        '<select class="tl_select" name="'.$this->strName.'['.$i.'][sort]" id="'.$id.'_sort" onfocus="Backend.getScrollOffset()">'.
        '<option value="NO"'.static::optionSelected('NO', $value['sort']).'>'.$GLOBALS['TL_LANG']['tl_module']['lbl_zad_docman_nosort'].'</option>'.
        '<option value="ASC"'.static::optionSelected('ASC', $value['sort']).'>'.$GLOBALS['TL_LANG']['tl_module']['lbl_zad_docman_asort'].'</option>'.
        '<option value="DESC"'.static::optionSelected('DESC', $value['sort']).'>'.$GLOBALS['TL_LANG']['tl_module']['lbl_zad_docman_dsort'].'</option>'.
        '</select> '.
        $html_buttons.
        '<label for="'.$id.'_show" style="display:inline;padding:0;vertical-align:middle;">'.$option['label'].'</label>'.
        '</div>';
		}
		if (empty($options)) {
  		// add a "no entries found" message
			$options[] = '<p class="tl_noopt">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}
    // create HTML code
    $html = '<div class="sortable">'.implode('', $options).'</div>';
    // return HTML code
    return $html;
	}

}

