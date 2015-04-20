<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
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
	// Classes
	'zad_docman\ZadDocman'              => 'system/modules/zad_docman/classes/ZadDocman.php',

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
	'zaddm_list'    => 'system/modules/zad_docman/templates',
	'zaddm_mail'    => 'system/modules/zad_docman/templates',
	'zaddm_message' => 'system/modules/zad_docman/templates',
	'zaddm_show'    => 'system/modules/zad_docman/templates',
	'zaddr_groups'  => 'system/modules/zad_docman/templates',
	'zaddr_reader'  => 'system/modules/zad_docman/templates',
));
