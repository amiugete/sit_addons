<?php
//session_set_cookie_params($lifetime);
session_start();

    
?>
<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Gestione piazzole PAP</title>
<?php 
require_once('./req.php');

the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}
?> 

</head>

<body>

<?php 
require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ((int)$id_role_SIT = 0) {
  redirect('no_permessi.php');
  //exit;
}

$cod_percorso= $_GET['cp'];
$versione= $_GET['v'];
?>


<div class="container">
    <div class="row g-3 align-items-center">
        <form id="piazzolapap" name="piazzolapap" method="post" autocomplete="off" action="">
            <div class="row g-3 align-items-center">
                <div class="card shadow-sm p-3">
                    <div class ="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <i class="bi bi-trash3-fill"></i><label for="tipo_elem"> Tipi Elementi </label> <font color="red">*</font> 
                                <select name="telem" id="telem" class="selectpicker show-tick form-control" data-live-search="true" data-size="5" onchange="writelist();">
                                    <option name="telem" value="">Seleziona un tipo elemento</option>
                                    <?php 
                                    $query2="select te.tipo_elemento, te.descrizione, te.tipologia_elemento   from elem.tipi_elemento te 
                                        where te.in_piazzola = 1 and te.tipologia_elemento not in ('N', 'I') and te.tipo_rifiuto is not null
                                        order by te.descrizione";
                                    $result2 = pg_query($conn_sit, $query2);  
                                    while($r2 = pg_fetch_assoc($result2)) {          
                                    ?>    
                                        <option name="telem" value="<?php echo $r2['tipo_elemento']?>" ><?php echo $r2['descrizione'] ?></option>
                                    <?php } ?>
                                </select>            
                            </div>

                            <div class="form-group  col-md-6">
                                <label for="lista_elem">Elenco elementi selezionati:</label>
                                <textarea readonly id="lista_elem" name="lista_elem" rows="4"  class="form-control" required>
                                </textarea>
                                <input type="hidden" id="lista_elementi_valori" name="lista_elementi_valori" value="">
                                <input type="hidden" id="lista_elementi_nomi" name="lista_elementi_nomi" value="">
                                <div class="form-group" style="display: flex; margin-top:2%;">
                                    <small class="text-muted" >
                                        <a href="#" class="btn btn-warning btn-sm" id="removeline" ><i class="bi bi-x-circle-fill"></i>Elimina ultimo elemento</a>
                                        <a href="#" class="btn btn-danger btn-sm" id="aggiorna" ><i class="fas fa-redo"></i>Elimina intero elenco</a>
                                    </small>
                                </div>
                                <script>
                                    $(document).ready(function() {
                                        $('#removeline').click(function() {
                                        //cancello ulrima riga del textarea
                                        var le = $('#lista_elem');
                                        var text_le = le.val().trim("\n");
                                        var valuelist_le = text_le.split("\n");
                                        var last_le = valuelist_le[valuelist_le.length - 1];
                                        replace_le=text_le.replace(last_le, "").replace(/\n$/, "")
                                        le.val(replace_le)

                                        // stessa cosa per i campi nascosti
                                        var le_valori = $('#lista_elementi_valori');
                                        var text_le_valori = le_valori.val().split(",");
                                        text_le_valori.pop();
                                        le_valori.val(text_le_valori)

                                        var le_nomi = $('#lista_elementi_nomi');
                                        console.log('lista nomi prima: '+le_nomi.val())
                                        var text_le_nomi = le_nomi.val().split(",");
                                        text_le_nomi.pop();
                                        le_nomi.val(text_le_nomi)

                                        console.log('lista elementi: '+le.val())
                                        console.log('lista nomi dopo: '+le_nomi.val())
                                        console.log('lista valori dopo: '+le_valori.val())
                                        })
                                    });
                                    $(document).ready(function() {
                                        $('#aggiorna').click(function() {
                                            // pulisco tutto compresi i campi nascosti
                                            $('#lista_elem').val('');
                                            $('#lista_elementi_valori').val('');
                                            $('#lista_elementi_nomi').val('');
                                        })
                                    });

                                </script>
                            </div>
                            <div class="form-group">
                                <div id="toast_req1" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 align-items-center">
                <div class="card shadow-sm p-3">
                    <div class ="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <i class="bi bi-people-fill"></i><label for="tcliente"> Tipo cliente </label> <font color="red">*</font> 
                                <select name="tcliente" id="tcliente" class="selectpicker show-tick form-control" data-live-search="true" data-size="5">
                                <option name="tcliente" value="">Seleziona un tipo cliente</option>
                                <?php  
                                $queryc="select * from utenze.macro_categorie mc
                                order by 2";
                                $resultc = pg_query($conn_sit, $queryc); 
                                while($rc = pg_fetch_assoc($resultc)) {            
                                ?>   
                                    <option name="tcliente" value="<?php echo $rc['id_macro_categoria']?>" ><?php echo $rc['descrizione'] ?></option>
                                <?php } ?>

                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <i class="bi bi-basket3-fill"></i><label for="desc"> Nominativo attività </label> <font color="red">*</font>
                                <input type="text" name="desc" id="desc" class="form-control">
                            </div>
                            <div class="form-group col-md-2">
                                <i class="bi bi-house-lock-fill"></i><label for="suolo_privato"> Suolo privato? </label> <font color="red">*</font>
                                <select name="suolo_privato" id="suolo_privato" class="form-control" required="">
                                    <option value="false">No</option>
                                    <option value="true">Sì</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div id="toast_req2" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 align-items-center">
                <div class="card shadow-sm p-3">
                    <div class ="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <i class="bi bi-globe-europe-africa"></i><label for="comune_list">Comune:</label> <font color="red">*</font>
                                <select name="comune_list" id="comune_list" class="selectpicker show-tick form-control" 
                                data-live-search="true" required="">
                                <option value="">Seleziona il comune</option>
                                <?php            
                                $query2="select id_comune, descr_comune  from topo.comuni
                                    where id_comune <> 3
                                    order by 2";
                                $result2 = pg_query($conn_sit, $query2);
                                while($r2 = pg_fetch_assoc($result2)) { 
                                    $valore=  $r2['id_comune']. ";".$r2['descr_comune'];            
                                ?>     
                                    <option name="comune_list" value="<?php echo $r2['id_comune'];?>" ><?php echo $r2['descr_comune'];?></option>
                                <?php } ?>

                                </select> 
                            </div>
                            <div class="form-group col-md-6">
                                <i class="bi bi-geo-alt-fill"></i><label for="via_list">Via:</label> <font color="red">*</font>
                                            <select name="via_list" id="via_list" class="selectpicker show-tick form-control" data-dropup-auto="false"
                                            data-live-search="true" required="" disabled>
                                                <option value="">Seleziona la via</option>
                                            </select> 
                            </div>
                            <div class="form-group col-md-2">
                                <i class="bi bi-geo-fill"></i><label for="civ_list">Civico:</label>
                                <select name="civ_list" id="civ_list" class="selectpicker show-tick form-control" data-live-search="true" required="" disabled>
                                <option name="civ_list" value="">Seleziona il civico</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 align-items-center" id="dettagli_piazzola" style="display:none;">
                <div class="card shadow-sm p-3" id="card_piazzola">
                    <div class ="card-body">
                        <div class="row">
                            <div class="form-group">
                                <div id="toast" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%;"></div>
                            </div>
                            <div class="form-group col-md-12">
                                <div class="row" id="dettagli_piazzola_content" style="display: flex; justify-content: center; align-items: center;">
                                    
                                </div>
                            </div>
                            <div class="form-group">
                                <div id="toast_end" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- RIPARTIRE DA QUI   
            In questo caso deve anche comparire un bottone per modificare i dettagli della piazzola trovata. Per una soluzione veloce, il bottone apre link a sit in altra scheda,
            a regime dovrà aprire un modal con dettagli editabili della piazzola.
            -->
        </form>
    </div>
</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>



<script type="text/javascript" >

    $(document).on("keydown", ":input:not(textarea)", function(event) {
        if (event.key == "Enter") {
            event.preventDefault();
        }
    });


	function writelist(){
		var elem_value=$("#telem option:selected").val(); //get the value of the current selected option.
        console.log(elem_value);
        var elem_text=$("#telem option:selected").text(); //get the text of the current selected option.
		console.log(elem_text);

        if (elem_value === '') return;

        var lista_elementi = document.getElementById("lista_elem");
        lista_elementi.value = lista_elementi.value.trim();

        

        if (lista_elementi.value != '') {
            // se la text area non è vuota, aggiungo ai valori esistenti una nuova riga con il nuovo elemento
            lista_elementi.value = lista_elementi.value + '\n' + elem_text;
        } else {
            lista_elementi.value = elem_text;
        }

        // creo una lista con i soli codici elementi, li salvo in un campo nascosto del form in modo da inviarli al submit
        var codici_list = document.getElementById("lista_elementi_valori");
        if (codici_list.value != "") {
            // il campo ha già dei valori aggiungo la virgola e il nuovo valore
            codici_list.value = codici_list.value + ',' + elem_value;
        } else {
            // il campo è ancora vuoto scrivo solo il valore, senza virgola iniziale
            codici_list.value = elem_value;
        }

        
        // creo una lista con i soli nomi elementi, li salvo in un campo nascosto del form in modo da inviarli al submit
        var nomi_list = document.getElementById("lista_elementi_nomi");
        if (nomi_list.value != "") {
            // il campo ha già dei valori  aggiungo la virgola e il nuovo valore
            nomi_list.value = nomi_list.value + ',' + elem_text;
        } else {
            // il campo è ancora vuoto  scrivo solo il valore, senza virgola iniziale
            nomi_list.value = elem_text;
        }

        //rimette la select del tipo elemento al valore di default (placeholder)
        $("#telem").selectpicker('val', '');
	} 

</script>

<script>

    const comuneSelect = document.getElementById('comune_list');
    const viaSelect = document.getElementById('via_list');
    const civicoSelect = document.getElementById('civ_list');

    //funzione agganciata al select del comune
    comuneSelect.addEventListener('change', function () {

        let id_comune = this.value;
        console.log('Comune selezionato: ' + id_comune);

        if(id_comune === '') {
            civicoSelect.disabled = true;
            viaSelect.disabled = true;
            return;
        }

        if (id_comune != 1) {
            console.log('Comune diverso da 1 (Genova), disabilito civico e via');
            civicoSelect.disabled = true;
            viaSelect.disabled = true;
            showToast('Hai selezionato un comune del genovesato, al momento i dati relativi ai numeri civici non sono disponibili', 'error', 'top');
            //verifico se ho già una classe border-* e in caso la rimuovo per evitare conflitti di stile
            if (/\bborder\S*/.test($('#card_piazzola')[0].className)) {
                $('#card_piazzola')[0].className = $('#card_piazzola')[0].className.replace(/\bborder\S*\s?/g, '');                    
            }
            $('#card_piazzola').addClass('border-danger');
            $('#card_piazzola').css({"border-width": "medium"});
            document.getElementById('dettagli_piazzola').style.display = "flex";
            if ($('#card_piazzola').is(':hidden')) {
                $('#card_piazzola').show();
            }
            return;     
        }
        
        // popolo il select della via in funzione del comune selezionato
        fetch('backoffice/get_vie.php?id_comune=' + id_comune)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Seleziona la via</option>';
                data.forEach(function(via) {
                options += `
                    <option value="${via.id_via}">
                        ${via.nome}
                    </option>
                `;
                });

                $('#via_list').selectpicker('destroy');
                $('#via_list').html(options);
                $('#via_list').prop('disabled', false);
                $('#via_list').selectpicker();
                
            })

            .catch(error => {
                viaSelect.innerHTML =
                    '<option value="">Errore caricamento</option>';
                console.error(error);
            });

    });

    //funzione agganciata al select della via
    viaSelect.addEventListener('change', function () {
  
        id_comune=$("#comune_list option:selected").val();
        console.log('Comune selezionato: ' + id_comune);

        let id_via = this.value;
        console.log('Via selezionata: ' + id_via);

        if(id_via === '') {
            civicoSelect.disabled = true;
            return;
        }

        /*if (id_comune != 1) {
            console.log('Comune diverso da 1 (Genova), disabilito civico e via');
            viaSelect.disabled = true;
            civicoSelect.disabled = true;
        }*/

        // popolo il select dei civici in funzione della via selezionata
        fetch('backoffice/get_civici.php?id_via=' + id_via)
            .then(response => response.json())
            .then(data => {
                let options = '<option value="">Seleziona il civico</option>';
    
                data.forEach(function(civ) {
                    options += `
                        <option value="${civ.testo}">
                            ${civ.testo}
                        </option>
                    `;
                });

                $('#civ_list').selectpicker('destroy');
                $('#civ_list').html(options);
                $('#civ_list').prop('disabled', false);
                $('#civ_list').selectpicker();
            })

            .catch(error => {
                viaSelect.innerHTML =
                    '<option value="">Errore caricamento</option>';
                console.error(error);

            });

    });

    civicoSelect.addEventListener('change', function () {
        /* ATTENZIONE!! questa funzione viene triggerata dall'onchange del select dei civici, 
        quindi al momento solo per Genova perchè per il genoveato non sono disponibili i civici.*/
        
        let id_civico = this.value;
        console.log('Civico selezionato: ' + id_civico);
        let id_via = viaSelect.value;
        console.log('Via selezionata: ' + id_via);
        if(id_civico === '') {
            return;
        }
        
        // verifico se esiste già una piazzola associata a quel civico
        fetch('backoffice/get_piazzole_pap.php?id_via=' + id_via + '&civico=' + id_civico)
            .then(response => response.json())
            .then(data => {
                bottone_crea =  `<div class="text-center">
                        <button id="bottone_crea" type="submit" class="btn btn-success" disabled>
                            <i class="bi bi-house-add-fill"></i> Crea piazzola PAP
                        </button>
                    </div>`;
                bottone_aggiungi = `<div class="text-center">
                        <button id="bottone_aggiungi" type="submit" class="btn btn-warning" disabled>
                            <i class="bi bi-plus-square-fill"></i> Aggiungi elemento/i a piazzola
                        </button>
                    </div>`;
                // se non c'è alcuna piazzola associata a quel civico, mostrare solo il tasto "Crea piazzola PAP"
                if (data == null){
                    showToast('Non esiste alcuna piazzola associata a questo civico', 'success', 'top');
                    
                    $('#dettagli_piazzola_content').html(bottone_crea);
                    $('#bottone_crea').prop("disabled",false);
                    // verifico se esiste già una classe border-* e in caso la rimuovo per evitare conflitti di stile
                    if (/\bborder\S*/.test($('#card_piazzola')[0].className)) {
                        $('#card_piazzola')[0].className = $('#card_piazzola')[0].className.replace(/\bborder\S*\s?/g, '');                    
                    }
                    $('#card_piazzola').addClass('border-success');
                    $('#card_piazzola').css({"border-width": "medium"});
                    document.getElementById('dettagli_piazzola').style.display = "flex";
                    if ($('#card_piazzola').is(':hidden')) {
                        $('#card_piazzola').show();
                    }
                } else {
                    // se invece trova una piazzola associata a quel civico, mostrare i dettagli della piazzola e i tasti "Crea piazzola PAP" "Aggiungi elemento/i a piazzola PAP"
                    showToast('Esiste già una piazzola associata a questo civico', 'warning', 'top');
                    
                    let pap = [];
                    let dettagli_piazzola = '<div class="col-md-4">';
                    data.forEach(function(det) {
                        if (det.is_pap === 't') {
                            pap.push(1);
                            dettagli_piazzola += `
                            <i class="bi bi-p-circle-fill"></i></i> Piazzola PAP associata a questo civico:`;
                        }else{
                            pap.push(0);
                            dettagli_piazzola += `
                            <i class="bi bi-circle"></i> Piazzola NON PAP associata a questo civico:`;
                        }
                        dettagli_piazzola += `
                            <ul>
                                <li>id piazzola: ${det.id_piazzola}</li>
                                <li>riferimento: ${det.riferimento}</li>
                                <li>via: ${det.via}</li>
                                <li>numero civico: ${det.civico_testo}</li>
                                <li>elementi: ${det.elementi}</li>
                            </ul>
                        `;
                    });
                    dettagli_piazzola += `</div>`;
                    // se c'è più di una piazzola associata a quel civico, mostrare una select per scegliere a quale piazzola aggiungere gli elementi, 
                    // altrimenti nascondere la select e passa direttamente l'id della piazzola esistente al form con un campo hidden
                    
                    if (data.length > 1) {
                        //qua aggiungo una select con cui scelgono la piazzola a cui aggiungere gli elementi
                        //let piazzola_esistente = data[0].id_piazzola;
                        //$('#piazzola_esistente').val(piazzola_esistente);
                        dettagli_piazzola += `<div class="form-group col-md-4">
                            <label for="piazzola_esistente">Scegli la piazzola:</label>
                            <select name="piazzola_esistente" id="piazzola_esistente" class="form-control" onchange="abilitaBottoni(this)" required>
                            <option value="">Seleziona una piazzola</option>
                        `;
                        data.forEach(function(det) {
                            dettagli_piazzola += `
                                <option value="${det.id_piazzola}" data-pap="${det.is_pap}">${det.id_piazzola} - ${det.riferimento}</option>
                            `;
                        });
                        dettagli_piazzola += `</select></div>`;
                    }else{
                        //se c'è una sola piazzola associata a quel civico aggiungo un campo hidden
                        dettagli_piazzola += `<div class="form-group col-md-4">
                            <input type="hidden" id="piazzola_esistente" name="piazzola_esistente" value="${data[0].id_piazzola}">
                        </div>`;
                    }
                    
                    // aggiungo i bottoni in ogni caso
                    dettagli_piazzola += `<div class="col-md-2 text-center">
                            ${bottone_crea}
                        </div>
                        <div class="col-md-2 text-center">
                            ${bottone_aggiungi}
                        </div>`;
                    
                    
                    $('#dettagli_piazzola_content').html(dettagli_piazzola);
                    if (/\bborder\S*/.test($('#card_piazzola')[0].className)) {
                        $('#card_piazzola')[0].className = $('#card_piazzola')[0].className.replace(/\bborder\S*\s?/g, '');                    
                    }
                    $('#card_piazzola').addClass('border-warning');
                    $('#card_piazzola').css({"border-width": "medium"});
                    document.getElementById('dettagli_piazzola').style.display = "flex";
                    if ($('#card_piazzola').is(':hidden')) {
                        $('#card_piazzola').show();
                    }

                    // verifico quali bottoni abilitare in base al fatto che le piazzole trovate siano PAP o non PAP
                    if(data.length === 1){
                        if (pap.every(p => p === 0)) {
                            console.log("solo piazzole NON PAP");
                            $('#bottone_crea').prop("disabled",false);
                            $('#bottone_aggiungi').prop("disabled",false);
                        } 
                        else if (pap.every(p => p === 1)) {
                            console.log("solo piazzole PAP");
                            $('#bottone_crea').prop("disabled",true);
                            $('#bottone_aggiungi').prop("disabled",false);
                        }
                    }
                }
            })

            .catch(error => {
                civicoSelect.innerHTML =
                    '<option value="">Errore caricamento</option>';

                console.error(error);
            });
    });


</script>

<script>
    // submit del form
    $(document).ready(function () {                 
        $('#piazzolapap').submit(function (event) { 
            event.preventDefault();
            
            //verifica campi obbligatori (gestito con js perchè per come strutturato l'html in alcuni casi il required non funziona)
            let lista_elementi = $("#lista_elem").val().trim();
            if (lista_elementi === "") {
                showToast("Devi aggiungere almeno un tipo elemento", "error", "req1");
                return;
            }

            let lista_cliente = $("#tcliente").val();
            console.log(lista_cliente);
            if (lista_cliente === "") {
                showToast("Devi selezionare un tipo cliente", "error", "req2");
                return;
            }

            let nome_cliente = $("#desc").val().trim();
            if (nome_cliente === "") {
                showToast("Devi inserire un nominativo attività", "error", "req2");
                return;
            }

            // in base al bottone cliccato decido quale url chiamare per il submit del form (creazione nuova piazzola o aggiunta elementi a piazzola esistente)
            let ajax_url = '';
            var buttonId = event.originalEvent.submitter.id;                 
            console.log('Bottone ' + buttonId + ' form piazzola PAP cliccato e finito qua');
            if (buttonId === 'bottone_crea') {
                ajax_url = 'backoffice/add_piazzola_pap.php';
            }else if (buttonId === 'bottone_aggiungi') {
                console.log('Bottone aggiungi elemento PAP cliccato');
                ajax_url = 'backoffice/add_elemento_piazzola_pap.php';
            }
            var formData = $(this).serialize();
            console.log(formData);
            $.ajax({ 
                url: ajax_url, 
                method: 'POST', 
                data: formData,
                success: function (response) {                       
                    console.log(response);
                    showToast(response, 'success', 'end');
                    $('#civ_list').selectpicker('val', '');
                    //$('#civ_list').selectpicker('refresh');
                    $('#tcliente').selectpicker('val', '');
                    //$('#tcliente').selectpicker('refresh');
                    $('#desc').val('');
                    $('#suolo_privato').val('false');
                }, 
                error: function (jqXHR, textStatus, errorThrown) {                        
                    showToast(response, 'error', 'end');
                    console.error(errorThrown); 
                } 
            });
        }); 
    }); 
</script>

<script>
    function abilitaBottoni(val) {
        piazzola_sel = $("#piazzola_esistente option:selected").val();
        is_pap = val.options[val.selectedIndex].getAttribute('data-pap');
        if(piazzola_sel === ''){
            console.log('non è stata selezionata alcuna piazzola, disabilito entrambi i bottoni');
            $('#bottone_crea').prop("disabled",true);
            $('#bottone_aggiungi').prop("disabled", true);

        }else{
            if (is_pap === 't') {
                console.log('piazzola selezionata è PAP, disabilito bottone crea e abilito bottone aggiungi');
                $('#bottone_crea').prop("disabled",true);
                $('#bottone_aggiungi').prop("disabled",false);
            }else{
                console.log('piazzola selezionata è NON PAP, abilito entrambi i bottoni');
                $('#bottone_crea').prop("disabled",false);
                $('#bottone_aggiungi').prop("disabled", false);
            }
        }
    }
</script>

<script>
  function showToast(message, type = 'success', pos = 'top') {
    // mostro tosast in posizioni diverse in funzione del parametro passato
    let toast;
    if (pos == 'top'){
        console.log('posizione toast: top');
        toast = document.getElementById('toast');
    }else if(pos == 'req1'){
        console.log('posizione toast: req1');
        toast = document.getElementById('toast_req1');
    }else if(pos == 'req2'){
        console.log('posizione toast: req2');
        toast = document.getElementById('toast_req2');
    }else{
        console.log('posizione toast: end');
        toast = document.getElementById('toast_end');
    }

    console.log('toast: '+toast.id);
    toast.textContent = message;
    toast.className = 'toast show ' + type;

    setTimeout(() => {
        toast.className = 'toast';
        // se la card è in stato di errore dopo 3 secondi la nascondo insieme al toast
        if ($('#card_piazzola').hasClass('border-danger')) {
            $('#card_piazzola').hide();
        }
    }, 3000);
  }
</script>


</body>

</html>