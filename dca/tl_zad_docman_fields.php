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
 * Table tl_zad_docman_data
 */
$GLOBALS['TL_DCA']['tl_zad_docman_fields'] = array(
	// Configuration
	'config' => array(
		'dataContainer'                 => 'Table',
    'ptable'                        => 'tl_zad_docman_archive',
		'enableVersioning'              => true,
		'sql' => array(
      'keys'                        => array('id'=>'primary', 'pid'=>'index')
    )
	),
	// Listing
	'list' => array(
		'sorting' => array(
			'mode'                        => 4,
			'fields'                      => array('sorting'),
			'headerFields'                => array('name','enableAttach','editing','notify'),
			'panelLayout'                 => 'search,limit',
			'child_record_callback'       => array('tl_zad_docman_fields', 'listFields')
		),
		'global_operations' => array(
			'all' => array(
				'label'                     => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                      => 'act=select',
				'class'                     => 'header_edit_all',
				'attributes'                => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array(
			'edit' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['edit'],
				'href'                      => 'act=edit',
				'icon'                      => 'edit.gif'
			),
			'copy' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['copy'],
				'href'                      => 'act=copy',
				'icon'                      => 'copy.gif'
			),
			'delete' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['delete'],
				'href'                      => 'act=delete',
				'icon'                      => 'delete.gif',
				'attributes'                => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['show'],
				'href'                      => 'act=show',
				'icon'                      => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes' => array(
		'__selector__'                  => array('type'),
		'default'                       => '{settings_legend},name,label,type;'
	),
	// Subpalettes
	'subpalettes' => array(
		'type_t_text'                   => 'defaultValue,mandatory',
		'type_t_number'                 => 'defaultValue,mandatory',
		'type_t_sequence'               => 'mandatory',
		'type_t_date'                   => 'defaultNow,mandatory',
		'type_t_time'                   => 'defaultNow,mandatory',
		'type_t_datetime'               => 'defaultNow,mandatory',
		'type_t_choice'                 => 'list,listOther,mandatory',
		'type_t_mchoice'                => 'list,listOther,mandatory',
		'type_t_auto'                   => 'autofield'
	),
	// Fields
	'fields' => array(
		'id' => array(
			'sql'                         => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array(
			'foreignKey'                  => 'tl_zad_docman_archive.name',
			'sql'                         => "int(10) unsigned NOT NULL default '0'",
			'relation'                    => array('type'=>'belongsTo', 'load'=>'eager')
		),
    'sorting' => array(
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array(
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		),
		'name' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['name'],
			'search'                      => true,
			'exclude'                     => true,
			'inputType'                   => 'text',
			'eval'                        => array('mandatory'=>true, 'unique'=>true, 'rgxp'=>'alias', 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'label' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['label'],
			'search'                      => true,
			'exclude'                     => true,
			'inputType'                   => 'text',
			'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'type' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['type'],
			'exclude'                     => true,
			'inputType'                   => 'select',
		  'default'                     => 't_text',
		  'options'                     => array('t_text','t_number','t_sequence','t_date','t_time','t_datetime','t_choice','t_mchoice','t_auto'),
			'reference'                   => &$GLOBALS['TL_LANG']['tl_zad_docman_fields'],
			'eval'                        => array('mandatory'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
			'sql'                         => "varchar(16) NOT NULL default ''"
		),
		'defaultValue' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['defaultValue'],
			'exclude'                     => true,
			'inputType'                   => 'text',
			'eval'                        => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'defaultNow' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['defaultNow'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('tl_class'=>'w50 m12'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'list' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['list'],
			'exclude'                     => true,
			'inputType'                   => 'optionWizard',
			'eval'                        => array('mandatory'=>true, 'tl_class'=>'clr long'),
			'sql'                         => "blob NULL"
		),
		'listOther' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['listOther'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('tl_class'=>'w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'autofield' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['autofield'],
			'exclude'                     => true,
			'inputType'                   => 'select',
		  'default'                     => 'af_timestamp',
		  'options'                     => array('af_timestamp', 'af_user'),
			'reference'                   => &$GLOBALS['TL_LANG']['tl_zad_docman_fields'],
			'eval'                        => array('mandatory'=>true, 'tl_class'=>'w50'),
			'sql'                         => "varchar(16) NOT NULL default ''"
		),
		'mandatory' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_fields']['mandatory'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('tl_class'=>'clr w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		)
	)
);


/**
 * Class tl_zad_docman_fields
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class tl_zad_docman_fields extends Backend {

	/**
	 * List fields
	 *
	 * @param array $row  The table row
	 *
	 * @return string  Html text for an item
	 */
	public function listFields($row) {
		return
      '<div class="tl_content_left">'.$row['label'].' <span style="color:#b3b3b3;padding-left:3px">['.$row['name'].': '.$GLOBALS['TL_LANG']['tl_zad_docman_fields'][$row['type']].']</span></div>';
	}

}

