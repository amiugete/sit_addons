<?php
//session_set_cookie_params($lifetime);
session_start();

    
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Gestione servizi</title>
<?php 
require_once('./req.php');

the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}
?> 
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>




</head>

<body>

<?php require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ((int)$id_role_SIT = 0) {
  redirect('no_permessi.php');
  //exit;
}
?>

<?php 

require_once("./filter_tables/filter_consuntivazione_ekovision.php");

?>

<div class="container">
  <h4 style="margin-bottom: 4%;">Report consuntivazione Ekovision</h4>

<div class="row justify-content-start">
<div class="col-4">
<?php 

require_once("./last_update_ekovision.php");

?>
  </div>
  <div class="col-4">
  <!--FILTRO PER UT sulla base delle UT del mio profilo SIT-->
<div class="rfix">

<script>
  function utScelta(val) {
    document.getElementById('open_ut').submit();
  }
</script>

 


<form class="row" name="open_ut" method="post" id="open_ut" autocomplete="off" action="consuntivazione_ekovision.php" >

<?php //echo $username; 
//echo $_POST['ut'].'<br>';?>

<div class="form-group">

<label for="ut" >Seleziona una UT</label>
  <select class="selectpicker show-tick form-control" 
  data-live-search="true" name="ut" id="ut" onchange="utScelta(this.value);" required="">
  
  
  <?php 
  if (intval($_POST['ut'])>=0) {
    $query0='select cmu.id_uo as ut, u.id_ut, descrizione
    from topo.ut u
    join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut 
    where cmu.id_uo = $1';

    $result0 = pg_prepare($conn, "my_query0", $query0);
    $result0 = pg_execute($conn, "my_query0", array($_POST['ut']));
    
    while($r0 = pg_fetch_assoc($result0)) { 
  ?>    
          <option name="ut" value="<?php echo $r0['ut']?>"> <?php echo $r0['descrizione']?></option>
  <?php }
  pg_free_result($result0); 
  ?>
  <option name="ut" value="0">Nessuna UT</option>
  <?php 
  } else {
  ?>
    <option name="ut" value="0">Nessuna UT</option>
  
  
  <?php            
  }

  require_once('query_ut.php');

  //echo "<br>". $query1;


  $result1 = pg_prepare($conn, "my_query1", $query_ut);
  $result1 = pg_execute($conn, "my_query1", array($_SESSION['username']));

  while($r1 = pg_fetch_assoc($result1)) { 
?>    
        <option name="ut0" value="<?php echo $r1['id_uo'];?>" ><?php echo $r1['descrizione']?></option>
<?php 
  }
  pg_free_result($result1); 
?>

  </select>  
       
</div>
  </form>

  </div>

<!--FINE FILTRO PER UT sulla base delle UT del mio profilo SIT -->

  </div>

<?php 
$dt= new DateTime();
$today = new DateTime();
$last_month = $dt->modify("-1 month");
?>



<div class="form-group col-lg-4">
<label for="data_inizio" >Da  (GG/MM/AAAA) - A (GG/MM/AAAA)</label><font color="red">*</font>
    <input type="text" class="form-control" name="daterange" value="<?php echo $last_month->format('d/m/Y');?> - <?php echo $today->format('d/m/Y');?>"/>
    <small>Massimo 31 giorni </small>
</div>


<script>

$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left',
    maxSpan: {
        days: 31
    },
    showISOWeekNumbers: true,
    minDate: "<?php echo $partenza_ekovision;?>"

    
  }, function(start, end, label) {
    var data_inizio = start.format('YYYY-MM-DD') ;
    var data_fine= end.format('YYYY-MM-DD');
    console.log("A new date selection was made: " + data_inizio + ' to ' + data_fine);
    
  

    // Faccio refres della data-url
    $table.bootstrapTable('refresh', {
      url: "./tables/report_consuntivazione_ekovision.php?ut=<?php echo $_POST['ut']?>&data_inizio="+data_inizio+"&data_fine="+data_fine+""
    });
  });
});
</script>

</div>
<!--hr-->

<button id="export-btn" class="btn btn-primary"><i class="fa-solid fa-file-excel"></i>Esporta dati</button>
<button id="export-btn-filtered" class="btn btn-primary"><i class="fa-solid fa-file-excel"></i>Esporta dati filtrati</button>

<div id="tabella">

        <div class="table-responsive-sm">

                  <!--div id="toolbar">
        <button id="showSelectedRows" class="btn btn-primary" type="button">Crea ordine di lavoro</button>
      </div-->
    
      <div id="toolbar" class="isDisabled"> 
      <!--a target="_new" class="btn btn-primary btn-sm"
         href="./export_consuntivazione_ekovision.php"><i class="fa-solid fa-file-excel"></i> Esporta xlsx completo</a-->
      </div>
        <table  id="ek_cons" class="table-hover table-sm" 
        data-locale="it-IT"
        idfield="ID_SCHEDA" 
        data-show-columns="true"
        data-group-by="false"
        data-group-by-field='["indirizzo", "frazione"]'
        data-show-search-clear-button="true"   
        data-show-export="false" 
        data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
				data-search="false" 
        data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
				data-pagination="true" data-page-size=100 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="server" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
        data-search-on-enter-key="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-export-data-type="all"
        data-url="./tables/report_consuntivazione_ekovision.php?ut=<?php echo $_POST['ut']?>&data_inizio=<?php echo $last_month->format("Y-m-d");?>&data_fine=<?php echo $today->format("Y-m-d");?>" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        data-query-params="queryParams"
        >
        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="FAM_SERVIZIO" data-sortable="true" data-visible="false"  data-filter-control="select">Fam.<br>servizio</th>
        <th data-field="DESC_SERVIZIO" data-sortable="true" data-visible="true" data-filter-data="var:servizio_filtro" data-filter-control="select">Tipo<br>Servizio</th>
        <th data-field="UT" data-sortable="true" data-visible="true" data-filter-control="input">UT</th> 
        <th data-field="DATA_PIANIFICATA" data-formatter="dateFormat" data-sortable="true" data-visible="true" data-filter-control="false">Data<br>pianificata</th>
        <th data-field="DATA_ESECUZIONE" data-formatter="dateFormat" data-sortable="true" data-visible="true" data-filter-control="false">Data<br>esecuzione</th>
        <th data-field="COD_PERCORSO" data-sortable="true" data-visible="false" data-filter-control="input">Cod<br>percorso</th>
        <th data-field="DESCR_PERCORSO" data-sortable="true" data-visible="false" data-filter-control="input">Descr<br>percorso</th>
        <th data-field="PERCORSO" data-sortable="true" data-visible="true" data-filter-control="input">Percorso</th>
        <th data-field="PREVISTO"  data-sortable="true" data-visible="true" data-formatter="prevFormat" data-export-formatter="false"
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni_prev" data-filter-control="select">Prev</th>
        <th data-field="STATO" data-sortable="true" data-visible="true" data-formatter="statoFormat"  
        data-filter-strict-search="true" data-search-formatter="false" 
        data-filter-data="var:opzioni_stato"  data-filter-control="select">Stato</th>
        <th data-field="ORARIO_ESECUZIONE" data-sortable="true" data-visible="false" data-filter-control="input">Orario</th>
        <th data-field="FASCIA_TURNO" data-sortable="true" data-visible="true" data-filter-control="select">Turno</th>
        <th data-field="FLG_SEGN_SRV_NON_COMPL" data-sortable="true" data-visible="false" data-filter-control="select">Non<br>completato</th>
        <!--th data-field="FLG_SEGN_SRV_NON_EFFETT" data-sortable="true" data-visible="true" 
        data-formatter="nonEseguitoFormat" data-filter-data="var:causale_filtro"  data-filter-control="select">Causale non<br>effettuato</th-->
        <th data-field="DESCR_CAUSALE" data-sortable="true" data-visible="true" data-filter-data="var:causale_filtro" data-filter-control="select">Causale non<br>effettuato</th>
        <th data-field="ID_SCHEDA" data-sortable="true" data-visible="true" data-events="operateEvents" 
        data-formatter="operateFormatter" data-filter-control="input"></th>
    </tr>
</thead>
</table>


<div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <!--h5 class="modal-title" id="exampleModalLabel">Titolo</h5-->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <div class="modal-body">
                <!-- output data here-->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>








<script type="text/javascript">


const $table = $('#ek_cons');

$(function() {
    $table.bootstrapTable();
    //console.log($table.bootstrapTable());
  });

  
var opzioni_prev = ['Previsto', 'Non previsto'] ;

function prevFormat(value) {
  if (value =='Previsto'){
    return '<span style="font-size: 1em; color: grey;"><i  title="'+value+'" class="fa-regular fa-calendar-check"></i></span>';
  } else if (value =='Non previsto') {
    return '<span style="font-size: 1em; color: blue;"> <i title="'+value+'" class="fa-regular fa-calendar-plus"></i></span>';
  }
};


function operateFormatter(value, row, index) {
    return [
        '<a class="info btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewMemberModal">',
        '<i class="fa-regular fa-square-caret-down"></i>',
        '</a>'
    ].join('');
};


window.operateEvents = {
    'click .info': function (e, value, row, index) {
        console.log('Sono qua');
        console.log(row.ID_SCHEDA);
        var id = row.ID_SCHEDA;
        console.log('id'+id);
        $.ajax({   
            type: "post",
            url: "consuntivazione_ekovision_dettaglio.php",
            data: 'id=' + id,
            dataType: "text",                  
            success: function(response){                    
                $(".modal-body").html(response); 
            }
        });
        $('#viewMemberModal').modal('show')
        	/*.find('.modal-body').html('<p> '+row.ID_SCHEDA+' </p><pre>' + 
            JSON.stringify(row) + '</pre>');*/
    }
};

// Modal con dettagli consuntivazione percorso 





var opzioni_stato = ['COMPLETATO', 'NON COMPLETATO', 'NON EFFETTUATO', 'NON ESEGUITO' ] ;

function statoFormat(value) {
  if (value =='COMPLETATO'){
    return '<span style="font-size: 1em; color: blue;"><i  title="'+value+'" class="fa-solid fa-check-double"></i></span>';
  }  else if (value =='NON ESEGUITO') {
    return '<span style="font-size: 1em; color: red;"> <i title="'+value+'" class="fa-solid fa-pencil"></i></span>';
  } else if (value =='NON COMPLETATO') {
    return '<span style="font-size: 1em; color: orange;"> <i title="'+value+'" class="fa-solid fa-circle-exclamation"></i></span>';
  } else if (value =='NON EFFETTUATO') {
    return '<span style="font-size: 1em; color: red;"> <i title="'+value+'" class="fa-solid fa-triangle-exclamation"></i></span>';
  }
};


function nonEseguitoFormat(value,row) {
  if (value == 0){
    return ''; 
  } else if (value ==1) {
    return '<span style="font-size: 1em; color: red;"> '+row.DESCR_CAUSALE+'</span>';
  } 
};

function idFormatter() {
    return 'Tot contenitori:'
  };

  function countFormatter(data) {
    return data.length
  };



  


  function queryParams(params) {
    const options = $table.bootstrapTable('getOptions')
    if (!options.pagination) {
      params.limit = options.totalRows
    }
    return params
  };




function dateFormat(value, row, index) {
   if (value){ 
    return moment(value).format('DD/MM/YYYY (ddd)');
   } else {
    return '-';
   }
};



  /*data.forEach(d=>{
       data_creazione = moment(d.data_creazione).format('DD/MM/YYYY HH24:MI')
    });*/
    
    function dateFormatter(date) {
      return moment(date).format('DD/MM/YYYY HH:mm')
    };


    const dataInizio = $('input[name="daterange"]').val().split(' - ')[0];
    const partsI = dataInizio.split('/');
    //const dateObjI = new Date(partsI[2], partsI[1]-1, partsI[0]); // mese 0-indexed
    //const dataIni = dateObjI.toISOString().slice(0, 10);
    const dataIni = `${partsI[2]}-${partsI[1]}-${partsI[0]}`;
    const dataFine = $('input[name="daterange"]').val().split(' - ')[1];
    const partsF = dataFine.split('/');
    //const dateObjF = new Date(partsF[2], partsF[1]-1, partsF[0]); // mese 0-indexed
    //const dataFin = dateObjF.toISOString().slice(0, 10); // filtro data fine
    const dataFin = `${partsF[2]}-${partsF[1]}-${partsF[0]}`;
    var ut = $('#ut').val();
    if(ut == 0){
      ut = "";
    }
//////// esportazione totale
  $('#export-btn').click(async () => {
  // Recupera tutti i dati dal server
  const res = await fetch('./tables/report_consuntivazione_ekovision.php?ut=<?php echo $_POST['ut']?>&data_inizio='+dataIni+'&data_fine='+dataFin+'');
  const data = await res.json();

  console.log("URL fetch export:", res);

  // SheetJS si aspetta un array → usiamo data.rows
  const ws = XLSX.utils.json_to_sheet(data.rows);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

  // Salva file Excel
  XLSX.writeFile(wb, "export.xlsx");
});

//////// esportazione filtrata
  // Funzione per leggere i filtri delle colonne dai <input>/<select> nel thead
  window.getColumnFilters = function() {
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
  };

  // queryParams: unisce params standard con filtri colonna
  window.queryParams = function(params) {
    const filters = window.getColumnFilters ? window.getColumnFilters() : {};
    return Object.assign({}, params, filters);
  };

  // Bottone export Excel filtrato
  $('#export-btn-filtered').click(async () => {
    const options = $table.bootstrapTable('getOptions');
    const filters = window.getColumnFilters();
	  

    // Base URL (senza query string)
    const baseUrl = "./tables/report_consuntivazione_ekovision.php";

    // Costruzione sicura dei parametri con URLSearchParams
    const params = new URLSearchParams();

    // Parametri fissi (se presenti)
    
    console.log($('input[name="daterange"]').val())
    console.log(dataInizio);
    console.log(dataFine);
    params.set("ut", ut);
    params.set("data_inizio", dataIni);
    params.set("data_fine", dataFin);
    //params.set("data_inizio", "2025-08-03");
    //params.set("data_fine", "2025-09-03");

    // Paginazione lato server
    params.set("limit", options.totalRows || 1000);
    params.set("offset", 0);

    // Filtro globale (barra search)
    if (options.searchText) {
      params.set("search", options.searchText);
    }

    // Filtri colonna
    /*Object.entries(filters).forEach(([k, v]) => {
      if (v !== undefined && v !== null && v !== '') {
        const obj = { [k]: v };
        console.log(obj)
        const jsonString = JSON.stringify(obj);
        console.log(jsonString)
        params.set("filter", jsonString);
        //params.set(k, v);
      }
    });*/

    const filterObj = {};

    Object.entries(filters).forEach(([k, v]) => {
      if (v !== undefined && v !== null && v !== '') {
        filterObj[k] = v;
      }
    });

    // Lo trasformo in JSON
    if (Object.keys(filterObj).length > 0) {
      params.set("filter", JSON.stringify(filterObj));
    }

    console.log("Filtri JSON:", params.get("filter"));

    // URL finale sicuro
    const url = `${baseUrl}?${params.toString()}`;
    console.log("URL fetch export:", url);

    try {
      const res = await fetch(url);
      const text = await res.text(); // prima leggo testo per debug

      // Provo a parsare JSON
      let data;
      try {
        data = JSON.parse(text);
      } catch (err) {
        console.error("Risposta server non JSON valido:", text);
        alert("Errore: risposta server non JSON valido. Controlla console.");
        return;
      }

      const rows = data.rows || data;

      // Genero Excel con SheetJS
      const ws = XLSX.utils.json_to_sheet(rows);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Filtrati");
      XLSX.writeFile(wb, "export-filtrato.xlsx");

    } catch (err) {
      console.error("Errore fetch export:", err);
      alert("Errore durante la richiesta di export. Controlla console.");
    }
  });
</script>


</div>	<!--tabella-->










</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>


<script>

$('#js-date').datepicker({
    format: 'dd/mm/yyyy',
    //startDate: '+1d', 
    language:'it' 
});

$('#js-date1').datepicker({
    format: 'dd/mm/yyyy', 
    language:'it'
});
</script>


</body>

</html>