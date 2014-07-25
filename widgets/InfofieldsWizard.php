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
 * Run in a custom namespace, so the class can be replaced
 */
namespace Contao;


/**
 * Class InfofieldsWizard
 *
 * Provide methods to handle info fields for document manager.
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class InfofieldsWizard extends \Widget {

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate() {
    // init
		$this->import('Database');
    \System::loadLanguageFile('tl_zad_docman');
		$arrButtons = array('edit', 'copy', 'delete', 'up', 'down');
		$strCommand = 'cmd_' . $this->strField;
		// make sure there is at least one item
		if (!is_array($this->varValue) || count($this->varValue) < 2) {
      $this->varValue = array();
      $this->varValue[0]['name'] = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_timestamp'];
			$this->varValue[0]['type'] = 'auto';
			$this->varValue[0]['auto_field'] = 'timestamp';
			$this->varValue[0]['auto_editable'] = '';
			$this->varValue[0]['visible'] = '1';
			$this->varValue[0]['sorting'] = 'none';
      $this->varValue[1]['name'] = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_user'];
			$this->varValue[1]['type'] = 'auto';
			$this->varValue[1]['auto_field'] = 'user';
			$this->varValue[1]['auto_editable'] = '';
			$this->varValue[1]['visible'] = '1';
			$this->varValue[1]['sorting'] = 'none';
			$this->varValue[2]['name'] = $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_name'];
			$this->varValue[2]['type'] = 'text';
			$this->varValue[2]['list'] = '';
			$this->varValue[2]['mandatory'] = '';
			$this->varValue[2]['visible'] = '1';
			$this->varValue[2]['sorting'] = 'none';
      // set row to edit
      $row = 2;
		} else {
      // set row to edit
      $row = intval(\Input::get('cid'));
    }
		// exec commands
		if (\Input::get($strCommand) && \Input::get($strCommand) != 'edit' && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord) {
      $cid = \Input::get('cid');
  		switch (\Input::get($strCommand)) {
				case 'copy':
          // copy
          if ($this->varValue[$cid]['type'] != 'auto') {
    				$this->varValue = array_duplicate($this->varValue, $cid);
          }
					break;
				case 'delete':
          // avoids deleting automatic fields
          if ($this->varValue[$cid]['type'] != 'auto') {
            // delete
  					$this->varValue = array_delete($this->varValue, $cid);
	        }
					break;
				case 'up':
          // move up
					$this->varValue = array_move_up($this->varValue, $cid);
					break;
				case 'down':
          // move down
					$this->varValue = array_move_down($this->varValue, $cid);
					break;
			}
			$this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
					 ->execute(serialize($this->varValue), $this->currentRecord);
			$this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));
		}
		// initialize the tab index
		if (!\Cache::has('tabindex')) {
			\Cache::set('tabindex', 1);
		}
    $tabindex = \Cache::get('tabindex');
		// add the table wizard
		$return = '
      <div class="tl_infofieldswizard">
      <table id="ctrl_'.$this->strId.'" class="tl_tablewizard">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>[#]</th>
            <th>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_name'].'</th>
            <th>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory'].'</th>
            <th>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_visible'].'</th>
            <th>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting'].'</th>
            <th>&nbsp;</th>
          </tr>
        </thead>
        <tbody class="sortable" data-tabindex="'.$tabindex.'">';
		// add the input fields
		for ($i=0, $c=count($this->varValue); $i<$c; $i++) {
			$return .= '
        <tr>';
			// add current pointer
			$return .= '
          <td class="current">'.
            ($i==$row ? \Image::getHtml('system/modules/zad_docman/assets/current.gif', $GLOBALS['TL_LANG']['tl_zad_docman']['lbl_current'], 'class="tl_img"') : '').'
          </td>';
			// add position
			$return .= '
          <td class="position">['.($i+1).']</td>';
      // add name field
      $return .= '
          <td class="field1">'.specialchars($this->varValue[$i]['name']).'</td>
          <td class="field2">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory_'.($this->varValue[$i]['mandatory'] ? 'yes' : ($this->varValue[$i]['type'] != 'auto' ? 'no' : 'auto'))].'</td>
          <td class="field3">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_visible_'.($this->varValue[$i]['visible'] ? 'yes' : 'no')].'</td>
          <td class="field4">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting_'.$this->varValue[$i]['sorting']].'</td>';
			// add buttons
			$return .= '
          <td class="buttons">';
			foreach ($arrButtons as $button) {
        if ($this->varValue[$i]['type'] == 'auto' && $button == 'delete') {
          // automatic field: no delete buttons
  				$return .= ' ' . \Image::getHtml('delete_.gif', $GLOBALS['TL_LANG']['tl_zad_docman']['iw_'.$button], 'class="tl_img"').'';
        } elseif ($this->varValue[$i]['type'] == 'auto' && $button == 'copy') {
          // automatic field: no copy buttons
  				$return .= ' ' . \Image::getHtml('copy_.gif', $GLOBALS['TL_LANG']['tl_zad_docman']['iw_'.$button], 'class="tl_img"').'';
  			} else {
					$return .= ' <a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'"' . $class . ' title="'.specialchars($GLOBALS['TL_LANG']['tl_zad_docman']['iw_'.$button]).'" onclick="Backend.getScrollOffset()">'.\Image::getHtml($button.'.gif', $GLOBALS['TL_LANG']['tl_zad_docman']['iw_'.$button], 'class="tl_img"').'</a>';
        }
			}
			$return .= '
          </td>
        </tr>';
		}
		$return .= '
        </tbody>
      </table>';
    // add name field
    $return .= '
      <div class="long">
        <label for="ctrl_'.$this->strId.'_'.$row.'_name">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_name'].':</label><br>
        <input type="text" name="'.$this->strId.'['.$row.'][name]" id="ctrl_'.$this->strId.'_'.$row.'_name" class="tl_text" tabindex="'.$tabindex++.'" value="'.specialchars($this->varValue[$row]['name']).'" maxlength="255" onfocus="Backend.getScrollOffset()">
      </div>';
    // add type field
    if ($this->varValue[$row]['type'] != 'auto') {
      $visibility = ($this->varValue[$row]['type'] == 'choice' || $this->varValue[$row]['type'] == 'mchoice') ? 'visible' : 'hidden';
      $return .= '
        <div class="w50 type">
          <label for="ctrl_'.$this->strId.'_'.$row.'_type">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type'].':</label><br>
          <select name="'.$this->strId.'['.$row.'][type]" id="ctrl_'.$this->strId.'_'.$row.'_type" class="tl_select" tabindex="'.$tabindex++.'" onfocus="Backend.getScrollOffset()" onchange="zad_docman_infofields(this,'.$row.')">
            <option value="text"'.static::optionSelected('text',$this->varValue[$row]['type']).'>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_text'].'</option>
            <option value="number"'.static::optionSelected('number',$this->varValue[$row]['type']).'>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_number'].'</option>
            <option value="date"'.static::optionSelected('date',$this->varValue[$row]['type']).'>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_date'].'</option>
            <option value="choice"'.static::optionSelected('choice',$this->varValue[$row]['type']).'>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_choice'].'</option>
            <option value="mchoice"'.static::optionSelected('mchoice',$this->varValue[$row]['type']).'>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_mchoice'].'</option>
          </select>
        </div>
        <div class="w50 list">
          <label style="visibility:'.$visibility.'" id="lbl_'.$this->strId.'_'.$row.'_list" for="ctrl_'.$this->strId.'_'.$row.'_list">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_list'].':</label><br>
          <input style="visibility:'.$visibility.'"type="text" name="'.$this->strId.'['.$row.'][list]" id="ctrl_'.$this->strId.'_'.$row.'_list" class="tl_text" tabindex="'.$tabindex++.'" value="'.specialchars($this->varValue[$row]['list']).'"'.$this->getAttributes().'>
        </div>';
      $field_name = 'mandatory';
    } else {
      $return .= '
        <input type="hidden" name="'.$this->strId.'['.$row.'][type]" value="'.$this->varValue[$row]['type'].'" >
        <input type="hidden" name="'.$this->strId.'['.$row.'][auto_field]" value="'.$this->varValue[$row]['auto_field'].'" >';
      $field_name = 'auto_editable';
    }
    // add mandatory/editable field
      $return .= '
        <div class="w50 mandatory">
          <label for="ctrl_'.$this->strId.'_'.$row.'_'.$field_name.'">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_'.$field_name].':</label><br>
          <input type="checkbox" name="'.$this->strId.'['.$row.']['.$field_name.']" id="ctrl_'.$this->strId.'_'.$row.'_'.$field_name.'" class="tl_checkbox" tabindex="'.$tabindex++.'" value="1"'.static::optionChecked('1',$this->varValue[$row][$field_name]).' onfocus="Backend.getScrollOffset()">
        </div>';
    // add visible field
    $return .= '
      <div class="w50 visible">
        <label for="ctrl_'.$this->strId.'_'.$row.'_visible">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_visible'].':</label><br>
        <input type="checkbox" name="'.$this->strId.'['.$row.'][visible]" id="ctrl_'.$this->strId.'_'.$row.'_visible" class="tl_checkbox" tabindex="'.$tabindex++.'" value="1"'.static::optionChecked('1',$this->varValue[$row]['visible']).' onfocus="Backend.getScrollOffset()">
      </div>';
    // add sorting field
    $return .= '
      <div class="w50 sorting">
        <label for="ctrl_'.$this->strId.'_'.$row.'_sorting">'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting'].':</label><br>
        <select name="'.$this->strId.'['.$row.'][sorting]" id="ctrl_'.$this->strId.'_'.$row.'_sorting" class="tl_select" tabindex="'.$tabindex++.'" onfocus="Backend.getScrollOffset()">
          <option value="none"'.static::optionSelected('none',$this->varValue[$row]['sorting']).'>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting_none'].'</option>
          <option value="asc"'.static::optionSelected('asc',$this->varValue[$row]['sorting']).'>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting_asc'].'</option>
          <option value="desc"'.static::optionSelected('desc',$this->varValue[$row]['sorting']).'>'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting_desc'].'</option>
        </select>
      </div>';
    // add hidden fields
		for ($i=0, $c=count($this->varValue); $i<$c; $i++) {
      if ($i != $row) {
        if ($this->varValue[$i]['type'] == 'auto') {
          $return .= '
            <input type="hidden" name="'.$this->strId.'['.$i.'][name]" value="'.specialchars($this->varValue[$i]['name']).'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][type]" value="'.$this->varValue[$i]['type'].'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][auto_field]" value="'.$this->varValue[$i]['auto_field'].'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][auto_editable]" value="'.$this->varValue[$i]['auto_editable'].'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][visible]" value="'.$this->varValue[$i]['visible'].'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][sorting]" value="'.$this->varValue[$i]['sorting'].'" >';
        } else {
          $return .= '
            <input type="hidden" name="'.$this->strId.'['.$i.'][name]" value="'.specialchars($this->varValue[$i]['name']).'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][type]" value="'.$this->varValue[$i]['type'].'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][list]" value="'.specialchars($this->varValue[$i]['list']).'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][mandatory]" value="'.$this->varValue[$i]['mandatory'].'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][visible]" value="'.$this->varValue[$i]['visible'].'" >
            <input type="hidden" name="'.$this->strId.'['.$i.'][sorting]" value="'.$this->varValue[$i]['sorting'].'" >';
        }
      }
    }
    // add update button
    $return .= '
      <div class="w50 update">
        <input type="submit" id="ctrl_'.$this->strId.'_update" class="tl_submit" value="'.$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_update'].'" onclick="Backend.getScrollOffset()">
      </div>';
    // close infofieldsWizard DIV
    $return .= '
      </div>';
    // store the tab index
		\Cache::set('tabindex', $tabindex);
    // return the Html string
		return $return;
	}

}

