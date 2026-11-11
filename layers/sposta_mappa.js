// SPOSTAMENTO DELLA MAPPA (inserisco un timer per evitare troppe chiamate veloci nel tempo)
// id del timer attualmente attivo
let timerLayers = null;


// quando l'utente sposta la mappa entro qua dentro
map.on("moveend", () => {
    //Se c'era un aggiornamento programmato, annullalo
    clearTimeout(timerLayers);
    // creo un nuovo timer per aspettare XXX ms
    timerLayers = setTimeout(() => {

        aggiornaAste(map);
        aggiornaPiazzole(map);
    }, 250);

    if ($("#chkPiazzole").is(":checked"))
        visualizzaPiazzole(map);
});




function fetchJson(url, cache) {

    if (cache.controller)
        cache.controller.abort();

    cache.controller = new AbortController();

    return fetch(url, {
        signal: cache.controller.signal
    });

}




// funzione per gestire parametri mappa (bbox e filtri)
function getParametriMappa(mappa) {

    let viewport = mappa.getBounds();

    let bounds = viewport.pad(0.30);


    let params = new URLSearchParams({

        xmin: bounds.getWest(),
        ymin: bounds.getSouth(),
        xmax: bounds.getEast(),
        ymax: bounds.getNorth()

    });


    let params_filter = new URLSearchParams({

        xmin: bounds.getWest(),
        ymin: bounds.getSouth(),
        xmax: bounds.getEast(),
        ymax: bounds.getNorth()

    });


    if (filtraLayer) {

        let comune = $("#cmbComune").val();
        let via = $("#cmbVia").val();

        console.log("Comune: " + comune);

        if (comune)
            params_filter.append(
                "id_comune",
                comune
            );

        if (via)
            params_filter.append(
                "id_via",
                via
            );
    }


    return {
        viewport,
        bounds,
        params,
        params_filter
    };
}


let cacheLayer = {

    aste: {
        bounds: null,
        firmaQuery: null,
        ultimoAggiornamento: null,
        controller: null, 
        visible: false
    },

    piazzole: {
        bounds: null,
        firmaQuery: null,
        ultimoAggiornamento: null,
        controller: null, 
        visible : false
    }

};