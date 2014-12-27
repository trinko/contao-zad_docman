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
 * Table tl_zad_docman_templates
 */
$GLOBALS['TL_DCA']['tl_zad_docman_templates'] = array(
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
			'fields'                      => array('name'),
			'flag'                        => 1,
			'headerFields'                => array('name','enableAttach','editing','notify'),
			'panelLayout'                 => 'search,limit',
			'child_record_callback'       => array('tl_zad_docman_templates', 'listTemplates')
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
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_templates']['edit'],
				'href'                      => 'act=edit',
				'icon'                      => 'edit.gif'
			),
			'copy' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_templates']['copy'],
				'href'                      => 'act=copy',
				'icon'                      => 'copy.gif'
			),
			'delete' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_templates']['delete'],
				'href'                      => 'act=delete',
				'icon'                      => 'delete.gif',
				'attributes'                => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_templates']['show'],
				'href'                      => 'act=show',
				'icon'                      => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes' => array(
		'__selector__'                  => array(),
		'default'                       => '{settings_legend},name,text;'
	),
	// Subpalettes
	'subpalettes' => array(
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
		'tstamp' => array(
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		),
		'name' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_templates']['name'],
			'search'                      => true,
			'exclude'                     => true,
			'inputType'                   => 'text',
			'eval'                        => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'text' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_templates']['text'],
			'exclude'                     => true,
			'inputType'                   => 'textarea',
			'explanation'                 => 'zad_docman_tags',
			'eval'                        => array('rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class'=>'clr'),
			'sql'                         => "text NULL"
		)
	)
);


/**
 * Class tl_zad_docman_templates
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class tl_zad_docman_templates extends Backend {

	/**
	 * List templates
	 *
	 * @param array $row  The table row
	 *
	 * @return string  Html text for an item
	 */
	public function listTemplates($row) {
		return
      '<div class="tl_content_left">'.$row['name'].'</div>';
	}

}

