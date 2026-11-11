<div class="row g-3 align-items-stretch" id="row-descr-nota"><!-- I RIGA -->
  <!-- INIZIO FORM DESCRIZIONE -->
  <?php if($check_versione_successiva==0 and $check_edit==1 and $flg_disattivo==0){?>
    <div class="col-md-6">
      <form class="row-cols-lg-auto g-3 align-items-center" name="form_desc" id="form_desc" autocomplete="off">
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
        <div class="row align-items-end">
          <div class="col-md-11" >
            <label class="form-check-label" for="desc"><b>Descrizione</b></label>
            <input type="text" name="desc" id="desc" maxlength="auto" class="form-control" value="<?php echo $descr_percorso;?>" required="">
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary" title="salva descrizione">
              <i class="fa-solid fa-floppy-disk"></i>
            </button>
          </div>
        </div> 
      </form>
    </div>
    <!-- lancio il form e scrivo il risultato -->
    <!--p><div id="results_desc"></div></p-->
    <script> 
      $(document).ready(function () {                 
        $('#form_desc').submit(function (event) { 
          event.preventDefault();                  
          console.log('Bottone form dd cliccato e finito qua');
          var formData = $(this).serialize();
          console.log(formData);
          $.ajax({ 
            url: 'backoffice/update_descrizione.php', 
            method: 'POST', 
            data: formData,
            dataType: 'json',
            //processData: true, 
            //contentType: false, 
            success: function (response) {                       
                //alert('Your form has been sent successfully.'); 
                console.log(response);
                //$("#results_desc").html(response).fadeIn("slow");
                showToast(response.messaggio, 'success');
            }, 
            error: function (jqXHR, textStatus, errorThrown) {
              const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio della descrizione!';                        
                //console.error(errorThrown);
                //console.error(jqXHR.responseText);

                showToast(messaggio, 'error');
            } 
          }); 
        }); 
      }); 
    </script>
  <?php } else {?>
    <div class="col-md-6">
        <label class="form-check-label" for="desc"><b>Descrizione</b></label>
        <input type="text" name="desc" id="desc" class="form-control" value="<?php echo $descr_percorso;?>" readonly="">
    </div>
  <?php }
  // INIZIO FORM NOTA
  if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $flg_disattivo==0 and $versione > 1){?>
    <div class="col-md-6">  
      <form class="row-cols-lg-auto g-3 align-items-center" name="form_nota" id="form_nota" autocomplete="off">
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
        <div class="row align-items-end">
          <div class="col-md-11" >
            <label class="form-check-label" for="nota_vers"><b>Note versione</b></label>
            <input type="text" name="nota_vers" id="nota_vers" class="form-control" value="<?php echo $nota_versione;?>" required="">
          </div>
          <div class="col-md-1 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" title="salva nota">
              <i class="fa-solid fa-floppy-disk"></i>
            </button>
          </div>
        </div> 
      </form>
    </div>    
    <!-- lancio il form e scrivo il risultato -->
    <!--p><div id="results_nota"></div></p-->
    <script> 
      $(document).ready(function () {                 
        $('#form_nota').submit(function (event) { 
          event.preventDefault();                  
          console.log('Bottone form nota cliccato e finito qua');
          var formData = $(this).serialize();
          console.log(formData);
          $.ajax({ 
            url: 'backoffice/update_nota.php', 
            method: 'POST', 
            data: formData,
            dataType: 'json',
            //processData: true, 
            //contentType: false, 
            success: function (response) {                       
              //alert('Your form has been sent successfully.'); 
              console.log(response);
              //$("#results_nota").html(response).fadeIn("slow");
              showToast(response.messaggio, 'success');
            }, 
            error: function (jqXHR, textStatus, errorThrown) {                        
              const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio della nota!';                        
                //console.error(errorThrown);
                //console.error(jqXHR.responseText);

                showToast(messaggio, 'error');
            } 
          }); 
        }); 
      }); 
    </script>
  <?php 
  } else {
    if ($nota_versione != '' and $versione > 1){ ?>
      <div class="col-md-6">
          <label class="form-check-label" for="nota_vers"><b>Nota versione</b></label>
          <input type="text" name="nota_vers" id="nota_vers" class="form-control" value="<?php echo $nota_versione;?>" readonly="">
      </div>
  <?php }                  
  } ?>
</div>
<div class="row g-3 align-items-stretch" id="row-turno-freq"><!-- II RIGA -->
  <!-- INIZIO FORM TURNO -->
  <?php if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $flg_disattivo==0){?>
  <div class="col-md-6">
    <form class="row-cols-lg-auto g-3 align-items-center" name="form_turno" id="form_turno" autocomplete="off" >
      <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
      <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
      <div class="row align-items-end">
        <div class="col-md-11">
          <label class="form-check-label" for="desc"><b>Turno</b></label>
          <select name="turno" id="turno" class="form-select" data-live-search="true"  required="">
            <?php            
              $query2bis="SELECT ID_TURNO, 
              concat(concat(codice_turno, ' --> '), DESCR_ORARIO) AS DESCR
              FROM ANAGR_TURNI at2 
              WHERE DTA_DISATTIVAZIONE > SYSDATE 
              ORDER BY inizio_ora, inizio_minuti, fine_ora,fine_minuti";
              $result2bis = oci_parse($oraconn, $query2bis);
              oci_execute($result2bis);
              while($r2bis = oci_fetch_assoc($result2bis)) { 
                  //$valore=  $r2['id_via']. ";".$r2['desvia'];            
            ?>
              <option name="turno" <?php if ($r2bis['ID_TURNO']==$r["id_turno"]) { echo 'selected '; }?>
                  value="<?php echo $r2bis['ID_TURNO']?>" ><?php echo $r2bis['DESCR'];?></option>
            <?php } 
              oci_free_statement($result2bis);
            ?>
          </select> 
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary" title="salva turno">
            <i class="fa-solid fa-floppy-disk"></i>
          </button>
        </div>
      </div>
    </form>
  </div>   
  <!--p><div id="results_turno"></div></p-->
  <script> 
    $(document).ready(function () {                 
      $('#form_turno').submit(function (event) { 
        console.log('Bottone form dd cliccato e finito qua');
        event.preventDefault();                  
        var formData = $(this).serialize();
        console.log(formData);
        $.ajax({ 
          url: 'backoffice/update_turno.php', 
          method: 'POST', 
          data: formData,
          dataType: 'json',
          //processData: true, 
          //contentType: false, 
          success: function (response) {                       
              //alert('Your form has been sent successfully.'); 
              console.log(response);
              //$("#results_turno").html(response).fadeIn("slow");
              showToast(response.messaggio, 'success');
          }, 
          error: function (jqXHR, textStatus, errorThrown) {                        
              const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio del turno!';                        
                //console.error(errorThrown);
                //console.error(jqXHR.responseText);

                showToast(messaggio, 'error');
          } 
        }); 
      }); 
    }); 
  </script>
  <?php } else {?>
    <div class="col-md-6">
      <label class="form-check-label" for="desc"><b>Turno</b></label>
      <input type="text" name="desc" id="desc" class="form-control" value="<?php echo $cod_turno;?>" readonly="">
    </div>
  <?php } ?>
  <!-- FREQUENZA E DURATA PER ORA IN SOLA LETTURA -->
  <div class="col-md-6">
    <div class="row">
      <div class="col-md-8">
        <label class="form-check-label" for="desc"><b>Frequenza <?php echo $tipo_frequenza; ?></b></label>
        <input type="text" name="desc" id="desc" class="form-control" value="<?php echo $descrizione_long;?>" readonly="">
      </div>
      <div class="col-md-4">
        <label class="form-check-label" for="desc"><b>Durata</b></label>
        <input type="text" name="desc" id="desc" class="form-control" value="<?php echo $durata;?>" readonly="">
      </div>
    </div>
  </div>
</div>
<div class="row g-3 align-items-stretch" id="row-data-att-dis"><!-- III RIGA -->
  <!-- INIZIO FORM DATA ATTIVAZIONE -->
  <?php if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $flg_disattivo==0){?>
  <div class="col-md-6">
    <form class="row-cols-lg-auto g-3 align-items-center" name="form_da" id="form_da" autocomplete="off" >
      <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
      <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
      <div class="row align-items-end">
        <div class="col-md-11">
          <label class="form-check-label" for="data_att"><b>Data attivazione (inclusa)</b></label>
          <input name="data_att" id="js-date1" type="text" class="form-control" value="<?php echo $data_inizio_print;?>" required="">
          <div class="input-group-addon">
            <span class="glyphicon glyphicon-th"></span>
          </div>
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary" title="salva data attivazione">
              <i class="fa-solid fa-floppy-disk"></i>
          </button>
        </div>
      </div> 
    </form>
  </div>
  <script> 
    $(document).ready(function () {                 
      $('#form_da').submit(function (event) { 
        console.log('Bottone form dd cliccato e finito qua');
        event.preventDefault();                  
        var formData = $(this).serialize();
        console.log(formData);
        $.ajax({ 
            url: 'backoffice/update_data_attivazione.php', 
            method: 'POST', 
            data: formData,
            dataType: 'json',
            //processData: true, 
            //contentType: false, 
            success: function (response) {                       
                //alert('Your form has been sent successfully.'); 
                console.log(response);
                showToast(response.messaggio, 'success');
            }, 
            error: function (jqXHR, textStatus, errorThrown) {                        
                const messaggio = jqXHR.responseJSON?.messaggio 
                  || 'Errore durante il salvataggio della data di attivazione!';                        
                //console.error(errorThrown);
                //console.error(jqXHR.responseText);

                showToast(messaggio, 'error'); 
            } 
        }); 
      }); 
    }); 
  </script>
  <?php } else {?>
    <div class="col-md-6">
      <label class="form-check-label" for="data_att"><b>Data attivazione (inclusa)</b></label>
      <input type="text" name="data_att" id="data_att" class="form-control" value="<?php echo $data_inizio_print;?>" readonly="">
    </div>
  <?php }
    // INIZIO FORM DATA DISATTIVAZIONE
  if ($check_versione_successiva==0 and $check_superedit==1 and $flg_disattivo==0){ ?>
    <div class="col-md-6">
      <form class="row-cols-lg-auto g-3 align-items-center" name="form_dd" id="form_dd">
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
        <div class="row align-items-end">
          <label class="form-check-label" for="data_att"><b>Data disattivazione (esclusa <small>ovvero nella data indicata il percorso non ci sarà già più</small>)</b></label>
          <div class="col-md-11 align-items-center" style="display: inline-flex; white-space:nowrap;">
            <input name="data_disatt" id="js-date2" type="text" class="form-control" value="<?php echo $data_disattivazione_testata;?>" required="">
            <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
            </div>
            <button type="button" id="btnTomorrow" class="btn btn-info btn-sm" title="Imposta a domani"><i class="fa-solid fa-calendar-day"></i></button>
            <button type="button" id="btnInf" class="btn btn-info btn-sm" title="Imposta all'infinito"><i class="fa-solid fa-calendar-xmark"></i></button>
          </div>
          <div class="col-md-1 d-flex justify-content-end">
            <button type="button" class="btn btn-primary" title="salva data disattivazione" onclick="confermaDismissione(<?php echo $cod_percorso;?>, '<?php echo $stag;?>')">
              <i class="fa-solid fa-floppy-disk"></i>
            </button>
          </div> 
        </div>
      </form>
    </div>
    <script>
      // data disattivazione
      function confermaDismissione(cod_percorso, stagionalita) {

        disattivaStagionale = '';

        console.log('la stagionalità è '+stagionalita)
        // Recupera la data selezionata nel form
        const dataDisatt = document.getElementById('js-date2').value;

        // Evita di procedere se la data è vuota
        if (!dataDisatt.trim()) {
            document.getElementById('js-date2').reportValidity();
            return;
        }

        // Rimuove un eventuale overlay già presente
        $('#overlayDataDis').remove();

        if (stagionalita != ''){
          $("#viewMemberModal").append(`
            <div id="overlayDataDis" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
            style="background: rgba(0,0,0,.2); z-index: 2000;">
              <div class="bg-white rounded shadow p-3 text-center">
                <h5>Vuoi modificare la data di fine esercisio del percorso stagionale o disattivare proprio il servizio?</h5>
                <br>
                <button type="button" class="btn btn-primary" id="btnConfermaDisattivazione" style="cursor:pointer; margin-right: 10px;" title="disattivare percorso" ><i class="fa-solid fa-circle-check"></i> Disattiva percorso</button>
                <button type="button" class="btn btn-primary" id="btnModificaDisattivazione" style="cursor:pointer; margin-left: 10px;" title="modifica data fine servizio"><i class="fa-solid fa-square-pen"></i> Modifica data</button>
                <hr>
                <div class="d-flex justify-content-end gap-2 mt-4">
                  <button type="button" class="btn btn-secondary" id="btnChiudiDataDis" onclick="chiudiDisattivazione()"> Chiudi </button>
                </div>
              </div>
            </div>`
          );
        }else{
          // l'overlay lo appendo al modal <div class="modal fade" id="viewMemberModal"....> e non al body, in questo modo blocco lo scrolling della pagina
          $("#viewMemberModal").append(`
            <div id="overlayDataDis" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
            style="background: rgba(0,0,0,.2); z-index: 2000;">
              <div class="bg-white rounded shadow p-3 text-center">
                <h5>Sei sicuro di voler disattivare il percorso a partire da <strong>${$('<div>').text(dataDisatt).html()}</strong>?</h5>
                <br>
                <button type="button" class="btn btn-primary" id="btnConfermaDisattivazione" style="cursor:pointer; margin-right: 10px;" title="disattivare percorso" ><i class="fa-solid fa-thumbs-up"></i> SI</button>
                <button type="button" class="btn btn-primary" id="btnAnnullaDisattivazione" style="cursor:pointer; margin-left: 10px;" title="non disattivare percorso" onclick="chiudiDisattivazione()"><i class="fa-solid fa-thumbs-down"></i> NO</button>
                <hr>
                <div class="d-flex justify-content-end gap-2 mt-4">
                  <button type="button" class="btn btn-secondary" id="btnChiudiDataDis" onclick="chiudiDisattivazione()"> Chiudi </button>
                </div>
              </div>
            </div>`
          );
        }
        
        
        // al click su SI o su Disattiva percorso viene triggerato il submit e quindi la chiamata ajax
        $('#btnConfermaDisattivazione').on('click', function () {
          //verifico se stagionale e in quel caso disattivaStagionale = 1 
          // che uso poi per aggiungere il parametro dis_stag al form come conferma della disattivazione
          if (stagionalita != '') {
            disattivaStagionale = '1';
          }

          $('#overlayDataDis').remove();

          $('#form_dd').trigger('submit');
        });

        $('#btnModificaDisattivazione').on('click', function () {
          // non aggiungo il parametro dis_stag al form 
          // quindi il percorso non viene disattivato ma solo modificata la data di fine esercizio
          
          $('#overlayDataDis').remove();

          $('#form_dd').trigger('submit');
        });
      }

      function chiudiDisattivazione() {
        $("#overlayDataDis").remove();
      }

      $(document).ready(function () {                 
        $('#form_dd').submit(function (event) { 
          console.log('Bottone form dd cliccato e finito qua');
          event.preventDefault();                  
          var formData = $(this).serialize();

          // Aggiungo il parametro solo se 
          // voglio proprio dismettere un percorso stagionale
          if (disattivaStagionale === '1') {
              formData += '&dis_stag=t';
          }

          console.log(formData);
          $.ajax({ 
            url: 'backoffice/update_data_disattivazione.php', 
            method: 'POST', 
            data: formData,
            dataType: 'json',
            //processData: true, 
            //contentType: false, 
            success: function (response) {                       
                //alert('Your form has been sent successfully.'); 
                console.log(response);
                showToast(response.messaggio, 'success');
            }, 
            error: function (jqXHR, textStatus, errorThrown) {                        
                const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio della data di disattivazione!';                        
                //console.error(errorThrown);
                //console.error(jqXHR.responseText);

                showToast(messaggio, 'error'); 
            } 
          }); 
        }); 
      }); 
    </script>
  <?php } else {   ?>
    <div class="col-md-6">
      <label class="form-check-label" for="data_dis"><b>Data disattivazione (esclusa)</b></label>
      <input type="text" name="data_dis" id="data_dis" class="form-control" value="<?php echo $data_disattivazione_testata;?>" readonly="">
    </div>
  <?php }
    if ($flg_disattivo==1){
      echo ' - <b><font color=red>Percorso disattivo</font></b>';
    }
  ?>
</div>
<div class="row g-3 align-items-stretch" id="row-stag"><!-- IV RIGA -->
  <?php if($stag != ''){ ?>
    <!-- INIZIO FORM STAGIONALITA -->
    <?php if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $flg_disattivo==0){
      $switchOng = substr($switchON, 0, 2);
      $switchOnm = substr($switchON, 2);
      $switchOffg = substr($switchOFF, 0, 2);
      $switchOffm = substr($switchOFF, 2); ?>
      <div class="col-md-6">
        <form class="row-cols-lg-auto g-3 align-items-center w-100" name="form_stag" id="form_stag" autocomplete="off" >
          <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
          <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
          <div class="row g-2 align-items-end w-100">
            <label class="form-check-label" for="form_stag"><b>Stagionalità</b></label>
            <div class="col-md-3 align-items-center" style="display: inline-flex; white-space:nowrap;">
              <label for="stag"> Stagione </label><span>&nbsp;</span>
              <select name="stag" id="stag" class="form-select" data-live-search="true">
                <option name="stag" value="" <?php if($stag ==''){echo 'selected';}?>>Nessuna</option>
                <option name="stag" value="E" <?php if($stag =='E'){echo 'selected';}?>>Estate</option>
                <option name="stag" value="I" <?php if($stag =='I'){echo 'selected';}?>>Inverno</option>
              </select>        
            </div>
            <!--div class="form-group col-md-6" id="switchstag" style=" display: block;"-->
            <div class="col-md-4 align-items-center" style="display: inline-flex; white-space:nowrap;">
              <label for="switchon"> Switch On </label> <span>&nbsp;</span>
              <input type="number" placeholder="Giorno" name="switchong" id="gson" max="31" class="form-control" value="<?php if($stag !=''){echo $switchOng;}?>">
              <input type="number" placeholder="Mese" name="switchonm" id="mson" max="12" class="form-control" value="<?php if($stag !=''){echo $switchOnm;}?>">
            </div>
            <div class=" col-md-4 align-items-center" style="display: inline-flex; white-space:nowrap;">
              <label for="switchoff"> Switch Off </label><span>&nbsp;</span>
              <input type="number" placeholder="Giorno" name="switchoffg" id="gsof" max="31" class="form-control" value="<?php if($stag !=''){echo $switchOffg;}?>">
              <input type="number" placeholder="Mese" name="switchoffm" id="msof" max="12" class="form-control" value="<?php if($stag !=''){echo $switchOffm;}?>">
            </div>
            <div class="col-md-1 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary" title="salva stagionalità" style="margin-right: 10%;">
                  <i class="fa-solid fa-floppy-disk"></i>
              </button>
            </div>
          </div> 
        </form>
      </div>
      <script> 
        $(document).ready(function () {                 
          $('#form_stag').submit(function (event) { 
            console.log('Bottone form dd cliccato e finito qua');
            event.preventDefault();                  
            var formData = $(this).serialize();
            console.log(formData);
            $.ajax({ 
              url: 'backoffice/update_stagione.php', 
              method: 'POST', 
              data: formData, 
              dataType: 'json',
              //processData: true, 
              //contentType: false, 
              success: function (response) {                       
                  //alert('Your form has been sent successfully.'); 
                  console.log(response);
                  showToast(response.messaggio, 'success');
              }, 
              error: function (jqXHR, textStatus, errorThrown) {                        
                  const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio della stagionalità!';                        
                //console.error(errorThrown);
                //console.error(jqXHR.responseText);

                showToast(messaggio, 'error'); 
              } 
            }); 
          }); 
        }); 
      </script>
      <?php } else { ?>
      <div class="col-md-6">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 text-nowrap" for="data_stag"><b>Stagione</b></label>
          <input type="text" name="data_stag" id="data_stag" class="form-control" value="<?php echo $stag;?>" readonly="">
          <label class="form-label mb-0 text-nowrap" for="switchON"><b>Switch On</b></label>
          <input type="text" name="switchON" id="switchON" class="form-control" value="<?php echo $switchON;?>" readonly="">
          <label class="form-label mb-0 text-nowrap" for="switchOFF"><b>Switch Off</b></label>
          <input type="text" name="switchOFF" id="switchOFF" class="form-control" value="<?php echo $switchOFF;?>" readonly="">
        </div>
      </div>
    <?php } ?>
  <?php } else{ ?>
    <div class="col-md-6">
      <span><strong> </strong></span>
    </div>
    <?php } ?>
    <div class="col-md-6 align-items-end d-flex justify-content-end">
      <span><strong> Data ultima modifica: <?php echo $data_ultima_modifica?> </strong></span>
    </div>
</div>
<div class="row g-3 align-items-stretch" id="row-gc-rim"><!-- V RIGA -->
  <?php if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $flg_disattivo==0){ ?>
    <div class="col-md-6">
      <span><strong> UT Esecutrice: </strong><?php echo $utgc ? $utgc : '-'; ?> (Data attivazione: <?php echo $data_att_print_gc?>)</span>
      <form class="row-cols-lg-auto g-3 align-items-center" name="form_squadra" id="form_squadra" autocomplete="off" >
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
        <input type="hidden" id="id_ut_sit" name="id_ut_sit" value="<?php echo $id_utgc;?>">
        <div class="row align-items-end">
          <div class="col-md-11" >
            <div class="d-flex align-items-center gap-2">
              <label class="form-check-label" for="sq_ut"><b>Squadra</b></label>
              <select name="sq_ut" id="sq_ut" class="form-select" data-size="5"  data-live-search="true" required="">
                <?php
                $query0_1="select id_squadra, 
                concat(cod_squadra, ' - ', desc_squadra) as descr 
                from elem.squadre s order by desc_squadra ;";
                $result0_1 = pg_prepare($conn_sit, "query0_1", $query0_1);            
                $result0_1 = pg_execute($conn_sit, "query0_1", array()); 
                while($r0_1 = pg_fetch_assoc($result0_1)) {         
                  ?>
                  <option name="sq_ut" 
                  <?php 
                  if ($r0_1['id_squadra']==$id_squadra_gc){
                    echo ' selected ';
                  } ?>
                  value="<?php echo $r0_1['id_squadra']?>" ><?php echo $r0_1['descr'] ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary" title="salva squadra">
              <i class="fa-solid fa-floppy-disk"></i>
            </button>
          </div>
        </div> 
      </form>
    </div>
    <script> 
      $(document).ready(function () {                 
          $('#form_squadra').submit(function (event) { 
              console.log('Bottone form dd cliccato e finito qua');
              event.preventDefault();                  
              var formData = $(this).serialize();
              console.log(formData);
              $.ajax({ 
                  url: 'backoffice/update_squadra.php', 
                  method: 'POST', 
                  data: formData,
                  dataType: 'json', 
                  //processData: true, 
                  //contentType: false, 
                  success: function (response) {                       
                      //alert('Your form has been sent successfully.'); 
                      console.log(response);
                      showToast(response.messaggio, 'success');
                  }, 
                  error: function (jqXHR, textStatus, errorThrown) {                        
                      const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio dela squadra!';                        
                      //console.error(errorThrown);
                      //console.error(jqXHR.responseText);

                      showToast(messaggio, 'error'); 
                  } 
              }); 
          }); 
      }); 
    </script>
    <?php 
    } else { ?>
      <div class="col-md-6">
        <span><strong> UT Esecutrice: </strong><?php echo $utgc ? $utgc : '-'; ?> (Data attivazione: <?php echo $data_att_print_gc; ?>)</span>
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 text-nowrap" for="sq_ut"><b>Squadra</b></label>
          <input type="text" name="sq_ut" id="sq_ut" class="form-control" value="<?php echo $squadra_gc;?>" readonly="">
        </div>
      </div>
    <?php }
      if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $flg_disattivo==0){ ?>
        <div class="col-md-6">
          <span><strong> Rimessa: </strong><?php  echo $utr ? $utr : '-'; ?> (Data attivazione: <?php echo $data_att_print_r ? $data_att_print_r : '-'; ?>)</span>
          <form class="row-cols-lg-auto g-3 align-items-center" name="form_squadrar" id="form_squadrar" autocomplete="off" >
            <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
            <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
            <input type="hidden" id="id_ut_sit_r" name="id_ut_sit_r" value="<?php echo $id_utr;?>">
            <div class="row align-items-end">
              <div class="col-md-11" >
                <div class="d-flex align-items-center gap-2">
                  <label class="form-check-label" for="sq_utr"><b>Squadra</b></label>
                  <?php if ($utr == '') { ?>
                    <input type="text" class="form-control" placeholder="Nessuna squadra per la rimessa" readonly>
                  <?php } else { ?>
                    <select name="sq_utr" id="sq_utr" class="form-select" data-size="5"  data-live-search="true" required="">
                      <?php
                      $query0_1r="select id_squadra, 
                      concat(cod_squadra, ' - ', desc_squadra) as descr 
                      from elem.squadre s order by desc_squadra ;";
                      $result0_1r = pg_prepare($conn_sit, "query0_1r", $query0_1r);            
                      $result0_1r = pg_execute($conn_sit, "query0_1r", array()); 
                      while($r0_1r = pg_fetch_assoc($result0_1r)) {         
                        ?>
                        <option name="sq_utr" 
                        <?php 
                        if ($r0_1r['id_squadra']==$sq_rimessa){
                          echo ' selected ';
                        } ?>
                        value="<?php echo $r0_1r['id_squadra']?>" ><?php echo $r0_1r['descr'] ?></option>
                      <?php } ?>
                    </select>
                  <?php } ?>
                </div>
              </div>
              <div class="col-md-1 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" title="salva squadra" <?= ($utr == '') ? 'disabled' : '' ?>>
                  <i class="fa-solid fa-floppy-disk"></i>
                </button>
              </div>
            </div> 
          </form>
        </div>
        <script> 
          $(document).ready(function () {                 
              $('#form_squadrar').submit(function (event) { 
                  event.preventDefault();                  
                  var formData = $(this).serialize();
                  console.log(formData);
                  $.ajax({ 
                      url: 'backoffice/update_squadra.php', 
                      method: 'POST', 
                      data: formData,
                      dataType: 'json',
                      //processData: true, 
                      //contentType: false, 
                      success: function (response) {                       
                          //alert('Your form has been sent successfully.'); 
                          console.log(response);
                          showToast(response.messaggio, 'success');
                      }, 
                      error: function (jqXHR, textStatus, errorThrown) {                        
                          const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio della squadra!';                        
                          //console.error(errorThrown);
                          //console.error(jqXHR.responseText);

                          showToast(messaggio, 'error'); 
                      } 
                  }); 
              }); 
          }); 
        </script>
        <?php 
      } else { ?>
        <div class="col-md-6">
          <span><strong> Rimessa: </strong><?php  echo $utr ? $utr : '-'; ?> (Data attivazione: <?php echo $data_att_print_r; ?>)</span>
          <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 text-nowrap" for="sq_rim"><b>Squadra</b></label>
            <?php if ($utr == '') { ?>
              <input type="text" name="sq_rim" id="sq_rim" class="form-control" value="Nessuna squadra per la rimessa" readonly>
            <?php } else{ ?>
            <input type="text" name="sq_rim" id="sq_rim" class="form-control" value="<?php echo $squadra_rim;?>" readonly="">
            <?php } ?>
          </div>
        </div>
      <?php } ?>
</div>
<div class="row g-3 align-items-stretch" id="row-vis" style="margin-top: 1%;"><!-- VI RIGA -->
  <?php if($check_visualizzazione == 1){ 
    $ut_viewer = [];

    while ($ruv = pg_fetch_assoc($resultut2)) {
      $ut_viewer[] = $ruv["ut"]." (".$ruv["data_attivazione_print"].")";
    }

    $utviewer_stringa = implode(', ', $ut_viewer);
  ?>
  <div class="col-md-6">
    <label class="form-check-label" for="utvis"><b>UT Visualizzazione</b></label>
    <input type="text" name="utvis" id="utvis" class="form-control" value="<?php echo htmlspecialchars($utviewer_stringa, ENT_QUOTES, 'UTF-8'); ?>" readonly>
  </div>
  <?php } else{ ?>
  <div class="col-md-6">
    <label class="form-check-label" for="utvis"><b>UT Visualizzazione</b></label>
    <input type="text" name="utvis" id="utvis" class="form-control" value="-" readonly>
  </div>
  <?php } ?>
    <?php if ($check_superedit==1){?>
    <div class="col-md-6">
      <form id="vis" name="vis" autocomplete="off" >
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="desc" name="desc" value="<?php echo $descr_percorso;?>">
        <input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
        <input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
        <input type="hidden" id="freq_sett" name="freq_sett" value="<?php echo $freq_sett;?>">
        <input type="hidden" id="id_servizio_uo" name="id_servizio_uo" value="<?php echo $id_servizio_uo;?>">
        <input type="hidden" id="id_servizio_sit" name="id_servizio_sit" value="<?php echo $id_servizio_sit;?>">
        <input type="hidden" id="durata" name="durata" value="<?php echo $durata;?>">
        <input type="hidden" id="turno" name="turno" value="<?php echo $turno;?>">
        <input type="hidden" id="tipo" name="tipo" value="<?php echo $id_tipo;?>">
        <input type="hidden" id="data_attivazione" name="data_attivazione" value="<?php echo $data_inizio_validita;?>">
        <input type="hidden" id="data_disattivazione" name="data_disattivazione" value="<?php echo $data_disattivazione_testata;?>">
        <!--input type="hidden" id="gc" name="gc" value="<?php echo $id_utgc;?>"-->
        <input type="hidden" id="sq_ut" name="sq_ut" value="15">
        <input type="hidden" id="mezzo" name="mezzo" value="<?php echo $mezzo;?>">
        <input type="hidden" id="new_vers" name="new_vers" value="<?php echo $versione;?>">
        <input type="hidden" id="stag" name="stag" value="<?php echo $stag;?>">
        <input type="hidden" id="swon" name="swon" value="<?php echo $switchON;?>">
        <input type="hidden" id="swoff" name="swoff" value="<?php echo $switchOFF;?>">
        <input type="hidden" id="cdr" name="cdr" value="<?php echo $cdr;?>">
        <div class="row align-items-end">
          <div class="col-md-11" >
            <label for="ut"><b>Nuova UT Visualizzazione:</b></label>
            <select name="ut" id="ut" class="selectpicker show-tick form-control" placeholder= "Seleziona UT in visualizzazione" data-live-search="true" data-size="5" required="">
              <?php 
                $query3="select id_ut, descrizione 
                from topo.ut   
                where id_ut not in (select distinct cmu.id_uo_sit  from anagrafe_percorsi.percorsi_ut pu 
                join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut 
                where cod_percorso = $1 and data_disattivazione  =to_date($2, 'DD/MM/YYYY'))
                order by descrizione;";
                
                $result3 = pg_prepare($conn_sit, "query3", $query3);
                $result3 = pg_execute($conn_sit, "query3", array($cod_percorso , $data_disattivazione_testata));  
                while($r3 = pg_fetch_assoc($result3)) { 
                    //$valore=  $r2['id_via']. ";".$r2['desvia'];            
              ?>
                <option name="ut" value="<?php echo $r3['id_ut']?>" ><?php echo $r3['descrizione'] ?></option>
              <?php } ?>

            </select>            
          </div>
          <div class="col-md-1 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" title="aggiungi ut visualizzazione">
              <i class="fa-solid fa-circle-plus"></i>
            </button>
          </div>
        </div> 
      </form>
    </div>
    <?php }?>
    <script> 
      $(document).ready(function () {                 
          $('#vis').submit(function (event) { 
              console.log('Bottone nuova visualizzazione cliccato e finito qua');
              event.preventDefault();                  
              var formData = $(this).serialize();
              console.log(formData);
              $.ajax({ 
                  url: 'backoffice/nuova_visualizzazione.php', 
                  method: 'POST', 
                  data: formData, 
                  dataType: 'json',
                  //processData: true, 
                  //contentType: false, 
                  success: function (response) {                       
                      //alert('Your form has been sent successfully.'); 
                      console.log(response);
                      //$("#results_desc").html(response).fadeIn("slow");
                      showToast(response.messaggio, 'success');
                  }, 
                  error: function (jqXHR, textStatus, errorThrown) {
                    const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante l\'aggiunta dell\'UT in visualizzazione!';                        
                      //console.error(errorThrown);
                      //console.error(jqXHR.responseText);

                      showToast(messaggio, 'error');
                  } 
              }); 
          }); 
      }); 
    </script>
</div>
<?php if ($id_servizio_sit=="" and $cdr=='f'){ ?><!-- VII RIGA solo se verificate le due condizioni-->
  <div class="row g-3 align-items-stretch" id="row-comuni" style="margin-top: 1%;"><!-- VI RIGA -->
    <?php if($check_versione_successiva==0 and $check_edit==1 and $flg_disattivo==0){ ?>
    <div class="col-md-12" style="display: none !important;" id="comuni-label"><strong>Competenza Comuni</strong></div>
      <div class="col-md-12">
        <form class="row-cols-lg-auto g-3 align-items-center" name="form_com" id="form_com" autocomplete="off" style="margin-top: unset;">
          <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso; ?>">
          <input type="hidden" id="id_servizio_uo" name="id_servizio_uo" value="<?php echo $id_servizio_uo; ?>">
          <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione; ?>">
          <div class="row align-items-end">
            <div id="comuni" class="form-group col-md-11" style="display: none !important;">
              <?php while($rC = pg_fetch_assoc($resultC)) { ?>
                <div class="comuni-row">
                  <label for="inlineCheckbox1" style="font-size:small;"><?php echo $rC['descr_comune']?></label>
                  <?php
                    if($count_result == 1) {
                      // Se c'è un solo comune, lo seleziono automaticamente e metto la checkbox disabled
                      //devo però passare il valore dell'id_comune con un input hidden perchè il disabled non passa il valor in post
                      echo '<input type="hidden" name="comuni[]" value="' . $rC['id_comune'] . '">';
                    }
                  ?>
                  <input class="comuni-check" type="checkbox" style="border-color:darkgrey;" name="comuni[]" id="comune_<?php echo $rC['id_comune']?>" value="<?php echo $rC['id_comune']?>" <?php 
                  if ($count_result == 1){
                      echo "checked disabled";
                  } elseif ($count_result > 1 and $rC['competenza'] > 0 ){
                      echo "checked";
                  } ?>>
                  <input type="number" class="percent-input" name="percentuali[<?= $rC['id_comune'] ?>]" placeholder="%" min="0" max="100" <?php
                    if ($count_result == 1) {
                      // Se c'è un solo comune, imposto il valore 100 e metto l'input readonly
                      //altrimenti lo metto disabled in modo che venga attivato solo se viene checcata la checbox corrispondente
                        echo 'value="100" readonly';
                    } elseif ($count_result > 1 and $rC['competenza'] > 0 ){
                        echo 'value="'.$rC['competenza'].'"';
                    }
                    else {
                        echo 'disabled';
                    }
                  ?>>
                </div>
              <?php
                } // chiude il while della query_comuni
              ?>
            </div>
            <div class="col-md-1 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary" title="salva comuni">
                <i class="fa-solid fa-floppy-disk"></i>
              </button>
            </div>
          </div> 
        </form>
      </div>
      <script> 
        $(document).ready(function () {
          $('#form_com').on('submit', function (event) {

            // verifico se almeno un comune  è selezionato
            let checkedC = $("input[name='comuni[]']:checked");
            console.log("Checkbox selezionate:", checkedC.length);

            if (checkedC.length === 0) {
                event.preventDefault();
                alert("E' necessario selezionare almeno un comune.");
                return false;
            }

            // verifico se la somma delle percentuali è 100
            let somma = 0;
            $(".percent-input:not([disabled])").each(function () {
                somma += parseFloat($(this).val()) || 0;
            });

            console.log("Somma percentuali:", somma);

            if (somma !== 100) {
                event.preventDefault();
                alert("La somma delle percentuali deve essere 100. Somma attuale: " + somma);
                return false;
            }

            // --- Se i controlli sono ok → invio AJAX ---
            event.preventDefault();  
            console.log("Controlli OK, invio form comuni");

            var formData = $(this).serialize();

            $.ajax({
                url: 'backoffice/update_comuni.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                  //processData: true, 
                  //contentType: false, 
                  success: function (response) {                       
                      //alert('Your form has been sent successfully.'); 
                      console.log(response);
                      //$("#results_desc").html(response).fadeIn("slow");
                      showToast(response.messaggio, 'success');
                  }, 
                  error: function (jqXHR, textStatus, errorThrown) {
                    const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio della percentuale competenza Comuni!';                        
                      //console.error(errorThrown);
                      //console.error(jqXHR.responseText);

                      showToast(messaggio, 'error');
                  }
            });
          });
        });
      </script>
    <?php }else{ ?>
      <div class="col-md-12"><strong>Competenza Comuni</strong></div>
        <div class="col-md-12">
          <div class="comuni-row">
          <?php while($rC = pg_fetch_assoc($resultC)) {
            echo $rC['descr_comune'].': '.$rC['competenza'].'% ';
          } ?>
          </div>
        </div>
    <?php } ?>
  </div>
<?php } ?>
<div class="row g-3 align-items-stretch" id="row-mezzi"><!-- VIII RIGA -->
  <?php if ($check_versione_successiva==0 and $check_superedit==1 and $check_in_attivazione==1 and $flg_disattivo==0){ ?>
    <div class="col-md-12"><strong>Risorse tecniche</strong></div>
    <div class="col-md-12">
      <form class="row-cols-lg-auto g-3 align-items-center" name="form_mezzo" id="form_mezzo" autocomplete="off" >
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
        <input type="hidden" id="id_ut_sit" name="id_ut_sit" value="<?php echo $id_utgc;?>">
        <div class="row align-items-center">
          <div class="form-group col-md-6">
            <select name="mezzo_ut" id="mezzo_ut" class="selectpicker show-tick form-control" data-size="5"  data-live-search="true" onchange="writelist();" title="Seleziona la tipologia di mezzo">
              <!--option name="cdaog3" value="">Seleziona la tipologia di mezzo</option-->
              <?php
              ## da cambiare la query per lista mezzi
              $querymezzo="select cdaog3,
              concat(trim(categoria), ' (', trim(nome), ')') as cat_estesa  from elem.automezzi a 
              where a.cdaog3 not in ('9998' , '9997', '9996', '9994','9993')
              order by categoria ;";
              $resultmezzo = pg_query($conn_sit, $querymezzo);
              //echo $query1;    
              while($rm = pg_fetch_assoc($resultmezzo)) { 
              ?>   
                <option name="cdaog3" value="<?php echo $rm['cdaog3']?>" ><?php echo $rm['cat_estesa'] ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col-md-5">
            <label for="lista_mezzi">Elenco mezzi selezionati:</label>
            <textarea readonly id="lista_mezzi" name="lista_mezzi" rows="4"  class="form-control" required><?php
              while($rm = pg_fetch_assoc($result_mezzi)) {
                  echo $rm['cdaog3'].' - '.$rm['cat_estesa']."\n";
                }?>
            </textarea>
            <input type="hidden" id="lista_mezzi_valori" name="lista_mezzi_valori" value="">
            <input type="hidden" id="lista_mezzi_nomi" name="lista_mezzi_nomi" value="">
            <div class="form-group" style="display: flex; margin-top:2%;">
              <small class="text-muted" ><!--Anteprima del file con l'elenco mezzi usato dall'applicativo per generare i file con le utenze. 
              In caso di errori con i mezzi clicca sul bottone per rimuovere l'ultima linea.<br-->
              <a href="#" class="btn btn-warning btn-sm" id="removeline" ><i class="far fa-trash-alt"></i>Elimina ultimo mezzo</a>
              <!--br> o riparti dall'inizio<br-->
              <a href="#" class="btn btn-danger btn-sm" id="aggiorna" ><i class="fas fa-redo"></i>Elimina intero elenco</a></small>
            </div>
            <script>
                $(document).ready(function() {
                    $('#removeline').click(function() {
                      //cancello ulrima riga del textarea
                      var lm = $('#lista_mezzi');
                      var text_lm = lm.val().trim("\n");
                      var valuelist_lm = text_lm.split("\n");
                      var last_lm = valuelist_lm[valuelist_lm.length - 1];
                      replace_lm=text_lm.replace(last_lm, "").replace(/\n$/, "")
                      lm.val(replace_lm)

                      
                      // stessa cosa per i campi nascosti
                      var lm_valori = $('#lista_mezzi_valori');
                      var text_lm_valori = lm_valori.val().split(",");
                      text_lm_valori.pop();
                      lm_valori.val(text_lm_valori)

                      var lm_nomi = $('#lista_mezzi_nomi');
                      console.log('lista nomi prima: '+lm_nomi.val())
                      var text_lm_nomi = lm_nomi.val().split(",");
                      text_lm_nomi.pop();
                      lm_nomi.val(text_lm_nomi)

                      console.log('lista mezzi: '+lm.val())
                      console.log('lista nomi dopo: '+lm_nomi.val())
                      console.log('lista valori dopo: '+lm_valori.val())
                    })
                });
                $(document).ready(function() {
                    $('#aggiorna').click(function() {
                        // pulisco tutto
                        $('#lista_mezzi').val('');
                        $('#lista_mezzi_valori').val('');
                        $('#lista_mezzi_nomi').val('');
                    })
                });

            </script>            
          </div>
          <div class="col-md-1 d-flex justify-content-center align-items-center">
            <button type="submit" class="btn btn-primary" title="salva mezzo">
              <i class="fa-solid fa-floppy-disk"></i>
            </button>
          </div>
        </div> 
      </form>
    </div>
    <script> 
      $(document).ready(function () {                 
          $('#form_mezzo').submit(function (event) { 
              console.log('Bottone form dd cliccato e finito qua');
              event.preventDefault();                  
              var formData = $(this).serialize();
              console.log(formData);
              $.ajax({ 
                  url: 'backoffice/update_mezzo.php', 
                  method: 'POST', 
                  data: formData, 
                  dataType: 'json',
                  //processData: true, 
                  //contentType: false, 
                  success: function (response) {                       
                      //alert('Your form has been sent successfully.'); 
                      console.log(response);
                      //$("#results_desc").html(response).fadeIn("slow");
                      showToast(response.messaggio, 'success');
                  }, 
                  error: function (jqXHR, textStatus, errorThrown) {
                    const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio del mezzo!';                        
                      //console.error(errorThrown);
                      //console.error(jqXHR.responseText);

                      showToast(messaggio, 'error');
                  }  
              }); 
          }); 
      }); 
    </script>
    <?php }else{ 

      $mezzi = [];

      while ($rm = pg_fetch_assoc($result_mezzi)) {
        $mezzi[] = $rm["cat_estesa"];
      }

      $mezzi_stringa = implode(', ', $mezzi);

      ?>

      <div class="col-md-12">
          <label class="form-check-label" for="mezzi"><b>Risorse tecniche</b></label>
          <input type="text" name="mezzi" id="mezzi" class="form-control" value="<?php echo htmlspecialchars($mezzi_stringa, ENT_QUOTES, 'UTF-8'); ?>" readonly>
      </div>
    <?php } ?>
</div>
<div class="row g-3 align-items-stretch" id="row-new-version" style="margin-top: 1%;"><!-- VIII RIGA -->
  <div class="col-md-6">
  </div>
  <div class="col-md-6 d-flex justify-content-end">
    <?php if($check_versione_successiva==0){?>
      <form id="nv" name="nuova_versione" target="_blank" method="post" autocomplete="off" action="./nuova_versione.php">
        <input type="hidden" id="id_percorso" name="id_percorso" value="<?php echo $cod_percorso;?>">
        <input type="hidden" id="desc" name="desc" value="<?php echo $descr_percorso;?>">
        <input type="hidden" id="freq_uo" name="freq_uo" value="<?php echo $freq_uo;?>">
        <input type="hidden" id="freq_sit" name="freq_sit" value="<?php echo $freq_sit;?>">
        <input type="hidden" id="fbin" name="fbin" value="<?php echo $freq_testata;?>">
        <input type="hidden" id="freq_sett" name="freq_sett" value="<?php echo $freq_sett;?>">
        <input type="hidden" id="id_servizio_uo" name="id_servizio_uo" value="<?php echo $id_servizio_uo;?>">
        <input type="hidden" id="id_servizio_sit" name="id_servizio_sit" value="<?php echo $id_servizio_sit;?>">
        <input type="hidden" id="durata" name="durata" value="<?php echo $durata;?>">
        <input type="hidden" id="turno" name="turno" value="<?php echo $turno;?>">
        <input type="hidden" id="turno_ini" name="turno_ini" value="<?php echo $turnoIni;?>">
        <input type="hidden" id="turno_fine" name="turno_fine" value="<?php echo $turnoFine;?>">
        <input type="hidden" id="turno_ref_day" name="turno_ref_day" value="<?php echo $giorno_competenza;?>">
        <input type="hidden" id="tipo" name="tipo" value="<?php echo $id_tipo;?>">
        <input type="hidden" id="old_vers" name="old_vers" value="<?php echo $versione;?>">
        <input type="hidden" id="rimessa" name="rimessa" value="<?php echo $rimessa;?>">
        <input type="hidden" id="sq_rimessa" name="sq_rimessa" value="<?php echo $sq_rimessa;?>">
        <input type="hidden" id="gc" name="gc" value="<?php echo $id_utgc;?>">
        <input type="hidden" id="sq_gc" name="sq_gc" value="<?php echo $id_squadra_gc;?>">
        <input type="hidden" id="mezzo" name="mezzo" value="<?php echo $mezzo;?>">
        <input type="hidden" id="eko" name="eko" value="<?php echo $eko;?>">
        <input type="hidden" id="stag" name="stag" value="<?php echo $stag;?>">
        <input type="hidden" id="swon" name="swon" value="<?php echo $switchON;?>">
        <input type="hidden" id="swoff" name="swoff" value="<?php echo $switchOFF;?>">
        <input type="hidden" id="cdr" name="cdr" value="<?php echo $cdr;?>">
        <?php if ($check_superedit==1 and $check_in_attivazione==0){?>
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-plus"></i> <i class="fa-solid fa-arrow-up-from-bracket"></i> Nuova versione
            </button>
        </form>
        <?php } else {
          //echo 'Check in attivazione '. $check_in_attivazione;
          if ($check_in_attivazione==1){
            echo '<div style=" text-align-last: center;"><h6><i class="fa-solid fa-ban"></i> Percorso non ancora attivo. Per modifiche contattare <a href="mailto:assterritorio@amiu.genova.it">AssTerritorio</a>.</h6></div>';
          }
        }?>

    <?php } else {
        echo '<i class="fa-solid fa-ghost"></i> Non è ultima versione del percorso.. non posso fare modifiche.';
    }?>
  </div>
</div>
<div><!-- TOAST RIGA -->
  <div id="toast" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
</div>