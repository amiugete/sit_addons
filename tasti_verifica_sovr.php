<?php 
// parte do codice per le verifiche
?>

<div class="col-12">
      <div class="form-check">
        <input type="checkbox" class="btn-check btn-sm" id="<?php echo $re['id_elemento'];?>" name="<?php echo $re['id_elemento'];?>" 
         checked autocomplete="off">
        <label class="btn btn-outline-primary  btn-sm" id="<?php echo $re['id_elemento'];?>_ver"  for="<?php echo $re['id_elemento'];?>">Verificato</label>
  
        <input type="checkbox" class="btn-check btn-sm" name="<?php echo $re['id_elemento'];?>_sovr" id="<?php echo $re['id_elemento'];?>_sovr" autocomplete="off">
        <label class="btn btn-outline-danger btn-sm" id="<?php echo $re['id_elemento'];?>_lsovr" for="<?php echo $re['id_elemento'];?>_sovr">Non sovrariempito</label>

      </div>
      </div>
      
      
      <script type="text/javascript">

      // JavaScript
          const someCheckbox_<?php echo $re['id_elemento'];?> = document.getElementById('<?php echo $re['id_elemento'];?>');

          someCheckbox_<?php echo $re['id_elemento'];?>.addEventListener('change', e => {
            if(e.target.checked === true) {
              console.log("Checkbox is checked - boolean value: ", e.target.checked);
              document.getElementById("<?php echo $re['id_elemento'];?>_ver").innerHTML = 'Verificato';
              $('#<?php echo $re['id_elemento'];?>_sovr').removeAttr('disabled');
              return true;
            }
          if(e.target.checked === false) {
              console.log("Checkbox is not checked - boolean value: ", e.target.checked);
              $('#<?php echo $re['id_elemento'];?>_ver').innerHTML = 'Non presente';
              document.getElementById("<?php echo $re['id_elemento'];?>_ver").innerHTML = 'Non presente';
              document.getElementById("<?php echo $re['id_elemento'];?>_lsovr").innerHTML = 'Non sovrariempito';
              $('#<?php echo $re['id_elemento'];?>_sovr').attr('disabled',true);
              $('#<?php echo $re['id_elemento'];?>_sovr').prop('checked', false);
              return true;
            }
          });


          const someCheckbox_sovr_<?php echo $re['id_elemento'];?> = document.getElementById('<?php echo $re['id_elemento'];?>_sovr');

          someCheckbox_sovr_<?php echo $re['id_elemento'];?>.addEventListener('change', e1 => {
            if(e1.target.checked === true) {
              document.getElementById("<?php echo $re['id_elemento'];?>_lsovr").innerHTML = 'Sovrariempito';
              return true;
            }
            if(e1.target.checked === false) {
              document.getElementById("<?php echo $re['id_elemento'];?>_lsovr").innerHTML = 'Non sovrariempito';
              return true;
            }
          });



        </script>
<?php //fine ?>