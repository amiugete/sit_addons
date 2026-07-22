function apriPannelloRisultati(){

    risultati.classList.remove("chiuso");

    setTimeout(() => {

        map.invalidateSize();

    },300);

}


/* Funzione asincrona per vedere il dettaglio delle aste

La funzione deve fare solo quattro cose:

1. mostrare un indicatore di caricamento;
2. chiamare il PHP;
3. ricevere il JSON;
4. passare i dati alla funzione che costruisce la card

*/


async function caricaDettaglioAsta(idAsta) {

    // Mostra un piccolo spinner nella card
    $("#cardAsta").removeClass("d-none");
    $("#cardAstaBody").html(`
        <div class="text-center p-3">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span class="ms-2">Caricamento...</span>
        </div>
    `);

    try {

        const response = await fetch(
            `layers/asta_info.php?id_asta=${encodeURIComponent(idAsta)}`
        );

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        mostraDettaglioAsta(data.asta);
        mostraElemPiazzole(data.elem_piazzole);
    }
    catch (err) {

        console.error(err);

        $("#cardAstaBody").html(`
            <div class="alert alert-danger mb-0">
                Errore durante il caricamento delle informazioni dell'asta.
            </div>
        `);

    }

}


function riga(label, valore) {


    if (valore) {
        return `
            <dt class="col-5">${label}</dt>
            <dd class="col-7">${valore ?? "-"}</dd>
        `;
    }
    else {
        return "";
    }
}


// Funzione per mostrare il dettaglio dell'asta nella card

function mostraDettaglioAsta(asta) {

    $("#cardAsta").removeClass("d-none");

    $("#cardAstaHeader").text(`Asta ${asta.id_asta}`);

    $("#cardAstaBody").html(`
        <dl class="row mb-0">

            ${riga("Via", asta.via)}
            ${riga("Comune", asta.comune)}
            ${riga("Municipio", asta.municipio)}
            ${riga("Quartiere", asta.quartiere)}
            ${riga("UT", asta.ut)}
            ${riga("Lunghezza", asta.lung + " m")}
        </dl>

        <div class="accordion" id="accordionAsta">

        </div>
    `);

}


// funzione generica per creare accordion Item con titolo e body

function creaAccordionItem(id, titolo, contenuto, badge = "", aperto = false) {

    return `

        <div class="accordion-item">

            <h2 class="accordion-header">

                <button
                    class="accordion-button ${aperto ? "" : "collapsed"}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#${id}">

                    ${titolo}

                    ${badge !== ""
                        ? `<span class="badge bg-secondary ms-2">${badge}</span>`
                        : ""}

                </button>

            </h2>

            <div
                id="${id}"
                class="accordion-collapse collapse ${aperto ? "show" : ""}"
                data-bs-parent="#accordionAsta">

                <div class="accordion-body">

                    ${contenuto}

                </div>

            </div>

        </div>

    `;

}




// 3 funzioni che poi userò






function mostraElemPiazzole(elementi) {

    if (elementi.length === 0)
        return;

    const piazzole = {};

    elementi.forEach(e => {

        if (!piazzole[e.id_piazzola]) {

            piazzole[e.id_piazzola] = {
                info: e,
                elementi: []
            };

        }

        piazzole[e.id_piazzola].elementi.push(e);

    });

    let contenuto = "";

    Object.values(piazzole).forEach(p => {

        contenuto += creaAccordionPiazzola(p);

    });

    $("#accordionAsta").append(

        creaAccordionItem(
            "collapseElemPiazzole",
            "Elementi in piazzola",
            contenuto,
            elementi.length
        )

    );

}


function creaAccordionPiazzola(piazzola) {

    const numPrivati = piazzola.elementi.filter(e => e.privato == 1).length;

    const idCollapse = "collapsePiazzola_" + piazzola.info.id_piazzola;

    let html = `

        <div class="accordion-item">

            <h2 class="accordion-header">

                <button class="accordion-button collapsed py-2"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#${idCollapse}">

                    <strong>Piazzola ${piazzola.info.id_piazzola}</strong>

                    <span class="badge bg-secondary ms-2">
                        ${piazzola.elementi.length}
                    </span>

                </button>

            </h2>

            <div id="${idCollapse}"
                 class="accordion-collapse collapse">

                <div class="accordion-body">

                    <div class="small text-muted mb-3">

                        ${piazzola.info.via}
                        ${piazzola.info.numero_civico ?? ""}

                        ${piazzola.info.riferimento
                            ? "<br>"+piazzola.info.riferimento
                            : ""}

                        <br>

                        ${piazzola.elementi.length} elementi

                        (${numPrivati} privati)

                    </div>

    `;

    //---------------------------------------------------
    // Raggruppo per rifiuto
    //---------------------------------------------------

    const rifiuti = {};

    piazzola.elementi.forEach(e => {

        if (!rifiuti[e.rifiuto]) {

            rifiuti[e.rifiuto] = {
                info: e,
                elementi: []
            };

        }

        rifiuti[e.rifiuto].elementi.push(e);

    });

    Object.values(rifiuti).forEach(r => {

        html += creaBloccoRifiuto(r);

    });

    html += `

                </div>

            </div>

        </div>

    `;

    return html;

}


function creaBloccoRifiuto(rifiuto) {

    let html = `

        <div class="mb-3">

            <div class="fw-bold mb-2">

                <i class="fa-solid ${rifiuto.info.fa_icon}"
                   style="color:${rifiuto.info.colore}"></i>

                ${rifiuto.info.rifiuto}

                <span class="badge"
                    style="
                        background-color:${rifiuto.info.colore};
                        color:white;
                ">

                    ${rifiuto.elementi.length}

                </span>

            </div>

    `;

    //---------------------------------------------------
    // Raggruppo per tipo elemento
    //---------------------------------------------------

    const tipi = {};

    rifiuto.elementi.forEach(e => {

        if (!tipi[e.tipo_elemento]) {

            tipi[e.tipo_elemento] = [];

        }

        tipi[e.tipo_elemento].push(e);

    });

    Object.entries(tipi).forEach(([tipo, lista]) => {

        html += creaBloccoTipoElemento(tipo, lista, rifiuto);

    });

    html += `

        </div>

    `;

    return html;

}


function creaBloccoTipoElemento(tipoElemento, elementi, rifiuto) {

    let html = `

        <div class="ms-4 mb-3">

            <div class="d-flex justify-content-between">

                <span>${tipoElemento}</span>

                <span class="badge"
                    style=" font-size: 0.8rem;
                            background-color:${rifiuto.info.colore};
                            color:white;
                ">

                    ${elementi.length}

                </span>

            </div>


    `;

    

    html += `

           

        </div>

    `;

    return html;

}



async function caricaDettaglioPiazzola(idPiazzola) {

    $("#cardPiazzola").removeClass("d-none");

    $("#cardPiazzolaBody").html(`
        <div class="text-center p-3">
            <div class="spinner-border spinner-border-sm"></div>
            <span class="ms-2">Caricamento...</span>
        </div>
    `);

    try {

        const response = await fetch(
            `layers/piazzola_info.php?id=${encodeURIComponent(idPiazzola)}`
        );

        if (!response.ok)
            throw new Error(`HTTP ${response.status}`);

        const data = await response.json();

        mostraDettaglioPiazzola(data, idPiazzola);

    }
    catch(err){

        console.error(err);

        $("#cardPiazzolaBody").html(`
            <div class="alert alert-danger mb-0">
                Errore durante il caricamento della piazzola.
            </div>
        `);

    }

}

function mostraDettaglioPiazzola(piazzola, idPiazzola) {

    $("#cardPiazzola").removeClass("d-none");

    const iconaPap =
        piazzola.is_pap == "1"
            ? '<i class="fa-solid fa-user ms-2"></i>'
            : "";

    $("#cardPiazzolaHeader").html(`
        Piazzola ${idPiazzola}
        ${iconaPap}
    `);

    let html = `

        <div class="mb-2">

            <div class="fw-semibold">
                ${piazzola.via}
                ${piazzola.numero_civico ?? ""}
            </div>

            <div class="small text-muted">

                ${piazzola.riferimento ?? ""}

                ${
                    piazzola.note
                        ? `<br>${piazzola.note}`
                        : ""
                }

            </div>

        </div>

    `;

    html += '<div class="d-flex flex-column gap-1">';

    piazzola.rifiuti
        .sort((a,b)=>a.ordinamento-b.ordinamento)
        .forEach(r=>{

            html += `

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <i class="fa-solid fa-circle me-2"
                           style="color:${r.colore}"></i>

                        ${r.tipo_elem}

                    </div>

                    <span class="badge"
                          style="
                            background:${r.colore};
                            color:white;
                            min-width:32px;
                          ">

                        ${r.num}

                    </span>

                </div>

            `;

        });

    html += "</div>";

    $("#cardPiazzolaBody").html(html);

}