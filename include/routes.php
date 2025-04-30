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
        /*Route generazione automatica di incassi e corrispettivi*/
        $router->addRoute('/corrispettivi/ridistribuisci-incassi', function () use ($database, $dominio, $titolo, $apps, $menu) {
            //inizzializza variabili locali
            $corrispettivi="";
            $tipo_alert='';
            $result_azione="";
            $alert='';

            if( $_SERVER['REQUEST_METHOD'] === 'POST') {
                $meseRiferimento = $_POST['mese'];
                $annoRiferimento = $_POST['anno'];
                $incassoMensile = $_POST['incassoMensile'];
                if(checkIncassiCorrispettiviEsistono($database, $meseRiferimento, $annoRiferimento)){
                    $result_azione="Corrispettivi già generati per il mese di ".$meseRiferimento."/".$annoRiferimento;
                    $tipo_alert="danger";
                } else {
                    $azione = ridistribuisciIncassiRoute($database, $meseRiferimento, $annoRiferimento, $incassoMensile);
                    $result_azione=$azione;
                    if($azione=="Ridistribuzione incassi e salvataggio completati con successo."){
                        $result_azione="Corrispettivi generati con successo per il mese di ".$meseRiferimento."/".$annoRiferimento;
                        $tipo_alert="success";
                    } else {
                        $result_azione="Errore durante la generazione dei corrispettivi: ".$azione;
                        $tipo_alert="danger";
                    }
                }
                $corrispettivi = getCorrispettiviPerMese($database, $meseRiferimento, $annoRiferimento); 
            } 
            
        
            
            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Corrispettivi',
                'serp' => '/corrispettivi/ridistribuisci-incassi',
                'menu' => $menu['corrispettivi'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Corrispettivi',
                'alert' => $result_azione,
                'tipo_alert' => $tipo_alert,
                'date' => array(),
                'content' => $corrispettivi
            ];
            $result = render('corrispettivi/corrispettivi_multipli', $content);
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

        //fake data
        $router->addRoute('/fake_taxi', function () use ($database) {
            $nomi = ["Mario", "Luigi", "Anna", "Giovanni", "Paola", "Giuseppe", "Francesca", "Roberto", "Elena", "Marco",
                      "Sofia", "Alessandro", "Chiara", "Simone", "Valentina", "Luca", "Giulia", "Andrea", "Federica", "Davide"];
            $cognomi = ["Rossi", "Verdi", "Bianchi", "Neri", "Gialli", "Ferrari", "Russo", "Colombo", "Ricci", "Marino",
                        "Romano", "Greco", "Bruno", "Gallo", "Conti", "Esposito", "Mancini", "Giordano", "Lombardi", "De Luca"];
            $giorniSettimana = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $targhePrefissi = ["TA", "TB", "TC", "TD", "TE", "TF"];
            $licenzePrefissi = ["AB", "XY", "FG", "JK", "OP", "LM", "WZ", "QR", "ST", "UV"];
        
            $tassisti = [];
        
            for ($i = 1; $i <= 100; $i++) {
                $nome = $nomi[array_rand($nomi)];
                $cognome = $cognomi[array_rand($cognomi)];
                $licenza = $licenzePrefissi[array_rand($licenzePrefissi)] . rand(100, 999) . strtoupper(substr(md5(rand()), 0, 2));
                $telefono = "33" . rand(100000000, 999999999);
                $email = strtolower(str_replace(" ", ".", $nome) . "." . str_replace(" ", ".", $cognome)) . "@example.com";
                $targa = $targhePrefissi[array_rand($targhePrefissi)] . rand(1000, 9999) . strtoupper(substr(md5(rand()), 0, 2));
        
                // Genera turni casuali (massimo 3 giorni a settimana)
                shuffle($giorniSettimana);
                $numGiorni = rand(3, 6);
                $turni = array_slice($giorniSettimana, 0, $numGiorni);
                sort($turni);
                // Serializza l'array dei turni
                $turniStringa = serialize($turni);
        
                $tassisti[] = [
                    'Nome' => $nome,
                    'Cognome' => $cognome,
                    'Licenza di guida' => $licenza,
                    'Telefono' => $telefono,
                    'Email' => $email,
                    'Targa Taxi' => $targa,
                    'Turni di lavoro' => $turniStringa,
                ];
            }
        
            $query = "INSERT INTO tassisti (Nome, Cognome, `Licenza di guida`, Telefono, Email, `Targa Taxi`, `Turni di lavoro`) VALUES\n";
            $valori = [];
            foreach ($tassisti as $tassista) {
                $valori[] = "('" . implode("','", array_map('addslashes', array_values($tassista))) . "')";
            }
            $query .= implode(",\n", $valori) . ";";
        
            try {
                $result = $database->query($query);
                if ($result === TRUE) {
                    echo "100 tassisti inseriti correttamente nel database.";
                } else {
                    echo "Errore sconosciuto durante l'inserimento.";
                }
            } catch (Exception $e) {
                echo "Errore durante l'inserimento dei dati: " . $e->getMessage();
            }
        });
    }; 


    // Controllo di esistenza
    function checkIncassiCorrispettiviEsistono(Database $database, int $meseRiferimento, int $annoRiferimento): bool {
        // Controllo nella tabella 'incassi'
        $primoGiorno = date("Y-m-d", strtotime("$annoRiferimento-$meseRiferimento-01"));
        $ultimoGiorno = date("Y-m-t", strtotime($primoGiorno));

        $queryIncassi = "SELECT COUNT(*) FROM incassi_taxi WHERE data_incasso BETWEEN '$primoGiorno' AND '$ultimoGiorno'";
        $resultIncassi = $database->query($queryIncassi);
        if ($resultIncassi && $resultIncassi[0]['COUNT(*)'] > 0) {
            return true; // Esistono già incassi per questo periodo
        }

        // Controllo nella tabella 'corrispettivi'
        $queryCorrispettivi = "SELECT COUNT(*) FROM corrispettivi_taxi WHERE data BETWEEN '$primoGiorno' AND '$ultimoGiorno'";
        $resultCorrispettivi = $database->query($queryCorrispettivi);
        if ($resultCorrispettivi && $resultCorrispettivi[0]['COUNT(*)'] > 0) {
            return true; // Esistono già corrispettivi per questo periodo
        }

        return false; // Non esistono incassi né corrispettivi
    }
    // Ridistribuzione Incassi
    function ridistribuisciIncassiRoute(Database $database, int $meseRiferimento, int $annoRiferimento, float $incassoMensile) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $database->beginTransaction();
        try {
            $numeroGiorni = cal_days_in_month(CAL_GREGORIAN, $meseRiferimento, $annoRiferimento);
            $incassiGiornalieri = [];
    
            $tassisti = $database->select("tassisti", ["id", "`Turni di lavoro`"]);
    
            foreach ($tassisti as $tassista) {
                $tassistaId = $tassista['id'];
                // Decodifica dei turni
                $turni = unserialize($tassista['Turni di lavoro']);
    
                // Assicurati che $turni sia un array (gestisci il caso di null o errore di decodifica)
                if (!is_array($turni)) {
                    $turni = []; // o log di errore, a seconda della tua strategia di gestione degli errori
                    echo "  ATTENZIONE: Impossibile decodificare i turni per il tassista $tassistaId. Trattati come vuoti.<br>";
                }
                $incassoGiornalieroTassista = [];
                $incassoTotaleRidistribuito = 0;
                $giorniLavorativi = [];
    
                for ($i = 1; $i <= $numeroGiorni; $i++) {
                    $data = new DateTime("$annoRiferimento-$meseRiferimento-$i");
                    $giornoSettimana = date('D', $data->getTimestamp());
    
                    if (in_array($giornoSettimana, $turni)) {
                        $dataLavorativa = $data->format('Y-m-d');
                        $giorniLavorativi[] = $dataLavorativa;
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
    
                    //echo "  Query di inserimento in incassi: INSERT INTO incassi (" . implode(", ", array_keys($dataInsert)) . ") VALUES ('" . implode("', '", array_values($dataInsert)) . "')<br>";
                    //var_dump($dataInsert);
                    $result = $database->insert("incassi_taxi", $dataInsert);
                    if ($result === false) {
                        //echo "  Errore inserimento incassi: " . mysqli_error($database->connection) . "<br>";
                    } else {
                        //echo "  Inserimento in incassi riuscito<br>";
                    }
                }
            }

            // Salvataggio nella tabella 'corrispettivi'
            // Costruzione della query per l'inserimento multiplo in 'corrispettivi_taxi'
            $queryCorrispettivi = "INSERT INTO corrispettivi_taxi (data, giorno_settimana, valore_corrispettivo) VALUES ";
            $values = [];

            foreach ($incassiGiornalieri as $data => $valore) {
                $dataObj = new DateTime($data);
                $giornoSettimanaNome = date('l', $dataObj->getTimestamp());
                $values[] = "('$data', '$giornoSettimanaNome', " . round($valore, 2) . ")";
            }

            $queryCorrispettivi .= implode(",", $values);
            $result = $database->query_nr($queryCorrispettivi); // Usa query_nr per le query senza risultati
            if ($result === false) {
                throw new Exception("Errore inserimento corrispettivi: " . mysqli_error($database->connection));
            }
            $database->commit();
            return "Ridistribuzione incassi e salvataggio completati con successo.";
        } catch (Exception $e) {
            $database->rollback();
            return "Errore: " . $e->getMessage() . "\n";
        }
    }
    // Funzione per ottenere i corrispettivi per mese
    function getCorrispettiviPerMese(Database $database, int $meseRiferimento, int $annoRiferimento) {
        // Calcola il primo e l'ultimo giorno del mese
        $primoGiorno = date("Y-m-d", strtotime("$annoRiferimento-$meseRiferimento-01"));
        $ultimoGiorno = date("Y-m-t", strtotime($primoGiorno)); // "t" restituisce l'ultimo giorno del mese
    
        $query = "SELECT * FROM corrispettivi_taxi WHERE data BETWEEN '$primoGiorno' AND '$ultimoGiorno'";
    
        try {
            $result = $database->query($query);
            return $result;
        } catch (Exception $e) {
            // Gestisci l'errore (log, eccezione, ecc.)
            echo "Errore nella query: " . $e->getMessage();
            return false; // o throw $e;
        }
    }

    //Funzione per la registrazione dei corrispettivi
    /**
     * Invia una registrazione di prima nota all'API Mexal.
     *
     * @param Database $database Oggetto per l'interazione con il database (necessario per le credenziali).
     * @param float $cont Importo totale dei contanti incassati (lordo).
     * @param int $id_azienda ID dell'azienda per cui registrare la nota.
     * @param string $data Data della registrazione nel formato 'Ymd' (es. '20231027').
     * @param string $dnsapi URL base dell'API (es. "https://services.passepartout.cloud/webapi/risorse/").
     * @param string $codiceAziendaGestionale Codice azienda da usare nell'header Coordinate-Gestionale (es. 'IMP').
     * @param string $causaleContabile Causale contabile (es. 'CO').
     * @param string $contoCassa Codice conto cassa (es. '201.00001').
     * @param string $contoRicavo Codice conto ricavo (es. '807.00002').
     * @param string $contoIva Codice conto IVA (es. '415.00012').
     * @param float $aliquotaIva Valore aliquota IVA (es. 22.0).
     *
     * @return array Array associativo con l'esito dell'operazione:
     *               [
     *                   'esito' => 'ok' | 'ko',
     *                   'id_primanota' => int|null, // ID restituito dall'API in caso di successo
     *                   'errore' => string|null    // Messaggio di errore in caso di fallimento
     *               ]
     */
    function scritturaPrimaNota(
        Database $database,
        float $cont,
        int $id_azienda,
        string $data,
        string $dnsapi,
        string $codiceAziendaGestionale = 'IMP', // Valore di default, può essere sovrascritto
        string $causaleContabile = 'CO',
        string $contoCassa = '201.00001',
        string $contoRicavo = '807.00002',
        string $contoIva = '415.00012',
        float $aliquotaIva = 22.0
    ): array {

        // Inizializza l'array di risposta con valori di default per l'errore
        $risposta_array = [
            'esito' => 'ko',
            'id_primanota' => null,
            'errore' => 'Errore non specificato durante l\'esecuzione.'
        ];

        // --- 1. Recupero Credenziali API ---
        try {
            // Assicurati che l'ID azienda sia un intero per sicurezza
            $id_azienda_int = (int)$id_azienda;
            $credenziali = $database->select('aziende', 'userapi, pwdapi, dominioapi', 'id=' . $id_azienda_int);

            if (empty($credenziali) || !$credenziali[0]['userapi'] || !$credenziali[0]['pwdapi'] || !$credenziali[0]['dominioapi']) {
                $risposta_array['errore'] = "Azienda con ID $id_azienda_int non trovata o credenziali API incomplete nel database.";
                return $risposta_array; // Esce dalla funzione restituendo l'errore
            }

            $usernameapi = $credenziali[0]['userapi'];
            $passwordapi = $credenziali[0]['pwdapi'];
            $dominioapi = $credenziali[0]['dominioapi'];

        } catch (Exception $e) {
            $risposta_array['errore'] = "Errore durante il recupero delle credenziali dal DB: " . $e->getMessage();
            return $risposta_array; // Esce dalla funzione restituendo l'errore
        }

        // --- 2. Preparazione Dati e Header API ---
        $credentialsapi = base64_encode($usernameapi . ':' . $passwordapi);
        // Assicurati che $dominioapi non sia vuoto e costruisci l'header
        if (empty($dominioapi)) {
            $risposta_array['errore'] = "Dominio API non configurato per l'azienda ID $id_azienda_int.";
            return $risposta_array;
        }
        $authHeaderapi = 'Authorization: Passepartout ' . $credentialsapi . ' Dominio=' . $dominioapi;
        $contentTypeHeader = 'Content-type: application/json';
        $annoCorrente = date('Y');
        $coordinateGestionaleHeader = 'Coordinate-Gestionale: Azienda=' . $codiceAziendaGestionale . ' Anno=' . $annoCorrente;

        // Endpoint specifico per la prima nota
        $endpoint = "prima-nota/"; // Assicurati che sia corretto secondo la documentazione API

        // Calcolo importi scorporando l'IVA
        $moltiplicatoreIva = 1 + ($aliquotaIva / 100);
        $rica = round($cont / $moltiplicatoreIva, 2) * -1; // Importo ricavo (negativo)
        $iva = round($cont + $rica, 2) * -1;           // Importo IVA (negativo)
        // L'importo $cont per la cassa deve essere positivo
        $contCassa = abs($cont);

        // --- 3. Costruzione Payload JSON ---
        // Nota: La struttura esatta dipende dalle specifiche API di Mexal.
        // Questa struttura è basata sul tuo esempio. Potrebbe necessitare aggiustamenti.
        $entita = [
            "data_registr" => $data,
            "descrizione" => "Incasso corrispettivi del " . DateTime::createFromFormat('Ymd', $data)->format('d/m/Y'), // Descrizione più utile
            "cau_contabile" => $causaleContabile,
            "rstro_prot_iva" => 'C', // Registro Corrispettivi
            "serie_prot" => 1,
            "nr_protocollo" => 0,    // Solitamente assegnato dal gestionale
            'nr_documento' => 0,     // Potrebbe essere necessario un numero progressivo o altro
            'data_documento' => $data,
            // Righe contabili: Cassa (Dare), Ricavo (Avere), IVA (Avere)
            'codice_conto' => [
                ['0' => 1, '1' => $contoCassa],  // Riga 1: Cassa
                ['0' => 2, '1' => $contoRicavo], // Riga 2: Ricavo
                ['0' => 3, '1' => $contoIva]     // Riga 3: IVA ns/debito
            ],
            'importo_riga' => [
                ['0' => 1, '1' => $contCassa], // Dare Cassa (positivo)
                ['0' => 2, '1' => $rica],      // Avere Ricavo (negativo)
                ['0' => 3, '1' => $iva]        // Avere IVA (negativo)
            ],
            // Dettagli IVA sulla riga del ricavo (riga 2)
            'imponibile_iva' => [
                ['riga' => 2, 'castelletto' => 1, 'imponibile' => abs($rica)] // Imponibile positivo
            ],
            'imposta_iva' => [
                ['0' => 2, '1' => 1, '2' => abs($iva)] // Imposta positiva
            ],
            'imposta_iva' => [
                // Formato 'XX.X' o 'XX,X' dipende da API
                ['0' => 2, '1' => 1, '2' => number_format($aliquotaIva, 1, ',', '')]
            ]
        ];
        $entita_json = json_encode($entita);

        if ($entita_json === false) {
            $risposta_array['errore'] = "Errore nella codifica JSON del payload: " . json_last_error_msg();
            return $risposta_array;
        }

        // --- 4. Esecuzione Chiamata cURL ---
        $url = rtrim($dnsapi, '/') . '/' . ltrim($endpoint, '/'); // Costruzione URL sicura

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // Restituisce la risposta come stringa
        curl_setopt($curl, CURLOPT_HEADER, 0);         // Non includere header nella risposta
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // Segui eventuali redirect
        // ATTENZIONE: Disabilitare la verifica SSL è rischioso in produzione.
        // Usare solo se strettamente necessario e si è consapevoli dei rischi.
        // Sarebbe meglio configurare il server per accettare il certificato.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 0 è più corretto di FALSE per questa opzione
        curl_setopt($curl, CURLOPT_POST, true);        // Metodo POST
        curl_setopt($curl, CURLOPT_POSTFIELDS, $entita_json); // Body della richiesta
        curl_setopt($curl, CURLOPT_HTTPHEADER, [       // Array di header
            $authHeaderapi,
            $contentTypeHeader,
            $coordinateGestionaleHeader
        ]);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // Timeout per la richiesta (es. 30 secondi)

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Ottieni codice stato HTTP
        $curl_error = curl_error($curl); // Ottieni eventuale errore cURL

        curl_close($curl);

        // --- 5. Gestione Risposta ---
        if ($response === false) {
            // Errore a livello cURL (rete, timeout, etc.)
            $risposta_array['errore'] = 'Errore cURL: ' . $curl_error;
        } else {
            // La richiesta cURL è andata a buon fine, analizza la risposta HTTP e JSON
            $resp_array_api = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // La risposta non è JSON valido
                $risposta_array['errore'] = "Errore decodifica JSON dalla risposta API (HTTP $http_code): " . json_last_error_msg() . ". Risposta ricevuta (parziale): " . substr($response, 0, 500);
            } elseif ($http_code >= 200 && $http_code < 300 && isset($resp_array_api['id'])) {
                // Successo! (Codice HTTP 2xx e ID presente nel JSON)
                $risposta_array['esito'] = 'ok';
                $risposta_array['id_primanota'] = (int)$resp_array_api['id']; // Assicurati sia un intero
                $risposta_array['errore'] = null; // Nessun errore
            } else {
                // Errore restituito dall'API (HTTP non 2xx o JSON di errore)
                $apiErrorMessage = 'Errore sconosciuto dall\'API';
                if (isset($resp_array_api['error']['message'])) {
                    $apiErrorMessage = $resp_array_api['error']['message'];
                } elseif (isset($resp_array_api['message'])) { // Alcune API usano 'message'
                    $apiErrorMessage = $resp_array_api['message'];
                } elseif (is_string($response) && strlen($response) < 500) { // Se non è JSON ma è breve, mostrala
                    $apiErrorMessage = $response;
                }
                $risposta_array['errore'] = "Errore API (HTTP $http_code): " . $apiErrorMessage;
                // id_primanota rimane null
            }
        }

        // --- 6. Restituzione Risultato ---
        return $risposta_array;
    }

    


?>
