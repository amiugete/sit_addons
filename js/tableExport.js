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

  var $table = $(`#${config.tableId}`);

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
  const params = new URLSearchParams();

  // Parametri extra forniti dall'utente
  if (typeof config.extraParams === "function") {
    const extras = config.extraParams() || {};
    Object.entries(extras).forEach(([k, v]) => {
      if (v !== undefined && v !== null && v !== "") {
        params.set(k, v);
      }
    });
  }

  if (includeFilters) {
    const options = $table.bootstrapTable("getOptions");

    // Paginazione lato server
    params.set("limit", options.totalRows || 1000);
    params.set("offset", 0);

    // Barra di ricerca globale
    if (options.searchText) {
      params.set("search", options.searchText);
    }

    // Filtri colonne
    const filters = getColumnFilters();
    if (Object.keys(filters).length > 0) {
      params.set("filter", JSON.stringify(filters));
    }
  } else {
    // Export totale: ignoriamo tutto
    params.set("limit", 1000000); // un numero molto grande per prendere tutti i record
    params.set("offset", 0);
    // Non settiamo searchText né filtri
  }

  return params;
}


/*function createExcelSheet(rows, fileName, sheetName) {
  if (!rows || rows.length === 0) {
    alert("Nessun dato da esportare.");
    return;
  }

  const colNames = Object.keys(rows[0]);

  function detectCellType(val) {
    if (val === null || val === undefined || val === "") return null;

    // Numeri
    if (typeof val === "number" || (!isNaN(val) && val !== "")) {
      const num = Number(val);
      if (Number.isInteger(num)) {
        return { v: num, t: "n", z: "0" }; // intero
      } else {
        return { v: num, t: "n", z: "0.00" }; // decimale
      }
    }

    // Boolean
    if (typeof val === "boolean") return { v: val, t: "b" };

    // Solo ora (es. "14:35" o "14:35:22")
    if (typeof val === "string" && /^([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/.test(val)) {
      const parts = val.split(":").map(Number);
      const d = new Date();
      d.setHours(parts[0], parts[1], parts[2] || 0, 0);
      return { v: d, t: "d", z: "hh:mm:ss" };
    }

    // Solo data (es. "2025-10-03")
    if (typeof val === "string" && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
      const d = new Date(val);
      d.setHours(0, 0, 0, 0); // azzera orario
      return { v: d, t: "d", z: "dd/mm/yyyy" };
    }

    // Oggetto Date
    if (val instanceof Date && !isNaN(val)) {
      const hasTime = val.getHours() + val.getMinutes() + val.getSeconds() > 0;
      if (!hasTime) val.setHours(0, 0, 0, 0); // azzera se solo data
      return { v: val, t: "d", z: hasTime ? "dd/mm/yyyy hh:mm:ss" : "dd/mm/yyyy" };
    }

    // Stringa parsabile come data completa
    if (typeof val === "string" && !isNaN(Date.parse(val))) {
      const d = new Date(val);
      const hasTime = d.getHours() + d.getMinutes() + d.getSeconds() > 0;
      if (!hasTime) d.setHours(0, 0, 0, 0);
      return { v: d, t: "d", z: hasTime ? "dd/mm/yyyy hh:mm:ss" : "dd/mm/yyyy" };
    }

    // Stringhe generiche
    return { v: val.toString(), t: "s" };
  }

  // Header
  const aoa = [colNames];
  rows.forEach(row => {
    const r = colNames.map(h => detectCellType(row[h]));
    aoa.push(r);
  });

  const ws = XLSX.utils.aoa_to_sheet(aoa);

  // Autofilter
  const range = XLSX.utils.decode_range(ws['!ref']);
  ws['!autofilter'] = { ref: XLSX.utils.encode_range(range.s, { r: range.s.r, c: range.e.c }) };

  // Larghezza colonne
  ws['!cols'] = colNames.map(h => {
    const maxLen = Math.max(h.length, ...rows.map(r => r[h] ? r[h].toString().length : 0));
    return { wch: maxLen + 1 };
  });

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, sheetName);

  XLSX.writeFile(wb, fileName);
}*/

function createExcelSheet(rows, fileName, sheetName) {
  if (!rows || rows.length === 0) {
    alert("Nessun dato da esportare.");
    return;
  }

  const colNames = Object.keys(rows[0]);

function detectCellType(val) {
  if (val === null || val === undefined || val === "") return null;

  // 🔹 STRINGA numerica con zero iniziale → STRINGA (es. "00123")
  if (typeof val === "string" && /^\d+$/.test(val) && val.length > 1 && val.startsWith("0")) {
    return { v: val, t: "s" };
  }

  // 🔹 NUMERI reali
  if (typeof val === "number" || (typeof val === "string" && !isNaN(val) && val.trim() !== "")) {
    const num = Number(val);
    if (Number.isInteger(num)) return { v: num, t: "n", z: "0" };
    return { v: num, t: "n", z: "0.00" };
  }

  // 🔹 BOOLEAN
  if (typeof val === "boolean") return { v: val, t: "b" };

  // 🔹 SOLO ORA "HH:mm" o "HH:mm:ss"
  if (typeof val === "string" && /^([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/.test(val)) {
    const parts = val.split(":").map(Number);
    const d = new Date(1899, 11, 30, parts[0], parts[1], parts[2] || 0); // Excel base date
    const format = parts.length === 3 && parts[2] > 0 ? "hh:mm:ss" : "hh:mm";
    return { v: d, t: "d", z: format };
  }

  // 🔹 SOLO DATA ISO "YYYY-MM-DD"
  if (typeof val === "string" && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
    const d = new Date(val + "T00:00:00");
    return { v: d, t: "d", z: "dd/mm/yyyy" };
  }

  // 🔹 SOLO DATA Italiana "DD/MM/YYYY"
  if (typeof val === "string" && /^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
    const [dd, mm, yyyy] = val.split("/").map(Number);
    const d = new Date(yyyy, mm - 1, dd, 0, 0, 0);
    return { v: d, t: "d", z: "dd/mm/yyyy" };
  }

  // 🔹 DATA + ORA ISO "YYYY-MM-DDTHH:mm" o "YYYY-MM-DDTHH:mm:ss"
  if (typeof val === "string" && /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/.test(val)) {
    const d = new Date(val);
    const s = d.getSeconds();
    const format = s > 0 ? "dd/mm/yyyy hh:mm:ss" : "dd/mm/yyyy hh:mm";
    return { v: d, t: "d", z: format };
  }

  // 🔹 Oggetto Date
  if (val instanceof Date && !isNaN(val)) {
    const h = val.getHours(), m = val.getMinutes(), s = val.getSeconds();
    if (h + m + s === 0) {
      return { v: val, t: "d", z: "dd/mm/yyyy" };
    } else {
      const format = s > 0 ? "dd/mm/yyyy hh:mm:ss" : "dd/mm/yyyy hh:mm";
      return { v: val, t: "d", z: format };
    }
  }

  // 🔹 Tutto il resto → STRINGA
  return { v: val.toString(), t: "s" };
}


  // Header
  const aoa = [colNames];

  // Righe
  rows.forEach(row => {
    const r = colNames.map(h => detectCellType(row[h]));
    aoa.push(r);
  });

  const ws = XLSX.utils.aoa_to_sheet(aoa);

  // Autofilter
  const range = XLSX.utils.decode_range(ws['!ref']);
  ws['!autofilter'] = { ref: XLSX.utils.encode_range(range.s, { r: range.s.r, c: range.e.c }) };

  // Larghezza colonne
  ws['!cols'] = colNames.map(h => {
    const maxLen = Math.max(h.length, ...rows.map(r => r[h] ? r[h].toString().length : 0));
    return { wch: maxLen + 1 };
  });

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, sheetName);

  XLSX.writeFile(wb, fileName);
}


  // Export dati totale
  $(config.exportAllBtn).off("click").on("click", async () => {
    const url = `${config.baseUrl}?${buildParams(false).toString()}`;
    console.log("URL fetch export totale:", url);

    try {
      const res = await fetch(url);
      const data = await res.json();
      createExcelSheet(data.rows || data, "export_completo.xlsx", "Dati");
    } catch (err) {
      console.error("Errore fetch export totale:", err);
      alert("Errore durante export totale.");
    }
  });

  // Export dati filtrati applicando filtri e autodimensionamento colonne
  $(document).off("click", config.exportFilteredBtn).on("click", config.exportFilteredBtn, async () => {
    const url = `${config.baseUrl}?${buildParams(true).toString()}`;
    console.log("URL fetch export filtrato:", url);

    try {
      const res = await fetch(url);
      //const text = await res.text();
      let data;
      try {
        data = await res.json();
      } catch {
        const text = await res.text();
        console.error('Risposta non JSON valida:', text);
        alert('Errore: risposta server non valida.');
        return;
      }
      createExcelSheet(data.rows || data, "export_filtrato.xlsx", "Dati");
    } catch (err) {
      console.error("Errore fetch export filtrato:", err);
      alert("Errore durante export filtrato.");
    }
  });
}