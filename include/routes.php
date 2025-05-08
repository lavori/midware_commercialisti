<?php
     include ('config.php');
     require_once ('db.php');
     require_once ('template.php');
     
     // Inizializzazione del database una sola volta
     $database = new Database($host, $username, $password, $db); 
     $database->connect();

     return function ($router)  use ($database, $dominio, $titolo, $apps, $menu, $dominioapi) {
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
        //Route categorie merciologiche
        $router->addRoute('/settings/aziende', function () use ($database, $dominio, $titolo, $apps, $menu) {

            //Azioni col GET
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                //Delete
                if(isset($_GET['action']) &&  $_GET['action']=='delete' && isset($_GET['id'])) {
                    $data=array(
                        'attiva'=>'No'
                    );
                    $where= 'id= '.$_GET['id'];
                    $database->update('aziende', $data, $where);
                }
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                //Insert
                if (isset($_POST['action']) && $_POST['action']=='insert') {

                    $data = array(
                        'ragione_sociale' => $_POST['ragione_sociale'],
                        'dominioAPI' => $_POST['dominioAPI'],
                        'attiva' => 'Si'
                    );

                    $database->insert('aziende', $data);
                }
                //Update
                if (isset($_POST['aggiorna_id']) && !empty($_POST['aggiorna_id'])) {
                    $data = array(
                        'ragione_sociale' => $_POST['ragione_sociale'],
                        'dominioAPI' => $_POST['dominioAPI'],
                    );

                    $where = "id= " . $_POST['aggiorna_id'];

                    $database->update('aziende', $data, $where);
                }
            }

            $aziende = $database->select("aziende", "*", "attiva='Si'");

            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'title' => 'Settings > Aziende',
                'serp' => '/settings/aziende',
                'menu' => $menu['settings'],
                'apps' => $apps,
                'h1' => 'CRM di gestione aziendale',
                'h2' => 'Aziende',
                'content' => $aziende
            ];
            // Utilizza la funzione render per generare l'output HTML
            $result = render('settings/aziende', $content);
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
        /*Route tassisti*/
        $router->addRoute('/users/tassisti', function () use ($database, $dominio, $titolo, $apps, $menu) {

            //delete
            if (isset($_POST['delete']) && !empty($_POST['delete'])) {
                //echo'entro';exit();
                $data = array(
                    'visibilita' => 0
                );
                $where = "id= " . $_POST['delete'];
                $database->update("tassisti", $data, $where);

            }

            //update
            if (isset($_POST['aggiorna_id']) && !empty($_POST['aggiorna_id'])) {
                //print_r($_POST);exit();
                $data = array(
                    'nome' => $_POST['nome'],
                    'cognome' => $_POST['cognome'],
                    'cf' => $_POST['cf'],
                    'associato' => $_POST['associato'],
                );

                $where = "id= " . $_POST['aggiorna_id'];

                $database->update('tassisti', $data, $where);
            }

            //insert
            if (isset($_POST['insert']) && !empty($_POST['insert'])) {
                //print_r($_POST);exit();
                $data = array(
                    'nome' => $_POST['nome'],
                    'cognome' => $_POST['cognome'],
                    'cf' => $_POST['cf'],
                    'associato' => $_POST['associato'],
                );
                $database->insert("tassisti", $data);
            }

            $wheretassisti = "visibilita=1";
            $tassisti = $database->select("tassisti", "*", $wheretassisti);


            $content = [
                'dominio' => $dominio,
                'titolo' => $titolo,
                'tassisti' => $tassisti,
                'title' => 'Utenti > Tassisti',
                'serp' => '/user/tassisti',
                'menu' => $menu['users'],
                'apps' => $apps,
                'h1' => 'Gestione Tassisti',
                'h2' => 'Tassisti',
                'date' => array(),
                'content' => "Questa è la tua dashboard"
            ];
            // Utilizza la funzione render per generare l'output HTML
            $result = render('user/tassisti', $content);

            // Restituisci l'output HTML generato
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
        /*Route corrispettivi*/
        $router->addRoute('/corrispettivi/taxisti', function () use ($database, $dominio, $titolo, $apps, $menu)  {
            $alert='';
            $tipo_alert = '';
            $ricerca='corrispettivi_taxisti';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                //echo"<pre>"; print_r($_POST); echo "</pre>"; exit();
                if ($_POST['tipo_action']=="ricerca"){
                    $taxistaId = $_POST['taxista'] ?? '0';
                    $dataDaInput = $_POST['da'] ?? null;
                    $dataAInput = $_POST['a'] ?? null;
                    // --- Conversione Date ---
                    $dataDa = null;
                    $dataA = null;
                    try {
                        if ($dataDaInput) {
                            $dataDaObj = DateTime::createFromFormat('d/m/Y', $dataDaInput);
                            if ($dataDaObj) {
                                $dataDa = $dataDaObj->format('Y-m-d');
                            } else {
                                throw new Exception("Formato data 'Da' non valido.");
                            }
                        }
                        if ($dataAInput) {
                            $dataAObj = DateTime::createFromFormat('d/m/Y', $dataAInput);
                            if ($dataAObj) {
                                // Per includere tutto il giorno finale nella ricerca BETWEEN
                                $dataA = $dataAObj->format('Y-m-d');
                            } else {
                                throw new Exception("Formato data 'A' non valido.");
                            }
                        }
                    } catch (Exception $e) {
                        $alert = "Errore nel formato delle date: " . $e->getMessage();
                        $tipo_alert = 'danger';
                        // Non procedere con la query se le date sono errate
                        //goto render_page; // Salta alla fine per renderizzare la pagina con l'errore
                        $corrispettivi='';
                    }

                    // --- Costruzione Query ---
                    $queryParams = [];
                    if ($taxistaId === '0') { // Ricerca Corrispettivi (aggregati)
                        $query = "SELECT ct.contabilizzato, ct.n_registrazione, ct.data, ct.giorno_settimana, ct.valore_corrispettivo,  COUNT(DISTINCT it.tassista_id) AS numero_tassisti
                                  FROM corrispettivi_taxi ct
                                  LEFT JOIN incassi_taxi it ON ct.data = it.data_incasso
                                  WHERE 1=1"; // Clausola base
                        if ($dataDa) $query .= " AND ct.data >= '$dataDa'";
                        if ($dataA) $query .= " AND ct.data <= '$dataA'";
                        // Applica filtro contabilizzazione solo se si cercano i corrispettivi aggregati
                        if(isset($_POST['tipo'])) $query .= " AND ct.contabilizzato = '1'";
                        $query .= " GROUP BY ct.data, ct.giorno_settimana, ct.valore_corrispettivo ORDER BY ct.data";
                    } else { // Ricerca Incassi (singolo tassista)
                        $query = "SELECT it.data_incasso AS data, DAYNAME(it.data_incasso) AS giorno_settimana, it.valore_incasso, ct.contabilizzato AS incasso_contabilizzato
                                  FROM incassi_taxi it
                                  LEFT JOIN corrispettivi_taxi ct ON it.data_incasso = ct.data
                                  WHERE it.tassista_id = '$taxistaId'";

                        if ($dataDa) $query .= " AND data_incasso >= '$dataDa'";
                        if ($dataA) $query .= " AND data_incasso <= '$dataA'";
                        $query .= " ORDER BY data_incasso";
                        // Nota: Il filtro 'contabilizzato' non si applica direttamente agli incassi singoli qui
                        $ricerca='incassi_taxisti';
                    }
                } elseif($_POST['tipo_action']=="modifica_incasso"){
                    // Dati necessari per l'aggiornamento e la successiva ricerca/visualizzazione
                    $incassoNuovoValore = $_POST['incasso'] ?? 0.0;
                    $dataIncasso = $_POST['data_incasso'] ?? null; // Formato YYYY-MM-DD
                    $taxistaId = $_POST['tassista_id'] ?? null;
                    $dataDaInput = $_POST['da'] ?? null; // Formato dd/mm/yyyy (per ricerca successiva)
                    $dataAInput = $_POST['a'] ?? null;   // Formato dd/mm/yyyy (per ricerca successiva)

                    // Validazione minima
                    if (!$dataIncasso || !$taxistaId) {
                        $alert = "Dati mancanti per la modifica dell'incasso.";
                        $tipo_alert = 'danger';
                        $corrispettivi = []; // Nessun dato da mostrare
                    } else {
                        // Inizia la transazione
                        $database->beginTransaction();
                        try {
                            // 1. Aggiorna il singolo incasso
                            $updateIncassoData = ['valore_incasso' => $incassoNuovoValore];
                            $updateIncassoWhere = "data_incasso = '" . $database->escapeString($dataIncasso) . "' AND tassista_id = '" . $database->escapeString($taxistaId) . "'";
                            $affectedRowsIncasso = $database->update('incassi_taxi', $updateIncassoData, $updateIncassoWhere);

                            // 2. Ricalcola il totale corrispettivo per quella data
                            $querySum = "SELECT SUM(valore_incasso) AS nuovo_corrispettivo FROM incassi_taxi WHERE data_incasso = '" . $database->escapeString($dataIncasso) . "'";
                            $resultSum = $database->query($querySum);

                            if ($resultSum && isset($resultSum[0]['nuovo_corrispettivo'])) {
                                $nuovoCorrispettivo = $resultSum[0]['nuovo_corrispettivo'] ?? 0.0; // Default a 0.0 se NULL

                                // 3. Aggiorna il corrispettivo aggregato
                                $updateCorrispettivoData = ['valore_corrispettivo' => $nuovoCorrispettivo];
                                $updateCorrispettivoWhere = "data = '" . $database->escapeString($dataIncasso) . "'";
                                $affectedRowsCorrispettivo = $database->update('corrispettivi_taxi', $updateCorrispettivoData, $updateCorrispettivoWhere);

                                // Se l'aggiornamento del corrispettivo non ha modificato righe (potrebbe non esistere), potresti volerlo inserire
                                // if ($affectedRowsCorrispettivo == 0) {
                                //    // Logica per inserire il corrispettivo se non esiste (opzionale)
                                // }

                                // Se tutto ok, conferma la transazione
                                $database->commit();
                                $alert = "Incasso del $dataIncasso modificato con successo. Corrispettivo ricalcolato.";
                                $tipo_alert = 'success';

                            } else {
                                // Errore nel calcolo della somma
                                throw new Exception("Impossibile ricalcolare il corrispettivo totale per la data $dataIncasso.");
                            }

                        } catch (Exception $e) {
                            // Errore durante le operazioni DB, annulla la transazione
                            $database->rollback();
                            $alert = "Errore durante la modifica dell'incasso: " . $e->getMessage();
                            $tipo_alert = 'danger';
                        }

                        // --- Riesegui la query per visualizzare i dati aggiornati ---
                        // (Questa parte è simile a quella della ricerca, ma forzata per il tassista modificato)
                        $dataDa = $dataDaInput ? (DateTime::createFromFormat('d/m/Y', $dataDaInput) ? DateTime::createFromFormat('d/m/Y', $dataDaInput)->format('Y-m-d') : null) : null;
                        $dataA = $dataAInput ? (DateTime::createFromFormat('d/m/Y', $dataAInput) ? DateTime::createFromFormat('d/m/Y', $dataAInput)->format('Y-m-d') : null) : null;

                        $query = "SELECT it.data_incasso AS data, DAYNAME(it.data_incasso) AS giorno_settimana, it.valore_incasso, ct.contabilizzato AS incasso_contabilizzato
                                  FROM incassi_taxi it
                                  LEFT JOIN corrispettivi_taxi ct ON it.data_incasso = ct.data
                                  WHERE it.tassista_id = '" . $database->escapeString($taxistaId) . "'";

                        if ($dataDa) $query .= " AND data_incasso >= '$dataDa'";
                        if ($dataA) $query .= " AND data_incasso <= '$dataA'";
                        $query .= " ORDER BY data_incasso";
                        $ricerca='incassi_taxisti';

                        // Esegui la query per popolare $corrispettivi
                        $corrispettivi = $database->query($query); // La gestione errori della query è più avanti
                    }
                } elseif($_POST['tipo_action']=="modifica_corrispettivo"){
                    //echo"<pre>"; print_r($_POST); echo "</pre>"; exit();
                    // Dati necessari
                    $nuovoValoreCorrispettivo = (float)($_POST['corrispettivo'] ?? 0.0);
                    $dataCorrispettivo = $_POST['data_corrispettivo'] ?? null; // Formato YYYY-MM-DD
                    $dataDaInput = $_POST['da'] ?? null; // Formato dd/mm/yyyy (per ricerca successiva)
                    $dataAInput = $_POST['a'] ?? null;   // Formato dd/mm/yyyy (per ricerca successiva)
                    // --- Conversione Date ---
                    $dataDa = null;
                    $dataA = null;
                    try {
                        if ($dataDaInput) {
                            $dataDaObj = DateTime::createFromFormat('d/m/Y', $dataDaInput);
                            if ($dataDaObj) {
                                $dataDa = $dataDaObj->format('Y-m-d');
                            } else {
                                throw new Exception("Formato data 'Da' non valido.");
                            }
                        }
                        if ($dataAInput) {
                            $dataAObj = DateTime::createFromFormat('d/m/Y', $dataAInput);
                            if ($dataAObj) {
                                // Per includere tutto il giorno finale nella ricerca BETWEEN
                                $dataA = $dataAObj->format('Y-m-d');
                            } else {
                                throw new Exception("Formato data 'A' non valido.");
                            }
                        }
                    } catch (Exception $e) {
                        $alert = "Errore nel formato delle date: " . $e->getMessage();
                        $tipo_alert = 'danger';
                        // Non procedere con la query se le date sono errate
                        //goto render_page; // Salta alla fine per renderizzare la pagina con l'errore
                        $corrispettivi='';
                    }
                    // Validazione
                    if (!$dataCorrispettivo || $nuovoValoreCorrispettivo < 0) {
                        $alert = "Dati mancanti o non validi per la modifica del corrispettivo.";
                        $tipo_alert = 'danger';
                        $corrispettivi = [];
                    } else {
                        $database->beginTransaction();
                        try {
                            // 1. Ottieni il vecchio valore del corrispettivo
                                $where=" data = '" . $database->escapeString($dataCorrispettivo) . "'";
                                $resultVecchio = $database->select('corrispettivi_taxi','valore_corrispettivo',$where);
                                if (!$resultVecchio || !isset($resultVecchio[0]['valore_corrispettivo'])) {
                                    throw new Exception("Corrispettivo originale non trovato per la data $dataCorrispettivo.");
                                }
                                $vecchioValoreCorrispettivo = (float)$resultVecchio[0]['valore_corrispettivo'];

                            // 2. Calcola la differenza
                                $differenza = $nuovoValoreCorrispettivo - $vecchioValoreCorrispettivo;

                            // 3. Ottieni gli incassi e i tassisti per quella data
                                $where="data_incasso = '" . $database->escapeString($dataCorrispettivo) . "'";
                                $incassiDelGiorno = $database->select('incassi_taxi','tassista_id, valore_incasso',$where);

                            if (empty($incassiDelGiorno)) {
                                // Se non ci sono incassi, aggiorna solo il corrispettivo (caso anomalo?)
                                if ($differenza != 0) {
                                     throw new Exception("Impossibile distribuire la differenza: nessun incasso trovato per la data $dataCorrispettivo.");
                                }
                            } else {
                                // 4. Calcola il totale attuale degli incassi per la distribuzione proporzionale
                                    $totaleIncassiAttuale = 0;
                                    foreach ($incassiDelGiorno as $incasso) {
                                        $totaleIncassiAttuale += (float)$incasso['valore_incasso'];
                                    }

                                    $differenzaRimanente = $differenza; // Per gestire arrotondamenti

                                // 5. Distribuisci la differenza
                                    foreach ($incassiDelGiorno as $index => $incasso) {
                                        $tassistaId = $incasso['tassista_id'];
                                        $valoreAttuale = (float)$incasso['valore_incasso'];

                                        // Calcola la proporzione (distribuisci equamente se il totale è 0)
                                        $proporzione = ($totaleIncassiAttuale > 0) ? ($valoreAttuale / $totaleIncassiAttuale) : (1 / count($incassiDelGiorno));

                                        // Calcola l'aggiustamento, ma usa la differenza rimanente per l'ultimo elemento
                                        if ($index === count($incassiDelGiorno) - 1) {
                                            $aggiustamento = $differenzaRimanente;
                                        } else {
                                            $aggiustamento = round($differenza * $proporzione, 2);
                                            $differenzaRimanente -= $aggiustamento;
                                        }

                                        $nuovoIncassoTassista = max(0, round($valoreAttuale + $aggiustamento, 2)); // Non andare sotto zero

                                        // Aggiorna l'incasso del singolo tassista
                                        $updateIncassoData = ['valore_incasso' => $nuovoIncassoTassista];
                                        $updateIncassoWhere = "data_incasso = '" . $database->escapeString($dataCorrispettivo) . "' AND tassista_id = '" . $database->escapeString($tassistaId) . "'";
                                        $database->update('incassi_taxi', $updateIncassoData, $updateIncassoWhere);
                                    }
                            }

                            // 6. Aggiorna il corrispettivo totale
                                $updateCorrispettivoData = ['valore_corrispettivo' => $nuovoValoreCorrispettivo];
                                $updateCorrispettivoWhere = "data = '" . $database->escapeString($dataCorrispettivo) . "'";
                                $database->update('corrispettivi_taxi', $updateCorrispettivoData, $updateCorrispettivoWhere);

                                $database->commit();
                                $alert = "Corrispettivo del $dataCorrispettivo modificato. Differenza distribuita sugli incassi.";
                                $tipo_alert = 'success';

                        } catch (Exception $e) {
                            $database->rollback();
                            $alert = "Errore durante la modifica del corrispettivo: " . $e->getMessage();
                            $tipo_alert = 'danger';
                        }
                        $query = "SELECT ct.contabilizzato, ct.data, ct.giorno_settimana, ct.valore_corrispettivo,  COUNT(DISTINCT it.tassista_id) AS numero_tassisti
                                  FROM corrispettivi_taxi ct
                                  LEFT JOIN incassi_taxi it ON ct.data = it.data_incasso
                                  WHERE 1=1"; // Clausola base
                        if ($dataDa) $query .= " AND ct.data >= '$dataDa'";
                        if ($dataA) $query .= " AND ct.data <= '$dataA'";
                        // Applica filtro contabilizzazione solo se si cercano i corrispettivi aggregati
                        if(isset($_POST['tipo'])) $query .= " AND ct.contabilizzato = '1'";
                    }
                }
                // --- Esecuzione Query ---
                try {
                    // echo "<pre>Query: " . htmlspecialchars($query) . "</pre>"; // Debug query
                    $corrispettivi = $database->query($query);
                    if (empty($corrispettivi)) {
                        $alert = "Nessun risultato trovato per i criteri specificati.";
                        $tipo_alert = 'warning';
                    }
                } catch (Exception $e) {
                    $alert = "Errore durante la ricerca nel database: " . $e->getMessage();
                    $tipo_alert = 'danger';
                }
            } else {
                $corrispettivi = getCorrispettiviTaxi($database, date('Y'), 'contabilizzato=0');  //CorrispettiviTaxi  
            }
            $tassisti=$database->select('tassisti','id,Nome,Cognome,Dimissioni');
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
                'alert' => $alert,
                'tassisti' => $tassisti,
                'ricerca' => $ricerca,
                'content' => $corrispettivi
            ];
            $result = render('corrispettivi/corrispettivi_taxisti', $content);
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
            $result = render('corrispettivi/generazione_incassi_corrispettivi', $content);
            return $result;
        });
        /*Route per la registrazione in prima nota [tassisti/operatori]*/
        $router->addRoute('/corrispettivi/invio-corrispettivi', function () use ($database, $dominio, $dominioapi, $titolo, $apps, $menu) {
            if( $_SERVER['REQUEST_METHOD'] === 'POST') :
                $errorMessage = null; // Inizializza la variabile per il messaggio di errore
                $successMessage = null; // Inizializza la variabile per il messaggio di successo

                $tipo=$_POST['tipo'];
                if(isset($_POST['id_azienda']) && $_POST['id_azienda']!=""):
                    $id_azienda=$_POST['id_azienda'];
                else:
                    $id_azienda=1;
                endif;
                $dnsapi=$dominioapi;
                if (!isset($_POST['corrispettivo']) || !is_array($_POST['corrispettivo']) || empty($_POST['corrispettivo'])) {
                    $errorMessage = "Nessun corrispettivo selezionato per l'invio.";
                } else {
                    foreach($_POST['corrispettivo'] as $key => $value) {
                        $dati_economici=explode('|', $value);
                        // Controllo aggiuntivo: assicurati che explode abbia prodotto almeno 2 elementi
                        if (count($dati_economici) < 2) {
                            $errorMessage = "Formato dati corrispettivo non valido per il valore: " . htmlspecialchars($value);
                            break; // Interrompi il ciclo se un valore non è valido
                        }
                        $data_input = $dati_economici[0]; // Data nel formato Y-m-d
                        $cont_input = $dati_economici[1];
                        try {
                            $data_obj = new DateTime($data_input);
                            $data_formatted = $data_obj->format('Ymd'); // Formato per API
                            $cont = round((float)$cont_input, 2); // Assicura sia float
                        
                            // Chiama la funzione e salva la risposta
                            $risposta_array = scritturaPrimaNota($database, $cont, $id_azienda, $data_formatted, $tipo, $dnsapi);

                            // Controlla l'esito
                            if(isset($risposta_array['esito']) && $risposta_array['esito'] == 'ko'){
                                $errorMessage = "Errore durante l'invio del corrispettivo del " . $data_obj->format('d/m/Y') . ": " . ($risposta_array['errore'] ?? 'Errore sconosciuto.');
                                break; // Interrompi il ciclo al primo errore
                            }
                        } catch (Exception $e) {
                            // Gestisce eccezioni da DateTime o altri errori imprevisti
                            $errorMessage = "Errore imprevisto durante l'elaborazione del corrispettivo del " . htmlspecialchars($data_input) . ": " . $e->getMessage();
                            break; // Interrompi il ciclo
                        }

                    }
                }

                // Se non ci sono stati errori, imposta un messaggio di successo
                if ($errorMessage === null && isset($_POST['corrispettivo']) && !empty($_POST['corrispettivo'])) {
                    $successMessage = "Invio corrispettivi completato con successo.";
                }
                // Reindirizza alla pagina precedente o a una pagina di riepilogo, passando i messaggi
                // Qui reindirizziamo a /corrispettivi/taxisti (adatta se necessario)
                // Usiamo la sessione per passare i messaggi, è più robusto del GET
                session_start(); // Assicurati che la sessione sia attiva
                if ($errorMessage) {
                    $_SESSION['alert'] = $errorMessage;
                    $_SESSION['tipo_alert'] = 'danger';
                } elseif ($successMessage) {
                    $_SESSION['alert'] = $successMessage;
                    $_SESSION['tipo_alert'] = 'success';
                }
                ///corrispettivi/taxisti
                header('Location: corrispettivi/taxisti');
                exit();
            else:
                header('Location: /corrispettivi');
                exit(); 
            endif;
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
    /* Calcola le festività italiane per un dato anno.
     *
     * @param int $year L'anno per cui calcolare le festività.
     * @return array Un array di date ('Y-m-d') delle festività.
     */
    function getFestivitaItaliane(int $year): array {
        $easterDate = easter_date($year); // Timestamp di Pasqua
        $easterMonday = strtotime('+1 day', $easterDate); // Timestamp di Lunedì dell'Angelo

        $holidays = [
            date('Y-m-d', mktime(0, 0, 0, 1, 1, $year)),   // Capodanno
            date('Y-m-d', mktime(0, 0, 0, 1, 6, $year)),   // Epifania
            date('Y-m-d', $easterMonday),                  // Lunedì dell'Angelo
            date('Y-m-d', mktime(0, 0, 0, 4, 25, $year)),  // Festa della Liberazione
            date('Y-m-d', mktime(0, 0, 0, 5, 1, $year)),   // Festa dei Lavoratori
            date('Y-m-d', mktime(0, 0, 0, 6, 2, $year)),   // Festa della Repubblica
            date('Y-m-d', mktime(0, 0, 0, 8, 15, $year)),  // Ferragosto
            date('Y-m-d', mktime(0, 0, 0, 11, 1, $year)),  // Ognissanti
            date('Y-m-d', mktime(0, 0, 0, 12, 8, $year)),  // Immacolata Concezione
            date('Y-m-d', mktime(0, 0, 0, 12, 25, $year)), // Natale
            date('Y-m-d', mktime(0, 0, 0, 12, 26, $year)), // Santo Stefano
        ];

        return $holidays;
    }
    // Ridistribuzione Incassi
    function ridistribuisciIncassiRoute(Database $database, int $meseRiferimento, int $annoRiferimento, float $incassoMensile) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $database->beginTransaction();
        try {
            $numeroGiorni = cal_days_in_month(CAL_GREGORIAN, $meseRiferimento, $annoRiferimento);
            $incassiGiornalieri = []; // Totale per giorno per tabella corrispettivi

            // Ottieni le festività italiane per l'anno di riferimento una sola volta
            $holidays = getFestivitaItaliane($annoRiferimento);
            // echo "Festività per $annoRiferimento: <pre>" . print_r($holidays, true) . "</pre>"; // Debug

            //Inserire al termine del task di variazione registrazione tassisiti il controllo della data fine rapporto
            $tassisti = $database->select("tassisti", ["id", "`Turni di lavoro`"]);
    
            foreach ($tassisti as $tassista) {
                $tassistaId = $tassista['id'];
                // Decodifica dei turni
                $turni = unserialize($tassista['Turni di lavoro']);
                $turni = [];
                try {
                    // Aggiungi @ per sopprimere warning se unserialize fallisce
                    $decodedTurni = @unserialize($tassista['Turni di lavoro']);
                    if ($decodedTurni !== false && is_array($decodedTurni)) {
                        $turni = $decodedTurni;
                    } else {
                         error_log("ATTENZIONE: Impossibile decodificare i turni per il tassista $tassistaId. Valore: " . $tassista['Turni di lavoro']);
                         // echo "  ATTENZIONE: Impossibile decodificare i turni per il tassista $tassistaId. Trattati come vuoti.<br>"; // Mantieni se necessario per UI
                    }
                } catch (Exception $e) {
                     error_log("Eccezione durante unserialize per tassista $tassistaId: " . $e->getMessage());
                     // echo "  ECCEZIONE: Errore decodifica turni per tassista $tassistaId.<br>"; // Mantieni se necessario per UI
                }

                // Assicurati che $turni sia un array (gestisci il caso di null o errore di decodifica)
                if (!is_array($turni)) {
                    // Logga l'errore invece di fare echo direttamente, se possibile
                    error_log("ATTENZIONE: Impossibile decodificare i turni per il tassista $tassistaId. Trattati come vuoti.");
                    echo "  ATTENZIONE: Impossibile decodificare i turni per il tassista $tassistaId. Trattati come vuoti.<br>";
                }
                $incassoGiornalieroTassista = []; // Incasso per questo tassista per giorno
                $incassoTotaleRidistribuitoTassista = 0; // Totale ridistribuito per questo tassista
                $giorniLavorativiTassista = []; // Giorni lavorativi validi per questo tassista

                // echo "--- Tassista ID: $tassistaId, Turni: " . implode(', ', $turni) . " ---<br>"; // Debug

                for ($i = 1; $i <= $numeroGiorni; $i++) {
                    $data = new DateTime("$annoRiferimento-$meseRiferimento-$i");
                    $timestamp = $data->getTimestamp();
                    $giornoSettimanaNum = (int)date('N', $timestamp); // 1 (Lunedì) a 7 (Domenica)
                    $giornoSettimanaAbbr = date('D', $timestamp); // Mon, Tue, etc.
                    $dataFormattata = $data->format('Y-m-d');

                    // Verifica le condizioni:
                    // 1. È nel turno del tassista?
                    $isInShift = !empty($turni) && is_array($turni) && in_array($giornoSettimanaAbbr, $turni);
                    // 2. È un giorno feriale (Lunedì-Venerdì)?
                    $isWeekday = ($giornoSettimanaNum >= 1 && $giornoSettimanaNum <= 5);
                    // 3. NON è una festività italiana?
                    $isHoliday = in_array($dataFormattata, $holidays);

                    // Debug condizioni (decommenta per vedere i controlli giorno per giorno)
                    // echo "Data: $dataFormattata ($giornoSettimanaAbbr - $giornoSettimanaNum) -> InShift: " . ($isInShift ? 'SI' : 'NO') . ", Weekday: " . ($isWeekday ? 'SI' : 'NO') . ", Holiday: " . ($isHoliday ? 'SI' : 'NO') . "<br>";

                    if ($isInShift && $isWeekday && !$isHoliday) {
                        $giorniLavorativiTassista[] = $dataFormattata;
                        // echo "  -> AGGIUNTO a giorniLavorativiTassista<br>"; // Debug
                    } else {
                        // echo "  -> NON AGGIUNTO (Motivo: Turno=$isInShift, Feriale=$isWeekday, Festivo=$isHoliday)<br>"; // Debug
                    } 
                }
    
                if (empty($giorniLavorativiTassista)) {
                    // echo "Nessun giorno lavorativo valido per tassista $tassistaId nel periodo.<br>"; // Debug
                    continue;
                }
    
                // echo "Giorni Lavorativi Validi per Tassista $tassistaId: <pre>" . print_r($giorniLavorativiTassista, true) . "</pre>"; // Debug

                // Calcola l'incasso medio solo sui giorni lavorativi effettivi del tassista
                $numeroGiorniLavorativiTassista = count($giorniLavorativiTassista);
                $incassoMedioGiornaliero = $incassoMensile / $numeroGiorniLavorativiTassista;

                // Distribuzione e accumulo
                foreach ($giorniLavorativiTassista as $dataLavorativa) {
                    // Calcola variazione e incasso giornaliero
                    // (Usa mt_rand per una migliore casualità rispetto a rand)
                    $variazione = mt_rand(-1500, 1500) / 10000;
                    // Assicura che l'incasso non sia negativo
                    $incassoGiornaliero = max(0, round($incassoMedioGiornaliero * (1 + $variazione), 2));

                    // Arrotondamento a 1 decimale se non termina con 0
                    // Questo arrotondamento sembra strano, lo commento per ora.
                    // if ((int)($incassoGiornaliero * 10) % 10 !== 0) {
                    //     $incassoGiornaliero = round($incassoGiornaliero, 1);
                    // }
    
                    $incassoGiornalieroTassista[$dataLavorativa] = $incassoGiornaliero;
                    $incassoTotaleRidistribuitoTassista += $incassoGiornaliero;

                    // Accumula per i corrispettivi totali giornalieri
                    if (!isset($incassiGiornalieri[$dataLavorativa])) {
                        $incassiGiornalieri[$dataLavorativa] = 0;
                    }
                    $incassiGiornalieri[$dataLavorativa] += $incassoGiornaliero;
                }
    
                // Correzione arrotondamento per il tassista
                // Applica la differenza all'ultimo giorno lavorativo del tassista
                $differenza = round($incassoMensile - $incassoTotaleRidistribuitoTassista, 2);
                if ($differenza != 0) {
                    $ultimoGiorno = end($giorniLavorativiTassista);
                    // Assicurati che l'ultimo giorno esista nell'array prima di modificarlo
                    if (isset($incassoGiornalieroTassista[$ultimoGiorno])) {
                         $nuovoValoreUltimoGiorno = round($incassoGiornalieroTassista[$ultimoGiorno] + $differenza, 2);
                         // Assicura che non diventi negativo
                         $nuovoValoreUltimoGiorno = max(0, $nuovoValoreUltimoGiorno);
                         $differenzaEffettiva = $nuovoValoreUltimoGiorno - $incassoGiornalieroTassista[$ultimoGiorno];

                         // Aggiorna l'incasso del tassista
                         $incassoGiornalieroTassista[$ultimoGiorno] = $nuovoValoreUltimoGiorno;

                         // Aggiorna anche l'incasso totale giornaliero per i corrispettivi
                         if (isset($incassiGiornalieri[$ultimoGiorno])) {
                             $incassiGiornalieri[$ultimoGiorno] = round($incassiGiornalieri[$ultimoGiorno] + $differenzaEffettiva, 2);
                             $incassiGiornalieri[$ultimoGiorno] = max(0, $incassiGiornalieri[$ultimoGiorno]); // Non negativo
                         }
                         // echo "Correzione per Tassista $tassistaId: Aggiunti $differenza a $ultimoGiorno.<br>"; // Debug
                    } else {
                         error_log("Errore correzione: Ultimo giorno $ultimoGiorno non trovato per tassista $tassistaId.");
                         // echo "ERRORE Correzione: Ultimo giorno $ultimoGiorno non trovato per tassista $tassistaId.<br>"; // Debug
                    }
                }
    
                // Salvataggio nella tabella 'incassi_taxi' per questo tassista
                foreach ($incassoGiornalieroTassista as $dataIncasso => $valoreIncasso) {
                    // Verifica che il valore sia > 0 prima di inserire? Dipende dai requisiti.
                    if ($valoreIncasso >= 0) { // Inserisci anche 0 se necessario, altrimenti > 0
                        $dataInsert = [
                            'tassista_id' => $tassistaId,
                            'data_incasso' => $dataIncasso,
                            'valore_incasso' => $valoreIncasso
                        ];
                        // Usa prepared statements se possibile per sicurezza e performance
                        $result = $database->insert("incassi_taxi", $dataInsert);
                        // Aggiungi gestione errori più robusta se $result è false
                        if ($result === false) {
                             error_log("Errore inserimento incassi_taxi per Tassista $tassistaId, Data $dataIncasso.");
                             // Potrebbe essere utile lanciare un'eccezione qui per far scattare il rollback
                             // throw new Exception("Errore inserimento incassi_taxi per Tassista $tassistaId, Data $dataIncasso.");
                        }
                    }
                }
            } // Fine ciclo tassisti

            // Salvataggio nella tabella 'corrispettivi_taxi' (totali giornalieri)
            // Costruzione della query per l'inserimento multiplo in 'corrispettivi_taxi'
            if (!empty($incassiGiornalieri)) {
                $valuesCorrispettivi = [];
                // Ordina per data per inserimento più ordinato (opzionale)
                ksort($incassiGiornalieri);

                foreach ($incassiGiornalieri as $data => $valore) {
                    if ($valore >= 0) { // Inserisci anche 0 se necessario
                        $dataObj = new DateTime($data);
                        // Usa 'N' per giorno numerico (1-7) o 'l' per nome completo ('Monday', 'Tuesday', ...)
                        // Scegli quello che corrisponde alla definizione della tua tabella
                        $giornoSettimanaNome = $dataObj->format('l'); // Nome completo es. Monday
                        // $giornoSettimanaNum = $dataObj->format('N'); // Numero 1-7

                        // Assicurati che i valori siano correttamente escapati se non usi prepared statements
                        // La classe Database dovrebbe gestire l'escaping, ma è bene esserne consapevoli.
                        // Qui usiamo l'inserimento multiplo, quindi l'escaping è cruciale.
                        $escapedData = $database->escapeString($data);
                        $escapedGiornoNome = $database->escapeString($giornoSettimanaNome);
                        $escapedValore = round($valore, 2); // Già numerico, non serve escape SQL diretto

                        $valuesCorrispettivi[] = "('$escapedData', '$escapedGiornoNome', $escapedValore)";
                    }
                }

                if (!empty($valuesCorrispettivi)) {
                    $queryCorrispettivi = "INSERT INTO corrispettivi_taxi (data, giorno_settimana, valore_corrispettivo) VALUES ";
                    $queryCorrispettivi .= implode(",", $valuesCorrispettivi);

                    // Usa query_nr o un metodo appropriato per query senza risultati attesi
                    $resultCorrispettivi = $database->query_nr($queryCorrispettivi);
                    if ($resultCorrispettivi === false) {
                        // Lancia eccezione per triggerare il rollback
                        throw new Exception("Errore inserimento corrispettivi_taxi: " . $database->connection->error);
                    }
                }
            } else {
                 // echo "Nessun incasso giornaliero totale da salvare in corrispettivi.<br>"; // Debug
            }
            $database->commit();
            return "Ridistribuzione incassi e salvataggio completati con successo.";
        } catch (Exception $e) {
            $database->rollback();
            // Logga l'errore completo per il debug
            error_log("Errore in ridistribuisciIncassiRoute: " . $e->getMessage() . "\nStack Trace:\n" . $e->getTraceAsString());
            // Restituisci un messaggio generico o specifico all'utente
            return "Errore durante la ridistribuzione degli incassi: " . $e->getMessage(); // Rimuovi \n alla fine
        }
    }
    // Funzione per ottenere i corrispettivi per mese
    function getCorrispettiviPerMese(Database $database, int $meseRiferimento, int $annoRiferimento) {
        // Calcola il primo e l'ultimo giorno del mese
        $primoGiorno = date("Y-m-d", strtotime("$annoRiferimento-$meseRiferimento-01"));
        $ultimoGiorno = date("Y-m-t", strtotime($primoGiorno)); // "t" restituisce l'ultimo giorno del mese
    
        // Modifica la query per includere il conteggio dei tassisti distinti dalla tabella incassi_taxi
        $query = "SELECT
                      ct.data,
                      ct.giorno_settimana,
                      ct.valore_corrispettivo,
                      COUNT(DISTINCT it.tassista_id) AS numero_tassisti
                  FROM
                      corrispettivi_taxi ct
                  LEFT JOIN -- Usa LEFT JOIN per mantenere tutti i giorni con corrispettivi, anche se non ci sono incassi taxi associati
                      incassi_taxi it ON ct.data = it.data_incasso
                  WHERE ct.data BETWEEN '$primoGiorno' AND '$ultimoGiorno'
                  GROUP BY ct.data, ct.giorno_settimana, ct.valore_corrispettivo -- Raggruppa per la chiave univoca (data) e le altre colonne non aggregate
                  ORDER BY ct.data";

        try {
            $result = $database->query($query);
            return $result;
        } catch (Exception $e) {
            // Gestisci l'errore (log, eccezione, ecc.)
            echo "Errore nella query: " . $e->getMessage();
            return false; // o throw $e;
        }
    }
    // Funzione per ottenere i corrispettivi non contabilizzati
    function getCorrispettiviTaxi(Database $database, int $annoRiferimento = null, string $contabilizzato = '0') {
        // Imposta l'anno corrente se non fornito
        if ($annoRiferimento === null) {
            $annoRiferimento = (int)date('Y');
        }
        // Modifica la query per includere il conteggio dei tassisti distinti dalla tabella incassi_taxi
        $query = "SELECT
                      ct.contabilizzato,
                      ct.n_registrazione,
                      ct.data,
                      ct.giorno_settimana,
                      ct.valore_corrispettivo,
                      COUNT(DISTINCT it.tassista_id) AS numero_tassisti
                  FROM
                      corrispettivi_taxi ct
                  LEFT JOIN -- Usa LEFT JOIN per mantenere tutti i giorni con corrispettivi, anche se non ci sono incassi taxi associati
                      incassi_taxi it ON ct.data = it.data_incasso
                  WHERE YEAR(ct.data) = $annoRiferimento AND ct.contabilizzato = '$contabilizzato' -- Aggiunto filtro per anno e corretto escaping per contabilizzato
                  GROUP BY ct.data, ct.giorno_settimana, ct.valore_corrispettivo -- Raggruppa per la chiave univoca (data) e le altre colonne non aggregate
                  ORDER BY ct.data";

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
        string $tipo,
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
                    $risposta_array['esito'] = 'ko';
                    $risposta_array['errore'] = "Errore decodifica JSON dalla risposta API (HTTP $http_code): " . json_last_error_msg() . ". Risposta ricevuta (parziale): " . substr($response, 0, 500);
                } elseif ($http_code >= 200 && $http_code < 300 && isset($resp_array_api['id'])) {
                    // Successo! (Codice HTTP 2xx e ID presente nel JSON)
                    $risposta_array['esito'] = 'ok';
                    $risposta_array['id_primanota'] = (int)$resp_array_api['id']; // Assicurati sia un intero
                    $risposta_array['errore'] = null; // Nessun errore

                    // Aggiornamento registrazione corrispettivo
                    $table="corrispettivi_".$tipo;
                    $data_tb=DateTime::createFromFormat('Ymd', $data)->format('Y-m-d');
                    $database->update($table,['n_registrazione'=>$risposta_array['id_primanota'], 'contabilizzato'=>1],'data='.$data_tb);

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
                    $risposta_array['esito'] = 'ko';
                    $risposta_array['errore'] = "Errore API (HTTP $http_code): " . $apiErrorMessage;
                    // id_primanota rimane null
                }
            }

        // --- 6. Restituzione Risultato ---
        return $risposta_array;
    }

    


?>
