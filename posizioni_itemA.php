<?php
//session_set_cookie_params($lifetime);
require_once './session.php';

    
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





</head>

<body>

<?php require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ((int)$id_role_SIT = 0) {
  redirect('no_permessi.php');
  //exit;
}
?>


<div class="container-fluid">


<div class="row align-items-center">

    <div class="col">

        <div class="info-panel"></div>

        <div id="stats">
            Caricamento...
        </div>

    </div>

    <div class="col d-flex align-items-center justify-content-center">

    <!--input id="searchSportello"
       class="form-control form-control-sm"
       list="listaSportelli"
       placeholder="Cerca sportello...">

    <datalist id="listaSportelli"></datalist-->

    <div class="input-group input-group-sm" style="max-width: 250px;">

    <span class="input-group-text">
        🔎
    </span>

    <input type="text"
       id="searchSportello"
       class="form-control"
       list="listaSportelli"
       placeholder="Cerca sportello...">

    </div>
    <datalist id="listaSportelli"></datalist>


</div>
    <div class="col">

        <div class="form-check form-switch m-0">
            <input class="form-check-input"
                   type="checkbox"
                   id="soloRossi">

            <label class="form-check-label"
                   for="soloRossi">
                Mostra solo mezzi non aggiornati
            </label>
        </div>

    </div>

    <div class="col d-flex justify-content-end">

        <button id="resetMap"
                class="btn btn-sm btn-primary">

            <i class="bi bi-aspect-ratio"></i>
            Torna visualizzazione iniziale

        </button>

    </div>

</div>
<div id="map"></div>
<!--script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script-->





<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
  require_once('req_bottom.php');
  require('./footer.php');
}
?>
</div>


<script type="text/javascript" src="mappa_base.js"></script>
<script>

/*const map = L.map('map');

// lo uso per il tasto torna allo zoom iniziale
const initialView = {
    bounds: null,
    zoomed: false
};*/

// questo richiama la funziona aggiornaTooltip ad ogni chiamata
map.on('zoomend', aggiornaTooltip);


const clusterRossi = L.markerClusterGroup({
    disableClusteringAtZoom: 18,
    iconCreateFunction: function (cluster) {
        return L.divIcon({
            html: `<div style="
                background:red;
                color:white;
                border-radius:50%;
                width:40px;
                height:40px;
                display:flex;
                align-items:center;
                justify-content:center;
                font-weight:bold;">
                ${cluster.getChildCount()}
            </div>`,
            className: 'cluster-red',
            iconSize: L.point(40, 40)
        });
    }
});

const clusterVerdi = L.markerClusterGroup({
    disableClusteringAtZoom: 18,
    iconCreateFunction: function (cluster) {
        return L.divIcon({
            html: `<div style="
                background:green;
                color:white;
                border-radius:50%;
                width:40px;
                height:40px;
                display:flex;
                align-items:center;
                justify-content:center;
                font-weight:bold;">
                ${cluster.getChildCount()}
            </div>`,
            className: 'cluster-green',
            iconSize: L.point(40, 40)
        });
    }
});


map.addLayer(clusterVerdi);
map.addLayer(clusterRossi);


/*L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 22,
    maxNativeZoom: 19
}).addTo(map);*/


let datiMezzi = [];




function aggiornaMappa() {

    clusterVerdi.clearLayers();
    clusterRossi.clearLayers();

    const soloRossi =
        document.getElementById("soloRossi").checked;

    const bounds = [];

    datiMezzi.forEach(m => {

        const isGreen = (m.posizione_ultime_24h === 't');

        if (soloRossi && isGreen) {
            return;
        }

        const colore = isGreen ? 'green' : 'red';

        const marker = L.circleMarker([m.lat, m.lon], {
            radius: 8,
            color: colore,
            fillColor: colore,
            fillOpacity: 0.8,
            weight: 2
        });

        marker.bindTooltip(m.sportello, {
            permanent: false, // per aprirli o chiuderli con la funzione openTolltip() o closeTolltip()
            direction: 'right',
            offset: [10,0],
            className: 'sportello-label'
        });

        // aggiungo sportello come ID
        marker.options.sportello = m.sportello;
        
        marker.bindPopup(`
            <b>Sportello:</b> ${m.sportello}<br>
            <b>Ultimo aggiornamento:</b> ${m.last_update}<br>
            <b>Installazione:</b> ${m.data_installazione}<br>
            <b>Ultime 24h:</b> ${isGreen ? 'SI' : 'NO'}
        `);

        if (isGreen) {
            clusterVerdi.addLayer(marker);
        } else {
            clusterRossi.addLayer(marker);
        }

        bounds.push([m.lat, m.lon]);
    });

    if (bounds.length > 0) {


        const b = L.latLngBounds(bounds);

        map.fitBounds(b, { padding: [30, 30] });

        initialView.bounds = b;
        initialView.zoomed = true;
    }

    aggiornaTooltip();
}
// fine aggiornamappa()



function aggiornaTooltip() {

    const zoom = map.getZoom();
    console.log('Zoom attuale:', zoom);
    [clusterVerdi, clusterRossi].forEach(cluster => {
        cluster.getLayers().forEach(marker => {

            const tooltip = marker.getTooltip();

            if (!tooltip) return;
           
            if (zoom >= 15) {
                console.log('sono qui');
                /*tooltip.getElement()?.style.setProperty(
                    'display',
                    'block'
                );*/
                marker.openTooltip();
            } else {
                console.log('OK zomm < 15')
                marker.closeTooltip();
                /*tooltip.getElement()?.style.setProperty(
                    'display',
                    'none'
                );*/
            }
        });
    });
}
// fine funziona aggiorna tooltip









fetch('tables/posizioni_itemA.php')
    .then(response => response.json())
    .then(data => {

        datiMezzi = data;

        let verdi = 0;
        let rossi = 0;

        data.forEach(m => {
            if (m.posizione_ultime_24h==='t')
                verdi++;
            else
                rossi++;
        });

        document.getElementById('stats').innerHTML = `
    <div class="d-flex align-items-center gap-3 p-2"
         style="font-size: 14px; white-space: nowrap;">

        <div class="d-flex align-items-center gap-1">
            <span>Attivi 24h:</span>
            <span class="badge bg-success">${verdi}</span>
        </div>

        <div class="d-flex align-items-center gap-1">
            <span>Non aggiornati:</span>
            <span class="badge bg-danger">${rossi}</span>
        </div>

        <div class="d-flex align-items-center gap-1">
            <span>Totale:</span>
            <span class="badge bg-dark">${data.length}</span>
        </div>

    </div>
`;

        aggiornaMappa();


    // Bootstrap select

    // autocomplete dell'input
    const dl = document.getElementById("listaSportelli");

    data.forEach(m => {
        const opt = document.createElement("option");
        opt.value = m.sportello;
        dl.appendChild(opt);
    });

    })
    .catch(err => {

        document.getElementById('stats').innerHTML =
            '<span style="color:red">Errore caricamento dati</span>';

        console.error(err);

    });

    

// solo rossi
document
    .getElementById("soloRossi")
    .addEventListener("change", aggiornaMappa);




// zoom iniziale
/*document.getElementById("resetMap").addEventListener("click", function () {

    if (initialView.bounds) {
        map.fitBounds(initialView.bounds, { padding: [30, 30] });
    } else {
        map.setView([44.4056, 8.9463], 11); // fallback
    }

});  */


// ricerca per sportello

document.getElementById("searchSportello")
    .addEventListener("change", function () {

        const sportello = this.value;
        if (!sportello) return;

        const trovato = datiMezzi.find(m => m.sportello === sportello);
        if (!trovato) return;

        map.setView([trovato.lat, trovato.lon], 18, {
            animate: true
        });

        const allLayers = [
            ...clusterVerdi.getLayers(),
            ...clusterRossi.getLayers()
        ];

        allLayers.forEach(layer => {
            if (layer.options.sportello === sportello) {
                layer.openPopup();
            }
        });

});

</script>
</body>

</html>