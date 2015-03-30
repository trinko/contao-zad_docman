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
 * Table tl_module
 */

// Configuration
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] =
  array('tl_module_zad_docman', 'config');

// Palettes
$GLOBALS['TL_DCA']['tl_module']['palettes']['zad_docman_manager'] = '{title_legend},name,headline,type;{config_legend},zad_docman_archive,zad_docman_list,zad_docman_docname,zad_docman_attachname,perPage;{expert_legend:hide},cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['zad_docman_reader'] = '{title_legend},name,headline,type;{config_legend},zad_docman_archive,zad_docman_filter,zad_docman_filtervalue,zad_docman_groupby,zad_docman_list,zad_docman_docname,zad_docman_attachname,perPage;{expert_legend:hide},cssID,space';

// Fields
$GLOBALS['TL_DCA']['tl_module']['fields']['zad_docman_archive'] = array(
  'label'                       => &$GLOBALS['TL_LANG']['tl_module']['zad_docman_archive'],
  'exclude'                     => true,
  'inputType'                   => 'select',
  'foreignKey'                  => 'tl_zad_docman_archive.name',
  'eval'                        => array('mandatory'=>true, 'submitOnChange'=>true, 'includeBlankOption'=>true, 'tl_class'=>'clr'),
	'sql'                         => "int(10) unsigned NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['zad_docman_filter'] = array(
  'label'                       => &$GLOBALS['TL_LANG']['tl_module']['zad_docman_filter'],
  'exclude'                     => true,
	'inputType'                   => 'select',
	'options_callback'            => array('tl_module_zad_docman', 'getFieldsNull'),
	'eval'                        => array('tl_class'=>'w50'),
	'sql'                         => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['zad_docman_filtervalue'] = array(
  'label'                       => &$GLOBALS['TL_LANG']['tl_module']['zad_docman_filtervalue'],
  'exclude'                     => true,
	'inputType'                   => 'text',
	'eval'                        => array('maxlength'=>255, 'tl_class'=>'w50'),
	'sql'                         => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['zad_docman_groupby'] = array(
  'label'                       => &$GLOBALS['TL_LANG']['tl_module']['zad_docman_groupby'],
  'exclude'                     => true,
	'inputType'                   => 'select',
	'options_callback'            => array('tl_module_zad_docman', 'getFieldsNull'),
	'eval'                        => array('tl_class'=>'clr w50'),
	'sql'                         => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['zad_docman_list'] = array(
  'label'                       => &$GLOBALS['TL_LANG']['tl_module']['zad_docman_list'],
  'exclude'                     => true,
	'inputType'                   => 'sortableWizard',
	'options_callback'            => array('tl_module_zad_docman', 'getFields'),
	'eval'                        => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'clr'),
	'sql'                         => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['zad_docman_docname'] = array(
  'label'                       => &$GLOBALS['TL_LANG']['tl_module']['zad_docman_docname'],
  'exclude'                     => true,
	'inputType'                   => 'text',
	'explanation'                 => 'zad_docman_tags',
	'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'helpwizard'=>true, 'tl_class'=>'w50'),
	'sql'                         => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['zad_docman_attachname'] = array(
  'label'                       => &$GLOBALS['TL_LANG']['tl_module']['zad_docman_attachname'],
  'exclude'                     => true,
	'inputType'                   => 'text',
	'explanation'                 => 'zad_docman_tags',
	'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'helpwizard'=>true, 'tl_class'=>'w50'),
	'sql'                         => "varchar(255) NOT NULL default ''"
);


/**
 * Class tl_module_zad_docman
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright Antonello Dessì 2015
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class tl_module_zad_docman extends Backend {

	/**
	 * Dynamic fields configuration for the module
	 *
	 * @param \DataContainer $dc  The data container for the table.
	 */
	public function config($dc) {
		if ($_POST || (Input::get('act') != 'edit' && Input::get('act') != 'show')) {
      // not in edit mode
			return;
		}
		$module = ModuleModel::findByPk($dc->id);
		if ($module === null) {
      // record not found
			return;
		}
    if ($module->type == 'zad_docman_manager') {
      // module configuration
      Message::addInfo($GLOBALS['TL_LANG']['tl_module']['wrn_zad_docman_js']);
    }
  }

	/**
	 * Return all fields for this archive
	 *
	 * @param \DataContainer $dc  The data container for the table.
	 *
	 * @return array  A list with all fields
	 */
	public function getFields($dc) {
    $list = array();
		$fields = $this->Database->prepare("SELECT name,label FROM tl_zad_docman_fields WHERE pid=? ORDER BY sorting")
					                      ->execute($dc->activeRecord->zad_docman_archive);
    while ($fields->next()) {
      $list[$fields->name] = $fields->label;
    }
    return $list;
	}

	/**
	 * Return all fields for this archive and add null option
	 *
	 * @param \DataContainer $dc  The data container for the table.
	 *
	 * @return array  A list with all option
	 */
	public function getFieldsNull($dc) {
    $list = $this->getFields($dc);
    $first = array('__NULL__' => $GLOBALS['TL_LANG']['tl_module']['lbl_zad_docman_nofield']);
    return array_merge($first, $list);
	}

}

