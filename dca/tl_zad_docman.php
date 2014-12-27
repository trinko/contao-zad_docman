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
 * Table tl_zad_docman
 */
$GLOBALS['TL_DCA']['tl_zad_docman'] = array(
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
		'document' => array(
			'sql'                         => "binary(16) NULL"
		),
		'text' => array(
			'sql'                         => "text NULL"
		),
		'attach' => array(
			'sql'                         => "blob NULL"
		),
		'sentBy' => array(
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		),
		'published' => array(
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'publishedTimestamp' => array(
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		)
	)
);


