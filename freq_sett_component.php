<?php
// componente per radio button settimane
//echo 'fbin è: '.empty($fbin);
?>




<div class="btn-group btn-group-sm" role="group" aria-label="Basic radio toggle button group">

  <input type="radio" class="btn-check" name="tipo_freq" id="Sett" autocomplete="off" 
  <?php if (empty($fbin)==1 or substr($fbin,7,4)=='0000'){ echo 'checked';}?>
  >
  <label class="btn btn-outline-primary" for="Sett">Settimanale</label>

  <input type="radio" class="btn-check" name="tipo_freq" id="Mens" autocomplete="off"
  <?php if (empty($fbin)!=1 and substr($fbin,7,4)!='0000'){ echo 'checked';}?>
  >
  <label class="btn btn-outline-primary" for="Mens">Mensile</label>

  <input type="radio" class="btn-check" name="tipo_freq" id="Nulla" autocomplete="off"
  <?php if ($fbin== '000000000000'){ echo 'checked';}?>>
  <label class="btn btn-outline-primary" for="Nulla">Nulla</label>

</div>
<hr style="border-width:0;">

<?php


//if ($fbin != '000000000000'){


//if (substr($fbin, 7,4)!='0000'){ 
?>



<div id="freq_mensili">


    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="I" id="I" value="1"
      <?php if ($fbin[10]==1){ echo 'checked';}?>
      >
      <label class="form-check-label" for="inlineCheckbox1">Primo</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="II" id="II" value="1"
      <?php if ($fbin[9]==1){ echo 'checked';}?>
      >
      <label class="form-check-label" for="inlineCheckbox2">Secondo</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="III" id="III" value="1"
      <?php if ($fbin[8]==1){ echo 'checked';}?>
      >
      <label class="form-check-label" for="inlineCheckbox1">Terzo</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="IV" id="IV" value="1"
      <?php if ($fbin[7]==1){ echo 'checked';}?>
      >
      <label class="form-check-label" for="inlineCheckbox2">Quarto</label>
    </div>

<hr style="border-width:0;">
</div>


<?php //} // fine mensile?>

<div id="freq_settimanali">


<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" name="lu" id="lu" value="1"
  <?php if ($fbin[6]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox1">Lu</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" name="ma" id="ma" value="1"
  <?php if ($fbin[5]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox2">Ma</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" name="me" id="me" value="1"
  <?php if ($fbin[4]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox1">Me</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" name="gi" id="gi" value="1"
  <?php if ($fbin[3]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox2">Gi</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" name="ve" id="ve" value="1"
  <?php if ($fbin[2]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox1">Ve</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" name="sa" id="sa" value="1"
  <?php if ($fbin[1]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox2">Sa</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="checkbox" name="do" id="do" value="1"
  <?php if ($fbin[0]==1){ echo 'checked';}?>
  >
  <label class="form-check-label" for="inlineCheckbox1">Do</label>
</div>


</div>




<hr style="border-width:0;">





<?php
//}


/*for ($i = 0; $i <= 10; $i++) {
  echo $i;
  echo ' - ';
  echo $fbin[$i].'<br>';
}*/


?>




<div id="quindicinali">
  <div class="btn-group btn-group-sm" role="group" aria-label="Basic radio toggle button group">
    <input type="radio" class="btn-check btn-sm" name="freq_sett" id="T" value="T" autocomplete="off" 
    <?php if ($freq_sett=='T'){ echo 'checked';}?>
    >
    <label class="btn btn-outline-primary" for="T">ND</label>

    <input type="radio" class="btn-check btn-sm" name="freq_sett" value="P" id="P" autocomplete="off"
    <?php if ($freq_sett=='P'){ echo 'checked';}?>>
    <label class="btn btn-outline-primary" for="P">Solo settimane pari</label>

    <input type="radio" class="btn-check btn-sm" name="freq_sett" value="D" id="D" autocomplete="off"
    <?php if ($freq_sett=='D'){ echo 'checked';}?>>
    <label class="btn btn-outline-primary" for="D">Solo settimane dispari</label>
  </div>
    <br>
    <!--small> Vale solo per le frequenze settimanali, è automaticamente ignorato per quelle mensili 
      dove al contrario scelgo il giorno del mese sulla base delle prime 4 settimane del mese</small-->
</div>







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
              $("#quindicinali").show();
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
              $("#quindicinali").hide();
              $('#T').prop('checked', true);
              $('#P').prop('checked', false);
              $('#D').prop('checked', false);
              return true;
            }
          });



            someCheckbox_tipo_freq_nulla.addEventListener('change', e => {
            if(e.target.checked === true) {
              console.log("Checkbox Nulla is checked - boolean value: ", e.target.checked);
              $("#freq_mensili").hide();
              $("#freq_settimanali").hide();
              $("#quindicinali").hide();
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
              $('#T').prop('checked', true);
              $('#P').prop('checked', false);
              $('#D').prop('checked', false);
              return true;
            }

            /*if(e.target.checked === false) {
              
              $('#').attr('disabled',true);
              $('#').prop('checked', false);
              return true;
            }*/
          });
</script>