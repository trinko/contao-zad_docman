<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Zad_docman
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'zad_docman',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Models
	'zad_docman\ZadDocmanDocModel'      => 'system/modules/zad_docman/models/ZadDocmanDocModel.php',
	'zad_docman\ZadDocmanInfoModel'     => 'system/modules/zad_docman/models/ZadDocmanInfoModel.php',
	'zad_docman\ZadDocmanModel'         => 'system/modules/zad_docman/models/ZadDocmanModel.php',

	// Modules
	'zad_docman\ModuleZadDocmanManager' => 'system/modules/zad_docman/modules/ModuleZadDocmanManager.php',
	'zad_docman\ModuleZadDocmanReader'  => 'system/modules/zad_docman/modules/ModuleZadDocmanReader.php',

	// Widgets
	'Contao\InfofieldsWizard'           => 'system/modules/zad_docman/widgets/InfofieldsWizard.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'zaddm_confirm' => 'system/modules/zad_docman/templates',
	'zaddm_edit'    => 'system/modules/zad_docman/templates',
	'zaddm_list'    => 'system/modules/zad_docman/templates',
	'zaddm_message' => 'system/modules/zad_docman/templates',
	'zaddm_reader'  => 'system/modules/zad_docman/templates',
));
