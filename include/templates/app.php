<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Corrispettivi | <?php echo $_SESSION['utente']['nome']." ".$_SESSION['utente']['cognome']; ?></title>
    <link rel="icon" href="../img/logo_sf.png" type="image/png">
    <style>
        /* Stile per i pulsanti radio */
        .radio-btn {
            display: inline-block;
            margin-right: 10px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="radio"] {
            margin-right: 5px;
            cursor: pointer;
        }
        /* Stile per l'etichetta dei pulsanti radio */
        .radio-label {
            color: #555;
            font-size: 16px;
            cursor: pointer;
        }
        /* Aggiunge una transizione per un feedback visivo al clic */
        input[type="radio"]:focus + .radio-label {
            outline: none;
            border: 1px solid #A06AFF;
            border-radius: 4px;
            padding: 2px 5px;
        }
        @media (min-width: 769px) {
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background-color: #f8f9fa;
            }

            .form-container {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 480px; /* Imposta una larghezza massima */
                box-sizing: border-box;
                margin: 20px;
            }

            h2 {
                text-align: center;
                margin-bottom: 20px;
                color: #333;
            }

            .form-group {
                margin-bottom: 15px;
            }

            label {
                display: block;
                font-size: 17px;
                margin-bottom: 5px;
                color: #555;
            }

            input[type="text"], input[type="password"] {
                width: 100%;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 14px;
                color: #333;
                transition: border-color 0.3s;
                box-sizing: border-box;
            }

            input[type="text"]:focus, input[type="password"]:focus{
                border-color: #007bff;
                outline: none;
            }

            input[type="submit"] {
                background-color: #007bff;
                color: #fff;
                padding: 10px 15px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                width: 100%;
            }

            input[type="submit"]:hover {
                background-color: #0056b3;
            }

            .hidden {
                display: none;
            }

            .message {
                text-align: center;
                color: #007bff;
                margin-top: 10px;
            }

            /* Stile comune per entrambi i pulsanti */
            .btn {
                background-color: #A06AFF; /* Colore di sfondo uniforme */
                color: #fff; /* Colore del testo */
                font-size: 16px; /* Dimensione del testo */
                padding: 15px 30px; /* Imbottitura uniforme per dimensione pulsante */
                border: none; /* Rimozione del bordo */
                border-radius: 8px; /* Angoli arrotondati */
                cursor: pointer; /* Cambia il cursore al passaggio */
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Ombra per effetto 3D */
                transition: background-color 0.3s, transform 0.3s; /* Transizioni */
                width: 200px; /* Larghezza fissa per entrambi i pulsanti */
                text-align: center; /* Testo centrato */
            }

            /* Hover: Cambia colore di sfondo e ingrandisce leggermente */
            .btn:hover {
                background-color: #8e5ee6; /* Colore più scuro al passaggio del mouse */
                transform: scale(1.05); /* Leggero ingrandimento */
            }

            /* Stile per mantenere il focus visibile */
            .btn:focus {
                outline: none; /* Rimuove il bordo del focus predefinito */
                box-shadow: 0 0 0 4px rgba(160, 106, 255, 0.4); /* Aggiunge un'ombra per il focus */
            }

            a {
                text-decoration: none; /* Rimuove la sottolineatura */
            }

            input[type="text"], input[type="email"], input[type="tel"], input[type="number"] {
                width: 100%;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 14px;
                color: #333;
                transition: border-color 0.3s;
                box-sizing: border-box;
            }

            input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus, input[type="number"]:focus {
                border-color: #007bff;
                outline: none;
            }

            input[type="submit"] {
                background-color: #007bff;
                color: #fff;
                padding: 10px 15px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                width: 100%;
            }

            input[type="submit"]:hover {
                background-color: #0056b3;
            }

            .button-container {
                display: flex;
                flex-direction: column; /* Impilamento verticale dei pulsanti */
                gap: 15px; /* Spazio tra i pulsanti */
                align-items: center; /* Centra i pulsanti in colonna */
                margin: 20px 0;
            }
        }
        @media (max-width: 768px) {
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: flex-start; /* Allinea l'inizio della pagina */
                min-height: 100vh;
                margin: 0;
                padding-top: 150px; /* Aggiunge uno spazio sopra il form */
                background-color: #f8f9fa;
            }

            .form-container {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                border: 2px solid #007bff; /* Aggiunge un bordo visibile */
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                width: 90%; /* Riduce leggermente la larghezza al 90% */
                max-width: 350px; /* Imposta una larghezza massima ridotta per essere più compatta */
                box-sizing: border-box;
                margin: 0 auto 20px auto; /* Centra la form orizzontalmente e aggiunge un piccolo margine inferiore */
            }

            h2 {
                text-align: center;
                margin-bottom: 20px;
                color: #333;
            }

            .form-group {
                margin-bottom: 15px;
            }

            label {
                display: block;
                font-size: 17px;
                margin-bottom: 5px;
                color: #555;
            }

            input[type="text"], input[type="password"], input[type="email"], input[type="tel"], input[type="number"] {
                width: 100%;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 14px;
                color: #333;
                transition: border-color 0.3s;
                box-sizing: border-box;
            }

            input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus, input[type="tel"]:focus, input[type="number"]:focus {
                border-color: #007bff;
                outline: none;
            }

            input[type="submit"] {
                background-color: #007bff;
                color: #fff;
                padding: 10px 15px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                width: 100%;
            }

            input[type="submit"]:hover {
                background-color: #0056b3;
            }

            .hidden {
                display: none;
            }

            .message {
                text-align: center;
                color: #007bff;
                margin-top: 10px;
            }

            .button-container {
                display: flex;
                flex-direction: column; /* Impilamento verticale dei pulsanti */
                gap: 15px; /* Spazio tra i pulsanti */
                align-items: center; /* Centra i pulsanti in colonna */
                margin: 20px 0;
            }

            .btn {
                background-color: #A06AFF;
                color: #fff;
                font-size: 16px;
                padding: 15px 30px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                transition: background-color 0.3s, transform 0.3s;
                width: 100%; /* Imposta la larghezza al 100% per essere responsive */
                max-width: 180px; /* Larghezza massima ridotta dei pulsanti */
                text-align: center;
            }

            .btn:hover {
                background-color: #8e5ee6;
                transform: scale(1.05);
            }

            .btn:focus {
                outline: none;
                box-shadow: 0 0 0 4px rgba(160, 106, 255, 0.4);
            }

            a {
                text-decoration: none;
            }
        }
        .user-avatar, [class^=user-avatar]:not([class*=-group]) {
            margin: 2em auto;
            border-radius: 50%;
            height: 120px;
            width: 120px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            background: #798bff;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 0.06em;
            flex-shrink: 0;
            position: relative;
        }
        /* Tabella responsive */ 
        .table-responsive {
            width: 100%;
            overflow-x: auto; /* Mantiene lo scroll orizzontale per tabelle più larghe */
            font-size:0.8em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        thead {
            background-color: #f9f9f9;
        }
        .descrizione{width: 42%;}
    </style>
</head>
<body>
    <div class="form-container">
            <!-- Contenitore per il layout orizzontale -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <!-- Torna alla home -->
            <a id="home" href="/app" style="background-color: #A06AFF; color: white; font-size: 13px; padding: 8px 20px; border: 0.5px; border-radius: 8px; cursor: pointer; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); transition: background-color 0.3s, transform 0.3s; text-decoration: none;">
                Home
            </a>
           <!-- Logout -->
            <button id="logout" onclick="confirmLogout();" style="background-color: #A06AFF; color: white; font-size: 13px; padding: 8px 20px; border: 0.5px; border-radius: 8px; cursor: pointer; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); transition: background-color 0.3s, transform 0.3s;">
                Logout
            </button>
        </div>
        <div class="user-avatar">
            <span>
                <?php 
                    $iniziali=strtoupper(substr($_SESSION['utente']['nome'], 0, 1).substr($_SESSION['utente']['cognome'], 0, 1));
                    echo $iniziali;
                ?>
            </span>
        </div>
        <?= $pageContent; ?>    
        <br><br>
    </div>
    <script>
        // Funzione JavaScript che chiede conferma e reindirizza alla stessa pagina con ?logout=1
        function confirmLogout() {
            if (confirm('Sei sicuro di voler effettuare il logout?')) {
                // Ottiene l'URL corrente senza parametri di query
                let currentUrl = '/login.php';
                // Reindirizza aggiungendo il parametro logout=1
                window.location.href = currentUrl + '?logout=1';
            }
        }

    </script>

</body>
</html>
