// per il datarange 
$(function () {
    $('#daterange').daterangepicker({
        opens: 'left',
        maxSpan: {
            days: 548
        },
        showISOWeekNumbers: true,
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Applica',
            cancelLabel: 'Annulla',
            fromLabel: 'Da',
            toLabel: 'A',
            customRangeLabel: 'Personalizzato',
            weekLabel: 'S',
            daysOfWeek: [ 'Lu', 'Ma', 'Me', 'Gi', 'Ve', 'Sa', 'Do'],
            monthNames: [
                'Gennaio', 'Febbraio', 'Marzo', 'Aprile',
                'Maggio', 'Giugno', 'Luglio', 'Agosto',
                'Settembre', 'Ottobre', 'Novembre', 'Dicembre'
            ],
            firstDay: 1
        }
    });
});



// per caricare i filtri della dashboard
async function caricaFiltri() {

    const url = './ajax/statistiche_cons_raccolta_filtri.php';

    try {

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`Errore HTTP ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || 'Errore nel caricamento dei filtri');
        }

        popolaSelect('comune', result.filtri.comuni, 'Tutti');
        popolaSelect('zona', result.filtri.zone, 'Tutte');
        popolaSelect('ut', result.filtri.uts, 'Tutte');

    } catch (error) {

        console.error('Errore caricamento filtri:', error);

        alert('Impossibile caricare i filtri della dashboard.');

    }
}


function popolaSelect(id, valori, testoDefault) {

    const select = document.getElementById(id);

    if (!select) return;

    // Mantiene la prima opzione
    select.replaceChildren(
        new Option(testoDefault, '')
    );

    valori.forEach(valore => {

        select.add(
            new Option(valore, valore)
        );

    });

}


// Caricamento iniziale

document.addEventListener('DOMContentLoaded', async () => {

    // Inizializzazione ECharts
    inizializzaGrafici();

    // Popolamento filtri
    await caricaFiltri();

    // Pulsante aggiornamento
    document.getElementById('btn_aggiorna')
        .addEventListener('click', aggiornaDashboard);

    // Ridimensionamento
    window.addEventListener('resize', () => {
        chartDisserviziMese.resize();
        chartCausaliMese.resize();
        chartDow.resize();
        chartHeatmap.resize();
    });

});





// =====================================================
// ISTANZE ECHARTS
// =====================================================

let chartDisserviziMese = null;
let chartCausaliMese = null;
let chartDow = null;
let chartHeatmap = null;

// per conservare poi i dati per l'excel export
let datiDashboard = null;
let filtriDashboard = null;



// =====================================================
// INIZIALIZZAZIONE
// =====================================================

function inizializzaGrafici() {

    chartDisserviziMese = echarts.init(
        document.getElementById('chart_disservizi_mese')
    );

    chartCausaliMese = echarts.init(
        document.getElementById('chart_causali_mese')
    );

    chartDow = echarts.init(
        document.getElementById('chart_dow')
    );

    chartHeatmap = echarts.init(
        document.getElementById('chart_heatmap')
    );

}


// =====================================================
// RESIZE
// =====================================================

window.addEventListener('resize', () => {

    chartDisserviziMese?.resize();
    chartCausaliMese?.resize();
    chartDow?.resize();
    chartHeatmap?.resize();

});


/* Aggiornamento KPI */

function aggiornaKPI(kpi) {

    const numero = valore =>
        Number(valore ?? 0).toLocaleString('it-IT');

    document.getElementById('kpi_servizi').textContent =
        numero(kpi.servizi);

    document.getElementById('kpi_disservizi').textContent =
        numero(kpi.disservizi);

    document.getElementById('kpi_perc_disservizi').textContent =
        kpi.perc_disservizi !== null
            ? `${Number(kpi.perc_disservizi).toLocaleString('it-IT', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}%`
            : '-';

    document.getElementById('kpi_causali').textContent =
        numero(kpi.causali);

}




function etichettaMese(anno, mese) {
    const nomiMesi = [
        'Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu',
        'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'
    ];

    return `${nomiMesi[Number(mese) - 1]} ${anno}`;
}




function aggiornaGraficoDisserviziMese(dati) {

    const mesi = dati.map(r =>
        etichettaMese(r.anno, r.mese)
    );

    const numeroDisservizi = dati.map(r =>
        Number(r.non_fatti)
    );

    const percentuali = dati.map(r =>
        Number(r.perc_non_fatti)
    );

    chartDisserviziMese.setOption({

        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross'
            },
            formatter: function (params) {

                const indice = params[0].dataIndex;

                return `
                    <strong>${mesi[indice]}</strong><br>
                    Disservizi: ${numeroDisservizi[indice].toLocaleString('it-IT')}<br>
                    Incidenza: ${percentuali[indice].toFixed(2)}%
                `;
            }
        },

        legend: {
            data: ['Numero disservizi', 'Incidenza %']
        },

        grid: {
            left: 65,
            right: 65,
            bottom: 45,
            top: 55
        },

        xAxis: {
            type: 'category',
            data: mesi,
            axisLabel: {
                rotate: 30
            }
        },

        yAxis: [
            {
                type: 'value',
                name: 'Disservizi',
                min: 0
            },
            {
                type: 'value',
                name: 'Incidenza %',
                min: 0,
                axisLabel: {
                    formatter: '{value}%'
                }
            }
        ],

        series: [
            {
                name: 'Numero disservizi',
                type: 'bar',
                data: numeroDisservizi,
                yAxisIndex: 0,
                barMaxWidth: 35
            },
            {
                name: 'Incidenza %',
                type: 'line',
                data: percentuali,
                yAxisIndex: 1,
                smooth: true,
                symbol: 'circle',
                symbolSize: 8
            }
        ]

    }, true);
}



function aggiornaGraficoCausaliMese(dati) {

    const mesi = [...new Set(
        dati.map(r => `${r.anno}-${String(r.mese).padStart(2, '0')}`)
    )].sort();

    const causali = [...new Set(
        dati.map(r => r.causale || 'Senza causale')
    )].sort();

    const etichetteMesi = mesi.map(m => {
        const [anno, mese] = m.split('-');
        return etichettaMese(anno, mese);
    });

    const matrice = [];

    dati.forEach(r => {

        const mese = `${r.anno}-${String(r.mese).padStart(2, '0')}`;
        const causale = r.causale || 'Senza causale';

        const x = mesi.indexOf(mese);
        const y = causali.indexOf(causale);

        matrice.push([
            x,
            y,
            Number(r.perc_causale),
            Number(r.numero_eventi ?? 0)
        ]);
    });

    chartCausaliMese.setOption({

        tooltip: {
            position: 'top',
            formatter: function (params) {

                const mese = etichetteMesi[params.value[0]];
                const causale = causali[params.value[1]];
                const percentuale = params.value[2];
                const numero = params.value[3];

                return `
                    <strong>${causale}</strong><br>
                    ${mese}<br>
                    Disservizi: ${numero.toLocaleString('it-IT')}<br>
                    Incidenza: ${percentuale.toFixed(2)}%
                `;
            }
        },

        grid: {
            left: 180,
            right: 40,
            top: 20,
            bottom: 90
        },

        xAxis: {
            type: 'category',
            data: etichetteMesi,
            splitArea: {
                show: true
            },
            axisLabel: {
                rotate: 35
            }
        },

        yAxis: {
            type: 'category',
            data: causali,
            splitArea: {
                show: true
            }
        },

        visualMap: {
            min: 0,
            max: 100,
            calculable: true,
            orient: 'horizontal',
            left: 'center',
            bottom: 10,
            text: ['100%', '0%']
        },

        series: [{
            name: 'Incidenza causali',
            type: 'heatmap',
            data: matrice,
            label: {
                show: false
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowColor: 'rgba(0, 0, 0, 0.4)'
                }
            }
        }]

    }, true);
}



function aggiornaGraficoDow(dati) {

    const righe = [...dati].sort((a, b) =>
        Number(a.dow) - Number(b.dow)
    );

    chartDow.setOption({

        tooltip: {
            trigger: 'axis',
            valueFormatter: v => `${v}%`
        },

        grid: {
            left: 55,
            right: 30,
            top: 35,
            bottom: 40
        },

        xAxis: {
            type: 'category',
            data: righe.map(r => r.giorno_settimana)
        },

        yAxis: {
            type: 'value',
            name: '%',
            axisLabel: {
                formatter: '{value}%'
            }
        },

        series: [{
            name: 'Disservizi',
            type: 'bar',
            data: righe.map(r =>
                Number(r.perc_non_fatti ?? 0)
            ),
            barMaxWidth: 55,
            label: {
                show: true,
                position: 'top',
                formatter: '{c}%'
            }
        }]

    }, true);

}


/*Grafico 4 – Heatmap causali per giorno della settimana
Qui sfruttiamo una delle funzionalità più interessanti di ECharts.
- Asse X: giorni della settimana.
- Asse Y: causali.
- Colore: percentuale della causale sul totale dei disservizi del rispettivo giorno.
*/


function aggiornaGraficoHeatmap(dati) {

    const giorni = [...new Map(
        [...dati]
            .sort((a, b) => Number(a.dow) - Number(b.dow))
            .map(r => [
                r.giorno_settimana,
                r.giorno_settimana
            ])
    ).values()];

    const causali = [...new Set(
        dati.map(r => r.causale ?? 'Senza causale')
    )].sort((a, b) => a.localeCompare(b, 'it'));

    const valori = dati.map(r => {

        const x = giorni.indexOf(r.giorno_settimana);

        const y = causali.indexOf(
            r.causale ?? 'Senza causale'
        );

        return [
            x,
            y,
            Number(r.perc_causale ?? 0)
        ];

    });

    chartHeatmap.setOption({

        tooltip: {
            position: 'top',

            formatter: params => {

                const [x, y, valore] = params.value;

                return `
                    <strong>${causali[y]}</strong><br>
                    ${giorni[x]}<br>
                    Incidenza: ${valore}%
                `;

            }
        },

        grid: {
            left: 150,
            right: 50,
            top: 30,
            bottom: 65
        },

        xAxis: {
            type: 'category',
            data: giorni,
            splitArea: {
                show: true
            }
        },

        yAxis: {
            type: 'category',
            data: causali,
            splitArea: {
                show: true
            }
        },

        visualMap: {
            min: 0,
            max: 100,
            calculable: true,
            orient: 'horizontal',
            left: 'center',
            bottom: 0,
            formatter: value => `${value}%`
        },

        series: [{
            name: 'Incidenza causali',
            type: 'heatmap',
            data: valori,

            label: {
                show: true,
                formatter: p => `${p.value[2]}%`
            },

            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowColor: 'rgba(0,0,0,0.4)'
                }
            }
        }]

    }, true);

}





async function aggiornaDashboard() {

    const form = document.getElementById('frm_filtri');

    const params = new URLSearchParams(new FormData(form));

    const periodo = $('#daterange').val().split(' - ');

    const [giornoDa, meseDa, annoDa] = periodo[0].split('/');
    const [giornoA, meseA, annoA] = periodo[1].split('/');

    params.set('data_da', `${annoDa}-${meseDa}-${giornoDa}`);
    params.set('data_a', `${annoA}-${meseA}-${giornoA}`);

    // Checkbox
    params.set(
        'solo_arera',
        document.getElementById('solo_arera').checked
    );

    params.set(
        'escludi_non_previsti',
        document.getElementById('escludi_non_previsti').checked
    );

    params.set(
        'escludi_causali_non_previste',
        document.getElementById('escludi_causali_non_previste').checked
    );

    if (!params.get('data_da') || !params.get('data_a')) {
        alert('Seleziona entrambe le date.');
        return;
    }

    const btn = document.getElementById('btn_aggiorna');

    btn.disabled = true;

    try {

        const response = await fetch(
            './ajax/statistiche_cons_raccolta.php?' + params.toString()
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.error || 'Errore durante il caricamento'
            );
        }
        
        // Conserva i dati per l'esportazione Excel
        datiDashboard = result;

        // Fotografia dei filtri utilizzati per questa estrazione
        filtriDashboard = Object.fromEntries(params.entries());

        // Abilita il pulsante di esportazione Excel
        document.getElementById('btn_excel').disabled = false;

        // KPI
        aggiornaKPI(result.kpi);

        // GRAFICI
        aggiornaGraficoDisserviziMese(
            result.grafici.disservizi_mese
        );

        aggiornaGraficoCausaliMese(
            result.grafici.causali_mese
        );

        aggiornaGraficoDow(
            result.grafici.dow
        );

        aggiornaGraficoHeatmap(
            result.grafici.heatmap
        );

    } catch (error) {

        console.error(error);

        alert('Errore aggiornamento dashboard: ' + error.message);

    } finally {

        btn.disabled = false;

    }

}


// con questa funzione recuperiamo i filtri selezionati dall'utente per inserirli nel file Excel esportato
function recuperaFiltriExport() {

    if (!filtriDashboard) {
        return [];
    }

    const valori = {
        data_da: 'Data iniziale',
        data_a: 'Data finale',
        comune: 'Comune',
        zona: 'Zona',
        ut: 'UT'
    };

    const risultati = [];

    // Filtri a selezione
    Object.entries(valori).forEach(([chiave, etichetta]) => {

        const valore = filtriDashboard[chiave];

        risultati.push({
            Indicatore: etichetta,
            Valore: valore || 'Tutti'
        });

    });

    // Checkbox
    const checkbox = {
        solo_arera: 'Solo ARERA',
        escludi_non_previsti: 'Escludi non previsti',
        escludi_causali_non_previste: 'Escludi causali non previste'
    };

    Object.entries(checkbox).forEach(([chiave, etichetta]) => {

        risultati.push({
            Indicatore: etichetta,
            Valore: filtriDashboard[chiave] === 'true' ? 'Sì' : 'No'
        });

    });

    return risultati;
}


function esportaExcel() {

    if (!datiDashboard) {
        alert('Non ci sono dati da esportare. Aggiorna prima la dashboard.');
        return;
    }

    const wb = XLSX.utils.book_new();

    // =========================
    // Funzione per creare fogli
    // =========================
    function aggiungiFoglio(nome, dati, larghezze) {

        const ws = XLSX.utils.json_to_sheet(dati);

        if (larghezze) {
            ws['!cols'] = larghezze.map(w => ({ wch: w }));
        }

        // Filtro automatico sulle intestazioni
        if (dati.length > 0) {
            ws['!autofilter'] = {
                ref: ws['!ref']
            };
        }

        XLSX.utils.book_append_sheet(wb, ws, nome);
    }

    // =========================
    // 1. KPI e filtri
    // =========================
    const kpi = datiDashboard.kpi;

    const datiKPI = [
        { Indicatore: 'Servizi', Valore: Number(kpi.servizi) },
        { Indicatore: 'Disservizi', Valore: Number(kpi.disservizi) },
        { Indicatore: 'Percentuale disservizi', Valore: Number(kpi.perc_disservizi) },
        { Indicatore: 'Numero causali', Valore: Number(kpi.causali) },

        // Riga separatrice
        {},

        { Indicatore: 'FILTRI APPLICATI', Valore: '' },

        ...recuperaFiltriExport()
    ];

    aggiungiFoglio('KPI', datiKPI, [38, 30]);

    // =========================
    // 2. Disservizi mensili
    // =========================
    const disservizi = datiDashboard.grafici.disservizi_mese.map(r => ({
        Anno: Number(r.anno),
        Mese: Number(r.mese),
        Totale_servizi: Number(r.totale),
        Disservizi: Number(r.non_fatti),
        Percentuale_disservizi: Number(r.perc_non_fatti)
    }));

    aggiungiFoglio(
        'Disservizi mensili',
        disservizi,
        [12, 12, 20, 16, 25]
    );

    // =========================
    // 3. Causali mensili
    // =========================
    const causali = datiDashboard.grafici.causali_mese.map(r => ({
        Anno: Number(r.anno),
        Mese: Number(r.mese),
        Causale: r.causale || 'Senza causale',
        Percentuale: Number(r.perc_causale)
    }));

    aggiungiFoglio(
        'Causali mensili',
        causali,
        [12, 12, 55, 18]
    );

    // =========================
    // 4. Giorni settimana
    // =========================
    const dow = datiDashboard.grafici.dow.map(r => ({
        Giorno: r.giorno_settimana,
        Numero_giorno: Number(r.dow),
        Totale_servizi: Number(r.totale),
        Disservizi: Number(r.non_fatti),
        Percentuale_disservizi: Number(r.perc_non_fatti)
    }));

    aggiungiFoglio(
        'Giorni settimana',
        dow,
        [20, 16, 20, 16, 25]
    );

    // =========================
    // 5. Heatmap
    // =========================
    const heatmap = datiDashboard.grafici.heatmap.map(r => ({
        Giorno: r.giorno_settimana,
        Numero_giorno: Number(r.dow),
        Causale: r.causale || 'Senza causale',
        Percentuale: Number(r.perc_causale)
    }));

    aggiungiFoglio(
        'Heatmap',
        heatmap,
        [20, 16, 55, 18]
    );

    // =========================
    // Download
    // =========================
    XLSX.writeFile(
        wb,
        'statistiche_raccolta.xlsx'
    );
}