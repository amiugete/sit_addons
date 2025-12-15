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

    <title>Pronto Intervento</title>
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







<div class="container-fluid">



  
 

<!--button id="export-btn" class="btn btn-primary" title="Esporta file Excel completo"><i class="fa-solid fa-table-list"></i>Esporta dati</button>
<button id="export-btn-filtered" class="btn btn-primary" title="Esporta file Excel filtrato"><i class="fa-solid fa-filter"></i>Esporta dati filtrati</button-->

<div id="tabella_raccolta">
            
  <h4>Chiamate Pronto
  <!--a href="#tabella_spazzamento" class="btn btn-sm btn-info"> Vai allo spazzamento </a--></h4>

  <?php
    require_once ('./link_treg.php'); 
  ?>
  <!--div class="row justify-content-start" style="margin-top: 2%;">

    <div class="col-5">
    </div>
    <div class="col-5">
    </div>
    <div class="col-2">
      <button id="exportR-btn" class="btn btn-primary" title="Esporta file Excel completo" style="width: 100%;"><i class="bi bi-file-earmark-excel" ></i>Esporta tabella</button>
    </div>
  </div-->
<!--button id="exportR-btn" class="btn btn-primary" title="Esporta file Excel completo"><i class="fa-solid fa-table-list"></i>Esporta dati</button>
<button id="exportR-btn-filtered" class="btn btn-primary" title="Esporta file Excel filtrato"><i class="fa-solid fa-filter"></i>Esporta dati filtrati</button-->


            <div class="row">

                  
  <!--div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div-->
      <table  id="pin" class="table-hover table-sm" 
        data-locale="it-IT"
        data-show-columns="true"
        data-show-search-clear-button="true"   
        data-show-export="false" 
        data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'pdf', 'excel',  'doc'] 
				data-search="false" data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="server" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false" 
        data-export-data-type="all"
        data-url="./tables/report_pin_arera.php" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        data-row-style="rowStyle"
        >

<!--Per i filtri guardare report_fascia_oraria_esecuzione -->

        
<thead>

							
 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <th data-field="id" data-sortable="true" data-visible="true" data-filter-control="input">ID</th>  
        <!--th data-field="scopo" data-sortable="true" data-visible="true" data-filter-control="select">Scopo</th-->
        <th data-field="sottoscopo" data-sortable="true" data-visible="true" data-filter-control="select">Sottoscopo</th>
        <th data-field="esito" data-sortable="true" data-visible="true" data-filter-control="select">Esito</th>
        <th data-field="data_telefonata" data-sortable="true" data-visible="true" data-formatter="dateTimeFormat0" data-filter-control="select" >Data/ora<br>telefonata</th>
        <th data-field="cod_ident_segn"  data-sortable="true" data-visible="false" data-filter-control="input">ID GAP</th>
        <th data-field="tipo_apertura_guasto"  data-sortable="true" data-visible="true" data-filter-control="input">Tipo segnalazione</th>
        <th data-field="data_apert_segn"   data-sortable="true" data-visible="true" data-formatter="dateTimeFormatA" data-filter-control="input">Apertura segn.</th>
        <!--th data-field="ora_apert_segn"   data-sortable="true" data-visible="true" data-filter-control="input">Ora apertura</th-->
        <th data-field="localita" data-sortable="true" data-visible="true" data-filter-control="input">Località</th>
        <th data-field="indirizzo"   data-sortable="true" data-visible="true" data-filter-control="input">Indirizzo</th>
        <th data-field="nomin_segn" data-sortable="true" data-visible="true" data-filter-control="input">Segnalante</th>
        <th data-field="rec_tel" data-sortable="true" data-visible="false" data-filter-control="select">Telefono</th>
        <th data-field="tipo_richiesta" data-sortable="true" data-visible="true" data-filter-control="select">Tipo richiesta</th>
        <th data-field="note_chiusura" data-sortable="true" data-visible="true" data-formatter="troncaFormatter" data-filter-control="select">Note chiusura</th>
        <th data-field="data_chius_segn"   data-sortable="true" data-visible="true" data-formatter="dateTimeFormatC" data-filter-control="input">Chiusura segn.</th>
        <!--th data-field="ora_chius_segn"   data-sortable="true" data-visible="false" data-filter-control="input">Ora chiusura</th-->
        <th data-field="nomin_tecn_amiu" data-sortable="true" data-visible="true" data-filter-control="input">Tecnico</th>
        <th data-field="ident_interv_chius" data-sortable="true" data-visible="false" data-filter-control="input">ID chiusura</th>
        <!--th data-field="data_ins"  data-sortable="true" data-visible="false" data-filter-control="input">Data inserimento</th>
        <th data-field="nome_file"  data-sortable="true" data-visible="false" data-filter-control="input">File GAP</th-->
        <th data-field="data_arrivo_luogo"   data-sortable="true" data-visible="true" data-formatter="dateTimeFormatAL" data-filter-control="select">Arrivo<br>sul luogo</th>
        <!--th data-field="ora_arrivo_luogo" data-sortable="true" data-visible="false" data-filter-control="input">ora arrivo sul luogo</th-->
        <th data-field="data_messa_sic"   data-sortable="true" data-visible="true" data-formatter="dateTimeFormatMS" data-filter-control="input">Messa<br>in sicurezza</th>
        <!--th data-field="ora_messa_sic" data-sortable="true" data-visible="false" data-filter-control="input">ora messa in sicurezza</th-->
        <th data-field="data_rim_rif"   data-sortable="true" data-visible="true" data-formatter="dateTimeFormatRR" data-filter-control="input">Rimozione<br>rifiuti</th>
        <!--th data-field="ora_rim_rif" data-sortable="true" data-visible="false" data-filter-control="input">ora rimozione rifiuti</th-->
        <th data-field="mot_rit" data-sortable="true" data-visible="true" data-filter-control="input">Motivazione<br>ritardo</th>
    </tr>
</thead>
</table>



<div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-scrollable modal-xl" >
      <div class="modal-content">
        <div class="modal-header">
          <!--h5 class="modal-title" id="exampleModalLabel">Titolo</h5-->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <div class="modal-body" id="body_dettaglio">
                <!-- output data here-->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">



  var $table = $('#pin');


$table.on('post-body.bs.table', function () {
  if ($('#export-btn-filtered').length === 0) {
    $('.fixed-table-toolbar .columns')
      .append('<button id="export-btn-filtered" class="btn btn-secondary ms-2" title="Esporta file Excel"><i class="bi bi-download"></i> Esporta tabella</button>');
  }
});
  
  $(function() {
    $table.bootstrapTable()
  });
  


  function queryParams(params) {
    var options = $table.bootstrapTable('getOptions')
    if (!options.pagination) {
      params.limit = options.totalRows
    }
    return params
  };

  function dateTimeFormat0(value, row, index) {
      return moment(value).format('DD/MM/YYYY HH:mm')
    }

function dateTimeFormatA(value, row, index) {
  if (value){
   return moment(row.data_apert_segn).format('DD/MM/YYYY') + ' ' + moment(row.ora_apert_segn, 'HH:mm:ss').format('HH:mm');
}else {
    return '-';
   }
}

function dateTimeFormatC(value, row, index) {
  if (value){
   return moment(row.data_chius_segn).format('DD/MM/YYYY') + ' ' + moment(row.ora_chius_segn, 'HH:mm:ss').format('HH:mm');
   }else {
    return '-';
   }
}

function dateTimeFormatAL(value, row, index) {
  if (value){
   return moment(row.data_arrivo_luogo).format('DD/MM/YYYY') + ' ' + moment(row.ora_arrivo_luogo, 'HH:mm:ss').format('HH:mm');
   }else {
    return '-';
   }
}

function dateTimeFormatMS(value, row, index) {
  if (value){
   return moment(row.data_messa_sic).format('DD/MM/YYYY') + ' ' + moment(row.ora_messa_sic, 'HH:mm:ss').format('HH:mm');
   }else {
    return '-';
   }
}

function dateTimeFormatRR(value, row, index) {
  if (value){
   return moment(row.data_rim_rif).format('DD/MM/YYYY') + ' ' + moment(row.ora_rim_rif, 'HH:mm:ss').format('HH:mm');
   }else {
    return '-';
   }
}

function dateFormat(value, row, index) {
   if (value){ 
    return moment(value).format('MMM YYYY');
   } else {
    return '-';
   }
};

function dateFormat2(value, row, index) {
   if (value){ 
    return moment(value).format('MMM');
   } else {
    return '-';
   }
};


function realFormat(value, row, index) {
   if (value){ 
    return parseFloat(value).toLocaleString("it-IT");
   } else {
    return '-';
   }
};
 
function realFormat_pc(value, row, index) {
   if (value){ 
    return parseFloat(value).toLocaleString("it-IT")+'%';
   } else {
    return '-';
   }
};

function rowStyle(row, index) {
  console.log(row.data_chius_segn)
    if (row.data_chius_segn == null) {
      return {
        css: {
          '--bs-table-bg': 'orange'
        }
      }
    }  
}

function escapeHtml(text) {
  return text
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

function troncaFormatter(value, row, index) {
    const max = 100;
    if (!value) return '';
    const tronca = value.length > max ? value.substring(0, max) + '…' : value;
    console.log(value);
    console.log(tronca);
    return `<span title="${escapeHtml(value)}">${tronca}</span>`;
  }

$(function() {
  initTableExport({
    tableId: "pin",
    exportAllBtn: "#export-btn",
    exportFilteredBtn: "#export-btn-filtered",
    baseUrl: "./tables/report_pin_arera.php"
  });
});

</script>


</div>	<!--tabella-->



<?php
require_once('req_bottom.php');
require_once('./footer.php');
?>


  </div>

</body>

</html>