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
		'enableVersioning'            => true,
		'sql' => array(
      'keys'                        => array('id' => 'primary')
    )
	),
	// Listing
	'list' => array(
		'sorting' => array(
			'mode'                        => 1,
			'fields'                      => array('name'),
			'flag'                        => 1,
			'panelLayout'                 => 'search,limit'
		),
		'label' => array(
			'fields'                      => array('name'),
			'format'                      => '%s'
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
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman']['edit'],
				'href'                      => 'act=edit',
				'icon'                      => 'edit.gif'
			),
			'copy' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman']['copy'],
				'href'                      => 'act=copy',
				'icon'                      => 'copy.gif'
			),
			'delete' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman']['delete'],
				'href'                      => 'act=delete',
				'icon'                      => 'delete.gif',
				'attributes'                => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman']['toggle'],
				'icon'                      => 'visible.gif',
				'attributes'                => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'           => array('tl_zad_docman', 'toggleIcon')
			),
			'show' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman']['show'],
				'href'                      => 'act=show',
				'icon'                      => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes' => array(
		'__selector__'                => array('enableFields'),
		'default'                     => '{main_legend},name,manager;{user_legend:hide},groups,enableOthers;{document_legend},dir,fileTypes,enableAttach,enablePdf;{fields_legend},infoFields;{show_legend:hide},filelabel,filename,doclabel,perPage,groupbyList,grouplabel;'
	),
	// Subpalettes
	'subpalettes' => array(
	),
	// Fields
	'fields' => array(
		'id' => array(
			'sql'                         => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array(
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		),
		'name' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['name'],
			'search'                      => true,
			'exclude'                     => true,
			'inputType'                   => 'text',
			'eval'                        => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'manager' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['manager'],
			'exclude'                     => true,
			'inputType'                   => 'select',
			'foreignKey'                  => 'tl_member_group.name',
			'eval'                        => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'long'),
			'sql'                         => "int(10) unsigned NOT NULL default '0'",
			'relation'                    => array('type'=>'hasOne', 'load'=>'eager')
		),
		'groups' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['groups'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'foreignKey'                  => 'tl_member_group.name',
			'eval'                        => array('multiple'=>true, 'tl_class'=>'long'),
			'sql'                         => "blob NULL",
			'relation'                    => array('type'=>'hasMany', 'load'=>'lazy')
		),
		'enableOthers' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['enableOthers'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'dir' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['dir'],
			'exclude'                     => true,
			'inputType'                   => 'fileTree',
			'eval'                        => array('mandatory'=>true, 'fieldType'=>'radio', 'files'=>false, 'tl_class'=>'clr'),
			'sql'                         => "binary(16) NULL"
		),
		'fileTypes' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['fileTypes'],
			'exclude'                     => true,
			'inputType'                   => 'text',
      'default'                     => &$GLOBALS['TL_CONFIG']['uploadTypes'],
			'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'enableAttach' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['enableAttach'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('tl_class'=>'w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'enablePdf' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['enablePdf'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('tl_class'=>'w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'infoFields' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['infoFields'],
			'exclude'                     => true,
			'inputType'                   => 'infofieldsWizard',
			'sql'                         => "blob NULL"
		),
		'filelabel' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['filelabel'],
			'exclude'                     => true,
			'inputType'                   => 'text',
      'explanation'                 => 'zad_docman_tags',
			'default'                     => '',
			'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'helpwizard'=>true, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'filename' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['filename'],
			'exclude'                     => true,
			'inputType'                   => 'text',
      'explanation'                 => 'zad_docman_tags',
			'default'                     => '',
			'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'helpwizard'=>true, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'doclabel' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['doclabel'],
			'exclude'                     => true,
			'inputType'                   => 'text',
			'default'                     => '',
			'eval'                        => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'perPage' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['perPage'],
			'exclude'                     => true,
			'inputType'                   => 'text',
      'default'                     => '0',
			'eval'                        => array('rgxp'=>'digit', 'tl_class'=>'w50'),
			'sql'                         => "smallint(5) unsigned NOT NULL default '0'"
		),
		'groupbyList' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['groupbyList'],
			'exclude'                     => true,
			'inputType'                   => 'checkboxWizard',
			'options_callback'            => array('tl_zad_docman', 'fieldNameOptions'),
			'reference'                   => &$GLOBALS['TL_LANG']['tl_zad_docman'],
			'eval'                        => array('multiple'=>true, 'tl_class'=>'w50 clr'),
			'sql'                         => "blob NULL",
		),
		'grouplabel' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['grouplabel'],
			'exclude'                     => true,
			'inputType'                   => 'text',
      'explanation'                 => 'zad_docman_tags',
			'default'                     => '',
			'eval'                        => array('maxlength'=>255, 'helpwizard'=>true, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'active' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman']['active'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('doNotCopy'=>true),
			'sql'                         => "char(1) NOT NULL default ''"
		)
	)
);


/**
 * Class tl_zad_docman
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class tl_zad_docman extends Backend {

	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes) {
		if (strlen(Input::get('tid'))) {
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
			$this->redirect($this->getReferer());
		}
		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['active'] ? '' : 1);
		if (!$row['active']) {
			$icon = 'invisible.gif';
		}
		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}

	/**
	 * Disable/enable a document manager
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($id, $visible) {
		// Update the database
		$this->Database->prepare("UPDATE tl_zad_docman SET active=? WHERE id=?")
					         ->execute(($visible ? 1 : ''), $id);
	}

	/**
	 * Add the field name options depending on the info fields
	 * @param \DataContainer
	 * @return array
	 */
	public function fieldNameOptions($dc) {
    $opts = array();
    if (!empty($dc->activeRecord->infoFields)) {
      foreach (deserialize($dc->activeRecord->infoFields) as $key=>$item) {
        if ($item['sorting'] == 'none' || $item['visible'] != '1') {
          // exclude unsorted or invisible fields
          continue;
        }
        $opts[] = 'field_'.$key;
        $GLOBALS['TL_LANG']['tl_zad_docman']['field_'.$key] = $item['name'];
      }
    }
    // return option array
    return $opts;
  }

}

