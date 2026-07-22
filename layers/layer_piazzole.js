

// creo una Map dei rifiuti presenti nella mia bbox
const rifiutiDisponibili = new Map();

let geojsonPiazzole = null;
let firmaRifiuti = "";


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


// aggiungo il cluster alla mappa
map.addLayer(clusterPiazzole);



// checkbox layer piazzole
$("#chkPiazzole").on("change", function(){

    aggiornaPiazzole();

});


// aggiorno quando cambia bbox
map.on("moveend", aggiornaPiazzole);





function creaIconaPiazzola(properties) {


    const colore = properties.colore_piazzola || '#c200c9';



    let simbolo = "";

    let classe = "piazzola";


    if (properties.is_pap == 1 && properties.suolo_privato == 0) {

        simbolo = '<i class="fa-solid fa-user"></i>';
        //classe += " pap";

    } else if (properties.is_pap == 1 && properties.suolo_privato == 1) {

        simbolo = `<span class="user-lock">
                <i class="fa-solid fa-user fa-inverse"></i>
                <i class="fa-solid fa-lock lock-overlay fa-inverse"></i>
            </span>`;
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






function aggiornaPiazzole(){

    console.log("aggiorno piazzole");
    // checkbox spenta

    if(!$("#chkPiazzole").is(":checked")){


        clusterPiazzole.clearLayers();

        return;

    }



    const b = map.getBounds();


    const url =

        "layers/piazzole.php?" +

        "xmin=" + b.getWest() +

        "&ymin=" + b.getSouth() +

        "&xmax=" + b.getEast() +

        "&ymax=" + b.getNorth();




    fetch(url)


    .then(r => r.json())


    .then(data => {

        geojsonPiazzole = data;

        aggiornaElencoRifiuti();

        visualizzaPiazzole();

    })

    .catch(err => {

        console.error(
            "Errore caricamento piazzole",
            err
        );

    });


}



function aggiornaElencoRifiuti(){

    const nuovaMap = new Map();

    geojsonPiazzole.features.forEach(function(feature){

        if (!feature.properties.rifiuti)
            return;

        feature.properties.rifiuti.forEach(function(r){

            nuovaMap.set(r.nome, r);

        });

    });

    const nuovaFirma =
        [...nuovaMap.keys()]
            .sort()
            .join("|");

    if (nuovaFirma === firmaRifiuti){

        return;

    }

    firmaRifiuti = nuovaFirma;

    rifiutiDisponibili.clear();

    nuovaMap.forEach((v,k)=>rifiutiDisponibili.set(k,v));

    aggiornaMenuRifiuti();

}



function visualizzaPiazzole(){

    clusterPiazzole.clearLayers();

    if (!geojsonPiazzole)
        return;

    const filtriAttivi =
        $(".filtro-rifiuto:checked")
        .map(function(){

            return this.value;

        })
        .get();

    const layer = L.geoJSON(geojsonPiazzole, {

        pointToLayer:function(feature,latlng){

            const marker = L.marker(latlng,{

                icon: creaIconaPiazzola(feature.properties)

            });

            marker.feature = feature;

            return marker;

        },

        onEachFeature:function(feature,marker){

            marker.on("click",function(){

                selezionaPiazzola(
                    feature.properties.id_piazzola,
                    feature.properties.id_asta,
                    layer
                );

            });

            marker.bindTooltip(
                "Piazzola " + feature.properties.id_piazzola,
                {direction:"top"}
            );

        }

    });

    layer.eachLayer(function(marker){

        /*console.log(marker.feature);*/

        
        const rifiuti = marker.feature.properties.rifiuti || [];

        const visibile =

            filtriAttivi.length === 0 ||

            rifiuti.some(function(r){

                return filtriAttivi.includes(r.nome);

            });

        if(visibile){

            clusterPiazzole.addLayer(marker);

        }

    });

}


function aggiornaMenuRifiuti(){

    let html = "";

    [...rifiutiDisponibili.values()]
        .sort((a,b)=>a.ordine-b.ordine)
        .forEach(function(r){

            html += `
                <div class="form-check">

                    <input
                        class="form-check-input filtro-rifiuto"
                        type="checkbox"
                        value="${r.nome}"
                        checked>

                    <label class="form-check-label">

                        ${r.nome}

                    </label>

                </div>
            `;

        });

    $("#filtriRifiuti").html(html);

}
 // nel file html devo avere un div con id filtriRifiuti <div id="filtriRifiuti"></div>


 $(document).on("change", ".filtro-rifiuto", function(){

    visualizzaPiazzole();

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
