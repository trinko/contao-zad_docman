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
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['name'] = array('Nome', 'Inserisci il nome dell\'archivio di documenti.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['manager'] = array('Amministratori', 'Scegli il gruppo di utenti a cui affidare il ruolo di amministratore (ha la gestione completa dei documenti).');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['groups'] = array('Utenti abilitati', 'Scegli i gruppi di utenti a cui consentire l\'inserimento dei documenti.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['enableOthers'] = array('Mostra documenti degli altri', 'Abilita l\'opzione per consentire agli utenti abilitati di vedere (ma non modificare) anche i documenti inseriti dagli altri.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['waitingTime'] = array('Attesa per la pubblicazione', 'Inserisci il tempo di attesa per la pubblicazione: scaduto questo tempo, i documenti saranno pubblicati e potranno essere modificati o eliminati solo dagli amministratori.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['dir'] = array('Cartella dei documenti', 'Scegli la cartella dove inserire i documenti.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['fileTypes'] = array('Tipi di file', 'Inserisci la lista dei tipi di file che possono essere caricati.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['enableAttach'] = array('Consenti allegati', 'Abilita l\'opzione per consentire l\'aggiunta di allegati.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['enablePdf'] = array('Converti in PDF', 'Abilita l\'opzione per convertire automaticamente i documenti caricati in formato PDF. La possibilità di usare questa funzionalità dipende dalla configurazione del sistema.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['editing'] = array('Abilita la scrittura online dei documenti', 'Attiva l\'opzione per scrivere online i documenti; in caso contrario saranno caricati da file.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['template'] = array('Modello predefinito', 'Scegli il modello predefinito per i documenti.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['showTemplates'] = array('Abilita modelli', 'Abilita l\'opzione per consentire all\'utente di scegliere il modello per il documento.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notify'] = array('Abilita le notifiche', 'Attiva l\'opzione per inviare la notifica della pubblicazione di un documento.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notifyGroups'] = array('Destinatari delle notifiche', 'Scegli i gruppi di utenti che riceveranno la notifica.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notifyCollect'] = array('Raggruppa notifiche', 'Abilita l\'opzione per raggruppare le notifiche in modo da averne al massimo una al giorno.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notifySubject'] = array('Oggetto', 'Inserisci l\'oggetto della notifica.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notifyText'] = array('Testo', 'Inserisci il testo della notifica. Si possono usare appositi tag per l\'inserimento dei campi impostati.');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['active'] = array('Abilitato', 'Indica se l\'archivio dei documenti è attivo.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['settings_legend'] = 'Impostazioni principali';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['documents_legend'] = 'Impostazioni per i documenti';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['editing_legend'] = 'Scrittura online dei documenti';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['notify_legend'] = 'Notifiche';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['new'] = array('Nuovo', 'Crea un nuovo archivio di documenti');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['edit'] = array('Modifica', 'Modifica l\'archivio di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['templates'] = array('Modelli', 'Modelli di documento per l\'archivio di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['fields'] = array('Campi', 'Campi dati per l\'archivio di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['copy'] = array('Duplica', 'Duplica l\'archivio di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['delete'] = array('Cancella', 'Cancella l\'archivio di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['toggle'] = array('Abilita/Disabilita', 'Abilita o disabilita l\'archivio di documenti con ID %s');
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['show'] = array('Dettagli', 'Mostra i dettagli l\'archivio di documenti con ID %s');


/**
 * Labels
 */
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['wt_0'] = 'Nessuna attesa';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['wt_1'] = '1 ora';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['wt_2'] = '2 ore';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['wt_3'] = '3 ore';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['wt_6'] = '6 ore';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['wt_12'] = '12 ore';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['wt_24'] = '24 ore';
$GLOBALS['TL_LANG']['tl_zad_docman_archive']['lbl_default'] = 'Predefinito';

