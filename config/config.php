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
 * BACK END MODULES
 */
$GLOBALS['BE_MOD']['content']['zad_docman'] = array(
	'tables'		   =>	array('tl_zad_docman'),
  'icon'			   =>	'system/modules/zad_docman/assets/icon.png',
	'stylesheet'   => 'system/modules/zad_docman/assets/be_styles.css',
  'javascript'   => 'system/modules/zad_docman/assets/be_scripts.js'
);


/**
 * BACK END FORM FIELDS
 */
$GLOBALS['BE_FFL']['infofieldsWizard'] = 'InfofieldsWizard';


/**
 * FRONT END MODULES
 */
$GLOBALS['FE_MOD']['zad_docman']['zad_docman_manager'] = 'ModuleZadDocmanManager';
$GLOBALS['FE_MOD']['zad_docman']['zad_docman_reader'] = 'ModuleZadDocmanReader';

