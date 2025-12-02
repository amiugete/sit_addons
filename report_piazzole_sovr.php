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

// usato dai filtri sotto
require_once('./tables/query_piazzole_sovr.php');

?>




<div class="container-fluid">


<?php 
// time_update
$query_lr="select to_char(last_refresh, 'DD/MM/YYYY HH24:MI') as time_update
from sovrariempimenti.mv_report_piazzole_da_analizzare limit 1";


$result = pg_prepare($conn_sovr, "query_lr", $query_lr);

if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 2";
$result = pg_execute($conn_sovr, "query_lr", array());  
if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 3";


while($r = pg_fetch_assoc($result)) {
    $time_update= $r['time_update'];
}




// filtro comuni
$query_comuni="select distinct comune from 
( ".$query_ps.") e
order by comune";


$result = pg_prepare($conn_sovr, "query_comuni", $query_comuni);

if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 2";
$result = pg_execute($conn_sovr, "query_comuni", array());  
if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
#echo "Sono qua 3";

?>
<script>
  var comuni_filtro = {
<?php
while($r = pg_fetch_assoc($result)) {
    echo '"'.$r["comune"].'":"'.$r["comune"].'",';
}
?> 
}
</script>



<?php 
// filtro municipi
$query_municipi="select distinct municipio from 
( ".$query_ps.") e
order by municipio";


$result = pg_prepare($conn_sovr, "query_municipi", $query_municipi);

if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 2";
$result = pg_execute($conn_sovr, "query_municipi", array());  
if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 3";

?>
<script>
  var municipi_filtro = {
<?php
while($r = pg_fetch_assoc($result)) {
    echo '"'.$r["municipio"].'":"'.$r["municipio"].'",';
}
?> 
}
</script>


<?php 
// filtro anno
$query_anni="select distinct anno from 
( ".$query_ps.") e
order by anno";


$result = pg_prepare($conn_sovr, "query_anni", $query_anni);

if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 2";
$result = pg_execute($conn_sovr, "query_anni", array());  
if (!pg_last_error($conn_sovr)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sovr);
    $res_ok= $res_ok+1;
}
//echo "Sono qua 3";

?>
<script>
  var anni_filtro = {
<?php
while($r = pg_fetch_assoc($result)) {
    echo '"'.$r["anno"].'":"'.$r["anno"].'",';
}
?> 
}
</script>



<!--script>
var comuni_filtro = {
  "202401": "2024/01",
  "202402": "2024/02",
  "202403": "2024/03",
  "202404": "2024/04",
  "202405": "2024/05",
  "202406": "2024/06",
  "202407": "2024/07",
  "202408": "2024/08",
  "202409": "2024/09",
  "202410": "2024/10",
  "202411": "2024/11",
  "202412": "2024/12",
};
</script-->
 


<div id="tabella_piazzole_sovr">
            
        <h4>Elenco piazzola da ispezionare</h4>

        <a class="btn btn-info btn-sm" href="./export_piazzole_sovr.php"> <i class="fa-solid fa-download"></i> Download </a>
        
      <?php echo ' - Ultimo aggiornamento: '.$time_update;?>  
      <a id="btn_fefresh" class="btn btn-info btn-sm"> <i class="fa-solid fa-arrows-rotate"></i> Aggiorna dati </a>
      <div id="result_refresh"></div>


<!-- lancio il form e scrivo il risultato -->
<script> 
            $(document).ready(function () {                 
                $('#btn_fefresh').click(function (event) { 
                    console.log('Bottone refresh elemento cliccato e finito qua');
                    //event.preventDefault();                  
                    $.ajax({ 
                        url: 'backoffice/refresh_mv_sovr.php', 
                        type: 'POST', 
                        //data: {}, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                            // mostro il messaggio 
                            $("#result_refresh").html(response).fadeIn("slow");
                            // refresh tabella
                            $table.bootstrapTable('refresh', {
                              url: "./tables/report_piazzole_sovr.php"
                            });   
                            console.log('refresh fatto');
                                      
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                          $("#msg").text("❌ Errore AJAX: " + xhr.statusText);
                          console.error(errorThrown); 
                        } 
                    }); 
                });
              }); 

        </script>
            <div class="row">

                  
  <!--div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div-->
      <table  id="piazzole_sovr" class="table-hover table-sm" 
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
        data-url="./tables/report_piazzole_sovr.php" 
        data-toolbar="#toolbar" 
        data-show-footer="false"
        >

<!--Per i filtri guardare report_fascia_oraria_esecuzione -->

        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="comune"   data-sortable="true" data-visible="true" data-filter-control="select" data-filter-data="var:comuni_filtro" >Comune</th>
        <th data-field="id_piazzola" data-sortable="true" data-visible="true" data-filter-control="input">Id<br>piazzola</th>
        <th data-field="id_elemento" data-sortable="true" data-visible="true"  data-filter-control="input">Id<br>Elemento</th>
        <th data-field="rif"  data-sortable="true" data-visible="true" data-filter-control="input">Rif</th>
        <th data-field="municipio"  data-sortable="true" data-visible="true" data-filter-control="select" data-filter-data="var:municipi_filtro">Mun</th>
        <th data-field="eliminata"   data-sortable="true" data-visible="true" data-filter-control="select">Eliminata</th>
        <th data-field="segnalazioni"   data-sortable="true" data-visible="true" data-filter-control="select">Segnalazioni</th>
        <th data-field="elementi"   data-sortable="true" data-visible="true" data-filter-control="select">Elementi<br>(al 31/12)</th>
        <th data-field="percorsi"   data-sortable="true" data-visible="true" data-filter-control="select">Percorsi<br>(al 31/12)</th>
        <th data-field="anno"   data-sortable="true" data-visible="true" data-filter-control="select" data-filter-data="var:anni_filtro">Anno</th>
        <th data-field="n_ispezioni_anno_previste"   data-sortable="true" data-visible="true" data-filter-control="input">Numero<br>ispezioni<br>prev</th>
        <th data-field="n_ispezioni_anno_effettuate"   data-sortable="true" data-visible="true" data-filter-control="input">Numero<br>ispezioni<br>effettuate</th>
    </tr>
</thead>
</table>




<script type="text/javascript">



  var $table = $('#piazzole_sovr');

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



function dateFormat(value, row, index) {
   if (value){ 
    return moment(value).format('MMM YYYY');
   } else {
    return '-';
   }
};



function realFormat(value, row, index) {
   if (value){ 
    return parseFloat(value);
   } else {
    return '-';
   }
};
 
function realFormat_pc(value, row, index) {
   if (value){ 
    return parseFloat(value)+'%';
   } else {
    return '-';
   }
};

$(function() {
  initTableExport({
    tableId: "piazzole_sovr",
    exportAllBtn: "#export-btn",
    exportFilteredBtn: "#export-btn-filtered",
    baseUrl: "./tables/report_piazzole_sovr.php"
  });
});


</script>


</div>
</div>






</div>

<?php
require_once('req_bottom.php');
require_once('./footer.php');
?>



</body>

</html>