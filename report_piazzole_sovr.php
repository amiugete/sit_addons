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




<div class="container">

<?php 
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
//echo "Sono qua 3";

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

            <div class="row">

                  
  <!--div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div-->
      <table  id="piazzole_sovr" class="table-hover" 
        data-show-columns="true"
        data-show-search-clear-button="true"   
        data-show-export="true" 
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
        <th data-field="id_piazzola" data-sortable="true" data-visible="true" data-filter-control="input">Id<br>piazzola</th>
        <th data-field="id_elemento" data-sortable="true" data-visible="true"  data-filter-control="input">Id<br>Elemento</th>
        <th data-field="rif"  data-sortable="true" data-visible="true" data-filter-control="input">Rif</th>
        <th data-field="municipio"  data-sortable="true" data-visible="true" data-filter-control="select" data-filter-data="var:municipi_filtro">Municipio</th>
        <th data-field="comune"   data-sortable="true" data-visible="true" data-filter-control="select" data-filter-data="var:comuni_filtro" >Comune</th>
        <th data-field="eliminata"   data-sortable="true" data-visible="true" data-filter-control="select">Eliminata</th>
        <th data-field="anno"   data-sortable="true" data-visible="true" data-filter-control="select" data-filter-data="var:anni_filtro">Anno</th>
        <th data-field="n_ispezioni_anno"   data-sortable="true" data-visible="true" data-filter-control="input">Numero<br>ispezioni<br>anno</th>
    </tr>
</thead>
</table>




<script type="text/javascript">



  var $table = $('#piazzole_sovr');
  
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