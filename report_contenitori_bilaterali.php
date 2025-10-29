<?php
//session_set_cookie_params($lifetime);
session_start();

    
?>
<!DOCTYPE html>
<html lang="it">

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





</head>

<body>

<?php require_once('./navbar_up.php');
$name=dirname(__FILE__);
if ((int)$id_role_SIT = 0) {
  redirect('no_permessi.php');
  //exit;
}
?>


<div class="container">


<div class="row">
  <div class="col-md-10">
<?php 

$query_min="select ci.id_piazzola,
concat(vpd.id_piazzola, ' - ', vpd.via, ' ',vpd.civ,' ', vpd.riferimento) as indirizzo,
vls.targa_contenitore, data_ora_last_sv
from idea.v_last_svuotamenti vls
left join idea.censimento_idea ci on ci.targa_contenitore = vls.targa_contenitore
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int
left join idea.codici_cer cc on cc.codice_cer = ci.cod_cer_mat 
where ci.id_piazzola not like 'MAG%' and data_ora_last_sv = (select min(data_ora_last_sv) as data_min
		from idea.v_last_svuotamenti 
		where targa_contenitore in (select targa_contenitore from idea.censimento_idea ci where ci.id_piazzola not like 'MAG%')
)";

$result_min = pg_prepare($conn, "query_min", $query_min); 
if (pg_last_error($conn)){
  echo pg_last_error($conn);
  $res_ok=$res_ok+1;
  exit(0);
}  
$result_min = pg_execute($conn, "query_min", array());
if (pg_last_error($conn)){
  echo pg_last_error($conn);
  $res_ok=$res_ok+1;
  exit(0);
}

while($rmin = pg_fetch_assoc($result_min)) {
  echo "";
}



$query_max1="select date_trunc('minute',max(ci.data_agg_api)) as max_data_agg_api
from idea.censimento_idea ci 
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int
where ci.id_piazzola not like 'MAG%'";

$result_max1 = pg_prepare($conn, "query_max1", $query_max1);  
if (pg_last_error($conn)){
  echo pg_last_error($conn);
  $res_ok=$res_ok+1;
  exit(0);
}
$result_max1 = pg_execute($conn, "query_max1", array());
if (pg_last_error($conn)){
  echo pg_last_error($conn);
  $res_ok=$res_ok+1;
  exit(0);
}

while($rmax1 = pg_fetch_assoc($result_max1)) {
  echo '<i class="fa-solid fa-stopwatch"></i> ';
  /*if($rmax1['max_data_agg_api'] ){

  }*/
  echo "<b>Ultimo aggiornamento AMIU</b>: ".$rmax1['max_data_agg_api'] ." / ";
}


$query_max0="select date_trunc('minute',max(ci.data_ultimo_agg)) as max_data_ultimo_agg
from idea.censimento_idea ci 
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int
where ci.id_piazzola not like 'MAG%'";

$result_max0 = pg_prepare($conn, "query_max0", $query_max0);   
if (pg_last_error($conn)){
  echo pg_last_error($conn);
  $res_ok=$res_ok+1;
  exit(0);
}
$result_max0 = pg_execute($conn, "query_max0", array());
if (pg_last_error($conn)){
  echo pg_last_error($conn);
  $res_ok=$res_ok+1;
  exit(0);
}

while($rmax0 = pg_fetch_assoc($result_max0)) {
  echo '<i class="fa-solid fa-stopwatch-20"></i>';
  echo "<b>Ultimo aggiornamento contenitore</b>: ".$rmax0['max_data_ultimo_agg'] ." <br>";
}



$query_max="select ci.id_piazzola,
concat(vpd.id_piazzola, ' - ', vpd.via, ' ',vpd.civ,' ', vpd.riferimento) as indirizzo,
vls.targa_contenitore, data_ora_last_sv, cc.descrizione as frazione
from idea.v_last_svuotamenti vls
left join idea.censimento_idea ci on ci.targa_contenitore = vls.targa_contenitore
left join elem.v_piazzole_dwh vpd on vpd.id_piazzola = ci.id_piazzola::int
left join idea.codici_cer cc on cc.codice_cer = ci.cod_cer_mat 
where ci.id_piazzola not like 'MAG%' 
and data_ora_last_sv = (select max(data_ora_last_sv) as data_min
		from idea.v_last_svuotamenti 
		where targa_contenitore in (select targa_contenitore from idea.censimento_idea ci where ci.id_piazzola not like 'MAG%')
)";

$result_max = pg_prepare($conn, "query_max", $query_max);   
if (pg_last_error($conn)){
  echo '4';
  echo pg_last_error($conn);
  $res_ok=$res_ok+1;
  exit(0);
}
$result_max = pg_execute($conn, "query_max", array());
if (pg_last_error($conn)){
  echo pg_last_error($conn);
  $res_ok=$res_ok+1;
  exit(0);
}

while($rmax = pg_fetch_assoc($result_max)) {
  echo '<i class="fa-solid fa-truck-droplet"></i>';
  echo "<b>Ultimo contenitore svuotato registrato a sistema</b>: ".$rmax['indirizzo']." ".$rmax['frazione']." alle ore ".$rmax['data_ora_last_sv'] ."<br>";
}

?>
</div>
</div>



<script type="text/javascript">


$(document).ready(function(){
  $('.downloadBtn').on('click', function(event) {
    event.preventDefault(); 
    console.log('Sono qua');
    $('#output_message').show(); 
    

    // 🔹 Leggo i parametri dal bottone cliccato
    const reportType = $(this).data('report'); // es: "bilaterali", "ekovision", ecc.

    console.log('Download richiesto per:', reportType);

    $.ajax({ 
        url: './backoffice/download_report_percorsi_bilaterali.php', 
        method: 'POST', 
        data: { report: reportType }, // invio i dati
        //processData: true, 
        //contentType: false, 
        xhrFields: {
        responseType: 'blob' // to avoid binary data being mangled on charset conversion
        },
        success: function(blob, status, xhr) {
            console.log('Finito di elaborare il file');
            //console.log(response);
          
            $('#output_message').hide(); 
            // check for a filename
            var filename = "";
            var disposition = xhr.getResponseHeader('Content-Disposition');
            if (disposition && disposition.indexOf('attachment') !== -1) {
                var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                var matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
            }

            if (typeof window.navigator.msSaveBlob !== 'undefined') {
                // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
                window.navigator.msSaveBlob(blob, filename);
            } else {
                var URL = window.URL || window.webkitURL;
                var downloadUrl = URL.createObjectURL(blob);

                if (filename) {
                    // use HTML5 a[download] attribute to specify filename
                    var a = document.createElement("a");
                    // safari doesn't support this yet
                    if (typeof a.download === 'undefined') {
                        window.location.href = downloadUrl;
                    } else {
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                    }
                } else {
                    window.location.href = downloadUrl;
                }

                setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 100); // cleanup
            }
            console.log('Sono arrivato qua');
        },
        error: function (jqXHR, textStatus, errorThrown) {                        
            alert('Your form was not sent successfully.'); 
            console.error(errorThrown); 
        } 
    }); 

    //return true;
    });
});



$(window).bind ("beforeunload",  function (zEvent) {
  console.log('Nascondo gif 2');
  //$('#output_message').hide();
} );


</script>









<div class="row justify-content-end g-2" style="display: flex; margin-top:1%;">
  <div class="col-auto">
  <button class="btn btn-sm btn-rsu downloadBtn" title="Scarica report di tutti i percorsi bilaterali RSU" data-report="200301"><i class="fa-regular fa-file-excel"></i> Percorsi RSU</button>
  </div>
  <div class="col-auto">
  <button class="btn btn-sm btn-carta downloadBtn" title="Scarica report di tutti i percorsi bilaterali CARTA" data-report="200101"><i class="fa-regular fa-file-excel"></i> Percorsi CARTA</button>
  </div>
  <div class="col-auto">
  <button class="btn btn-sm btn-multi downloadBtn" title="Scarica report di tutti i percorsi bilaterali MULTI" data-report="150106"><i class="fa-regular fa-file-excel"></i> Percorsi MULTI</button>
  </div>
  <div class="col-auto">
  <button class="btn btn-sm btn-org downloadBtn" title="Scarica report di tutti i percorsi bilaterali ORGANICO" data-report="200108"><i class="fa-regular fa-file-excel"></i> Percorsi ORG</button>
  </div>
  <div class="col-auto">
  <button class="btn btn-sm btn-dark downloadBtn" title="Scarica report di tutti i percorsi bilaterali" data-report="all"><i class="fa-regular fa-file-excel"></i> TUTTI percorsi</button>
  <!--a class="btn btn-sm btn-info" href="./download_report_percorsi_bilaterali.php"><i class="fa-regular fa-file-excel"></i> Report percorsi bilaterali</a--> 
</div>
</div>




<div class="row align-items-center" style="display:none;" id="output_message">
  <hr>
  <img src="./img/loading.gif" alt="loader1" style="height:40px; width:auto;" class="img-fluid" id="loaderImg">
  L'operazione potrebbe impiegare un po' di tempo. Attendere, il file sarà presto disponibile per il download. 
  <img src="./img/loading.gif" alt="loader1" style="height:40px; width:auto;" class="img-fluid" id="loaderImg">

  <!--div id='seconds-counter'> </div-->
</div>


<hr>

<div id="tabella">
            
        <h4>Report contenitori bilaterali</h4>




        <div class="table-responsive-sm">

                  <!--div id="toolbar">
        <button id="showSelectedRows" class="btn btn-primary" type="button">Crea ordine di lavoro</button>
      </div-->
    
      <div id="toolbar"> 
        <!--a target="_new" class="btn btn-primary btn-sm"
         href="./export_contenitori_bilaterali.php"><i class="fa-solid fa-file-excel"></i> Esporta xlsx completo</a-->
      </div>
				<table  id="contenitori" class="table-hover table-sm" 
        data-cache="true"
        idfield="targa_contenitore" 
        data-show-columns="true"
        data-group-by="false"
        data-group-by-field='["indirizzo", "frazione"]'
        data-sort-name="val_riemp"
        data-sort-order="desc"
        data-show-search-clear-button="true"   
        data-show-export="false" 
        data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
				data-search="true" data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="true"
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="false" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false"
        data-query-params="queryParams"
        data-url="./tables/report_contenitori_bilaterali.php" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        >
        
        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="id_piazzola" data-sortable="true" data-visible="false"  data-filter-control="input">id</th>
        <th data-field="indirizzo" data-sortable="true" data-visible="true" data-footer-formatter="idFormatter" data-filter-control="input">Piazzola</th>
        <th data-field="municipio" data-sortable="true" data-visible="true" data-footer-formatter="countFormatter" data-filter-control="select">Municipio</th> 
        <th data-field="quartiere" data-sortable="true" data-visible="false" data-filter-control="select">Quartiere</th>
        <th data-field="frazione" data-sortable="true" data-visible="true" data-filter-control="select">Frazione<br>rifiuto</th>
        <th data-field="targa_contenitore" data-sortable="true" data-visible="false" data-filter-control="input">Targa<br>cont</th>
        <th data-field="volume_contenitore" data-sortable="true" data-visible="false" data-filter-control="select">Volume</th>
        <!--th data-field="data_ultimo_agg" data-formatter="dateFormat" data-sortable="true" data-visible="true" data-filter-control="input">Data<br>agg</th>
        <th data-field="ora_ultimo_agg" data-formatter="timeFormat" data-sortable="true" data-visible="true" data-filter-control="input">Ora<br>agg</th-->
        <th data-field="data_ultimo_agg" data-formatter="dateTimeFormat" data-sortable="false" data-visible="true" data-filter-control="input">DataOra<br>agg</th>
        <th data-field="val_riemp" data-sortable="true" data-visible="true" data-filter-control="input">Riemp</th>
        <th data-field="val_bat_elettronica" data-sortable="true" data-visible="true" data-filter-control="input">Batt</th>
        <th data-field="val_bat_bocchetta" data-sortable="true" data-visible="false" data-filter-control="input">Batt<br>bocchetta</th>
        <!--th data-field="data_last_sv" data-formatter="dateFormat" data-sortable="true" data-visible="true" data-filter-control="input">Data<br>svuot</th>
        <th data-field="ora_last_sv" data-formatter="timeFormat" data-sortable="true" data-visible="true" data-filter-control="input">Ora<br>svuot</th-->
        <th data-field="data_ora_last_sv" data-formatter="dateTimeFormat" data-sortable="false" data-visible="true" data-filter-control="input">DataOra<br>svuot</th>
        <th data-field="riempimento_svuotamento" data-sortable="true" data-visible="true" data-filter-control="input">Riemp<br>svuot</th>
        <th data-field="media_conf_giorno" data-sortable="true" data-visible="true" data-filter-control="input">Media<br>conf<br>giorno</th>
        <th data-field="percorsi" data-sortable="true" data-visible="true" data-filter-control="input">Percorsi</th>
        <!--th data-field="targa_contenitore" data-sortable="false" data-formatter="nameFormatterEdit" data-visible="true" >Dettagli</th-->
        <!--th data-field="quartiere" data-sortable="true" data-visible="true" data-filter-control="select">Quartiere<br>/Comune</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">UT</th>
        <th data-field="tipo" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>apertura</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>chiusura</th>
        <th data-field="desc_intervento" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th-->
    </tr>
</thead>
</table>


<script type="text/javascript">



function idFormatter() {
    return 'Tot contenitori:'
  }

  function countFormatter(data) {
    return data.length
  }


var $table = $('#contenitori')

$table.on('post-body.bs.table', function () {
  if ($('#export-btn-filtered').length === 0) {
    $('.fixed-table-toolbar .columns')
      .append('<button id="export-btn-filtered" class="btn btn-secondary ms-2" title="Esporta file Excel"><i class="bi bi-download"></i> Esporta tabella</button>');
  }
});

$(function() {
    $table.bootstrapTable()
  })
  


  function queryParams(params) {
    var options = $table.bootstrapTable('getOptions')
    if (!options.pagination) {
      params.limit = options.totalRows
    }
    return params
  }


/*
function dateFormat(value, row, index) {
   return moment(value).format('DD/MM/YYYY');
}

function timeFormat(value, row, index) {
   return moment(value, "HH:mm:ss").format('HH:mm');
}
*/
function dateTimeFormat(value, row, index) {
      return moment(value).format('DD/MM/YYYY HH:mm')
    }

  /*data.forEach(d=>{
       data_creazione = moment(d.data_creazione).format('DD/MM/YYYY HH24:MI')
    });*/
    
    function dateFormatter(date) {
      return moment(date).format('DD/MM/YYYY HH:mm')
    }

    function nameFormatterEdit(value, row) {
        
        return '<a class="btn btn-warning" href=./dettagli_percorso.php?cp='+row.cod_percorso+'&v='+row.versione+'><i class="fa-solid fa-pencil"></i></a>';
     
        }

$(function() {
  initTableExport({
    tableId: "contenitori",
    exportAllBtn: "#export-btn",
    exportFilteredBtn: "#export-btn-filtered",
    baseUrl: "./tables/report_contenitori_bilaterali.php"
  });
});
</script>


</div>	<!--tabella-->










</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>