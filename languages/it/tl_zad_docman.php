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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_zad_docman']['name'] = array('Nome', 'Inserisci il nome del gestore di documenti.');
$GLOBALS['TL_LANG']['tl_zad_docman']['manager'] = array('Gruppo amministratore', 'Scegli il gruppo a cui affidare il ruolo di amministratore (ha la gestione completa dei documenti).');
$GLOBALS['TL_LANG']['tl_zad_docman']['groups'] = array('Gruppi di utenti abilitati', 'Scegli i gruppi a cui consentire il caricamento dei documenti.');
$GLOBALS['TL_LANG']['tl_zad_docman']['enableOthers'] = array('Documenti altrui visibili', 'Abilita l\'opzione per consentire agli utenti abilitati di vedere (ma non modificare) i documenti caricati da altri.');
$GLOBALS['TL_LANG']['tl_zad_docman']['dir'] = array('Cartella dei documenti', 'Scegli la cartella dove inserire i documenti.');
$GLOBALS['TL_LANG']['tl_zad_docman']['fileTypes'] = array('Documenti ammessi', 'Inserisci la lista dei tipi di file che possono essere caricati.');
$GLOBALS['TL_LANG']['tl_zad_docman']['enableAttach'] = array('Consenti allegati', 'Abilita l\'opzione per consentire l\'aggiunta di allegati al documento principale.');
$GLOBALS['TL_LANG']['tl_zad_docman']['enablePdf'] = array('Converti in PDF', 'Abilita l\'opzione per convertire automaticamente i documenti caricati in formato PDF.');
$GLOBALS['TL_LANG']['tl_zad_docman']['infoFields'] = array('Impostazione campi', 'Imposta i campi necessari per l\'inserimento delle informazioni richieste.');
$GLOBALS['TL_LANG']['tl_zad_docman']['filelabel'] = array('Etichetta per il file', 'Inserisci l\'etichetta mostrata per il file da scaricare; si possono utilizzare anche i tag di inserimento campi.');
$GLOBALS['TL_LANG']['tl_zad_docman']['filename'] = array('Nome del file', 'Inserisci il nome del file da scaricare; si possono utilizzare anche i tag di inserimento campi.');
$GLOBALS['TL_LANG']['tl_zad_docman']['doclabel'] = array('Etichetta per il documento', 'Inserisci l\'etichetta mostrata per indicare il documento.');
$GLOBALS['TL_LANG']['tl_zad_docman']['perPage'] = array('Documenti per pagina di amministrazione', 'Numero massimo di documenti visualizzati per pagina (0=nessun limite). Il parametro è usato nelle pagine di amministrazione.');
$GLOBALS['TL_LANG']['tl_zad_docman']['groupbyList'] = array('Campi per il raggruppamento', 'Scegli i campi in base a cui effettuare il raggruppamento in pagine. Si possono usare solo campi visibili e ordinati. Il parametro è usato nelle pagine di visualizzazione.');
$GLOBALS['TL_LANG']['tl_zad_docman']['grouplabel'] = array('Etichetta per il raggruppamento', 'Inserisci l\'etichetta mostrata per il ragruppamento impostato; si possono utilizzare anche i tag di inserimento campi.');
$GLOBALS['TL_LANG']['tl_zad_docman']['active'] = array('Abilitato', 'Indica se il gestore dei documenti è attivo.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_zad_docman']['main_legend'] = 'Impostazioni principali';
$GLOBALS['TL_LANG']['tl_zad_docman']['user_legend'] = 'Utenti abilitati';
$GLOBALS['TL_LANG']['tl_zad_docman']['document_legend'] = 'Impostazioni per i documenti';
$GLOBALS['TL_LANG']['tl_zad_docman']['fields_legend'] = 'Dati da inserire';
$GLOBALS['TL_LANG']['tl_zad_docman']['show_legend'] = 'Visualizzazione';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_zad_docman']['new'] = array('Nuovo', 'Crea un nuovo gestore di documenti');
$GLOBALS['TL_LANG']['tl_zad_docman']['edit'] = array('Modifica', 'Modifica il gestore di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman']['copy'] = array('Duplica', 'Duplica il gestore di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman']['delete'] = array('Cancella', 'Cancella il gestore di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman']['toggle'] = array('Abilita/Disabilita', 'Abilita o disabilita il gestore di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman']['show'] = array('Dettagli', 'Mostra i dettagli del gestore di documenti con ID %s');


/**
 * Labels
 */
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_name'] = 'Nome';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type'] = 'Tipo';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_auto'] = 'Automatico';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_text'] = 'Testo';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_number'] = 'Numero';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_date'] = 'Data';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_choice'] = 'Scelta';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_type_mchoice'] = 'Scelta multipla';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory'] = 'Obbligatorio';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory_yes'] = 'Si';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory_no'] = 'No';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_mandatory_auto'] = 'Automatico';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_visible'] = 'Visibile';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_visible_yes'] = 'Si';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_visible_no'] = 'No';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_auto_editable'] = 'Modificabile';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting'] = 'Ordinamento';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting_none'] = 'No';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting_asc'] = 'Crescente';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_sorting_desc'] = 'Decrescente';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_current'] = 'Campo in modifica';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_list'] = 'Elenco';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_update'] = 'Aggiorna';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_timestamp'] = 'Data e ora di invio';
$GLOBALS['TL_LANG']['tl_zad_docman']['lbl_user'] = 'Inviato da';
$GLOBALS['TL_LANG']['tl_zad_docman']['iw_edit'] = 'Modifica il campo';
$GLOBALS['TL_LANG']['tl_zad_docman']['iw_copy'] = 'Duplica il campo';
$GLOBALS['TL_LANG']['tl_zad_docman']['iw_delete'] = 'Elimina il campo';
$GLOBALS['TL_LANG']['tl_zad_docman']['iw_up'] = 'Sposta il campo sopra';
$GLOBALS['TL_LANG']['tl_zad_docman']['iw_down'] = 'Sposta il campo sotto';

