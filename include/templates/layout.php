<!DOCTYPE html>
<html lang="ita" class="js">
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
        <div class="modal" id="Modal" >
            <div class="modal-dialog" role="document">
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


    <!-- Modal -->
    <!-- JavaScript -->
    <script src="/assets/js/bundle.js?ver=3.1.3"></script>
    <script src="/assets/js/scripts.js?ver=3.1.3"></script>
    <script src="/assets/js/libs/tagify.js?ver=3.1.3"></script>
    <link rel="stylesheet" href="/assets/css/editors/tinymce.css?ver=3.1.3">
    <script src="/assets/js/libs/editors/tinymce.js?ver=3.1.3"></script>
    <script src="/assets/js/editors.js?ver=3.1.3"></script>
    <script src="/assets/js/charts/gd-analytics.js?ver=3.1.3"></script>
    <script src="/assets/js/libs/jqvmap.js?ver=3.1.3"></script>
    <script src="/assets/js/libs/datatable-btns.js?ver=3.1.3"></script>
    
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        //Funzione per aprire il modal
        async function apriModal(a, b, c, d = 'new', table = '') {
            var url = "/include/componenti/" + c + ".php";
            if(d=="new"){
                var param='';
            } else {
                var param='id='+d;
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
                $('#contentModal select').select2();
                $(document).ready(function() {
                    if(table==1){
                        NioApp.DataTable('.datatable-init-export', {
                            responsive: {
                                details: true
                            },
                            buttons: ['copy', 'excel', 'csv', 'pdf']
                        });
                        $.fn.DataTable.ext.pager.numbers_length = 7;
                    }
                });
                // Footer MODAL   
                var footerModal = document.getElementById('footerModal');
                footerModal.innerHTML = b;
                // Mostra il modal
                divModal.style.display = 'block';
                divModal.classList.add('show');
            } catch (error) {
                console.error('Errore:', error);
            }
        }
        //Funzione per ridimensionare il modale
        function ridimensionaModale(larghezza, altezza) {
            var divModal = document.getElementById("Modal");
            if (divModal) {
                divModal.style.width = larghezza;
                divModal.style.height = altezza;
                // Imposta anche lo stile per il contentModal per assicurarti che si adatti.
                var contentModal = document.getElementById("contentModal");
                if (contentModal) {
                contentModal.style.maxHeight = 'none'; //Rimuove l'altezza massima
                }
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
        //Funzione per la Raccolta dati di un FORM
        function raccoltadati(a,b){
            // Seleziona tutti gli input, radio button e selezioni nel form
            var form = document.getElementById(b);
            var inputs = form.querySelectorAll('input[type="hidden"], input[type="text"], input[type="radio"]:checked, select');

            var formData = {};
            formData['tipo_action']=a;
            inputs.forEach(function(input) {
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
        // Funzione per aggiungere i dati al modulo e inviarlo
        function inviaFormConDati(formData) {
            // Seleziona il modulo
            var form = document.getElementById('mainForm');
            
            // Rimuovi eventuali input nascosti precedenti per evitare duplicazioni
            var hiddenInputs = form.querySelectorAll('input[type="hidden"]');
            hiddenInputs.forEach(function(input) {
                input.remove();
            });

            // Aggiungi i dati di formData al modulo come input nascosti
            for (var key in formData) {
                if (formData.hasOwnProperty(key)) {
                    if (Array.isArray(formData[key])) {
                        // Se il valore è un array (es. per select multiple)
                        formData[key].forEach(function(value) {
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

    </script>

</body>

</html>