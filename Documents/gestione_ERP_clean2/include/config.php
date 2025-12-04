<?php
//$dominio = "https://sviluppo.universitygtc.it";
$dominio = "https://gestione.universitygtc.it";

$titolo = "Università";

$host = "localhost";
$username = "root";
$password = "DB@@-A663d0.AdminInfo";
$db = "universita";

$revisione = "1.3";

$pathUpload = "/var/www/html/gestione-universitygtc/uploads";

$emailpdf = ["r.fortunato@networkgtc.it", "l.donofrio@networkgtc.it"];

$apps = array(
    'Home' => array('/dashboard', 'home', 'azure', '1,2'),
    'Area Discenti' => array('/discenti', 'user-round', 'azure', '1,2,7'),
    'Calendario Scadenze' => array('/scadenze_rate', 'calendar', 'primary', '1,2,7'),
    'Panieri' => array('/panieri', 'file-check', 'primary', '1,2,7'),
    'Settings' => array('/settings/facolta', 'setting', 'danger', '1,2'),
    'Pacchetti Esami' => array('/genera_pacchetti', 'view-x3', 'primary', '1,2,7'),
    'Area Ruoli' => array('/ruoli', 'opt', 'primary', '1'),
    'Gestione Profilo' => array('/users', 'account-setting', 'primary', '1,2'),
    'Reportistica' => array('/reportistica', 'reports', '', '1,2'),
);

$menu = array(
    'dashboard' => array(),
    'discenti' => array(
        'Gestione Discenti' => array('/discenti', 'list-index-fill', 'azure', '1,2,7'),
        'Convalida Esami' => array('/discenti/gestione/voti', 'list-thumb-fill', 'azure', '1,2,7'),
        'Domanda di Iscrizione' => array('/gestione_pdf', 'file-pdf', 'azure', '1,2'),
        'Elenco Domanda Iscrizione' => array('/elencoPDF', 'file-pdf', 'azure', '1,2'),
    ),
    'ruoli' => array(
        'Gestione Ruoli' => array('/ruoli', 'opt', 'azure', '1'),
    ),
    'settings' => array(
        'Elenco Enti' => array('/settings/enti', 'list-index-fill', 'azure', '1,2'),
        'Elenco Facoltà' => array('/settings/facolta', 'list-index-fill', 'azure', '1,2'),
        'Elenco Esami' => array('/settings/esami', 'list-index', 'azure', '1,2'),
        'Elenco Esami Master' => array('/settings/esami_master', 'list-index', 'azure', '1,2'),
        'Elenco Percosi' => array('/settings/percorsi', 'list-thumb-fill', 'azure', '1,2'),
        'Elenco Master' => array('/settings/master', 'list-thumb', 'azure', '1,2'),
        'Elenco Certificazioni' => array('/settings/certificazioni', 'list-thumb-fill', 'azure', '1,2'),
        'Gestione Tutor/Professori' => array('/settings/gestione', 'user-list-fill', 'primary', '1,2'),
        'Gestione Utenti' => array('/settings/utenti', 'account-setting', 'primary', '1,2'),
    ),
    'genera_pacchetti' => array(
        'Genera Pacchetti' => array('/genera_pacchetti', 'plus-c', 'azure', '1,2'),
        'Elenco Pacchetti' => array('/elenco_pacchetti', 'view-list-fill', 'azure', '1,2'),
    ),
    'users' => array(
        'Gestione Profilo' => array('/users', 'user', 'azure', '1,2'),
    ),
    'panieri' => array(
        'Gestione Paniere' => array('/panieri', 'list-index-fill', 'azure', '1,2,7'),
        'Crea Paniere' => array('/panieri/new', 'plus', 'primary', '1,2'),
    ),
    'scadenza_esami' => array(
        'Scadenzieri' => array(
            'Scadenzario Economico' => array('/scadenze_rate', 'calendar', 'azure', '1,2,7'),
            'Scadenzario Esami' => array('/scadenza_esami', 'calendar', 'azure', '1,2,7'),
        ),
        'Calendari' => array(
            'Calendario Scadenze Rate' => array('/calendario_rate', 'calendar', 'azure', '1,2,7'),
            'Calendario Scadenze Esami' => array('/calendario_esami', 'calendar', 'azure', '1,2,7'),
        ),
    ),
    'reportistica' => array(
        'Reportistica Discenti' => array('/reportistica', 'reports', 'azure', '1,2,7'),
        'Reportistica Prodotti' => array('/reportistica_prodotti', 'reports-alt', 'azure', '1,2,7'),
    ),
);

/*
    bg-blue-dim => color: #559bfb; 
    bg-azure-dim => color: #1676fb;
    bg-indigo-dim => color: #2c3782;
    bg-purple-dim => color: #816bff;
    bg-pink-dim => color: #ff63a5;
    bg-orange-dim => color: #ffa353;
    bg-teal-dim => color: #20c997;
    bg-primary-dim => color: #6576ff;
    bg-secondary-dim => color: #364a63;
    bg-success-dim => color: #1ee0ac; 
    bg-info-dim => color: #09c2de;
    bg-warning-dim => color: #f4bd0e;
    bg-danger-dim => color: #e85347;
    bg-light => color: #526484;
    bg-lighter => color: #8094ae;
    bg-dark-dim => color: #dde2ea;
    bg-gray-dim => color: #e9f0f9;
*/


?>