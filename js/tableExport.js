/**
 * Modulo per gestire export Excel (totale e filtrato) di una tabella Bootstrap
 * Richiede:
 *  - jQuery
 *  - bootstrap-table
 *  - SheetJS (XLSX)
 *
 * @param {Object} config - configurazione
 * @param {string} config.tableId - ID tabella bootstrap (senza #)
 * @param {string} config.exportAllBtn - selettore bottone export totale
 * @param {string} config.exportFilteredBtn - selettore bottone export filtrato
 * @param {string} config.baseUrl - URL base per la chiamata al server
 * @param {function} [config.extraParams] - (opzionale) funzione che ritorna un oggetto con parametri extra
 * 
 * Funzione da richiamare nella pagina web qualora oltre ai filtri sulle colonne ci fossero altri filtri (es. consuntivazione_ekovision.php)
 * $(function() {
  initTableExport({
    tableId: "ek_cons", // id della tabella
    exportAllBtn: "#export-btn", //id del bottone eexport totale
    exportFilteredBtn: "#export-btn-filtered", //id del bottone esxport filtrato
    baseUrl: "./tables/report_consuntivazione_ekovision.php", // url per recupero dei dati
    extraParams: () => {
      const range = $('input[name="daterange"]').val().split(" - ");
      return {
        ut: $("#ut").val() == 0 ? "" : $("#ut").val(), // id elemento html parametro 1
        data_inizio: range[0].split('/').reverse().join('-'), // id elemento html parametro 2
        data_fine: range[1].split('/').reverse().join('-') // id elemento html parametro n
      };
    }
  });
});
 * 
 *Funzione da richiamare nella pagina web qualora oltre ai filtri sulle colonne
 * $(function() {
  initTableExport({
    tableId: "ek_cons",
    exportAllBtn: "#export-btn",
    exportFilteredBtn: "#export-btn-filtered",
    baseUrl: "./tables/report_generico.php"
  });
});

 */


function initTableExport(config) {
  if (!config || !config.tableId || !config.exportAllBtn || !config.exportFilteredBtn || !config.baseUrl) {
    console.error("initTableExport: configurazione mancante o incompleta", config);
    return;
  }

  const $table = $(`#${config.tableId}`);

  if ($table.length === 0) {
    console.error(`initTableExport: tabella con id '${config.tableId}' non trovata`);
    return;
  }

  // Inizializza bootstrapTable se non già inizializzato
  if (!$table.data('bootstrap.table')) {
    $table.bootstrapTable();
  }

  // Funzione per leggere i filtri dalle colonne
  function getColumnFilters() {
    const filters = {};
    const $thead = $table.find('thead');
    $thead.find('input, select').each(function () {
      const $el = $(this);
      let field = $el.closest('th').attr('data-field');
      if (!field) {
        const idx = $el.closest('th').index();
        field = $thead.find('tr:first th').eq(idx).attr('data-field');
      }
      const value = $el.val();
      if (field && value !== '' && value != null) {
        filters[field] = value;
      }
    });
    return filters;
  }

  // Recupera i parametri comuni (con extra e filtri opzionali)
  function buildParams(includeFilters = false) {
    const options = $table.bootstrapTable('getOptions');
    const params = new URLSearchParams();

    // Parametri extra (se definiti dall'utente)
    if (typeof config.extraParams === 'function') {
      try {
        const extras = config.extraParams();
        if (extras && typeof extras === 'object') {
          for (const [k, v] of Object.entries(extras)) {
            if (v !== undefined && v !== null && v !== '') {
              params.set(k, v);
            }
          }
        }
      } catch (err) {
        console.warn("initTableExport: errore in extraParams()", err);
      }
    }

    // Paginazione lato server
    params.set("limit", options.totalRows || 1000);
    params.set("offset", 0);

    // Barra di ricerca globale
    if (options.searchText) {
      params.set("search", options.searchText);
    }

    // Filtri colonne
    if (includeFilters) {
      const filters = getColumnFilters();
      const filterObj = {};
      Object.entries(filters).forEach(([k, v]) => {
        if (v !== undefined && v !== null && v !== '') {
          filterObj[k] = v;
        }
      });
      if (Object.keys(filterObj).length > 0) {
        params.set("filter", JSON.stringify(filterObj));
      }
    }

    return params;
  }

  function createExcelSheet(rows, fileName, sheetName) {
    if (!rows || rows.length === 0) {
      alert("Nessun dato da esportare.");
      return;
    }

    // Recupero nomi delle colonne
    const colNames = Object.keys(rows[0]);

    // Creo array of arrays (prima riga = intestazione)
    const aoa = [colNames, ...rows.map(row => colNames.map(h => row[h]))];

    // Creo il foglio Excel
    const ws = XLSX.utils.aoa_to_sheet(aoa);

    // Applico i filtri sulla prima riga
    const range = XLSX.utils.decode_range(ws["!ref"]);
    ws["!autofilter"] = { ref: XLSX.utils.encode_range(range.s, { r: range.s.r, c: range.e.c }) };

    // Calcolo larghezza automatica delle colonne
    ws["!cols"] = colNames.map(h => {
      const maxLen = Math.max(
        h.length,
        ...rows.map(row => row[h] ? row[h].toString().length : 0)
      );
      return { wch: maxLen + 1 }; // + margine
    });

    // Creo la cartella Excel e aggiungo il foglio
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, sheetName);

    // Salvo il file
    XLSX.writeFile(wb, fileName);
  }

  // Export dati totale
  $(config.exportAllBtn).off("click").on("click", async () => {
    const url = `${config.baseUrl}?${buildParams(false).toString()}`;
    console.log("URL fetch export totale:", url);

    try {
      const res = await fetch(url);
      const data = await res.json();
      createExcelSheet(data.rows || data, "export_consuntivazione.xlsx", "Dati");
    } catch (err) {
      console.error("Errore fetch export totale:", err);
      alert("Errore durante export totale.");
    }
  });

  // Export dati filtrati applicando filtri e autodimensionamento colonne
  $(config.exportFilteredBtn).off("click").on("click", async () => {
    const url = `${config.baseUrl}?${buildParams(true).toString()}`;
    console.log("URL fetch export filtrato:", url);

    try {
      const res = await fetch(url);
      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (err) {
        console.error("Risposta non JSON valida:", text);
        alert("Errore: risposta server non valida.");
        return;
      }
      createExcelSheet(data.rows || data, "export_consuntivazione_filtrato.xlsx", "Dati");
    } catch (err) {
      console.error("Errore fetch export filtrato:", err);
      alert("Errore durante export filtrato.");
    }
  });
}
