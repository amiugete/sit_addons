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

    <title>Quadrature</title>
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

<!--?php 

require_once("./filter_tables/filter_consuntivazione_ekovision.php");

?-->

<div class="container-fluid">
  
  <h4 style="margin-bottom: 1%; display:inline;"> <i class="fa-solid fa-business-time"></i> 
  Quadrature </h4>
  
  <button type="button" class="btn btn-sm" data-bs-container="body" 
  data-bs-toggle="popover" data-bs-placement="right">
  <i class="fa-regular fa-circle-question"></i>
</button>

  <!--?php 

require_once("./last_update_ekovision.php");

?-->

<div class="row justify-content-start" style="margin-top: 1%;">

  <div class="col-4">
  <!--FILTRO PER UT sulla base delle UT del mio profilo SIT-->
<div class="rfix">

<script>
  function utScelta(val) {
    document.getElementById('open_ut').submit();
  }
</script>

 


<form class="row" name="open_ut" method="post" id="open_ut" autocomplete="off" action="quadrature.php" >

<?php //echo $username; 
//echo $_POST['ut'].'<br>';?>

<div class="form-group">

<label for="ut" style="margin-bottom: 1%;">Seleziona una UT</label>
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



<div class="form-group col-4">
<label for="data_percorsi" class="form-label">Data verifica</label>
      <div class="input-group">
      <button type="button" class="btn btn-outline-secondary" id="prevDay" title="Giorno precedente">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <input type="text" class="form-control" id="data_query" name="data_percorsi" onchange="cambiata_data(this.value);" value="<?php echo $today->format('d/m/Y');?>" required>
       <button type="button" class="btn btn-outline-secondary" id="nextDay" title="Giorno successivo">
        <i class="fa-solid fa-chevron-right"></i>
      </button>   
</div>

</div>
<script>

  function cambiata_data(val) {
    aggiornaBottoniNavigazione();
    console.log("Bottone cambiata_data  cliccato");
    var data_percorsi=val.split('/').reverse().join('');
    console.log(data_percorsi);
    var uos="<?php echo $_POST['ut']?>";
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
      url: "./tables/report_quadrature.php?ut="+uos+"&data="+data_percorsi+"&solo_squadrati="+getQuadrature()
    }); 

  });
};
</script>

<div class="col-4 d-flex align-items-center justify-content-end" style="align-content: center; ">
   <!--div class="col-md-6 d-flex align-items-center justify-content-end"-->
      <div class="form-check form-switch" style="padding-right: 5rem;">
          <input class="form-check-input" type="checkbox" id="flag_quad" name="flag_quad" onchange="flagCambiato(this)">
          <label class="form-check-label fw-bold ms-2" for="flag_quad">Mostra anche OK</label>
      </div>

    </div>




</div>

<script type="text/javascript">
  let quadrature = 't';

  function setQuadrature(val) {
    quadrature = val;
  }

  function getQuadrature() {
    return quadrature;
  }

  function flagCambiato(el) {
    if (el.checked) {
      setQuadrature('f');
      console.log(quadrature)
      console.log("Flag mostra anche quadrature ON");
      $(function() {    // Faccio refres della data-url
      $table.bootstrapTable('refresh', {
        url: "./tables/report_quadrature.php?ut=<?php echo $_POST['ut'];?>&data=<?php echo $today->format('Ymd');?>&solo_squadrati=f"
      }); 

    });
    } else {
      setQuadrature('t');
      console.log(quadrature)
      console.log("Flag mostra anche quadrature OFF");
      $(function() {    // Faccio refres della data-url
      $table.bootstrapTable('refresh', {
        url: "./tables/report_quadrature.php?ut=<?php echo $_POST['ut'];?>&data=<?php echo $today->format('Ymd');?>&solo_squadrati=t"
      }); 

    });
    }
  }
</script>
<!--hr-->




<div id="tabella">

        <div class="table-responsive-sm">
          
   
      <div id="toolbar"> 
        <!--button id="export-btn-filtered" class="btn btn-primary" title="Esporta file Excel filtrato"><i class="fa-solid fa-filter"></i>Esporta tabella filtrata</button-->
      </div>
        <table  id="quad" class="table-hover table-sm" 
        data-locale="it-IT"
        idfield="ID" 
        data-escape="false"
        data-show-columns="true"
        data-group-by="false"
        data-group-by-field='["indirizzo", "frazione"]'
        data-show-search-clear-button="true"   
        data-show-export="false" 
				data-search="false" 
        data-click-to-select="true" data-show-print="false"  
        data-virtual-scroll="false"
        data-show-pagination-switch="false"
				data-pagination="true" data-page-size=100 data-page-list=[10,25,50,75,100,200,500]
				data-side-pagination="false" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
        data-search-on-enter-key="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-export-data-type="all"
        data-url="./tables/report_quadrature.php?ut=<?php echo $_POST['ut'];?>&data=<?php echo $today->format('Ymd');?>&solo_squadrati=t"
        data-toolbar="#toolbar"
        data-show-footer="false"
        data-query-params="queryParams"
        >
        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th--> 
        <th data-field="ID" data-sortable="true" data-visible="false" data-filter-control="false">ID</th> 
        <th data-field="ID_UO_GEST" data-sortable="true" data-visible="false"  data-filter-control="input">ID UT</th>
        <th data-field="DESC_UO" data-sortable="true" data-visible="true" data-filter-control="input">UT</th>
        <th data-field="NOMINATIVO" data-sortable="true" data-visible="true" data-filter-control="input">Nominativo</th> 
        <th data-field="MATRICOLA" data-sortable="true" data-visible="true" data-filter-control="input">Matricola</th>
        <th data-field="DURATA_SERVIZIO_EKOVISION" data-sortable="true" data-visible="true" data-filter-control="false">Minuti<br>Ekovision</th>
        <th data-field="MINUTI_LAVORATI_ESIPERT" data-sortable="true" data-visible="true" data-filter-control="false">Minuti<br>lavorati</th>
        <th data-field="MINUTI_ASSENZE" data-sortable="true" data-visible="true" data-filter-control="input">Minuti<br>assenza</th>
        <th data-field="SERVIZI" data-sortable="false" data-visible="true" data-formatter="formatterServizi" data-filter-control="input">Servizi<br>Ekovision</th>
        <th data-field="ANOMALIA_MIN" data-sortable="true" data-visible="true" data-filter-control="input">Anomalia (min)</th>
    </tr>
</thead>
</table>










<script type="text/javascript">


var $table = $('#quad');

/*
function filterActive() {
  options = $('#quad').bootstrapTable('getOptions');
  console.log(options)
  const filters = options.filterControlValues || {};
  const hasFilters = Object.values(filters).some(val => val !== '' && val !== null);

  console.log(hasFilters)
  return hasFilters
};*/

$table.on('post-body.bs.table', function () {
  if ($('#export-btn-filtered').length === 0) {
    $('.fixed-table-toolbar .columns')
      .append('<button id="export-btn-filtered" class="btn btn-secondary ms-2" title="Esporta file Excel"><i class="bi bi-download"></i> Esporta tabella</button>');
  }
});


$(function() {
    $table.bootstrapTable();
    //console.log($table.bootstrapTable());
  });


  function queryParams(params) {
    const options = $table.bootstrapTable('getOptions')
    if (!options.pagination) {
      params.limit = options.totalRows
    }
    return params
  };



$(function() {
  initTableExport({
    tableId: "quad",
    exportAllBtn: "#export-btn",
    exportFilteredBtn: "#export-btn-filtered",
    baseUrl: "./tables/report_quadrature.php",
    extraParams: () => {
      // parametri extra della pagina
      const data_anomalia=$('#data_query').val().split('/').reverse().join('');
      return {
        ut: $("#ut").val() == 0 ? "" : $("#ut").val(),
        data: data_anomalia,
        solo_squadrati: getQuadrature()
      };
    }
  });
});


function formatterServizi(value) {
  if (!value) return '';
  return value.split(';').join(';<br>');
}

</script>


</div>	<!--tabella-->










</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>


<script type="text/javascript">




//Inizializzazione popover Bootstrap

  var popoverContent = `La pagina mostra le quadrature del personale in servizio, confrontando i 
  <i>minuti di servizio</i> rilevati da Ekovision con i <i>minuti lavorati</i> registrati su Esipert. 
  <br>Per ogni operatore, se sono presenti anomalie, viene evidenziata la differenza in minuti. 
  <br>Selezionando <i>Mostra anche OK</i>, vengono visualizzati anche gli operatori senza anomalie.`;


  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl, {
      html: true,
      content: popoverContent
    })
  })



  // Inizializzazione datepicker
 var today = new Date();
 var eko_birthday=new Date('2023-11-19');
$('#data_query').datepicker({
      format: 'dd/mm/yyyy',
      todayBtn: "linked", // in conflitto con startDate
      endDate:today,
      startDate:eko_birthday,
      language:'it', 
      autoclose: true
  });




// Funzione di aggiornamento pulsanti
function aggiornaBottoniNavigazione() {
  console.log('Sono dentro la funzione aggiornaBottoniNavigazione');
  var current = $('#data_query').datepicker('getDate');
  var today = new Date();


  var week_before=new Date();
  week_before.setDate(week_before.getDate() - 7);
  // azzera ore per confronto solo giorno/mese/anno
  current.setHours(0, 0, 0, 0);
  today.setHours(0, 0, 0, 0);
  week_before.setHours(0, 0, 0, 0);


  console.log(current.getTime());
  console.log(today.getTime());
  console.log(week_before.getTime());
  

  // Disabilita next se la data corrente è oggi
  if (current.getTime() === today.getTime()) {
    $('#nextDay').prop('disabled', true);
  } else {
    $('#nextDay').prop('disabled', false);
  }


// Disabilita prevDay se la data corrente è oggi
  if (current.getTime() === (week_before.getTime())) {
    $('#prevDay').prop('disabled', true);
  } else {
    $('#prevDay').prop('disabled', false);
  }
}




// Al cambio data (manuale o da datepicker)
$('#data_query').on('changeDate change', function () {
});



// Pulsante giorno precedente
$('#prevDay').on('click', function() {
  var current = $('#data_query').datepicker('getDate');
  if (current) {
    current.setDate(current.getDate() - 1);
    $('#data_query').datepicker('setDate', current).trigger('change');
  }
});

// Pulsante giorno successivo
$('#nextDay').on('click', function() {
  var current = $('#data_query').datepicker('getDate');
  if (current) {
    current.setDate(current.getDate() + 1);
    $('#data_query').datepicker('setDate', current).trigger('change');
  }
});

// Inizializza lo stato dei pulsanti al primo caricamento
aggiornaBottoniNavigazione();

  //$('#myIframe').attr('src', "https://expo.wingsoft.it/amiu/webapp/indexdesk.php?operatore=0170"); 
 


</script>

</body>

</html>