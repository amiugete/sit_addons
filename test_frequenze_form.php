
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
$freq_sit=32;

$fbin='000100011100';


?>




<div class="btn-group btn-group-sm" role="group" aria-label="Basic radio toggle button group">

  <input type="radio" class="btn-check" name="tipo_freq" id="Sett" autocomplete="off" 
  <?php if (substr($fbin,7,4)=='0000'){ echo 'checked';}?>
  >
  <label class="btn btn-outline-primary" for="Sett">Settimanale</label>

  <input type="radio" class="btn-check" name="tipo_freq" id="Mens" autocomplete="off"
  <?php if (substr($fbin,7,4)!='0000'){ echo 'checked';}?>
  >
  <label class="btn btn-outline-primary" for="Mens">Mensile</label>

  <input type="radio" class="btn-check" name="tipo_freq" id="Nulla" autocomplete="off"
  <?php if ($fbin== '000000000000'){ echo 'checked';}?>>
  <label class="btn btn-outline-primary" for="Nulla">Nulla</label>

</div>
<hr>

<?php


if ($fbin != '000000000000'){


if (substr($fbin, 7,4)!='0000'){ 
?>



<div id="freq_mensili">


    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" id="I" value="1"
      <?php if ($fbin[10]==1){ echo 'checked';}?>
      >
      <label class="form-check-label" for="inlineCheckbox1">Primo</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" id="II" value="1"
      <?php if ($fbin[9]==1){ echo 'checked';}?>
      >
      <label class="form-check-label" for="inlineCheckbox2">Secondo</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" id="III" value="1"
      <?php if ($fbin[8]==1){ echo 'checked';}?>
      >
      <label class="form-check-label" for="inlineCheckbox1">Terzo</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" id="IV" value="1"
      <?php if ($fbin[7]==1){ echo 'checked';}?>
      >
      <label class="form-check-label" for="inlineCheckbox2">Quarto</label>
    </div>

<hr>
</div>


<?php } // fine mensile?>

<div id="freq_settimanali">


<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" id="lu" value="1"
  <?php if ($fbin[6]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox1">Lu</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" id="ma" value="1"
  <?php if ($fbin[5]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox2">Ma</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" id="me" value="1"
  <?php if ($fbin[4]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox1">Me</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" id="gi" value="1"
  <?php if ($fbin[3]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox2">Gi</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" id="ve" value="1"
  <?php if ($fbin[2]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox1">Ve</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" id="sa" value="1"
  <?php if ($fbin[1]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox2">Sa</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" id="do" value="1"
  <?php if ($fbin[0]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox1">Do</label>
</div>


</div>




<hr>


<script type="text/javascript">

      // JavaScript
          const someCheckbox_tipo_freq_sett = document.getElementById('Sett');
          const someCheckbox_tipo_freq_mens = document.getElementById('Mens');
          const someCheckbox_tipo_freq_nulla = document.getElementById('Nulla');

          someCheckbox_tipo_freq_sett.addEventListener('change', e => {
            if(e.target.checked === true) {
              console.log("Checkbox sett is checked - boolean value: ", e.target.checked);
              $("#freq_mensili").hide();
              $("#freq_settimanali").show();
              $('#I').prop('checked', false);              
              $('#II').prop('checked', false);
              $('#III').prop('checked', false);
              $('#IV').prop('checked', false);
              return true;
            }     

            /*if(e.target.checked === false) {
              console.log("Checkbox is not checked - boolean value: ", e.target.checked);
              return true;
            }*/
          });




          someCheckbox_tipo_freq_mens.addEventListener('change', e => {
            if(e.target.checked === true) {
              console.log("Checkbox Mens is checked - boolean value: ", e.target.checked);
              $("#freq_mensili").show();
              $("#freq_settimanali").show();
              return true;
            }
          });



            someCheckbox_tipo_freq_nulla.addEventListener('change', e => {
            if(e.target.checked === true) {
              console.log("Checkbox Nulla is checked - boolean value: ", e.target.checked);
              $("#freq_mensili").hide();
              $("#freq_settimanali").hide();

              $('#lu').prop('checked', false);
              $('#ma').prop('checked', false);
              $('#me').prop('checked', false);
              $('#gi').prop('checked', false);
              $('#ve').prop('checked', false);
              $('#sa').prop('checked', false);
              $('#do').prop('checked', false);
              $('#I').prop('checked', false);              
              $('#II').prop('checked', false);
              $('#III').prop('checked', false);
              $('#IV').prop('checked', false);
              return true;
            }

            /*if(e.target.checked === false) {
              
              $('#').attr('disabled',true);
              $('#').prop('checked', false);
              return true;
            }*/
          });
</script>



<?php
}


/*for ($i = 0; $i <= 10; $i++) {
  echo $i;
  echo ' - ';
  echo $fbin[$i].'<br>';
}*/


?>


</div>
<?php 


 

require_once('req_bottom.php');
require('./footer.php');
?>





</body>

</html>