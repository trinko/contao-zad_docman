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
  array('Tag di inserimento',
    'Per inserire il contenuto preimpostato, usare la sintassi seguente:<br /><br />
     <strong>{{field:nome}}</strong><br />
     inserisce il valore corrente del campo <em>nome</em>.<br /><br />
     <strong>{{attachnum}}</strong><br />
     inserisce il numero dell\'allegato (usato solo per gli allegati).<br /><br />
    ')
);
$GLOBALS['TL_LANG']['XPL']['zad_docman_notifytags'] = array(
  array('Tag di inserimento',
    'Per inserire il contenuto preimpostato, usare la sintassi seguente:<br /><br />
     <strong>{{field:nome}}</strong><br />
     inserisce il valore corrente del campo <em>nome</em>.<br /><br />
     <strong>{{repeat:start}}...{{repeat:end}}</strong><br />
     il testo racchiuso dal comando <em>repeat</em> viene ripetuto per ogni documento da notificare (da usare nel caso di raggruppamento delle notifiche).<br /><br />
    ')
);

