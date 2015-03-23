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
	'tables'		   =>	array('tl_zad_docman_archive','tl_zad_docman_templates','tl_zad_docman_fields'),
  'icon'			   =>	'system/modules/zad_docman/assets/icon.gif'
);


/**
 * BACK END FORM FIELDS
 */
$GLOBALS['BE_FFL']['sortableWizard'] = 'SortableWizard';


/**
 * FRONT END MODULES
 */
$GLOBALS['FE_MOD']['zad_docman']['zad_docman_manager'] = 'ModuleZadDocmanManager';
$GLOBALS['FE_MOD']['zad_docman']['zad_docman_reader'] = 'ModuleZadDocmanReader';


/**
 * CRON JOBS
 */
$GLOBALS['TL_CRON']['hourly'][] = array('ZadDocman', 'singleNotify');
$GLOBALS['TL_CRON']['daily'][] = array('ZadDocman', 'groupedNotify');

