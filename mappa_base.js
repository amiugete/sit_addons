/*
Mappa di base da caricare all'occorenza nelle varie pagine PHP. 
Contiene la mappa, layer di base e le funzioni di base.

la mappa va richiamata in php con:
<script type="text/javascript" src="mappa_base.js"></script>

e va aggiunto alla pagina php il div con id "map" dove verrà caricata la mappa (width e height sono settati in main.css):
<div id="map"></div> 

*/

const map = L.map('map', {
    minZoom: 1,
    maxZoom: 22,
    attributionControl: false
}).setView([44.4056, 8.9463], 13);

// lo uso per il tasto torna allo zoom iniziale
const initialView = {
    bounds: null,
    zoomed: false
};

L.control.scale().addTo(map);

L.control.attribution({ 
    position: 'bottomright',
    prefix: 'Mappa realizzata da <img src="./favicon_SIT.ico" width="12" height="12" alt="SIT" style="vertical-align: middle;"> APTE con <a href="https://leafletjs.com/" title="A JavaScript library for interactive maps" target="_blank">Leaflet</a>'
}).addTo(map);



/*L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 22,
    maxNativeZoom: 19
}).addTo(map);*/

var osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 22,
    maxNativeZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(map);

var osmHOT = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
    maxZoom: 22,
    maxNativeZoom: 19,
    attribution: '© OpenStreetMap contributors, Tiles style by Humanitarian OpenStreetMap Team hosted by OpenStreetMap France'});

var esriImagery = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    {
        maxZoom: 22,
        maxNativeZoom: 19,
        attribution: '© Esri, Vantor, Earthstar Geographics'
    }
);

var esriLabels = L.tileLayer(
    'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
    {
        maxZoom: 22,
        maxNativeZoom: 19
    }
);

var esriLabelsTraffic = L.tileLayer(
    'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}',
    {
        maxZoom: 22,
        maxNativeZoom: 19,
        attribution: 'HERE, Garmin, © OpenStreetMap'
    }
);

var esriHybrid = L.layerGroup([
    esriImagery,
    esriLabels,
    esriLabelsTraffic
]);

var streetMap = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 22,
    maxNativeZoom: 19,
     attribution: 'Esri, HERE, Garmin, USGS, Intermap, INCREMENT P, NRCan, METI, NGCC, © OpenStreetMap'
});

var baseMaps = {
    "OpenStreetMap": osm,
    "OpenStreetMap.HOT": osmHOT,
    "Esri Satellite": esriImagery,
    "Esri Hybrid": esriHybrid,
    "Esri Street Map": streetMap
};

var layerControl = L.control.layers(baseMaps).addTo(map);

//geolocation utilizza plugin Leaflet.Locate installato con npm e richiamato in req e req_bottom
L.control.locate({
    strings: {
      title: "Zoom alla tua posizione",
      popup: "Sei qui"      
    },
    showPopup: false
}).addTo(map);

// zoom iniziale
if (document.getElementById("resetMap")) {
    document.getElementById("resetMap").addEventListener("click", function () {

        if (initialView.bounds) {
            map.fitBounds(initialView.bounds, { padding: [30, 30] });
        } else {
            map.setView([44.4056, 8.9463], 11); // fallback
        }

    })
};


