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
 * Table tl_zad_docman_notify
 */
$GLOBALS['TL_DCA']['tl_zad_docman_notify'] = array(
	// Configuration
	'config' => array(
		'dataContainer'               => 'Table',
		'sql' => array(
      'keys'                        => array('id'=>'primary', 'pid'=>'index')
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
		'subject' => array(
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'text' => array(
			'sql'                         => "text NULL"
		),
		'recipients' => array(
			'sql'                         => "blob NULL"
		),
		'state' => array(
			'sql'                         => "varchar(16) NOT NULL default ''"
		)
	)
);

