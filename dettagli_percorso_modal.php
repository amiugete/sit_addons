<?php
//session_set_cookie_params($lifetime);
require_once './session.php';
require_once './conn_ok.php';
?>

<!DOCTYPE html>
<html lang="it">
  <head>

    <?php
      $check_modal = 1;

      require_once('./req.php');

      the_page_title();
    ?>
  </head>
  <body>
    <?php

      require_once('./navbar_up.php');
      $name=dirname(__FILE__);
      if ((int)$id_role_SIT = 0) {
        redirect('no_permessi.php');
        //exit;
      }


      $cod_percorso= $_GET['cp'];
      $versione= $_GET['v'];


      // verifico se c'è già una versione successiva
      $check_versione_successiva=0;
      $query_check_versione="select ep.cod_percorso from anagrafe_percorsi.elenco_percorsi ep
      where ep.cod_percorso = $1 and ep.versione_testata > $2";
      $result0 = pg_prepare($conn_sit, "query_check_versione", $query_check_versione);
      $result0 = pg_execute($conn_sit, "query_check_versione", array($cod_percorso, intval($versione)));
      while($r0 = pg_fetch_assoc($result0)) {
        $check_versione_successiva=1;
      }

      $check_in_attivazione=0;

      //verifico se il percorso è stagionale ma in dismissione
      $check_stag_dismiss = 0;
      $query_check_stag_dismiss="select p.cod_percorso, p.stagionalita, p.ddmm_switch_on, p.ddmm_switch_off from elem.percorsi p 
      where p.cod_percorso = $1";
      $resultst = pg_prepare($conn_sit, "query_check_stag_dismiss", $query_check_stag_dismiss);
      $resultst = pg_execute($conn_sit, "query_check_stag_dismiss",array($cod_percorso));
      while($rst = pg_fetch_assoc($resultst)) {
        if ($rst["stagionalita"] == '' && $rst["ddmm_switch_on"] != '' && $rst["ddmm_switch_off"] != ''){
          $check_stag_dismiss=1;
        }
      }
    ?>

      <?php
        // ottengo dettagli testata percorso
        $query_testata = "select ep.cod_percorso, 
        ep.descrizione, t.cod_turno, t.id_turno, t.inizio_ora, t.fine_ora, ep.durata, fo.descrizione_long, 
        ep.freq_testata, ep.freq_testata::bit(12) as fbin, fo.freq_binaria, ep.id_tipo, 
        case 
        	when tipo_servizio_sit  is null then 'TESTATA' 
        else 
        	case 
	        	when tipo_servizio_sit = 'piazzole' then 'RACCOLTA'
        	else upper(tipo_servizio_sit) 
        	end
        end tipo_servizio_new,
        ep.freq_settimane,
        at2.id_servizio_uo, at2.id_servizio_sit, 
        to_char(ep.data_inizio_validita, 'DD/MM/YYYY') as data_inizio_print,
        ep.data_inizio_validita, to_char(ep.data_fine_validita, 'DD/MM/YYYY') as data_disattivazione_testata,
        case 
        when ep.data_fine_validita < now() then 1
        else 0
        end flg_disattivo, 
        case 
        when ep.data_inizio_validita > now()::date then 1
        else 0
        end flg_in_attivazione,
        to_char(ep.data_ultima_modifica, 'DD/MM/YYYY HH24:MI') as data_ultima_modifica, 
        ep.ekovision,
        ep.stagionalita,
        ep.ddmm_switch_on, 
        ep.ddmm_switch_off,
        ep.giorno_competenza,
        at2.cdr,
        ep.nota_versione,
        at2.gestione_arera
        from anagrafe_percorsi.elenco_percorsi ep
        join elem.turni t on t.id_turno = ep.id_turno
        join etl.frequenze_ok fo on fo.cod_frequenza = ep.freq_testata
        join anagrafe_percorsi.anagrafe_tipo at2 on at2.id = ep.id_tipo 
        where ep.cod_percorso = $1 and ep.versione_testata  = $2";
        $result = pg_prepare($conn_sit, "query_testata", $query_testata);
        $result = pg_execute($conn_sit, "query_testata", array($cod_percorso, $versione)); 
        while($r = pg_fetch_assoc($result)) {
          $descr_percorso = $r["descrizione"];
          $cod_turno = $r["cod_turno"];
          $turno = $r["id_turno"];
          $turnoIni = $r["inizio_ora"];
          $turnoFine = $r["fine_ora"];
          $durata = $r["durata"];
          $descrizione_long = $r["descrizione_long"];
          $freq_testata = $r["fbin"];
          $freq_uo = $r["freq_binaria"];
          $id_tipo = $r["id_tipo"];
          $tipo_servizio = $r["tipo_servizio_new"];
          $freq_sett = $r["freq_settimane"];
          $id_servizio_uo = $r["id_servizio_uo"];
          $id_servizio_sit = $r["id_servizio_sit"];
          $data_inizio_print = $r["data_inizio_print"];
          $data_inizio_validita = $r["data_inizio_validita"];
          $data_disattivazione_testata = $r["data_disattivazione_testata"];
          $flg_disattivo = $r["flg_disattivo"];
          $flg_in_attivazione = $r["flg_in_attivazione"];
          $data_ultima_modifica = $r["data_ultima_modifica"];
          $eko = $r["ekovision"];
          $stag = $r["stagionalita"];
          $switchON = $r["ddmm_switch_on"];
          $switchOFF = $r["ddmm_switch_off"];
          $giorno_competenza = $r["giorno_competenza"];
          $cdr = $r["cdr"];
          $nota_versione = $r["nota_versione"];
          $gestione_arera = $r["gestione_arera"];
          $freq_sit=$r["freq_testata"];
        }
        if(intval($flg_in_attivazione)==1){
          $check_in_attivazione=1;
        }

        if ($freq_settimane =='T'){
          $tipo_frequenza = 'Settimanale';
        } else if ($freq_settimane =='P') {
          $tipo_frequenza = 'Mensile - Solo settimane Pari';
        } else if ($freq_settimane=='D') {
          $tipo_frequenza = 'Mensile - Solo settimane Dispari';
        }

        // query per dettagli UT- risorse umane
        $query0_modal="select 
        u.descrizione as ut,
        pu.id_squadra,
        concat(s.cod_squadra, ' - ',s.desc_squadra) as squadra,
        pu.cdaog3,
        a.categoria as mezzo,
        pu.responsabile, 
        pu.solo_visualizzazione, 
        pu.rimessa, 
        pu.data_attivazione,
        to_char(pu.data_attivazione, 'DD/MM/YYYY') as data_attivazione_print, 
        pu.data_disattivazione, 
        u.id_ut
        from anagrafe_percorsi.percorsi_ut pu 
        join anagrafe_percorsi.cons_mapping_uo cmu on cmu.id_uo = pu.id_ut 
        left join elem.automezzi a on a.cdaog3 = pu.cdaog3 
        join elem.squadre s on s.id_squadra = pu.id_squadra 
        join topo.ut u on u.id_ut = cmu.id_uo_sit  
        where pu.cod_percorso = $1  and pu.data_disattivazione = to_date($2, 'DD/MM/YYYY')";

        // RIMESSA - SEDE OPERATIVA
        $query_rimessa=$query0_modal ." and rimessa = 'S' and responsabile = 'N'";
        $result1 = pg_prepare($conn_sit, "query_rimessa", $query_rimessa);
        $result1 = pg_execute($conn_sit, "query_rimessa", array($cod_percorso, $data_disattivazione_testata));

        while($r1 = pg_fetch_assoc($result1)) {
          $utr = $r1["ut"];
          $id_utr = $r1["id_ut"];
          $squadra_rim = $r1["squadra"]; 
          $data_att_print_r = $r1["data_attivazione_print"];
          $rimessa = $r1["id_ut"];
          $sq_rimessa = $r1["id_squadra"];
          $mezzo = $r1["cdaog3"];

        }

        // GRUPPO DI COORDINAMENTO
        $query_ut=$query0_modal ." AND responsabile = 'S' and pu.id_squadra!= 15 ";
        $result2 = pg_prepare($conn_sit, "query_ut", $query_ut);
        $result2 = pg_execute($conn_sit, "query_ut", array($cod_percorso, $data_disattivazione_testata));
        
        while($r2 = pg_fetch_assoc($result2)) {
          $utgc = $r2["ut"];
          $id_squadra_gc = $r2["id_squadra"];
          $id_utgc = $r2["id_ut"];
          $squadra_gc = $r2["squadra"];
          $data_att_print_gc = $r2["data_attivazione_print"];
          $mezzo = $r2["cdaog3"];
          
        }

        // UT VISUALIZZAZIONE
        $query_ut2=$query0_modal ." and pu.id_squadra= 15 and rimessa = 'N' order by responsabile desc";
        $resultut2 = pg_prepare($conn_sit, "query_ut2", $query_ut2);
        $resultut2 = pg_execute($conn_sit, "query_ut2", array($cod_percorso, $data_disattivazione_testata));

        $check_visualizzazione = 0;
        if (pg_num_rows($resultut2) > 0){
          $check_visualizzazione = 1;
        }


        /*while($rut2 = pg_fetch_assoc($resultut2)) {
          $utvis = $rut2["ut"];
          $id_utvis = $rut2["id_ut"];
          $data_att_print_vis = $rut2["data_attivazione_print"];

        }*/

        // query competenza comuni
        if ($id_servizio_sit=="" and $cdr=='f'){
          $query_comuni="SELECT 
              cu.id_comune,
              cu.id_ut,
              c.descr_comune,
              COALESCE(pc.competenza, 0) AS competenza
              FROM topo.comuni_ut cu
              JOIN topo.comuni c 
                  ON c.id_comune = cu.id_comune 
              LEFT JOIN anagrafe_percorsi.percorsi_comuni pc 
                  ON pc.id_comune = cu.id_comune
                  AND pc.cod_percorso = $1 AND pc.versione = $2
                  WHERE cu.id_ut = $3
              ORDER BY c.descr_comune;";
            $resultC = pg_prepare($conn_sit, "queryC", $query_comuni);
            $resultC = pg_execute($conn_sit, "queryC", array($cod_percorso, $versione, $id_utgc));  
            $count_result = pg_num_rows($resultC);
        }

        $mezzo='';
        //query risorse tecniche
        $query_mezzi="select cdaog3,
            concat(categoria, ' (', trim(nome), ')') as cat_estesa  
            from elem.automezzi a 
            join anagrafe_percorsi.percorsi_mezzi pm on pm.id_mezzo = a.cdaog3 
            where pm.cod_percorso = $1 and pm.versione = $2
            order by categoria ";
        $result_mezzi = pg_prepare($conn_sit, "query_mezzi", $query_mezzi);
        $result_mezzi = pg_execute($conn_sit, "query_mezzi", array($cod_percorso, $versione));

        //per debug da commentare o eliminare
        //$check_in_attivazione=1;
      ?>

      <div id="modal_dettagli">
        <div class="row g-3 align-items-center">
          <h3> Percorso  <?php echo $cod_percorso; ?> - <?php echo $descr_percorso; ?> - versione n° <?php echo $versione; ?>
            <?php
              if ($gestione_arera === 't'){
                echo ' <i class="fa-solid fa-scale-balanced" title="Tipo servizio gestione area"></i>';
              }
              if ($eko === 't'){
                echo ' <i class="fa-solid fa-list-check" title="Percorso trasmesso a Ekovision"></i>';
              }
            ?>
          </h3>
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="testata-tab" data-bs-toggle="tab" data-bs-target="#testata-tab-pane" type="button" role="tab" aria-controls="testata-tab-pane" aria-selected="true">Testata</button>
            </li>
            <?php if ($tipo_servizio == 'RACCOLTA'){?>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="piazzole-tab" data-bs-toggle="tab" data-bs-target="#piazzole-tab-pane" type="button" role="tab" aria-controls="piazzole-tab-pane" aria-selected="false">Piazzole</button>
              </li>
            <?php } elseif ($tipo_servizio == 'SPAZZAMENTO'){?>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="aste-tab" data-bs-toggle="tab" data-bs-target="#aste-tab-pane" type="button" role="tab" aria-controls="aste-tab-pane" aria-selected="false">Aste</button>
              </li>
            <?php } elseif ($tipo_servizio == 'ALTRO'){?>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="elementi-tab" data-bs-toggle="tab" data-bs-target="#elementi-tab-pane" type="button" role="tab" aria-controls="elementi-tab-pane" aria-selected="false">Elementi</button>
              </li>
            <?php } ?>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="posizione-tab" data-bs-toggle="tab" data-bs-target="#posizione-tab-pane" type="button" role="tab" aria-controls="posizione-tab-pane" aria-selected="false">Modifica</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="analisi-tab" data-bs-toggle="tab" data-bs-target="#analisi-tab-pane" type="button" role="tab" aria-controls="analisi-tab-pane" aria-selected="false">Analisi</button>
            </li>
            <!--li class="nav-item" role="presentation">
              <button class="nav-link" id="disabled-tab" data-bs-toggle="tab" data-bs-target="#disabled-tab-pane" type="button" role="tab" aria-controls="disabled-tab-pane" aria-selected="false" disabled>Disabled</button>
            </li-->
          </ul>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="testata-tab-pane" role="tabpanel" aria-labelledby="testata-tab" tabindex="0">
              <!-- CONTENUTO TAB DETTAGLI TESTATA-->
              <?php require './dettagli_percorso_tab_testata.php'; ?>
            </div>
            <div class="tab-pane fade" id="piazzole-tab-pane" role="tabpanel" aria-labelledby="piazzole-tab" tabindex="0">
              <!-- CONTENUTO TAB PIAZZOLE-->
               <?php if(($_ENV['APP_ENV'] ?? '') === 'test') {
                  require './dettagli_percorso_tab_piazzole.php'; 
                  }else{
                    require './modal_wip.php';
                  }?>
            </div>
            <div class="tab-pane fade" id="aste-tab-pane" role="tabpanel" aria-labelledby="aste-tab" tabindex="0">
              <div class="row g-3 align-items-stretch">
                <?php require './modal_wip.php'; ?>
              </div>
            </div>
            <div class="tab-pane fade" id="elementi-tab-pane" role="tabpanel" aria-labelledby="elementi-tab" tabindex="0">
              <div class="row g-3 align-items-stretch">
                <?php require './modal_wip.php'; ?>
              </div>
            </div>
            <div class="tab-pane fade" id="posizione-tab-pane" role="tabpanel" aria-labelledby="posizione-tab" tabindex="0">
              <?php if(($_ENV['APP_ENV'] ?? '') === 'test') {
                  require './dettagli_percorso_tab_modifica.php'; 
                  }else{
                    require './redirect2wip.php';
                  }?>
            </div>
            <div class="tab-pane fade" id="analisi-tab-pane" role="tabpanel" aria-labelledby="analisi-tab" tabindex="0">
              <div class="row g-3 align-items-stretch">
                <?php require './modal_wip.php'; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    
    




    <script>

    var $table = $('#piazzole_percorso');

    $(function() {
      $table.bootstrapTable()
    });

    // imposto la data di disattivazione a domani
    $('#btnTomorrow').click(function() {     
      $('#js-date2').datepicker('setDate', "+1d");
    });

    $('#btnInf').click(function() {     
      $('#js-date2').datepicker('setDate', "31/12/2099");
    });

    $(document).ready(function(){
      console.log('Ci passo?');
      $('#js-date1').datepicker({
          format: 'dd/mm/yyyy',
          todayBtn: "linked", // in conflitto con startDate
          startDate: '+1d', 
          language:'it' 
      });


      $('#js-date2').datepicker({
          format: 'dd/mm/yyyy',
          todayBtn: 'linked',
          startDate: '+1d', 
          assumeNearbyYear: "true",
          startView: 0 ,
          language:'it' 
      });
    }); 

    $(document).ready(function(){
        console.log('Arrivo qua');
        //$('#ut').removeAttr('disabled');
        $('#ut').selectpicker("show");
        //$('#ut').selectpicker("refresh");
    });


    $(document).ready(function() {
        // Se ci sono checkbox già selezionate al caricamento della pagina, abilita gli input percentuali corrispondenti
        id_sit = "<?php echo $id_servizio_sit;?>";
        cdr = "<?php echo $cdr;?>";
        if(id_sit==""){
          console.log("percorso SENZA id sit");
          if(cdr=="f"){
            console.log("NON è un CDR");
            document.getElementById('comuni').style.display = "";
            document.getElementById('comuni-label').style.display = "";
          }
        } else {
          console.log("percorso CON id sit, non devo fare nulla");
        }
    });

    document.addEventListener("change", function(e) {
        if (e.target.classList.contains("comuni-check")) {

            //trovo il div padre della checkbox
            let div_check = e.target.closest(".comuni-row") || e.target.parentElement;
            console.log(div_check);
          
            // cerca l'input test nel div padre
            let inputPercent = div_check.querySelector(".percent-input");
            if (!inputPercent) {
                // fallback: niente input trovato (debug)
                console.warn("Percent input non trovato per la checkbox", e.target);
                return;
            }

            if (e.target.checked) {
                inputPercent.disabled = false;
                // mette il puntatore del mouse sull'input corrispondente
                inputPercent.focus();
            } else {
                inputPercent.disabled = true;
                inputPercent.value = "";
            }
        }
    });

    // al caricamento della pagina, popola i campi hidden con i valori già presenti nella textarea
    $(document).ready(function() {
        var lista = document.getElementById("lista_mezzi").value.trim();
        if (lista === '') return;

        var righe = lista.split('\n').filter(r => r.trim() !== '');
        
        var codici = righe.map(r => r.split(' - ')[0].trim());
        var nomi   = righe.map(r => r.split('(')[1].replace(/\)$/, '').trim());

        document.getElementById("lista_mezzi_valori").value = codici.join(',');
        document.getElementById("lista_mezzi_nomi").value   = nomi.join(',');
    });

    </script>


    <script type="text/javascript" >

    $(document).on("keydown", ":input:not(textarea)", function(event) {
        if (event.key == "Enter") {
            event.preventDefault();
        }
    });

    </script>

    <script>

      $(function () {
        $('#mezzo_ut').selectpicker();
      });

      function writelist(){
        var mezzo_value=$("#mezzo_ut option:selected").val(); //get the value of the current selected option.
        console.log(mezzo_value);
        var mezzo_text=$("#mezzo_ut option:selected").text();
        console.log(mezzo_text);

        if (mezzo_value === '') return;

        //document.getElementById("lista_mezzi").value += mezzo_value+ ' - '+mezzo_text+ "\n";

        var lista_mezzi = document.getElementById("lista_mezzi");
        lista_mezzi.value = lista_mezzi.value.trim();

        if (lista_mezzi.value != '') {
            lista_mezzi.value = lista_mezzi.value + '\n' + mezzo_value + ' - ' + mezzo_text;
        } else {
            lista_mezzi.value = mezzo_value + ' - ' + mezzo_text;
        }

        // creo una lista con i soli codici mezzo, li salvo in un campo nascosto del form in modo da inviarli al submit
        var codici_list = document.getElementById("lista_mezzi_valori");
        if (codici_list.value != "") {
            // il campo ha già dei valori → aggiungo la virgola e il nuovo valore
            codici_list.value = codici_list.value + ',' + mezzo_value;
        } else {
            // il campo è ancora vuoto → scrivo solo il valore, senza virgola iniziale
            codici_list.value = mezzo_value;
        }
        
        // creo una lista con i soli nomi mezzo, li salvo in un campo nascosto del form in modo da inviarli al submit
        var nomi_list = document.getElementById("lista_mezzi_nomi");
        if (nomi_list.value != "") {
            // il campo ha già dei valori → aggiungo la virgola e il nuovo valore
            nomi_list.value = nomi_list.value + ',' + mezzo_text.split('(')[1].replace(/\)$/, '');
        } else {
            // il campo è ancora vuoto → scrivo solo il valore, senza virgola iniziale
            nomi_list.value = mezzo_text.split('(')[1].replace(/\)$/, '');
        }

        $("#mezzo_ut").selectpicker('val', '');
      } 

    </script>


    <script>
      function showToast(message, type = 'success', pos='page') {

        let toast;
        if (pos === 'modal') {
            toast = document.getElementById('toast_modal');
        } else if (pos === 'map') {
            toast = document.getElementById('toast_map');
        }else {
            toast = document.getElementById('toast');
        }
        toast.textContent = message;
        toast.className = 'toast show ' + type;

        setTimeout(() => {
            toast.className = 'toast';
        }, 3000);
      }
    </script>
    </body>
</html>