<?php
//session_set_cookie_params($lifetime);
require_once './session.php';

$pageTitle = 'Titolo della pagina';
$pageDescription = 'Descrizione della pagina per i meta tag';

require_once './includes/header.php';

$name = dirname(__FILE__);

if ((int)$id_role_SIT === 0) {
    ///echo'Non ho i permessi..';
    redirect('no_permessi.php');
    exit;
}
# da togliere / commentARE quando si vuole andare in produzione 
require_once './redirect2wip.php';
?>

<!-- FLUID è più esteso su tutto lo schermo -->
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
                <select id="cmbComune" class="form-select select2-clear-no-open">
                    <option value="">Seleziona comune </option>
                </select>
            </div>
            <div class="mb-3">
                <select id="cmbMunicipio" class="form-select select2-clear-no-open" disabled>
                    <option value="">Seleziona municipio </option>
                </select>
            </div>
            <div class="mb-3">
                <select id="cmbQuartiere" class="form-select select2-clear-no-open" disabled>
                    <option value="">Seleziona quartiere </option>
                </select>
            </div>
            <div class="mb-3">
                <select id="cmbVia" class="form-select select2-clear-no-open" disabled>
                    <option value="">Seleziona via </option>
                </select>
            </div>
            
            <hr>
            <div class="mb-3">
                <select id="cmbUT" class="form-select select2-clear-no-open">
                    <option value="">Seleziona UT </option>
                </select>
            </div>
            <hr>
            <!-- Piazzola -->
            <div class="mb-3">
                <select id="cmbPiazzola" class="form-select select2-clear-no-open">
                    <option value="">Seleziona piazzola </option>
                </select>
            </div>

            

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
                    <!--button type="button" class="btn btn-sm" data-bs-container="body" 
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

                    </a-->
                </div>

                

                <!-- LEGENDA --> 
                 <a class="text-decoration-none"
                    data-bs-toggle="collapse"
                    href="#legendaPiazzole"
                    role="button">

                        <i class="fa-solid fa-location-dot"></i>
                        Legenda

                </a>
                <div class="collapse mt-2 ms-3"
                    id="legendaPiazzole">
                    
                    
                <div class="legend-item">

                    <div class="piazzola">
                        <div class="piazzola-marker"
                            style="--marker-bg:#c200c988; --marker-bd:#c200c9;">
                        </div>
                    </div>

                    <span>Piazzola</span>

                </div>


                <div class="legend-item">

                    <div class="piazzola">
                        <div class="piazzola-marker"
                            style="--marker-bg:#c200c988; --marker-bd:#c200c9;">
                            
                            <i class="fa-solid fa-user"></i>

                        </div>
                    </div>

                    <span>Piazzola con cliente</span>

                </div>


                <div class="legend-item">

                    <div class="piazzola">
                        <div class="piazzola-marker"
                            style="--marker-bg:#c200c988; --marker-bd:#c200c9;">

                            <i class="fa-solid fa-recycle"></i>

                        </div>
                    </div>

                    <span>Ecopunto</span>

                </div>


                <div class="legend-item">

                    <!--div class="piazzola">
                        <div class="piazzola-marker"
                            style="--marker-bg:#c200c988; --marker-bd:#c200c9;">

                            
                            <i class="fa-solid fa-user-lock"></i>

                        </div>
                    </div-->

                    <small title="Si ricorda che indicare se la piazzola è su suolo privato ha impatto sul pagamento della COSAP 
                    (Canone per l'Occupazione di Spazi ed Aree Pubbliche)"><i class="fa-solid fa-lock"></i> - La presenza del lucchetto in basso a destra indica che la piazzola 
                    è su <i>suolo privato</i>. 
                    </small>

                </div>
                </div>
                


                <!--    FILTRO RIFIUTI -->
                <a class="text-decoration-none"
                data-bs-toggle="collapse"
                href="#filtriPiazzole"
                role="button">

                    <i class="fa-solid fa-filter"></i>
                    Filtri rifiuti

                </a>
                <div class="collapse mt-2 ms-3"
                    id="filtriPiazzole">
                
                
                <div class="d-flex justify-content-between mb-2">

                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="btnSelezionaRifiuti">

                    Seleziona tutti

                </button>


                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="btnDeselezionaRifiuti">

                    Deseleziona tutti

                </button>

            </div>



            <div id="filtriRifiuti"></div>



            <script type="text/javascript">
                $("#btnSelezionaRifiuti").on("click", function(){
                    
                    filtroRifiutiAttivo = true;

                    $(".filtro-rifiuto").each(function(){

                        this.checked = true;
                        filtriRifiutiAttivi.add(this.value);

                    });

                    visualizzaPiazzole(map);

                });


                $("#btnDeselezionaRifiuti").on("click", function(){


                    filtroRifiutiAttivo = true;
                    
                    $(".filtro-rifiuto").each(function(){

                        this.checked = false;
                        filtriRifiutiAttivi.delete(this.value);

                    });

                    visualizzaPiazzole(map);

                });
                
            </script> 
            </div>

            
                
            <hr>
            </div>



            <!-- qua ci andranno altri layer -->







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


<!-- SEZIONE MODAL 
 
vengono poi popolati lato JS

-->



<!-- modal piazzola -->
<div class="modal fade" id="modalFotoPiazzola" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Foto piazzola
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body text-center"
                 id="contenutoFotoPiazzola">
            
            </div>

        </div>

    </div>

</div>





<!-- Modal Percorsi-->
<div class="modal fade" id="modalPercorsi" tabindex="-1" aria-labelledby="modalPercorsiLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="modalPercorsiLabel">
                    <i class="fa-solid fa-route me-2"></i>
                    Percorsi
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Chiudi">
                </button>

            </div>

            <div class="modal-body" id="modalPercorsiBody">

                <!-- Contenuto inserito dinamicamente -->

                <div class="text-center text-muted py-4">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i>
                    Caricamento...
                </div>

            </div>

        </div>

    </div>

</div>




<?php require_once './includes/footer.php'; ?>


<script type="text/javascript" src="./mappa_base.js"></script>

<script>
    const mappaPrincipale = creaMappa("mappa_principale");

    map = mappaPrincipale.map;
    initialView = mappaPrincipale.initialView;
    
    setTimeout(() => {
        map.invalidateSize();
    }, 100);
</script>



<!-- condiviso per piazzole, aste e altri elementi -->
<script type="text/javascript" src="./layers/pannello_risultati.js"></script>



<script type="text/javascript" src="./layers/layer_aste.js"></script>
<link rel="stylesheet" href="./layers/layer_piazzole.css">
<script type="text/javascript" src="./layers/layer_piazzole.js"></script>

<script type="text/javascript" src="./layers/sposta_mappa.js"></script>


<script type="text/javascript" src="./layers/modal_percorsi_piazzola_elemento.js"></script>



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




fetch('ajax/ut.php')
    .then(r => r.json())
    .then(data => {

        const cmb = document.getElementById('cmbUT');

        data.forEach(c => {

            const opt = document.createElement('option');

            opt.value = c.id;
            opt.textContent = c.descrizione;

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



$('#cmbUT').select2({
    theme: 'bootstrap-5',
    placeholder: 'Cerca UT...',
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

// devo poi assegnare la classe select2-clear-no-open alle quattro <select> <select>
// in modo che all'eliminazione della selezione non si apra la select2

$('.select2-clear-no-open')
    .on('select2:unselecting', function () {
        $(this).data('unselecting', true);
    })
    .on('select2:opening', function (e) {

        if ($(this).data('unselecting')) {
            $(this).removeData('unselecting');
            e.preventDefault();
        }

    });


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
    console.log('aggiornaVie')
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


async function zoomSuEntita(urlBbox) {

    // Invalida la cache dei layer
    cacheLayer.aste.bounds = null;
    cacheLayer.piazzole.bounds = null;

    try {

        const response = await fetch(urlBbox);

        if (!response.ok) {
            throw new Error(`Errore HTTP ${response.status}`);
        }

        const b = await response.json();

        if (!b || b.length === 0) {
            return;
        }

        map.fitBounds([
            [parseFloat(b[0].ymin), parseFloat(b[0].xmin)],
            [parseFloat(b[0].ymax), parseFloat(b[0].xmax)]
        ]);

    }
    catch (err) {
        console.error("Errore durante lo zoom:", err);
    }

}




// zoom bbox e aggiorno cmbobox municipio, quartiere e via 
$('#cmbComune').on('change', async function () {

   
    const idComune = $(this).val();

    console.log('Cambiato comune', idComune);


    

    if (!idComune){
        resetSelect("#cmbMunicipio", "Seleziona municipio");
        resetSelect("#cmbQuartiere", "Seleziona quartiere");
        resetSelect("#cmbVia", "Seleziona via");
        resetSelect("#cmbVia", "Seleziona via", false);
        cacheLayer.aste.bounds = null;
        cacheLayer.piazzole.bounds = null;

        return;
        // dovrei abilitare disabled=false CmbVie
    }
    


    // aggiorno cmbobox municipio, quartiere e via
    await aggiornaMunicipi();
    await aggiornaQuartieri();
    await aggiornaVie();

    // Zoom sul comune
    await zoomSuEntita(`ajax/comune_bbox.php?id=${idComune}`);


});


// zoom bbox e aggiorno cmbobox municipio, quartiere e via 
$('#cmbUT').on('change', async function () {

    const idUT = $(this).val();

    console.log('Cambiata UT', idUT);
    

    if (!idUT){
        resetSelect("#cmbComune", "Seleziona comune", false);
        resetSelect("#cmbMunicipio", "Seleziona municipio");
        resetSelect("#cmbQuartiere", "Seleziona quartiere");
        resetSelect("#cmbVia", "Seleziona via");

        cacheLayer.aste.bounds = null;
        cacheLayer.piazzole.bounds = null;

        return;
        // dovrei abilitare disabled=false CmbVie
    }
    

    // Zoom sul comune
    await zoomSuEntita(`ajax/ut_bbox.php?id=${idUT}`);


});

$('#cmbMunicipio').on('change', async function () {

   

    const idMunicipio = $(this).val();

    if (!idMunicipio) {
        await aggiornaQuartieri();
        await aggiornaVie();
        return;
    }

    
    // aggiorno cmbobox quartiere e via
    await aggiornaQuartieri();
    await aggiornaVie();
    

    // zoom sul municipio
    await zoomSuEntita(`ajax/municipio_bbox.php?id=${idMunicipio}`);


});



$('#cmbQuartiere').on('change', async function () {

   
    const idQuartiere = $(this).val();

    if (!idQuartiere) {
        await aggiornaVie();
        return;
    }

    // aggiorno cmbobox via
    await aggiornaVie();


    // zoom sul quartiere
    await zoomSuEntita(`ajax/quartiere_bbox.php?id=${idQuartiere}`);


});



$('#cmbVia').on('change', function () {

    const idVia = $(this).val();

    console.log('Cambiata via', idVia);


   
    if (!idVia){
        return;
    }
    
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




$('#cmbPiazzola').select2({

    placeholder: "Cerca piazzola...",
    allowClear: true,
    minimumInputLength: 3,

    ajax: {

        url: 'ajax/piazzole.php',
        dataType: 'json',
        delay: 300,
        cache: true,

        data: function (params) {

            const dati = {
                q: params.term
            };

            if (filtraLayer) {

                dati.id_comune = $("#cmbComune").val();
                dati.id_municipio = $("#cmbMunicipio").val();
                dati.id_quartiere = $("#cmbQuartiere").val();
                dati.id_via = $("#cmbVia").val();

            }

            return dati;

        },

        processResults: function (data) {

            return {
                results: data.map(p => ({
                    id: p.id,
                    text: p.descrizione
                }))
            };

        }

    }

});


$('#cmbPiazzola').on('select2:select', async function (e) {

    const idPiazzola = e.params.data.id;

    console.log('La piazzola selezionata è:', idPiazzola);

    piazzolaSelezionata = idPiazzola;


    let p = await fetch(`ajax/piazzola_geo.php?id=${idPiazzola}`)
        .then(r => r.json());

    console.log('Dati piazzola:', p);
    console.log('Lat:', p[0].lat, 'Lon:', p[0].lon);
    map.setView(
        [parseFloat(p[0].lat), parseFloat(p[0].lon)],
        18,              // livello di zoom
        {
            animate: true,
            duration: 0.5
        }
    );

    //evidenziaPiazzolaById(idPiazzola);
    apriPannelloRisultati();
    await caricaDettaglioPiazzola(idPiazzola) 

});



// chkFiltraLayer

let filtraLayer = false;


$('#chkFiltraLayer').on('change', function(){

    filtraLayer = this.checked;

    $('#badgeFiltroLayer')
        .toggleClass('d-none', !filtraLayer);

    aggiornaAste(map);

    aggiornaPiazzole(map);

});
</script>

</body>

</html>