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
 * Table tl_zad_docman_data
 */
$GLOBALS['TL_DCA']['tl_zad_docman_data'] = array(
	// Configuration
	'config' => array(
		'dataContainer'               => 'Table',
		'sql' => array(
      'keys'                        => array('id'=>'primary', 'pid'=>'index', 'field'=>'index')
    )
	),
	// Fields
	'fields' => array(
		'id' => array(
			'sql'                         => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array(
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array(
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		),
		'field' => array(
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'value' => array(
			'sql'                         => "varchar(255) NOT NULL default ''"
		)
	)
);


