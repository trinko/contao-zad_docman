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
	'contao',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Models
	'zad_docman\ZadDocmanArchiveModel'  => 'system/modules/zad_docman/models/ZadDocmanArchiveModel.php',
	'zad_docman\ZadDocmanDataModel'     => 'system/modules/zad_docman/models/ZadDocmanDataModel.php',
	'zad_docman\ZadDocmanFieldsModel'   => 'system/modules/zad_docman/models/ZadDocmanFieldsModel.php',
	'zad_docman\ZadDocmanModel'         => 'system/modules/zad_docman/models/ZadDocmanModel.php',

	// Modules
	'zad_docman\ModuleZadDocman'        => 'system/modules/zad_docman/modules/ModuleZadDocman.php',
	'zad_docman\ModuleZadDocmanManager' => 'system/modules/zad_docman/modules/ModuleZadDocmanManager.php',
	'zad_docman\ModuleZadDocmanReader'  => 'system/modules/zad_docman/modules/ModuleZadDocmanReader.php',

	// Widgets
	'contao\SortableWizard'             => 'system/modules/zad_docman/widgets/SortableWizard.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'zaddm_confirm' => 'system/modules/zad_docman/templates',
	'zaddm_edit'    => 'system/modules/zad_docman/templates',
	'zaddm_groups'  => 'system/modules/zad_docman/templates',
	'zaddm_list'    => 'system/modules/zad_docman/templates',
	'zaddm_message' => 'system/modules/zad_docman/templates',
	'zaddm_reader'  => 'system/modules/zad_docman/templates',
	'zaddm_show'    => 'system/modules/zad_docman/templates',
));
