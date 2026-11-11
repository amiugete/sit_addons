// creo una Map dei rifiuti presenti nella mia bbox
const rifiutiDisponibili = new Map(); // solo vista corrente

// vedo che filtri sono attivi
const filtriRifiutiAttivi = new Set(); // scelta utente persistente

// uesta invece è una variabile globale che indica se il filtro rifiuti è attivo o meno
let filtroRifiutiAttivo = true;

//let geojsonPiazzole = null;
let firmaRifiuti = "";


// per la piazzola selezionata
let piazzolaSelezionata = null;
let markerPiazzolaSelezionata = null;


let piazzoleById = new Map();


// funzione per evidenziare la piazzola selezionata su mappa
function evidenziaPiazzola(markerP) {

    
    if (markerPiazzolaSelezionata) {
        markerPiazzolaSelezionata.getElement()?.classList.remove("piazzola-selezionata");
    }

    markerPiazzolaSelezionata = markerP;

    markerP.getElement()?.classList.add("piazzola-selezionata");
}



// funzione per evidenziare la piazzola selezionata dal select2
function evidenziaPiazzolaById(idPiazzola) {

    const markerPiazzola = piazzoleById.get(idPiazzola);

    if (!markerPiazzola) {
        console.warn("Piazzola non trovata:", idPiazzola);
        return;
    }

    evidenziaPiazzola(markerPiazzola);

}




var popoverContentPiazzole = `Piazzole (o Punti di Raccolta PdR) AMIU`;

var popoverTriggerListPiazzole = [].slice.call(document.querySelectorAll('#btnInfoPiazzole'))

var popoverListPiazzole = popoverTriggerListPiazzole.map(function (popoverTriggerEl) {
return new bootstrap.Popover(popoverTriggerEl, {
    html: true,
    content: popoverContentPiazzole
})
})




const clusterPiazzole = L.markerClusterGroup({

    disableClusteringAtZoom: 18,
    spiderfyOnMaxZoom: true,
    showCoverageOnHover: false,

    iconCreateFunction: function(cluster) {

        const count = cluster.getChildCount();

        let classe = "cluster-small";

        if (count >= 20)
            classe = "cluster-medium";

        if (count >= 100)
            classe = "cluster-large";

        return L.divIcon({
            html: `<div><span>${count}</span></div>`,
            className: `piazzola-cluster ${classe}`,
            iconSize: L.point(42, 42)
        });
    }

});



const layerPiazzole = L.geoJSON(null, {

    pointToLayer: function(feature, latlng) {

        const marker = L.marker(latlng, {

            icon: creaIconaPiazzola(feature.properties)

        });

        marker.feature = feature;
        
        piazzoleById.set(feature.properties.id_piazzola, marker);


        return marker;

    },

    onEachFeature: function(feature, marker) {

        marker.on("click", function() {

            piazzolaSelezionata = marker.feature.properties.id_piazzola;

            selezionaPiazzola(
                feature.properties.id_piazzola,
                feature.properties.id_asta,
                marker
            );
            evidenziaPiazzola(marker);
        });

        marker.bindTooltip(
            "Piazzola " + feature.properties.id_piazzola,
            { direction: "top" }
        );

    }

});







// checkbox layer piazzole
$("#chkPiazzole").on("change", function(){

    aggiornaPiazzole(map);

});








function creaIconaPiazzola(properties) {


    const colore = properties.colore_piazzola || '#c200c9';



    let simbolo = "";

    let classe = "piazzola";


    if (properties.is_pap == 1 && properties.suolo_privato == 0) {

        simbolo = '<i class="fa-solid fa-user"></i>';
        //classe += " pap";

    } else if (properties.is_pap == 1 && properties.suolo_privato == 1) {

        /*simbolo = `<span class="user-lock">
                <i class="fa-solid fa-user fa-inverse"></i>
                <i class="fa-solid fa-lock lock-overlay fa-inverse"></i>
            </span>`;*/
        simbolo = '<i class="fa-solid fa-user-lock fa-inverse"></i>';
        //classe += " ecopunto";

    } else if (properties.ecopunto == 1 && properties.suolo_privato == 0) {

        simbolo = '<i class="fa-solid fa-recycle"></i>';
        //classe += " ecopunto";

    } else if (properties.ecopunto == 1 && properties.suolo_privato == 1) {
        // devo definire posizione-lock e lock-overlay nei CSS
        simbolo = `<span class="fa-stack posizione-lock">
            <i class="fa-solid fa-recycle fa-stack-1x fa-inverse"></i>
            <i class="fa-solid fa-lock lock-overlay fa-inverse"></i>
        </span>`;
        //classe += " ecopunto";

    }


    /*if (properties.suolo_privato == 1) {

        classe += " privato";

    }*/


    return L.divIcon({

        className: classe,

        html: `

            <div class="piazzola-marker"
                style="--marker-bg:${colore}88; --marker-bd:${colore};">

                ${simbolo}

            </div>

        `,

        iconSize: [32,32],

        iconAnchor: [16,16]

    });

}



function svuotaLayerPiazzole() {

    piazzoleById.clear();

    layerPiazzole.clearLayers();

    clusterPiazzole.clearLayers();

    if (map.hasLayer(clusterPiazzole))
        map.removeLayer(clusterPiazzole);

    //geojsonPiazzole = null;

    firmaRifiuti = "";
    rifiutiDisponibili.clear();
    $("#filtriRifiuti").empty();



    if (cacheLayer.piazzole.controller)
        cacheLayer.piazzole.controller.abort();

    cacheLayer.piazzole.bounds = null;
    cacheLayer.piazzole.firmaQuery = null;
    cacheLayer.piazzole.ultimoAggiornamento = null;
    cacheLayer.piazzole.controller = null;

}




function aggiornaPiazzole(mappa) {

    //console.log("aggiorno piazzole");

    //console.log("zoom =" + mappa.getZoom());

    // da decidere un livello di zoom
    if (mappa.getZoom() < 11) {

        $("#chkPiazzole").prop("disabled", true);
        svuotaLayerPiazzole();

        return;
    }

    $("#chkPiazzole").prop("disabled", false);


    if (!$("#chkPiazzole").is(":checked")) {

        svuotaLayerPiazzole();

        return;
    }

    //console.log("Sono dentro aggiorno piazzole");
    const p = getParametriMappa(mappa);
    //console.log(p);
    const firmaQuery = p.params.toString();

    /*console.log("PARAMS:", p.params.toString());
    console.log("VIEWPORT:", p.viewport);
    console.log("BOUNDS:", p.bounds);*/
    
    // se il viewport è completamente contenuto
    // nell'area già caricata evito una nuova query
    if (
        cacheLayer.piazzole.bounds &&
        cacheLayer.piazzole.bounds.contains(p.viewport) &&
        cacheLayer.piazzole.firmaQuery === firmaQuery
    ) {
        return;
    }


    // costruisco la URL
    const url =
        "layers/piazzole.php?" +
        p.params_filter.toString();


    fetchJson(url, cacheLayer.piazzole)

        .then(r => r.json())

        .then(data => {

            layerPiazzole.clearLayers();
            layerPiazzole.addData(data);


            cacheLayer.piazzole.bounds = p.bounds;
            cacheLayer.piazzole.firmaQuery = firmaQuery;
            cacheLayer.piazzole.ultimoAggiornamento = new Date();


            aggiornaElencoRifiuti();

            visualizzaPiazzole(mappa);


            if (piazzolaSelezionata) {

                const marker =
                    piazzoleById.get(piazzolaSelezionata);

                console.log(
                    "marker selezionato: ",
                    marker
                );

                if (marker) {

                    console.log(
                        "evidenzio marker selezionato"
                    );

                    evidenziaPiazzola(marker);
                }
            }

        })

        .catch(err => {

            if (err.name === "AbortError")
                return;

            console.error(err);

        });
}



function aggiornaElencoRifiuti() {

    const nuovaMap = new Map();

    layerPiazzole.eachLayer(function(marker) {

        const rifiuti = marker.feature.properties.rifiuti || [];

        rifiuti.forEach(function(r) {

            nuovaMap.set(r.nome, r);

        });

    });

    const nuovaFirma =
        [...nuovaMap.keys()]
            .sort()
            .join("|");

    if (nuovaFirma === firmaRifiuti)
        return;

    firmaRifiuti = nuovaFirma;

    rifiutiDisponibili.clear();

    nuovaMap.forEach((v, k) => rifiutiDisponibili.set(k, v));

    aggiornaMenuRifiuti();

}



function visualizzaPiazzole(mappa) {

    // il cluster deve essere associato alla mappa
    // PRIMA di aggiungergli i marker
    if (!mappa.hasLayer(clusterPiazzole))
        mappa.addLayer(clusterPiazzole);


    clusterPiazzole.clearLayers();


    const boundsVisibili = mappa.getBounds();

    const conteggi = new Map();


    // recupero i filtri una sola volta
    const filtriAttivi =
        $(".filtro-rifiuto:checked")
            .map(function () {
                return this.value;
            })
            .get();


    layerPiazzole.eachLayer(function(marker) {


        // considero solo quello realmente visibile
        if (!boundsVisibili.contains(marker.getLatLng()))
            return;


        const rifiuti =
            marker.feature.properties.rifiuti || [];


        rifiuti.forEach(r => {

            conteggi.set(
                r.nome,
                (conteggi.get(r.nome) || 0) + 1
            );

        });


        const visibile =
            filtriAttivi.length > 0 &&
            rifiuti.some(r =>
                filtriAttivi.includes(r.nome)
            );


        if (visibile)
            /*console.log("AGGIUNGO MARKER AL CLUSTER");
            console.log("marker:", marker);
            console.log("marker._map:", marker._map);
            console.log("cluster._map:", clusterPiazzole._map);*/
            clusterPiazzole.addLayer(marker);

    });


    aggiornaContatoriRifiuti(conteggi);

}



// funzione per aggiornare i contatori dei rifiuti nel menu
function aggiornaContatoriRifiuti(conteggi){

    rifiutiDisponibili.forEach((r,nome)=>{

        const el =
          document.getElementById(
             "count-rifiuto-"+nome
          );

        if(el)
            el.textContent = conteggi.get(nome)||0;

    });

}


function aggiornaMenuRifiuti(){

    let html = "";

    [...rifiutiDisponibili.values()]
        .sort((a,b)=>a.ordine-b.ordine)
        .forEach(function(r){
            

            let inizializzatoFiltroRifiuti = false;


            const checked =
                filtriRifiutiAttivi.size === 0 ||
                filtriRifiutiAttivi.has(r.nome)
                ? "checked"
                : "";


            html += `
                <div class="form-check">

                    <input
                        class="form-check-input filtro-rifiuto"
                        type="checkbox"
                        value="${r.nome}"
                        ${checked}>

                    <label class="form-check-label d-flex justify-content-between w-100">

                        <span>${r.nome}</span>

                        <span class="badge bg-secondary ms-2"
                            id="count-rifiuto-${r.nome}">
                            0
                        </span>

                    </label>

                </div>
            `;

        });

    $("#filtriRifiuti").html(html);
    
    inizializzatoFiltroRifiuti = true;



}

 // nel file html devo avere un div con id filtriRifiuti <div id="filtriRifiuti"></div>


$(document).on("change", ".filtro-rifiuto", function(){

    if(this.checked)
        filtriRifiutiAttivi.add(this.value);
    else
        filtriRifiutiAttivi.delete(this.value);

    visualizzaPiazzole(mappa);

});



function selezionaPiazzola(idPiazzola, idAsta, layerPiazzola) {

    console.log("Piazzola selezionata: " + idPiazzola);
    // funzione da definire in layer_piazzole.js
    //evidenziaPiazzola(layerAsta);

    
    if (map.hasLayer(layerAste)) {
        console.log("Asta della piazzola selezionata: " + idAsta);
        const layerAsta = asteById.get(idAsta);

        if (layerAsta) {
            evidenziaAsta(layerAsta);
        }

    } else {
        console.log("Layer aste non presente sulla mappa");
    }
        // funzione definita in layer_aste.js
    // evidenziaAsta(layerAsta);

    console.log("apro pannello risultati");
    // funzione definita in layer_aste.js
    apriPannelloRisultati();

    console.log("carico dettaglio Asta");
    // funzione definita in layer_aste.js
    caricaDettaglioAsta(idAsta);

    console.log("carico dettaglio Piazzola");
    // funzione da definire in layer_piazzole.js
    caricaDettaglioPiazzola(idPiazzola);

}
