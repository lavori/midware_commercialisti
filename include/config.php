<?php
$dominio="http://localhost:8080";
$titolo="CRM di gestione aziendale";

$host="localhost";
$username="root";
$password="";
$db="azienda";

$apps=array(
    'Home' => array('/','home','azure'),
    'Dashboard' => array('/admin','dashlite','azure'),
    'Settings' => array('/settings','setting','danger'),
    'Utenti' => array('/users','users','primary'),
    'Corrispettivi' => array('/corrispettivi','coins','primary')
);

$menu=array(
    'home' => array(),
    'dashboard' => array(),
    'settings' => array(
        'Ruoli Utenti' => array('/settings/ruoli','users','azure'),
        'Categorie Merciologiche' => array('/settings/cat-merciologiche','puzzle','azure'),
    ),
    'users' => array(
        'Utenti' => array('/users','users','azure')
    ),
    'corrispettivi' => array(
        'corrispettivi operatori' => array(
            'Gestione corrispettivi' => array('/corrispettivi','coins','primary'),
            'Generazione corrispettivi' => array('/corrispettivi/corrispettivi_multipli','setting','primary')
        ),
        'corrispettivi taxisti' => array(
            'Gestione corrispettivi' => array('/corrispettivi/taxisti','coins','primary'),
            'Generazione incassi/corrispettivi' => array('corrispettivi/ridistribuisci-incassi','setting','primary')
        )
    )
);

$prepath="C:\\xampp\\video\\";

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

//Variabili di impaginazione
$campiRegUtente=array(
                 'nome' => 'text',
                 'cognome' => 'text',
                 'ruolo' =>'select',
                 'abilitazione' => 'select',
                 'email' => 'email', 
                 'username' => 'text',
                 'password' => 'password',
                 'conferma_password' => 'password',
);
/*
$ruoli_piking=array(
    '1' => 'SuperAdmin',
    '2' => 'Admin', 
    '3' => 'Responsabile Magazzino',
    '4' => 'Responsabile Pacchi',
    '5' => 'Responsabile Spedizioni',
    '6' => 'Operatore Scaffali',
    '7' => 'Operatore Pacchi',
    '8' => 'Operatore Spedizioni',
    '9' => 'Customer Care',
); 

*/
//credenziali e Variabili API
$usernameapi="ROBERTO"; 
$passwordapi="ROBERTO2023";
$dominioapi="";
$codice_conto="";
$codice_articolo="";
$cod_iva="";




?>