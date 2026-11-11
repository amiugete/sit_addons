const asteById = new Map();


const layerAste = L.geoJSON(null, {

    style: {
        color: "#007bff",
        weight: 2
    },

    onEachFeature: function(feature, layer) {

        // memorizzo il layer dell'asta (serve poi alle piazzole per evidenziare l'asta a cui appartengono)
        asteById.set(feature.properties.id_asta, layer);


        layer.on("click", function() {

            console.log(feature.properties);
            
            selezionaAsta(feature.properties.id_asta, layer);

            

        });

    }

});

let astaSelezionata = null;

function evidenziaAsta(layer) {

    if (astaSelezionata) {

        astaSelezionata.setStyle({
            color: "#007bff",
            weight: 2
        });

    }

    layer.setStyle({
        color: "red",
        weight: 5
    });

    astaSelezionata = layer;

}



function selezionaAsta(idAsta, layer) {

    console.log("Asta selezionata: " + idAsta);
    evidenziaAsta(layer);

    console.log("apro pannello risultati");
    apriPannelloRisultati();

    console.log("carico dettaglio asta");
    caricaDettaglioAsta(idAsta);

}


var popoverContentAste = `Grafo stradake AMIU <b> visibile solo a determinati livelli di zoom</b></b><br>`;


var popoverTriggerListAste = [].slice.call(document.querySelectorAll('#btnInfoAste')
)
var popoverList = popoverTriggerListAste.map(function (popoverTriggerEl) {
return new bootstrap.Popover(popoverTriggerEl, {
    html: true,
    content: popoverContentAste
})
})

// evento legato al checkbox per mostrare/nascondere le aste
$("#chkAste").on("change", function(){

    aggiornaAste(map);
    

});



function svuotaLayerAste() {

    asteById.clear();

    layerAste.clearLayers();

    if (map.hasLayer(layerAste))
        map.removeLayer(layerAste);


    if (cacheLayer.aste.controller)
        cacheLayer.aste.controller.abort();

    cacheLayer.aste.bounds = null;
    cacheLayer.aste.firmaQuery = null;
    cacheLayer.aste.ultimoAggiornamento = null;
    cacheLayer.aste.controller = null;

}



function aggiornaAste(mappa) {

    // controllo livello di zoom, se è inferiore a 15 non faccio nulla
    if (mappa.getZoom() < 15) {

        $("#chkAste").prop("disabled", true);
        svuotaLayerAste();
        aggiornaPulsanteAsta();
        return;

    }

    // abilito il checkbox
    $("#chkAste").prop("disabled", false);

    // controllo se il checkbox è selezionato, altrimenti non faccio nulla
    if (!$("#chkAste").is(":checked")) {

        console.log("checkbox aste non selezionato, non faccio nulla");

        svuotaLayerAste();
        aggiornaPulsanteAsta();
        return;

    }

    const p = getParametriMappa(mappa);

    const firmaQuery = p.params.toString();

    // se il viewport è completamente contenuto
    // nell'area già caricata evito una nuova query
    if (
        cacheLayer.aste.bounds &&
        cacheLayer.aste.bounds.contains(p.viewport) &&
        cacheLayer.aste.firmaQuery === firmaQuery
    ) {
        return;
    }

    const url = "layers/aste.php?" + p.params.toString();

    fetchJson(url, cacheLayer.aste)

        .then(r => r.json())

        .then(data => {

            layerAste.clearLayers();

            layerAste.addData(data);

            // aggiungo il layer alla mappa se non è già presente
            if (!mappa.hasLayer(layerAste)) {
                mappa.addLayer(layerAste);
            }

            // salvo la cache
            cacheLayer.aste.bounds = p.bounds;
            cacheLayer.aste.firmaQuery = firmaQuery;
            cacheLayer.aste.ultimoAggiornamento = new Date();

            // Riapplica lo stile alle aste selezionate
            applicaEvidenziazioneAste();

            aggiornaPulsanteAsta();

        })

        .catch(err => {

            if (err.name === "AbortError")
                return;

            console.error(err);

        });
}




// bottone per visualizzare le aste,

// funzione per mostrare/nascondere il layer delle aste, se il livello di zoom è sufficiente
$("#btnToggleAsta").on("click", function () {

    if (map.getZoom() < 15) {
        return;
    }

    const chk = $("#chkAste");

    chk.prop("checked", !chk.is(":checked"))
       .trigger("change");
});




// funzione per aggiornare l'icona del pulsante in base allo stato del layer delle aste
function aggiornaPulsanteAsta() {

    const acceso = map.hasLayer(layerAste);

    $("#icoToggleAsta")
        .toggleClass("fa-eye", acceso)
        .toggleClass("fa-eye-slash", !acceso);

    $("#btnToggleAsta")
        .attr(
            "title",
            acceso ? "Nascondi il grafo" : "Mostra il grafo"
        );
}


// funzioni da usare in caso di ricerca via 

let idsAsteEvidenziate = [];

function evidenziaAste(idsAste) {

    idsAsteEvidenziate = idsAste;

    // Accende il layer
    $('#chkAste').prop('checked', true).trigger('change');

    applicaEvidenziazioneAste();
}




function applicaEvidenziazioneAste() {

    layerAste.eachLayer(function(layer) {

        const id = layer.feature.properties.id_asta;

        if (idsAsteEvidenziate.includes(id)) {

            layer.setStyle({
                color: "limegreen",
                weight: 5
            });

        } else {

            layer.setStyle({
                color: "#007bff",
                weight: 2
            });

        }

    });

}


