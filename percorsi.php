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
?>


<div class="container">


<script>
  function utScelta(val) {
    document.getElementById('open_ut').submit();
  }


</script>

<div class="rfix">

<form class="row" name="open_ut" method="post" id="open_ut" autocomplete="off" action="percorsi.php" >




<?php //echo $username;?>

<div class="form-group col-lg-4">
  <select class="selectpicker show-tick form-control" 
  data-live-search="true" name="ut" id="ut" onchange="utScelta(this.value);" required="">
  
  <?php 
  if ($_POST['ut']) {
    $query0='select id_ut, descrizione
    from topo.ut where id_ut = $1';

    $result0 = pg_prepare($conn, "my_query0", $query0);
    $result0 = pg_execute($conn, "my_query0", array($_POST['ut']));
    
    while($r0 = pg_fetch_assoc($result0)) { 
  ?>    
          <option name="ut" value="<?php echo $_POST['ut'];?>" ><?php echo $r0['descrizione']?></option>
  <?php }
  pg_free_result($result0); 
  } else{
  ?>
    <option name="ut" value="NO">Seleziona una UT</option>
  
  
  <?php            
  }

  
  $query1='select id_ut, descrizione
  from topo.ut where id_ut in   
  (select 
    id_ut
    from util.sys_users_ut suu where id_user in (
        select id_user from util.sys_users su where "name" ILIKE $1
  )   
  and id_ut > 0
  union 
  select 
  u.id_ut 
    from util.sys_users_ut suu, topo.ut u
    where suu.id_user in (
        select id_user from util.sys_users su where "name" ILIKE $1
  )   
  and suu.id_ut = -1
  ) and id_ut in (select id_uo_sit from anagrafe_percorsi.cons_mapping_uo)
  order by 2';

  //echo "<br>". $query;


  $result1 = pg_prepare($conn, "my_query1", $query1);
  $result1 = pg_execute($conn, "my_query1", array($_SESSION['username']));

  while($r1 = pg_fetch_assoc($result1)) { 
?>    
        <option name="ut" value="<?php echo $r1['id_ut'];?>" ><?php echo $r1['descrizione']?></option>
<?php 
  }
  pg_free_result($result1); 
?>

  </select>  
  <!--small>L'elenco delle piazzole..  </small-->        
</div>
<div class="form-group col-lg-4">
<a class="btn btn-primary" href="./percorsi.php">Tutte le UT</a>
</div>
  </form>

  </div>
  
  <hr>


<div id="tabella">
            
        <h4>Elenco percorsi</h4>




            <div class="row">

                  <!--div id="toolbar">
        <button id="showSelectedRows" class="btn btn-primary" type="button">Crea ordine di lavoro</button>
      </div-->
    
      <!--div id="toolbar" class="select">
  <select class="form-control">
    <option value="all">Export All</option>
    <option value="">Export Basic</option>
    <option value="selected">Export Selected</option>
  </select>
</div-->
  <div id="toolbar"> Per esportare i dati completi rimuovere la paginazione (primo tasto dopo la ricerca)
</div>
				<table  id="percorsi" class="table-hover" 
        idfield="id" 
        data-toolbar="#toolbar" 
        data-group-by="false"
        data-group-by-field='["cod_percorso", "descrizione", "famiglia", "tipo"]'
        data-show-search-clear-button="true"   
        data-show-export="true" 
        data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
				data-search="true" data-click-to-select="true" data-show-print="false"  
				data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
        data-show-pagination-switch="true"
				data-side-pagination="false" 
        data-search-on-enter-key="true"  
        data-remember-order="true"
        data-search-highlight = "true" 
        data-show-refresh="true" data-show-toggle="true"
        data-show-columns="true"
				data-filter-control="true"
        data-sort-select-options = "true"
        data-filter-control-multiple-search="false"
        data-query-params="queryParams"
        data-url="./tables/percorsi_raggruppati.php?ut=<?php echo $_POST['ut'];?>"-->
        

        
<thead>



 	<tr>
        <!--th data-checkbox="true" data-field="id"></th-->  
        <!--th data-field="state" data-checkbox="true" ></th-->  
        <th data-field="famiglia" data-sortable="true" data-visible="true" data-filter-control="select">Famiglia</th>
        <th data-field="tipo" data-sortable="true" data-visible="true"  data-filter-control="select">Tipo</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">UT</th> 
        <th data-field="cod_percorso" data-sortable="true" data-visible="true" data-filter-control="input">Codice</th>
        <th data-field="descrizione" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th>
        <th data-field="freq" data-sortable="true" data-visible="true" data-filter-control="input">Frequenza</th>
        <th data-field="turno" data-sortable="true" data-visible="true" data-filter-control="select">Turno</th>
        <th data-field="versione" data-sortable="true" data-visible="true" data-filter-control="select">Versione</th>
        <th data-field="flg_disattivo" data-sortable="true" data-visible="true" data-formatter="nameFormatterAtt" 
        data-filter-strict-search="true" data-search-formatter="false" data-filter-data="var:opzioni" data-filter-control="select"></th>
        <th data-field="cp_edit" data-sortable="false" data-formatter="nameFormatterEdit" data-visible="true" >Edit</th>
        <!--th data-field="quartiere" data-sortable="true" data-visible="true" data-filter-control="select">Quartiere<br>/Comune</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-filter-control="select">UT</th>
        <th data-field="tipo" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>apertura</th>
        <th data-field="ut" data-sortable="true" data-visible="true" data-formatter="dateFormatter" data-filter-control="input">Data<br>chiusura</th>
        <th data-field="desc_intervento" data-sortable="true" data-visible="true" data-filter-control="input">Descrizione</th-->
    </tr>
</thead>
</table>


<script type="text/javascript">



  var $table = $('#percorsi')
  
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




 


    var opzioni = ['Attivo', 'Disattivo'] 

    function nameFormatterAtt(value) {
      if (value =='Attivo'){
        return '<span style="font-size: 1em; color: green;"> <i title='+value+' class="fa-solid fa-play"></i></span>';
      } else if (value =='Disattivo') {
        return '<span style="font-size: 1em; color: Tomato;"> <i title='+value+' class="fa-solid fa-stop"></i></span>'
      }
    }

    function nameFormatterEdit(value, row) {
        
        return '<a class="btn btn-warning" href="./dettagli_percorso.php?cp='+row.cod_percorso+'&v='+row.versione+'"><i class="fa-solid fa-pencil"></i></a>';
     
        }

</script>


</div>	<!--tabella-->










</div>

<?php
require_once('req_bottom.php');
require_once('./footer.php');
?>





</body>

</html>