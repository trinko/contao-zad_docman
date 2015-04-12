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
 * Error messages
 */
$GLOBALS['TL_LANG']['tl_zad_docman']['err_nologged'] = 'ATTENZIONE! Nessun utente connesso.';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_auth'] = 'ATTENZIONE! Utente non autorizzato.';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_nofields'] = 'ATTENZIONE! Il modulo non è impostato correttamente.';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_id'] = 'ATTENZIONE! Il documento indicato non esiste.';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_mandatory'] = 'Devi compilare il campo!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_dateformat'] = 'La data non è nel formato corretto!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_timeformat'] = 'L\'orario non è nel formato corretto!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_datimformat'] = 'La data e l\'orario non sono nel formato corretto!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_autovoid'] = 'Il campo automatico non può essere lasciato vuoto!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_userformat'] = 'L\'utente non esiste o non è scritto nel formato corretto!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_file'] = 'Il file non è stato caricato correttamente!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_filesize'] = 'La dimensione del file caricato è superiore ai limiti previsti!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_filetype'] = 'Il tipo di file caricato non è consentito!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_filecount'] = 'Non puoi caricare altri documenti!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_dropzone'] = 'Il programma usato non permette di caricare file trascinandoli su questa zona!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_store'] = 'Impossibile memorizzare il documento nella cartella di destinazione!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_nofile'] = 'Il file non è stato trovato!';
$GLOBALS['TL_LANG']['tl_zad_docman']['err_listother'] = 'Devi inserire un valore!';


/**
 * Warning messages
 */
$GLOBALS['TL_LANG']['tl_zad_docman']['wrn_nodata'] = 'Nessun documento presente';
$GLOBALS['TL_LANG']['tl_zad_docman']['wrn_cancelupload'] = 'Sei sicuro di voler annullare il caricamento in corso?';


/**
 * Labels
 */
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentlist'] = 'Documenti caricati';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentadd'] = 'Inserisci un nuovo documento';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentedit'] = 'Modifica il seguente documento';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentdelete'] = 'Cancella il seguente documento';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_documentshow'] = 'Dettagli del documento';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory'] = 'Campo obbligatorio';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_document'] = 'Documento';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attach'] = 'Allegati';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_attachnum'] = 'Allegato %s';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_status'] = 'Stato';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_published'] = 'Pubblicato';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_draft'] = 'Bozza';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_waiting'] = 'In attesa di pubblicazione';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_publishedtm'] = 'Pubblicato il %s';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_drafttm'] = 'Bozza (inserito il %s)';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_waitingtm'] = 'In attesa di pubblicazione dal %s';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_dropzone'] = 'Clicca o trascina qui i file per caricarli';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_listother'] = 'Altro';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_downloaddocument'] = 'Scarica il documento';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_downloadattach'] = 'Scarica l\'allegato %s';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_grouped'] = 'Documenti raggruppati per %s';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatorydesc'] = 'I campi contrassegnati con l\'asterisco sono obbligatori.';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_notification'] = 'Notifica';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_notifysend'] = 'Da inviare entro un\'ora';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_notifygroup'] = 'Da inviare a fine giornata';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_notifysent'] = 'Inviata il %s';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_zad_docman']['but_save'] = 'Salva';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_cancel'] = 'Annulla';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_confirm'] = 'Conferma';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_add'] = 'Nuovo documento';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_show'] = 'Dettagli';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_publish'] = 'Pubblica';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_unpublish'] = 'Togli pubblicazione';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_edit'] = 'Modifica';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_delete'] = 'Cancella';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_cancelupload'] = 'Annulla';
$GLOBALS['TL_LANG']['tl_zad_docman']['but_removefile'] = 'Cancella';

