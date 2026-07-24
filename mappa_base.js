/*
 * Mappa base Leaflet
 *
 * Utilizzo:
 *
 * const mappa = creaMappa("map");
 *
 * oppure
 *
 * const mappa = creaMappa("mapModal", {
 *     center: [44.41, 8.93],
 *     zoom: 17,
 *     locateControl: false
 * });
 *
 * map = oggetto Leaflet
 * initialView = dati per reset
 * layerControl = controllo layer
 */

function creaMappa(idElemento, options = {}) {

    const config = {

        center: [44.4056, 8.9463],
        zoom: 13,

        minZoom: 1,
        maxZoom: 22,

        attributionControl: true,
        scaleControl: true,
        locateControl: true,
        layerControl: true,

        ...options

    };

    //----------------------------------------------------
    // Mappa
    //----------------------------------------------------

    const map = L.map(idElemento, {

        minZoom: config.minZoom,
        maxZoom: config.maxZoom,
        attributionControl: false

    }).setView(config.center, config.zoom);

    //----------------------------------------------------
    // Stato
    //----------------------------------------------------

    const initialView = {

        bounds: null,
        zoomed: false

    };

    //----------------------------------------------------
    // Controlli
    //----------------------------------------------------

    if (config.scaleControl) {

        L.control.scale().addTo(map);

    }

    if (config.attributionControl) {

        L.control.attribution({

            position: "bottomright",

            prefix:
                'Mappa realizzata da ' +
                '<img src="./favicon_SIT.ico" width="12" height="12">' +
                ' APTE con ' +
                '<a href="https://leafletjs.com/" target="_blank">Leaflet</a>'

        }).addTo(map);

    }

    //----------------------------------------------------
    // Layer base
    //----------------------------------------------------

    const osm = L.tileLayer(
        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom:22,
            maxNativeZoom:19,
            attribution:"© OpenStreetMap"
        }
    ).addTo(map);

    const osmHOT = L.tileLayer(
        'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
        {
            maxZoom:22,
            maxNativeZoom:19,
            attribution:"© OpenStreetMap contributors"
        }
    );

    const esriImagery = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        {
            maxZoom:22,
            maxNativeZoom:19,
            attribution:"© Esri"
        }
    );

    const esriLabels = L.tileLayer(
        'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
        {
            maxZoom:22,
            maxNativeZoom:19
        }
    );

    const esriTraffic = L.tileLayer(
        'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}',
        {
            maxZoom:22,
            maxNativeZoom:19
        }
    );

    const esriHybrid = L.layerGroup([
        esriImagery,
        esriLabels,
        esriTraffic
    ]);

    const streetMap = L.tileLayer(
        'https://services.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}',
        {
            maxZoom:22,
            maxNativeZoom:19
        }
    );

    const baseMaps = {

        "OpenStreetMap": osm,
        "OpenStreetMap HOT": osmHOT,
        "Esri Satellite": esriImagery,
        "Esri Hybrid": esriHybrid,
        "Esri Street Map": streetMap

    };

    //----------------------------------------------------
    // Layer control
    //----------------------------------------------------

    let layerControl = null;

    if (config.layerControl) {

        layerControl =
            L.control.layers(baseMaps).addTo(map);

    }

    //----------------------------------------------------
    // Geolocalizzazione
    //----------------------------------------------------

    if (config.locateControl) {

        L.control.locate({

            strings: {

                title: "Zoom alla tua posizione",
                popup: "Sei qui"

            },

            showPopup:false

        }).addTo(map);

    }

    //----------------------------------------------------
    // Pulsante reset
    //----------------------------------------------------

    const btnReset = document.getElementById("resetMap");

    if (btnReset) {

        btnReset.addEventListener("click", function(){

            if (initialView.bounds) {

                map.fitBounds(initialView.bounds,{
                    padding:[30,30]
                });

            }
            else {

                map.setView(
                    config.center,
                    config.zoom
                );

            }

        });

    }

    //----------------------------------------------------
    // ritorno
    //----------------------------------------------------

    return {

        map,
        initialView,
        baseMaps,
        layerControl

    };

}