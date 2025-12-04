<!DOCTYPE html>
<html lang="it" class="js">
<?php include('./include/html/head.php'); ?>

<body class="nk-body npc-default has-apps-sidebar has-sidebar ">
    <div class="nk-app-root">
        <!--Menu sx esterno-->
        <?php include('./include/html/menu-sx.php'); ?>
        <!--Menu sx esterno-->
        <!-- main @s -->
        <div class="nk-main ">
            <!-- wrap @s -->
            <div class="nk-wrap ">
                <!-- main header @s -->
                <?php include('./include/html/header.php'); ?>
                <!-- main header @e -->
                <?php include('./include/html/submenu-sx.php'); ?>
                <!-- content @s -->
                <div class="nk-content ">
                    <div class="container-fluid">
                        <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <!-- Contenuto Pabina -->
                                <div class="nk-block">
                                    <div class="row g-gs">


                                        <?= $pageContent; ?>


                                    </div><!-- .row -->
                                </div><!-- .nk-block -->
                                <!-- Contenuto Pabina -->

                            </div>
                        </div>
                    </div>
                </div>
                <!-- content @e -->
            </div>
            <!-- wrap @e -->
        </div>
        <!-- main @e -->
    </div>
    <!-- app-root @e -->

    <div class="modal fade" tabindex="-1" role="dialog" id="region">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <a href="#" class="close" data-bs-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                <div class="modal-body modal-body-md">
                    <h5 class="title mb-4">Select Your Country</h5>
                    <div class="nk-country-region">
                        <ul class="country-list text-center gy-2">
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/arg.png" alt="" class="country-flag">
                                    <span class="country-name">Argentina</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/aus.png" alt="" class="country-flag">
                                    <span class="country-name">Australia</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/bangladesh.png" alt="" class="country-flag">
                                    <span class="country-name">Bangladesh</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/canada.png" alt="" class="country-flag">
                                    <span class="country-name">Canada <small>(English)</small></span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/china.png" alt="" class="country-flag">
                                    <span class="country-name">Centrafricaine</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/china.png" alt="" class="country-flag">
                                    <span class="country-name">China</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/french.png" alt="" class="country-flag">
                                    <span class="country-name">France</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/germany.png" alt="" class="country-flag">
                                    <span class="country-name">Germany</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/iran.png" alt="" class="country-flag">
                                    <span class="country-name">Iran</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/italy.png" alt="" class="country-flag">
                                    <span class="country-name">Italy</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/mexico.png" alt="" class="country-flag">
                                    <span class="country-name">México</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/philipine.png" alt="" class="country-flag">
                                    <span class="country-name">Philippines</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/portugal.png" alt="" class="country-flag">
                                    <span class="country-name">Portugal</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/s-africa.png" alt="" class="country-flag">
                                    <span class="country-name">South Africa</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/spanish.png" alt="" class="country-flag">
                                    <span class="country-name">Spain</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/switzerland.png" alt="" class="country-flag">
                                    <span class="country-name">Switzerland</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/uk.png" alt="" class="country-flag">
                                    <span class="country-name">United Kingdom</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="country-item">
                                    <img src="./images/flags/english.png" alt="" class="country-flag">
                                    <span class="country-name">United State</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div><!-- .modal-content -->
        </div><!-- .modla-dialog -->
    </div><!-- .modal -->
    <!-- Modal -->
    <div class="modal" id="Modal">
        <div class="modal-dialog" id="modal-dialog" role="document">
            <div class="modal-content">
                <a onClick="chiudiModal()" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
                <div class="modal-header">
                    <h5 class="modal-title" id="titoloModal">&nbsp;</h5>
                </div>
                <div class="modal-body" id="contentModal">
                    &nbsp;
                </div>
                <div class="modal-footer bg-light">
                    <span class="sub-text" id="footerModal">&nbsp;</span>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalLarge" tabindex="-1" aria-labelledby="titoloModalLarge" aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal-dialog-inner" role="document">
            <div class="modal-content">
                <a onClick="chiudiModalLarge()" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
                <div class="modal-header">
                    <h5 class="modal-title" id="titoloModalLarge">&nbsp;</h5>
                </div>
                <div class="modal-body" id="contentModalLarge">
                    &nbsp;
                </div>
                <div class="modal-footer bg-light">
                    <span class="sub-text" id="footerModalLarge">&nbsp;</span>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <!-- Il file bundle.js di DashLite include già jQuery, quindi non è necessario caricarlo separatamente -->
    <?php if ($serp != '/dashboard') { ?>
        <script src="/assets/js/bundle.js?ver=3.1.3"></script>
        <script src="/assets/js/scripts.js?ver=3.1.3"></script>
    <?php } ?>

    <!-- Librerie e plugin -->
    <script src="/assets/js/libs/fullcalendar.js?ver=3.1.3"></script>
    <script src="/assets/js/libs/calendar.js?ver=3.1.3"></script>
    <!-- Aggiunta localizzazione italiana per Bootstrap Datepicker -->
    <script src="/js/bootstrap-datepicker.it.min.js"></script>
    <script src="/assets/js/libs/tagify.js?ver=3.1.3"></script>
    <script src="/assets/js/libs/editors/tinymce.js?ver=3.1.3"></script>
    <script src="/assets/js/libs/jqvmap.js?ver=3.1.3"></script>
    <script src="/assets/js/libs/datatable-btns.js?ver=3.1.3"></script>
    <script src="/asset/chart.umd.min.js"></script> <!-- Assumendo che /asset/ sia corretto per questo file -->
    <script src="/asset/js/select2.min.js"></script> <!-- Assumendo che /asset/js/ sia corretto per questo file -->

    <!-- Script delle app del template -->
    <!-- Il seguente script causa errori se la pagina non contiene un elemento calendario. -->
    <!-- Va decommentato e spostato solo nelle pagine che utilizzano FullCalendar. -->
    <?php if ($serp == "/calendario_esami" || $serp == "/calendario_rate" || $serp == '/reportistica_prodotti') { ?>
        <script src="/assets/js/apps/calendar.js?ver=3.1.3"></script>
    <?php } ?>
    <script src="/assets/js/editors.js?ver=3.1.3"></script>
    <script src="/assets/js/charts/gd-analytics.js?ver=3.1.3"></script>

    <!-- Script custom -->
    <script src="/assets/js/codice-fiscale.js?ver=1.0.1"></script>

    <!-- Stili per componenti caricati via JS -->
    <link rel="stylesheet" href="/assets/css/editors/tinymce.css?ver=3.1.3">

    <script>
        (function (NioApp, $) {
            'use strict';
            // Attendiamo che il DOM sia pronto per sovrascrivere la funzione del datepicker
            $(function () {
                if (NioApp && NioApp.Picker && typeof $.fn.datepicker === 'function') {
                    // Sovrascriviamo la funzione NioApp.Picker.date per personalizzare il comportamento
                    NioApp.Picker.date = function (selector, options) {
                        $(selector).each(function () {
                            var $visibleInput = $(this);
                            var originalId = $visibleInput.attr('id');

                            // Per i date-range o elementi senza ID, usiamo l'implementazione standard del datepicker
                            // senza la nostra logica di campo nascosto.
                            if ($visibleInput.hasClass('date-picker-range') || !originalId) {
                                $(this).datepicker(options);
                                return; // Passa all'elemento successivo
                            }

                            // --- Logica personalizzata per il doppio formato ---
                            var originalName = $visibleInput.attr('name'); // e.g. "dataNascita"

                            // Il campo visibile mantiene il suo ID originale (es. "dataNascita") per compatibilità con altri script,
                            // ma gli viene rimosso il 'name' per non inviarlo al server.
                            $visibleInput.removeAttr('name');

                            // Crea un campo nascosto che prenderà il 'name' originale per l'invio al server
                            // e avrà un ID distinto. Conterrà il valore in formato aaaa-mm-gg.
                            var $hiddenInput = $('<input type="hidden">')
                                .attr('name', originalName)
                                .attr('id', originalId + '_iso') // es. "dataNascita_iso"
                                .val($visibleInput.val()) // Mantiene il valore iniziale se c'è
                                .insertAfter($visibleInput);

                            // Opzioni di default per il nostro picker personalizzato
                            var defaultOptions = {
                                format: 'dd/mm/yyyy', // Formato visualizzato all'utente
                                language: 'it',
                                autoclose: true,
                                todayHighlight: true
                            };

                            // Uniamo le opzioni di default con quelle eventualmente passate nella chiamata
                            var finalOptions = $.extend({}, defaultOptions, options);

                            // Inizializza il datepicker sul campo visibile
                            $visibleInput.datepicker(finalOptions);

                            // Definiamo una funzione helper per aggiornare il campo nascosto
                            var updateHiddenInput = function (date) {
                                if (date) {
                                    var year = date.getFullYear();
                                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                                    var day = ('0' + date.getDate()).slice(-2);
                                    var isoDate = year + '-' + month + '-' + day;
                                    $hiddenInput.val(isoDate);
                                } else {
                                    $hiddenInput.val('');
                                }
                            };

                            // Evento per quando l'utente seleziona una data dal calendario
                            $visibleInput.on('changeDate', function (e) {
                                updateHiddenInput(e.date);
                            });

                            // Evento 'change' per catturare le modifiche programmatiche (es. da codice-fiscale.js)
                            $visibleInput.on('change', function () {
                                updateHiddenInput($(this).datepicker('getDate'));
                            });

                            // Se c'è un valore iniziale nel campo (es. da DB in formato aaaa-mm-gg),
                            // lo formatta correttamente per la visualizzazione.
                            // Al caricamento, il valore è ancora nell'input visibile.
                            var initialValue = $visibleInput.val();
                            if (initialValue && /^\d{4}-\d{2}-\d{2}/.test(initialValue)) {
                                $hiddenInput.val(initialValue); // Sposta il valore ISO nel campo nascosto
                                var parts = initialValue.split(' ')[0].split('-');
                                // new Date(year, monthIndex, day)
                                var displayDate = new Date(parts[0], parseInt(parts[1], 10) - 1, parts[2]);
                                $visibleInput.datepicker('update', displayDate);
                            }
                        });
                    };
                }
            });
        })(NioApp, jQuery);

        // Inizializzazione globale dei componenti all'avvio della pagina
        $(function () {
            // Questa funzione viene eseguita una volta che il DOM è pronto.
            // Inizializza tutti i componenti NioApp presenti nella pagina al caricamento iniziale.
            if (NioApp.Select2) {
                NioApp.Select2('.js-select2');
            }
            if (NioApp.Picker) {
                NioApp.Picker.date('.date-picker');
                NioApp.Picker.dob('.date-picker-alt');
                NioApp.Picker.time('.time-picker');
                NioApp.Picker.date('.date-picker-range', {
                    todayHighlight: false,
                    autoclose: false
                });
            }
            if (NioApp.BS) {
                NioApp.BS.fileinput('.form-file-input');
            }

            // Script per il reindirizzamento alla selezione del master in /settings/esami_master/
            // Viene eseguito solo se nella pagina è presente il selettore con name="cercaMaster".
            var masterSelect = $('select[name="cercaMaster"]');
            if (masterSelect.length) {
                // L'evento 'change' funziona anche con il plugin Select2
                masterSelect.on('change', function () {
                    var selectedId = $(this).val();
                    // Reindirizza solo se è stato selezionato un valore valido
                    if (selectedId && selectedId !== '#') {
                        window.location.href = '/settings/esami_master/' + selectedId;
                    }
                });
            }
        });

        //Funzione per aprire il modal
        async function apriModal(a, b, c, d = 'new', table = '', remove = '') {
            var url = "/include/componenti/" + c + ".php";
            if (d == "new") {
                var param = '';
            } else {
                var param = 'id=' + d;
            }
            try {
                var contenuto = await componente(url, param);

                var divModal = document.getElementById("Modal");
                // Titolo MODAL
                var titoloModal = document.getElementById('titoloModal');
                titoloModal.innerHTML = a;
                // Gestione Contenuto Modal
                var contentModal = document.getElementById('contentModal');
                contentModal.innerHTML = contenuto;

                // Inizializza i componenti JS dopo aver caricato il contenuto nel modale.
                // Rimosso $(document).ready() perché non è necessario e può causare problemi qui.
                NioApp.Select2('.js-select2');

                // Inizializzazione dei datepicker. La nostra funzione custom si occuperà del formato.
                NioApp.Picker.date('.date-picker');
                NioApp.Picker.dob('.date-picker-alt');
                NioApp.Picker.time('.time-picker');
                NioApp.Picker.date('.date-picker-range', {
                    todayHighlight: false,
                    autoclose: false
                });

                NioApp.BS.fileinput('.form-file-input');
                if (table == 1) {
                    NioApp.DataTable('.datatable-init-export', {
                        responsive: {
                            details: true
                        },
                        buttons: ['copy', 'excel', 'csv', 'pdf']
                    });
                    $.fn.DataTable.ext.pager.numbers_length = 7;
                }

                if (remove == 1) {
                    $riga = document.getElementById('dom_' + d);
                    $riga.remove();
                }

                // Footer MODAL   
                var footerModal = document.getElementById('footerModal');
                footerModal.innerHTML = b;

                divModal.style.display = 'block';
                divModal.classList.add('show');
            } catch (error) {
                console.error('Errore:', error);
            }
        }
        //Funzione per aprire il modal
        function chiudiModal() {
            var divModal = document.getElementById("Modal");
            //Titolo MODAL
            var titoloModal = document.getElementById('titoloModal');
            titoloModal.innerHTML = '&nbsp;';
            //Contenuto MODAL
            var contentModal = document.getElementById('contentModal');
            contentModal.innerHTML = '&nbsp;';
            //Footer MODAL
            var footerModal = document.getElementById('footerModal');
            footerModal.innerHTML = '&nbsp;';
            // Mostra il modal
            divModal.style.display = 'none'
            divModal.classList.remove('show');
        };
        //Funzione per aprire il modal Large
        async function apriModalLarge(a, b, c, d = 'new', table = '') {
            var url = "/include/componenti/" + c + ".php";
            if (d == "new") {
                var param = '';
            } else {
                var param = 'id=' + d;
            }
            try {
                var contenuto = await componente(url, param);

                // Ora divModal punta al contenitore esterno, come in Bootstrap
                var divModal = document.getElementById("ModalLarge");

                // Titolo MODAL
                var titoloModal = document.getElementById('titoloModalLarge');
                titoloModal.innerHTML = a;
                // Gestione Contenuto Modal
                var contentModal = document.getElementById('contentModalLarge');
                contentModal.innerHTML = contenuto;

                // Inizializza i componenti JS dopo aver caricato il contenuto nel modale.
                NioApp.Select2('.js-select2');

                // Inizializzazione dei datepicker. La nostra funzione custom si occuperà del formato.
                NioApp.Picker.date('.date-picker');
                NioApp.Picker.dob('.date-picker-alt');
                NioApp.Picker.time('.time-picker');
                NioApp.Picker.date('.date-picker-range', {
                    todayHighlight: false,
                    autoclose: false
                });
                NioApp.BS.fileinput('.form-file-input');
                if (table == 1) {
                    NioApp.DataTable('.datatable-init-export', {
                        responsive: {
                            details: true
                        },
                        buttons: ['copy', 'excel', 'csv', 'pdf']
                    });
                    $.fn.DataTable.ext.pager.numbers_length = 7;
                }

                // Footer MODAL   
                var footerModal = document.getElementById('footerModalLarge');
                footerModal.innerHTML = b;

                divModal.style.display = 'block';
                divModal.classList.add('show');
                divModal.setAttribute('aria-hidden', 'false'); // ✅ fix accessibilità

            } catch (error) {
                console.error('Errore:', error);
            }
        }
        //Funzione per aprire il modal Large
        function chiudiModalLarge() {
            var divModal = document.getElementById("ModalLarge");
            //Titolo MODAL
            var titoloModal = document.getElementById('titoloModalLarge');
            titoloModal.innerHTML = '&nbsp;';
            //Contenuto MODAL
            var contentModal = document.getElementById('contentModalLarge');
            contentModal.innerHTML = '&nbsp;';
            //Footer MODAL
            var footerModal = document.getElementById('footerModalLarge');
            footerModal.innerHTML = '&nbsp;';
            // Mostra il modal
            divModal.style.display = 'none'
            divModal.classList.remove('show');
            divModal.setAttribute('aria-hidden', 'true'); // ✅ restore per screen reader
        };
        //Funzione per ridimensionare il modale
        function ridimensionaModale(larghezza, altezza) {
            var divModal = document.getElementById("modal-dialog");
            if (divModal) {
                divModal.style.width = larghezza;
                divModal.style.height = altezza;
                divModal.style.maxWidth = larghezza; //Rimuove l'altezza massima
                // Imposta anche lo stile per il contentModal per assicurarti che si adatti.
                var contentModal = document.getElementById("contentModal");
                if (contentModal) {
                    contentModal.style.maxHeight = 'none'; //Rimuove l'altezza massima

                }
            }
        }
        //Funzione Componente
        function componente(a, b = '') {
            return new Promise(function (resolve, reject) {
                // Esegui una richiesta AJAX
                var xhr = new XMLHttpRequest();
                var url = a; // Sostituisci con il percorso al tuo script PHP

                xhr.open('POST', url, true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            // La richiesta è stata completata con successo
                            var response = xhr.responseText;
                            resolve(response);
                        } else {
                            reject('Errore nella richiesta AJAX');
                        }
                    }
                };
                // Invia i dati al server
                xhr.send(b);
            });
        }
        //Funzione per verificare i campi obbligatori
        function validaCampiObbligatori(formData, campiObbligatori) {
            var campiNonValidi = [];

            campiObbligatori.forEach(function (campo) {
                var valore = formData[campo];
                if (
                    valore === undefined ||
                    valore === null ||
                    (typeof valore === 'string' && valore.trim() === '') ||
                    (Array.isArray(valore) && valore.length === 0)
                ) {
                    campiNonValidi.push(campo);
                }
            });

            return campiNonValidi.length > 0 ? campiNonValidi : true;
        }
        //Funzione per la Raccolta dati di un FORM
        function raccoltadati(a, b) {
            // Seleziona tutti gli input, radio button e selezioni nel form
            var form = document.getElementById(b);
            var inputs = form.querySelectorAll('input[type="hidden"], input[type="text"], input[type="radio"]:checked, select');

            var formData = {};
            formData['tipo_action'] = a;
            inputs.forEach(function (input) {
                if (input.type === 'radio') {
                    formData[input.id] = input.value; // Per radio, aggiungi solo quelli selezionati
                } else if (input.type === 'select-multiple') {
                    // Per multi select, prendi tutti i valori selezionati
                    var selectedOptions = Array.from(input.selectedOptions).map(option => option.value);
                    formData[input.id] = selectedOptions;
                } else {
                    formData[input.id] = input.value; // Aggiungi il valore dell'input
                }
            });

            console.log(formData); // Mostra i dati raccolti nella console o fai altro con essi
            inviaFormConDati(formData);
        }
        //Funzione modificata con il controllo dei dati prima di essere inseriti con la serializzazione dei checkbox
        function raccoltadati_controllo(azione, formId, campiObbligatori) {
            var form = document.getElementById(formId);
            if (!form) {
                console.error("Form con ID '" + formId + "' non trovato.");
                return false;
            }

            // Selettore per tutti i campi che potrebbero essere validati o inviati (inclusi file per validazione)
            var allPotentialFields = form.querySelectorAll('input[type="hidden"], input[type="text"], input[type="number"], input[type="email"], input[type="file"], input[type="radio"]:checked, select, textarea');

            var formDataPerValidazione = {};
            // 'action' è già nel form, non serve per la validazione a meno che non sia in campiObbligatori

            allPotentialFields.forEach(function (input) {
                var fieldName = input.id || input.name;
                if (!fieldName) return; // Salta se non ha né id né nome

                if (input.type === 'radio') {
                    // Per i radio, solo quello checkato viene preso dal selettore
                    formDataPerValidazione[fieldName] = input.value;
                } else if (input.type === 'select-multiple') {
                    var selectedOptions = Array.from(input.selectedOptions).map(option => option.value);
                    formDataPerValidazione[fieldName] = selectedOptions;
                } else if (input.type === 'file') {
                    // Per la validazione, verifico se almeno un file è stato selezionato.
                    // Il valore per la validazione sarà il nome del primo file o una stringa vuota.
                    formDataPerValidazione[fieldName] = input.files && input.files.length > 0 ? input.files[0].name : '';
                } else if (input.tagName.toLowerCase() === 'textarea') {
                    formDataPerValidazione[fieldName] = input.value;
                } else {
                    // text, hidden, number, email, select-one
                    formDataPerValidazione[fieldName] = input.value;
                }
            });

            // Gestione specifica per checkbox 'giorni[]' (se esiste nel form)
            var checkboxesGiorni = form.querySelectorAll('input[name="giorni[]"]:checked');
            if (form.querySelector('input[name="giorni[]"]')) { // Controlla se esistono checkbox con questo nome
                var giorniSelezionati = [];
                checkboxesGiorni.forEach(function (checkbox) {
                    giorniSelezionati.push(checkbox.value);
                });
                formDataPerValidazione['giorni'] = giorniSelezionati; // Anche se vuoto, per la validazione
            }

            // ✅ Validazione dei campi obbligatori ricevuti come parametro
            var esitoValidazione = validaCampiObbligatori(formDataPerValidazione, campiObbligatori);

            if (esitoValidazione !== true) {
                alert('Compila i seguenti campi obbligatori: ' + esitoValidazione.join(', '));
                return false;
            }

            // Verifica la presenza di campi file e l'enctype del form
            var hasFileInputs = form.querySelector('input[type="file"]') !== null;
            var isMultipart = form.getAttribute('enctype') === 'multipart/form-data';

            if (hasFileInputs && isMultipart) {
                // Se ci sono file e l'enctype è corretto, sottometti il form originale.
                // Il campo 'action' dovrebbe già essere nel form originale con il valore corretto.
                form.submit();
                return false; // Previene il submit predefinito del browser
            } else {
                // Altrimenti, usa il metodo `inviaFormConDati` con `mainForm`.
                // Raccogli i dati per `mainForm`, escludendo i campi file.
                var formDataPerMainForm = {};
                formDataPerMainForm['action'] = azione; // Azione per il mainForm

                var fieldsForMainForm = form.querySelectorAll('input[type="hidden"], input[type="text"], input[type="number"], input[type="email"], input[type="radio"]:checked, select, textarea');
                fieldsForMainForm.forEach(function (input) {
                    var fieldName = input.id || input.name;
                    if (!fieldName) return;

                    if (input.type === 'radio') {
                        formDataPerMainForm[fieldName] = input.value;
                    } else if (input.type === 'select-multiple') {
                        var selectedOptions = Array.from(input.selectedOptions).map(option => option.value);
                        formDataPerMainForm[fieldName] = selectedOptions;
                    } else if (input.tagName.toLowerCase() === 'textarea') {
                        formDataPerMainForm[fieldName] = input.value;
                    } else { // text, hidden, number, email, select-one
                        formDataPerMainForm[fieldName] = input.value;
                    }
                });
                if (form.querySelector('input[name="giorni[]"]')) {
                    formDataPerMainForm['giorni'] = formDataPerValidazione['giorni'];
                }
                inviaFormConDati(formDataPerMainForm);
                return false; // Previene il submit predefinito del browser
            }
        }
        // Funzione per aggiungere i dati al modulo e inviarlo
        function inviaFormConDati(formData) {
            // Seleziona il modulo
            var form = document.getElementById('mainForm');

            // Rimuovi eventuali input nascosti precedenti per evitare duplicazioni
            var hiddenInputs = form.querySelectorAll('input[type="hidden"]');
            hiddenInputs.forEach(function (input) {
                input.remove();
            });

            // Aggiungi i dati di formData al modulo come input nascosti
            for (var key in formData) {
                if (formData.hasOwnProperty(key)) {
                    if (Array.isArray(formData[key])) {
                        // Se il valore è un array (es. per select multiple)
                        formData[key].forEach(function (value) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key + '[]'; // Indica un array in PHP
                            input.value = value;
                            form.appendChild(input);
                        });
                    } else {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = formData[key];
                        form.appendChild(input);
                    }
                }
            }

            // Effettua il submit del modulo
            form.submit();
        }
        //Funzione per confermare il DELETE
        function confermaDelete() {
            return confirm("Sei sicuro di voler cancellare il dato?");
        }
        //Script per il funzionamento dell'upload file in settings/esami
        function mostraUpload() {
            var uploadDiv = document.getElementById('uploadDiv');
            var button = document.getElementById('buttoncsv');
            var form = document.getElementById('uploadfileForm');
            var fileInput = document.getElementById('fileInput');

            if (!fileInput) {
                alert("Campo file non trovato. Verifica che l'input abbia id='fileInput'.");
                return;
            }

            if (uploadDiv.style.display === "none" || uploadDiv.style.display === "") {
                uploadDiv.style.display = "block";
                button.textContent = "Invia CSV";
            } else {
                if (fileInput.files.length > 0) {
                    form.submit();
                } else {
                    alert("Seleziona un file CSV prima di inviare.");
                }
            }
        }

    </script>
</body>

</html>