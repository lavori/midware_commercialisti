<?php
     include ('config.php');
     require_once ('db.php');
     require_once ('template.php');
     
     // Inizializzazione del database una sola volta
     $database = new Database($host, $username, $password, $db); 
     $database->connect();

     return function ($router)  use ($database, $dominio, $titolo, $apps, $menu) {
        /*Route dashboard*/
        $router->addRoute('/admin', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Dashboard',
                'serp' => '/admin',
                'menu' => $menu['dashboard'],
                'apps' => $apps,
                'h1' => 'Gestione Aziendale',
                'h2' => 'CRM di gestione aziendale',
                'date' => array(),
                'content' => "Questa è la tua dashboard"
            ];
             // Utilizza la funzione render per generare l'output HTML
             $result = render('dashboard', $content);

             // Restituisci l'output HTML generato
             return $result;
        });
        /*Route settings*/
        $router->addRoute('/settings', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            $query_ruoli  ="SELECT 
                                r.`ruolo`,
                                GROUP_CONCAT(DISTINCT p.`permesso` ORDER BY p.`permesso` SEPARATOR ', ') AS permessi_base,
                                GROUP_CONCAT(DISTINCT e.`permesso` ORDER BY e.`permesso` SEPARATOR ', ') AS permessi_extra
                            FROM 
                                `rules` AS r
                            LEFT JOIN `permessi` AS p ON FIND_IN_SET(p.id, r.permessi)
                            LEFT JOIN `permessi` AS e ON FIND_IN_SET(e.id, r.extra)
                            GROUP BY 
                                r.`ruolo`";
            $ruoli=$database->query($query_ruoli);
            $cat_merc=$database->select('cat_merc','*','sub=0');

            $contenuto=array(
                'Ruoli'=>array('6',$ruoli, '/settings/ruoli'),
                'Categorie Merciologiche'=>array('6',$cat_merc, '/settings/cat-merceologiche')
            );
            
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Setting',
                'serp' => '/settings',
                'menu' => $menu['settings'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Settings',
                'date' => array(),
                'content' => $contenuto
            ];
             // Utilizza la funzione render per generare l'output HTML
             $result = render('settings', $content);

             // Restituisci l'output HTML generato
             return $result;
        });
        //Route ruoli
        $router->addRoute('/settings/ruoli', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if(!isset($_POST['permessi']) || $_POST['permessi']=="") $_POST['permessi']=array();
                if(!isset($_POST['extra']) || $_POST['permessi']=="") $_POST['extra']=array();
                if($_POST['tipo_action']=="new"){
                    $data['ruolo']=$_POST['nomeruolo'];
                    $data['permessi']=implode(',',$_POST['permessi']).",";
                    $data['extra']=implode(',',$_POST['extra']).",";
                    $database->insert('rules', $data);
                } elseif ($_POST['tipo_action']=="update"){
                    $data['ruolo']=$_POST['nomeruolo'];
                    $data['permessi']=implode(',',$_POST['permessi']).",";
                    $data['extra']=implode(',',$_POST['extra']).",";
                    $where='id='.$_POST['id'];
                    $database->update('rules', $data, $where);
                }
            }
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if (isset($_GET['action']) && $_GET['action'] === "delete") {
                    // Verifica se ci sono utenti attivi con quel ruolo
                    $activeUsers = $database->select('users', '*', [
                        'ruolo' => $_GET['id'],
                        'status' => 'attivo' // Presupponendo che 'status' sia il campo per identificare gli utenti attivi
                    ]);
                    // Se non ci sono utenti attivi con quel ruolo, procedi con l'eliminazione
                    if (empty($activeUsers)) {
                        $database->delete('rules', 'id=' . $_GET['id']);
                        $message= "Ruolo eliminato con successo.";
                    } else {
                        $message= "Impossibile eliminare: ci sono utenti attivi con questo ruolo.";
                    }
                }
            }
            $query_ruoli  ="SELECT 
                                r.`id`,
                                r.`ruolo`,
                                GROUP_CONCAT(DISTINCT p.`permesso` ORDER BY p.`permesso` SEPARATOR ', ') AS permessi_base,
                                GROUP_CONCAT(DISTINCT e.`permesso` ORDER BY e.`permesso` SEPARATOR ', ') AS permessi_extra
                            FROM 
                                `rules` AS r
                            LEFT JOIN `permessi` AS p ON FIND_IN_SET(p.id, r.permessi)
                            LEFT JOIN `permessi` AS e ON FIND_IN_SET(e.id, r.extra)
                            GROUP BY 
                                r.`id`";
            $ruoli=$database->query($query_ruoli);
            $permessi=$database->select('permessi','*');

            $contenuto=array(
                'Ruoli'=>array('12',$ruoli),
            );
            
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Settings > Ruoli Utenti',
                'serp' => '/settings/ruoli',
                'menu' => $menu['settings'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Ruoli Utenti',
                'permessi' => $permessi,
                'content' => $ruoli
            ];
             // Utilizza la funzione render per generare l'output HTML
             $result = render('settings/ruoli', $content);

             // Restituisci l'output HTML generato
             return $result;
        });
        //Route categorie merciologiche
        $router->addRoute('/settings/cat-merciologiche', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                //debug
                //echo "<pre>";print_r($_POST); echo "</pre>"; exit();
                if($_POST['tipo_action']== 'new'){
                    $data['categoria']=$_POST['categoria'];
                    $data['sub']=$_POST['sub'];
                    $database->insert('cat_merc', $data);
                } elseif ($_POST['tipo_action']=="update"){
                    $data['categoria']=$_POST['categoria'];
                    $data['sub']=$_POST['sub'];
                    $where='id='.$_POST['id'];
                    //debug
                    //echo "<pre>";print_r($data); echo "</pre>"; //exit();
                    $database->update('cat_merc', $data, $where);
                }
            }
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if (isset($_GET['action']) && $_GET['action'] === "delete") {
                    // Verifica se ci sono elementi attivi con quella categoria
                    //*******************************************************//
                    //*                                                     *//
                    //*         Costruita la tab FORNITORI                  *//
                    //*         inserire controlo di esistenza              *//
                    //*         inserire controllo figli                    *//
                    //*                                                     *//
                    //*******************************************************//
                    /*
                    $activeUsers = $database->select('users', '*', [
                        'ruolo' => $_GET['id'],
                        'status' => 'attivo' // Presupponendo che 'status' sia il campo per identificare gli utenti attivi
                    ]);
                    */
                    $activeFornitori='';
                    // Se non ci sono utenti attivi con quel ruolo, procedi con l'eliminazione
                    if (empty($activeFornitori)) {
                        $database->delete('rules', 'id=' . $_GET['id']);
                        $message= "Categoria eliminata con successo.";
                    } else {
                        $message= "Impossibile eliminare: ci sono fornitori attivi in questa categoria.";
                    }
                }
            }
            $query_cat_merciologiche  ="SELECT 
                                            cm.id,
                                            cm.categoria,
                                            CASE 
                                                WHEN cm.sub = 0 THEN '' -- Indica che non c'è una categoria madre
                                                ELSE cm_madre.categoria    -- Mostra la categoria madre se `sub` > 0
                                            END AS categoria_madre,
                                            cm.data
                                        FROM 
                                            `cat_merc` AS cm
                                        LEFT JOIN 
                                            `cat_merc` AS cm_madre ON cm.sub = cm_madre.id;";
            $categorie=$database->query($query_cat_merciologiche);
            $categorie_madre=$database->select('cat_merc','id,categoria','sub=0');

            $contenuto=array(
                'categorie'=>array('12',$categorie,),
            );
            
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Settings > Categorie Merciologiche',
                'serp' => '/settings/cat-merciologiche',
                'menu' => $menu['settings'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Categorie Merciologiche',
                'cat-madre' => $categorie_madre,
                'content' => $categorie
            ];
             // Utilizza la funzione render per generare l'output HTML
             $result = render('settings/cat-merciologiche', $content);

             // Restituisci l'output HTML generato
             return $result;
        });
        /*Route user*/
        $router->addRoute('/users', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if($_POST['tipo_action']=="new"){
                    if($_POST['pwd']==$_POST['pwd2']){
                        $data=array(
                            'user'=>$_POST['user'],
                            'pwd'=>md5($_POST['pwd']),
                            'nome'=>$_POST['nome'],
                            'cognome'=>$_POST['cognome'],
                            'email'=>$_POST['email'],
                            'ruolo'=>$_POST['ruolo'],
                            'status'=>$_POST['status']
                        );
                        $database->insert('users', $data);
                    }
                } elseif ($_POST['tipo_action']=="update"){
                    $data['ruolo']=$_POST['nomeruolo'];
                    $data['permessi']=implode(',',$_POST['permessi']).",";
                    $data['extra']=implode(',',$_POST['extra']).",";
                    $where='id='.$_POST['id'];
                    $database->update('rules', $data, $where);
                }
            }
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if (isset($_GET['action']) && $_GET['action'] === "delete") {
                    // Verifica se ci sono utenti attivi con quel ruolo
                    $activeUsers = $database->select('users', '*', [
                        'ruolo' => $_GET['id'],
                        'status' => 'attivo' // Presupponendo che 'status' sia il campo per identificare gli utenti attivi
                    ]);
                    // Se non ci sono utenti attivi con quel ruolo, procedi con l'eliminazione
                    if (empty($activeUsers)) {
                        $database->delete('rules', 'id=' . $_GET['id']);
                        $message= "Ruolo eliminato con successo.";
                    } else {
                        $message= "Impossibile eliminare: ci sono utenti attivi con questo ruolo.";
                    }
                }
            }
            $query_utenti  ="SELECT 
                                u.`id`,
                                u.`user`,
                                u.`nome`, 
                                u.`cognome`,
                                u.`email`,
                                r.`ruolo`
                            FROM 
                                `rules` AS r,
                                `users` AS u
                            WHERE
                                u.`ruolo` = r.`id`";
                            
            $utenti=$database->query($query_utenti);
            $ruoli=$database->select('rules','*');
            $contenuto=$utenti;
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Utenti',
                'serp' => '/users',
                'menu' => $menu['users'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Utenti',
                'date' => array(),
                'content' => $contenuto
            ];
            $result = render('user/users', $content);
            return $result;
        });
        /*Route corrispettivi*/
        $router->addRoute('/corrispettivi', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($_POST['tipo_action']=="update"){
                    $data['corrispettivo']=$_POST['corrispettivo'];
                    $data['descrizione']=$_POST['descrizione'];
                    $data['email']=$_POST['email'];
                    $where='id='.$_POST['id'];
                    $database->update('corrispettivo', $data, $where);
                } elseif($_POST['tipo_action']=="new"){
                    $data['corrispettivo']=$_POST['corrispettivo'];
                    $data['descrizione']=$_POST['descrizione'];
                    $data['email']=$_POST['email'];
                    $database->insert('corrispettivo', $data);
                }
            }
            $query="SELECT 
                        c.id,
                        CONCAT(u.nome, ' ', u.cognome) AS utente,
                        c.descrizione AS descrizione,
                        c.corrispettivo AS valore, 
                        DATE_FORMAT(c.data, '%d/%m/%Y') AS data,
                        CASE 
                            WHEN c.mexal = 0 THEN 'non inviato'
                            WHEN c.mexal = 1 THEN DATE_FORMAT(c.data_invio, '%d/%m/%Y')
                            ELSE 'stato sconosciuto'
                        END AS Mexal
                    FROM 
                        corrispettivo AS c
                    INNER JOIN 
                        users AS u
                    ON 
                        u.id = c.u_id
                    WHERE 
                        u.ruolo = 3 AND
                        u.status = 'attivo'";
                        
            if (isset($_GET['utente']) && $_GET['utente']!='*'){
                $query.= ' AND c.u_id = '.$_GET['utente'];
            }
            if (isset($_GET['tipo']) && $_GET['tipo']!=''){
                $query.= ' AND c.mexal = '.$_GET['tipo'];
            }

            //echo $query; exit();
            $corrispettivi=$database->query($query);
            $where_u='ruolo = 3';
            $utenti=$database->select('users','*', $where_u);
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Corrispettivi',
                'serp' => '/corrispettivi',
                'menu' => $menu['corrispettivi'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Corrispettivi',
                'date' => array(),
                'utenti' => $utenti,
                'content' => $corrispettivi
            ];
            $result = render('corrispettivi/corrispettivi', $content);
            return $result;
        });
        /*Route corrispettivi
        $router->addRoute('/corrispettivi/generazione_multipla', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $u_id = $_POST['u_id'];
                $total_amount = $_POST['total_amount'];

                $start_date_iso = $_POST['data_inizio']; // Esempio: "2025-01-01T07:05"
                $end_date_iso = $_POST['data_fine'];    // Esempio: "2025-01-10T18:30"
                // Converte il formato "YYYY-MM-DDTHH:MM" in "d/m/Y H:i"
                $start_date = DateTime::createFromFormat("Y-m-d\TH:i", $start_date_iso)->format("d/m/Y H:i");
                $end_date = DateTime::createFromFormat("Y-m-d\TH:i", $end_date_iso)->format("d/m/Y H:i");
                
                $email = "no-reply@noreply.it";
                $num_entries = $_POST['num_corrispettivi'];
                //echo"<pre>";print_r($_POST);echo"</pre>";
                $corrispettivi = generaCorrispettivi($u_id, $total_amount, $start_date, $end_date, $email, $num_entries);
                //echo"<pre>";print_r($corrispettivi);echo"</pre>"; exit();
                foreach($corrispettivi as $corrispettivo){
                    $database->insert('corrispettivo', $corrispettivo);
                }
            }
            $query="SELECT 
                        c.id,
                        CONCAT(u.nome, ' ', u.cognome) AS utente,
                        c.descrizione AS descrizione,
                        c.corrispettivo AS valore, 
                        DATE_FORMAT(c.data, '%d/%m/%Y') AS data,
                        CASE 
                            WHEN c.mexal = 0 THEN 'non inviato'
                            WHEN c.mexal = 1 THEN DATE_FORMAT(c.data_invio, '%d/%m/%Y')
                            ELSE 'stato sconosciuto'
                        END AS Mexal
                    FROM 
                        corrispettivo AS c
                    INNER JOIN 
                        users AS u
                    ON 
                        u.id = c.u_id
                    WHERE 
                        u.ruolo = 3 AND
                        u.status = 'attivo'";
                        
            if (isset($_GET['utente']) && $_GET['utente']!='*'){
                $query.= ' AND c.u_id = '.$_GET['utente'];
            }
            if (isset($_GET['tipo']) && $_GET['tipo']!=''){
                $query.= ' AND c.mexal = '.$_GET['tipo'];
            }

            //echo $query; exit();
            $corrispettivi=$database->query($query);
            $where_u='ruolo = 3';
            $utenti=$database->select('users','*', $where_u);
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Generazione Corrispettivi Multipli',
                'serp' => '/corrispettivi/generazione_multipla',
                'menu' => $menu['corrispettivi'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Corrispettivi',
                'date' => array(),
                'utenti' => $utenti,
                'content' => $corrispettivi
            ];
            $result = render('corrispettivi/corrispettivi_multipli', $content);
            return $result;
        });*/
        $router->addRoute('/corrispettivi/ridistribuisci-incassi', function () use ($database) {
            $meseRiferimento = 3;  // Marzo (esempio)
            $annoRiferimento = 2025; // Anno (esempio)
            $incassoMensile = 3000.00; // Incasso mensile (esempio)
            $corrispettivi="";
        
            $result = ridistribuisciIncassiRoute($database, $meseRiferimento, $annoRiferimento, $incassoMensile);
            echo "<pre>";print_r($result);echo"</pre>"; exit();
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Corrispettivi',
                'serp' => '/corrispettivi/ridistribuisci-incassi',
                'menu' => $menu['corrispettivi'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Corrispettivi',
                'date' => array(),
                'utenti' => $utenti,
                'content' => $corrispettivi
            ];
            $result = render('corrispettivi/corrispettivi', $content);
            return $result;
        });

        /*Route app*/
        $router->addRoute('/app', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Dashboard',
                'serp' => '/app',
                'menu' => $menu['users'],
                'apps' => $apps,    
                'h1' => 'Gestione Corrispettivi',
                'h2' => '',
                'date' => array(),
                'content' => "asd"
            ];
            // Utilizza la funzione render per generare l'output HTML
            $result = renderMobi('app/moby', $content);
            // Restituisci l'output HTML generato
            return $result;
        });
        /*Route app/corrispettivo*/
        $router->addRoute('/app/corrispettivo', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            $content="";
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if($_POST['email']!=''){$email=$_POST['email'];} else {$email=NULL;}
                $data=array(
                    'u_id' =>$_SESSION['utente']['id'],
                    'descrizione' => $_POST['descrizione'],
                    'email' => $email,
                    'corrispettivo' => $_POST['corrispettivo'],
                );
                $database->insert('corrispettivo', $data);
                $content="ok";
            }
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Dashboard',
                'serp' => '/app',
                'menu' => $menu['users'],
                'apps' => $apps,    
                'h1' => 'Gestione Corrispettivi',
                'h2' => '',
                'date' => array(),
                'content' => $content
            ];
            // Utilizza la funzione render per generare l'output HTML
            $result = renderMobi('app/corrispettivo', $content);
            // Restituisci l'output HTML generato
            return $result;
        });
        /*Route app/invio_corrispettivi*/
        $router->addRoute('/app/invio_corrispettivi', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            if ($_SERVER['REQUEST_METHOD'] === 'GET' and (isset($_GET['ids']) && null !== $_GET['ids']) ) {
                $where=" `id` IN (".$_GET['ids'].")";
                $corrispettivi=$database->select('corrispettivo','*',$where);
                $url="https://services.passepartout.cloud/webapi/risorse/documenti/ordini-clienti";
                //credenziali e Variabili API
                $azienda=$_SESSION['utente']['azienda'];
                $anno=date('Y');
                $credentialsapi = base64_encode($usernameapi . ':' . $passwordapi);
                $authHeaderapi = 'Authorization: Passepartout ' . $credentialsapi . ' Dominio=' . $dominioapi;
                $contentTypeHeader = 'Content-type: application/json';
                $coordinateGestionaleHeader = 'Coordinate-Gestionale: Azienda='.$azienda.' Anno='.$anno;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array (
                                                        $authHeaderapi, 
                                                        $contentTypeHeader, 
                                                        $coordinateGestionaleHeader
                                                        )
                            );
                foreach($corrispettivi as $corrispettivo){   
                    // Crea un oggetto DateTime dalla variabile
                    $data = new DateTime($corrispettivo['data']);
                    // Formatta la data nel formato desiderato
                    $dataFormattata = $data->format('Ymd');         
                    $documento=array(
                        "sigla" => "OX",
                        "serie" =>              1,
                        "numero" =>             0,
                        "data_documento" =>     $dataFormattata,
                        "cod_conto" =>          $codice_conto, //cliente
                        "id_riga" =>            1,
                        "tp_riga" =>            array(1,'R'),
                        "codice_articolo" =>    $codice_articolo, //codice articolo default
                        "cod_iva" =>            $cod_iva, //codice iva
                        "quantita" =>           1,
                        "prezzo"=>              $corrispettivo['corrispettivo'], //valore corrispettivo
                    );

                    $entita_json=json_encode($documento);
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $entita_json);
                    $response = curl_exec($curl);
                    if ($response === false) {
                        $message= 'Errore cURL contabilizzazione: '.$url.' - ' . curl_error($curl);
                    } else {
                        // aggiornamento db
                        $data=array(
                            'mexal' => 1,
                            'data_invio' => NOW()
                        );
                        $where=' id = '.$corrispettivo['id'];
                        $database->update('corrispettivo', $data, $where);
                    }
                }
                
                curl_close($curl);
            }
            $where='u_id='.$_SESSION['utente']['id'].' and mexal=0';
            $corrispettivi=array();
            $corrispettivi=$database->select('corrispettivo','*',$where);
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Dashboard',
                'serp' => '/app',
                'menu' => $menu['users'],
                'apps' => $apps,    
                'h1' => 'Gestione Corrispettivi',
                'h2' => '',
                'date' => array(),
                'content' => $corrispettivi
            ];
            // Utilizza la funzione render per generare l'output HTML
            $result = renderMobi('app/invio_corrispettivo', $content);
            // Restituisci l'output HTML generato
            return $result;
        });
    };
    
    function generaCorrispettivi($u_id, $total_amount, $start_date, $end_date, $email, $num_entries) {
        $descrizioni = ["visita guidata", "acquisto biglietto", "donazione", "prenotazione evento", "servizio extra"];
    
        // Converte le date dal formato ISO 8601 a timestamp
        $start_date_obj = DateTime::createFromFormat("Y-m-d\TH:i", $start_date);
        $end_date_obj = DateTime::createFromFormat("Y-m-d\TH:i", $end_date);
    
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
    
        // Calcola il valore medio di una transazione
        $avg_transaction = $total_amount / $num_entries;
    
        // Imposta un minimo e massimo proporzionato
        $min_transaction = round($avg_transaction * 0.5, 2);  // 50% del valore medio
        $max_transaction = round($avg_transaction * 1.5, 2);  // 150% del valore medio
    
        // Assicura che il minimo sia almeno 0.01
        if ($min_transaction < 0.01) {
            $min_transaction = 0.01;
        }
    
        // Suddivisione casuale del totale in $num_entries valori
        $corrispettivi = [];
        $remaining_amount = $total_amount;
    
        for ($i = 0; $i < $num_entries - 1; $i++) {
            // Imposta un limite massimo realistico per evitare di superare il totale disponibile
            $max_possible = min($remaining_amount - ($num_entries - $i - 1) * $min_transaction, $max_transaction);
    
            // Genera un valore casuale tra min_transaction e max_possible
            $amount = round(mt_rand($min_transaction * 100, $max_possible * 100) / 100, 2);
            $remaining_amount -= $amount;
            $corrispettivi[] = $amount;
        }
    
        // L'ultimo valore è il rimanente per evitare errori di arrotondamento
        $corrispettivi[] = round($remaining_amount, 2);
    
        // Generazione dell'array finale
        $result = [];
        foreach ($corrispettivi as $corrispettivo) {
            $random_timestamp = mt_rand($start_timestamp, $end_timestamp);
            $random_date = date("Y-m-d H:i:s", $random_timestamp);
            $random_descrizione = $descrizioni[array_rand($descrizioni)];
    
            $result[] = [
                "u_id" => $u_id,
                "descrizione" => $random_descrizione,
                "email" => $email,
                "corrispettivo" => $corrispettivo,
                "data" => $random_date
            ];
        }
    
        return $result;
    }

    // Ridistribuzione Incassi
    function ridistribuisciIncassiRoute(PDO $database, int $meseRiferimento, int $annoRiferimento, float $incassoMensile) {
        $database->beginTransaction(); // Inizia la transazione
        try {
            // Ottieni il numero di giorni nel mese
            $numeroGiorni = cal_days_in_month(CAL_GREGORIAN, $meseRiferimento, $annoRiferimento);
            $incassiGiornalieri = [];
    
            // Recupera i dati dei tassisti dal database
            $tassisti = $database->select("tassisti", ["id", "`Turni di lavoro`"]);
    
            foreach ($tassisti as $tassista) {
                $tassistaId = $tassista['id'];
                $turni = preg_split('/[\[\],]+/', trim($tassista['Turni di lavoro'], '[]'));
                $incassoGiornalieroTassista = [];
                $incassoTotaleRidistribuito = 0;
                $giorniLavorativi = [];
    
                for ($i = 1; $i <= $numeroGiorni; $i++) {
                    $data = new DateTime("$annoRiferimento-$meseRiferimento-$i");
                    $giornoSettimana = date('D', $data->getTimestamp());
                    if (in_array($giornoSettimana, $turni)) {
                        $giorniLavorativi[] = $data->format('Y-m-d');
                    }
                }
    
                if (empty($giorniLavorativi)) {
                    continue;
                }
    
                $incassoMedioGiornaliero = $incassoMensile / count($giorniLavorativi);
                $incassoResiduo = $incassoMensile;
    
                foreach ($giorniLavorativi as $dataLavorativa) {
                    $variazione = rand(-1500, 1500) / 10000;
                    $incassoGiornaliero = round($incassoMedioGiornaliero * (1 + $variazione), 2);
    
                    if ((int)($incassoGiornaliero * 10) % 10 !== 0) {
                        $incassoGiornaliero = round($incassoGiornaliero, 1);
                    }
    
                    $incassoGiornalieroTassista[$dataLavorativa] = $incassoGiornaliero;
                    $incassoTotaleRidistribuito += $incassoGiornaliero;
                    $incassoResiduo -= $incassoGiornaliero;
    
                    if (!isset($incassiGiornalieri[$dataLavorativa])) {
                        $incassiGiornalieri[$dataLavorativa] = 0;
                    }
                    $incassiGiornalieri[$dataLavorativa] += $incassoGiornaliero;
                }
    
                if (!empty($giorniLavorativi) && round($incassoTotaleRidistribuito, 2) !== round($incassoMensile, 2)) {
                    $differenza = round($incassoMensile - $incassoTotaleRidistribuito, 2);
                    $ultimoGiorno = end($giorniLavorativi);
                    $incassoGiornalieroTassista[$ultimoGiorno] = round(($incassoGiornalieroTassista[$ultimoGiorno] ?? 0) + $differenza, 2);
                }
    
                // Salvataggio nella tabella 'incassi'
                foreach ($incassoGiornalieroTassista as $dataIncasso => $valoreIncasso) {
                    $dataInsert = [
                        'tassista_id' => $tassistaId,
                        'data_incasso' => $dataIncasso,
                        'valore_incasso' => $valoreIncasso
                    ];
    
                    echo "Query di inserimento in incassi: INSERT INTO incassi (" . implode(", ", array_keys($dataInsert)) . ") VALUES ('" . implode("', '", array_values($dataInsert)) . "')<br>";
                    var_dump($dataInsert);
                    $database->insert("incassi", $dataInsert);
                }
            }
    
            // Salvataggio nella tabella 'corrispettivi'
            foreach ($incassiGiornalieri as $data => $valore) {
                $dataObj = new DateTime($data);
                $giornoSettimanaNome = date('l', $dataObj->getTimestamp());
    
                $dataInsertCorrispettivi = [
                    'data' => $data,
                    'giorno_settimana' => $giornoSettimanaNome,
                    'valore_corrispettivo' => round($valore, 2)
                ];
    
                echo "Query di inserimento in corrispettivi: INSERT INTO corrispettivi (" . implode(", ", array_keys($dataInsertCorrispettivi)) . ") VALUES ('" . implode("', '", array_values($dataInsertCorrispettivi)) . "')<br>";
                var_dump($dataInsertCorrispettivi);
                $database->insert("corrispettivi", $dataInsertCorrispettivi);
            }
    
            $database->commit(); // Se tutto va bene, conferma la transazione
            return "Ridistribuzione incassi e salvataggio completati con successo.\n";
    
        } catch (Exception $e) {
            $database->rollback(); // Se c'è un errore, annulla la transazione
            return "Errore: " . $e->getMessage() . "\n";
        }
    }
    


?>
