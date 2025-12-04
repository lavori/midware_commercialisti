<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('config.php');
require_once('db.php');
require_once('template.php');
require './mailing/vendor/autoload.php';
require_once './vendor/autoload.php';
use Jurosh\PDFMerge\PDFMerger;

// Inizializzazione del database una sola volta
$database = new Database($host, $username, $password, $db);
$database->connect();
//Gestione ALERT

$query = "SELECT sr.*, ad.nome, ad.cognome 
            FROM scadenzeRate AS sr 
            JOIN an_discenti AS ad 
                ON sr.id_discente = ad.id 
            WHERE sr.pagato IS NULL 
            AND (
                sr.data_scadenza < CURDATE() -- rate vecchie non pagate
                OR
                (sr.data_scadenza >= CURDATE() AND sr.data_scadenza < CURDATE() + INTERVAL 8 DAY) -- in scadenza entro 8 giorni
            )
            ORDER BY sr.data_scadenza ASC";
$scadenze = $database->query($query);
$_SESSION['scadenze'] = $scadenze;

//Gestione ALERT rinvii esami
$queryRinvii = "SELECT 
                    COUNT(se.id) AS numero_rinvii,
                    se.id_esame,
                    se.id_discente,
                    ae.nome AS nome_esame,
                    ad.nome AS nome_discente,
                    ad.cognome AS cognome_discente
                FROM scadenzaEsami se
                JOIN an_esami ae ON se.id_esame = ae.id
                JOIN an_discenti ad ON se.id_discente = ad.id
                WHERE se.motivazione = 'rinviato'
                GROUP BY se.id_discente, se.id_esame, ae.nome, ad.nome, ad.cognome";
$rinvii = $database->query($queryRinvii);
$_SESSION['rinvii'] = $rinvii;

return function ($router) use ($database, $dominio, $titolo, $apps, $menu, $revisione, $emailpdf) {

    $router->addRoute('/', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        if ($_SESSION['utente']['ruolo'] != 'Discente') {
            header('location: /dashboard');
            exit();
        }
    });
    /*Route Dashboard*/
    $router->addRoute('/dashboard', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        //Per prendermi il totale dei discenti iscritti
        $queryDiscenti = "SELECT data_iscrizione, COUNT(id) AS totale
                            FROM an_discenti
                            GROUP BY data_iscrizione
                            ORDER BY data_iscrizione";
        $discenti = $database->query($queryDiscenti);


        // Inizializza array con 12 zeri (mesi da Jan a Dec)
        $monthly_data = array_fill(0, 12, 0);

        foreach ($discenti as $riga) {
            $mese = (int) date("n", strtotime($riga['data_iscrizione'])) - 1; // 0-based index
            $monthly_data[$mese] += (int) $riga['totale'];
        }

        // Preparazione per il JS
        $discenti_js = json_encode($monthly_data);

        $queryPagamento = "SELECT 
                                rd.id AS id_discente,
                                DATE(sr.dataPagamentoRata) AS data,
                                SUM(sr.importoRata) AS totaleGiornaliero,
                                rd.acconto
                            FROM 
                                rateDiscente AS rd
                            INNER JOIN 
                                scadenzeRate AS sr ON rd.id = sr.id_rateScadenze
                            WHERE 
                                sr.pagato = 'ok'
                            GROUP BY 
                                rd.id, DATE(sr.dataPagamentoRata)
                            ORDER BY 
                                rd.id, data ASC
                        ";

        $pagamenti = $database->query($queryPagamento);

        $mensili = [];
        $accontiUsati = [];

        foreach ($pagamenti as $row) {
            $id = $row['id_discente'];
            $data = $row['data'];
            $mese = date('Y-m', strtotime($data)); // Chiave mensile
            $importo = (float) $row['totaleGiornaliero'];

            // Somma acconto una sola volta per discente
            if (!isset($accontiUsati[$id])) {
                $importo += (float) $row['acconto'];
                $accontiUsati[$id] = true;
            }

            if (!isset($mensili[$mese])) {
                $mensili[$mese] = 0;
            }

            $mensili[$mese] += $importo;
        }

        ksort($mensili); // Ordina i mesi in ordine cronologico

        $pagamento_js = json_encode($mensili);


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Dashboard',
            'serp' => '/dashboard',
            'revisione' => $revisione,
            'menu' => $menu['dashboard'],
            'apps' => $apps,
            'discenti' => $discenti,
            'discenti_js' => $discenti_js,
            'pagamento_js' => $pagamento_js,
            'h1' => 'Dashboard',
            'h2' => "Benvenuto all'interno della tua dashboard",
            'date' => array(),
        ];
        // Utilizza la funzione render per generare l'output HTML
        $result = render('dashboard', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    /*Route Registrazione Discente Tramite Link*/
    $router->addRoute('/dashboard/{id}', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        $idDecoded = base64_decode($id['id']); // Decodifico l'ID
        $idDecoded = (int) $idDecoded; // Casting di sicurezza

        // Prendo il link dalla tabella per verificare se è scaduto
        $query = "SELECT * 
                FROM emailInviataDiscenti 
                WHERE id_discente = $idDecoded 
                AND data >= NOW() - INTERVAL 48 HOUR";
        $discente = $database->query($query);


        $datidiscente = "SELECT * FROM an_discenti WHERE id = " . $idDecoded;
        $datidiscente = $database->query($datidiscente);

        if (!empty($discente)) {

            $data_record = new DateTime($discente['data']);
            $now = new DateTime();

            $now->modify('-48 hours');
            //exit();
            if ($data_record >= $now) {
                $_SESSION['autorizzato'] = 'ok';
                $_SESSION['utente']['permesso'] = 'Discente';

                $_SESSION['utente']['nome'] = $datidiscente[0]['nome'];
                $_SESSION['utente']['cognome'] = $datidiscente[0]['cognome'];
                $_SESSION['utente']['last_login'] = "";
                $_SESSION['utente']['id'] = $datidiscente['id'];
                $_SESSION['utente']['email'] = $datidiscente['email'];

                header("Location: /discenti/" . $id['id']);
            } else {
                $_SESSION['message'] = "Link scaduto";
                header("Location: /");
                exit();
            }
        } else {
            $_SESSION['message'] = "Link non valido";
            header("Location: /");
            exit();
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Dashboard',
            'serp' => '/dashboard/' . $id['id'],
            'revisione' => $revisione,
            'menu' => $menu['dashboard'],
            'apps' => $apps,
            'h1' => 'Dashboard',
            'h2' => "Benvenuto all'interno della tua dashboard",
            'date' => array()
        ];
        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/visualizza_discenti', $content);

        return $result;
    });
    /*Route Ruoli*/
    $router->addRoute('/ruoli', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";

        //Delete
        if (isset($_GET['action']) && $_GET['action'] == 'delete') {

            $where = "id= " . $_GET['id'];

            $database->delete('rules', $where);
            $message = "delete";
        }

        //Update
        if (isset($_POST['action']) && $_POST['action'] == 'update') {

            if (isset($_POST['permessi'])) {
                $permesso = implode(',', $_POST['permessi']);
            } else {
                $permesso = '';
            }

            $data = array(
                'ruolo' => $_POST['ruolo'],
                'id_permesso' => $permesso
            );
            $where = "id= " . $_POST['id'];

            $database->update('rules', $data, $where);
            $message = "update";
        }

        //Insert
        if (isset($_POST['action']) && $_POST['action'] == 'new') {
            if (isset($_POST['permessi'])) {
                $permesso = implode(',', $_POST['permessi']);
            } else {
                $permesso = '';
            }

            $data = array(
                'ruolo' => $_POST['ruolo'],
                'id_permesso' => $permesso
            );

            $database->insert('rules', $data);
            $message = "insert";
        }

        $query_ruoli = "SELECT 
                            r.id AS id_ruolo, 
                            r.ruolo, 
                            GROUP_CONCAT(p.permesso ORDER BY p.id SEPARATOR ', ') AS permessi 
                        FROM rules r 
                        JOIN permessi p ON FIND_IN_SET(p.id, r.id_permesso) 
                        GROUP BY r.id, r.ruolo 
                        ORDER BY r.id";

        $ruoli = $database->query($query_ruoli);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Ruoli',
            'serp' => '/ruoli',
            'menu' => $menu['ruoli'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Ruoli',
            'revisione' => $revisione,
            'content' => $ruoli,
            'message' => $message
        ];

        $message = '';

        // Utilizza la funzione render per generare l'output HTML
        $result = render('ruoli', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    /*Area Generazione pacchetti esami*/
    $router->addRoute('/genera_pacchetti', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        //Insert
        if (isset($_POST['action']) && $_POST['action'] == 'new') {


            $esamiSelezionati = json_encode($_POST['esamiSelezionati']);

            $dati = [
                'id_esami' => $esamiSelezionati,
                'nomePacchetto' => $_POST['nomePacchetto']
            ];

            $database->insert('an_pacchettiEsame', $dati);

            $_SESSION['message'] = "insert";
            header("Location: /genera_pacchetti");

        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Pacchetti Esami>Genera Pacchetti',
            'serp' => '/pacchetti_esami',
            'menu' => $menu['genera_pacchetti'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Genera Pacchetti Esami',
            'date' => array(),
            'message' => $message,
            'content' => ""
        ];

        $message = '';

        // Utilizza la funzione render per generare l'output HTML
        $result = render('pacchetti_esami/genera_pacchetti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    /*Area modifica pacchetti esami*/
    $router->addRoute('/genera_pacchetti/update', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id = $_GET['id'];
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        //Update
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            $id_esami = json_encode($_POST['esamiSelezionati']);
            $dataUpdate = [
                'nomePacchetto' => $_POST['nomePacchetto'],
                'id_esami' => $id_esami,
            ];
            $where = "id= " . $id;
            $database->update('an_pacchettiEsame', $dataUpdate, $where);
            $_SESSION['message'] = "update";
            header("Location: /genera_pacchetti/update?id=" . $id);
        }

        //query con il parametro passato in get
        $pacchettoEsami = $database->select("an_pacchettiEsame", "*", "id =" . $_GET['id'])[0];

        $pacchettoEsami['id_esami'] = json_decode($pacchettoEsami['id_esami']);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Pacchetti Esami>Modifica Pacchetti',
            'serp' => '/pacchetti_esami',
            'menu' => $menu['genera_pacchetti'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Modifica Pacchetti Esami',
            'date' => array(),
            'pacchettoEsami' => $pacchettoEsami,
            'update' => 'update',
            'message' => $message,
            'content' => ""
        ];

        $message = '';

        // Utilizza la funzione render per generare l'output HTML
        $result = render('pacchetti_esami/genera_pacchetti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    /*Area Visualizzazione pacchetti esami*/
    $router->addRoute('/elenco_pacchetti', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        $query = "SELECT * FROM an_pacchettiEsame WHERE 1";
        $pacchettiEsami = $database->query($query);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Pacchetti Esami>Genera Pacchetti',
            'serp' => '/pacchetti_esami',
            'menu' => $menu['genera_pacchetti'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Pacchetti Esami',
            'date' => array(),
            'pacchettiEsami' => $pacchettiEsami,
            'message' => $message,
            'content' => ""
        ];

        $message = '';

        // Utilizza la funzione render per generare l'output HTML
        $result = render('pacchetti_esami/visualizza_pacchetti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });

    /*AREA SETTINGS*/
    /*Route Facoltà*/
    $router->addRoute('/settings/facolta', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Insert
        if (isset($_POST['action']) && $_POST['action'] == 'new') {
            $data = array(
                'facolta' => $_POST['facolta'],
                'eid' => $_POST['eid']
            );
            //echo 'ciao';exit();
            $database->insert('an_facolta', $data);
            $_SESSION['message'] = "insert";
            header("Location: /settings/facolta");
        }

        //Update
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            $data = array(
                'facolta' => $_POST['facolta'],
                'eid' => $_POST['eid']
            );
            $where = "id= " . $_POST['id'];

            $database->update('an_facolta', $data, $where);
            $_SESSION['message'] = "update";
            header("Location: /settings/facolta");
        }

        $query = "SELECT id, facolta FROM an_facolta WHERE 1";
        $corsi = $database->query($query);

        //echo "<pre>";print_r($corsi); echo "</pre>"; exit();
        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Facoltà',
            'serp' => '/settings/facolta',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Facoltà',
            'date' => array(),
            'corsi' => $corsi,
            'message' => $message,
            'content' => ""
        ];

        $message = '';

        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/elenco_facolta', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    /*Route Elenco Esami*/
    $router->addRoute('/settings/esami', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        //Update
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            // Controllo se c'è un file caricato e il nome dell'esame
            if (
                !empty($_FILES['filePaniere']['name']) &&
                !empty($_FILES['filePaniere']['tmp_name']) &&
                $_FILES['filePaniere']['error'] === UPLOAD_ERR_OK &&
                !empty($_POST['nome'])
            ) {
                $oldPaniere = "SELECT paniere FROM an_esami WHERE id= " . $_POST['id'];
                $oldPaniere = $database->query($oldPaniere);
                $oldPaniere = $oldPaniere[0];
                if (!empty($oldPaniere)) {
                    $oldPanierePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim(parse_url($oldPaniere['paniere'], PHP_URL_PATH), '/');
                    unlink($oldPanierePath);
                }
                $nomeEsame = trim($_POST['nome']);
                $nomeEsameSanitizzato = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomeEsame);

                $baseDir = __DIR__ . '/../uploads/paniere';
                $targetDir = $baseDir . '/' . $nomeEsameSanitizzato;

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $originalName = $_FILES['filePaniere']['name'];
                $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', str_replace(' ', '-', $originalName));
                $filePath = $targetDir . '/' . $sanitizedFileName;

                // Se il file non esiste già
                if (move_uploaded_file($_FILES['filePaniere']['tmp_name'], $filePath)) {

                    // ✅ Ora il file esiste → possiamo usare realpath()
                    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
                    $realFilePath = str_replace('\\', '/', realpath($filePath));
                    $webRelativePath = str_replace($documentRoot, '', $realFilePath);
                    $protocol = "https";
                    $host = $_SERVER['HTTP_HOST'];
                    $webFullPath = $protocol . '://' . $host . $webRelativePath;
                } else {
                    //echo 'Errore nel salvataggio del file.';
                }
            }


            $data = array(
                'pid' => $_POST['percorso'],
                'codice' => $_POST['codice'],
                'nome' => $_POST['nome'],
                'cfu' => $_POST['cfu'],
                'anno' => $_POST['anno'],
            );
            if (isset($webFullPath) && $webFullPath !== "") {
                $data['paniere'] = $webFullPath;
            }

            $where = "id= " . $_POST['id'];

            $database->update('an_esami', $data, $where);
            $_SESSION['message'] = "update";
            header("Location: /settings/esami");
        }

        //Insert
        if (isset($_POST['action']) && $_POST['action'] == 'new') {
            // Controllo se c'è un file caricato e il nome dell'esame
            if (
                isset($_FILES['filePaniere']) &&
                $_FILES['filePaniere']['error'] === UPLOAD_ERR_OK &&
                !empty($_POST['nome'])
            ) {
                $nomeEsame = trim($_POST['nome']);
                $nomeEsameSanitizzato = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomeEsame);

                $baseDir = __DIR__ . '/../uploads/paniere';
                $targetDir = $baseDir . '/' . $nomeEsameSanitizzato;

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $originalName = $_FILES['filePaniere']['name'];
                $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', str_replace(' ', '-', $originalName));
                $filePath = $targetDir . '/' . $sanitizedFileName;

                // Se il file non esiste già
                if (!file_exists($filePath)) {
                    if (rename($_FILES['filePaniere']['tmp_name'], $filePath)) {

                        // ✅ Ora il file esiste → possiamo usare realpath()
                        $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
                        $realFilePath = str_replace('\\', '/', realpath($filePath));
                        $webRelativePath = str_replace($documentRoot, '', $realFilePath);
                        $protocol = 'https'; // Usa 'https' come protocollo predefinito
                        $host = $_SERVER['HTTP_HOST'];
                        $webFullPath = $protocol . '://' . $host . $webRelativePath;
                    } else {
                        //echo 'Errore nel salvataggio del file.';
                    }
                } else {
                    // echo 'File già esistente: non sovrascritto.';
                }
            }

            $data = array(
                'pid' => $_POST['percorso'],
                'codice' => $_POST['codice'],
                'nome' => $_POST['nome'],
                'cfu' => $_POST['cfu'],
                'anno' => $_POST['anno'],
                'paniere' => $webFullPath ?? NULL
            );

            $database->insert('an_esami', $data);
            $_SESSION['message'] = "insert";

            header('location:/settings/esami');

        }

        //Insert CSV
        if (isset($_POST['action']) && $_POST['action'] === 'newfile_esame') {
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $saltati = []; // Inizializza $saltati per averlo sempre definito
                $fileTmpPath = $_FILES['file']['tmp_name'];
                $fileName = $_FILES['file']['name'];
                $fileType = $_FILES['file']['type'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if ($fileExt === 'csv' && in_array($fileType, ['text/csv', 'application/csv', 'application/vnd.ms-excel', 'text/plain'])) {
                    // Processa il file direttamente dal percorso temporaneo
                    if (($handle = fopen($fileTmpPath, "r")) !== false) {
                        $riga = 0;
                        // $saltati è già inizializzato
                        $inseriti_count = 0;
                        $falliti_count = 0;

                        while (($data_csv = fgetcsv($handle, 1000, ";")) !== false) {
                            $riga++;

                            // Salta intestazione
                            if ($riga === 1 && strtolower(trim($data_csv[0])) === 'pid') {
                                continue;
                            }
                            if (count($data_csv) < 6) { // Assicurati che ci siano abbastanza colonne
                                $saltati[] = "Riga " . $riga . ": numero di colonne insufficiente.";
                                continue;
                            }

                            // Conversione accenti
                            foreach ($data_csv as &$value) {
                                $value = trim(mb_convert_encoding($value, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'));
                            }
                            unset($value); // Rompi riferimento all'ultimo elemento

                            $row = [
                                //'id' => $data_csv[0],
                                'pid' => $data_csv[0],
                                'codice' => $data_csv[1],
                                'nome' => $data_csv[2],
                                'cfu' => $data_csv[3],
                                'anno' => $data_csv[4],
                                'paniere' => isset($data_csv[5]) && trim($data_csv[5]) !== '' ? $data_csv[5] : NULL
                            ];

                            // ✅ Controllo duplicato su nome
                            $esiste = $database->select('an_esami', 'id', "nome = '" . $database->escapeString($row['nome']) . "'");
                            if (!empty($esiste)) {
                                $saltati[] = $row['nome'] . " (duplicato)";
                                continue;
                            }

                            try {
                                $database->insert('an_esami', $row);
                                $inseriti_count++;
                            } catch (Exception $e) {
                                $falliti_count++;
                                $saltati[] = $row['nome'] . " (errore DB: " . $e->getMessage() . ")";
                            }
                        }
                        fclose($handle);

                        if ($inseriti_count > 0 && $falliti_count == 0) {
                            $_SESSION['message'] = "CSV_insert";
                        } elseif ($inseriti_count > 0 && $falliti_count > 0) {
                            $_SESSION['message'] = "CSV_partial_insert";
                        } elseif ($inseriti_count == 0 && $falliti_count > 0) {
                            $_SESSION['message'] = "CSV_notInsert_all_failed";
                        } elseif ($inseriti_count == 0 && $falliti_count == 0) {
                            if (!empty($saltati)) {
                                // Se ci sono solo righe saltate (es. tutti duplicati)
                                $_SESSION['message'] = "CSV_no_new_data_duplicates_or_errors";
                            } elseif ($riga <= 1 && empty($saltati)) { // File vuoto o solo header
                                $_SESSION['message'] = "CSV_empty_or_header_only";
                            } elseif ($riga > 1 && empty($saltati)) { // Righe processate ma nessuna azione (es. tutte saltate da filtri non di errore)
                                $_SESSION['message'] = "CSV_no_new_data";
                            }
                        }

                    } else {
                        $_SESSION['message'] = "CSV_fopen_failed"; // Errore apertura file
                    }
                } else {
                    $_SESSION['message'] = "CSV_invalid_extension"; // Estensione non CSV
                }
            } else {
                // Gestisci errori di upload file
                $saltati = []; // Assicura che $saltati sia definito
                if (isset($_FILES['file']['error'])) {
                    switch ($_FILES['file']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $_SESSION['message'] = "CSV_file_too_large";
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $_SESSION['message'] = "CSV_upload_partial";
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $_SESSION['message'] = "CSV_no_file_sent";
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $_SESSION['message'] = "CSV_no_tmp_dir";
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $_SESSION['message'] = "CSV_cant_write";
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $_SESSION['message'] = "CSV_php_extension_error";
                            break;
                        default:
                            $_SESSION['message'] = "CSV_upload_unknown_error";
                    }
                } else {
                    $_SESSION['message'] = "CSV_no_file_uploaded"; // Nessun file inviato
                }
            }
        }

        if (isset($_POST['cercaFacolta']) && !empty($_POST['cercaFacolta']) && $_POST['cercaFacolta'] != '#') {
            $query = "SELECT
                        e.id,
                        e.codice,
                        e.nome,
                        p.percorso,
                        e.cfu,
                        e.anno,
                        e.paniere
                    FROM
                        an_esami AS e
                    JOIN
                        an_percorsi AS p ON e.pid = p.id
                    WHERE
                        e.pid= " . $_POST['cercaFacolta'];
            $corsi = $database->query($query);
        } else {
            $corsi = [];
        }

        //prende il nome del percorso con l'id, cosi che la query verrà fatta in base al pid nella tabella esami
        $query = "SELECT id, percorso FROM an_percorsi WHERE 1";
        $percorso = $database->query($query);


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Esami',
            'serp' => '/settings/esami',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Esami',
            'date' => array(),
            'corsi' => $corsi ?? "",
            'corsi_saltati' => $saltati ?? [],
            'percorso' => $percorso,
            'message' => $message,
            'content' => "Questa è la tua dashboard"
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/elenco_esami', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    /*Route Elenco Percorsi*/
    $router->addRoute('/settings/percorsi', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        //Variabili per abilitare gli alert di avvenuto inserimento e avvenuta modifica
        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        //Delete
        if (isset($_GET['action']) && $_GET['action'] == 'delete') {
            $data = array(
                'visibilita' => 0
            );
            $where = "id= " . $_GET['id'];
            $database->update('an_percorsi', $data, $where);
            $_SESSION['message'] = "delete";
            header("Location: /settings/percorsi");
        }

        //Insert
        if (isset($_POST['action']) && $_POST['action'] == 'new') {
            $data = array(
                'fid' => $_POST['facolta'],
                'percorso' => $_POST['percorso']
            );

            $database->insert('an_percorsi', $data);
            $_SESSION['message'] = "insert";
            header("Location: /settings/percorsi");

        }

        //Update
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            //echo'<pre>';print_r($_POST);echo '</pre>';exit();
            $data = array(
                'fid' => $_POST['facolta'],
                'percorso' => $_POST['percorso']
            );

            $where = "id= " . $_POST['id'];

            $database->update('an_percorsi', $data, $where);
            $_SESSION['message'] = "update";
            header("Location: /settings/percorsi");
        }

        //Download PDF Esami Percorso
        if (isset($_POST['PercorsoEsame']) && !empty($_POST['PercorsoEsame'])) {
            require_once('./fpdf/fpdf.php');

            $id = intval($_POST['PercorsoEsame']);
            $query = "SELECT * FROM `an_esami` WHERE pid = " . $id;
            $esami = $database->query($query);

            $pdf = new FPDF('L', 'mm', 'A4'); // Landscape
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);

            // Titolo
            $pdf->Cell(0, 10, 'Elenco Esami', 0, 1, 'C');
            $pdf->Ln(10);

            // Dimensioni celle
            $width_nome = 150;
            $width_cfu = 30;
            $width_anno = 40;
            $height = 10;

            // Calcolo larghezza tabella
            $table_width = $width_nome + $width_cfu + $width_anno;

            // Larghezza pagina A4 Landscape: 297mm
            $page_width = 297;
            $margin_left = ($page_width - $table_width) / 2;

            // Intestazione tabella
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetX($margin_left);
            $pdf->Cell($width_nome, $height, 'Nome', 1, 0, 'C');
            $pdf->Cell($width_cfu, $height, 'CFU', 1, 0, 'C');
            $pdf->Cell($width_anno, $height, 'Anno', 1, 1, 'C');

            // Corpo tabella
            $pdf->SetFont('Arial', '', 12);

            foreach ($esami as $v) {
                $anno = ($v['anno'] == 's') ? "A Scelta" : $v['anno'] . "° Anno";

                // Iconv per convertire UTF-8 -> ISO-8859-1 in modo sicuro
                $nome = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $v['nome']);
                $cfu = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $v['cfu']);
                $anno = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $anno);

                // Controllo overflow del Nome, taglio se troppo lungo
                if ($pdf->GetStringWidth($nome) > $width_nome - 2) {
                    while ($pdf->GetStringWidth($nome . '...') > $width_nome - 2) {
                        $nome = substr($nome, 0, -1); // Taglia un carattere
                    }
                    $nome .= '...';
                }

                // Sposta la X per centrare la tabella
                $pdf->SetX($margin_left);

                // Stampa delle celle
                $pdf->Cell($width_nome, $height, $nome, 1, 0, 'L');
                $pdf->Cell($width_cfu, $height, $cfu, 1, 0, 'C');
                $pdf->Cell($width_anno, $height, $anno, 1, 1, 'C');
            }

            $database->disconnect();

            // Output PDF per download
            $pdf->Output('D', 'elenco_esami.pdf');
            exit;
        }

        $query = "SELECT 
                        an_percorsi.id,
                        an_facolta.facolta,
                        an_percorsi.percorso

                    FROM 
                        an_percorsi
                    JOIN 
                        an_facolta ON an_percorsi.fid = an_facolta.id
                    WHERE 
                        visibilita = 1";
        $facolta = $database->query($query);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Percorsi',
            'serp' => '/settings/percorsi',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Percorsi',
            'date' => array(),
            'facolta' => $facolta,
            'message' => $message,
            'content' => "Questa è la tua dashboard"
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/elenco_percorsi', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    /*Route Elenco Esami*/
    $router->addRoute('/settings/esami_master', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //prende il nome del percorso con l'id, cosi che la query verrà fatta in base al pid nella tabella esami
        $query = "SELECT id, nome FROM an_master WHERE 1";
        $master = $database->query($query);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Esami Master',
            'serp' => '/settings/esami_master',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Master',
            'master' => $master,
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/Master', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    $router->addRoute('/settings/esami_master/{id}', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";

        //Insert
        if (isset($_POST['action']) && $_POST['action'] == 'new') {
            $controllo = "SELECT nome FROM an_esami WHERE codice= '" . $_POST['codice'] . "'";
            $controllo = $database->query($controllo);
            if (empty($controllo)) {
                $data = array(
                    'pid' => $id['id'],
                    'codice' => $_POST['codice'],
                    'nome' => $_POST['nome'],
                    'cfu' => $_POST['cfu'],
                    'anno' => 'M',
                    'paniere' => NULL
                );

                $database->insert('an_esami', $data);

                $message = 'insert';
            } else {
                $saltati[] = $_POST['nome'];
            }
        }

        //Update
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            $data = array(
                'pid' => $id['id'],
                'codice' => $_POST['codice'],
                'nome' => $_POST['nome'],
                'cfu' => $_POST['cfu'],
                'anno' => 'M',
                'paniere' => NULL
            );

            $where = "id= " . $_POST['id'];

            $database->update('an_esami', $data, $where);

            $message = 'update';
        }

        $query = "SELECT 
                    e.id, 
                    e.codice, 
                    e.nome, 
                    p.nome as nome_master,
                        e.cfu 
                    FROM an_esami AS e 
                    JOIN an_master AS p ON e.pid = p.id 
                    WHERE p.id=" . $id['id'] . " and e.anno = 'M'";
        //echo $query; exit();
        $corsi = $database->query($query);

        //prende il nome del percorso con l'id, cosi che la query verrà fatta in base al pid nella tabella esami
        $query = "SELECT nome FROM an_master WHERE id = " . intval($id['id']);
        $master_tip = $database->query($query);

        $query = "SELECT id, nome FROM an_master WHERE 1";
        $master = $database->query($query);


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Esami Master',
            'serp' => '/settings/esami_master',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => $master[0]['nome'] ?? 'Elenco Esami Master',
            'master' => $master,
            'id_master' => $id['id'],
            'corsi' => $corsi ?? [],
            'message' => $message,
            'content' => "Questa è la tua dashboard"
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/elencoEsamiMaster', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per l'elenco dei master
    $router->addRoute('/settings/master', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        //Variabili per abilitare gli alert di avvenuto inserimento e avvenuta modifica
        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Insert
        if (isset($_POST['action']) && $_POST['action'] == 'new') {
            $jsonEsami = json_encode($_POST['esami'], JSON_UNESCAPED_UNICODE);

            $data = array(
                'fid' => $_POST['facolta'],
                'nome' => $_POST['master'],
                'visibilita' => '1'
            );
            $database->insert('an_master', $data);
            $_SESSION['message'] = "insert";
            header("Location: /settings/master");

        }

        //Delete
        if (isset($_GET['action']) && $_GET['action'] == 'delete') {
            $data = array(
                'visibilita' => 0
            );
            $where = "id= " . $_GET['id'];
            $database->update('an_master', $data, $where);
            $_SESSION['message'] = "delete";
            header("Location: /settings/master");
        }

        //Update
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            //echo'<pre>';print_r($_POST);echo '</pre>';exit();
            $data = array(
                'fid' => $_POST['facolta'],
                'nome' => $_POST['master']
            );
            $where = "id= " . $_POST['id'];

            $database->update('an_master', $data, $where);
            $_SESSION['message'] = "update";
            header("Location: /settings/master");
        }


        // Download PDF Esami Master
        if (isset($_POST['PercorsoEsame']) && $_POST['PercorsoEsame'] !== '') {
            require_once('./fpdf/fpdf.php');

            $id = (int) $_POST['PercorsoEsame'];
            $query = "SELECT codice, nome, cfu FROM an_esamiMaster WHERE mid = $id";
            $esami = $database->query($query);

            $pdf = new FPDF('L', 'mm', 'A4'); // Landscape
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);

            // Titolo
            $pdf->Cell(0, 10, 'Elenco Esami Master', 0, 1, 'C');
            $pdf->Ln(6);

            // Dimensioni celle
            $wNome = 150;
            $wCfu = 30;
            $wCodice = 40;
            $h = 10;

            // Calcolo larghezza tabella e centratura
            $tableWidth = $wNome + $wCfu + $wCodice;
            $pageWidth = $pdf->GetPageWidth();
            $x = max(10, ($pageWidth - $tableWidth) / 2); // margine sinistro di sicurezza

            // Intestazione tabella
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetX($x);
            $pdf->Cell($wNome, $h, 'Nome', 1, 0, 'C');
            $pdf->Cell($wCfu, $h, 'CFU', 1, 0, 'C');
            $pdf->Cell($wCodice, $h, 'Codice', 1, 1, 'C'); // ln=1 chiude la riga

            // Corpo tabella
            $pdf->SetFont('Arial', '', 12);

            foreach ($esami as $row) {
                // Sicurezza: cast a stringa e converti solo se non null
                $nome = isset($row['nome']) ? (string) $row['nome'] : '';
                $cfu = isset($row['cfu']) ? (string) $row['cfu'] : '';
                $codice = isset($row['codice']) ? (string) $row['codice'] : '';

                // Converti UTF-8 -> ISO-8859-1 per FPDF
                $nome = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $nome);
                $cfu = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $cfu);
                $codice = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $codice);

                // Troncamento del nome se troppo lungo
                if ($pdf->GetStringWidth($nome) > $wNome - 2) {
                    while ($pdf->GetStringWidth($nome . '...') > $wNome - 2) {
                        // usa mb_substr per sicurezza con charset
                        $nome = mb_substr($nome, 0, mb_strlen($nome) - 1);
                    }
                    $nome .= '...';
                }

                // Riga
                $pdf->SetX($x);
                $pdf->Cell($wNome, $h, $nome, 1, 0, 'L');
                $pdf->Cell($wCfu, $h, $cfu, 1, 0, 'C');
                $pdf->Cell($wCodice, $h, $codice, 1, 1, 'C'); // chiude riga
            }

            $database->disconnect();

            // Evita "Some data has already been output"
            if (ob_get_length()) {
                ob_end_clean();
            }

            // Download
            $pdf->Output('D', 'elenco_esamiMaster.pdf');
            exit;
        }


        $query = "SELECT 
                        an_master.id,
                        an_facolta.facolta,
                        an_master.nome

                    FROM 
                        an_master
                    JOIN 
                        an_facolta ON an_master.fid = an_facolta.id
                    WHERE 
                        visibilita = 1";
        $master = $database->query($query);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Master',
            'serp' => '/settings/master',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Master',
            'date' => array(),
            'master' => $master,
            'message' => $message,
            'content' => "Questa è la tua dashboard"
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/elenco_master', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    $router->addRoute('/settings/enti', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Insert && Update
        if (isset($_POST) && !empty($_POST)) {
            // Recupera l'azione dal form (insert o update)
            $action = $_POST['action'];
            if ($action === 'new') {

                if (!empty($_POST['nome'])) {
                    $nome = substr(trim($_POST['nome']), 0, 50);
                    $NomeEnte = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nome);

                    // SOLO PER LA VISUALIZZAZIONE
                    $fileInputs = [
                        'listino' => "Listino",
                        'convenzione' => "Convenzione"
                    ];

                    $savedFiles = [];

                    foreach ($fileInputs as $inputName => $label) {
                        $percorso = __DIR__ . '/../uploads/enti';
                        $result = salvaFile($inputName, $NomeEnte, $percorso);
                        if ($result['success']) {
                            $savedFiles[$inputName] = $result['url'];  // Salvi l'URL univoco
                            //$message .= "✅ File inserito: {$label} - {$result['file']}<br>";
                        } else {
                            $savedFiles[$inputName] = '';  // fallback se qualcosa fallisce
                            //$message .= "❌ Errore per {$label}: {$result['error']}<br>";
                        }
                    }


                    $data_ente = [
                        'nome' => $_POST['nome'],
                        'listino' => $savedFiles['listino'],
                        'convenzione' => $savedFiles['convenzione'],
                        'data_scadenza' => $_POST['data_scadenza'],
                    ];

                    $database->insert("an_enti", $data_ente);

                }

                $_SESSION['message'] = "insert";
                header("Location: /settings/enti");
            } elseif ($action === 'update') {
                $ente_id = $_POST['id'] ?? null;
                if (!$ente_id) {
                    throw new Exception("ID ente non fornito per l'aggiornamento.");
                }

                // 1. SELECT per recuperare i dati esistenti
                $ente = $database->select('an_enti', '*', "id = " . $ente_id);

                if (!$ente || count($ente) === 0) {
                    throw new Exception("Discente non trovato.");
                }

                $ente = $ente[0]; // Prendi il primo (e unico) record

                if (!empty($_POST['nome'])) {
                    $nome = substr(trim($_POST['nome']), 0, 50);
                    $NomeEnte = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nome);

                    $fileInputs = [
                        'listino' => "Listino",
                        'convenzione' => "Convenzione"
                    ];

                    $savedFiles = [];

                    foreach ($fileInputs as $inputName => $label) {
                        // Controllo: l'utente ha caricato un nuovo file per questo campo?
                        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                            // 1. Se sì, elimina il file vecchio
                            if (!empty($ente[$inputName])) {
                                $fileUrl = $ente[$inputName];
                                $urlPath = parse_url($fileUrl, PHP_URL_PATH);
                                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $urlPath;

                                if (file_exists($fullPath)) {
                                    //print_r($fullPath); exit();
                                    unlink($fullPath);
                                }
                            }

                            // 2. Carica il nuovo file
                            $percorso = __DIR__ . '/../uploads/enti';
                            $result = salvaFile($inputName, $NomeEnte, $percorso);
                            if ($result['success']) {
                                $savedFiles[$inputName] = $result['url'];
                                $message .= "aggiornamento riuscito";
                                //print_r($savedFiles);exit();
                            } else {
                                // In caso di errore nel nuovo upload puoi decidere di mantenere il file vecchio o fermarti
                                $savedFiles[$inputName] = $ente[$inputName];
                                $message .= "errore";
                            }
                        } else {
                            // 3. Nessun nuovo file caricato, mantieni quello vecchio
                            $savedFiles[$inputName] = $ente[$inputName];
                        }
                    }

                    $data_ente = [
                        'nome' => $_POST['nome'],
                        'listino' => $savedFiles['listino'],
                        'convenzione' => $savedFiles['convenzione'],
                        'data_scadenza' => $_POST['data_scadenza'],
                    ];


                    // 4. UPDATE
                    $database->update('an_enti', $data_ente, "id = " . $ente_id);
                }
                $_SESSION['message'] = "update";
                header("Location: /settings/enti");
            }
        }

        $query = "SELECT 
                        id,
                        nome, 
                        data_scadenza
                    FROM 
                        an_enti
                    WHERE 
                        1";

        $enti = $database->query($query);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Gestione Enti',
            'serp' => '/settings/enti',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Enti',
            'date' => array(),
            'enti' => $enti,
            'message' => $message,
            'content' => ""
        ];

        $message = "";

        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/enti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Tutor/Professori
    $router->addRoute('/settings/gestione', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        //Variabili per abilitare gli alert di avvenuto inserimento e avvenuta modifica
        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Insert
        if (isset($_POST['action']) && $_POST['action'] == 'new') {
            // Controllo se c'è un file caricato e il nome dell'esame
            if (
                isset($_FILES['filecv']) &&
                $_FILES['filecv']['error'] === UPLOAD_ERR_OK &&
                !empty($_POST['nome'])
            ) {
                $nometutor = $_POST['nome'] . '_' . $_POST['cognome'];
                $nomeTutorSanitizzato = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nometutor);

                $baseDir = __DIR__ . '/../uploads/cv_tutor';
                $targetDir = $baseDir . '/' . $nomeTutorSanitizzato;

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $originalName = $_FILES['filecv']['name'];
                $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', str_replace(' ', '-', $originalName));
                $filePath = $targetDir . '/' . $sanitizedFileName;

                // Se il file non esiste già
                if (!file_exists($filePath)) {
                    if (rename($_FILES['filecv']['tmp_name'], $filePath)) {

                        // ✅ Ora il file esiste → possiamo usare realpath()
                        $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
                        $realFilePath = str_replace('\\', '/', realpath($filePath));
                        $webRelativePath = str_replace($documentRoot, '', $realFilePath);
                        $protocol = 'https'; // Usa 'https' come protocollo predefinito
                        $host = $_SERVER['HTTP_HOST'];
                        $webFullPath = $protocol . '://' . $host . $webRelativePath;

                        /*echo '<pre>';
                        echo 'DOCUMENT_ROOT: ' . $documentRoot . "\n";
                        echo 'REAL FILE PATH: ' . $realFilePath . "\n";
                        echo 'WEB RELATIVE: ' . $webRelativePath . "\n";
                        echo 'WEB FULL PATH: ' . $webFullPath . "\n";
                        echo '</pre>';*/

                    } else {
                        //echo 'Errore nel salvataggio del file.';
                    }
                } else {
                    //echo 'File già esistente: non sovrascritto.';
                }
            }

            $data = array(
                'nome' => $_POST['nome'],
                'cognome' => $_POST['cognome'],
                'cf' => $_POST['cf'],
                'email' => $_POST['email'],
                'telefono' => $_POST['telefono'],
                'cv' => $webFullPath
            );

            $database->insert('an_tutor', $data);

            $id = $database->lastId();

            $id_esame = $_POST['id_esami'];
            if (!empty($id_esame) && is_array($id_esame)) {
                foreach ($id_esame as $esame) {
                    $data = array(
                        'tid' => $id,
                        'eid' => intval($esame)
                    );

                    $database->insert('esami_tutor', $data);
                }
            }
            $_SESSION['message'] = "insert";
            header("Location: /settings/gestione");
        }

        //Update
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            $id = $_POST['id'];
            $webFullPath = "";

            // Controllo se c'è un file caricato e il nome dell'esame
            if (
                isset($_FILES['filecv']) &&
                $_FILES['filecv']['error'] === UPLOAD_ERR_OK
            ) {

                $oldCv = "SELECT cv FROM an_tutor WHERE id= " . $id;
                $oldCv = $database->query($oldCv);
                $oldCv = $oldCv[0];
                if (!empty($oldCv)) {
                    $oldCvPath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim(parse_url($oldCv['cv'], PHP_URL_PATH), '/');
                    unlink($oldCvPath);
                }
                $nometutor = $_POST['nome'] . '_' . $_POST['cognome'];
                $nomeTutorSanitizzato = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nometutor);

                $baseDir = __DIR__ . '/../uploads/cv_tutor';
                $targetDir = $baseDir . '/' . $nomeTutorSanitizzato;

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $originalName = $_FILES['filecv']['name'];
                $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', str_replace(' ', '-', $originalName));
                $filePath = $targetDir . '/' . $sanitizedFileName;

                // Se il file non esiste già
                if (move_uploaded_file($_FILES['filecv']['tmp_name'], $filePath)) {

                    // ✅ Ora il file esiste → possiamo usare realpath()
                    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
                    $realFilePath = str_replace('\\', '/', realpath($filePath));
                    $webRelativePath = str_replace($documentRoot, '', $realFilePath);
                    $protocol = "https";
                    $host = $_SERVER['HTTP_HOST'];
                    $webFullPath = $protocol . '://' . $host . $webRelativePath;

                    /*echo '<pre>';
                    echo 'DOCUMENT_ROOT: ' . $documentRoot . "\n";
                    echo 'REAL FILE PATH: ' . $realFilePath . "\n";
                    echo 'WEB RELATIVE: ' . $webRelativePath . "\n";
                    echo 'WEB FULL PATH: ' . $webFullPath . "\n";
                    echo '</pre>';*/

                } else {
                    //echo 'Errore nel salvataggio del file.';
                }

            }

            // 1. Aggiorna i dati del tutor
            $data = array(
                'nome' => $_POST['nome'],
                'cognome' => $_POST['cognome'],
                'cf' => $_POST['cf'],
                'email' => $_POST['email'],
                'telefono' => $_POST['telefono'],
            );

            // Aggiungi il campo 'cv' all'array $data solo se $webFullPath è stato impostato (non è più null),
            if ($webFullPath !== "") {
                $data['cv'] = $webFullPath;
            }

            $database->update('an_tutor', $data, "id = $id");

            // 2. Elimina vecchie associazioni esami
            $database->query("DELETE FROM `esami_tutor` WHERE `tid` = $id");


            // 3. Inserisci nuove associazioni (se presenti)
            $id_esame = $_POST['id_esami'];
            if (!empty($id_esame) && is_array($id_esame)) {
                foreach ($id_esame as $esame) {
                    $data = array(
                        'tid' => $id,
                        'eid' => intval($esame)
                    );
                    $database->insert('esami_tutor', $data);
                }
            }
            $_SESSION['message'] = "update";
            header("Location: /settings/gestione");
        }

        $query = "SELECT 
                        id, 
                        nome, 
                        cognome, 
                        email,
                        telefono,
                        cv
                    FROM 
                        an_tutor
                    WHERE 
                        1";

        $tutor = $database->query($query);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Gestione Tutor/Professori',
            'serp' => '/settings/gestione',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Tutor/Professori',
            'date' => array(),
            'tutor' => $tutor,
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/gestione', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per l'insert degli utenti
    $router->addRoute('/settings/utenti', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Insert
        if (isset($_POST) && !empty($_POST)) {
            if ($_POST['action'] == 'new') {
                $pwd = generaPasswordComplessa();
                $pwd = password_hash($pwd, PASSWORD_DEFAULT);
                $data = [
                    'user' => $_POST['email'],
                    'pwd' => $pwd,
                    'nome' => $_POST['nome'],
                    'cognome' => $_POST['cognome'],
                    'email' => $_POST['email'],
                    'ruolo' => $_POST['ruolo'],
                    'status' => 'attivo'
                ];

                //echo'<pre>';print_r($data);echo'</pre>';
                $database->insert('users', $data);

                $idUser = $database->lastId();

                $fileNames = "";
                $invii = "";
                $oggetto = "Registrazione Università GTC";
                $destinatario = $_POST['email'];
                $messaggio = "<div style='
                                background-color: #fff;
                                padding: 20px;
                                border-radius: 8px;
                                box-shadow: 5px 5px 20px 5px rgba(0, 0, 0, 0.25);
                                width: 80%;
                                max-width: 400px;
                                margin: auto;
                                box-sizing: border-box;
                                text-align: center;
                                font-family: Arial, sans-serif;
                                color: #333;
                            '>
                            <img src='{$dominio}/images/universitygtc.png' alt='' style='display: block; margin: 0 auto; width:108; height:100;'><br><br>
                            <h2 style='margin: 20px 0 10px;'>Ecco a te le credenziali di accesso:</h2>
                            <p style='font-size: 1.2em; font-weight: bold; color: #333;'>Username: " . $_POST['email'] . "</p>
                            <p style='font-size: 1.2em; font-weight: bold; color: #333;'>Password: " . $pwd . "</p>
                            <a href='https://gestione.universitygtc.it/login.php' style='margin-top: 20px; color: #555;'>https://gestione.universitygtc.it/login.php.</a>
                            <p style='margin-top: 20px; color: #555;'>Accedi al link per accedere.</p>
                        </div>";

                //InviaEmail($oggetto, $destinatario, $messaggio, $fileNames, $invii);

                $dataCollab = [
                    'id_user' => $idUser,
                    'telefono' => $_POST['telefono'],
                    'referente_via' => $_POST['referente_via'],
                    'note' => $_POST['note']
                ];

                $database->insert('an_collaboratori', $dataCollab);

                $_SESSION['message'] = "insert";
                header("Location: /settings/utenti");
            } else {
                //echo'<pre>';print_r($_POST);echo'</pre>';exit();
                $data = [
                    'nome' => $_POST['nome'],
                    'cognome' => $_POST['cognome'],
                    'email' => $_POST['email'],
                    'ruolo' => $_POST['ruolo'],
                ];

                $where = "id = " . $_POST['id_user'];
                $database->update('users', $data, $where);


                $dataCollab = [
                    'telefono' => $_POST['telefono'],
                    'referente_via' => $_POST['referente_via'],
                    'note' => $_POST['note']
                ];
                $where = "id = " . $_POST['id'];
                $database->update('an_collaboratori', $dataCollab, $where);

                $_SESSION['message'] = "update";
                header("Location: /settings/utenti");
            }
        }

        $utenti = "SELECT 
                        col.*, 
                        u.user,
                        u.pwd,
                        u.nome,
                        u.cognome,
                        u.email,
                        u.ruolo,
                        r.ruolo AS ruolo
                    FROM 
                        an_collaboratori AS col
                    JOIN 
                        users AS u ON col.id_user = u.id
                    JOIN 
                        rules AS r ON u.ruolo = r.id
                    WHERE 1";
        $utenti = $database->query($utenti);
        //echo'<pre>';print_r($utenti);echo'</pre>';exit();
        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Gestione Utenti',
            'serp' => '/discenti/gestione/new',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'utenti' => $utenti,
            'h1' => '',
            'h2' => 'Gestione Utenti',
            'date' => array(),
            'message' => $message,
            'content' => ""
        ];
        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/elenco_utenti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per l'insert delle certificazioni
    $router->addRoute('/settings/certificazioni', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        $query = "SELECT 
                    cer.*,
                    enti.nome AS nome_ente
                FROM 
                    an_certificazioni AS cer
                JOIN 
                    an_enti AS enti ON cer.id_ente = enti.id";

        $certificazioni = $database->query($query);

        // Sanifica il campo 'costo' se presente
        foreach ($certificazioni as &$s) {
            if (isset($s['costo'])) {
                $val = str_replace(',', '.', trim($s['costo'])); // Normalizza per controllo numerico
                if (is_numeric($val)) {
                    // Se è numerico, formatta in stile italiano per output
                    $s['costo'] = number_format((float) $val, 2, ',', '');
                } else {
                    // Se non è numerico, lo annulli o gestisci l'errore
                    $s['costo'] = null;
                }
            }
        }

        /*Printo solamente la variabile costo e aggiungo € o % in base all'ente
          e cambio il nome "percentuale" o "euro" in % o €*/

        foreach ($certificazioni as &$cert) {
            if (!empty($cert['percentuale'])) {
                $cert['costo'] = $cert['percentuale'];
                $cert['tipo_ente'] = '%';
            } else {
                $cert['tipo_ente'] = '€';
            }
            unset($cert['percentuale']);
        }


        if (isset($_POST) && !empty($_POST)) {
            if ($_POST['action'] == "new") {
                if (isset($_POST['url_piattaforma']) && ($_POST['username_piattaforma']) && ($_POST['pwd_piattaforma'])) {
                    $credenziali_api = json_encode(implode('||', [
                        $_POST['url_piattaforma'],
                        $_POST['username_piattaforma'],
                        $_POST['pwd_piattaforma']
                    ]));
                }

                // Inizializza i valori puliti
                $percentuale = null;
                $costo = null;

                // Sanifica il campo 'percentuale' se presente
                if (!empty($_POST['percentuale'])) {
                    $percentuale = str_replace(['%', ','], ['', '.'], trim($_POST['percentuale']));
                    if (!is_numeric($percentuale)) {
                        // Gestione errore opzionale
                        $percentuale = null;
                    }
                }

                // Sanifica il campo 'costo' se presente
                if (!empty($_POST['costo'])) {
                    $costo = str_replace(['%', ','], ['', '.'], trim($_POST['costo']));
                    if (!is_numeric($costo)) {
                        // Gestione errore opzionale
                        $costo = null;
                    }
                }

                $data = array(
                    'id_ente' => $_POST['id_ente'],
                    'nome' => $_POST['nome'],
                    'svolgimento' => $_POST['svolgimento'],
                    'piattaforma' => $_POST['piattaforma'] ?? NULL,
                    'percentuale' => $percentuale,
                    'costo' => $costo,
                    'credenziali_api' => $credenziali_api ?? NULL,
                    'validita' => $_POST['validita'],
                    'tipo' => $_POST['tipo'],
                    'tipoPagamento' => $_POST['tipoPagamento']
                );

                $database->insert("an_certificazioni", $data);
                $_SESSION['message'] = 'insert';
                header('Location: /settings/certificazioni');
            } else {
                if (isset($_POST['url_piattaforma']) && ($_POST['username_piattaforma']) && ($_POST['pwd_piattaforma'])) {
                    $credenziali_api = json_encode(implode('||', [
                        $_POST['url_piattaforma'],
                        $_POST['username_piattaforma'],
                        $_POST['pwd_piattaforma']
                    ]));
                }
                // Inizializza i valori puliti
                $percentuale = null;
                $costo = null;

                // Sanifica il campo 'percentuale' se presente
                if (!empty($_POST['percentuale'])) {
                    $percentuale = str_replace(['%', ','], ['', '.'], trim($_POST['percentuale']));
                    if (!is_numeric($percentuale)) {
                        // Gestione errore opzionale
                        $percentuale = null;
                    }
                }

                // Sanifica il campo 'costo' se presente
                if (!empty($_POST['costo'])) {
                    $costo = str_replace(['%', ','], ['', '.'], trim($_POST['costo']));
                    if (!is_numeric($costo)) {
                        // Gestione errore opzionale
                        $costo = null;
                    }
                }

                $data = array(
                    'id_ente' => $_POST['id_ente'],
                    'nome' => $_POST['nome'],
                    'svolgimento' => $_POST['svolgimento'],
                    'piattaforma' => $_POST['piattaforma'] ?? NULL,
                    'percentuale' => $percentuale,
                    'costo' => $costo,
                    'credenziali_api' => $credenziali_api ?? NULL,
                    'validita' => $_POST['validita'],
                    'tipo' => $_POST['tipo'],
                    'tipoPagamento' => $_POST['tipoPagamento']
                );


                $where = "id = " . $_POST['id'];
                $database->update("an_certificazioni", $data, $where);
                $_SESSION['message'] = 'update';
                header('Location: /settings/certificazioni');
            }
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Settings>Gestione Certificazioni',
            'serp' => '/settings/certificazioni',
            'menu' => $menu['settings'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'certificazioni' => $certificazioni,
            'h2' => 'Gestione Certificazioni',
            'date' => array(),
            'message' => $message,
            'content' => ""
        ];
        // Utilizza la funzione render per generare l'output HTML
        $result = render('settings/elenco_certificazioni', $content);

        // Restituisci l'output HTML generato
        return $result;
    });

    /*AREA DISCENTI*/
    //Elenco Discenti
    $router->addRoute('/discenti', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/' . $idUser);
            exit();
        }

        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        $query = "SELECT
                    id,
                    nome,
                    cognome,
                    email,
                    cellulare
                FROM
                    an_discenti
                WHERE
                    1";

        $discenti = $database->query($query);

        foreach ($discenti as &$d) {
            $id = intval($d['id']);

            // Controllo esami
            $controlloesami_query = "SELECT id, fileVoti FROM discente_esami WHERE did = $id";
            $esami = $database->query($controlloesami_query);

            $d['esami'] = !empty($esami) ? "si" : "no";
            $d['fileVoti'] = !empty($esami[0]['fileVoti']) ? $esami[0]['fileVoti'] : null;

            // Controllo carriera
            $carriera_query = "SELECT id FROM carriera WHERE id_discente = $id";
            $carriera = $database->query($carriera_query);

            $d['carriera'] = !empty($carriera) ? "ok" : "ko";

            //Controllo Presenza Pagamenti
            $pagamentoQuery = "SELECT id FROM rateDiscente WHERE id_discente = $id";
            $pagamento = $database->query($pagamentoQuery);

            $d['pagamento'] = !empty($pagamento) ? "si" : "no";

            //Controllo Presenza Esami
            $queryScadenzaEsami = "SELECT id FROM scadenzaEsami WHERE id_discente = $id";
            $pagamento = $database->query($queryScadenzaEsami);
            //print_r($pagamento);exit();
            $d['EsamiFatti'] = !empty($pagamento) ? "si" : "no";

        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Gestione Discenti',
            'serp' => '/discenti',
            'menu' => $menu['discenti'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Elenco Discenti',
            'date' => array(),
            'discenti' => $discenti,
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/discenti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Reindirizzamento alle aree dei discenti in base ai permessi
    $router->addRoute('/discenti/gestione/voti', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        $Prevalutazione = "https://gestione.universitygtc.it/uploads/Prevalutazione/Prevalutazione.docx";

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }


        if (isset($_FILES['FileVoti']) && !empty($_FILES['FileVoti']['name'])) {
            $fileVoti = $database->select("discente_esami", "id", "did = '" . $_POST['corsoSelect'] . "'");
            //print_r($fileVoti);exit();
            if (empty($fileVoti)) {
                $fileInputs = [
                    'FileVoti' => "fileVoti",
                ];

                $savedFiles = [];

                foreach ($fileInputs as $inputName => $label) {
                    $percorso = __DIR__ . '/../uploads/filePrevalutazione';
                    $result = salvaFile($inputName, $_POST['corsoSelect'], $percorso);
                    if ($result['success']) {
                        $savedFiles[$inputName] = $result['url'];  // Salvi l'URL univoco
                        //echo "✅ File inserito: {$label} - {$result['file']}<br>";
                    } else {
                        $savedFiles[$inputName] = '';  // fallback se qualcosa fallisce
                        //echo "❌ Errore per {$label}: {$result['error']}<br>";
                    }
                }

                $data = array(
                    'fileVoti' => $savedFiles['FileVoti']
                );
                $database->update("discente_esami", $data, "did= " . $_POST['corsoSelect']);
                $_SESSION['message'] = "FileInserito";
                header('Location: /discenti/gestione/voti');
            } else {
                $_SESSION['message'] = 'FileGiàPresente';
                header('Location: /discenti/gestione/voti');
            }
        }

        //Prendo tutti i discenti, cosi che verrà fatto un reindirizzamento alla pagina di gestione voti del discente scelto
        $discenti = "SELECT * FROM an_discenti WHERE 1";
        $discenti = $database->query($discenti);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Gestione Voti',
            'serp' => '/discenti/gestione/voti',
            'menu' => $menu['discenti'],
            'revisione' => $revisione,
            'apps' => $apps,
            'Prevalutazione' => $Prevalutazione,
            'h1' => '',
            'h2' => 'Elenco Voti',
            'date' => array(),
            'discenti' => $discenti,
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/areaVotiAmministrazione', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Visualizza Discenti
    $router->addRoute('/discenti/{id}', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $message = "";
        $idDiscente = base64_decode($id['id']);
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_POST['inviaemail']) && !empty($_POST['inviaemail'])) {
            $invitoesistenteQuery = "SELECT * FROM emailInviataDiscenti WHERE id_discente = " . $idDiscente;
            $invitoesistente = $database->query($invitoesistenteQuery);

            if (!empty($invitoesistente)) {
                $invito = $invitoesistente[0]; // Prima riga del risultato
                $data_record = new DateTime($invito['data']);
                $now = new DateTime();
                $now->modify('-48 hours');

                if ($data_record < $now) {
                    // È scaduto -> Invia nuovo invito
                    $emailQuery = "SELECT * FROM an_discenti WHERE id = " . $idDiscente;
                    $email = $database->query($emailQuery);
                    $email = $email[0];


                    $pwd = generaPasswordComplessa(8);
                    $fileNames = "";
                    $invii = "";
                    $oggetto = "Registrazione Università GTC";
                    $destinatario = $email['email'];
                    $messaggio = "<div style='
                                    background-color: #fff;
                                    padding: 20px;
                                    border-radius: 8px;
                                    box-shadow: 5px 5px 20px 5px rgba(0, 0, 0, 0.25);
                                    width: 80%;
                                    max-width: 400px;
                                    margin: auto;
                                    box-sizing: border-box;
                                    text-align: center;
                                    font-family: Arial, sans-serif;
                                    color: #333;
                                '>
                                <img src='{$dominio}/images/universitygtc.png' alt='' style='display: block; margin: 0 auto; width:108; height:100;'><br><br>
                                <h2 style='margin: 20px 0 10px;'>Ecco a te le credenziali di accesso:</h2>
                                <p style='font-size: 1.2em; font-weight: bold; color: #333;'>Username: " . $email['email'] . "</p>
                                <p style='font-size: 1.2em; font-weight: bold; color: #333;'>Password: " . $pwd . "</p>
                                <a href='https://gestione.universitygtc.it/login.php' style='margin-top: 20px; color: #555;'>https://gestione.universitygtc.it/login.php.</a>
                                <p style='margin-top: 20px; color: #555;'>Accedi al link per accedere.</p>
                            </div>";
                    try {
                        InviaEmail($oggetto, $destinatario, $messaggio, $fileNames, $invii);

                        $datidiscenteQuery = "SELECT * FROM an_discenti WHERE id = " . $idDiscente;
                        $datidiscente = $database->query($datidiscenteQuery);
                        $data = array(
                            'pwd' => password_hash($pwd, PASSWORD_DEFAULT),
                        );
                        $where = "user= '" . $email['email'] . "'";
                        $database->update('users', $data, $where);

                        $datadiscente = array(
                            'id_discente' => $idDiscente,
                            'email' => $email['email']
                        );

                        $database->insert('emailInviataDiscenti', $datadiscente);


                        $_SESSION['message'] = "EmailOk";
                        header('Location: /discenti/' . $id['id']);
                        exit();
                    } catch (Exception $e) {
                        $_SESSION['message'] = "EmailKo";
                        header('Location: /discenti/' . $id['id']);
                        exit();
                    }


                } else {
                    // Non è scaduto -> Metto EmailEsistente
                    $_SESSION['message'] = "EmailEsistente";
                    header('Location: /discenti/' . $id['id']);
                    exit();
                }
            } else {
                $emailQuery = "SELECT * FROM an_discenti WHERE id = " . $idDiscente;
                $email = $database->query($emailQuery);
                $email = $email[0];

                //Se il link è scaduto le credenziali già esistono, si fa un update della password e si rinvia l'email
                $pwd = generaPasswordComplessa(8);
                $fileNames = "";
                $invii = "";
                $oggetto = "Registrazione Università GTC";
                $destinatario = $email['email'];
                $messaggio = "<div style='
                                    background-color: #fff;
                                    padding: 20px;
                                    border-radius: 8px;
                                    box-shadow: 5px 5px 20px 5px rgba(0, 0, 0, 0.25);
                                    width: 80%;
                                    max-width: 400px;
                                    margin: auto;
                                    box-sizing: border-box;
                                    text-align: center;
                                    font-family: Arial, sans-serif;
                                    color: #333;
                                '>
                                <img src='{$dominio}/images/universitygtc.png' alt='' style='display: block; margin: 0 auto; width:108; height:100;'><br><br>
                                <h2 style='margin: 20px 0 10px;'>Ecco a te le credenziali di accesso:</h2>
                                <p style='font-size: 1.2em; font-weight: bold; color: #333;'>Username: " . $email['email'] . "</p>
                                <p style='font-size: 1.2em; font-weight: bold; color: #333;'>Password: " . $pwd . "</p>
                                <a href='https://gestione.universitygtc.it/login.php' style='margin-top: 20px; color: #555;'>https://gestione.universitygtc.it/login.php.</a>
                            </div>";


                try {
                    InviaEmail($oggetto, $destinatario, $messaggio, $fileNames, $invii);
                    $datidiscenteQuery = "SELECT * FROM an_discenti WHERE id = " . $idDiscente;
                    $datidiscente = $database->query($datidiscenteQuery);
                    $datidiscente = $datidiscente[0];
                    $data = array(
                        'user' => $email['email'],
                        'pwd' => password_hash($pwd, PASSWORD_DEFAULT),
                        'nome' => $datidiscente['nome'],
                        'cognome' => $datidiscente['cognome'],
                        'email' => $email['email'],
                        'ruolo' => 7,
                        'status' => 'attivo'
                    );

                    $database->insert('users', $data);
                    $idUser = $database->lastId();

                    $data = array(
                        'idUser' => $idUser,
                    );

                    $database->update('an_discenti', $data, "id = " . $idDiscente);


                    $datadiscente = array(
                        'id_discente' => $idDiscente,
                        'email' => $email['email']
                    );

                    $database->insert('emailInviataDiscenti', $datadiscente);

                    $_SESSION['message'] = "EmailOk";
                    header('Location: /discenti/' . $id['id']);
                    exit();
                } catch (Exception $e) {
                    $_SESSION['message'] = "EmailKo";
                    header('Location: /discenti/' . $id['id']);
                    exit();
                }

            }
        }

        // Recupera i dati del discente
        $query = "SELECT * FROM an_discenti WHERE id = " . $idDiscente;
        $discente = $database->query($query);

        foreach ($discente as &$d) {
            $raw = $d['certificazioneZip'] ?? null;

            if (is_string($raw) && trim($raw) !== '') {
                $decoded = json_decode($raw, true);
                if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    $d['certificazioneZip'] = null;
                } else {
                    $d['certificazioneZip'] = $decoded;
                }
            } elseif (!is_array($raw)) {
                $d['certificazioneZip'] = null;
            }
        }
        // Spezza SOLO la reference temporanea, i dati rimangono nell'array
        unset($d);

        $queryFile = "SELECT documento_unito FROM costruzionePDF WHERE id= " . $idDiscente;
        $fileImmatricolazione = $database->query($queryFile);

        if (!empty($fileImmatricolazione)) {
            // prendi il valore normalizzato dal primo record
            $czip = $discente[0]['certificazioneZip'] ?? null;

            // se durante il foreach l’hai convertito in array, evita di decodificare di nuovo
            if (is_array($czip)) {
                $decoded = $czip;
            } else {
                $decoded = is_string($czip) && trim($czip) !== '' ? json_decode($czip, true) : null;
                if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    error_log('JSON decode error: ' . json_last_error_msg());
                    die('Errore JSON: ' . json_last_error());
                }
            }

            // debug safe
            //var_dump(array_keys($decoded ?? []));
            //var_dump($decoded['zip'] ?? null);
        }
        //Insert File Discente
        if (isset($_POST) && !empty($_POST)) {
            $discente = $discente[0]; // Assicurati di accedere al primo elemento dell'array

            if (!empty($discente['nome']) && !empty($discente['cognome'])) {
                $nome = substr(trim($discente['nome']), 0, 50);
                $cognome = substr(trim($discente['cognome']), 0, 50);

                $nome_discente = ucfirst(strtolower($nome)) . '_' . ucfirst(strtolower($cognome));
                $NomeDiscenteSanitizzato = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nome_discente);

                // SOLO PER LA VISUALIZZAZIONE (certificazioneZip gestito a parte)
                $fileInputs = [
                    'id_card' => "Carta d'Identità",
                    'tessera' => "Tessera Sanitaria",
                    'cv' => "Curriculum Vitae"
                ];

                $savedFiles = [];

                // Salvataggio file singoli come prima
                foreach ($fileInputs as $inputName => $label) {
                    $percorso = __DIR__ . '/../uploads/anagrafica_discente';
                    $result = salvaFile($inputName, $NomeDiscenteSanitizzato, $percorso);
                    if (!empty($result['success'])) {
                        $savedFiles[$inputName] = $result['url'];  // stesso formato/percorsi di prima
                    } else {
                        $savedFiles[$inputName] = '';
                    }
                }

                // Gestione certificazioneZip: salva file, crea ZIP, salva JSON
                $certPaths = [];
                $zipPath = '';
                $percorso = __DIR__ . '/../uploads/anagrafica_discente';

                if (!empty($_FILES['certificazioneZip'])) {

                    // Caso MULTIPLO: name="certificazioneZip[]" (PHP fornisce array di name/tmp_name/...)
                    if (is_array($_FILES['certificazioneZip']['name'])) {
                        $names = $_FILES['certificazioneZip']['name'];
                        $count = is_array($names) ? count(array_filter($names, fn($n) => $n !== null && $n !== '')) : 0;

                        for ($i = 0; $i < $count; $i++) {
                            if ($_FILES['certificazioneZip']['error'][$i] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['certificazioneZip']['tmp_name'][$i])) {

                                // Riusa salvaFile creando una chiave temporanea in $_FILES
                                $tmpSingle = [
                                    'name' => $_FILES['certificazioneZip']['name'][$i],
                                    'type' => $_FILES['certificazioneZip']['type'][$i],
                                    'tmp_name' => $_FILES['certificazioneZip']['tmp_name'][$i],
                                    'error' => $_FILES['certificazioneZip']['error'][$i],
                                    'size' => $_FILES['certificazioneZip']['size'][$i],
                                ];
                                $backup = $_FILES['__single'] ?? null;
                                $_FILES['__single'] = $tmpSingle;

                                $res = salvaFile('__single', $NomeDiscenteSanitizzato, $percorso);

                                if ($backup === null) {
                                    unset($_FILES['__single']);
                                } else {
                                    $_FILES['__single'] = $backup;
                                }

                                if (!empty($res['success']) && !empty($res['url'])) {
                                    $certPaths[] = $res['url'];
                                }
                            }
                        }
                    }
                    // Caso SINGOLO: name="certificazioneZip" (senza [])
                    elseif (!empty($_FILES['certificazioneZip']['name'])) {
                        if ($_FILES['certificazioneZip']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['certificazioneZip']['tmp_name'])) {
                            $backup = $_FILES['__single'] ?? null;
                            $_FILES['__single'] = $_FILES['certificazioneZip'];
                            $res = salvaFile('__single', $NomeDiscenteSanitizzato, $percorso);
                            if ($backup === null) {
                                unset($_FILES['__single']);
                            } else {
                                $_FILES['__single'] = $backup;
                            }
                            if (!empty($res['success']) && !empty($res['url'])) {
                                $certPaths[] = $res['url'];
                            }
                        }
                    }
                }

                // Crea lo ZIP se ci sono file salvati
                if (!empty($certPaths)) {
                    $zipDir = __DIR__ . '/../uploads/anagrafica_discente/' . $NomeDiscenteSanitizzato;
                    if (!is_dir($zipDir)) {
                        @mkdir($zipDir, 0777, true);
                    }
                    if (!is_writable($zipDir)) {
                        error_log("ZIP error: dir not writable $zipDir");
                    }

                    $zipPath = $zipDir . '/certificazioni_' . date('Ymd_His') . '.zip';

                    $zip = new ZipArchive();
                    $open = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    if ($open === true) {
                        $added = 0;
                        foreach ($certPaths as $p) {
                            // p è un URL; estrai solo il path e mappalo su filesystem
                            $abs = '';
                            $parts = parse_url($p);
                            if (!empty($parts['path']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
                                $abs = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $parts['path'];
                            }
                            // fallback se già è un path
                            if ($abs === '') {
                                $abs = $p;
                            }
                            $abs = str_replace('\\', '/', $abs);

                            if (is_file($abs) && is_readable($abs)) {
                                if ($zip->addFile($abs, basename($abs))) {
                                    $added++;
                                }
                            } else {
                                error_log("ZIP addFile skip (unresolvable): $p -> $abs");
                            }
                        }
                        if ($added > 0) {
                            if (!$zip->close()) {
                                error_log("ZIP close failed: " . $zip->getStatusString());
                                $zipPath = '';
                            }
                        } else {
                            $zip->close();
                            @unlink($zipPath);
                            $zipPath = '';
                        }

                        // Converti il path ZIP in URL per il DB (se esiste)
                        $zipUrlForDb = $zipPath !== '' ? fsToUrl($zipPath) : '';

                        // JSON con tutti i percorsi da salvare nel campo esistente 'certificazioneZip'
                        $savedFiles['certificazioneZip'] = json_encode([
                            'files' => $certPaths,
                            'zip' => $zipUrlForDb
                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                    } else {
                        error_log("ZIP open failed code: $open");
                        $zipPath = '';
                    }
                }

                // Dati per l'update: stessi campi DB
                $data_discente = [
                    'id_card' => $savedFiles['id_card'] ?? '',
                    'tessera' => $savedFiles['tessera'] ?? '',
                    'certificazioneZip' => $savedFiles['certificazioneZip'] ?? '',
                    'cv' => $savedFiles['cv'] ?? ''
                ];

                $where = "id = " . $idDiscente;
                $database->update("an_discenti", $data_discente, $where);
            }

            $_SESSION['message'] = "insertDoc";
            header("Location: /discenti/" . $id['id']);
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Visualizza Discente',
            'serp' => '/discenti' . '/' . $id['id'],
            'id' => $id['id'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'menu' => $menu['discenti'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Visualizza Discente',
            'date' => [],
            'discente' => $discente,
            'message' => $message,
            'content' => ""
        ];

        if (isset($esami_discente) && !empty($esami_discente)) {
            $content['esami'] = $esami_discente;
        } else {
            $content['esami'] = []; // Inizializza come array vuoto se non ci sono esami
        }

        $result = render('discenti/visualizza_discenti', $content);

        return $result;
    });
    //gestione Esami di root
    $router->addRoute('/discenti/gestione/{id}/esami', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $idDiscente = base64_decode($id['id']);
        $controlloesami_query = "SELECT id, fileVoti FROM discente_esami WHERE did = $idDiscente";
        $esami = $database->query($controlloesami_query);


        if (!empty($esami)) {
            $url = '/discenti/gestione/' . $id['id'] . '/data_esami';
        } else {
            $url = '/discenti/gestione/' . $id['id'] . '/assegna_percorso';
        }
        header('Location: ' . $url);
        exit();


    });
    //Visualizza Discenti
    $router->addRoute('/discenti/gestione/{id}/voti', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $message = "";
        $idDiscente = base64_decode($id['id']);
        //percorso del file per scaricarlo
        $Prevalutazione = "https://gestione.universitygtc.it/uploads/Prevalutazione/Prevalutazione.docx";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_FILES['FileVoti']) && !empty($_FILES['FileVoti'])) {

            $fileInputs = [
                'FileVoti' => "fileVoti",
            ];

            $savedFiles = [];

            foreach ($fileInputs as $inputName => $label) {
                $percorso = __DIR__ . '/../uploads/filePrevalutazione';
                $result = salvaFile($inputName, $id['id'], $percorso);
                if ($result['success']) {
                    $savedFiles[$inputName] = $result['url'];  // Salvi l'URL univoco
                    //echo "✅ File inserito: {$label} - {$result['file']}<br>";
                } else {
                    $savedFiles[$inputName] = '';  // fallback se qualcosa fallisce
                    //echo "❌ Errore per {$label}: {$result['error']}<br>";
                }
            }

            $data = array(
                'fileVoti' => $savedFiles['FileVoti']
            );
            $database->update("discente_esami", $data, "did= " . $idDiscente);
            $_SESSION['message'] = "FileInserito";
            header('Location: /discenti/gestione/' . $id['id'] . '/voti');
        }

        // Recupera i dati del discente
        $query = "SELECT * FROM an_discenti WHERE id = " . $idDiscente;

        $discente = $database->query($query);

        // Verifica se sono stati trovati esami per il discente
        $query = "SELECT esame_json, fileVoti FROM discente_esami WHERE did = " . $idDiscente;
        $esami = $database->query($query);


        if (!empty($esami)) {
            $id_esami = json_decode($esami[0]['esame_json'], true);
            //echo 'entro';exit();
            $esami_discente = [];
            if (!empty($id_esami)) {
                foreach ($id_esami as $esame) {
                    $query = "SELECT 
                                e.id, 
                                e.codice, 
                                e.nome, 
                                p.percorso, 
                                e.cfu
                            FROM 
                                an_esami AS e
                            JOIN 
                                an_percorsi AS p ON e.pid = p.id
                            WHERE 
                                e.id = " . $esame;
                    $result = $database->query($query);
                    if (!empty($result)) {
                        $esame_info = $result[0];

                        // Query per recuperare il voto dell'esame
                        $query_voto = "SELECT voto FROM votiDiscenteEsami WHERE id_esame = " . $esame_info['id'] . " AND did = " . $idDiscente;
                        $voto_result = $database->query($query_voto);

                        if (!empty($voto_result)) {
                            $esame_info['voto'] = $voto_result[0]['voto'];
                        } else {
                            $esame_info['voto'] = null; // O un valore di default se l'esame non ha ancora un voto
                        }
                        $esami_discente[] = $esame_info;
                    }
                }
            }
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Visualizza Discente',
            'serp' => '/discenti/gestione/' . $id['id'] . '/voti',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Visualizza Discente',
            'date' => [],
            'discente' => $discente,
            'message' => $message,
            'Prevalutazione' => $Prevalutazione,
            'fileVoti' => $esami[0]['fileVoti'] ?? "",
            'content' => ""
        ];

        if (isset($esami_discente) && !empty($esami_discente)) {
            $content['esami'] = $esami_discente;
        } else {
            $content['esami'] = []; // Inizializza come array vuoto se non ci sono esami
        }

        $result = render('discenti/voti', $content);

        return $result;
    });
    //Visualizza Carriera Discente
    $router->addRoute('/discenti/gestione/{id}/carriera', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $message = "";
        $idDiscente = base64_decode($id['id']);
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        $query = "SELECT 
                    se.*,
                    d.numMatricola,
                    d.nome AS nome_discente, 
                    d.cognome AS cognome_discente, 
                    e.nome AS nome_esame
                FROM scadenzaEsami AS se
                JOIN an_discenti AS d ON se.id_discente = d.id
                JOIN an_esami AS e ON se.id_esame = e.id
                WHERE se.id_discente =" . intval($idDiscente);
        ;
        $esamiCarriera = $database->query($query);

        $discente = $database->select("an_discenti", "nome, cognome", "id= " . $idDiscente);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Visualizza Discente',
            'serp' => '/discenti/gestione/' . $id['id'] . '/carriera',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Visualizza Discente',
            'date' => [],
            'esamiCarriera' => $esamiCarriera,
            'message' => $message,
            'content' => ""
        ];

        if (isset($esami_discente) && !empty($esami_discente)) {
            $content['esami'] = $esami_discente;
        } else {
            $content['esami'] = []; // Inizializza come array vuoto se non ci sono esami
        }

        $result = render('discenti/carriera', $content);

        return $result;
    });
    //Route Per Inserire i Discenti
    $router->addRoute('/discenti/gestione/new', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = "";

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        //Insert
        if (isset($_POST) && !empty($_POST)) {
            // Recupera l'azione dal form (insert o update)
            if (!empty($_POST['nome']) && !empty($_POST['cognome'])) {
                $data_discente = [
                    'cognome' => $_POST['cognome'],
                    'nome' => $_POST['nome'],
                    'sesso' => $_POST['sesso'],
                    'cellulareEcampus' => $_POST['cellulareEcampus'],
                    'cellulare' => $_POST['cellulare'],
                    'emailPersonale' => $_POST['emailPersonale'],
                    'pwdPersonale' => $_POST['pwdPersonale'],
                    'email' => $_POST['email'],
                    'pwdMultiuniversity' => $_POST['pwdMultiuniversity'],
                    'cf' => $_POST['cf'],
                    'luogoNascita' => $_POST['luogoNascita'],
                    'provinciaNascita' => $_POST['provinciaNascita'],
                    'dataNascita' => $_POST['dataNascita'],
                    'cittadinanza' => $_POST['cittadinanza'],
                    'secondaCittadinanza' => $_POST['secondaCittadinanza'],
                    'viaResidenza' => $_POST['viaResidenza'],
                    'localitaResidenza' => $_POST['localitaResidenza'],
                    'CAPresidenza' => $_POST['CAPresidenza'],
                    'citta' => $_POST['citta'],
                    'provincia' => $_POST['provincia'],
                    'presso' => $_POST['presso'],
                    'viaRecapito' => $_POST['viaRecapito'],
                    'localitaRecapito' => $_POST['localitaRecapito'],
                    'telefonoDue' => $_POST['telefonoDue'],
                    'indirizzoCorrispondente' => $_POST['indirizzoCorrispondente'],
                    'tipoDiploma' => $_POST['tipoDiploma'],
                    'tipoLaurea' => $_POST['tipoLaurea'],
                    'emailEcampus' => $_POST['emailEcampus'],
                    'idEcampus' => $_POST['idEcampus'],
                    'pwdEcampus' => $_POST['pwdEcampus'],
                    'titoloLaurea' => $_POST['titoloLaurea'],
                    'numMatricola' => $_POST['numMatricola'] ?? NULL,
                    'referenteDiscente' => $_POST['referenteDiscente'],
                    'referenteInt' => $_POST['referenteInterno']
                ];

                if ($_POST['tipoLaurea'] != '1') {
                    $content['tipoLaurea'] = $_POST['tipoLaurea'];
                }

                $database->insert("an_discenti", $data_discente);

                $id_discente = $database->lastId();

                /*In base alla scelta del percorso bisogna ricavarsi facoltà e ente, e bisogna vedere per la logica per ricavarsi
                esame/master/certificazione*/

                if (isset($_POST['tipologiaDiscente']) && !empty($_POST['tipologiaDiscente'])) {
                    switch ($_POST['tipologiaDiscente']) {
                        case $_POST['tipologiaDiscente'] == 'an_percorsi':
                            $tabella = 'an_percorsi';
                            $location = "/discenti/gestione/" . base64_encode($id_discente) . "/assegna_percorso";
                            break;
                        case $_POST['tipologiaDiscente'] == 'an_esami':
                            $tabella = "an_esami";
                            $location = "/discenti/gestione/" . base64_encode($id_discente) . "/esame_singolo";
                            break;
                        case $_POST['tipologiaDiscente'] == 'an_certificazioni':
                            $tabella = "an_certificazioni";
                            $location = "/discenti/gestione/" . base64_encode($id_discente) . "/certificazione";
                            break;
                        case $_POST['tipologiaDiscente'] == 'an_master':
                            $tabella = "an_master";
                            $location = "";
                            break;
                    }

                    $data = array(
                        'id_discente' => $id_discente,
                        'tabella' => $tabella
                    );
                    //echo'<pre>';print_r($data);echo'</pre>';
                    $database->insert('carriera', $data);
                    if (empty($location)) {
                        $location = "/discenti/gestione";
                    }
                    $_SESSION['message'] = "insertProdotto";
                    header('location: ' . $location);
                }
            }
        }

        //Select dei percorsi di laurea
        $percorsi = "SELECT id, percorso FROM an_percorsi WHERE 1";
        $percorsi = $database->query($percorsi);

        $utenti = $database->select(
            "users JOIN rules ON users.ruolo = rules.id",
            "users.id, users.nome, users.cognome, rules.ruolo AS nome_ruolo",
            "1 ORDER BY rules.ruolo, users.cognome"
        );

        // Raggruppa gli utenti per ruolo
        $gruppi = [];
        $interni = ['Superadmin', 'Admin', 'Operatore'];
        $segnalatori = ['Admin', 'Point', 'Segnalatore', 'Rivenditore'];
        foreach ($utenti as $utente) {
            if (in_array($utente['nome_ruolo'], $interni)) {
                $referente_int[] = $utente;
            }
            if (in_array($utente['nome_ruolo'], $segnalatori)) {
                $gruppi[$utente['nome_ruolo']][] = $utente;
            }
        }


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Gestione Discenti',
            'serp' => '/discenti/gestione/new',
            'menu' => $menu['discenti'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Gestione Discenti',
            'date' => array(),
            'gruppi' => $gruppi,
            'message' => $message,
            'referente_int' => $referente_int,
            'percorsi' => $percorsi,
            'content' => ""
        ];
        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/gestione_discenti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per Modificare i Discenti
    $router->addRoute('/discenti/gestione/{id}/update', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $idDecodificato = base64_decode($id['id']);

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = "";
        $update = base64_decode($id['id']);

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        $discente = $database->select('an_discenti', '*', "id = " . $idDecodificato);

        //Update
        if (isset($_POST) && !empty($_POST)) {
            $discente_id = $idDecodificato ?? null;
            if (!$discente_id) {
                throw new Exception("ID discente non fornito per l'aggiornamento.");
            }

            if (!$discente || count($discente) === 0) {
                throw new Exception("Discente non trovato.");
            }

            $discente = $discente[0]; // Prendi il primo (e unico) record

            if (!empty($_POST['nome']) && !empty($_POST['cognome'])) {

                $data_discente = [
                    'cognome' => $_POST['cognome'],
                    'nome' => $_POST['nome'],
                    'sesso' => $_POST['sesso'],
                    'cellulareEcampus' => $_POST['cellulareEcampus'],
                    'cellulare' => $_POST['cellulare'],
                    'emailPersonale' => $_POST['emailPersonale'],
                    'pwdPersonale' => $_POST['pwdPersonale'],
                    'email' => $_POST['email'],
                    'pwdMultiuniversity' => $_POST['pwdMultiuniversity'],
                    'cf' => $_POST['cf'],
                    'luogoNascita' => $_POST['luogoNascita'],
                    'provinciaNascita' => $_POST['provinciaNascita'],
                    'dataNascita' => $_POST['dataNascita'],
                    'cittadinanza' => $_POST['cittadinanza'],
                    'secondaCittadinanza' => $_POST['secondaCittadinanza'],
                    'viaResidenza' => $_POST['viaResidenza'],
                    'localitaResidenza' => $_POST['localitaResidenza'],
                    'CAPresidenza' => $_POST['CAPresidenza'],
                    'citta' => $_POST['citta'],
                    'provincia' => $_POST['provincia'],
                    'presso' => $_POST['presso'],
                    'viaRecapito' => $_POST['viaRecapito'],
                    'localitaRecapito' => $_POST['localitaRecapito'],
                    'telefonoDue' => $_POST['telefonoDue'],
                    'indirizzoCorrispondente' => $_POST['indirizzoCorrispondente'],
                    'tipoDiploma' => $_POST['tipoDiploma'],
                    'tipoLaurea' => $_POST['tipoLaurea'],
                    'titoloLaurea' => $_POST['titoloLaurea'],
                    'numMatricola' => $_POST['numMatricola'],
                    'idEcampus' => $_POST['idEcampus'],
                    'referenteDiscente' => $_POST['referenteDiscente'],
                ];

                // 4. UPDATE
                $database->update('an_discenti', $data_discente, "id = " . $discente_id);
            }
            $_SESSION['message'] = "update";
            header("Location: /discenti");

        }

        if ((isset($update) && !empty($update))) {
            $discenti = $database->query("SELECT * FROM an_discenti WHERE id = $update")[0];
            $carriera = $database->query("SELECT * FROM carriera WHERE id_discente = $idDecodificato");
            if (!empty($carriera)) {
                $carriera = $carriera[0];
                switch ($carriera['tabella']) {
                    case 'an_percorsi':
                        $carriera['tabella'] = "Percorso di laurea";
                        break;
                    case 'an_esami':
                        $carriera['tabella'] = "Esame Singolo";
                        break;
                    case 'an_certificazioni':
                        $carriera['tabella'] = "Certificazione";
                        break;
                    case 'an_master':
                        $carriera['tabella'] = "Master";
                        break;
                }

                $discenti['carriera'] = $carriera['tabella'];
            }
        }



        $utenti = $database->select(
            "users JOIN rules ON users.ruolo = rules.id",
            "users.id, users.nome, users.cognome, rules.ruolo AS nome_ruolo",
            "1 ORDER BY rules.ruolo, users.cognome"
        );

        // Raggruppa gli utenti per ruolo
        $gruppi = [];
        $interni = ['Superadmin', 'Admin', 'Operatore'];
        $segnalatori = ['Admin', 'Point', 'Segnalatore', 'Rivenditore'];
        foreach ($utenti as $utente) {
            if (in_array($utente['nome_ruolo'], $interni)) {
                $referente_int[] = $utente;
            }
            if (in_array($utente['nome_ruolo'], $segnalatori)) {
                $gruppi[$utente['nome_ruolo']][] = $utente;
            }
        }

        //echo'<pre>';print_r($utenti);echo'</pre>';exit();

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Gestione Discenti',
            'serp' => '/discenti/' . $id['id'] . '/update',
            'id' => $id['id'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'menu' => $menu['discenti'],
            'gruppi' => $gruppi,
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'referente_int' => $referente_int,
            'h1' => '',
            'h2' => 'Gestione Discenti',
            'date' => array(),
            'message' => $message,
            'update' => $update,
            'content' => ""
        ];

        if ((isset($update) && !empty($update))) {
            $content['discente'] = $discenti;
        }

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/gestione_discenti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Collegamento E-Campus
    $router->addRoute('/discenti/gestione/{id}/collegamento', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }


        $query = "SELECT * FROM an_discenti WHERE id= " . $id_decodificato;
        $datiDiscente = $database->query($query);
        $datiDiscente = $datiDiscente[0];


        $query = "SELECT 
                    ve.id_esame, 
                    ve.voto, 
                    ae.nome AS nome_esame
                FROM votiDiscenteEsami as ve
                JOIN an_esami ae ON ve.id_esame = ae.id
                WHERE ve.did = $id_decodificato";

        $esami = $database->query($query);
        $datiDiscente['esami'] = $esami;

        //print_r($datiDiscente);exit();
        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Collegamento E-Campus',
            'serp' => '/discenti/gestione/' . $id['id'] . '/collegamento',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $datiDiscente['nome'],
            'cognome' => $datiDiscente['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Accesso E-Campus',
            'date' => array(),
            'datiDiscente' => $datiDiscente,
            'message' => $message,
            'content' => ""
        ];

        $message = "";

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/collegamento', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Accordo Discente
    $router->addRoute('/discenti/gestione/{id}/accordo', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        // Recupero dati discente
        $datiDiscente = $database->query("SELECT * FROM an_discenti WHERE id = $id_decodificato")[0];

        // Recupero carriera
        $carriera = $database->query("SELECT * FROM carriera WHERE id_discente = $id_decodificato");
        if (isset($carriera) && !empty($carriera)) {
            $carrieraZ = $carriera[0];
        }

        // Mappa tabella → colonna ID e nome da estrarre
        $tabellaMap = [
            'an_percorsi' => ['id_col' => 'id_percorso', 'nome_col' => 'percorso'],
            'an_esami' => ['id_col' => 'id_esame', 'nome_col' => 'nome'],
            'an_pacchettiEsame' => ['id_col' => 'id_pacchetto', 'nome_col' => 'nomePacchetto'],
            'an_certificazioni' => ['id_col' => 'id_certificazione', 'nome_col' => 'nome'],
            'an_master' => ['id_col' => 'id_master', 'nome_col' => 'nome']
        ];

        // Verifica esistenza tabella e recupero prodotto scelto
        if (!empty($carrieraZ)) {
            if (isset($tabellaMap[$carrieraZ['tabella']])) {
                $info = $tabellaMap[$carrieraZ['tabella']];
                $id_prodotto = intval($carrieraZ[$info['id_col']]);
                $risultato = $database->select($carrieraZ['tabella'], $info['nome_col'], "id = $id_prodotto");
                $datiDiscente['prodotto_scelto'] = $risultato[0][$info['nome_col']] ?? null;
            }
        }

        // Array per le opzioni del menu a tendina
        $RisultatoProdotto = [];
        // Recupera i nomi di tutti i prodotti per cui esiste già un accordo
        $accordiEsistenti = $database->select("rateDiscente", "riferimentoNomeProd", "id_discente = " . $id_decodificato);
        $nomiProdottiConAccordo = !empty($accordiEsistenti) ? array_column($accordiEsistenti, 'riferimentoNomeProd') : [];

        foreach ($carriera as $c) {
            $nomeProdotto = null;
            $tutoraggio = 'no';
            $tabellaOrigine = $c['tabella'];
            $idProdotto = null;
            $tabellaTutoraggio = null;
            $colonnaIdTutoraggio = null;

            // Identifica tipo e ID del prodotto
            if (!empty($c['id_percorso'])) {
                $idProdotto = (int) $c['id_percorso'];
                $nomeProdottoQuery = $database->select('an_percorsi', 'percorso', "id = $idProdotto");
                if ($nomeProdottoQuery)
                    $nomeProdotto = $nomeProdottoQuery[0]['percorso'];
                $tabellaTutoraggio = "discente_esami";
                $colonnaIdTutoraggio = 'pid';
            } elseif (!empty($c['id_esame'])) {
                $idProdotto = (int) $c['id_esame'];
                $nomeProdottoQuery = $database->select('an_esami', 'nome', "id = $idProdotto");
                if ($nomeProdottoQuery)
                    $nomeProdotto = $nomeProdottoQuery[0]['nome'];
                $tabellaTutoraggio = "discente_esameSingolo";
                $colonnaIdTutoraggio = 'id_esame';
            } elseif (!empty($c['id_certificazione'])) {
                $idProdotto = (int) $c['id_certificazione'];
                $nomeProdottoQuery = $database->select('an_certificazioni', 'nome', "id = $idProdotto");
                if ($nomeProdottoQuery)
                    $nomeProdotto = $nomeProdottoQuery[0]['nome'];
                $tabellaTutoraggio = "discente_certificazioni";
                $colonnaIdTutoraggio = 'id_cert';
            } elseif (!empty($c['id_master'])) {
                $idProdotto = (int) $c['id_master'];
                $nomeProdottoQuery = $database->select('an_master', 'nome', "id = $idProdotto");
                if ($nomeProdottoQuery)
                    $nomeProdotto = $nomeProdottoQuery[0]['nome'];
                $tabellaTutoraggio = "discente_esami";
                $colonnaIdTutoraggio = 'pid';
            } elseif (!empty($c['id_pacchettoEsami'])) {
                $idProdotto = (int) $c['id_pacchettoEsami'];
                $nomeProdottoQuery = $database->select('an_pacchettiEsame', 'nomePacchetto', "id = $idProdotto");
                if ($nomeProdottoQuery)
                    $nomeProdotto = $nomeProdottoQuery[0]['nomePacchetto'];
                $tabellaTutoraggio = "discente_esami";
                $colonnaIdTutoraggio = 'pid';
            } else {
                continue;
            }

            if (!$nomeProdotto || !$idProdotto) {
                continue;
            }

            // Recupera info tutoraggio in modo sicuro
            if ($tabellaTutoraggio && $colonnaIdTutoraggio) {
                $tutoraggioQuery = $database->select($tabellaTutoraggio, "tutoraggio", "did = $id_decodificato AND $colonnaIdTutoraggio = $idProdotto");
                if ($tutoraggioQuery && isset($tutoraggioQuery[0]['tutoraggio'])) {
                    $tutoraggio = $tutoraggioQuery[0]['tutoraggio'];
                }
            }

            // Aggiunge la voce per il prodotto principale, se non esiste già un accordo
            if (!in_array($nomeProdotto, $nomiProdottiConAccordo)) {
                $RisultatoProdotto[] = $nomeProdotto . '||' . $tabellaOrigine;
            }

            // Se c'è il tutoraggio, aggiunge una voce separata per esso, se non esiste già un accordo
            if ($tutoraggio == 'si') {
                $nomeProdottoTutoraggio = $nomeProdotto . ' (Tutoraggio)';
                if (!in_array($nomeProdottoTutoraggio, $nomiProdottiConAccordo)) {
                    $RisultatoProdotto[] = $nomeProdottoTutoraggio . '||' . $tabellaOrigine . '||tutoraggio';
                }
            }
        }

        if (isset($_POST) && !empty($_POST)) {
            //Recupera prodotti selezionati
            $prodotto = $_POST['prodotto'] ?? [];

            //Rate (array)
            $totalerata = $_POST['importoRata'] ?? [];
            $datascadenza = $_POST['dataImporto'] ?? [];

            if (!empty($prodotto) && is_array($prodotto)) {

                $nomiProdotti = [];
                $tabelleProdotti = [];
                $tutoraggio = 'no';
                foreach ($prodotto as $p) {
                    $parti = explode('||', $p);
                    $nomiProdotti[] = trim($parti[0] ?? '');
                    $tabelleProdotti[] = trim($parti[1] ?? '');
                    if (isset($parti[2]) && $parti[2] == 'tutoraggio') {
                        $tutoraggio = 'si';
                    }
                }

                $prodottiJson = json_encode($nomiProdotti, JSON_UNESCAPED_UNICODE);
                $prodottiTabellaJson = json_encode($tabelleProdotti, JSON_UNESCAPED_UNICODE);

                //Verifica se ci sono più prodotti
                $isMultiplo = count($prodotto) > 1;

                //Estrai il primo prodotto come riferimento
                $riferimentoNomeProd = $prodottiArray[0]['nome'] ?? '';
                $riferimentoTabellaProd = $prodottiArray[0]['tabella'] ?? '';

                //Determina il tipo pagamento base
                if ($isMultiplo) {
                    $tipoPagamento = 'Somma Prodotti';
                } else {
                    $tipoPagamento = 'Pagamento Ente';
                }

                //Determina il tipo finale (combinato tutoraggio + tipo pagamento)
                if ($tutoraggio === 'si' && $isMultiplo) {
                    $variabileTutoraggio = 'Tutoraggio + Somma Prodotti';
                } elseif ($tutoraggio === 'si') {
                    $variabileTutoraggio = 'Tutoraggio';
                } else {
                    $variabileTutoraggio = $tipoPagamento;
                }

                //Calcoli economici
                $importoTotale = (float) ($_POST['importoTotale'] ?? 0);
                $acconto = (float) ($_POST['acconto'] ?? 0);
                $residuo = $importoTotale - $acconto;

            }


            $data = array(
                'id_discente' => $id_decodificato,
                'riferimentoNomeProd' => $prodottiJson,
                'riferimentoTabellaProd' => $prodottiTabellaJson,
                'acconto' => $_POST['acconto'],
                'importoTotale' => $_POST['importoTotale'],
                'residuo' => $residuo,
                'tipoPagamento' => $variabileTutoraggio,
                'diritti_segreteria' => $_POST['diritti_segreteria'] ?? NULL,
                'tassa_regionale' => $_POST['tassa_regionale'] ?? NULL,
                'marca_bollo' => $_POST['marca_bollo'] ?? NULL

            );

            $database->insert("rateDiscente", $data);
            $idRateDiscente = $database->lastId();

            for ($i = 0; $i < count($totalerata); $i++) {
                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_rateScadenze' => $idRateDiscente,
                    'importoRata' => $totalerata[$i],
                    'data_scadenza' => $datascadenza[$i],
                    'pagato' => NULL
                );
                $database->insert('scadenzeRate', $data);
            }

            $_SESSION['message'] = "ImportoCorretto";
            header('location: /discenti');
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Accordo Economico',
            'serp' => '/discenti/gestione/' . $id['id'] . '/accordo',
            'id' => $id['id'],
            'nome' => $datiDiscente['nome'],
            'cognome' => $datiDiscente['cognome'],
            'menu' => $menu['discenti'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Accordo Economico',
            'date' => array(),
            'datiDiscente' => $datiDiscente,
            'carriera' => $carriera,
            'RisultatoProdotto' => $RisultatoProdotto,
            'message' => $message,
            'content' => ""
        ];

        $message = "";

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/accordo_economico', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Visualizza Accordi Sottoscritti
    $router->addRoute('/discenti/gestione/{id}/accordi_sottoscritti', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        // Recupero dati discente
        $datiDiscente = $database->query("SELECT idUser, nome, cognome, email, numMatricola FROM an_discenti WHERE id = $id_decodificato")[0];

        // Recupero pagamenti discente
        $pagamentiDiscente = $database->select("rateDiscente", "*", "id_discente= " . $id_decodificato);

        if (!empty($pagamentiDiscente)) {
            foreach ($pagamentiDiscente as &$p) {

                $Tabella = explode('||', $p['riferimentoTabellaProd']);
                $TabellaEsplosa = json_decode($Tabella[0]);
                $ProdottiEsplosi = json_decode($p['riferimentoNomeProd']);

                $arrayTabella = [];
                foreach ($TabellaEsplosa as $tab) {
                    switch ($tab) {
                        case 'an_percorsi':
                            $riferimento = 'Percorso di Laurea';
                            break;
                        case 'an_esami':
                            $riferimento = 'Esame';
                            break;
                        case 'an_certificazioni':
                            $riferimento = 'Certificazione';
                            break;
                        case 'an_master':
                            $riferimento = 'Master';
                            break;
                        default:
                            $riferimento = 'Altro';
                            break;
                    }

                    // Aggiungi solo se non già presente
                    if (!in_array($riferimento, $arrayTabella, true)) {
                        $arrayTabella[] = $riferimento;
                    }
                    if (count($arrayTabella) <= 2) {
                        $p['riferimentoTabellaProd'] = implode(', ', $arrayTabella);
                    } else {
                        //Se ci sono più di due → prendi solo i primi due e aggiungi "..."
                        $primeDue = array_slice($arrayTabella, 0, 2);
                        $p['riferimentoTabellaProd'] = implode(', ', $primeDue) . ' ...altro';
                    }
                }

                $arrayProdotti = [];
                foreach ($ProdottiEsplosi as $prod) {
                    $arrayProdotti[] = $prod;
                    if (count($arrayProdotti) <= 2) {
                        $p['riferimentoNomeProd'] = implode(', ', $arrayProdotti);
                    } else {
                        //Se ci sono più di due → prendi solo i primi due e aggiungi "..."
                        $primeDue = array_slice($arrayProdotti, 0, 2);
                        $p['riferimentoNomeProd'] = implode(', ', $primeDue) . ' ...altro';
                    }
                }

                if (isset($Tabella[1]) && $Tabella[1] == 'tutoraggio') {
                    $p['riferimentoTabellaProd'] .= ' + Tutoraggio';
                }

                $p['linkAccordo'] = '/discenti/gestione/' . $id['id'] . '/accordi_sottoscritti/' . base64_encode($p['id']);
            }
        } else {
            header('location: /discenti/gestione/' . $id['id'] . '/accordo');
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Accordi Sottoscritti',
            'serp' => '/discenti/gestione/' . $id['id'] . '/accordi_sottoscritti',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $datiDiscente['nome'],
            'cognome' => $datiDiscente['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Accordi Sottoscritti',
            'view' => 'view',
            'date' => array(),
            'datiDiscente' => $datiDiscente,
            'pagamentiDiscente' => $pagamentiDiscente,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/accordi_sottoscritti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Visualizza Accordo Discente
    $router->addRoute('/discenti/gestione/{id}/accordi_sottoscritti/{idAccordo}', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_discente = base64_decode($id['id']);
        $id_accordo = base64_decode($id['idAccordo']);

        if ($_SESSION['utente']['ruolo'] === 'Discente') {
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            if (!empty($idUser)) {
                $idUserEncoded = base64_encode($idUser[0]['id']);
                header("Location: /discenti/gestione/$idUserEncoded/voti");
                exit();
            }
        }
        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        //Dati discente
        $discente = $database->query("SELECT idUser, nome, cognome, email, numMatricola FROM an_discenti WHERE id = $id_discente");
        if (empty($discente)) {
            die("Discente non trovato.");
        }

        $datiDiscente = $discente[0];

        //Dati dell’accordo economico
        $accordo = $database->query("
            SELECT id, riferimentoNomeProd, riferimentoTabellaProd, tipoPagamento,
                diritti_segreteria, tassa_regionale, marca_bollo, acconto, importoTotale
            FROM rateDiscente
            WHERE id = $id_accordo
        ");

        if (empty($accordo)) {
            die("Accordo economico non trovato.");
        }

        $accordo = $accordo[0];
        $datiDiscente['pagamento'] = $accordo;
        $datiDiscente['prodotto_presente'] = json_decode($accordo['riferimentoNomeProd']) ?? '';
        //Mappa tabella → descrizione leggibile
        $tabellaToLabel = [
            'an_percorsi' => 'Percorso di Laurea',
            'an_esami' => 'Esame',
            'an_certificazioni' => 'Certificazione',
            'an_master' => 'Master',
            'an_pacchettiEsame' => 'Pacchetto Esami'
        ];
        $datiDiscente['riferimentoTabellaProd'] =
            $tabellaToLabel[$accordo['riferimentoTabellaProd']] ?? 'Altro';

        //Rate dell’accordo
        $rate = $database->query("
            SELECT id, importoRata, pagato, file, data_scadenza
            FROM scadenzeRate
            WHERE id_discente = $id_discente AND id_rateScadenze = {$accordo['id']}
        ");

        $datiDiscente['rata'] = $rate;

        //Carriera del discente
        $carriera = $database->query("SELECT * FROM carriera WHERE id_discente = $id_discente");
        $datiDiscente['carriera'] = $carriera;
        $carrieraZ = $carriera[0] ?? [];

        //Mappa tabella → colonne ID e Nome
        $tabellaMap = [
            'an_percorsi' => ['id_col' => 'id_percorso', 'nome_col' => 'percorso'],
            'an_esami' => ['id_col' => 'id_esame', 'nome_col' => 'nome'],
            'an_pacchettiEsame' => ['id_col' => 'id_pacchetto', 'nome_col' => 'nomePacchetto'],
            'an_certificazioni' => ['id_col' => 'id_certificazione', 'nome_col' => 'nome'],
            'an_master' => ['id_col' => 'id_master', 'nome_col' => 'nome']
        ];

        //Recupera nome del prodotto principale
        if (!empty($carrieraZ['tabella']) && isset($tabellaMap[$carrieraZ['tabella']])) {
            $info = $tabellaMap[$carrieraZ['tabella']];
            $id_prodotto = (int) $carrieraZ[$info['id_col']];
            $risultato = $database->select($carrieraZ['tabella'], $info['nome_col'], "id = $id_prodotto");
        }

        //Costruzione elenco prodotti/tutoraggi disponibili
        $RisultatoProdotto = [];
        $accordiEsistenti = $database->select("rateDiscente", "riferimentoNomeProd", "id_discente = $id_discente");
        $nomiProdottiConAccordo = array_column($accordiEsistenti ?? [], 'riferimentoNomeProd');

        foreach ($carriera as $c) {
            $tabella = $c['tabella'];
            $tipoProdotto = $tabellaToLabel[$tabella] ?? 'Altro';
            $idProdotto = null;
            $nomeProdotto = null;
            $tutoraggio = 'no';

            //Identifica id e nome prodotto in base alla tabella
            if (isset($tabellaMap[$tabella])) {
                $info = $tabellaMap[$tabella];
                $idProdotto = (int) $c[$info['id_col']];
                $nomeQuery = $database->select($tabella, $info['nome_col'], "id = $idProdotto");
                $nomeProdotto = $nomeQuery[0][$info['nome_col']] ?? null;
            }

            if (!$idProdotto || !$nomeProdotto)
                continue;

            //Recupera eventuale tutoraggio
            $tabellaTutoraggio = match ($tabella) {
                'an_percorsi', 'an_master', 'an_pacchettiEsame' => ['tab' => 'discente_esami', 'col' => 'pid'],
                'an_esami' => ['tab' => 'discente_esameSingolo', 'col' => 'id_esame'],
                'an_certificazioni' => ['tab' => 'discente_certificazioni', 'col' => 'id_cert'],
                default => null,
            };

            if ($tabellaTutoraggio) {
                $tutoraggioQuery = $database->select(
                    $tabellaTutoraggio['tab'],
                    "tutoraggio",
                    "did = $id_discente AND {$tabellaTutoraggio['col']} = $idProdotto"
                );
                if (!empty($tutoraggioQuery) && $tutoraggioQuery[0]['tutoraggio'] === 'si') {
                    $tutoraggio = 'si';
                }
            }

            //Aggiungi voce prodotto se non esiste già
            if (!in_array($nomeProdotto, $nomiProdottiConAccordo)) {
                $RisultatoProdotto[] = "$nomeProdotto||$tabella";
            }

            //Aggiungi tutoraggio se presente
            if ($tutoraggio === 'si' && !in_array($nomeProdotto . ' (Tutoraggio)', $nomiProdottiConAccordo)) {
                $RisultatoProdotto[] = "$nomeProdotto (Tutoraggio)||$tabella||tutoraggio";
            }
        }

        //POST UPDATE
        if (!empty($_POST)) {


            $prodotto = $_POST['prodotto'];
            $residuo = $_POST['importoTotale'] - $_POST['acconto'];
            $arrayProdotti = [];
            $arrayTabelle = [];
            $tutoraggioPresente = false;
            $tipoPagamento = "";

            //Caso multiplo
            if (count($prodotto) > 1) {
                foreach ($prodotto as $p) {
                    $prodottoEsploso = explode('||', $p);
                    $arrayProdotti[] = $prodottoEsploso[0] ?? '';
                    $arrayTabelle[] = $prodottoEsploso[1] ?? '';

                    if (isset($prodottoEsploso[2]) && trim($prodottoEsploso[2]) === 'tutoraggio') {
                        $tutoraggioPresente = true;
                    }
                }

                //Determina tipo pagamento finale
                $tipoPagamento = $tutoraggioPresente ? "Somma Prodotti + Tutoraggio" : "Somma Prodotti";

            } else {
                //Caso singolo
                $p = $prodotto[0];
                $prodottoEsploso = explode('||', $p);
                $arrayProdotti[] = $prodottoEsploso[0] ?? '';
                $arrayTabelle[] = $prodottoEsploso[1] ?? '';

                if (isset($prodottoEsploso[2]) && trim($prodottoEsploso[2]) === 'tutoraggio') {
                    $tipoPagamento = "Tutoraggio";
                } else {
                    $tipoPagamento = "Pagamento Ente";
                }
            }

            $prodottoJson = json_encode($arrayProdotti, JSON_UNESCAPED_UNICODE);
            $tabelleJson = json_encode($arrayTabelle, JSON_UNESCAPED_UNICODE);

            $data = [
                'id_discente' => $id_discente,
                'riferimentoNomeProd' => $prodottoJson,
                'riferimentoTabellaProd' => $tabelleJson,
                'importoTotale' => $_POST['importoTotale'],
                'acconto' => $_POST['acconto'],
                'residuo' => $residuo,
                'diritti_segreteria' => $_POST['diritti_segreteria'],
                'tassa_regionale' => $_POST['tassa_regionale'],
                'marca_bollo' => $_POST['marca_bollo'],
                'tipoPagamento' => $tipoPagamento
            ];

            $database->update("rateDiscente", $data, "id= " . $id_accordo);

            // Gestione rate
            if (isset($_POST['dataImporto'], $_POST['importoRata'])) {
                //Merge dei dati (vecchie + nuove)
                $arrayImporti = $_POST['importoRata'];
                $arrayDate = $_POST['dataImporto'];

                // Elimina tutte le rate precedenti legate all'accordo
                $database->delete("scadenzeRate", "id_rateScadenze = " . (int) $id_accordo);

                //Ciclo di inserimento
                foreach ($arrayImporti as $i => $importo) {
                    $dataScadenza = $arrayDate[$i] ?? null;

                    if (!empty($importo) && !empty($dataScadenza)) {
                        $dataRate = [
                            'id_discente' => $id_discente,
                            'id_rateScadenze' => $id_accordo,
                            'importoRata' => $importo,
                            'data_scadenza' => $dataScadenza
                        ];
                        $database->insert('scadenzeRate', $dataRate);
                    }
                }
            }

            $_SESSION['message'] = "UpdateOK";
            header("location: /discenti/gestione/{$id['id']}/accordi_sottoscritti/{$id['idAccordo']}");
        }

        //Prepara dati 
        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Accordo Economico',
            'template' => 'accordi_sottoscritti',
            'serp' => "/discenti/gestione/{$id['id']}/accordi_sottoscritti/{$id['idAccordo']}",
            'id' => $id['id'],
            'idAccordo' => $id['idAccordo'],
            'menu' => $menu['discenti'],
            'nome' => $datiDiscente['nome'],
            'cognome' => $datiDiscente['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'message' => $message,
            'h1' => '',
            'h2' => 'Accordo Economico',
            'view' => 'view',
            'date' => [],
            'RisultatoProdotto' => $RisultatoProdotto,
            'datiDiscente' => $datiDiscente,
            'content' => ""
        ];

        //Rendering della vista ===
        return render('discenti/accordo_economico', $content);
    });

    /*AREA ASSEGNAZIONE PRODOTTI AI DISCENTI*/
    //Route per l'inserimento di nuovi prodotti ai Discenti
    $router->addRoute('/discenti/gestione/{id}/nuovo_prodotto', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        // Recupero dati anagrafici del discente
        $discente = $database->select('an_discenti', 'nome, cognome, numMatricola', "id = " . $id_decodificato);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Aggiungi Prodotto',
            'serp' => '/discenti/gestione/' . $id['id'] . '/nuovo_prodotto',
            'id' => $id['id'],
            'menu_discenti' => '1',
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Aggiungi Prodotto',
            'date' => array(),
            'discente' => $discente,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/nuovo_prodotto', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per Assegnare i Corsi di Laurea ai Discenti
    $router->addRoute('/discenti/gestione/{id}/assegna_percorso', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = "";
        $id_decodificato = base64_decode($id['id']);
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_POST['action']) && $_POST['action'] == 'insert') {
            //echo'<pre>';print_r($_POST);echo'</pre>';exit();
            if (isset($_POST['esami']) && !empty($_POST['esami'])) {
                // Inserimento esami
                $nuovi_esami = $_POST['esami']; // array di nuovi ID esame
                $data = [
                    'did' => $id_decodificato,
                    'pid' => $_POST['corsoSelect'],
                    'esame_json' => json_encode($nuovi_esami),
                    'tutoraggio' => (isset($_POST['tutoraggio']) && $_POST['tutoraggio'] === 'on') ? 'si' : 'no'
                ];
                $database->insert('discente_esami', $data);
                $_SESSION['message'] = "insert";

                // Inserimento carriera sempre nuovo
                $dataCarriera = [
                    'annoAccessoDiscente' => $_POST['annoAccessoDiscente'],
                    'id_percorso' => $_POST['corsoSelect'],
                    'id_discente' => $id_decodificato,
                    'tutoraggio' => (isset($_POST['tutoraggio']) && $_POST['tutoraggio'] === 'on') ? 'si' : 'no',
                    'tabella' => 'an_percorsi',
                ];

                $database->insert("carriera", $dataCarriera);
                header('location: /discenti/gestione/' . $id['id'] . '/assegna_percorso');
            } else {
                $_SESSION['message'] = "CorsoNonPresente";
                header('location: /discenti/gestione/' . $id['id'] . '/assegna_percorso');
            }
        }

        // Recupera tutti i percorsi
        $query = "SELECT id, percorso FROM an_percorsi WHERE 1";
        $percorsi = $database->query($query);
        $discente = $database->select("an_discenti", "nome, cognome", "id= " . $id_decodificato);

        // Recupero tutti gli esami del discente
        $query = "SELECT esame_json FROM discente_esami WHERE did = " . $id_decodificato;
        $resEsami = $database->query($query);

        $percorsiDiscenteEsistenti = [];

        if (!empty($resEsami)) {
            $allEsami = [];

            // Unisco tutti gli esami contenuti nei JSON
            foreach ($resEsami as $row) {
                $ids = json_decode($row['esame_json'], true);
                if ($ids && is_array($ids)) {
                    $allEsami = array_merge($allEsami, $ids);
                }
            }

            $allEsami = array_values(array_unique(array_map('intval', $allEsami)));

            if (!empty($allEsami)) {
                // Recupero tutti i pid dagli esami
                $query = "SELECT pid FROM an_esami WHERE id IN (" . implode(',', $allEsami) . ")";
                $resPid = $database->query($query);

                // Estraggo e rendo unici i pid
                $pid_unici = array_values(array_unique(array_column($resPid, 'pid')));

                if (!empty($pid_unici)) {
                    // Recupero i dettagli dei percorsi
                    $query = "SELECT id, percorso FROM an_percorsi WHERE id IN (" . implode(',', $pid_unici) . ")";
                    $percorsiDiscenteEsistenti = $database->query($query);
                }
            }
        }


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Assegna Percorso',
            'serp' => '/discenti/gestione/' . $id['id'] . '/assegna_percorso',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'pid' => $pid_unici ?? [],
            'h1' => '',
            'h2' => 'Assegna Percorso',
            'date' => array(),
            'message' => $message,
            'percorsi' => $percorsi,
            'percorsiDiscenteEsistenti' => $percorsiDiscenteEsistenti ?? [],
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/prodotto/assegna_percorso', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per Modificare i Corsi di Laurea ai Discenti
    $router->addRoute('/discenti/gestione/{id}/modifica_percorso', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = "";
        $id_decodificato = base64_decode($id['id']);

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        $percorsi = $database->select("an_percorsi", "id, percorso", "1");
        $discente = $database->select("an_discenti", "nome, cognome", "id= " . $id_decodificato);
        $controllo = $database->select('discente_esami', '*', "did = " . intval($id_decodificato));
        $tutti_gli_esami = [];

        if (!empty($controllo)) {
            foreach ($controllo as &$c) {
                $esami = json_decode($c['esame_json'], true);
                if (!empty($esami)) {
                    $tutti_gli_esami = array_merge($tutti_gli_esami, $esami);
                }
                $carriera = null;

                if (!empty($esami)) {
                    $placeholders = implode(',', array_map('intval', $esami));
                    $queryEsame = $database->select('an_esami', 'DISTINCT pid', "id IN ($placeholders) AND pid IS NOT NULL");
                    $pidPossibili = array_unique(array_column($queryEsame, 'pid'));

                    if (!empty($pidPossibili)) {
                        $carriereData = $database->select(
                            'carriera',
                            'id_percorso, annoAccessoDiscente, data',
                            "id_discente = " . intval($id_decodificato)
                        );

                        foreach ($pidPossibili as $pid) {
                            foreach ($carriereData as $rigaCarriera) {
                                if ($rigaCarriera['id_percorso'] == $pid) {
                                    $carriera = $rigaCarriera;
                                    break 2;
                                }
                            }
                        }
                    }
                }

                $c['carriera'] = $carriera;
            }
        }

        //Stampa dei Nomi dei Percorsi degli esami presenti
        $AllEsami = json_encode(array_unique($tutti_gli_esami)); // opzionale
        $idEsamiEsistenti = json_decode($AllEsami, true);
        $idList = implode(',', array_map('intval', $idEsamiEsistenti));

        if (!empty($idList)) {
            $query = "SELECT DISTINCT percorso.percorso
            FROM an_esami AS esami
            INNER JOIN an_percorsi AS percorso ON esami.pid = percorso.id
            WHERE esami.id IN ($idList)";
            $NomePercorsi = $database->query($query);
        } else {
            $NomePercorsi = [];
        }

        //STAMPA ESAMI PRESENTI FUNZIONANTE
        if (isset($_POST) && !empty($_POST)) {
            $carrieraExist = $database->select("carriera", "id", "id_percorso= " . $_POST['corsoSelect'] . ' AND id_discente= ' . $id_decodificato);

            if (!empty($carrieraExist)) {
                $esami = isset($_POST['esami']) ? (array) $_POST['esami'] : [];
                $esamiEsistenti = isset($_POST['esamiEsistenti']) ? json_decode($_POST['esamiEsistenti'], true) : [];
                //Unisci, elimina duplicati e riindicizza
                $merged = array_values(
                    array_unique(
                        array_merge($esamiEsistenti, $esami)
                    )
                );

                //Ordina con sort
                sort($merged);
                $esamiEsistenti = json_encode($merged);

                $data = array(
                    'esame_json' => $esamiEsistenti,
                    'tutoraggio' => isset($_POST['tutoraggio']) ? 'si' : 'no'
                );
                $database->update("discente_esami", $data, "did= " . $id_decodificato);

                $_SESSION['message'] = "update";
                header('location: /discenti/gestione/' . $id['id'] . '/modifica_percorso');
            } else {
                $_SESSION['message'] = "errorCarriera";
                header('location: /discenti/gestione/' . $id['id'] . '/modifica_percorso');
            }
        }


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Modifica Percorso',
            'serp' => '/discenti/' . $id['id'] . '/modifica_percorso',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'NomePercorsi' => $NomePercorsi,
            'controllo' => $controllo,
            'h1' => '',
            'h2' => 'Modifica Percorso',
            'date' => array(),
            'message' => $message,
            'AllEsami' => $AllEsami,
            'percorsi' => $percorsi,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/prodotto/modifica_percorso', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per Assegnare i Master ai Discenti
    $router->addRoute('/discenti/gestione/{id}/master', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = "";
        $id_decodificato = base64_decode($id['id']);
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_POST['action']) && $_POST['action'] == 'insert') {
            $mid = $_POST['masterSelect'];

            $esamiMaster = $database->select("an_esamiMaster", "id", "mid =" . $mid);
            // Estrai solo la colonna id
            $ids = array_column($esamiMaster, 'id');

            // Codifica in JSON
            $esamiMasterJson = json_encode($ids);

            $data = [
                'did' => $id_decodificato,
                'pid' => $mid,
                'esame_json' => $esamiMasterJson,
                'tutoraggio' => $_POST['tutoraggio'] == 'on' ? 'si' : 'no'
            ];
            $database->insert('discente_esami', $data);
            $_SESSION['message'] = "insert";

            // Inserimento carriera sempre nuovo
            $dataCarriera = [
                'id_master' => $mid,
                'id_discente' => $id_decodificato,
                'tutoraggio' => $_POST['tutoraggio'] == 'on' ? 'si' : 'no',
                'tabella' => 'an_master',
            ];

            $database->insert("carriera", $dataCarriera);

            header("Location: /discenti/gestione/" . $id['id'] . "/master");
            exit;
        }

        $did = (int) $id_decodificato;

        /* 1) Dati anagrafici e master */
        $discente = $database->select("an_discenti", "nome, cognome", "id = {$did}");
        $tuttiIMaster = $database->select("an_master", "id, nome", "1");

        /* 2) Tutti gli id_master presenti in carriera per il discente */
        $masterIdsRaw = $database->query("
                SELECT id_master, tutoraggio
                FROM carriera
                WHERE id_discente = {$did} AND id_master IS NOT NULL
            ");
        $masterIds = array_map('intval', array_column($masterIdsRaw, 'id_master'));

        /* >>> NEW: mappa per sapere se per ciascun master il tutoraggio è 'si' o 'no' */
        $tutoraggioByMaster = [];
        foreach ($masterIdsRaw as $row) {
            $mid = (int) $row['id_master'];
            $flag = (isset($row['tutoraggio']) && strtolower(trim((string) $row['tutoraggio'])) === 'si') ? 'si' : 'no';
            // Se esistono più righe per lo stesso master, prevale 'si' se presente almeno una volta
            if (!isset($tutoraggioByMaster[$mid])) {
                $tutoraggioByMaster[$mid] = $flag;
            } elseif ($flag === 'si') {
                $tutoraggioByMaster[$mid] = 'si';
            }
        }

        /* 3) Dettagli dei master presenti (indicizzati per id) */
        $mastersById = [];
        if (!empty($masterIds)) {
            $inMaster = implode(',', $masterIds);
            $masters = $database->query("SELECT id, nome FROM an_master WHERE id IN ({$inMaster})");
            foreach ($masters as $m) {
                $mastersById[(int) $m['id']] = $m; // [ 12 => ['id'=>12,'nome'=>'...'], ... ]
            }
        }

        /* 4) Recupero tutti gli esami (JSON) del discente e li deduplica */
        $resEsami = $database->query("
            SELECT mid, esame_json, tutoraggio
            FROM discente_esami_master
            WHERE did = {$did}
        ");

        $allEsami = [];
        if (!empty($resEsami)) {
            foreach ($resEsami as $row) {
                if ($row['tutoraggio'] == 'no')
                    $ids = json_decode($row['esame_json'], true);
                if (is_array($ids)) {
                    foreach ($ids as $v)
                        $allEsami[] = (int) $v;
                }

            }
            $allEsami = array_values(array_unique($allEsami));
        }

        /* 5) Mappa esame -> master (mid) e gruppo per master */
        $esamiPerMaster = [];
        $examDetailsById = [];
        if (!empty($allEsami)) {
            $inEsami = implode(',', $allEsami);
            $rows = $database->query("
            SELECT id AS esame_id, mid, nome, codice
            FROM an_esamiMaster
            WHERE id IN ({$inEsami})
        ");
            foreach ($rows as $r) {
                $mid = (int) $r['mid'];
                $eid = (int) $r['esame_id'];
                $esamiPerMaster[$mid][] = $eid;
                $examDetailsById[$eid] = $r;
            }
        }

        /* 6) Costruisco il risultato PER OGNI id_master presente */
        $risultatoPerMaster = [];
        foreach ($masterIds as $mid) {
            $masterInfo = $mastersById[$mid] ?? ['id' => $mid, 'nome' => '(sconosciuto)'];
            $flagTut = $tutoraggioByMaster[$mid] ?? 'no';                 // 'si' | 'no'
            $labelTut = '(Tutoraggio: ' . $flagTut . ')';                  // stringa pronta per la stampa

            $risultatoPerMaster[] = [
                'master' => $masterInfo,     // ['id'=>..,'nome'=>..]
                'tutoraggio' => $flagTut,        // 'si' | 'no'
                'tutoraggio_label' => $labelTut,       // "(Tutoraggio: si/no)"
            ];
        }


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Assegna Master',
            'serp' => '/discenti/gestione/' . $id['id'] . '/master',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'mid' => $masterIds ?? [],
            'h1' => '',
            'h2' => 'Assegna Master',
            'date' => array(),
            'message' => $message,
            'tuttiIMaster' => $tuttiIMaster,
            'risultatoPerMaster' => $risultatoPerMaster,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/prodotto/master', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per Assegnare i Corsi di Laurea ai Discenti
    $router->addRoute('/discenti/gestione/{id}/certificazione', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_POST) && !empty($_POST)) {
            //echo'<pre>';print_r($_POST);;echo'</pre>';exit();
            list($idCertificazione, $nomeCert) = explode('||', $_POST['idCertificazione'], 2);

            $dataCertificazioni = [
                'did' => $id_decodificato,
                'id_cert' => $idCertificazione,
                'tutoraggio' => (isset($_POST['tutoraggio']) && $_POST['tutoraggio'] === 'on') ? 'si' : 'no',
                'riferimentoNomeCert' => $nomeCert,
            ];

            $dataCarriera = [
                'id_discente' => $id_decodificato,
                'id_certificazione' => $idCertificazione,
                'tutoraggio' => (isset($_POST['tutoraggio']) && $_POST['tutoraggio'] === 'on') ? 'si' : 'no',
                'tabella' => 'an_certificazioni',
            ];

            // Eseguo l'insert su discente_certificazioni
            $successCert = $database->insert('discente_certificazioni', $dataCertificazioni);
            // Esegue l'insert su carriera
            $successCarr = $database->insert('carriera', $dataCarriera);

            // Controlla che entrambi gli step siano andati a buon fine
            if ($successCert && $successCarr) {
                $_SESSION['message'] = "insertData";
            } else {
                $_SESSION['message'] = "error";
            }

            header('Location: /discenti/gestione/' . $id['id'] . '/certificazione');
        }

        // Recupera tutti i percorsi
        $query = "SELECT id, percorso FROM an_percorsi WHERE 1";
        $percorsi = $database->query($query);

        $discente = $database->select("an_discenti", "nome, cognome", "id= " . $id_decodificato);
        $certificazioni = $database->select("an_certificazioni", "*", "1");
        foreach ($certificazioni as &$c) {
            if ($c['tipoPagamento'] == 'Diretto') {
                $c['costo'] .= '€';
            } else {
                $c['costo'] = $c['percentuale'] . '%';
            }
        }

        $CertPresenti = $database->select("discente_certificazioni", "id, riferimentoNomeCert, tutoraggio, id_cert", "did= " . $id_decodificato);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Nuova Certificazione',
            'serp' => '/discenti/gestione/' . $id['id'] . '/certificazione',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'certificazioni' => $certificazioni,
            'CertPresenti' => $CertPresenti,
            'menu_discenti' => '1',
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Nuova Certificazione',
            'date' => array(),
            'message' => $message,
            'percorsi' => $percorsi,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/prodotto/certificazione', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per Assegnare gli esami singoli ai Discenti
    $router->addRoute('/discenti/gestione/{id}/esame_singolo', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_POST) && !empty($_POST)) {
            list($idEsame, $nomeEsame) = explode('||', $_POST['idEsame'], 2);

            $dataEsame = [
                'did' => $id_decodificato,
                'pid' => $idEsame,
                'esame_json' => json_encode([$idEsame]),
                'tutoraggio' => 'si',
                'fileVoti' => NULL
            ];

            $dataCarriera = [
                'id_discente' => $id_decodificato,
                'id_esame' => $idEsame,
                'tutoraggio' => 'si',
                'tabella' => 'an_esami',
            ];


            // Eseguo l'insert su discente_certificazioni
            $successCert = $database->insert('discente_esami', $dataEsame);
            // Esegue l'insert su carriera
            $successCarr = $database->insert('carriera', $dataCarriera);

            // Controlla che entrambi gli step siano andati a buon fine
            if ($successCert && $successCarr) {
                $_SESSION['message'] = "insertData";
            } else {
                $_SESSION['message'] = "error";
            }

            header('Location: /discenti/gestione/' . $id['id'] . '/esame_singolo');
        }

        $discente = $database->select("an_discenti", "nome, cognome", "id= " . $id_decodificato);
        $esami = $database->select("an_esami", "*", "1");

        $query = "SELECT DISTINCT id, tutoraggio, esame_json
                    FROM discente_esami
                    WHERE did= " . $id_decodificato;
        $discente_esameSingolo = $database->query($query);

        $esamiSingoli = [];

        foreach ($discente_esameSingolo as &$d) {
            // Decodifica il JSON e ottiene il conteggio degli esami
            $esameData = json_decode($d['esame_json'] ?? '', true);
            $esameCount = is_countable($esameData) ? count($esameData) : 0;

            // Usa un confronto (==) invece di un'assegnazione (=)
            if ($esameCount == 1) {
                // Accedi all'ID in modo corretto, assumendo che sia un array con un solo elemento
                $esameId = $esameData[0];
                $nome = $database->select("an_esami", "nome", "id = " . $esameId);

                // Aggiungi il nome dell'esame all'array, se serve
                $esamiSingoli[] = is_array($nome) ? ($nome[0]['nome'] ?? ($nome['nome'] ?? null)) : $nome;
            } else {
                continue;
            }
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Nuovo Esame Singolo',
            'serp' => '/discenti/gestione/' . $id['id'] . '/esame_singolo',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'esami' => $esami,
            'esamiSingoli' => $esamiSingoli,
            'discente_esameSingolo' => $discente_esameSingolo,
            'menu_discenti' => '1',
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Nuovo Esame Singolo',
            'date' => array(),
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/prodotto/esame_singolo', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Route per Assegnare i Pacchetti Esami ai Discenti
    $router->addRoute('/discenti/gestione/{id}/pacchetti_esami', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_POST) && !empty($_POST)) {
            $pacchettoid = $_POST['pacchettoEsami'];

            $esamiMaster = $database->select("an_pacchettiEsame", "id_esami", "id =" . $pacchettoid)[0];

            $data = [
                'did' => $id_decodificato,
                'pacchettoid' => $pacchettoid,
                'esame_json' => $esamiMaster['id_esami'],
                'tutoraggio' => $_POST['tutoraggio'] == 'on' ? 'si' : 'no'
            ];
            $database->insert('discente_pacchettoEsami', $data);

            $data = [
                'id_discente' => $id_decodificato,
                'id_pacchettoEsami' => $_POST['pacchettoEsami'],
                'tabella' => "an_pacchettiEsame",
                'tutoraggio' => isset($_POST['tutoraggio']) ? 'si' : 'no'
            ];

            $database->insert("carriera", $data);
            $_SESSION['message'] = "insertData";
            header('Location: /discenti/gestione/' . $id['id'] . '/pacchetti_esami');
        }

        // Recupera nome e cognome del discente
        $discente = $database->select(
            "an_discenti",
            "nome, cognome",
            "id = " . (int) $id_decodificato
        );

        // Recupera tutti i pacchetti esame disponibili
        $pacchettoEsami = $database->select("an_pacchettiEsame", "*");

        // Recupera tutti i pacchetti assegnati al discente con nome e tutoraggio
        $righe = $database->query("
                                        SELECT DISTINCT 
                                            a.id AS id_pacchettoEsami,
                                            a.nomePacchetto,
                                            c.tutoraggio
                                        FROM carriera c
                                        INNER JOIN an_pacchettiEsame a ON a.id = c.id_pacchettoEsami
                                        WHERE c.id_discente = " . (int) $id_decodificato . " 
                                        AND c.id_pacchettoEsami IS NOT NULL
                                        ORDER BY a.nomePacchetto ASC
                                    ");

        // Costruisci array strutturato con nome e tutoraggio
        $pacchettiAssegnati = [];
        $nomePacchetti = [];

        foreach ($righe as $r) {
            $pacchettiAssegnati[] = [
                'id_pacchettoEsami' => (int) $r['id_pacchettoEsami'],
                'nomePacchetto' => $r['nomePacchetto'],
                'tutoraggio' => $r['tutoraggio']
            ];

            // per stampa rapida nel template
            $nomePacchetti[] = $r['nomePacchetto'] . ' (' . $r['tutoraggio'] . ')';
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Area Discenti>Nuovo Pacchetto Esami',
            'serp' => '/discenti/gestione/' . $id['id'] . '/pacchetti_esami',
            'id' => $id['id'],
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'pacchettoEsami' => $pacchettoEsami,
            'pacchettiAssegnati' => $pacchettiAssegnati,
            'nomePacchetti' => $nomePacchetti,
            'revisione' => $revisione,
            'menu_discenti' => '1',
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Nuovo Pacchetto Esami',
            'date' => array(),
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/prodotto/pacchetti_esami', $content);

        // Restituisci l'output HTML generato
        return $result;
    });

    /*AREA DATE*/
    //Route per Assegnare la data ai Discenti
    $router->addRoute('/discenti/gestione/{id}/assegnazione_prodotti', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        //Recupero i prodotti dalla tabella carriera
        $prodotti = $database->select("carriera", "*", "id_discente= " . $id_decodificato . " AND id_certificazione IS NULL AND id_esame IS NULL");


        // 2) Colleziono gli ID per tabella
        $idsPercorsi = [];
        $idsMaster = [];
        $idsPacchettiEsami = [];

        foreach ($prodotti as $p) {
            if ($p['tabella'] === 'an_percorsi' && !empty($p['id_percorso'])) {
                $idsPercorsi[] = $p['id_percorso'];
            } elseif ($p['tabella'] === 'an_master' && !empty($p['id_master'])) {
                $idsMaster[] = $p['id_master'];
            } elseif ($p['tabella'] === 'an_pacchettiEsame' && !empty($p['id_pacchettoEsami'])) {
                $idsPacchettiEsami[] = $p['id_pacchettoEsami'];
            }
        }

        $idsPercorsi = array_values(array_unique($idsPercorsi));
        $idsMaster = array_values(array_unique($idsMaster));
        $idsPacchettiEsami = array_values(array_unique($idsPacchettiEsami));

        $Percorsi = [];
        $Master = [];
        $PacchettiEsami = [];

        // 3) Query batch per percorsi
        if (!empty($idsPercorsi)) {
            $in = implode(',', $idsPercorsi);
            // NB: se il campo "nome" del percorso ha un altro nome, cambia "percorso" di conseguenza
            $rows = $database->query("SELECT id, percorso AS nome FROM an_percorsi WHERE id IN ($in)");
            foreach ($rows as $r) {
                $Percorsi[] = ['id' => $r['id'], 'nome' => $r['nome']];
            }
        }

        // 4) Query batch per master
        if (!empty($idsMaster)) {
            $in = implode(',', $idsMaster);
            $rows = $database->query("SELECT id, nome FROM an_master WHERE id IN ($in)");
            foreach ($rows as $r) {
                $Master[] = ['id' => $r['id'], 'nome' => $r['nome']];
            }
        }

        // 4) Query batch per master
        if (!empty($idsPacchettiEsami)) {
            $in = implode(',', $idsPacchettiEsami);
            $rows = $database->query("SELECT id, nomePacchetto FROM an_pacchettiEsame WHERE id IN ($in)");
            foreach ($rows as $r) {
                $PacchettiEsami[] = ['id' => $r['id'], 'nome' => $r['nomePacchetto']];
            }
        }

        // Recupero dati anagrafici del discente
        $discente = $database->select('an_discenti', 'nome, cognome, numMatricola', "id = " . $id_decodificato);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Seleziona Prodotto',
            'serp' => '/discenti/gestione/' . $id['id'] . '/assegnazione_prodotti',
            'id' => $id['id'],
            'menu_discenti' => '1',
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'percorsi' => $Percorsi,
            'master' => $Master,
            'PacchettiEsami' => $PacchettiEsami,
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'prodotti' => $prodotti,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Seleziona Prodotto',
            'date' => array(),
            'discente' => $discente,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/assegnazione_prodotti', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Data Esami
    $router->addRoute('/discenti/gestione/{id}/data_esami', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }


        //Insert Date
        if (isset($_POST['tipoEsame']) && !empty($_POST['tipoEsame'])) {
            if ($_POST['tipoEsame'] == 'scritto') {
                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_esame' => $_POST['id_esame'],
                    'tipoEsame' => 'Scritto',
                    'data' => $_POST['dataEsame'],
                );
                $database->insert("scadenzaEsami", $data);
            } else {
                $idScadenzaEsame = $database->select("scadenzaEsami", "id", "id_discente = " . $id_decodificato . " AND id_esame = " . $_POST['id_esame'] . " AND data= '" . $_POST['dataScritto'] . "'");
                $idScadenzaEsame = $idScadenzaEsame[0];
                $dataToUpdate = array(
                    'motivazione' => 'Approfondimento Orale'
                );
                $where = "id= " . $idScadenzaEsame['id'];
                $database->update("scadenzaEsami", $dataToUpdate, $where);

                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_esame' => $_POST['id_esame'],
                    'tipoEsame' => 'Orale',
                    'data' => $_POST['dataEsame'],
                );
                $database->insert("scadenzaEsami", $data);
            }
            $_SESSION['message'] = "insertData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_esami?percorso=' . $_POST['percorso']);
        }
        //Insert Motivazione
        if (isset($_POST['updateEsame'])) {
            $dataEsame = array(
                'motivazione' => $_POST['motivazione'],
            );
            if (isset($_POST['voto'])) {
                $dataEsame['voto'] = $_POST['voto'];
            }
            if ($_POST['motivazione'] == "rinviato") {
                if ($_POST['te'] == "S") {
                    $dataM = $_POST['dataScritto'];
                } else {
                    $dataM = $_POST['dataOrale'];
                }

                $where = "id_esame= " . $_POST['id_esame'] . " AND id_discente= " . $id_decodificato . " AND data = '" . $dataM . "'";
                $database->update('scadenzaEsami', $dataEsame, $where);
            } else {
                $date_arr = [$_POST['dataScritto'], $_POST['dataOrale']];
                foreach ($date_arr as $date) {
                    if ($date === '') {
                        $date = null; // oppure continua il ciclo: continue;
                    }

                    if ($date !== null) {
                        $where = "id_esame = " . intval($_POST['id_esame']) .
                            " AND id_discente = " . intval($id_decodificato) .
                            " AND data = '" . $database->escapeString($date) . "'";
                        $database->update('scadenzaEsami', $dataEsame, $where);
                    }
                }
            }

            $_SESSION['message'] = "updateData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_esami?percorso=' . $_POST['percorso']);
        }
        //Insert Voto
        if (isset($_POST['voto']) && !empty($_POST['voto'])) {
            $data = array(
                'did' => $id_decodificato,
                'id_esame' => $_POST['id_esame'],
                'voto' => $_POST['voto'],
            );

            $database->insert('votiDiscenteEsami', $data);

            $data = array(
                'motivazione' => 'promosso',
                'voto' => $_POST['voto']
            );

            // Trova l'ID con valore massimo per lo stesso id_esame
            $idMax = $database->select("scadenzaEsami", "MAX(id) as max_id", "id_esame = " . intval($_POST['id_esame']));

            if (!empty($idMax[0]['max_id'])) {
                $where = "id = " . intval($idMax[0]['max_id']);
                $database->update('scadenzaEsami', ['motivazione' => 'promosso'], $where);
            }

            $_SESSION['message'] = "insertVoto";
            header('location: /discenti/gestione/' . $id['id'] . '/data_esami?percorso=' . $_POST['percorso']);
        }
        // Recupero dati anagrafici del discente
        $discente = $database->select('an_discenti', 'nome, cognome, numMatricola', "id = " . intval($id_decodificato));

        // Recupero lista ID esami associati al discente
        $esami_discente_raw = $database->select('discente_esami', "esame_json", "pid= " . $_GET['percorso'] . " AND did = " . intval($id_decodificato));
        $esami_for_template = [];

        if (!empty($esami_discente_raw)) {
            $exam_ids = json_decode($esami_discente_raw[0]['esame_json'], true);
            if (!empty($exam_ids) && is_array($exam_ids)) {
                // Inizializzazione array dati esami
                $dateEsamiScritto = [];
                $dateEsamiOrale = [];
                $motivazioniEsami = [];
                $voti = [];
                $dataVoti = [];


                foreach ($exam_ids as $exam_id) {
                    $exam_id = intval($exam_id);

                    // Recupero date, esiti, motivazioni
                    $dati_esame = $database->select(
                        'scadenzaEsami',
                        'tipoEsame, data, motivazione',
                        "id_esame = $exam_id AND id_discente = " . intval($id_decodificato) . " AND motivazione IS NULL OR motivazione ='Approfondimento Orale'"
                    );

                    foreach ($dati_esame as $dato) {
                        if ($dato['tipoEsame'] === 'Scritto') {
                            $dateEsamiScritto[$exam_id] = $dato['data'];
                        } elseif ($dato['tipoEsame'] === 'Orale') {
                            $dateEsamiOrale[$exam_id] = $dato['data'];
                        }

                        if (!empty($dato['motivazione'])) {
                            $motivazioniEsami[$exam_id] = $dato['motivazione'];
                        }
                    }

                    // Recupero voto (se presente)
                    $voto_dato = $database->select('votiDiscenteEsami', 'voto, data_insert_voto', "id_esame = $exam_id AND did = " . intval($id_decodificato));
                    if (!empty($voto_dato) && isset($voto_dato[0]['voto'])) {
                        $voti[$exam_id] = $voto_dato[0]['voto'];
                        $dataVoti[$exam_id] = substr($voto_dato[0]['data_insert_voto'], 0, 10);
                    }

                }


                // Recupero nomi esami
                $query = "SELECT id, nome FROM an_esami WHERE id IN (" . implode(',', array_map('intval', $exam_ids)) . ")";
                $exam_details = $database->query($query);

                // Mappa ID → Nome
                $exam_name_map = [];
                foreach ($exam_details as $detail) {
                    $exam_name_map[$detail['id']] = $detail['nome'];
                }

                // Composizione array finale per template
                foreach ($exam_ids as $exam_id) {
                    $esami_for_template[] = [
                        'id' => $exam_id,
                        'nome' => $exam_name_map[$exam_id] ?? 'Nome non trovato',
                        'data_scritto' => $dateEsamiScritto[$exam_id] ?? '',
                        'data_orale' => $dateEsamiOrale[$exam_id] ?? '',
                        'motivazione' => $motivazioniEsami[$exam_id] ?? '',
                        'voto' => $voti[$exam_id] ?? '',
                        'dataVoto' => $dataVoti[$exam_id] ?? ''
                    ];
                }
            }
        } else {
            header('location: /discenti/gestione/' . $id['id'] . '/assegna_percorso');
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Scadenza Esami',
            'serp' => '/discenti/gestione/' . $id['id'] . '/data_esami',
            'id' => $id['id'],
            'percorso' => $_GET['percorso'],
            'menu_discenti' => '1',
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Scadenza Esami',
            'date' => array(),
            'esami' => [['esami' => $esami_for_template]], // Wrap as the template expects $esami[0]['esami']
            'discente_id_encoded' => $id['id'], // Pass the encoded ID for the form
            'discente' => $discente,
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/assegnazione/assegna_esame', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Data Esami
    $router->addRoute('/discenti/gestione/{id}/data_pacchettiEsami', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {

        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Insert Date
        if (isset($_POST['tipoEsame']) && !empty($_POST['tipoEsame'])) {
            if ($_POST['tipoEsame'] == 'scritto') {
                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_esame' => $_POST['id_esame'],
                    'tipoEsame' => 'Scritto',
                    'data' => $_POST['dataEsame'],
                );
                $database->insert("scadenzaEsami", $data);
                header('location: /discenti/gestione/' . $id['id'] . '/data_pacchettiEsami?pacchetto=' . $_POST['pacchetto']);
            } else {
                $idScadenzaEsame = $database->select("scadenzaEsami", "id", "id_discente = " . $id_decodificato . " AND id_esame = " . $_POST['id_esame'] . " AND data= '" . $_POST['dataScritto'] . "'");
                $idScadenzaEsame = $idScadenzaEsame[0];
                $dataToUpdate = array(
                    'motivazione' => 'Approfondimento Orale'
                );
                $where = "id= " . $idScadenzaEsame['id'];
                $database->update("scadenzaEsami", $dataToUpdate, $where);

                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_esame' => $_POST['id_esame'],
                    'tipoEsame' => 'Orale',
                    'data' => $_POST['dataEsame'],
                );
                $database->insert("scadenzaEsami", $data);
            }
            $_SESSION['message'] = "insertData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_pacchettiEsami?pacchetto=' . $_POST['pacchetto']);
        }
        //Insert Motivazione
        if (isset($_POST['updateEsame'])) {
            $dataEsame = array(
                'motivazione' => $_POST['motivazione'],
            );
            if (isset($_POST['voto'])) {
                $dataEsame['voto'] = $_POST['voto'];
            }
            if ($_POST['motivazione'] == "rinviato") {
                if ($_POST['te'] == "S") {
                    $dataM = $_POST['dataScritto'];
                } else {
                    $dataM = $_POST['dataOrale'];
                }

                $where = "id_esame= " . $_POST['id_esame'] . " AND id_discente= " . $id_decodificato . " AND data = '" . $dataM . "'";
                $database->update('scadenzaEsami', $dataEsame, $where);
            } else {
                $date_arr = [$_POST['dataScritto'], $_POST['dataOrale']];
                foreach ($date_arr as $date) {
                    if ($date === '') {
                        $date = null; // oppure continua il ciclo: continue;
                    }

                    if ($date !== null) {
                        $where = "id_esame = " . intval($_POST['id_esame']) .
                            " AND id_discente = " . intval($id_decodificato) .
                            " AND data = '" . $database->escapeString($date) . "'";
                        $database->update('scadenzaEsami', $dataEsame, $where);
                    }
                }
            }

            $_SESSION['message'] = "updateData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_pacchettiEsami?pacchetto=' . $_POST['pacchetto']);
        }
        //Insert Voto
        if (isset($_POST['voto']) && !empty($_POST['voto'])) {
            $data = array(
                'did' => $id_decodificato,
                'id_esame' => $_POST['id_esame'],
                'voto' => $_POST['voto'],
            );

            $database->insert('votiDiscenteEsami', $data);

            $data = array(
                'motivazione' => 'promosso',
                'voto' => $_POST['voto']
            );

            // Trova l'ID con valore massimo per lo stesso id_esame
            $idMax = $database->select("scadenzaEsami", "MAX(id) as max_id", "id_esame = " . intval($_POST['id_esame']));

            if (!empty($idMax[0]['max_id'])) {
                $where = "id = " . intval($idMax[0]['max_id']);
                $database->update('scadenzaEsami', ['motivazione' => 'promosso'], $where);
            }

            $_SESSION['message'] = "insertVoto";
            header('location: /discenti/gestione/' . $id['id'] . '/data_pacchettiEsami?pacchetto=' . $_POST['pacchetto']);
        }
        // Recupero dati anagrafici del discente
        $discente = $database->select('an_discenti', 'nome, cognome, numMatricola', "id = " . intval($id_decodificato));
        if (isset($_GET['pacchetto'])) {
            // Recupero lista ID esami associati al discente
            $esami_discente_raw = $database->select('discente_pacchettoEsami', "esame_json", "pacchettoid= " . $_GET['pacchetto'] . " AND did = " . intval($id_decodificato));
            $esami_for_template = [];

            if (!empty($esami_discente_raw)) {
                $exam_ids = json_decode($esami_discente_raw[0]['esame_json'], true);
                if (!empty($exam_ids) && is_array($exam_ids)) {
                    // Inizializzazione array dati esami
                    $dateEsamiScritto = [];
                    $dateEsamiOrale = [];
                    $motivazioniEsami = [];
                    $voti = [];
                    $dataVoti = [];


                    foreach ($exam_ids as $exam_id) {
                        $exam_id = intval($exam_id);

                        // Recupero date, esiti, motivazioni
                        $dati_esame = $database->select(
                            'scadenzaEsami',
                            'tipoEsame, data, motivazione',
                            "id_esame = $exam_id AND id_discente = " . intval($id_decodificato) . " AND motivazione IS NULL OR motivazione ='Approfondimento Orale'"
                        );

                        foreach ($dati_esame as $dato) {
                            if ($dato['tipoEsame'] === 'Scritto') {
                                $dateEsamiScritto[$exam_id] = $dato['data'];
                            } elseif ($dato['tipoEsame'] === 'Orale') {
                                $dateEsamiOrale[$exam_id] = $dato['data'];
                            }

                            if (!empty($dato['motivazione'])) {
                                $motivazioniEsami[$exam_id] = $dato['motivazione'];
                            }
                        }

                        // Recupero voto (se presente)
                        $voto_dato = $database->select('votiDiscenteEsami', 'voto, data_insert_voto', "id_esame = $exam_id AND did = " . intval($id_decodificato));
                        if (!empty($voto_dato) && isset($voto_dato[0]['voto'])) {
                            $voti[$exam_id] = $voto_dato[0]['voto'];
                            $dataVoti[$exam_id] = substr($voto_dato[0]['data_insert_voto'], 0, 10);
                        }

                    }


                    // Recupero nomi esami
                    $query = "SELECT id, nome FROM an_esami WHERE id IN (" . implode(',', array_map('intval', $exam_ids)) . ")";
                    $exam_details = $database->query($query);

                    // Mappa ID → Nome
                    $exam_name_map = [];
                    foreach ($exam_details as $detail) {
                        $exam_name_map[$detail['id']] = $detail['nome'];
                    }

                    // Composizione array finale per template
                    foreach ($exam_ids as $exam_id) {
                        $esami_for_template[] = [
                            'id' => $exam_id,
                            'nome' => $exam_name_map[$exam_id] ?? 'Nome non trovato',
                            'data_scritto' => $dateEsamiScritto[$exam_id] ?? '',
                            'data_orale' => $dateEsamiOrale[$exam_id] ?? '',
                            'motivazione' => $motivazioniEsami[$exam_id] ?? '',
                            'voto' => $voti[$exam_id] ?? '',
                            'dataVoto' => $dataVoti[$exam_id] ?? ''
                        ];
                    }
                }
            } else {
                header('location: /discenti/gestione/' . $id['id'] . '/data_pacchettiEsami');
            }
        } else {
            header('location: /discenti/gestione/' . $id['id'] . '/pacchetti_esami');
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Scadenza Esami (Pacchetto)',
            'serp' => '/discenti/gestione/' . $id['id'] . '/data_pacchettiEsami',
            'id' => $id['id'],
            'pacchetto' => $_GET['pacchetto'],
            'menu_discenti' => '1',
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Scadenza Esami',
            'date' => array(),
            'esami' => [['esami' => $esami_for_template]], // Wrap as the template expects $esami[0]['esami']
            'discente_id_encoded' => $id['id'], // Pass the encoded ID for the form
            'discente' => $discente,
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/assegnazione/data_pacchettiEsame', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Data Esami Singoli
    $router->addRoute('/discenti/gestione/{id}/data_esame_singolo', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);
            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        //Insert Date
        if (isset($_POST['tipoEsame']) && !empty($_POST['tipoEsame'])) {
            //echo'<pre>';print_r($_POST);echo'</pre>';exit();
            if ($_POST['tipoEsame'] == 'scritto') {
                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_esame' => $_POST['idEsameSingolo'],
                    'tipoEsame' => 'Scritto',
                    'data' => $_POST['dataEsame'],
                );
                $database->insert("scadenzaEsami", $data);
            } else {
                $idScadenzaEsame = $database->select("scadenzaEsami", "id", "id_discente = " . $id_decodificato . " AND id_esame = " . $_POST['id_esame'] . " AND data= '" . $_POST['dataScritto'] . "'");
                $idScadenzaEsame = $idScadenzaEsame[0];
                $dataToUpdate = array(
                    'motivazione' => 'Approfondimento Orale'
                );
                $where = "id= " . $idScadenzaEsame['id'];
                $database->update("scadenzaEsami", $dataToUpdate, $where);

                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_esame' => $_POST['idEsameSingolo'],
                    'tipoEsame' => 'Orale',
                    'data' => $_POST['dataEsame'],
                );
                $database->insert("scadenzaEsami", $data);
            }
            $_SESSION['message'] = "insertData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_esame_singolo');
        }
        //Insert Motivazione
        if (isset($_POST['updateEsame'])) {
            $dataEsame = array(
                'motivazione' => $_POST['motivazione'],
            );
            if (isset($_POST['voto'])) {
                $dataEsame['voto'] = $_POST['voto'];
            }
            if ($_POST['motivazione'] == "rinviato") {
                if ($_POST['te'] == "S") {
                    $dataM = $_POST['dataScritto'];
                } else {
                    $dataM = $_POST['dataOrale'];
                }
                $where = "id_esame= " . $_POST['idEsameSingolo'] . " AND id_discente= " . $id_decodificato . " AND data = '" . $dataM . "'";
                $database->update('scadenzaEsami', $dataEsame, $where);
            } else {
                $date_arr = [$_POST['dataScritto'], $_POST['dataOrale']];
                foreach ($date_arr as $date) {
                    if ($date === '') {
                        $date = null; // oppure continua il ciclo: continue;
                    }

                    if ($date !== null) {
                        $where = "id_esame = " . intval($_POST['idEsameSingolo']) .
                            " AND id_discente = " . intval($id_decodificato) .
                            " AND data = '" . $database->escapeString($date) . "'";
                        $database->update('scadenzaEsami', $dataEsame, $where);
                    }
                }
            }

            $_SESSION['message'] = "updateData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_esame_singolo');
        }
        //Insert Voto
        if (isset($_POST['voto']) && !empty($_POST['voto'])) {

            $data = array(
                'did' => $id_decodificato,
                'id_esame' => $_POST['idEsameSingolo'],
                'voto' => $_POST['voto'],
            );

            $database->insert('votiDiscenteEsami', $data);

            $data = array(
                'motivazione' => 'promosso',
                'voto' => $_POST['voto']
            );

            // Trova l'ID con valore massimo per lo stesso id_esame
            $idMax = $database->select("scadenzaEsami", "MAX(id) as max_id", "id_esame = " . intval($_POST['id_esame']));

            if (!empty($idMax[0]['max_id'])) {
                $where = "id = " . intval($idMax[0]['max_id']);
                $database->update('scadenzaEsami', ['motivazione' => 'promosso'], $where);
            }

            $_SESSION['message'] = "insertVoto";
            header('location: /discenti/gestione/' . $id['id'] . '/data_esame_singolo');
        }

        // Recupero dati anagrafici del discente
        $discente = $database->select('an_discenti', 'nome, cognome, numMatricola', "id = " . intval($id_decodificato));
        $esami_discente_raw = $database->select('discente_esami', 'esame_json', "did = " . intval($id_decodificato));

        if (!empty($esami_discente_raw)) {
            $exam_ids = json_decode($esami_discente_raw[0]['esame_json'], true);
            $count_id = count($exam_ids);

            if (($count_id >= 1) && !empty($exam_ids) && is_array($exam_ids)) {
                // Inizializzazione array dati esami
                $dateEsamiScritto = [];
                $dateEsamiOrale = [];
                $motivazioniEsami = [];
                $voti = [];
                $dataVoti = [];


                foreach ($exam_ids as $exam_id) {
                    $exam_id = intval($exam_id);

                    // Recupero date, esiti, motivazioni
                    $dati_esame = $database->select(
                        'scadenzaEsami',
                        'tipoEsame, data, motivazione',
                        "id_esame = $exam_id AND id_discente = " . intval($id_decodificato) . " AND motivazione IS NULL OR motivazione ='Approfondimento Orale'"
                    );

                    foreach ($dati_esame as $dato) {
                        if ($dato['tipoEsame'] === 'Scritto') {
                            $dateEsamiScritto[$exam_id] = $dato['data'];
                        } elseif ($dato['tipoEsame'] === 'Orale') {
                            $dateEsamiOrale[$exam_id] = $dato['data'];
                        }

                        if (!empty($dato['motivazione'])) {
                            $motivazioniEsami[$exam_id] = $dato['motivazione'];
                        }
                    }

                    // Recupero voto (se presente)
                    $voto_dato = $database->select('votiDiscenteEsami', 'voto, data_insert_voto', "id_esame = $exam_id AND did = " . intval($id_decodificato));
                    if (!empty($voto_dato) && isset($voto_dato[0]['voto'])) {
                        $voti[$exam_id] = $voto_dato[0]['voto'];
                        $dataVoti[$exam_id] = substr($voto_dato[0]['data_insert_voto'], 0, 10);
                    }

                }

                // Recupero nomi esami
                $query = "SELECT id, nome FROM an_esami WHERE id IN (" . implode(',', array_map('intval', $exam_ids)) . ")";
                $exam_details = $database->query($query);

                // Mappa ID → Nome
                $exam_name_map = [];
                foreach ($exam_details as $detail) {
                    $exam_name_map[$detail['id']] = $detail['nome'];
                }

                // Composizione array finale per template
                foreach ($exam_ids as $exam_id) {
                    $esami_for_template[] = [
                        'id' => $exam_id,
                        'nome' => $exam_name_map[$exam_id] ?? 'Nome non trovato',
                        'data_scritto' => $dateEsamiScritto[$exam_id] ?? '',
                        'data_orale' => $dateEsamiOrale[$exam_id] ?? '',
                        'motivazione' => $motivazioniEsami[$exam_id] ?? '',
                        'voto' => $voti[$exam_id] ?? '',
                        'dataVoto' => $dataVoti[$exam_id] ?? ''
                    ];
                }
            }

            if (empty($exam_ids)) {
                header('location: /discenti/gestione/' . $id['id'] . '/esame_singolo');
                exit();
            }
        } else {
            header('location: /discenti/gestione/' . $id['id'] . '/esame_singolo');
        }


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Scadenza Esame Singolo',
            'serp' => '/discenti/gestione/' . $id['id'] . '/data_esame_singolo',
            'id' => $id['id'],
            'menu_discenti' => '1',
            'menu' => $menu['discenti'],
            'esami' => $esami_for_template,
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Scadenza Esame Singolo',
            'date' => array(),
            'discente' => $discente,
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/assegnazione/data_esame_singolo', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Data Esami Master
    $router->addRoute('/discenti/gestione/{id}/data_master', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Insert Date
        if (isset($_POST['tipoEsame']) && !empty($_POST['tipoEsame'])) {
            if ($_POST['tipoEsame'] == 'scritto') {
                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_esame' => $_POST['id_esame'],
                    'tipoEsame' => 'Scritto',
                    'data' => $_POST['dataEsame'],
                );
                $database->insert("scadenzaEsamiMaster", $data);
            } else {
                $idScadenzaEsame = $database->select("scadenzaEsamiMaster", "id", "id_discente = " . $id_decodificato . " AND id_esame = " . $_POST['id_esame'] . " AND data= '" . $_POST['dataScritto'] . "'");
                $idScadenzaEsame = $idScadenzaEsame[0];
                $dataToUpdate = array(
                    'motivazione' => 'Approfondimento Orale'
                );
                $where = "id= " . $idScadenzaEsame['id'];
                $database->update("scadenzaEsamiMaster", $dataToUpdate, $where);

                $data = array(
                    'id_discente' => $id_decodificato,
                    'id_esame' => $_POST['id_esame'],
                    'tipoEsame' => 'Orale',
                    'data' => $_POST['dataEsame'],
                );
                $database->insert("scadenzaEsamiMaster", $data);
            }
            $_SESSION['message'] = "insertData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_master');
        }
        //Insert Motivazione
        if (isset($_POST['updateEsame'])) {
            $dataEsame = array(
                'motivazione' => $_POST['motivazione'],
            );
            if (isset($_POST['voto'])) {
                $dataEsame['voto'] = $_POST['voto'];
            }
            if ($_POST['motivazione'] == "rinviato") {
                if ($_POST['te'] == "S") {
                    $dataM = $_POST['dataScritto'];
                } else {
                    $dataM = $_POST['dataOrale'];
                }

                $where = "id_esame= " . $_POST['id_esame'] . " AND id_discente= " . $id_decodificato . " AND data = '" . $dataM . "'";
                $database->update('scadenzaEsamiMaster', $dataEsame, $where);
            } else {
                $date_arr = [$_POST['dataScritto'], $_POST['dataOrale']];
                foreach ($date_arr as $date) {
                    if ($date === '') {
                        $date = null; // oppure continua il ciclo: continue;
                    }

                    if ($date !== null) {
                        $where = "id_esame = " . intval($_POST['id_esame']) .
                            " AND id_discente = " . intval($id_decodificato) .
                            " AND data = '" . $database->escapeString($date) . "'";
                        $database->update('scadenzaEsamiMaster', $dataEsame, $where);
                    }
                }
            }

            $_SESSION['message'] = "updateData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_master');
        }
        //Insert Voto
        if (isset($_POST['voto']) && !empty($_POST['voto'])) {
            $data = array(
                'did' => $id_decodificato,
                'id_esame' => $_POST['id_esame'],
                'voto' => $_POST['voto'],
            );

            $database->insert('votiDiscenteEsamiMaster', $data);

            $data = array(
                'motivazione' => 'promosso',
                'voto' => $_POST['voto']
            );

            // Trova l'ID con valore massimo per lo stesso id_esame
            $idMax = $database->select("scadenzaEsamiMaster", "MAX(id) as max_id", "id_esame = " . intval($_POST['id_esame']));

            if (!empty($idMax[0]['max_id'])) {
                $where = "id = " . intval($idMax[0]['max_id']);
                $database->update('scadenzaEsamiMaster', ['motivazione' => 'promosso'], $where);
            }

            $_SESSION['message'] = "insertVoto";
            header('location: /discenti/gestione/' . $id['id'] . '/data_master');
        }

        // Recupero dati anagrafici del discente
        $discente = $database->select('an_discenti', 'nome, cognome, numMatricola', "id = " . intval($id_decodificato));
        // Recupero lista ID esami associati al discente
        $esami_discente_raw = $database->select('discente_esami_master', "esame_json", "mid= " . $_GET['master'] . " && did = " . intval($id_decodificato));

        $esami_for_template = [];

        if (!empty($esami_discente_raw)) {
            $exam_ids = json_decode($esami_discente_raw[0]['esame_json'], true);
            if (!empty($exam_ids) && is_array($exam_ids)) {
                // Inizializzazione array dati esami
                $dateEsamiScritto = [];
                $dateEsamiOrale = [];
                $motivazioniEsami = [];
                $voti = [];
                $dataVoti = [];


                foreach ($exam_ids as $exam_id) {
                    $exam_id = intval($exam_id);

                    // Recupero date, esiti, motivazioni
                    $dati_esame = $database->select(
                        'scadenzaEsamiMaster',
                        'tipoEsame, data, motivazione',
                        "id_esame = $exam_id AND id_discente = " . intval($id_decodificato) . " AND motivazione IS NULL OR motivazione ='Approfondimento Orale'"
                    );

                    foreach ($dati_esame as $dato) {
                        if ($dato['tipoEsame'] === 'Scritto') {
                            $dateEsamiScritto[$exam_id] = $dato['data'];
                        } elseif ($dato['tipoEsame'] === 'Orale') {
                            $dateEsamiOrale[$exam_id] = $dato['data'];
                        }

                        if (!empty($dato['motivazione'])) {
                            $motivazioniEsami[$exam_id] = $dato['motivazione'];
                        }
                    }

                    // Recupero voto (se presente)
                    $voto_dato = $database->select('votiDiscenteEsamiMaster', 'voto, data_insert_voto', "id_esame = $exam_id AND did = " . intval($id_decodificato));
                    if (!empty($voto_dato) && isset($voto_dato[0]['voto'])) {
                        $voti[$exam_id] = $voto_dato[0]['voto'];
                        $dataVoti[$exam_id] = substr($voto_dato[0]['data_insert_voto'], 0, 10);
                    }

                }


                // Recupero nomi esami
                $query = "SELECT id, nome FROM an_esamiMaster WHERE id IN (" . implode(',', array_map('intval', $exam_ids)) . ")";
                $exam_details = $database->query($query);

                // Mappa ID → Nome
                $exam_name_map = [];
                foreach ($exam_details as $detail) {
                    $exam_name_map[$detail['id']] = $detail['nome'];
                }

                // Composizione array finale per template
                foreach ($exam_ids as $exam_id) {
                    $esami_for_template[] = [
                        'id' => $exam_id,
                        'nome' => $exam_name_map[$exam_id] ?? 'Nome non trovato',
                        'data_scritto' => $dateEsamiScritto[$exam_id] ?? '',
                        'data_orale' => $dateEsamiOrale[$exam_id] ?? '',
                        'motivazione' => $motivazioniEsami[$exam_id] ?? '',
                        'voto' => $voti[$exam_id] ?? '',
                        'dataVoto' => $dataVoti[$exam_id] ?? ''
                    ];
                }
            }
        } else {
            header('location: /discenti/gestione/' . $id['id'] . '/master');
        }
        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Scadenza Esami',
            'serp' => '/discenti/gestione/' . $id['id'] . '/data_esami',
            'id' => $id['id'],
            'menu_discenti' => '1',
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Scadenza Esami',
            'date' => array(),
            'esami' => [['esami' => $esami_for_template]], // Wrap as the template expects $esami[0]['esami']
            'discente_id_encoded' => $id['id'], // Pass the encoded ID for the form
            'discente' => $discente,
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/assegnazione/assegna_data_master', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Data Certificazioni
    $router->addRoute('/discenti/gestione/{id}/data_certificazioni', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);
            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = "";
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Insert Date
        if (isset($_POST['idDataCertificazioni']) && !empty($_POST['idDataCertificazioni'])) {
            $where = "id= " . $_POST['idDataCertificazioni'];

            $data = array(
                'dataSvolgimento' => $_POST['dataCertificazione'],
            );
            $database->update("discente_certificazioni", $data, $where);

            $_SESSION['message'] = "insertData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_certificazioni');
        }
        //Bocciato
        if (isset($_POST['bocciatoCert'])) {

            $certPresente = $database->select("discente_certificazioni", "*", "id= " . $_POST['idCertificazione']);
            $certPresente = $certPresente[0];

            $dataEsame = array(
                'esito' => 'bocciato'
            );

            $where = "id= " . $_POST['idCertificazione'];
            $database->update('discente_certificazioni', $dataEsame, $where);

            $DataToInsert = array(
                'did' => $id_decodificato,
                'id_cert' => $certPresente['id_cert'],
                'riferimentoNomeCert' => $certPresente['riferimentoNomeCert'],
                'voto' => NULL,
                'esito' => NULL,
                'tutoraggio' => $certPresente['tutoraggio'],
                'prezzo' => $certPresente['prezzo'],
                'prezzoTutoraggio' => $certPresente['prezzoTutoraggio'],
                'prezzoTotale' => $certPresente['prezzoTotale'],
                'data' => $certPresente['data'],
                'dataSvolgimento' => NULL
            );
            $database->insert("discente_certificazioni", $DataToInsert);

            $_SESSION['message'] = "updateData";
            header('location: /discenti/gestione/' . $id['id'] . '/data_certificazioni');
        }
        //Promosso
        if (isset($_POST['voto']) && !empty($_POST['voto'])) {

            $data = array(
                'voto' => $_POST['voto'],
                'esito' => 'Promosso'
            );

            $where = "id= " . $_POST['idCertificazione'];

            print_r($_POST);
            echo '<hr>';
            echo $where . '<hr>';
            print_r($data);
            echo '<hr>';

            $database->update('discente_certificazioni', $data, $where);

            $_SESSION['message'] = "insertVoto";
            header('location: /discenti/gestione/' . $id['id'] . '/data_certificazioni');
        }

        $query = "SELECT id, riferimentoNomeCert, voto, dataSvolgimento, esito FROM discente_certificazioni WHERE did= " . $id_decodificato .
            " AND esito IS NULL OR esito!='bocciato'";
        $certificazioni = $database->query($query);

        if (empty($certificazioni)) {
            header('location: /discenti/gestione/' . $id['id'] . '/certificazione');
            exit();
        }

        // Recupero dati anagrafici del discente
        $discente = $database->select('an_discenti', 'nome, cognome, numMatricola', "id = " . intval($id_decodificato));

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Scadenza Certificazione',
            'serp' => '/discenti/gestione/' . $id['id'] . '/data_certificazioni',
            'id' => $id['id'],
            'menu_discenti' => '1',
            'menu' => $menu['discenti'],
            'nome' => $discente[0]['nome'],
            'cognome' => $discente[0]['cognome'],
            'revisione' => $revisione,
            'certificazioni' => $certificazioni,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Scadenza Certificazione',
            'date' => array(),
            'discente' => $discente,
            'message' => $message,
            'content' => ""
        ];

        // Utilizza la funzione render per generare l'output HTML
        $result = render('discenti/assegnazione/data_certificazioni', $content);

        // Restituisci l'output HTML generato
        return $result;
    });
    //Gestione Pagamenti discente
    $router->addRoute('/discenti/gestione/{id}/pagamento/{idRata}', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $id_decodificato = base64_decode($id['id']);
        $idRata_decodificata = base64_decode($id['idRata']);

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);
            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        //Gestione caricamento file
        if (isset($_POST['idRata'], $_FILES['filePagamento']) && $_FILES['filePagamento']['error'] === UPLOAD_ERR_OK) {

            $uploadBaseDir = __DIR__ . '/../uploads/pagamentoDiscenti'; // La cartella 'uploads' sarà creata nella stessa directory dello script
            $nomeSubdirectory = (string) $id['idRata']; // Usa l'ID del discente decodificato per la sottocartella
            $fileInputNameInHtml = 'filePagamento'; // Deve corrispondere all'attributo 'name' dell'input file HTML

            $result = salvaFile($fileInputNameInHtml, $nomeSubdirectory, $uploadBaseDir);

            $data = array(
                'dataPagamentoRata' => date('Y-m-d'),
                'pagato' => $_POST['pagato'],
                'file' => $result['url'] // Usa l'URL restituito da salvaFile
            );
            $where = "id = " . $idRata_decodificata;
            $database->update("scadenzeRate", $data, $where);
            $_SESSION['message'] = "insertRata";
            header('location: /scadenze_rate');
            exit();

        } elseif (isset($_FILES['filePagamento']) && $_FILES['filePagamento']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['filePagamento']['error'] !== UPLOAD_ERR_OK) {
            // Gestisce altri errori di upload (es. file troppo grande)
            $_SESSION['message'] = "erroreCaricamentoFile";
            header('Location: /discenti/gestione/' . $id['id'] . '/pagamento/' . $id['idRata']);
            exit();
        }

        $queryDiscente = "SELECT nome, cognome, email, telefono, cellulare, numMatricola 
                  FROM an_discenti WHERE id = " . (int) $id_decodificato;
        $discente = $database->query($queryDiscente);

        $queryRata = "SELECT id, id_rateScadenze, importoRata, data_scadenza, pagato 
              FROM scadenzeRate WHERE id = " . (int) $idRata_decodificata;
        $dettagliRata = $database->query($queryRata);

        $queryAccordo = "SELECT importoTotale, acconto, riferimentoNomeProd, riferimentoTabellaProd 
                 FROM rateDiscente WHERE id = " . (int) $dettagliRata[0]['id_rateScadenze'];
        $accordoPagamento = $database->query($queryAccordo);


        //Alterazione del campo riferimentoTabellaProd per poterlo stampare facilmente
        if (!empty($accordoPagamento)) {
            $accordo = $accordoPagamento[0];

            // Decodifica JSON in array
            $accordo['riferimentoNomeProd'] = json_decode($accordo['riferimentoNomeProd'] ?? '[]', true);
            $accordo['riferimentoTabellaProd'] = json_decode($accordo['riferimentoTabellaProd'] ?? '[]', true);

            // Mappa tabella → etichetta leggibile
            $mappaTabelle = [
                'an_esami' => 'Esame Singolo',
                'an_percorsi' => 'Percorso di Laurea',
                'an_master' => 'Master',
                'an_certificazioni' => 'Certificazione',
            ];

            $accordo['riferimentoTabellaProd'] = array_map(function ($tabella) use ($mappaTabelle) {
                return $mappaTabelle[$tabella] ?? 'Altro';
            }, (array) $accordo['riferimentoTabellaProd']);

            if (is_array($accordo['riferimentoNomeProd']) && in_array('tutoraggio', $accordo['riferimentoNomeProd'], true)) {
                $accordo['riferimentoTabellaProd'][] = 'Tutoraggio';
            }

            // Inserisco nell’array discente
            $discente = $discente[0];
            $discente['accordo'] = $accordo;
        }


        $discente['rata'] = $dettagliRata[0];

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Dettagli Pagamento',
            'serp' => '/discenti/gestione/' . $id['id'] . '/pagamento/' . $id['idRata'],
            'menu' => $menu['dashboard'],
            'id' => $id['id'],
            'nome' => $discente['nome'],
            'cognome' => $discente['cognome'],
            'menu_discenti' => '1',
            'revisione' => $revisione,
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Dettagli Pagamento',
            'date' => array(),
            'discente' => $discente,
            'dettagliRata' => $dettagliRata, // Pass the specific installment details
            'accordoPagamento' => $accordoPagamento, // Pass the main agreement details
            'message' => $message,
            'content' => ""
        ];

        $message = "";

        // Utilizza la funzione render per generare l'output HTML
        $result = render('notifiche/pagamento', $content);

        // Restituisci l'output HTML generato
        return $result;
    });

    //AREA SCADENZE
    //Area scadenze esame
    $router->addRoute('/scadenza_esami', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        if ($_SESSION['utente']['ruolo'] == 'Point' || $_SESSION['utente']['ruolo'] == 'Segnalatore') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /dashboard');
            exit();
        }
        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_POST['cercaDiscenteEsame']) && !empty($_POST['cercaDiscenteEsame'])) {
            $where = "id_discente= " . $_POST['cercaDiscenteEsame'];
        } else {
            $oggi = new DateTime(); // oggi
            $inizio_mese = $oggi->format('Y-m-01');
            $fine_mese = $oggi->format('Y-m-t'); // "t" restituisce l'ultimo giorno del mese

            if ($_SESSION['utente']['ruolo'] == 'Discente') {
                $idDiscente = $database->select("an_discenti", "id", "idUser= " . $_SESSION['utente']['id']);
                $idDiscente = $idDiscente[0];
                $where = "id_discente = " . $idDiscente['id'];
            } else {
                $where = "data BETWEEN '$inizio_mese' AND '$fine_mese'";
            }
        }

        $events = $database->select("scadenzaEsami", "id_esame, id_discente, data, motivazione, voto", $where);
        if (!empty($events)) {
            foreach ($events as &$s) {
                // Recupero dati discente ed esame
                if ($_SESSION['utente']['ruolo'] == 'Discente') {
                    $nomeDiscente = $database->select("an_discenti", "nome, cognome", "id= " . $idDiscente['id']);
                } else {
                    $nomeDiscente = $database->select("an_discenti", "nome, cognome", "id= " . $s['id_discente']);
                }

                $esame = $database->select("an_esami", "nome, cfu, codice", "id= " . $s['id_esame']);

                $nome = $nomeDiscente[0]['nome'];
                $cognome = $nomeDiscente[0]['cognome'];
                $s['codice'] = $esame[0]['codice'];
                $s['esame'] = $esame[0]['nome'];
                $s['cfu'] = $esame[0]['cfu'];
                $s['nomeDiscente'] = $nome . " " . $cognome;

            }
        }

        $query = "SELECT id, nome, cognome FROM an_discenti WHERE 1";
        $discente = $database->query($query);

        //echo'<pre>';print_r($events);echo'</pre>';exit();

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Gestione Scadenze',
            'serp' => '/scadenza_esami',
            'menu' => $menu['scadenza_esami'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Scadenze Esami',
            'revisione' => $revisione,
            'events' => $events,
            'message' => $message,
            'discente' => $discente,
            'date' => array(),
            'content' => ''
        ];

        $result = render('notifiche/scadenze_esami', $content);
        return $result;
    });
    //Area scadenze
    $router->addRoute('/scadenze_rate', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        if (isset($_POST['cercaDiscenteRata']) && !empty($_POST['cercaDiscenteRata'])) {
            $where = "id_discente= " . $_POST['cercaDiscenteRata'];
        } else {
            $oggi = new DateTime(); // oggi
            $inizio_mese = $oggi->format('Y-m-01');
            $fine_mese = $oggi->format('Y-m-t'); // "t" restituisce l'ultimo giorno del mese

            if ($_SESSION['utente']['ruolo'] == 'Discente') {
                $idDiscente = $database->select("an_discenti", "id", "idUser= " . $_SESSION['utente']['id']);
                $idDiscente = $idDiscente[0];
                $where = "id_discente= " . $idDiscente['id'];
            } else {
                $where = " pagato IS NULL AND data_scadenza BETWEEN '$inizio_mese' AND '$fine_mese'";
            }
        }

        $events = $database->select("scadenzeRate", "*", $where);

        if (!empty($events)) {
            $oggi = new DateTime(); // assicurati che sia definito fuori dal foreach
            foreach ($events as &$s) {
                if ($_SESSION['utente']['ruolo'] != 'Discente') {
                    $nomeDiscente = $database->select("an_discenti", "nome, cognome", "id= " . $s['id_discente']);
                } else {
                    $nomeDiscente = $database->select("an_discenti", "nome, cognome", "id= " . $idDiscente['id']);
                }
                //Prendo la tipologia, quindi e-campus o tutoraggio
                $tipologia = $database->select("rateDiscente", "tipoPagamento", "id= " . $s['id_rateScadenze'])[0];
                $s['tipologia'] = $tipologia['tipoPagamento'];

                $nome = $nomeDiscente[0]['nome'];
                $cognome = $nomeDiscente[0]['cognome'];

                $s['nomeDiscente'] = $nome . ' ' . $cognome;
                $s['data'] = $s['data_scadenza'];
                $s['description'] = "Scadenza della rata del discente " . $nome . " " . $cognome . " dall'importo di €" . $s['importoRata'];
                if ($s['pagato'] == NULL) {
                    $s['pagato'] = 'Non Pagato';
                } else {
                    $s['pagato'] = 'Pagato';
                }
            }
        }

        //echo'<pre>';print_r($events);;echo'</pre>';exit();
        $query = "SELECT id, nome, cognome FROM an_discenti WHERE 1";
        $discente = $database->query($query);

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Gestione Scadenze',
            'serp' => '/scadenze',
            'menu' => $menu['scadenza_esami'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Scadenze Rate',
            'revisione' => $revisione,
            'discente' => $discente,
            'events' => $events,
            'message' => $message,
            'date' => array(),
            'content' => ''
        ];

        $result = render('notifiche/scadenze_rate', $content);
        return $result;
    });
    //Area calendario esame
    $router->addRoute('/calendario_esami', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        if ($_SESSION['utente']['ruolo'] == 'Point' || $_SESSION['utente']['ruolo'] == 'Segnalatore') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /dashboard');
            exit();
        }

        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        $oggi = new DateTime(); // Prende la data e ora attuali
        $data_oggi = $oggi->format('Y-m-d');

        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            $idDiscente = $database->select("an_discenti", "id", "idUser= " . $_SESSION['utente']['id']);
            $idDiscente = $idDiscente[0];
            $where = "id_discente= " . $idDiscente['id'] . " AND motivazione IS NULL AND data>='" . $data_oggi . "'";
        } else {
            $where = " motivazione IS NULL AND data>='" . $data_oggi . "'";
        }

        $events = $database->select("scadenzaEsami", "*", $where);

        if (!empty($events)) {
            $oggi = new DateTime(); // Assicurati che sia definito una sola volta all'esterno
            foreach ($events as &$s) {
                // Recupero dati discente ed esame
                if ($_SESSION['utente']['ruolo'] == 'Discente') {
                    $nomeDiscente = $database->select("an_discenti", "nome, cognome", "id= " . $idDiscente['id']);
                } else {
                    $nomeDiscente = $database->select("an_discenti", "nome, cognome", "id= " . $s['id_discente']);
                }

                $esame = $database->select("an_esami", "nome, cfu, codice", "id= " . $s['id_esame']);

                $nome = $nomeDiscente[0]['nome'];
                $cognome = $nomeDiscente[0]['cognome'];

                // Imposta informazioni evento
                $s['title'] = "Esame " . $esame[0]['nome'];
                $s['start'] = $s['data'];
                $s['description'] = "Esame del discente " . $nome . " " . $cognome . " da " . $esame[0]['cfu'] . ' CFU';

                // Classificazione per data
                $dataEsame = new DateTime($s['data']);

                if ($dataEsame < $oggi) {
                    $s['className'] = "fc-event-danger"; // Esame passato
                } else {
                    $intervallo = $oggi->diff($dataEsame);
                    $giorniDiDifferenza = $intervallo->days;

                    if ($giorniDiDifferenza <= 7) {
                        $s['className'] = "fc-event-warning";
                    } elseif ($giorniDiDifferenza <= 14) {
                        $s['className'] = "fc-event-info";
                    } else {
                        $s['className'] = "fc-event-success";
                    }
                }
            }
        }


        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Gestione Scadenze',
            'serp' => '/calendario_esami',
            'menu' => $menu['scadenza_esami'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Calendario Esami',
            'revisione' => $revisione,
            'events' => $events,
            'message' => $message,
            'date' => array(),
            'content' => ''
        ];

        $result = render('notifiche/calendario_esami', $content);
        return $result;
    });
    //Area calendario rate
    $router->addRoute('/calendario_rate', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        $oggi = new DateTime(); // Prende la data e ora attuali
        $data_oggi = $oggi->format('Y-m-d');

        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            $idDiscente = $database->select("an_discenti", "id", "idUser= " . $_SESSION['utente']['id']);
            $idDiscente = $idDiscente[0];
            $where = "id_discente= " . $idDiscente['id'] . " AND pagato IS NULL AND data_scadenza>='" . $data_oggi . "'";
        } else {
            $where = "pagato IS NULL";
        }

        $events = $database->select("scadenzeRate", "*", $where);

        if (!empty($events)) {
            $oggi = new DateTime(); // assicurati che sia definito fuori dal foreach

            foreach ($events as &$s) {
                if ($_SESSION['utente']['ruolo'] != 'Discente') {
                    $nomeDiscente = $database->select("an_discenti", "nome, cognome", "id= " . $s['id_discente']);
                } else {
                    $nomeDiscente = $database->select("an_discenti", "nome, cognome", "id= " . $idDiscente['id']);
                }

                $nome = $nomeDiscente[0]['nome'];
                $cognome = $nomeDiscente[0]['cognome'];

                $s['title'] = "Scadenza Rata " . $nome . ' ' . $cognome;
                $s['start'] = $s['data_scadenza'];
                $s['description'] = "Scadenza della rata del discente " . $nome . " " . $cognome . " dall'importo di €" . $s['importoRata'];

                $dataScadenza = new DateTime($s['data_scadenza']);

                // Rata già scaduta
                if ($dataScadenza < $oggi) {
                    $s['className'] = "fc-event-danger";
                } else {
                    // Calcola differenza in giorni solo per rate future
                    $intervallo = $oggi->diff($dataScadenza);
                    $giorniDiDifferenza = $intervallo->days;

                    if ($giorniDiDifferenza <= 7) {
                        $s['className'] = "fc-event-warning";
                    } elseif ($giorniDiDifferenza <= 14) {
                        $s['className'] = "fc-event-info";
                    } else {
                        $s['className'] = "fc-event-success";
                    }
                }
            }

        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Gestione Scadenze',
            'serp' => '/calendario_rate',
            'menu' => $menu['scadenza_esami'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Scadenze',
            'revisione' => $revisione,
            'events' => $events,
            'message' => $message,
            'date' => array(),
            'content' => ''
        ];

        $result = render('notifiche/calendar', $content);
        return $result;
    });
    //Area PDF
    $router->addRoute('/gestione_pdf', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione, $emailpdf) {

        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = '';

        if (!empty($_SESSION['message'])) {
            //echo $_SESSION['message'];exit();
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (isset($_FILES) && !empty($_FILES)) {
            $querycontrollofile = "SELECT id FROM costruzionePDF WHERE id_discente= " . $_POST['id_discente'];
            $controllofile = $database->query($querycontrollofile);
            if (empty($controllofile)) {

                $fileInputs = [
                    'domanda_ammissione' => "Domanda Ammissione",
                    'autocertificazione' => "Autocertificazione",
                    'autenticazioneFoto' => "Autenticazione Foto",
                    'contrattoStudente' => "Contratto Studente",
                    'docIdentita' => "Documento Identità",
                    'regolamentoEco' => "Regolamento Economico",
                    'pianoStudi' => "Piano Studi",
                    'certCarriera' => "Certificazione Carriera",
                    'cv' => "CV",
                    'attestati' => "Attestati",
                    'ricevutePagamento' => "Ricevute Pagamento"
                ];

                $discente = $database->select("an_discenti", "nome, cognome", "id = " . $_POST['id_discente']);

                // Sanifica nome completo
                $nome = preg_replace('/[^a-zA-Z0-9_\-]/', '', $discente[0]['nome']);
                $cognome = preg_replace('/[^a-zA-Z0-9_\-]/', '', $discente[0]['cognome']);
                $nomecompleto = $nome . '-' . $cognome;

                $percorso = __DIR__ . '/../uploads/UnionePDF';
                $cartellaUtente = $percorso . '/' . $nomecompleto;

                if (!file_exists($cartellaUtente)) {
                    mkdir($cartellaUtente, 0777, true);
                }

                $savedFiles = array_fill_keys(array_keys($fileInputs), '');

                $allPdfPaths = [];

                foreach ($fileInputs as $inputName => $label) {
                    $result = salvaFile($inputName, $nomecompleto, $percorso);

                    if ($result['success']) {
                        $filePath = $result['url'];         // per il DB
                        $fileSystemPath = $result['path'];  // per elaborazioni interne

                        $savedFiles[$inputName] = $filePath;

                        $ext = strtolower(pathinfo($fileSystemPath, PATHINFO_EXTENSION));
                        if ($ext === 'pdf') {
                            $allPdfPaths[] = $fileSystemPath; // aggiungi PDF originale
                        }

                        // Se ZIP, estrai
                        if (
                            in_array($inputName, ['attestati', 'ricevutePagamento']) &&
                            $ext === 'zip'
                        ) {
                            $zip = new ZipArchive;
                            if ($zip->open($fileSystemPath) === TRUE) {
                                $zip->extractTo($cartellaUtente);

                                for ($i = 0; $i < $zip->numFiles; $i++) {
                                    $filename = $zip->getNameIndex($i);
                                    $extInside = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                    if ($extInside === 'pdf') {
                                        $allPdfPaths[] = $cartellaUtente . '/' . $filename;
                                    }
                                }

                                $zip->close();
                                // NON eliminare lo ZIP
                            } else {
                                echo '❌ Errore apertura ZIP: ' . $fileSystemPath;
                                $_SESSION['message'] = "errorOpenPDF";
                                header("Location: /gestione_pdf");
                            }
                        }
                    } else {
                        $savedFiles[$inputName] = '';
                        error_log("❌ Errore salvataggio {$label}: {$result['error']}");
                        $_SESSION['message'] = "errorSavePDF";
                        header("Location: /gestione_pdf");
                    }
                }

                if (count($allPdfPaths) > 0) {
                    $pdf = new PDFMerger;

                    foreach ($allPdfPaths as $pdfPath) {
                        $compatPdf = $pdfPath . '.compat.pdf';

                        if (convertToCompatiblePdf($pdfPath, $compatPdf)) {
                            $pdf->addPDF($compatPdf, 'all');
                        } else {
                            $_SESSION['message'] = "PDFnoncompatibile";
                            header("Location: /gestione_pdf");
                        }
                    }
                    $finalPdfPath = $cartellaUtente . '/documento_unificato.pdf';
                    $pdf->merge('file', $finalPdfPath);

                    // Calcolo URL corretto
                    $relativePath = str_replace('/var/www/html/gestione-universitygtc/public_html', '', $finalPdfPath);
                    $relativePath_trim = ltrim($relativePath, '/');
                    $baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
                    $savedFiles['pdf_unificato'] = $baseUrl . '/' . $relativePath_trim;

                }

                // Preparazione dati per il DB
                $data = [
                    'id_discente' => $_POST['id_discente'],
                    'domandaAmmissione' => $savedFiles['domanda_ammissione'],
                    'autoCertificazione' => $savedFiles['autocertificazione'],
                    'autenticazioneFoto' => $savedFiles['autenticazioneFoto'],
                    'contrattoStudente' => $savedFiles['contrattoStudente'],
                    'docIdentita' => $savedFiles['docIdentita'],
                    'regolamentoEconomico' => $savedFiles['regolamentoEco'],
                    'PianoStudi' => $savedFiles['pianoStudi'],
                    'CertificazioneCarriera' => $savedFiles['certCarriera'],
                    'CV' => $savedFiles['cv'],
                    'Ricevute' => $savedFiles['ricevutePagamento'],
                    'Attestati' => $savedFiles['attestati'],
                    'documento_unito' => $savedFiles['pdf_unificato']
                ];

                $database->insert("costruzionePDF", $data);

                $oggetto = "File Iscrizione";
                $messaggio_personalizzato = '
                                                <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f5f6fa" style="font-family: Arial, sans-serif;">
                                                <tr>
                                                    <td align="center">
                                                    <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="margin: 30px auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                                                        
                                                        <!-- HEADER -->
                                                        <tr>
                                                        <td align="center" style="padding: 30px 0;">
                                                            <img src="https://gestione.universitygtc.it/universitygtc.png" width="120" style="display:block;">
                                                            <h2 style="color: #364a63; font-size: 18px; margin: 10px 0 0;">
                                                            File Iscrizione Discente
                                                            </h2>
                                                        </td>
                                                        </tr>

                                                        <!-- CONTENUTO -->
                                                        <tr>
                                                        <td style="padding: 0 40px 20px;">
                                                            <h3 style="color: #1f2d3d; font-size: 16px;">Ciao,</h3>

                                                            <p style="color: #526484; font-size: 14px; line-height: 1.6;">
                                                            In allegato trovi il file per l\'immatricolazione del discente.
                                                            </p>
                                                            <p style="color: #526484; font-size: 14px;">----</p>

                                                            <p style="color: #1f2d3d; font-size: 14px;">
                                                            <b>WebMaster</b><br>
                                                            </p>
                                                        </td>
                                                        </tr>

                                                        <!-- FOOTER -->
                                                        <tr>
                                                        <td style="padding: 20px 40px; border-top: 1px solid #e5e9f2; text-align: center;">
                                                            <p style="font-size: 12px; color: #8392a5;">
                                                            Copyright © 2025<br>
                                                            <a href="https://gestione-universitygtc.it" style="color: #6a82fb;">https://gestione-universitygtc.it</a>.
                                                            </p>
                                                        </td>
                                                        </tr>

                                                    </table>
                                                    </td>
                                                </tr>
                                                </table>';


                //Percorso Fisico sul server
                $fileNames = [$finalPdfPath];
                $invii = "";
                foreach ($emailpdf as $destinatario) {
                    InviaEmail($oggetto, $destinatario, $messaggio_personalizzato, $fileNames, $invii);
                }
                $_SESSION['message'] = "FileInserito";
                header("Location: /gestione_pdf");
            } else {
                $_SESSION['message'] = "FileGiaInserito";
                header("Location: /gestione_pdf");
            }
        }
        $discente = $database->select("an_discenti", "id, nome, cognome", "1");

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Gestione PDF',
            'serp' => '/gestione_pdf',
            'revisione' => $revisione,
            'menu' => $menu['discenti'],
            'apps' => $apps,
            'message' => $message,
            'discente' => $discente,
            'h1' => '',
            'h2' => "Area PDF",
            'date' => array(),
        ];
        // Utilizza la funzione render per generare l'output HTML
        $result = render('pdf/gestionePDF', $content);

        // Restituisci l'output HTML generato
        return $result;
    });

    //Area per scaricare i file uniti dei discenti che hanno completato l'iscrizione
    $router->addRoute('/elencoPDF', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }

        $query = "SELECT id, id_discente, documento_unito FROM costruzionePDF WHERE 1";
        $file = $database->query($query);

        $elenco = []; // Qui salveremo ogni discente con documento

        if (!empty($file)) {
            foreach ($file as $f) {
                $queryDiscenti = "SELECT nome, cognome, email FROM an_discenti WHERE id = " . intval($f['id_discente']);
                $anDiscenti = $database->query($queryDiscenti);

                if (!empty($anDiscenti)) {
                    $discente = $anDiscenti[0]; // prende la prima riga
                    $discente['id_discente'] = $f['id_discente'];
                    $discente['documento_unito'] = $f['documento_unito'];
                    $elenco[] = $discente;
                }
            }
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'PDF Discenti',
            'serp' => '/discentiPDF',
            'menu' => $menu['discenti'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'File Discente',
            'revisione' => $revisione,
            'date' => array(),
            'content' => ''
        ];

        if (isset($anDiscenti) && !empty($anDiscenti)) {
            $content['anDiscenti'] = $elenco;
        }

        $result = render('pdf/discentiPDF', $content);
        return $result;
    });

    /*Route user*/
    $router->addRoute('/users', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        //Se accede un discente, lo reindirizzo alla sua area personale
        if ($_SESSION['utente']['ruolo'] == 'Discente') {
            //prendo i dati dall'id_User in an_anagrafica
            $idUser = $database->select('an_discenti', 'id', "idUser = '" . $_SESSION['utente']['id'] . "'");
            $idUser = base64_encode($idUser[0]['id']);

            header('Location: /discenti/gestione/' . $idUser . '/voti');
            exit();
        }
        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        if (isset($_POST['id_update']) && $_POST['id_update'] != '') {
            if (!empty($_POST['pwd'])) {
                if ($_POST['pwd'] == $_POST['cpwd']) {
                    $data = array(
                        'nome' => $_POST['nome'],
                        'cognome' => $_POST['cognome'],
                        'email' => $_POST['email'],
                        'pwd' => password_hash($_POST['pwd'], PASSWORD_DEFAULT)
                    );

                    $where = "id= " . $_POST['id_update'];
                    $database->update('users', $data, $where);

                    $_SESSION['message'] = 'ok';
                    header("Location: /users");
                } else {
                    $_SESSION['message'] = 'pwd_error';
                    header("Location: /users");
                }
            } else {
                $data = array(
                    'nome' => $_POST['nome'],
                    'cognome' => $_POST['cognome'],
                    'email' => $_POST['email'],
                );

                $where = "id= " . $_POST['id_update'];
                $database->update('users', $data, $where);
                $_SESSION['message'] = 'ok';
                header("Location: /users");
            }
        }

        $query = "SELECT id, nome, cognome, email FROM users WHERE status = 'attivo' AND id= " . $_SESSION['utente']['id'];
        $utenti = $database->query($query);
        $utenti = $utenti[0];

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Utenti',
            'serp' => '/users',
            'menu' => $menu['users'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Dati Profilo',
            'revisione' => $revisione,
            'utenti' => $utenti,
            'message' => $message,
            'date' => array(),
            'content' => ''
        ];

        $result = render('user/users', $content);
        return $result;
    });

    /*Panieri*/
    $router->addRoute('/panieri', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $message = '';

        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        $id_utente = $_SESSION['utente']['id'];
        $acquisti_validi = [];
        if ($_SESSION['utente']['ruolo'] == 'Discente' || $_SESSION['utente']['ruolo'] == 'Tutor') {
            //prendo i dati dall'id_User in an_anagrafica
            $query = "SELECT pid
                    FROM panieri_acq
                    WHERE   uid = " . $id_utente . " AND 
                            data_aq >= CURDATE() AND 
                            data_sc <= CURDATE()";
            $acquisti_validi = $database->query($query);
        }
        $q_esami = "  SELECT DISTINCT
                        ae.id,
                        ae.nome
                    FROM
                        an_esami ae
                    JOIN
                        panieri p ON CONCAT(',', p.esami, ',') LIKE CONCAT('%,', ae.id, ',%');";

        $esami = $database->query($q_esami);

        $titolo = "Panieri Esami";
        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Panieri Esami',
            'serp' => '/panieri',
            'menu' => $menu['panieri'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Area Panieri',
            'revisione' => $revisione,
            'acquisti_validi' => $acquisti_validi,
            'message' => $message,
            'date' => array(),
            'content' => $esami
        ];
        $result = render('panieri/index', $content);
        return $result;
    });
    $router->addRoute('/panieri/esame/{id}', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $panieri = $database->select('panieri', 'id, domanda, r1, r2, r3, r4', "esami like '%," . $id['id'] . ",%'");
        $esame = $database->select('an_esami', 'nome', "id=" . $id['id']);
        $titolo = "Panieri Esami";
        $message = '';
        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Panieri Esami',
            'serp' => '/panieri/esame/' . $id['id'],
            'menu' => $menu['panieri'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Panieri Esame ' . $esame[0]['nome'],
            'revisione' => $revisione,
            'esame' => $esame[0]['nome'],
            'panieri' => $panieri ?? array(),
            'message' => $message,
            'date' => array(),
            'content' => ''
        ];
        $result = render('panieri/paniere', $content);
        return $result;
    });
    $router->addRoute('/panieri/esame/{id}/{update}', function ($id) use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $esami = $database->select('an_esami', 'id,codice,nome');
        $titolo = "Panieri Esami";
        $message = '';
        if (!empty($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES) && !empty($_FILES)) {
                if (
                    !empty($_FILES['img-domanda']['name']) ||
                    !empty($_FILES['img-r1']['name']) ||
                    !empty($_FILES['img-r2']['name']) ||
                    !empty($_FILES['img-r3']['name']) ||
                    !empty($_FILES['img-r4']['name']) ||
                    !empty($_FILES['img-ra']['name'])
                ) {
                    $fileInputs = [
                        'img-domanda' => "img-domanda",
                        'img-r1' => "img-r1",
                        'img-r2' => "img-r2",
                        'img-r3' => "img-r3",
                        'img-r4' => "img-r4",
                        'img-ra' => "img-ra"
                    ];
                    $savedFiles = [];

                    foreach ($fileInputs as $inputName => $label) {
                        $percorso = __DIR__ . '/../uploads/docPanieri';
                        $result = salvaFile($inputName, $_POST['id'], $percorso);
                        if ($result['success']) {
                            $savedFiles[$inputName] = $result['url'];  // Salvi l'URL univoco
                            echo "✅ File inserito: {$label} - {$result['file']}<br>";
                            //exit();
                        } else {
                            $savedFiles[$inputName] = '';  // fallback se qualcosa fallisce
                            echo "❌ Errore per {$label}: {$result['error']}<br>";
                            //exit();
                        }
                    }
                }
            }
            $id_esami = ',' . implode(',', $_POST['esami']) . ',';
            $r1 = $_POST['r1'];
            $r2 = $_POST['r2'];
            $r3 = $_POST['r3'];
            $r4 = $_POST['r4'];
            $rc = $_POST['rc'];


            if (!empty($_POST['ra'])) {
                $r1 = $r2 = $r3 = $r4 = $rc = null;
            }

            $data = [
                'domanda' => $_POST['domanda'],
                'r1' => $r1,
                'r2' => $r2,
                'r3' => $r3,
                'r4' => $r4,
                'esami' => $id_esami,
                'rc' => $rc,
                'ra' => $_POST['ra'] ?? NULL,
                'img_domanda' => $savedFiles['img-domanda'] ?? NULL,
                'img_r1' => $savedFiles['img-r1'] ?? NULL,
                'img_r2' => $savedFiles['img-r2'] ?? NULL,
                'img_r3' => $savedFiles['img-r3'] ?? NULL,
                'img_r4' => $savedFiles['img-r4'] ?? NULL,
                'img_ra' => $savedFiles['img-ra'] ?? NULL
            ];
            $where = "id= " . $_POST['id'];
            $database->update('panieri', $data, $where);
            $_SESSION['message'] = "UpdatePaniere";
            header("Location: /panieri/esame/" . $id['id'] . "/update");
            exit();
        }

        $query = "SELECT * FROM panieri WHERE id= " . $id['id'];
        $panieri = $database->query($query);

        $id_esami = explode(',', $panieri[0]['esami']);
        $id_esami = array_filter(array_map('intval', explode(',', $panieri[0]['esami'])));

        $esamiArray = [];

        foreach ($id_esami as $i) {
            $esami = "SELECT nome FROM an_esami WHERE id= " . $i;
            $select = $database->query($esami);
            $esamiArray[] = $select[0]['nome'];
        }
        $panieri[0]['esamiArray'] = implode(',', $esamiArray);
        $esami = $database->select('an_esami', 'id,codice,nome');

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Panieri Esami',
            'serp' => '/panieri/esame/' . $id['id'] . '/update',
            'menu' => $menu['panieri'],
            'apps' => $apps,
            'update' => $id['update'],
            'panieri' => $panieri,
            'h1' => '',
            'h2' => 'Nuovo Quesito per Paniere Esame',
            'revisione' => $revisione,
            'esami' => $esami,
            'message' => $message,
            'date' => array(),
            'content' => ''
        ];
        $result = render('panieri/nm_paniere', $content);
        return $result;
    });
    $router->addRoute('/panieri/new', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {
        $esami = $database->select('an_esami', 'id,codice,nome');
        $titolo = "Panieri Esami";
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recupera i dati dal form
            $esami_selezionati = isset($_POST['esami']) ? ',' . implode(',', $_POST['esami']) . ',' : '';
            $domanda = $_POST['domanda'] ?? '';
            $r1 = $_POST['r1'] ?? '';
            $r2 = $_POST['r2'] ?? '';
            $r3 = $_POST['r3'] ?? '';
            $r4 = $_POST['r4'] ?? '';
            $rc = $_POST['rc'] ?? '';
            $ra = $_POST['ra'] ?? '';


            if (!empty($ra)) {
                $r1 = NULL;
                $r2 = NULL;
                $r3 = NULL;
                $r4 = NULL;
                $rc = NULL;
            }
            // Inserimento nel database
            $data = [
                'domanda' => $domanda,
                'r1' => $r1,
                'r2' => $r2,
                'r3' => $r3,
                'r4' => $r4,
                'esami' => ',' . $esami_selezionati . ',',
                'rc' => $rc,
                'ra' => $ra
            ];
            $database->insert('panieri', $data);

            $lastid = $database->lastId();

            if (!empty($_FILES)) {
                $fileInputs = [
                    'img-domanda' => "img-domanda",
                    'img-r1' => "img-r1",
                    'img-r2' => "img-r2",
                    'img-r3' => "img-r3",
                    'img-r4' => "img-r4",
                    'img-ra' => "img-ra"
                ];

                $savedFiles = [];

                foreach ($fileInputs as $inputName => $label) {
                    $percorso = __DIR__ . '/../uploads/docPanieri';
                    $result = salvaFile($inputName, $lastid, $percorso);
                    if ($result['success']) {
                        $savedFiles[$inputName] = $result['url'];  // Salvi l'URL univoco
                        //echo "✅ File inserito: {$label} - {$result['file']}<br>";
                        //exit();
                    } else {
                        $savedFiles[$inputName] = '';  // fallback se qualcosa fallisce
                        //echo "❌ Errore per {$label}: {$result['error']}<br>";
                        //exit();
                    }
                }

                $dataFile = array(
                    'img_r1' => $savedFiles['img-r1'],
                    'img_r2' => $savedFiles['img-r2'],
                    'img_r3' => $savedFiles['img-r3'],
                    'img_r4' => $savedFiles['img-r4'],
                    'img_domanda' => $savedFiles['img-domanda'] ?? '',
                    'img_ra' => $savedFiles['img-ra'] ?? ''
                );

                $where = "id= " . $lastid;
                $database->update("panieri", $dataFile, $where);
                $_SESSION['message'] = "insert";
                header("Location: /panieri");
                exit();
            }
        }

        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Panieri Esami',
            'serp' => '/panieri',
            'menu' => $menu['panieri'],
            'apps' => $apps,
            'h1' => '',
            'h2' => 'Nuovo Quesito per Paniere Esame',
            'revisione' => $revisione,
            'esami' => $esami,
            'message' => $message,
            'date' => array(),
            'content' => ''
        ];
        $result = render('panieri/nm_paniere', $content);
        return $result;
    });
    /* Reportistica  */
    include_once __DIR__ . '/funzioni/reportistica/routes.php';

    $router->addRoute('/reportistica_prodotti', function () use ($database, $dominio, $titolo, $apps, $menu, $revisione) {


        $prodotti = $database->select("rateDiscente", "riferimentoTabellaProd, importoTotale, tipoPagamento", "1");

        // Inizializziamo le variabili per le somme
        $percorso = 0;
        $master = 0;
        $esameSingolo = 0;
        $certificazioni = 0;
        $tutoraggio = 0;

        foreach ((array) $prodotti as $p) {
            $importo = (float) ($p['importoTotale'] ?? 0);
            $tipo = strtolower(trim((string) ($p['tipoPagamento'] ?? '')));
            $rif = (string) ($p['riferimentoTabellaProd'] ?? '');
            $valueTabella = strtolower(trim(explode('||', $rif, 2)[0]));

            // Se è tutoraggio, lo gestiamo immediatamente e passiamo al prossimo elemento
            if ($tipo === 'tutoraggio') {
                $tutoraggio += $importo;
                continue;
            }

            switch ($valueTabella) {
                case 'an_percorsi':
                    $percorso += $importo;
                    break;

                case 'an_master':
                    $master += $importo;
                    break;

                case 'an_esami':
                    $esameSingolo += $importo;
                    break;

                case 'an_certificazioni':
                    $certificazioni += $importo;
                    break;

                default:
                    // Altri casi ignorati o gestiscili qui se necessario
                    break;
            }
        }

        // Creiamo un array con le somme totali
        $TotaliIncassi = [
            $percorso,
            $master,
            $esameSingolo,
            $certificazioni,
            $tutoraggio
        ];

        // JSON per il front-end
        $TotaleIncassiJson = json_encode($TotaliIncassi);
        $content = [
            'dominio' => $dominio,
            'titolo' => $titolo,
            'title' => 'Reportistica Prodotti',
            'serp' => '/reportistica_prodotti',
            'menu' => $menu['reportistica'] ?? [],
            'apps' => $apps,
            'h1' => 'Reportistica Prodotti',
            'h2' => 'Ricerca prodotti',
            'TotaleIncassiJson' => $TotaleIncassiJson,
            'revisione' => $revisione,
            'message' => '',
        ];
        return render('reportistica/reportistica_prodotti', $content);
    });

};

function salvaFile($fileInputName, $nome, $baseDir)
{

    error_log("🟢 Inizio salvaFile per: $fileInputName");

    if (!isset($_FILES[$fileInputName])) {
        error_log("❌ File '$fileInputName' non presente in \$_FILES.");
        return [
            'success' => false,
            'error' => "File '$fileInputName' non presente in \$_FILES."
        ];
    }

    if ($_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        error_log("❌ Errore nell'upload di '$fileInputName': " . $_FILES[$fileInputName]['error']);
        return [
            'success' => false,
            'error' => "Errore nell'upload di '$fileInputName' (code " . $_FILES[$fileInputName]['error'] . ")"
        ];
    }

    if ($_FILES[$fileInputName]['size'] === 0) {
        error_log("❌ Il file '$fileInputName' è vuoto.");
        return [
            'success' => false,
            'error' => "File caricato è vuoto per '$fileInputName'."
        ];
    }

    if (
        isset($_FILES[$fileInputName]) &&
        $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK
    ) {
        // 🛡️ Controllo dimensione file
        if ($_FILES[$fileInputName]['size'] === 0) {
            return [
                'success' => false,
                'error' => "File caricato è vuoto per '$fileInputName'."
            ];
        }

        $targetDir = $baseDir . '/' . $nome;
        if (!is_dir($targetDir)) {
            // Usa permessi più restrittivi e affidati alla configurazione del server per la proprietà
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                return ['success' => false, 'error' => "Impossibile creare la directory di destinazione: " . $targetDir];
            }
            chown($targetDir, 'www-data');
            chgrp($targetDir, 'www-data');

        }

        $originalName = $_FILES[$fileInputName]['name'];
        $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', str_replace(' ', '-', $originalName));
        $filePath = $targetDir . '/' . $sanitizedFileName;

        // 👇 SE IL FILE ESISTE, CREA UN NUOVO NOME UNIVOCO
        if (file_exists($filePath)) {
            $fileInfo = pathinfo($sanitizedFileName);
            $timestamp = time();
            $randomString = bin2hex(random_bytes(4)); // 8 caratteri random
            $newFileName = $fileInfo['filename'] . '_' . $timestamp . '_' . $randomString . '.' . $fileInfo['extension'];
            $filePath = $targetDir . '/' . $newFileName;
        } else {
            $newFileName = $sanitizedFileName;
        }

        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $filePath)) {
            $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
            $realFilePath = str_replace('\\', '/', realpath($filePath));
            $webRelativePath = str_replace($documentRoot, '', $realFilePath);
            $protocol = "https";
            $host = $_SERVER['HTTP_HOST'];
            $webFullPath = $protocol . '://' . $host . $webRelativePath;

            return [
                'success' => true,
                'file' => $newFileName,
                'url' => $webFullPath,  // URL per browser
                'path' => $filePath     // Percorso server fisico
            ];
        } else {
            return [
                'success' => false,
                'error' => "Errore nel salvataggio del file '$fileInputName'."
            ];
        }
    } else {
        return [
            'success' => false,
            'error' => "Nessun file caricato o errore upload per '$fileInputName'."
        ];
    }
}
function InviaEmail($oggetto, $destinatario, $messaggio_personalizzato, $fileNames, $invii)
{

    // Crea una nuova istanza di PHPMailer
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug = 0;
    try {
        // Configurazione del server SMTP
        $mail->isSMTP();
        $mail->Host = 'smtps.aruba.it'; // Specifica il server SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'comunicazione@networkgtc.it'; // SMTP username
        $mail->Password = 'Ctnt378@'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Abilita la crittografia TLS
        $mail->Port = 465; // Porta SMTP

        // Destinatari
        $mail->setFrom('comunicazione@networkgtc.it', 'NoReply'); // Nome visibile mascherato            //$mail->addReplyTo(); // Mittente per risposte
        $mail->addAddress($destinatario); // Aggiungi il destinatario

        // Contenuto dell'email
        $mail->isHTML(true); // Imposta il formato email a HTML
        $mail->Subject = mb_convert_encoding($oggetto, 'UTF-8', 'auto');
        $mail->Body = mb_convert_encoding($messaggio_personalizzato, 'UTF-8', 'auto'); // Corpo del messaggio

        // Aggiungi gli allegati
        if (is_array($fileNames)) {
            foreach ($fileNames as $filePath) {
                if (file_exists($filePath)) {
                    $mail->addAttachment($filePath);
                }
            }
        }

        // Invia email
        $mail->send();

    } catch (Exception $e) {
        $invii .= "Errore nell'invio dell'email a $destinatario. Errore: {$mail->ErrorInfo}<br>";
    }
}
function generaPasswordComplessa($lunghezza = 8, $caratteriConsentiti = '')
{
    // Se non vengono specificati caratteri consentiti, usa un set predefinito complesso
    if (empty($caratteriConsentiti)) {
        $caratteriConsentiti = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_+=[]{}|;:,.<>?';
    }

    $password = '';
    $lunghezzaCaratteri = strlen($caratteriConsentiti);

    // Verifica se la lunghezza della password è valida
    if ($lunghezza <= 0) {
        throw new InvalidArgumentException("La lunghezza della password deve essere un numero positivo.");
    }
    // Verifica se ci sono abbastanza caratteri consentiti per generare una password unica
    if ($lunghezzaCaratteri === 0) {
        throw new InvalidArgumentException("La stringa di caratteri consentiti non può essere vuota.");
    }

    try {
        for ($i = 0; $i < $lunghezza; $i++) {
            // Utilizza random_int() per generare numeri casuali criptograficamente sicuri
            $indiceCasuale = random_int(0, $lunghezzaCaratteri - 1);
            $password .= $caratteriConsentiti[$indiceCasuale];
        }
        return $password;
    } catch (Exception $e) {
        // Gestione degli errori per random_int (ad esempio, se non è disponibile)
        error_log("Errore nella generazione di numeri casuali sicuri: " . $e->getMessage());
        return false; // O gestisci l'errore in altro modo
    }
}
function convertToCompatiblePdf($inputPath, $outputPath)
{
    $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/default " .
        "-dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($outputPath) . " " . escapeshellarg($inputPath);

    exec($cmd . " 2>&1", $output, $returnVar);

    if ($returnVar !== 0) {
        error_log("❌ Errore Ghostscript ($returnVar) su file: $inputPath");
        error_log("Comando: $cmd");
        error_log("Output:\n" . implode("\n", $output));
    }

    return ($returnVar === 0 && file_exists($outputPath));
}

// Costruisce la base URL corrente (https/http + host)
function getBaseUrl(): string
{
    $scheme = 'https://';
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    return rtrim($scheme . $host, '/');
}

// Converte un URL del sito in path filesystem
function urlToFs(string $url): string
{
    $docRoot = rtrim(realpath($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    if ($docRoot === '')
        return '';
    $parts = parse_url($url);
    if (empty($parts['path']))
        return '';
    $fs = $docRoot . $parts['path'];
    $abs = realpath($fs);
    return $abs ? str_replace('\\', '/', $abs) : str_replace('\\', '/', $fs);
}

// Converte un path filesystem in URL del sito
function fsToUrl(string $fsPath): string
{
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    if ($docRoot === '')
        return '';
    $abs = str_replace('\\', '/', realpath($fsPath) ?: $fsPath);
    if (!str_starts_with($abs, $docRoot)) {
        // non è sotto il document root: non convertire
        return '';
    }
    $rel = substr($abs, strlen($docRoot)); // inizia con '/'
    return getBaseUrl() . $rel;
}



?>