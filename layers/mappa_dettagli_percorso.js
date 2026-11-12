mappaPercorso = creaMappa("mappa_percorso");

mappercorso = mappaPercorso.map;
initialView = mappaPercorso.initialView;

// var per definire se la mappa viene richiamata tramite bottone piazzole o aprendo dirattamente la tab
//lo uso poi per fare zoom alla singola asta o al layer
astaSelezionata = false;
indiceAstaSelezionata = -1;

// funzione per definire lo stile in base agli elementi sull'asta
function stileAsta(feature) {
    if (feature.properties.count_elem > 0) {
        return {
            color: '#c70000',
            weight: 4,
            opacity: 0.8
        };
    }

    return {
        color: '#c70000',
        weight: 4,
        opacity: 0.8,
        dashArray: '8, 5'
    };
}

// aggiugo layer delle aste alla mappa
layerAstePercorso = L.geoJSON(null, {

    style: function(feature) {
        return stileAsta(feature);
    },

    /*onEachFeature: function(feature, layer) {

        layer.bindPopup(`
            <b>Asta:</b> ${feature.properties.id_asta}<br>
            <b>Sequenza:</b> ${feature.properties.num_seq}<br>
            <b>Elementi:</b> ${feature.properties.count_elem}<br>
            <b>Piazzole:</b> ${feature.properties.count_piazzole}
        `);

    }*/
   //seleziono la geometria al click sulla mappa
    onEachFeature: function(feature, layer) {
        layer.on('click', function(e) {
            L.DomEvent.stopPropagation(e);
            evidenziaAsta(feature.properties.id_asta, 0, cod_percorso, nome_servizio);
        });
    }

}).addTo(mappercorso);


// popolo il layer delle aste con il risultato della query
function caricaAstePercorso(cod_percorso) {

    fetch(`./layers/aste_percorso.php?cod_percorso=${cod_percorso}`)

        .then(response => {
            if (!response.ok) {
                throw new Error('Errore caricamento aste');
            }
            return response.json();
        })

        .then(geojson => {

            layerAstePercorso.clearLayers();
            layerAstePercorso.addData(geojson);

            astePercorso = layerAstePercorso.getLayers();
            
            if (astePercorso.length > 0) {
                posizioneTab = document.getElementById('posizione-tab');
                posizioneTab.addEventListener('shown.bs.tab', function () {
                    //aspetto che il DOM sia interamente caricato e ricalcolo la dimensione della mappa (ncessario con tab nascosto)
                    setTimeout(() => {
                        mappercorso.invalidateSize();
                        // se vado direttamente sulla mappa senza passare dal bottone delle piazzole setto la bbox al layer
                        if (!astaSelezionata) {
                            mappercorso.fitBounds(layerAstePercorso.getBounds(), {
                                padding: [15, 15],
                                maxZoom: 19
                            });
                            disableBottoni();
                        }
                    }, 200);
                });
            }

        })

        .catch(error => console.error(error));

}

caricaAstePercorso(cod_percorso);


function caricaInfoAsta(properties, cod_percorso, nome_servizio) {
    console.log(properties.dict_elem_piazzole)
    console.log(properties.dict_elem_piazzole_np)

    checkPiazzole = 0;
    checkPiazzoleNp = 0;

     html = `
        <h6>Dettaglio asta ${properties.nome_via}</h6>
        <span>Sequenza ${properties.id ?? 0}</span>`;

    html_elem = '';
    if (
        (properties.dict_elem_piazzole != null && Object.keys(properties.dict_elem_piazzole).length > 0) ||
        (properties.dict_elem_piazzole_np != null && Object.keys(properties.dict_elem_piazzole_np).length > 0)
    ) {
        for (const [id_piazzola, elementi] of Object.entries(properties.dict_elem_piazzole ?? {})) {

            //recupero l'array corrispondente della piazzola dalla colonna array_piazzole
            // e mi salvo civico e riferimento
            dettagli_piazzola = properties.array_piazzole.find(function(p) {
                return p[0] == id_piazzola;
            });

            riferimentoP = dettagli_piazzola?.[1] ?? '';
            civicoP = dettagli_piazzola?.[2] === '' ? '' : 'civico ' + (dettagli_piazzola?.[2] ?? '');

            html_elem += `
                <div id="piazzola-${id_piazzola}">
                    <b>Piazzola ${id_piazzola} ${civicoP } ${riferimentoP} </b> 
                    <ul style="list-style-type:none;">
            `;

            elementi.forEach(([id_tipo_elemento, tipo_elemento, count]) => {
                html_elem += `
                    <li>
                        <button class="btn btn-sm btn-danger" id="checkElem-${count}-${tipo_elemento}"><i class="fa-solid fa-xmark"></i></button> ${count} x ${tipo_elemento}
                    </li>
                `;
            });

            html_elem += `</ul></div>`;

            checkPiazzole += 1;
        }

        for (const [id_piazzola_np, elementi_np] of Object.entries(properties.dict_elem_piazzole_np ?? {})) {

            /******************************
             * AAGIUNGERE UN FINTO TOAST WARNING CHE SE C'è ALMENO UNA PIAZZOLA SULL'ASTA NON ASSOCIATA AL PERCORSO
             * SPIEGHI IL MOTIVO OVVERO:
             * SU QUESTA ASTA CI SONO ELEMENTI COMPATIBILI CON IL SERVIZIO XXXXX NON ASSOCIATI AL PERCORSO
             * ******************************/


            //recupero l'array corrispondente della piazzola dalla colonna array_piazzole
            // e mi salvo civico e riferimento
            dettagli_piazzola_np = properties.array_piazzole_np.find(function(p_np) {
                return p_np[0] == id_piazzola_np;
            });

            riferimentoP_np = dettagli_piazzola_np?.[1] ?? '';
            civicoP_np = dettagli_piazzola_np?.[2] === '' ? '' : 'civico ' + (dettagli_piazzola_np?.[2] ?? '');

            html_elem += `
                <div id="piazzola-${id_piazzola_np}">
                    <b>Piazzola ${id_piazzola_np} ${civicoP_np} ${riferimentoP_np} </b> 
                    <ul style="list-style-type:none;">
            `;

            elementi_np.forEach(([id_tipo_elemento_np, tipo_elemento_np, count_np]) => {
                html_elem += `
                    <li>
                        <button class="btn btn-sm btn-success" id="checkElem-${count_np}-${tipo_elemento_np}"><i class="fa-solid fa-plus"></i></button>
                        ${count_np} x ${tipo_elemento_np}
                    </li>
                `;
            });

            html_elem += `</ul></div>`;

            checkPiazzoleNp += 1;
        }

        if (checkPiazzoleNp > 0 && checkPiazzole > 0) {
            html_elem += `
                <p class="alert alert-info">
                    Su questa asta ci sono alcuni contenitori compatibili con il servizio <i>${nome_servizio}</i> non associati al percorso. 
                </p>
            `;
        }

        if (checkPiazzoleNp > 0 && checkPiazzole === 0) {
            html_elem += `
                <p class="alert alert-warning">
                    Ci sono contenitori compatibili con il servizio <i>${nome_servizio}</i>, ma non sono associati al percorso.
                    <br> Associali al servizio o elimina l'asta. 
                </p>
            `;
        }

        /* aggiungere un bottone che mi permette di rimuovere tutte le piazzole e l'asta, se le piazzole sono associate al percorso quando clicco sul bottone mi deve chiedere conferma molto esplicita,
        se le piazzole non sono associate al percorso si può anche fare l'eleiminazione dell'asta senza troppe conferme*/

        /*
            1 tutte le piazzole dell'asta sono già associate al percorso --> ok così come è ora
            2 anche solo una pizzola dell'asta non è associata al percorso --> aggiungere un toast warning che spiega il motivo
            3 nessuna piazzola dell'asta è associata al percorso --> aggiungere un toast error che invita ad eliminare l'asta

            Questi tre casi li verifico con dei contatori nei for 
        */

        /*
        backoffice dei tasti + e x da condividere con la mappa
        */

    }else {
        html_elem +=`
            <div class="alert alert-danger">
                Nessun contenitore su questa asta. 
                <div class="d-flex justify-content-end mt-2">
                    <button class="btn btn-sm btn-danger" id="eliminaAsta-${properties.id_asta}" onclick="eliminaAsta('${properties.id_asta}')">
                        <i class="fa-solid fa-xmark"></i> Elimina asta
                    </button>
                </div>
            </div>
        `;
    }
    html += `<div id="info_elem">${html_elem}</div>`;

    $('#info_tappa').html(html);
}

function eliminaAsta(id_asta) {
    if (confirm('Sei sicuro di voler eliminare l\'asta ' + id_asta + '?')) {
        /*fetch(`./layers/elimina_asta.php?id_asta=${id_asta}`, {
            method: 'DELETE'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore eliminazione asta');
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);
            // Ricarica le aste del percorso dopo l'eliminazione
            caricaAstePercorso(cod_percorso);
        })
        .catch(error => console.error(error));*/
    }
}


// la funzione viene chiamata al click del bottone su tabella piazzole
function evidenziaAsta(id_asta, id_piazzola, cod_percorso, nome_servizio) {

    console.log('sono passata dalla piazzola '+id_piazzola)

    console.log('il servizio è '+nome_servizio)

    astaSelezionata = true;

    layerAstePercorso.eachLayer(function(layer) {
        
        //console.log('Proprietà:', layer.feature.properties);
        if (layer.feature.properties.id_asta == id_asta) {

            indiceAstaSelezionata = layer.feature.id;

            layer.setStyle({
                color: 'green',
                weight: 6,
                opacity: 1
            });

            // Recupero le proprietà alfanumeriche
            caricaInfoAsta(layer.feature.properties, cod_percorso, nome_servizio);
            
            bbox_feature = L.geoJSON(layer.feature).getBounds();
            //console.log(bounds)
            //mappercorso.invalidateSize();
            mappercorso.fitBounds(bbox_feature, {
                padding: [40, 40],
                maxZoom: 19,
                animate: true
            });

            //layer.openPopup();

        } else {

            layer.setStyle({
                color: '#c70000',
                weight: 4,
                opacity: 0.8
            });

        }

        disableBottoni();

    });

}

// funzione sui due bottoni per spostarsi avanti e indietro nelle aste del percorso
function navigaAsta(passo) {

    nuovoIndice = indiceAstaSelezionata + passo;

    if (nuovoIndice < 0 || nuovoIndice >= astePercorso.length) {
        return;
    }

    nuovaAsta = astePercorso[nuovoIndice];

    evidenziaAsta(nuovaAsta.feature.properties.id_asta, 0, cod_percorso, nome_servizio);
}

//disabilito pulsante indietro se sono sulla prima asta e quello avanti se sono sull'ultima
function disableBottoni() {

    $('#btnAstaPrecedente').prop(
        'disabled',
        indiceAstaSelezionata <= 0
    );

    $('#btnAstaSuccessiva').prop(
        'disabled',
        indiceAstaSelezionata >= astePercorso.length - 1
    );
}

// resetto la mappa deselezionando le geom e rimettendo la view alla bbox del layer
function resetMappaPercorso() {

    // Ripristino lo stile di tutte le aste
    layerAstePercorso.eachLayer(function(layer) {
        layer.setStyle(stileAsta(layer.feature));
    });

    // Chiudo eventuali popup
    //mappercorso.closePopup();

    // Ripristino la vista iniziale
    mappercorso.fitBounds(layerAstePercorso.getBounds(), {
        padding: [15, 15],
        maxZoom: 19
    });

    //tolgo le info e ripristino la scritta di default
    $('#info_tappa').html(`
        <p class="alert alert-primary">
            Seleziona un'asta dalla tabella per visualizzarne i dettagli.
        </p>
    `);

    // Reset dello stato
    astaSelezionata = false;
    
    // reset indice asta selezionata
    indiceAstaSelezionata = -1;

    //reset stato bottoni
    disableBottoni();
}

// richiamo il reset della mappa ogni volta che dalla tab della mappa passo a un'altra
document.querySelectorAll('#viewMemberModal [data-bs-toggle="tab"]')
    .forEach(function(tab) {

        tab.addEventListener('shown.bs.tab', function(event) {

            // Se entro in una tab diversa da Posizione
            if (event.target.id !== 'posizione-tab') {

                resetMappaPercorso();

            }

        });

    });

// essendo la mappa dentro a un tab non attivo leaflet fa un po' di casino con le dimensioni della mappa, 
// quando il tab viene mostrato bisogna ridisegnare la mappa e impostare la vista sulla piazzola 
// intercettando l'evento del cambio di tab ovvero "shown.bs.tab" del tab "posizione-tab"
// posizioneTab = document.getElementById('posizione-tab');
// posizioneTab.addEventListener('shown.bs.tab', function () {
//     setTimeout(() => {
//         mappercorso.invalidateSize();
//         mappercorso.setView(
//             [44.4048, 8.9444],
//             18
//         );
//     }, 100);
// });


