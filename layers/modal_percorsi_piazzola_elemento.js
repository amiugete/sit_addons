
function mostraPercorsi(idPiazzola, tipoElemento) {

    const modalElement = document.getElementById('modalPercorsi');
    const modalBody = document.getElementById('modalPercorsiBody');

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    // Mostro subito il modal con caricamento
    modalBody.innerHTML = `
        <div class="text-center text-muted py-4">
            <i class="fa-solid fa-spinner fa-spin me-2"></i>
            Caricamento...
        </div>
    `;

    modal.show();


    const url =
        `./layers/percorsi_piazzola_elemento.php?id_piazzola=${encodeURIComponent(idPiazzola)}` +
        `&tipo_elemento=${encodeURIComponent(tipoElemento)}`;


    fetch(url)
        .then(response => {

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            return response.json();
        })

        .then(data => {

            if (!data.percorsi || data.percorsi.length === 0) {

                modalBody.innerHTML = `
                    <div class="alert alert-info mb-0">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Nessun percorso associato.
                    </div>
                `;

                return;
            }


            // --------------------------------------------------
            // RAGGRUPPAMENTO PER PERCORSO
            // --------------------------------------------------

            const percorsiMap = new Map();


            data.percorsi.forEach(r => {

                if (!percorsiMap.has(r.id_percorso)) {

                    percorsiMap.set(r.id_percorso, {
                        id_percorso: r.id_percorso,
                        cod_percorso: r.cod_percorso,
                        descrizione_percorso: r.descrizione_percorso,
                        stagionalita: r.stagionalita,
                        colore: r.colore,
                        turno: r.turno,
                        ordinamento: r.ordinamento,

                        // tutte le righe relative agli elementi
                        elementi: [],

                        // tipi disponibili per questo percorso
                        tipi_disponibili: r.tipi_disponibili || []
                    });
                }


                percorsiMap
                    .get(r.id_percorso)
                    .elementi
                    .push(r);
            });


            // --------------------------------------------------
            // COSTRUZIONE HTML
            // --------------------------------------------------

            let html = '';


            percorsiMap.forEach(p => {

                html += `

                    <div class="card mb-3">

                        <div class="card-header">

                            <div class="d-flex align-items-center">

                                <i class="fa-solid fa-route me-2"
                                   style="color:${p.colore}">
                                </i>

                                <div>
                                    <b>
                                    ${p.cod_percorso}
                                    </b>
                                    
                                    ${
                                        p.descrizione_percorso
                                            ? `
                                                    ${p.descrizione_percorso}
                                              `
                                            : ''
                                    }
                                    ${
                                        p.turno
                                            ? ` - Turno · ${p.turno}`
                                            : ''
                                    }
                                    
                                </div>

                            </div>

                        </div>


                        <div class="card-body py-2">

                            ${costruisciElementiPercorso(
                                p,
                                idPiazzola
                            )}

                        </div>

                    </div>

                `;
            });


            modalBody.innerHTML = html;

        })

        .catch(error => {

            console.error('Errore caricamento percorsi:', error);

            modalBody.innerHTML = `
                <div class="alert alert-danger mb-0">

                    <i class="fa-solid fa-triangle-exclamation me-2"></i>

                    Errore nel caricamento dei percorsi.

                </div>
            `;
        });
}




function costruisciElementiPercorso(percorso, idPiazzola) {

    // ==================================================
    // RAGGRUPPAMENTO
    //
    // tipo_elemento
    //     └── ripasso
    //           └── frequenza
    //                 └── elementi[]
    // ==================================================

    const elementiMap = new Map();


    percorso.elementi.forEach(e => {

        const tipo = e.tipo_elemento;
        const ripasso = Number(e.ripasso || 0);

        // La frequenza identifica la riga.
        const frequenzaKey = [
            e.num_giorni ?? '',
            e.descrizione_long ?? '',
            e.turno ?? ''
        ].join('|');


        // --------------------------------------------------
        // GRUPPO TIPO
        // --------------------------------------------------

        if (!elementiMap.has(tipo)) {

            elementiMap.set(tipo, {
                tipo_elemento: tipo,
                icona: e.icona,
                colore: e.colore,
                ripassi: new Map()
            });

        }


        const gruppoTipo = elementiMap.get(tipo);


        // --------------------------------------------------
        // GRUPPO RIPASSO
        // --------------------------------------------------

        if (!gruppoTipo.ripassi.has(ripasso)) {

            gruppoTipo.ripassi.set(
                ripasso,
                new Map()
            );

        }


        const gruppoRipasso =
            gruppoTipo.ripassi.get(ripasso);


        // --------------------------------------------------
        // GRUPPO FREQUENZA
        // --------------------------------------------------

        if (!gruppoRipasso.has(frequenzaKey)) {

            gruppoRipasso.set(frequenzaKey, {

                num_giorni: e.num_giorni,
                descrizione_long: e.descrizione_long,
                turno: e.turno,

                elementi: []

            });

        }


        gruppoRipasso
            .get(frequenzaKey)
            .elementi
            .push(e);

    });


    let html = '';


    // ==================================================
    // TIPI DI ELEMENTO PRESENTI
    // ==================================================

    elementiMap.forEach(gruppoTipo => {


        // --------------------------------------------------
        // INTESTAZIONE DEL TIPO
        // --------------------------------------------------

        html += `

            <div class="d-flex align-items-center pt-2">

                <img src="./img/${gruppoTipo.icona}"
                     style="width:28px;height:28px"
                     class="me-2">

                <strong>
                    ${gruppoTipo.tipo_elemento}
                </strong>

            </div>

        `;


        // --------------------------------------------------
        // RIPASSI ORDINATI
        // --------------------------------------------------

        const ripassiOrdinati =
            [...gruppoTipo.ripassi.keys()]
                .sort((a, b) => a - b);


        ripassiOrdinati.forEach(ripasso => {

            const gruppoRipasso =
                gruppoTipo.ripassi.get(ripasso);


            // --------------------------------------------------
            // FREQUENZE
            // --------------------------------------------------

            gruppoRipasso.forEach(gruppoFrequenza => {

                const elementi =
                    gruppoFrequenza.elementi;


                // ==========================================
                // ARRAY ID_ELEMENTO_AP
                // usato da RIMUOVI
                // ==========================================

                const idElementiAp = elementi
                    .map(e => e.id_elemento_ap)
                    .filter(id => id != null);


                // ==========================================
                // ARRAY ID_ELEMENTO
                // usato da AGGIUNGI / CLONE
                // ==========================================

                const idElementi = elementi
                    .map(e => e.id_elemento)
                    .filter(id => id != null);


                // --------------------------------------------------
                // RIGA
                // --------------------------------------------------

                html += `

                    <div class="d-flex align-items-center py-1 ps-4">

                        <div class="flex-grow-1">

                            <small>

                                ${Number(gruppoFrequenza.num_giorni)} gg

                                ${
                                    gruppoFrequenza.descrizione_long
                                        ? ` · ${gruppoFrequenza.descrizione_long}`
                                        : ''
                                }

                                ${
                                    gruppoFrequenza.turno
                                        ? ` · ${gruppoFrequenza.turno}`
                                        : ''
                                }

                                ${
                                    ripasso > 0
                                        ? `
                                            <i class="fa-solid fa-clone ms-2"
                                               title="Ripasso ${ripasso}">
                                            </i>
                                          `
                                        : ''
                                }

                            </small>

                        </div>


                        <!-- =====================================
                             RIMUOVI
                             ===================================== -->

                        <button type="button"
                                class="btn btn-sm btn-danger me-1"
                                title="Rimuovi elementi"
                                onclick='rimuoviElemento(
                                    ${percorso.id_percorso},
                                    ${JSON.stringify(idElementiAp)}
                                )'>

                            <i class="fa-solid fa-xmark"></i>

                        </button>


                        <!-- =====================================
                             AGGIUNGI RIPASSO
                             ===================================== -->

                        <button type="button"
                                class="btn btn-sm btn-success"
                                title="Aggiungi ripasso"
                                onclick='aggiungiRipasso(
                                    ${percorso.id_percorso},
                                    ${JSON.stringify(idElementi)}
                                )'>

                            <i class="fa-solid fa-clone"></i>

                        </button>

                    </div>

                `;

            });

        });

    });


    // ==================================================
    // TIPI DISPONIBILI MA NON PRESENTI
    // ==================================================

    const tipiPresenti =
        new Set(elementiMap.keys());


    const tipiMancanti =
        (percorso.tipi_disponibili || [])
            .filter(tipo =>
                !tipiPresenti.has(tipo.tipo_elemento)
            );


    tipiMancanti.forEach(tipo => {


        // Per coerenza aggiungiElemento riceve SEMPRE
        // un array, anche se contiene un solo elemento.

        const idElementi = [
            tipo.id_elemento
        ];


        html += `

            <div class="d-flex align-items-center py-2">

                <div class="flex-grow-1">

                    <span class="text-muted">
                        ${tipo.tipo_elemento}
                    </span>

                </div>


                <!-- =====================================
                     AGGIUNGI TIPOLOGIA
                     ===================================== -->

                <button type="button"
                        class="btn btn-sm btn-success"
                        title="Aggiungi elemento"
                        onclick='aggiungiElemento(
                            ${percorso.id_percorso},
                            ${JSON.stringify(idElementi)}
                        )'>

                    <i class="fa-solid fa-plus"></i>

                </button>

            </div>

        `;

    });


    return html;
}



function aggiungiRipasso(idPercorso, idElemento) {

    if (!confirm('Vuoi aggiungere un ripasso per questo elemento?')) {
        return;
    }

    aggiungiElemento(
        idPercorso,
        idElemento
    );
}


function aggiungiElemento(idPercorso, idElemento) {

    const modalElement = document.getElementById('modalPercorsi');

    const idPiazzola = modalElement.dataset.idPiazzola;
    const tipoElemento = modalElement.dataset.tipoElemento;

    const payload = {
        azione: 'aggiungi',
        id_percorso: idPercorso,
        id_elementi: [idElemento]
    };

    fetch('./backoffice/elemento_percorso.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {

        if (!data.ok) {
            throw new Error(data.messaggio || 'Operazione non riuscita');
        }

        mostraPercorsi(idPiazzola, tipoElemento);

    })
    .catch(error => {

        console.error('Errore aggiunta elemento:', error);

        alert('Errore durante l\'aggiunta dell\'elemento.');
    });
}





function rimuoviElemento(idPercorso, idElementi) {
    
    console.log(idElementi);


    const modalElement = document.getElementById('modalPercorsi');

    const idPiazzola = modalElement.dataset.idPiazzola;
    const tipoElemento = modalElement.dataset.tipoElemento;

    const payload = {
        azione: 'rimuovi',
        id_percorso: idPercorso,
        id_elementi: idElementi
    };

    fetch('./backoffice/elemento_percorso.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
        //return response.text();
    })
    .then(data => {

        if (!data.ok) {
            throw new Error(data.messaggio || 'Operazione non riuscita');
        }

        mostraPercorsi(idPiazzola, tipoElemento);

    })
    .catch(error => {

        console.error('Errore rimozione elemento:', error);

        alert('Errore durante la rimozione dell\'elemento.');
    });
}