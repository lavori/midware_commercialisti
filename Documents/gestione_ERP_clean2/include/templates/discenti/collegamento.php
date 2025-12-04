<!-- content @s -->
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h1 class="page-title"><?php echo $h1; ?></h1>
            <div class="nk-block-des text-soft">
                <h2><?php echo $h2; ?></h2>
            </div>
        </div>
    </div><!-- .nk-block-between -->
</div><!-- .nk-block-head -->
<div class="nk-block  nk-block-lg">
    <div class="row g-gs my-3">
        <div class="col-md-12">
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <div>
                        <h3>Dati Anagrafici:</h3><br>

                        <p>
                            Cognome: <?php echo '<b>' . $datiDiscente['cognome'] . '</b>'; ?> &nbsp;&nbsp;
                            Nome: <?php echo '<b>' . $datiDiscente['nome'] . '</b>'; ?> &nbsp;&nbsp;
                            Sesso: <?php echo ($datiDiscente['sesso'] == 'M') ? '<b>Maschio</b>' : '<b>Femmina</b>'; ?>
                            &nbsp;&nbsp;
                            Matricola: <?php echo '<b>' . $datiDiscente['numMatricola'] . '</b>'; ?>
                        </p>

                        <p>
                            Codice Fiscale: <?php echo '<b>' . $datiDiscente['cf'] . '</b>'; ?> &nbsp;&nbsp;
                            Nato a: <?php echo '<b>' . $datiDiscente['luogoNascita'] . '</b>'; ?>
                            (<?php echo '<b>' . $datiDiscente['provinciaNascita'] . '</b>'; ?>) &nbsp;&nbsp;
                            il: <?php echo '<b>' . $datiDiscente['dataNascita'] . '</b>'; ?>
                        </p>

                        <p>
                            Cittadinanza: <?php echo '<b>' . $datiDiscente['cittadinanza'] . '</b>'; ?> &nbsp;&nbsp;
                            2ª Cittadinanza: <?php echo '<b>' . $datiDiscente['secondaCittadinanza'] . '</b>'; ?>
                        </p>

                        <p>
                            Residenza: <?php echo '<b>' . $datiDiscente['viaResidenza'] . '</b>'; ?> &nbsp;&nbsp;
                            CAP: <?php echo '<b>' . $datiDiscente['CAPresidenza'] . '</b>'; ?> &nbsp;&nbsp;
                            Città: <?php echo '<b>' . $datiDiscente['citta'] . '</b>'; ?>
                            (<?php echo '<b>' . $datiDiscente['provincia'] . '</b>'; ?>)
                        </p>

                        <p>
                            Telefono: <?php echo '<b>' . $datiDiscente['telefono'] . '</b>'; ?> &nbsp;&nbsp;
                            Cellulare: <?php echo '<b>' . $datiDiscente['cellulare'] . '</b>'; ?> &nbsp;&nbsp;
                            Email: <?php echo '<b>' . $datiDiscente['email'] . '</b>'; ?>
                        </p>

                        <p>
                            Indirizzo dove ricevere la corrispondenza:
                            <?php echo '<b>' . $datiDiscente['indirizzoCorrispondente'] . '</b>'; ?>
                        </p>

                        <p>
                            Diploma di Istruzione Superiore in:
                            <?php echo '<b>' . $datiDiscente['tipoDiploma'] . '</b>'; ?>
                        </p>

                        <p>
                            Tipo di Laurea: <?php echo '<b>' . $datiDiscente['tipoLaurea'] . '</b>'; ?> &nbsp;&nbsp;
                            In: <?php echo '<b>' . $datiDiscente['titoloLaurea'] . '</b>'; ?>
                        </p>
                        <hr>
                        <h3>Dati E-campus presenti</h3>
                        <ul class="list-unstyled">
                            <li>
                                <p>Email:
                                    <b><?= htmlspecialchars($datiDiscente['emailEcampus'] ?? '', ENT_QUOTES) ?: 'N/D' ?></b>
                                </p>
                            </li>
                            <li>
                                <p>Password:
                                    <b><?= htmlspecialchars($datiDiscente['pwdEcampus'] ?? '', ENT_QUOTES) ?: 'N/D' ?></b>
                                </p>
                            </li>
                            <li>
                                <p>ID:
                                    <b><?= htmlspecialchars($datiDiscente['idEcampus'] ?? '', ENT_QUOTES) ?: 'N/D' ?></b>
                                </p>
                            </li>
                        </ul>
                        <hr>
                        <h3>Allegati Presenti:</h3><br>
                        <ul>
                            <li>
                                - <a href="<?php echo $datiDiscente['id_card']; ?>" target="_blank">Carta D'Identità</a>
                            </li>
                            <li>
                                - <a href="<?php echo $datiDiscente['tessera'] ?>" target="_blank">Codice Fiscale</a>
                            </li>
                            <li>
                                - <a href="<?php echo $datiDiscente['certificazioneZip'] ?>"
                                    target="_blank">Certificazioni Zip</a>
                            </li>
                            <li>
                                - <a href="<?php echo $datiDiscente['cv'] ?>" target="_blank">CV</a>
                            </li>
                        </ul>
                        <hr>
                        <h3>Voti Presenti</h3>
                        <br>
                        <ul>
                            <?php foreach ($datiDiscente['esami'] as $esame): ?>
                                <li>
                                    <p>
                                        Esame:&nbsp;<b><?php echo $esame['nome_esame']; ?></b>&nbsp;&nbsp;
                                        Voto:&nbsp;<b><?php echo $esame['voto']; ?></b></p>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <hr>
                        <div class="center">
                            <button class="btn btn-primary"
                                onclick="window.open('https://sts.uniecampus.it/', '_blank')">Accedi a E-Campus</button>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
</div>
<!-- Form unico nella pagina -->
<form id="mainForm" action="<?php echo $serp; ?>" method="POST">
    <!-- Altri campi visibili opzionali -->
</form>