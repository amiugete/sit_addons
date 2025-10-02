<?php

//$id=pg_escape_string($_GET['id']);
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

    <title>Ricerca utenze</title>
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

//************************************************************************************ */
// Controllo permessi
if (trim($check_utenze) != 't') { 
  require('./assenza_permessi.php');
  exit;
}
//************************************************************************************ */

?>

<div class="banner"> <div id="banner-image"></div> </div>
      <div class="container">
      

            <h2> Utenze Piazzole <i class="fas fa-users"></i> </h2>
            <hr>
            <h5> Disegnare l'area sulla mappa prima di inviare la richiesta di estrazione utenze</h5>
            <div class="row">
              <iframe src="https://amiugis.amiu.genova.it/mappe/lizmap/www/index.php/view/map?repository=repository1&project=utenze_piazzole_iframe" 
              title="Disegna area per estrazione utenze\" style="height:500px;" allowfullscreen></iframe>
            </div>
              <hr>  
            <!--form name="openfile" method="post" autocomplete="off" action="<?php echo $_SERVER['PHP_SELF'] ?>" -->
            <form name="openfile" method="post" autocomplete="off" action="./backoffice/utenze_aree_output.php" >

            <div class="row">
            
            

            <div class="col-md-4"> 
            <div class="form-group">
            <label for="eco">Area:</label> <font color="red">*</font>
            <select class="form-control" name="eco" id="eco">
            <option name="naz" value="" > Scegli un'area </option>
            <?php            
            //$query2="SELECT * From etl.aree_4326 where data_disegno::date>=(NOW() - INTERVAL '30' DAY) order by data_disegno desc;";
            $query2= "SELECT id,
                      CASE 
                        WHEN ecopunto = true THEN 'ecopunto_' || nome
                        ELSE nome
                      END AS nome,
                      data_disegno
                      from etl.aree_4326 where (data_disegno::date>=(NOW() - INTERVAL '30' DAY) and ecopunto is not true) or 
                      ecopunto = true order by data_disegno desc;";
	          $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
            ?>    
                    <option name="eco" value="<?php echo $r2['id'];?>" ><?php echo $r2['nome']. "(".$r2['data_disegno']. ")";?></option>
             <?php } ?>
             </select>
                
             </div>
            </div>

            
            <div class="col-md-4"> 
            <div class="form-group">
                <label for="via">Utenze:</label> <font color="red">*</font>
                <!--select name="via-list" id="via-list" class="selectpicker show-tick form-control" 
                data-live-search="true" onChange="getCivico(this.value);" required=""-->
                <select name="ute-list" id="ute-list" class="selectpicker show-tick form-control" 
                data-live-search="true" required="">

                <option value="">Seleziona le utenze</option>
                <option name="ute" value="ute" >Utenze domestiche E non domestiche</option>
                <option name="uted" value="uted" >Solo utenze domestiche</option>
                <option name="utend" value="utend" >Solo utenze NON domestiche</option>

                </select>
            </div>
            </div>


            <!--div class="col-md-2 d-flex justify-content-center align-items-center">

            <div class="form-group  ">
              <label for="ecop">Ecopunto?</label>
            <input class="form-check-input" type="checkbox" value="ecop" name="ecop" id="ecop">
            </div>
            </div-->

            
            <div class="col-md-4 d-flex justify-content-center align-items-end">

            <div class="form-group  ">
            <button type="submit" name="submit" id=submit value="invia_utenze" class="btn btn-success"><i class="fa-solid fa-file-arrow-down"></i>Sacrica utenze</button>
            </div>
            </div>
            </div>
            </form>


</div>


</div>

<?php
require_once('req_bottom.php');
require('./footer.php');
?>

<script>
function refreshSelect() {
  let select = document.getElementById("eco");
  let selectedValue = select.value; // salvo l'opzione selezionata

  fetch("./index_aree_refresh_select.php")
    .then(response => response.text())
    .then(data => {
      select.innerHTML = data;

      // ripristino la selezione se ancora valida
      if (selectedValue) {
        let optionToSelect = select.querySelector("option[value='" + selectedValue + "']");
        if (optionToSelect) {
          optionToSelect.selected = true;
        }
      }
    });
}

// refresh ogni 10 secondi
setInterval(refreshSelect, 5000);
</script>


</body>

</html>