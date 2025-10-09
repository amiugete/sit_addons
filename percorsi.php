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


<?php 

$today = new DateTime('now');
$timezone = new DateTimeZone('Europe/Rome');
$today->setTimezone($timezone);


require_once("select_ut.php");
?>

<div id="tabella">
            
        <h4>Elenco percorsi <?php echo $today->format('d/m/Y');;?> </h4>




            <div class="row">

                  
  <div id="toolbar"> 
</div>
				<table  id="percorsi" class="table-hover table-sm" 
        idfield="id" 
        data-toolbar="#toolbar" 
        data-group-by="false"
        data-group-by-field='["cod_percorso", "descrizione", "famiglia", "tipo"]'
        data-show-search-clear-button="true"   
        data-show-export="false" 
        data-export-type="['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf']"
				data-search="true" data-click-to-select="true" data-show-print="false"  
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
        data-show-pagination-switch="true"
				data-side-pagination="false" 
        data-search-on-enter-key="true"  
        data-remember-order="true"
        data-search-highlight = "true" 
        data-auto-refresh="true"
        data-auto-refresh-interval = 120
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false"
        data-query-params="queryParams"
        data-url="./tables/percorsi_raggruppati.php?ut=<?php echo $_POST['ut0'];?>&solo_attivi=t">
        

        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="famiglia" data-sortable="true" data-visible="true" data-filter-control="select">Famiglia</th>
        <th data-field="tipo" data-sortable="true" data-visible="true"  data-filter-control="select">Tipo</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">UT/Rimessa</th> 
        <th data-field="cod_percorso" data-sortable="true" data-visible="true" data-filter-control="input">Codice</th>
        <th data-field="descrizione" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th>
        <th data-field="freq" data-sortable="true" data-visible="true" data-filter-control="input">Frequenza</th>
        <th data-field="turno" data-sortable="true" data-visible="true" data-filter-control="select">Turno</th>
        <th data-field="versione" data-sortable="true" data-visible="true" data-filter-control="select">V</th>
        <th data-field="stagionalita" data-sortable="true" data-visible="true" data-filter-control="select">Stag</th>
        <th data-field="flg_disattivo" data-sortable="true" data-visible="true" data-formatter="nameFormatterAtt" 
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni" 
        data-filter-control="select" data-filter-control-multiple-search="true" data-filter-control-multiple-search-delimiter=","
data-filter-options="{ filterAlgorithm: 'or' }"></th>
        <!--   data-filter-default='' -->
        <th data-field="cp_report" data-sortable="false" data-formatter="nameFormatterReport" data-visible="true" >Report</th>
        <?php if ($check_superedit == 1) { ?>
          <th data-field="cp_edit" data-sortable="true"  data-visible="true"  data-events="dpEvents" data-formatter="nameFormatterEdit_ok">Edit</th>
        <?php } ?>
          <!--th data-field="quartiere" data-sortable="true" data-visible="true" data-filter-control="select">Quartiere<br>/Comune</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">UT</th>
        <th data-field="tipo" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>apertura</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>chiusura</th>
        <th data-field="desc_intervento" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th-->
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

  document.addEventListener("DOMContentLoaded", function() {
      const params = new URLSearchParams(window.location.search);
      const cp = params.get("cp");
      const v = params.get("v");

      if (cp && v) {
          //console.log("il percorso è: "+cp);
          $.ajax({
              type: "get",
              url: "dettagli_percorso_modal.php",
              data: { 'cp': cp, 
              'v': v },
              dataType: "text",
              success: function(response) {
                  $("#body_dettaglio").html(response);

                  //console.log($("#body_dettaglio").html(response))
                  var modal = new bootstrap.Modal(document.getElementById("viewMemberModal"));
                  modal.show();

                  // toglie i parametri dalla url
                  const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                  window.history.replaceState({}, document.title, newUrl);
              }
          });
      }
  });

  var $table = $('#percorsi');

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




 


    var opzioni = ['Attivo', 'In attivazione', 'In disattivazione', 'Disattivo'] ;

    function nameFormatterAtt(value) {
      if (value =='Attivo'){
        return '<span style="font-size: 1em; color: green;"> <i title="'+value+'" class="fa-solid fa-play"></i></span>';
      } else if (value =='In attivazione') {
        return '<span style="font-size: 1em; color: blue;"> <i title="'+value+'" class="fa-solid fa-pause"></i></span>';
      } else if (value =='In disattivazione') {
        return '<span style="font-size: 1em; color: red;"> <i title="'+value+'" class="fa-solid fa-pause"></i></span>';
      } else if (value =='Disattivo') {
        return '<span style="font-size: 1em; color: Tomato;"> <i title="'+value+'" class="fa-solid fa-stop"></i></span>';
      }
    };

    function nameFormatterEdit(value, row) {
        
        return '<a class="btn btn-warning" href="./dettagli_percorso.php?cp='+row.cod_percorso+'&v='+row.versione+'"><i class="fa-solid fa-pencil"></i></a>';
     
    };



    function nameFormatterEdit_ok(value, row, index) {
    return [
        '<button type="button" class="info btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#viewMemberModal">',
        '<i class="fa-solid fa-pencil"></i>',
        '</button>'
    ].join('');
};


window.dpEvents = {
    'click .info': function (e, value, row, index) {
      $('#ut0').attr('disabled',true);
        console.log('Sono qua');
        console.log(row.cod_percorso);
        var cp = row.cod_percorso;
        console.log('cp'+cp);
        var v = row.versione;
        console.log('v'+v);
        $.ajax({   
            type: "get",
            url: "dettagli_percorso_modal.php",
            data: {'cp': cp,
                    'v': v
                  },
            dataType: "text",                  
            success: function(response){                    
                $("#body_dettaglio").html(response);
                
            }, 
              
            
        });
      
    }
};
    





    function nameFormatterReport(value, row) {
      if (row.flg_disattivo == 'Attivo' && !row.tipo.includes('SOLO TESTATA')) {
        return [
          '<div class="btn-group btn-group-sm" role="group" aria-label="...">',
          '<a class="btn btn-success btn-sm" href="./download_report_percorso.php?cod='+row.cod_percorso+'&vers=s"><i title="Versione per operatore" class="fa-solid fa-clipboard-list"></i></a>',
          '<a class="btn btn-primary btn-sm" href="./download_report_percorso.php?cod='+row.cod_percorso+'&vers=c"><i title="Versione completa" class="fa-solid fa-list-check"></i></a>',
          '</div>'  
        ].join('');
      }  
    };

$(function() {
  initTableExport({
    tableId: "percorsi",
    exportAllBtn: "#export-btn",
    exportFilteredBtn: "#export-btn-filtered",
    baseUrl: "./tables/percorsi_raggruppati.php",
    extraParams: () => {
      // parametri extra della pagina
      //const range = $('input[name="daterange"]').val().split(" - ");
      return {
        ut: $("#ut0").val() == 0 ? "" : $("#ut0").val()//,
        //data_inizio: range[0].split('/').reverse().join('-'),
        //data_fine: range[1].split('/').reverse().join('-')
      };
    }
  });
});
</script>



</div>	<!--tabella-->










</div>


<!-- Script  REFRESH -->
<script type="text/javascript">
  const myModalEl = document.getElementById('viewMemberModal');

  myModalEl.addEventListener('hidden.bs.modal', function () {
    // Funzione da eseguire alla chiusura del modal
    //console.log("Modal chiuso");
    // Qui puoi chiamare qualsiasi altra funzione
    var data_percorsi=$('#js-date3').val();
    //console.log(data_percorsi);
    //console.log($table);
    $table.bootstrapTable('refresh', {
    url: "./tables/percorsi_raggruppati.php?ut=<?php echo $_POST['ut0'];?>&solo_attivi=t"
    });   
    console.log('refresh fatto');
});

  
</script>








<?php
require_once('req_bottom.php');
require_once('./footer.php');
?>


  </div>



  <script type="text/javascript">
 var today = new Date();
 //var week_before=new Date();
 //week_before.setDate(week_before.getDate() - 7);
$('#js-date_attivi').datepicker({
      format: 'dd/mm/yyyy',
      todayBtn: "linked", // in conflitto con startDate
      endDate:today,
      //startDate:week_before,
      language:'it', 
      autoclose: true
  });

  
</script>
</body>

</html>