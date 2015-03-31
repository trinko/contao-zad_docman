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
 * Help wizard messages
 */
$GLOBALS['TL_LANG']['XPL']['zad_docman_tags'] = array(
  array('Insert Tags',
    'You can use these insert tags:<br /><br />
     <strong>{{field:name}}</strong><br />
     insert the text value of the <em>name</em> field.<br /><br />
     <strong>{{attachnum}}</strong><br />
     insert the attachment number (used only for attachments).<br /><br />
    ')
);
$GLOBALS['TL_LANG']['XPL']['zad_docman_notifytags'] = array(
  array('Insert Tags',
    'You can use these insert tags:<br /><br />
     <strong>{{field:name}}</strong><br />
     insert the text value of the <em>name</em> field.<br /><br />
     <strong>{{repeat:start}}...{{repeat:end}}</strong><br />
     the text enclosed by the command <em>repeat</em> will be repeated for each document to notify (used when you group notifications).<br /><br />
    ')
);

