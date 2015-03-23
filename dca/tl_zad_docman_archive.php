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
 * Table tl_zad_docman_archive
 */
$GLOBALS['TL_DCA']['tl_zad_docman_archive'] = array(
	// Configuration
	'config' => array(
		'dataContainer'                 => 'Table',
    'ctable'                        => array('tl_zad_docman_templates', 'tl_zad_docman_fields'),
		'enableVersioning'              => true,
		'onload_callback' => array(
			array('tl_zad_docman_archive', 'generateTemplate')
		),
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
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['edit'],
				'href'                      => 'act=edit',
				'icon'                      => 'edit.gif'
			),
			'templates' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['templates'],
				'href'                      => 'table=tl_zad_docman_templates',
				'icon'                      => 'system/modules/zad_docman/assets/templates.gif'
			),
			'fields' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['fields'],
				'href'                      => 'table=tl_zad_docman_fields',
				'icon'                      => 'system/modules/zad_docman/assets/fields.gif'
			),
			'copy' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['copy'],
				'href'                      => 'act=copy',
				'icon'                      => 'copy.gif'
			),
			'delete' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['delete'],
				'href'                      => 'act=delete',
				'icon'                      => 'delete.gif',
				'attributes'                => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['toggle'],
				'icon'                      => 'visible.gif',
				'attributes'                => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'           => array('tl_zad_docman_archive', 'toggleIcon')
			),
			'show' => array(
				'label'                     => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['show'],
				'href'                      => 'act=show',
				'icon'                      => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes' => array(
		'__selector__'                  => array('editing', 'notify'),
		'default'                       => '{settings_legend},name,manager,groups,enableOthers,waitingTime;{documents_legend},dir,fileTypes,enableAttach,enablePdf;{editing_legend},editing;{notify_legend:hide},notify;'
	),
	// Subpalettes
	'subpalettes' => array(
		'editing'                       => 'template,showTemplates',
		'notify'                        => 'notifyGroups,notifyCollect,notifySubject,notifyText'
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
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['name'],
			'search'                      => true,
			'exclude'                     => true,
			'inputType'                   => 'text',
			'eval'                        => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'manager' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['manager'],
			'exclude'                     => true,
			'inputType'                   => 'select',
			'foreignKey'                  => 'tl_member_group.name',
			'eval'                        => array('mandatory'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
			'sql'                         => "int(10) unsigned NOT NULL default '0'",
			'relation'                    => array('type'=>'hasOne', 'load'=>'eager')
		),
		'groups' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['groups'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'foreignKey'                  => 'tl_member_group.name',
			'eval'                        => array('multiple'=>true, 'tl_class'=>'w50'),
			'sql'                         => "blob NULL",
			'relation'                    => array('type'=>'hasMany', 'load'=>'eager')
		),
		'enableOthers' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['enableOthers'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('tl_class'=>'w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'waitingTime' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['waitingTime'],
			'exclude'                     => true,
			'inputType'                   => 'select',
		  'default'                     => 'wt_0',
		  'options'                     => array('wt_0', 'wt_1', 'wt_2', 'wt_3', 'wt_6', 'wt_12', 'wt_24'),
			'reference'                   => &$GLOBALS['TL_LANG']['tl_zad_docman_archive'],
			'eval'                        => array('mandatory'=>true, 'tl_class'=>'clr w50'),
			'sql'                         => "varchar(16) NOT NULL default ''"
		),
		'dir' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['dir'],
			'exclude'                     => true,
			'search'                      => true,
			'inputType'                   => 'fileTree',
			'eval'                        => array('mandatory'=>true, 'fieldType'=>'radio', 'files'=>false, 'tl_class'=>'w50'),
			'sql'                         => "binary(16) NULL"
		),
		'fileTypes' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['fileTypes'],
			'exclude'                     => true,
			'inputType'                   => 'text',
      'default'                     => 'odt,ods,odp,pdf,rtf,csv,doc,docx,xls,xlsx,ppt,pptx,pps,ppsx,html,htm,txt,zip,rar,7z',
			'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'enableAttach' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['enableAttach'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('tl_class'=>'clr w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'enablePdf' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['enablePdf'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('tl_class'=>'w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'editing' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['editing'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
		  'default'                     => '',
			'eval'                        => array('submitOnChange'=>true),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'template' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['template'],
			'exclude'                     => true,
			'inputType'                   => 'select',
    	'options_callback'            => array('tl_zad_docman_archive', 'getTemplates'),
			'eval'                        => array('mandatory'=>true, 'tl_class'=>'clr w50'),
			'sql'                         => "int(10) unsigned NOT NULL default '0'"
		),
		'showTemplates' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['showTemplates'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
		  'default'                     => '',
			'eval'                        => array('tl_class'=>'w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'notify' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notify'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
		  'default'                     => '',
			'eval'                        => array('submitOnChange'=>true),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'notifyGroups' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notifyGroups'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'foreignKey'                  => 'tl_member_group.name',
			'eval'                        => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'clr w50'),
			'sql'                         => "blob NULL",
			'relation'                    => array('type'=>'hasMany', 'load'=>'eager')
		),
		'notifyCollect' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notifyCollect'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
		  'default'                     => '',
			'eval'                        => array('tl_class'=>'w50'),
			'sql'                         => "char(1) NOT NULL default ''"
		),
		'notifySubject' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notifySubject'],
			'exclude'                     => true,
			'inputType'                   => 'text',
			'eval'                        => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr long'),
			'sql'                         => "varchar(255) NOT NULL default ''"
		),
		'notifyText' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notifyText'],
			'exclude'                     => true,
			'inputType'                   => 'textarea',
			'explanation'                 => 'zad_docman_notifytags',
			'eval'                        => array('mandatory'=>true, 'rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class'=>'clr'),
			'sql'                         => "text NULL"
		),
		'active' => array(
			'label'                       => &$GLOBALS['TL_LANG']['tl_zad_docman_archive']['active'],
			'exclude'                     => true,
			'inputType'                   => 'checkbox',
			'eval'                        => array('doNotCopy'=>true),
			'sql'                         => "char(1) NOT NULL default ''"
		)
	)
);


/**
 * Class tl_zad_docman_archive
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright Antonello Dessì 2014
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class tl_zad_docman_archive extends Backend {

	/**
	 * Return the "toggle" button
	 *
	 * @param array $row  The table row
	 * @param string $href  Url for the button
	 * @param string $label  Label text for the button
	 * @param string $title  Title text for the button
	 * @param string $icon  Icon name for the button
	 * @param string $attributes  Other attributes for the button
	 *
	 * @return string  Html text for the button
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
	 * Toggle visibility mode
	 *
	 * @param integer $id  Identifier of the record
	 * @param boolean $visible  True if archive is visible, False otherwise.
	 */
	public function toggleVisibility($id, $visible) {
		// update the database
		$this->Database->prepare("UPDATE tl_zad_docman_archive SET active=? WHERE id=?")
					         ->execute(($visible ? '1' : ''), $id);
	}

	/**
	 * Generate a default template for a new archive.
	 *
	 * @param \DataContainer $dc  The data container for the table.
	 */
	public function generateTemplate($dc) {
    if (!$dc->id) {
      // no selected record
      return;
    }
		$templates = $this->Database->prepare("SELECT count(*) AS cnt FROM tl_zad_docman_templates WHERE pid=?")
					                      ->execute($dc->id);
    if ($templates->cnt == 0) {
      // create a new default template
  		$set['pid'] = $dc->id;
  		$set['tstamp'] = time();
  		$set['name'] = $GLOBALS['TL_LANG']['tl_zad_docman_archive']['lbl_default'];
  		$set['text'] = NULL;
  		$this->Database->prepare("INSERT INTO tl_zad_docman_templates %s")->set($set)->execute();
    }
	}

	/**
	 * Return all templates for this archive
	 *
	 * @param \DataContainer $dc  The data container for the table.
	 *
	 * @return array  A list with all templates
	 */
	public function getTemplates($dc) {
    $list = array();
		$templates = $this->Database->prepare("SELECT id,name FROM tl_zad_docman_templates WHERE pid=? ORDER BY name")
					                      ->execute($dc->activeRecord->id);
    while ($templates->next()) {
      $list[$templates->id] = $templates->name;
    }
    return $list;
	}

}

