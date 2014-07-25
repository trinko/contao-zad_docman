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
 * Table tl_module
 */

// Palettes
$GLOBALS['TL_DCA']['tl_module']['palettes']['zad_docman_manager'] = '{title_legend},name,headline,type;{config_legend},zad_docman;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['zad_docman_reader'] = '{title_legend},name,headline,type;{config_legend},zad_docman;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// Fields
$GLOBALS['TL_DCA']['tl_module']['fields']['zad_docman'] = array(
  'label'                        => &$GLOBALS['TL_LANG']['tl_module']['zad_docman'],
  'exclude'                      => true,
  'inputType'                    => 'select',
  'foreignKey'                   => 'tl_zad_docman.name',
  'eval'                         => array('mandatory'=>true, 'tl_class'=>'clr'),
	'sql'                          => "int(10) unsigned NOT NULL default '0'",
	'relation'                     => array('type'=>'hasOne', 'load'=>'lazy')
);

