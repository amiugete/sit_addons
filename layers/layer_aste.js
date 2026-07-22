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

    aggiornaAste();
    

});

map.on("moveend", aggiornaAste);



function aggiornaAste(){

    // controllo livello di zoom, se è inferiore a 15 non faccio nulla
        if(map.getZoom()<15){

            $("#chkAste").prop("disabled", true);
            asteById.clear();
            layerAste.clearLayers();

            return;

        } else {
            // abilito il checkbox
            $("#chkAste").prop("disabled", false);

            // controllo se il checkbox è selezionato, altrimenti non faccio nulla
            if(!$("#chkAste").is(":checked")){
                console.log("checkbox aste non selezionato, non faccio nulla");
                asteById.clear();
                layerAste.clearLayers();

                return;

            } else {


                

                // aggiungo il layer al map se non è già presente
                if(!map.hasLayer(layerAste)){
                    map.addLayer(layerAste);
                }

        }
        aggiornaPulsanteAsta();   
    }


    //recupero il bbox
    const b = map.getBounds();

    const url =
        "layers/aste.php?" +

        "xmin="+b.getWest()+

        "&ymin="+b.getSouth()+

        "&xmax="+b.getEast()+

        "&ymax="+b.getNorth();

    

    // recupero i dati geojson dal server e li aggiungo al layer    
    fetch(url)

    .then(r=>r.json())

    .then(data=>{

        layerAste.clearLayers();

        layerAste.addData(data);

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