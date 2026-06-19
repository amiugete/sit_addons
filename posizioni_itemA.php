<?php
//session_set_cookie_params($lifetime);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

    
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



<script>

const map = L.map('map');

// lo uso per il tasto torna allo zoom iniziale
const initialView = {
    bounds: null,
    zoomed: false
};


const clusterRossi = L.markerClusterGroup({
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


L.tileLayer(
    'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        attribution: '&copy; OpenStreetMap'
    }
).addTo(map);

let tuttiMarker = [];
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
            permanent: true,
            direction: 'right',
            offset: [10,0],
            className: 'sportello-label'
        });

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

        tuttiMarker.forEach(marker => {

            const tooltip = marker.getTooltip();

            if (!tooltip) return;

            if (zoom >= 15) {
                tooltip.getElement()?.style.setProperty(
                    'display',
                    'block'
                );
            } else {
                tooltip.getElement()?.style.setProperty(
                    'display',
                    'none'
                );
            }
        });
    }

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
document.getElementById("resetMap").addEventListener("click", function () {

    if (initialView.bounds) {
        map.fitBounds(initialView.bounds, { padding: [30, 30] });
    } else {
        map.setView([44.4056, 8.9463], 11); // fallback
    }

});    

</script>
</body>

</html>