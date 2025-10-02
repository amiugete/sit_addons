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

# filtro sulle UT
$filter_totem="OK";


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
  
  <script>
  function utScelta(val) {
    document.getElementById('open_ut').submit();
  }
</script>


<div class="container">
<?php 




//require_once("select_ut.php");


if ($_POST['ut0']) {
  $query0='select cmu.id_uo as ut, u.id_ut, descrizione
    from topo.ut u
    join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut 
    where u.id_ut = $1';

  $result0 = pg_prepare($conn, "my_query0", $query0);
  $result0 = pg_execute($conn, "my_query0", array($_POST['ut0']));
  
  while($r0 = pg_fetch_assoc($result0)) {
      $uos=$r0["ut"];
      $uos_descrizione= $r0["descrizione"];
  }

  ?> 




  



<?php 

$today = new DateTime('now');
$timezone = new DateTimeZone('Europe/Rome');
$today->setTimezone($timezone);
$hour = $today->format('Hi');
if ($hour < '1120'){
    $today = $today->modify("-1 day");
    $nota_data='<font color="red"> <i class="fa-solid fa-clock-rotate-left"></i> Prima della fine del turno mattutino è impostata di default la data di ieri </font>';
} 
?>

<style>
.vl {
  border-left: 6px solid green;
  height: 72px;
}
</style>

<form  class="row row-cols-lg-auto g-3 align-items-center" name="form_filtro" id="form_filtro" autocomplete="off">
    <input type="hidden" class="form-control" id="uos" name="uos" value="<?php echo $uos;?>" required>

    <div class="form-group col-lg-3">
      <?php echo $uos_descrizione." - " ;?>
<a class="btn btn-info" href="<?php echo basename($_SERVER['PHP_SELF']);?>"> <i class="fa-solid fa-house"></i>Cambia UT</a>
</div>

<i class="fa-solid fa-grip-lines-vertical"></i>

<div class="form-group col-lg-3">
    <label for="data_percorsi" class="form-label">Data verifica</label>
    <input type="text" class="form-control" id="js-date3" name="data_percorsi" onchange="cambiata_data(this.value);" value="<?php echo $today->format('d/m/Y');?>" required>
    <!--input type="text" class="form-control" id="js-date3" name="data_percorsi" value="<?php echo $today->format('d/m/Y');?>" required-->
    <div id="nota_data" class="form-text"><?php echo $nota_data;?></div>
</div>
<!--div class="form-group col-lg-2">
<button type="submit" class="btn btn-primary">Filtra</button>
</div-->
</form>

<script>
  function cambiata_data(val) {
    console.log("Bottone cambiata_data  cliccato");
    var data_percorsi=val;
    console.log(data_percorsi);
    var uos="<?php echo $uos?>";
    console.log(uos);
    if ($('#check_id').is(":checked"))
    {
      var filtro_percorsi = 'all';
    } else {
      var filtro_percorsi = 'none';
    }
    
    $("#nota_data").html("").fadeIn("slow");
    $(function() {    // Faccio refres della data-url
    $table.bootstrapTable('refresh', {
      url: "./tables/report_totem_percorsi.php?uos="+uos+"&d="+data_percorsi
    }); 

  });
};

$(document).ready(function () {                 
  $('#form_filtro').submit(function (event) { 
      console.log("Bottone filtro cliccato");
      var data_percorsi=$('#js-date3').val();
      console.log(data_percorsi);
      var uos="<?php echo $uos?>";
      console.log(uos);
      if ($('#filtro_percorsi').is(":checked"))
      {
        var filtro_percorsi = 'all';
      } else {
        var filtro_percorsi = 'none';
      }
      console.log(filtro_percorsi);
      $("#nota_data").html("").fadeIn("slow");
      //$(function() {    // Faccio refres della data-url
        $table.bootstrapTable('refresh', {
          url: "./tables/report_totem_piazzola.php?uos="+uos+"&d="+data_percorsi+"&c="+filtro_percorsi
        }); 
      return false;
      //});
  });
});

function getFiltroPercorsi() {
   return $('#filtro_percorsi').is(":checked") ? 'all' : 'none';
}

</script>


<hr>

<div id="tabella">
            
        <h4>Report consuntivazione da totem - Percorsi con qualche piazzola di <?php echo $uos_descrizione;?></h4>




        <div class="table-responsive-sm">

                  <!--div id="toolbar">
        <button id="showSelectedRows" class="btn btn-primary" type="button">Crea ordine di lavoro</button>
      </div-->
    
      <div id="toolbar" class="isDisabled"> 
      <!--a target="_new" class="btn btn-primary btn-sm"
         href="./export_consuntivazione_ekovision.php"><i class="fa-solid fa-file-excel"></i> Esporta xlsx completo</a-->
      </div>
				<table  id="totem_percorsi" class="table-hover table-sm" 
        idfield="ID_SCHEDA" 
        data-show-columns="true"
        data-group-by="false"
        data-group-by-field='["indirizzo", "frazione"]'
        data-show-search-clear-button="true"   
        data-show-export="false" 
        data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'pdf', 'excel',  'doc'] 
				data-search="false" data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
				data-pagination="false" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="false" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false" 
        data-export-data-type="all"
        data-url="./tables/report_totem_percorsi.php?uos=<?php echo $uos?>&d=<?php echo $today->format('d/m/Y');?>&c=none" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        >
        
        
<thead>



 	  <tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="descr_servizio" data-sortable="true" data-visible="true"  data-filter-control="select">Tipo<br>Servizio</th>
        <th data-field="tipo_rifiuto" data-sortable="true" data-visible="false" data-filter-control="select">Rifiuto</th>
        <th data-field="id_percorso" data-sortable="true" data-visible="true" data-filter-control="input">Cod<br>Percorso</th> 
        <th data-field="descr_percorso" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th>
        <th data-field="uo_esec" data-sortable="true" data-visible="true" data-filter-control="input">UT<br>esec</th> 
        <th data-field="descr_orario"  data-sortable="true" data-visible="true" data-filter-control="select">Turno</th>
        <th data-field="stato_consuntivazione" data-sortable="true" data-visible="true" data-formatter="nameFormatterStato" 
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni" data-filter-control="select">Stato</th>
        <th data-field="causali_text" data-sortable="true" data-visible="true" data-filter-control="select">Causali</th>
        <th data-field="in_previsione" data-sortable="true" data-visible="true" data-filter-control="select"
        data-formatter="nameFormatterPrevisto" data-filter-data="var:opzioni1" data-filter-strict-search="true" data-search-formatter="false">Previsto</th>
        <th data-field="datalav" data-sortable="true"  data-formatter="dateFormat" data-visible="true" data-filter-control="input">data</th>
        <th data-field="ordine_rifiuto" data-sortable="true"  data-events="operateEvents" data-formatter="operateFormatter" 
         data-visible="true" data-filter-control="input">Dettagli</th>
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



var $table = $('#totem_percorsi');

$table.on('post-body.bs.table', function () {
    const $tableee = $(this); 
    const $toolbar = $tableee.closest('.bootstrap-table').find('.fixed-table-toolbar .columns');

    if ($toolbar.find('#exportT-btn-filtered').length === 0) {
      $toolbar.append(
        '<button id="exportT-btn-filtered" class="btn btn-secondary ms-2" title="Esporta file Excel">' +
        '<i class="bi bi-download"></i> Esporta tabella</button>'
      );
    }
  });

$(function() {
    $table.bootstrapTable();
  });
  




  var opzioni = ['COMPLETATO', 'NON CONSUNTIVATO', 'NON COMPLETATO', 'NON EFFETTUATO'] ;

function nameFormatterStato(value, row, index) {
  if (value =='COMPLETATO'){
    return '<span style="font-size: 1em; color: green;"> <i title="'+row.stato_consuntivazione+'" class="fa-solid fa-circle"></i></span>';
  } else if (value =='NON CONSUNTIVATO') {
    return '<span style="font-size: 1em; color: black;"> <i title="'+row.stato_consuntivazione+'" class="fa-solid fa-circle"></i></span>';
  } else if (value =='NON EFFETTUATO') {
    return '<span style="font-size: 1em; color: red;"> <i title="'+row.stato_consuntivazione+'" class="fa-solid fa-circle"></i></span>';
  } else if (value =='NON COMPLETATO') {
    return '<span style="font-size: 1em; color: orange;"> <i title="'+row.stato_consuntivazione+'" class="fa-solid fa-circle"></i></span>';
  }  
};


var opzioni1 = ['PREVISTO', 'NON PREVISTO'];

function nameFormatterPrevisto(value, row, index) {
  if (value =='PREVISTO'){
    return '<span style="font-size: 1em; color: green;"> <i title="'+value+'" class="fa-regular fa-calendar-check"></i></span>';
  } else if (value =='NON PREVISTO') {
    return ' ';
  }
};



  function queryParams(params) {
    var options = $table.bootstrapTable('getOptions')
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


function dateFormat2(value, row, index) {
   if (value){ 
    return moment(value).format('DD/MM/YYYY HH:mm');
   }
};



function operateFormatter(value, row, index) {
    if (row.stato_consuntivazione =='NON CONSUNTIVATO') {
      return '-';
    } else {
      return [
        '<a class="info btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewMemberModal">',
        '<i class="fa-regular fa-square-caret-down"></i>',
        '</a>'
      ].join('');
    }
};


window.operateEvents = {
    'click .info': function (e, value, row, index) {
        console.log('Sono qua');
        console.log(row.id_percorso);
        var id = row.id_percorso;
        var datalav = row.datalav;
        console.log('datalav'+datalav);
        $.ajax({   
            type: "post",
            url: "report_totem_percorsi_dettaglio.php",
            data: 'id=' + id + '&datalav='+datalav,
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




  /*data.forEach(d=>{
       data_creazione = moment(d.data_creazione).format('DD/MM/YYYY HH24:MI')
    });*/
    
    function dateFormatter(value) {
      return moment(value).format('DD/MM/YYYY HH:mm')
    };


$(function() {
  console.log($("#uos").val())
  console.log($("#js-date3").val().split('/').reverse().join('-'))
  console.log(<?php echo $today->format('d/m/Y');?>)
  initTableExport({
    tableId: "totem_percorsi",
    exportAllBtn: "#exportT-btn",
    exportFilteredBtn: "#exportT-btn-filtered",
    baseUrl: "./tables/report_totem_percorsi.php",
    extraParams: () => {
      // parametri extra della pagina
      //const range = $('input[name="daterange"]').val().split(" - ");
      return {
        //ut: $("#ut").val() == 0 ? "" : $("#ut").val(),
        uos: $("#uos").val() == 0 ? "" : $("#uos").val(),
        d: $("#js-date3").val(),
        c: getFiltroPercorsi()
        //data_fine: range[1].split('/').reverse().join('-')
      };
    }
  });
});

</script>


</div>	<!--tabella-->










</div>

<?php

// se non ho selezionato la UT

} else {
  //echo 'Sono qua';
  require_once('./query_ut.php');

//echo "<br>". $query_ut;
  ?>
<form class="row" name="open_ut" method="post" id="open_ut" autocomplete="off" action="<?php echo basename($_SERVER['PHP_SELF']);?>" >
<div class="form-group col-lg-4">
<select class="selectpicker show-tick form-control" 
data-live-search="true" name="ut0" id="ut0" onchange="utScelta(this.value);" required="">

  <option name="ut0" value="0">Seleziona una UT</option>


<?php            






$result1 = pg_prepare($conn, "my_query1", $query_ut);
$result1 = pg_execute($conn, "my_query1", array($_SESSION['username']));

while($r1 = pg_fetch_assoc($result1)) { 
?>    
      <option name="ut0" value="<?php echo $r1['id_ut'];?>" ><?php echo $r1['descrizione']?></option>
<?php 
}
pg_free_result($result1); 
?>

</select>  
</div>
</form>

<?php
  
}
?>

</div>
<?php 


 

require_once('req_bottom.php');
require('./footer.php');
?>


<script type="text/javascript">
 var today = new Date();
 var week_before=new Date();
 week_before.setDate(week_before.getDate() - 7);
$('#js-date3').datepicker({
      format: 'dd/mm/yyyy',
      todayBtn: "linked", // in conflitto con startDate
      endDate:today,
      startDate:week_before,
      language:'it', 
      autoclose: true
  });

  
</script>


</body>

</html>