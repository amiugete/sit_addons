<?php ?>
<script>
  function utScelta(val) {
    document.getElementById('open_ut').submit();
  }


</script>




<div class="row align-items-center g-2">

<form name="open_ut" method="post" id="open_ut" autocomplete="off"
        action="<?php echo basename($_SERVER['PHP_SELF']);?>"
        class="col-md-6 d-flex align-items-center flex-wrap">
<?php //echo $username;?>

<div class="form-group me-2 mb-4">
  <select class="selectpicker show-tick form-control" 
  data-live-search="true" name="ut0" id="ut0" onchange="utScelta(this.value);" required="">
  
  <?php 
  if ($_POST['ut0']) {
    $query0='select u.id_ut, u.descrizione, cmu.id_uo
    from topo.ut u
    left join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo_sit = u.id_ut 
    where id_ut = $1';

    $result0 = pg_prepare($conn, "my_query0", $query0);
    $result0 = pg_execute($conn, "my_query0", array($_POST['ut0']));
    
    while($r0 = pg_fetch_assoc($result0)) {
        $uos=$r0["id_uo"]; 
  ?>    
        
          <option name="ut0" value="<?php echo $_POST['ut0'];?>" ><?php echo $r0['descrizione']?></option>
  <?php }
  pg_free_result($result0); 
  } else{
  ?>
    <option name="ut0" value="0">Seleziona una UT</option>
  
  
  <?php            
  }

  
  require_once('query_ut.php');

  //echo "<br>". $query1;


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
  <!--small>L'elenco delle piazzole..  </small-->        
</div>
<div class="form-group mb-2">
  <a class="btn btn-primary" href="<?php echo basename($_SERVER['PHP_SELF']);?>">Tutte le mie UT</a>
</div>


</form>



 <div class="col-md-6 d-flex align-items-center justify-content-end">
<!--label for="js-date_attivi" class="me-4 mb-0">Attivi al</label>
<input type="text" class="form-control w-auto" id="js-date_attivi" name="data_percorsi" value="<?php echo $today->format('d/m/Y');?>" required-->
<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" id="flag_attivi" name="flag_attivi" onchange="flagCambiato(this)">
    <label class="form-check-label fw-bold ms-2" for="flag_attivi">Mostra anche versioni dismesse</label>
  </div>

</div>


<script type="text/javascript">
  let stato_versioni = 't';

  function setStatoVersioni(val) {
    stato_versioni = val;
  }

  function getStatoVersioni() {
    return stato_versioni;
  }

  function flagCambiato(el) {
    if (el.checked) {
      setStatoVersioni('f');
      console.log(stato_versioni)
      console.log("Flag disattivi ON");
      $(function() {    // Faccio refres della data-url
      $table.bootstrapTable('refresh', {
        url: "./tables/percorsi_raggruppati.php?ut=<?php echo $_POST['ut0'];?>&solo_attivi=f"
      }); 

    });
    } else {
      setStatoVersioni('t');
      console.log(stato_versioni)
      console.log("Flag disattivi OFF");
      $(function() {    // Faccio refres della data-url
      $table.bootstrapTable('refresh', {
        url: "./tables/percorsi_raggruppati.php?ut=<?php echo $_POST['ut0'];?>&solo_attivi=t"
      }); 

    });
    }
  }
</script>

  </div>
  
  <hr>

  <?php ?>