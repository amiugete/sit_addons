<?php
//session_set_cookie_params($lifetime);
require_once './session.php';
require_once './conn_ok.php';
require_once './redirect2wip.php';


    
?>
<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Gestione servizi</title>
<?php 

require_once('./req.php');

the_page_title();

require_once './conn_ok.php';
?> 


<style>
.d-flex.h-100 {
    height: 100vh;
    width: 100%;
    overflow: hidden;
}


#mapContainer {
    flex: 1 1 auto;
    min-width: 0;   /* 🔥 FONDAMENTALE */
    position: relative;
    display: flex;
    flex-direction: column;
}

/* MAPPA sotto */
#mappa_principale {
    top: 35px;
    width: 100%;
    height: 95%;
}

/* UI SOPRA la mappa (ma separata) */
#mapUI {
    position: absolute;
    inset: 0;
    z-index: 1000;
    pointer-events: none; /* IMPORTANTISSIMO */
}

/* bottoni cliccabili */
.map-btn {
    position: absolute;
    top: 0px;
    background: white;
    border: 1px solid #ccc;
    padding: 6px 10px;
    border-radius: 4px;

    pointer-events: auto; /* riattiva click */
}





/* posizioni */
#btnFiltri {
    left: 10px;
}

#btnRisultati {
    right: 10px;
}

/* SIDEBAR NON deve usare transform per questo caso */
.sidebar {
    width: 320px;
    flex: 0 0 320px;
    transition: all 0.3s ease;
    background: #fff;
    overflow-y: auto ; /* dovrebbe fare la scrollbar quando necessario*/
}


.sidebar-content {
    padding: 10px;
}




/* chiusura vera tipo Lizmap */
.sidebar.chiuso {
    flex: 0 0 0;
    width: 0;
    min-width: 0;
    overflow: hidden;
}



/*#panelFiltri.chiuso {
    width: 0;
    overflow: hidden;
}

#panelRisultati.chiuso {
    width: 0;
    overflow: hidden;
}*/

.ts-control input {
    width: 100% !important;
}


/*select2 con stesso aspetto delle card e dei pulsanti Bootstrap:*/
.select2-container--bootstrap-5 .select2-selection {
    border-radius: var(--bs-border-radius) !important;
}


.select2-container--bootstrap-5.select2-container--disabled .select2-selection {
    background-color: var(--bs-secondary-bg);
    color: var(--bs-secondary-color);
    opacity: .65;
    cursor: not-allowed;
}

.select2-container--bootstrap-5.select2-container--disabled .select2-selection__arrow b {
    opacity: .5;
}

/* rimuovere scelta */
/* il contenitore della select */
.select2-selection--single{
    position: relative;
}

/* bottone */
.select2-selection__clear{

    position:absolute !important;
    right:26px;
    top:50%;
    transform:translateY(-50%);

    width:20px;
    height:20px;

    margin:0 !important;
    padding:0 !important;

    border:none;
    background:transparent;

    display:flex;
    align-items:center;
    justify-content:center;
}

/* nasconde la X originale */
.select2-selection__clear span{
    display:none;
}

/* FontAwesome */
.select2-selection__clear::before{

    font-family:"Font Awesome 6 Free";
    font-weight:900;
    content:"\f057";     /* circle-xmark */

    color:var(--bs-danger);
    font-size:1rem;
}
</style>




</head>

<body>

<?php 
require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ((int)$id_role_SIT = 0) {
  redirect('no_permessi.php');
  //exit;
}



?>



<div class="container-fluid" id="mainContainer">


<!-- MODAL DETTAGLI PIAZZOLA -->
<div class="modal fade" id="modalDettaglioPiazzola" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-scrollable modal-piazzola" >
      <div class="modal-content">
        <div class="modal-header">
          <!--h5 class="modal-title" id="exampleModalLabel">Titolo</h5-->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <div class="modal-body" id="body_dettaglio_piazzola">
                <!-- output data here-->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


    <div class="d-flex h-100">

        <!-- FILTRI -->
        <div id="panelFiltri" class="sidebar">
          <div class="sidebar-content"> 

            <div class="card mb-3">

            <div class="card-header d-flex justify-content-between align-items-center">

                <span>
                    <i class="fa-solid fa-filter me-2"></i>
                    Filtri
                    <span
                    id="badgeFiltroLayer"
                    class="badge bg-success ms-2 d-none">

                    Layer filtrati

                    </span>
                </span>

                <div class="form-check form-switch m-0">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="chkFiltraLayer">

                    <label class="form-check-label small ms-1"
                        for="chkFiltraLayer">
                        Filtra piazzole
                    </label>

                </div>

            </div>

            <div class="card-body">

        


            <!-- COMUNE -->
            <div class="mb-3">
                <select id="cmbComune" class="form-select">
                    <option value="">Seleziona comune </option>
                </select>
            </div>
            <div class="mb-3">
                <select id="cmbMunicipio" class="form-select" disabled>
                    <option value="">Seleziona municipio </option>
                </select>
            </div>
            <div class="mb-3">
                <select id="cmbQuartiere" class="form-select" disabled>
                    <option value="">Seleziona quartiere </option>
                </select>
            </div>
            <div class="mb-3">
                <select id="cmbVia" class="form-select" disabled>
                    <option value="">Seleziona via </option>
                </select>
            </div>
            
            <!-- Comune -->

        <!-- Via -->

            </div>

        </div>
            
        <div class="card">

            <div class="card-header">

                <i class="fa-solid fa-layer-group me-2"></i>

                Layer

            </div>

            <div class="card-body">

       



                <div class="form-check">
                    <input class="form-check-input"
                        type="checkbox"
                        id="chkAste">

                    <label class="form-check-label"
                        for="chkAste" title="Mostra le aste solo a determinati livelli di zoom">

                        Grafo stradale

                    </label>
                    <button type="button" class="btn btn-sm" data-bs-container="body" 
                    data-bs-toggle="popover" data-bs-placement="right" id="btnInfoAste" data-bs-html="true" >
                    <i class="fa-regular fa-circle-question"></i>
                    </button>
                </div>
                <hr>
                <div class="form-check">
                    <input class="form-check-input"
                        type="checkbox"
                        id="chkPiazzole" checked>

                    <label class="form-check-label"
                        for="chkPiazzole" >

                       Piazzole

                    </label>
                    <button type="button" class="btn btn-sm" data-bs-container="body" 
                    data-bs-toggle="popover" data-bs-placement="right" id="btnInfoPiazzole" data-bs-html="true" >
                    <i class="fa-regular fa-circle-question"></i>
                    </button>

                    <a class="ms-2 text-decoration-none"
                    data-bs-toggle="collapse"
                    href="#legendaPiazzole"
                    role="button"
                    aria-expanded="false"
                    aria-controls="legendaPiazzole">

                        <i class="fa-solid fa-circle-info"></i>

                    </a>
                </div>

                <div class="collapse mt-2 ms-4" id="legendaPiazzole">

                    <div class="legend-item">

                        <div class="piazzola">
                            <div class="piazzola-marker"></div>
                        </div>

                        <span>Piazzola</span>

                    </div>

                    <div class="legend-item">

                        <div class="piazzola pap">
                            <div class="piazzola-marker">P</div>
                        </div>

                        <span>PAP</span>

                    </div>

                    <div class="legend-item">

                        <div class="piazzola ecopunto">
                            <div class="piazzola-marker">€</div>
                        </div>

                        <span>Ecopunto</span>

                    </div>

                    <div class="legend-item">

                        <div class="piazzola privato">
                            <div class="piazzola-marker"></div>
                        </div>

                        <span>Suolo privato</span>

                    </div>
                <hr>
                <div id="filtriRifiuti"></div>
                </div>
                
                <hr>

                    </div>

             </div>
        </div>
    </div>

    <!-- MAPPA -->
    <div id="mapContainer" style="position: relative; width: 100%; height: 100%; flex: 1;">

            
           <!--div id="map"></div-->
           <div id="mappa_principale"></div>

          <!-- BOTTONI OVERLAY -->
          <div id="mapUI">
            <button class="btn map-btn btn-sm" id="btnFiltri">
                 <i class="bi bi-chevron-left"></i>
            </button>

            <button class="btn map-btn btn-sm" id="btnRisultati">
                 <i class="bi bi-chevron-left"></i>
            </button>
          </div>
          



        </div>

        <!-- RISULTATI -->
       <div id="panelRisultati" class="sidebar chiuso">

            <div class="sidebar-content">

                <div class="alert alert-info" role="alert" id="nota_to_remove">
                    E' necessario selezionare qualcosa sulla mappa per vedere dei risultati in questo pannello.
                </div>

                <div id="cardPiazzola" class="card d-none">

                    <div class="card-header" id="cardPiazzolaHeader">
                        Piazzola
                    </div>

                    <div class="card-body" id="cardPiazzolaBody">

                    </div>

                </div>


                <div id="cardAsta" class="card mb-3 d-none">

                    <div class="card-header d-flex justify-content-between align-items-center">

                    <span id="cardAstaHeader">
                        Asta
                    </span>

                    <button
                        id="btnToggleAsta"
                        type="button"
                        class="btn btn-link btn-sm text-secondary p-0"
                        title="Mostra/Nascondi il grafo">

                        <i id="icoToggleAsta" class="fa-solid fa-eye-slash"></i>

                    </button>

                </div>

                    <div class="card-body" id="cardAstaBody">

                    </div>

            
                </div>

            </div>

    </div>

    </div>

</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>


<script type="text/javascript" src="./mappa_base.js"></script>

<script>
    const mappa = creaMappa("mappa_principale");

    map = mappa.map;
    initialView = mappa.initialView;
</script>



<!-- condiviso per piazzole, aste e altri elementi -->
<script type="text/javascript" src="./layers/pannello_risultati.js"></script>



<script type="text/javascript" src="./layers/layer_aste.js"></script>
<link rel="stylesheet" href="./layers/layer_piazzole.css">
<script type="text/javascript" src="./layers/layer_piazzole.js"></script>

<script type="text/javascript" src="./layers/sposta_mappa.js"></script>


<script type="text/javascript" >

/*
ogni volta che apri/chiudi un pannello devi fare: map.invalidateSize(); 
altrimenti ti ritrovi con:

tiles bianche
marker fuori posizione
centro mappa errato
*/

const filtri = document.getElementById('panelFiltri');
const risultati = document.getElementById('panelRisultati');


function aggiornaMappa() {
    setTimeout(() => {
        if (window.map) map.invalidateSize();
    }, 300);
}

function apriPannelloFiltri() {

    filtri.classList.remove("chiuso");

    $("#btnFiltri i")
        .removeClass("bi-chevron-right")
        .addClass("bi-chevron-left");

    $("#btnFiltri").attr("title", "Nascondi filtri");

    aggiornaMappa();

}

function chiudiPannelloFiltri() {

    filtri.classList.add("chiuso");

    $("#btnFiltri i")
        .removeClass("bi-chevron-left")
        .addClass("bi-chevron-right");

    $("#btnFiltri").attr("title", "Mostra filtri");

    aggiornaMappa();

}


function apriPannelloRisultati(){

    risultati.classList.remove("chiuso");

     $("#btnRisultati i")
        .removeClass("bi-chevron-left")
        .addClass("bi-chevron-right");

    $("#btnRisultati").attr("title", "Nascondi risultati");


    aggiornaMappa();


}

function chiudiPannelloRisultati(){

    risultati.classList.add("chiuso");

     $("#btnRisultati i")
        .removeClass("bi-chevron-right")
        .addClass("bi-chevron-left");

    $("#btnRisultati").attr("title", "Nascondi risultati");

    
    aggiornaMappa();


}


function nascondiNotaRisultati() {
    console.log('Elimino nota');
    const nota = document.getElementById("nota_to_remove");

    if (nota) {
        nota.remove();           // la elimina definitivamente dal DOM
    }

}


document.getElementById('btnFiltri').addEventListener('click', () => {
    if (filtri.classList.contains('chiuso')) {
        apriPannelloFiltri();
    } else {
        chiudiPannelloFiltri();
    }
});

document.getElementById('btnRisultati').addEventListener('click', () => {
    if (risultati.classList.contains('chiuso')) {
        console.log('Apro P. ris');
        apriPannelloRisultati();
    } else {
        console.log('Chiudo P. ris');
        chiudiPannelloRisultati();
    }
});
</script>


<script type="text/javascript" >
// popolamento comuni 

fetch('ajax/comuni.php')
    .then(r => r.json())
    .then(data => {

        const cmb = document.getElementById('cmbComune');

        data.forEach(c => {

            const opt = document.createElement('option');

            opt.value = c.id_comune;
            opt.textContent = c.descr_comune;

            cmb.appendChild(opt);
        });

    });






    // TOM SELECT
/*const comuneSelect = new TomSelect("#cmbComune", {
    create: false,
    maxOptions: 40,
    searchField: ['text'],
    placeholder: "Cerca comune..."
});*/



// inizializzo le select2


$('#cmbComune').select2({
    theme: 'bootstrap-5',
    placeholder: 'Cerca comune...',
    width: '100%',
    allowClear: true
});


$('#cmbMunicipio').select2({
    theme: 'bootstrap-5',
    placeholder: 'Cerca municipio...',
    width: '100%',
    allowClear: true
});


$('#cmbQuartiere').select2({
    theme: 'bootstrap-5',
    placeholder: 'Cerca quartiere...',
    width: '100%',
    allowClear: true
});


$('#cmbVia').select2({
    theme: 'bootstrap-5',
    placeholder: 'Cerca via...',
    width: '100%',
    allowClear: true
});

</script>





<script type="text/javascript" >

let idMunicipio = null;
let idQuartiere = null;


function resetSelect(selector, testo, disabilita = true) {

    $(selector)
        .empty()
        .append(`<option value="">${testo}</option>`)
        .prop("disabled", disabilita)
        .trigger("change.select2");

}


async function aggiornaMunicipi() {

    const idComune = $("#cmbComune").val();

    resetSelect("#cmbMunicipio", "Seleziona municipio");
    resetSelect("#cmbQuartiere", "Seleziona quartiere");
    resetSelect("#cmbVia", "Seleziona via");

    // Solo Genova
    if (idComune != 1) {
        return;
    }

    const data = await fetch(`ajax/municipi.php?id_comune=${idComune}`)
        .then(r => r.json());

    const cmb = $("#cmbMunicipio");

    data.forEach(m => {
        cmb.append(
            $("<option>", {
                value: m.id,
                text: m.descrizione
            })
        );
    });

    cmb.prop("disabled", false)
       .trigger("change.select2");

}


async function aggiornaQuartieri() {

    const idComune = $("#cmbComune").val();
    const idMunicipio = $("#cmbMunicipio").val();

    resetSelect("#cmbQuartiere", "Seleziona quartiere");
    resetSelect("#cmbVia", "Seleziona via");

    if (idComune != 1) {
        return;
    }

    let url = `ajax/quartieri.php?id_comune=${idComune}`;

    if (idMunicipio) {
        url += `&id_municipio=${idMunicipio}`;
    }

    const data = await fetch(url).then(r => r.json());

    const cmb = $("#cmbQuartiere");

    data.forEach(q => {
        cmb.append(
            $("<option>", {
                value: q.id,
                text: q.descrizione
            })
        );
    });

    cmb.prop("disabled", false)
       .trigger("change.select2");

}

async function aggiornaVie() {

    const idComune = $("#cmbComune").val();
    const idMunicipio = $("#cmbMunicipio").val();
    const idQuartiere = $("#cmbQuartiere").val();

    resetSelect("#cmbVia", "Seleziona via");

    if (!idComune) {
        return;
    }

    let url = `ajax/vie.php?id_comune=${idComune}`;

    if (idComune == 1) {

        if (idMunicipio) {
            url += `&id_municipio=${idMunicipio}`;
        }

        if (idQuartiere) {
            url += `&id_quartiere=${idQuartiere}`;
        }

    }

    const data = await fetch(url).then(r => r.json());

    const cmb = $("#cmbVia");

    data.forEach(v => {
        cmb.append(
            $("<option>", {
                value: v.id,
                text: v.descrizione
            })
        );
    });

    cmb.prop("disabled", false)
       .trigger("change.select2");

}







// zoom bbox 
$('#cmbComune').on('change', function () {


    cacheLayer.aste.bounds = null;
    cacheLayer.piazzole.bounds = null;

    
    const idComune = $(this).val();

    console.log('Cambiato comune', idComune);


    const cmbVia = $('#cmbVia');

    // Svuota sempre la combo vie
    cmbVia.empty()
          .append('<option value="">Seleziona via</option>')
          .prop('disabled', true);

    if (!idComune){
        return;
        // dovrei abilitare disabled=false CmbVie
    }
    // Abilita la combo
    cmbVia.prop('disabled', false);

    if (idComune == 1 ) {
        console.log('Comune selezionato è Genova, carico municipi e quartieri');
        // carica i municipi
        fetch(`ajax/municipi.php?id_comune=${idComune}`)
            .then(r => r.json())
            .then(data => {

                const cmbMunicipio = $('#cmbMunicipio');

                cmbMunicipio.empty()
                    .append('<option value="">Seleziona municipio</option>');

                data.forEach(m => {
                    cmbMunicipio.append(
                        $('<option>', {
                            value: m.id,
                            text: m.descrizione
                        })
                    );
                });


                // Abilita la combo
                cmbMunicipio.prop('disabled', false);

                // Se usi Select2 aggiorna la grafica
                cmbMunicipio.trigger('change.select2');
            });

        // carica i quartieri
        // se ci fosssero anche i municipi, dovrei filtrare i quartieri per municipio selezionato

        console.log('Carico quartieri per comune', idComune, 'e municipio', idMunicipio);

        let URL_QUARTIERI = `ajax/quartieri.php?id_comune=${idComune}`
        if (idMunicipio) {
            URL_QUARTIERI += `&id_municipio=${idMunicipio}`;
        }

        fetch(URL_QUARTIERI)
            .then(r => r.json())
            .then(data => {
                const cmbQuartiere = $('#cmbQuartiere');

                cmbQuartiere.empty()
                    .append('<option value="">Seleziona quartiere</option>');

                data.forEach(m => {
                    cmbQuartiere.append(
                        $('<option>', {
                            value: m.id,
                            text: m.descrizione
                        })
                    );
                });


                // Abilita la combo
                cmbQuartiere.prop('disabled', false);

                // Se usi Select2 aggiorna la grafica
                cmbQuartiere.trigger('change.select2');
        });
    } else {
        // disabilita la combo dei municipi
        $('#cmbMunicipio').empty()
            .append('<option value="">Seleziona municipio</option>')
            .prop('disabled', true);
        

        // disabilita la combo dei quartieri
        $('#cmbQuartiere').empty()
            .append('<option value="">Seleziona quartiere</option>')
            .prop('disabled', true);    
    }



    // Carica le vie del comune
    let URL_VIE = `ajax/vie.php?id_comune=${idComune}`
    if (idMunicipio) {
        URL_VIE += `&id_municipio=${idMunicipio}`;
    }
    if (idQuartiere) {
        URL_VIE += `&id_quartiere=${idQuartiere}`;
    }
    
    fetch(URL_VIE)
        .then(r => r.json())
        .then(data => {

            data.forEach(v => {
                cmbVia.append(
                    $('<option>', {
                        value: v.id_via,
                        text: v.nome
                    })
                );
            });

            // Se usi Select2 aggiorna la grafica
            cmbVia.trigger('change.select2');
        });


    // Zoom sul comune
    fetch(`ajax/comune_bbox.php?id=${idComune}`)
        .then(r => r.json())
        .then(b => {
            console.log('Faccio zoom su comune!')
            map.fitBounds([
                [parseFloat(b[0].ymin), parseFloat(b[0].xmin)],
                [parseFloat(b[0].ymax), parseFloat(b[0].xmax)]
            ]);

        });

    });







$('#cmbVia').on('change', function () {

    const idVia = $(this).val();

    console.log('Cambiata via', idVia);


   
    if (!idVia)
        return;
        // dovrei abilitare disabled=false CmbVie

        // Abilita la combo
        

        // Zoom sulla via
        fetch(`ajax/via_bbox.php?id=${idVia}`)
            .then(r => r.json())
            .then(b => {
                console.log('Faccio zoom su comune!')
                map.fitBounds([
                  [parseFloat(b[0].ymin), parseFloat(b[0].xmin)],
                  [parseFloat(b[0].ymax), parseFloat(b[0].xmax)]
                ]);

            });

            // evidenziazione aste 
            fetch(`ajax/via_aste.php?id=${idVia}`)
            .then(r => r.json())
            .then(data => {

                const ids = data.map(a => a.id_asta);

                evidenziaAste(ids);

            });



});



$('#cmbMunicipio').on('change', function () {

    idMunicipio = $(this).val();

    console.log('Cambiato municipio', idMunicipio);


   
    if (!idMunicipio){
        return;
    }
    // dovrei abilitare disabled=false CmbVie

    // Abilita la combo
    

    // Zoom sulla via
    fetch(`ajax/municipio_bbox.php?id=${idMunicipio}`)
        .then(r => r.json())
        .then(b => {
            console.log('Faccio zoom su municipio!')
            map.fitBounds([
                [parseFloat(b[0].ymin), parseFloat(b[0].xmin)],
                [parseFloat(b[0].ymax), parseFloat(b[0].xmax)]
            ]);

        });

});



$('#cmbQuartiere').on('change', function () {

    idQuartiere = $(this).val();

    console.log('Cambiato quartiere', idQuartiere);


   
    if (!idQuartiere){
        return;
    }
    // dovrei abilitare disabled=false CmbVie

    // Abilita la combo
    

    // Zoom sulla via
    fetch(`ajax/quartiere_bbox.php?id=${idQuartiere}`)
        .then(r => r.json())
        .then(b => {
            console.log('Faccio zoom su quartiere!')
            map.fitBounds([
                [parseFloat(b[0].ymin), parseFloat(b[0].xmin)],
                [parseFloat(b[0].ymax), parseFloat(b[0].xmax)]
            ]);

        });

});


// chkFiltraLayer

let filtraLayer = false;


$('#chkFiltraLayer').on('change', function(){

    filtraLayer = this.checked;

    $('#badgeFiltroLayer')
        .toggleClass('d-none', !filtraLayer);

    aggiornaAste();

    aggiornaPiazzole();

});
</script>

</body>

</html>