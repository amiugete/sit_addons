<?php
//session_set_cookie_params($lifetime);
require_once './session.php';
require_once './conn_ok.php';
require_once './redirect2wip.php';
    
?>
<!DOCTYPE html>
<html lang="it">

  <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberta" >

    <title>Dettagli piazzola</title>
    <?php 
      require_once('./req.php');

      the_page_title();

    ?>

  </head>

  <body>

    <?php 
      require_once('./navbar_up.php');
      $name=dirname(__FILE__);
      if ((int)$id_role_SIT == 0) {
        redirect('no_permessi.php');
        //exit;
      }
      
      require_once('./showPhoto.php');

      $id_piazzola= $_GET['idp'];   

      $query_piazzola = "SELECT a.id_ut, u.descrizione as ut, 
      a.id_uu, uu.descrizione as uu, p.id_asta, v.id_via,
      v.nome, t.descrizione as transitabilita, 
      STRING_AGG(distinct c.descrizione, ',') as criticita, 
      STRING_AGG(distinct cp.id_criticita::text, ',') as id_criticita,
      p.id_piazzola, p.numero_civico, 
      p.lettera_civico, p.colore_civico,
      p.riferimento, p.ecopunto, 
      p.suolo_privato, p.id_asta_servizio, 
      p.note, p.modificata_da, 
      p.data_ultima_modifica, p.foto, p.sospesa,
      st_y(st_transform(gp.geoloc, 4326)) as lat,
      st_x(st_transform(gp.geoloc, 4326)) as lon,
      ST_AsGeoJSON(ST_Transform(gp.geoloc, 4326))::json AS geometry,
      string_agg(distinct e.tipo_elemento::text, ',') as tipi_elementi,
      CASE
          WHEN bool_and(e.tipo_elemento IN (
            select te2.tipo_elemento 
            from elem.tipi_elemento te2 
            where te2.descrizione ilike 'trespolo%'
            )) THEN 1
          ELSE 0
      END AS solo_trespoli,
      count(distinct e.id_elemento) as n_elem,
      count(distinct e.tipo_elemento) as n_tipo_elem,
      count(distinct ep.id_elemento) as n_elem_priv,
      CASE
            WHEN count(distinct e.id_elemento) = count(distinct ep.id_elemento) THEN true
            ELSE false
      END AS is_pap,
      CASE WHEN COUNT(DISTINCT tr.colore_piazzola) = 1 THEN MAX(tr.colore_piazzola) ELSE NULL END AS colore_piazzola,
      bool_and(te.manutenzione IS NULL) as manutenzione
      FROM elem.piazzole p
      join elem.aste a on a.id_asta = p.id_asta
      join topo.vie v on v.id_via = a.id_via
      join geo.piazzola gp on gp.id = p.id_piazzola
      join elem.elementi e on e.id_piazzola = p.id_piazzola
      left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento
      left join elem.tipi_elemento te ON te.tipo_elemento = e.tipo_elemento
      left join elem.tipi_rifiuto tr ON tr.tipo_rifiuto = te.tipo_rifiuto
      left join topo.ut u on u.id_ut = a.id_ut
      left join geo.unita_urbanistiche uu ON uu.id = a.id_uu
      left join elem.criticita_piazzole cp on cp.id_piazzola = p.id_piazzola 
      left join elem.criticita c on c.id = cp.id_criticita 
      left join elem.transitabilita t on t.id_transitabilita = p.id_transitabilita
      WHERE p.id_piazzola = $1
      group by
      a.id_ut, u.descrizione, 
      a.id_uu, uu.descrizione, p.id_asta,
      v.id_via, v.nome, t.descrizione, 
      p.id_piazzola, p.numero_civico, 
      p.lettera_civico, p.colore_civico,
      p.riferimento, p.ecopunto, 
      p.suolo_privato, p.id_asta_servizio, 
      p.note, p.modificata_da, 
      p.data_ultima_modifica, p.foto,
      st_y(st_transform(gp.geoloc, 4326)),
      st_x(st_transform(gp.geoloc, 4326)),
      ST_AsGeoJSON(ST_Transform(gp.geoloc, 4326))";
      $result_piazzola = pg_prepare($conn_sit, "my_query_piazzola", $query_piazzola);
      $result_piazzola = pg_execute($conn_sit, "my_query_piazzola", array($id_piazzola));

      $geojson=array(
        'type' => 'FeatureCollection',
        'features' => array()
      );
      //echo "Inizio ciclo while per aggiungere le features...\n";
      require_once './layers/geojson.php';

      while($r = pg_fetch_assoc($result_piazzola)) {
        $id_piazzola = $r['id_piazzola'];
        $id_via = $r['id_via'];
        $via = $r['nome'];
        $civico = $r['numero_civico'].$r['lettera_civico'].$r['colore_civico'];
        $riferimento = $r['riferimento'];
        $transitabilita = $r['transitabilita'];
        $ecopunto = $r['ecopunto'];
        $suoloPrivato = $r['suolo_privato'];
        $id_asta_servizio = $r['id_asta_servizio'];
        $criticita = explode(',', $r['criticita']);;
        $id_criticita = explode(',', $r['id_criticita']);;
        $uu = $r['uu'];
        $ut = $r['ut'];
        $modificatoDa = $r['modificata_da'];
        $modificatoIl = $r['data_ultima_modifica'];
        $note = $r['note'];
        $check_foto = $r['foto'];
        $sospesa = $r['sospesa'];
        $lat = $r['lat'];
        $lon = $r['lon'];
        $soloTrespoli = $r['solo_trespoli'];
        $n_elem_piazzola = $r['n_elem'];
        $n_tipo_elem = $r['n_tipo_elem'];
        $is_pap = $r['is_pap'];
        $id_uu = $r['id_uu'];
        $id_ut = $r['id_ut'];
        $manutenzione = $r['manutenzione'];
        $id_asta_piazzola = $r['id_asta'];
        $geojson['features'][] = geojsonFeature($r['geometry'], [
                'id_piazzola' => $r['id_piazzola'],
                'id_ut' => $r['id_ut'],
                'id_via' => $r['id_via'],
                'id_asta' => $r['id_asta'],
                'colore_piazzola' => $r['colore_piazzola'] ?? $colore_piazzola,
                'is_pap' => $r['is_pap'],
                'ecopunto' => $r['ecopunto'],
                'suolo_privato' => $r['suolo_privato']
        ]);
      }
      $dt = new DateTime($modificatoIl);

      $mesi = [
        '01' => 'gennaio',
        '02' => 'febbraio',
        '03' => 'marzo',
        '04' => 'aprile',
        '05' => 'maggio',
        '06' => 'giugno',
        '07' => 'luglio',
        '08' => 'agosto',
        '09' => 'settembre',
        '10' => 'ottobre',
        '11' => 'novembre',
        '12' => 'dicembre'
      ];

      
      $modificatoIlFormat = $dt->format('d').' '.$mesi[$dt->format('m')].' '.$dt->format('Y H:i');


      // Per gestione permessi su bottoni, diabilitati o nascosti
      $piazzola_sospesa=0;
      if ($sospesa === 't') {
        //$check_edit_piazzola=0;
        $piazzola_sospesa=1;
      }

      //$check_edit_piazzola=0;
      if ($check_edit_piazzola==0 or $piazzola_sospesa==1){
        $edit = 'disabled';
        $hide = 'hidden';
      }else{
        $edit = '';
        $hide = '';
      }

      // verifico se gli elementi della piazzola hanno già un cliente (macrocategoria e nominativo) associato e se sì, se è lo stesso per tutti gli elementi della piazzola
      // se categoria/nominativo sono gli stessi allora li propngo già nell'accordion del pap, altrimenti lascio vuoti i campi e l'utente dovrà selezionare la macrocategoria e inserire il nominativo
      require_once('backoffice/get_clienti_piazzola_pap_query.php');
      $clienti = getClientiPiazzola($conn_sit, $id_piazzola);

      // array_column() genera una lista con tutti i valori della colonna passata

      // ottenevo il n di elementi con un cliente associato e se = a numero di elementi totali in piazzola allora la definisco pap
      // commentato perchè stessa informazione ottenuta direttamente nella query $query_piazzola usando n_elem = n_elem_privati
      /*$n_elementi_cliente = array_column($clienti, 'n_elem');
      if (array_sum($n_elementi_cliente) == $n_elem_piazzola){
        $testo= ' tutti gli elementi hanno un cliente';
      } else {
        $testo= ' NON tutti gli elementi hanno un cliente';
      }*/

      $macro_categoria_exist = array_column($clienti, 'id_macro_categoria');
      if (count($macro_categoria_exist) > 0 && count(array_unique($macro_categoria_exist)) === 1) {
          $id_macro_categoria_selezionata = $macro_categoria_exist[0];
      } else {
          $id_macro_categoria_selezionata = null;
      }

      $nome_cliente_exist = array_column($clienti, 'descrizione');
      if (count($nome_cliente_exist) > 0 && count(array_unique($nome_cliente_exist)) === 1) {
          $nome_cliente_selezionato = $nome_cliente_exist[0];
      } else {
          $nome_cliente_selezionato = null;
      }

      //se trovo una macro_categori e/o un nominativo cliente mostro l'accordion già aperto
      if ($id_macro_categoria_selezionata != null || $nome_cliente_selezionato != null){
        $accordion_show = 'show';
      }

      /*$soloTrespoli = 1;
      $piazzola_sospesa=1;*/
      
    ?>


    <!--div class="container-fluid"-->
    <div id="modal_dettagli_piazzola">
      <div class="row g-3 align-items-center">
        <h3>Dettagli piazzola <?php echo $id_piazzola; ?> - <?php echo $via; ?>
          <?php
            if ($civico){
              echo ', '.$civico;
            }
            if ($riferimento){
              echo ' - '.$riferimento;
            }
            if ($soloTrespoli) {
                echo ' - <i class="fa-regular fa-trash-can"></i> Solo trespoli';
            }
            if ($piazzola_sospesa==1){
              echo ' - <i class="fa-solid fa-circle-pause"></i> Temporaneamente dismessa'; 
            }
            if ($is_pap==='t'){
              echo ' - <i class="fa-solid fa-circle-user"></i> PAP'; 
            }
          ?>
          <a href="#map-card" class="btn btn-primary">
              <i class="fa-solid fa-map-location" title="Visualizza su mappa"></i> 
          </a>
        </h3>
        <!--hr style="border: 1px solid #006426; opacity: unset;"-->
      </div>
      <div class="row g-3 align-items-center">
        <!--div class="card shadow-sm p-3">
          <div class ="card-body"-->
              <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="dati-tab" data-bs-toggle="tab" data-bs-target="#dati-tab-pane" type="button" role="tab" aria-controls="dati-tab-pane" aria-selected="true">Dati</button>
                </li>
                <!--li class="nav-item" role="presentation">
                  <button class="nav-link" id="posizione-tab" data-bs-toggle="tab" data-bs-target="#posizione-tab-pane" type="button" role="tab" aria-controls="posizione-tab-pane" aria-selected="false">Posizione</button>
                </li-->
                <!--li class="nav-item" role="presentation">
                  <button class="nav-link" id="foto-tab" data-bs-toggle="tab" data-bs-target="#foto-tab-pane" type="button" role="tab" aria-controls="foto-tab-pane" aria-selected="false">Foto</button>
                </li-->
                <!--li class="nav-item" role="presentation">
                  <button class="nav-link" id="disabled-tab" data-bs-toggle="tab" data-bs-target="#disabled-tab-pane" type="button" role="tab" aria-controls="disabled-tab-pane" aria-selected="false" disabled>Disabled</button>
                </li-->
              </ul>
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="dati-tab-pane" role="tabpanel" aria-labelledby="dati-tab" tabindex="0">
                  <div class="row g-3 align-items-stretch">
                    <div class="col-md-10 d-flex">
                      <div class="card shadow-sm flex-fill">
                        <div class ="card-body">
                          <form id="dettagli_piazzola" name="dettagli_piazzola" method="post" autocomplete="off" action="">
                            <input type="hidden" name="id_piazzola" value="<?php echo $id_piazzola; ?>">
                            <div class="row g-3 align-items-center">
                              <div class="col-md-4">
                                <label class="form-check-label" for="riferimento"><b>Riferimento</b></label>
                                <input type="text" class="form-control" id="riferimento" name="riferimento" value="<?php echo $riferimento; ?>" <?php echo $edit;?>>
                              </div>
                              <div class="col-md-2">
                                <label for="civico" class="form-check-label"><b>Civico</b></label>
                                <!--input type="text" class="form-control" id="civico" name="civico" value="<?php echo $civico; ?>" <?php echo $edit; ?>-->
                                <select class="form-select" data-live-search="true" name="civico" id="civico" <?php echo $edit; ?>>
                                  <option value="">Seleziona civico</option>
                                  <!--riparto da qui e vedo se riesco a gestirlo con il fetch su get_civici.php così evito di duplicare troppo codice (8)vedi chat gpt)-->
                                  <?php
                                    $query_civico = 'SELECT testo FROM geo.v_civici where cod_strada = $1 
                                      order by
                                      numero::int,
                                      CASE WHEN lettera IS NULL THEN 0 ELSE 1 END,
                                      lettera,
                                      colore::int';
                                    $result_civ = pg_prepare($conn_sit, "select_civ", $query_civico);
                                    $result_civ = pg_execute($conn_sit, "select_civ", array($id_via));
                                    while ($row = pg_fetch_assoc($result_civ)) {
                                        $selected = ($row['testo'] == $civico) ? 'selected' : '';
                                        echo "<option value=\"{$row['testo']}\" $selected>{$row['testo']}</option>";
                                    }
                                  ?>
                                </select>
                              </div>
                              <div class="col-md-3">
                                <label for="transitabilita" class="form-check-label"><b>Transitabilità</b></label>
                                <select class="form-select" name="transitabilita" id="transitabilita" <?php echo $edit; ?>>
                                  <!--option value="">Seleziona transitabilità</option-->
                                  <?php
                                    $query_transitabilita = 'SELECT id_transitabilita, descrizione FROM elem.transitabilita';
                                    $result_transitabilita = pg_query($conn, $query_transitabilita);
                                    while ($row = pg_fetch_assoc($result_transitabilita)) {
                                        $selected = ($row['descrizione'] == $transitabilita) ? 'selected' : '';
                                        echo "<option value=\"{$row['id_transitabilita']}\" $selected>{$row['descrizione']}</option>";
                                    }
                                  ?>
                                </select>
                              </div>
                              <div class="col-md-3">
                                <label for="criticita" class="form-check-label">
                                  <b>Criticità:</b>
                                </label>
                                <select class="form-select" name="criticita[]" id="criticita" multiple <?php echo $edit; ?>>
                                  <?php 
                                    $query_criticita = 'SELECT id, descrizione FROM elem.criticita ORDER BY descrizione';
                                    $result_criticita = pg_query($conn, $query_criticita);
                                    while ($row = pg_fetch_assoc($result_criticita)) {
                                        $selected = ( isset($id_criticita) && is_array($id_criticita) && in_array($row['id'], $id_criticita) ) ? 'selected' : '';
                                        echo "<option value=\"{$row['id']}\" $selected> {$row['descrizione']} </option>";
                                    }
                                  ?>
                                </select>
                              </div>
                            </div>
                            <div class="row g-3 align-items-center">
                              <div class="col-md-2">
                                <label class="form-check-label" for="ecopunto"><b>Ecopunto</b></label>
                                <input class="form-check-input" type="checkbox" name="ecopunto" id="ecopunto" <?php echo ($ecopunto) ? 'checked' : ''; ?> <?php echo $edit; ?>>
                              </div>
                              <div class="col-md-2">
                                <label class="form-check-label" for="suolo_privato"><b>Suolo privato</b></label>
                                <input class="form-check-input" type="checkbox" name="suolo_privato" id="suolo_privato" <?php echo ($suoloPrivato) ? 'checked' : ''; ?> <?php echo $edit; ?>>
                              </div>
                              <!--
                              <div class="col-md-4">
                                <COMMENTO deve essere un flag solo esplicativo o deve triggerare qualche azione?>
                                <label for="solo_trespoli"><b>Solo trespoli</b></label>
                                <?php
                                  if ($soloTrespoli) {
                                      echo '<i class="bi bi-check-square-fill" style="font-size:16px; color: #0d6efd;"></i>';
                                  } else {
                                      echo '<i class="fa-solid fa-xmark" style="font-size:16px; color: #aa0000;"></i>';
                                  }
                                ?>
                                <COMMENTO input class="form-check-input" type="checkbox" name="solo_trespoli" id="solo_trespoli" <?php echo ($soloTrespoli) ? 'checked' : ''; ?> <?php echo $edit; ?>>
                              </div>
                              -->
                              <div class="col-md-2">
                                <label for="id_asta_servizio" class="form-check-label"><b>Asta di servizio</b></label>
                                <input type="text" class="form-control" name="id_asta_servizio" id="id_asta_servizio" value="<?php echo $id_asta_servizio; ?>" <?php echo $edit; ?>>
                              </div>
                              <div class="col-md-6">
                                <label for="note" class="form-check-label"><b>Note</b></label>
                                <input type="text" class="form-control" id="note" name="note" value="<?php echo $note; ?>" <?php echo $edit; ?>>
                              </div>
                            </div>
                            <!-- qui c'era l'accordion del PAP spostato sotto con bottone salva indipendente quindi fuori dal form-->
                            <div class="row g-3 align-items-center">
                              <div class="col-md-9">
                                <span class="me-5"><b>UU&nbsp;&nbsp;&nbsp;</b><?php echo $uu; ?></span> 
                                <span><b>UT&nbsp;&nbsp;&nbsp;</b><?php echo $ut; ?></span>
                                <!--br-->
                                <span class="ms-5"><b>Ultima modifica&nbsp;&nbsp;&nbsp;</b><?php echo $modificatoDa.' - '.$modificatoIlFormat; ?></span>
                              </div>
                              <div class="col-md-3 d-flex justify-content-end">
                                <?php if($piazzola_sospesa==1){?>
                                  <button type="button" class="btn btn-secondary ms-2" title="Ripristina la piazzola" id="ripristina_btn" onclick="confermaRipristinaPiazzola(<?php echo $id_piazzola; ?>, '<?php echo $manutenzione; ?>')" <?php if($check_edit_piazzola==0){echo $edit;} ?>><i class="fa-solid fa-play"></i></button>
                                <?php }else{?>
                                  <button type="button" class="btn btn-secondary ms-2" title="Dismetti temporaneamente la piazzola" id="dismiss_btn" onclick="confermaDismettiPiazzola(<?php echo $id_piazzola; ?>, '<?php echo $manutenzione; ?>')" <?php if($check_edit_piazzola==0){echo $edit;} ?>><i class="fa-solid fa-pause"></i></button>
                                <?php
                                }
                                ?>
                                <button type="button" class="btn btn-danger ms-2" title="Elimina piazzola" id="delete_btn" onclick="confermaEliminaPiazzola(<?php echo $id_piazzola; ?>, '<?php echo $manutenzione; ?>')" <?php echo $edit; ?>><i class="fa-solid fa-xmark"></i></button>
                                <button type="submit" title="Salva modifiche ai dettagli della piazzola" id="save_btn" class="btn btn-primary ms-2" <?php echo $edit; ?>><i class="fa-solid fa-floppy-disk"></i></button>
                              </div>
                              <div class="form-group">
                                  <div id="toast" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
                              </div>
                            </div>
                          </form>
                          <div class="modal fade" id="viewMemberModalP" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
                            <div class="modal-dialog modal-dialog-scrollable modal-xl" >
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h4 class="modal-title" id="exampleModalLabelP"><?php echo $id_piazzola; ?> - <?php echo $via; ?>, <?php echo $civico; ?> - <?php echo $riferimento; ?></h4>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="body_modalP">
                                          <!-- output data here-->
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row g-3 align-items-center" style="margin-bottom: unset;">
                            <div class="accordion" id="accordionPap">
                              <div class="accordion-item">
                                <h2 class="accordion-header">
                                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="padding: 8px; background-color: #f4f4f4;">
                                    Piazzola PAP?
                                  </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse <?php echo $accordion_show; ?>" data-bs-parent="#accordionPap">
                                  <div class="accordion-body">
                                    <div class="row g-3 align-items-end" style="margin-bottom: unset;">
                                      <div class="form-group col-md-5">
                                        <label for="tcliente"> Tipo cliente </label> <font color="red">*</font> 
                                        <select name="tcliente" id="tcliente" class="form-control" data-live-search="true" data-size="5" <?php echo $edit; ?>>
                                          <option name="tcliente" value="">Seleziona un tipo cliente</option>
                                          <?php 
                                          $queryc="select * from utenze.macro_categorie mc
                                          order by 2";
                                          $resultc = pg_query($conn_sit, $queryc); 
                                          while($rc = pg_fetch_assoc($resultc)) {
                                            $selected_macro = ($rc['id_macro_categoria'] == $id_macro_categoria_selezionata) ? 'selected' : '';           
                                          ?>   
                                              <option name="tcliente" value="<?php echo $rc['id_macro_categoria']?>" <?php echo $selected_macro; ?> ><?php echo $rc['descrizione'] ?></option>
                                          <?php } ?>
                                        </select>
                                      </div>
                                      <div class="form-group col-md-5">
                                        <label for="desc"> Nominativo attività </label> <font color="red">*</font>
                                        <input type="text" name="desc" id="desc" class="form-control" value="<?php echo $nome_cliente_selezionato; ?>" <?php echo $edit; ?>>
                                      </div>
                                      <div class="col-md-2 text-center">
                                        <!--?php if ($civico == '') { ?>
                                          <div class="alert alert-warning" role="alert" style="margin-bottom: unset;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Inserire un civico per allineare il cliente
                                          </div-->
                                        <!--?php } else { ?-->
                                          <button class="btn btn-primary btn-allinea" title="Associa cliente agli elementi in piazzola" style="cursor:pointer" onclick="allineaCliente(<?php echo $id_piazzola;?>, <?php echo $id_via; ?>, '<?php echo $civico; ?>', <?php echo $id_uu; ?>, <?php echo $id_ut; ?>, 1, 'all')" <?php echo $edit; ?>>
                                          <i class="fa-solid fa-users"></i> Allinea cliente</button>
                                        <!--?php } ?-->
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-2 d-flex">
                      <div class="card shadow-sm flex-fill">
                        <div class ="card-body">
                          <?php echo fotoPiazzola($id_piazzola, $check_foto, $check_edit_piazzola, $piazzola_sospesa); ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--hr style="border: 1px solid #006426; opacity: unset;"-->
                  <div class="card shadow-sm p-3" style="margin-bottom: 1%;">
                    <div class ="card-body">
                      <h5>Elementi 
                        <button class="btn btn-sm btn-primary" id="add_elemento_btn" title="Aggiungi elemento" onclick="aggiungiElementoGenerico(<?php echo $id_piazzola; ?>)" <?php echo $edit; ?>>
                          <i class="fa-solid fa-circle-plus"></i> Aggiungi elemento
                        </button> 
                      </h5>
                        <div id="tabella">
                          <div class="table-responsive-sm">
                            <div id="toolbar"> 
                              <!--button id="export-btn-filtered" class="btn btn-primary" title="Esporta file Excel filtrato"><i class="fa-solid fa-filter"></i>Esporta tabella filtrata</button-->
                            </div>
                            <table  id="elementi" class="table-hover table-sm"
                              data-locale="it-IT"
                              idfield="ID" 
                              data-escape="false"
                              data-show-columns="false"
                              data-group-by="false"
                              data-group-by-field=""
                              data-show-search-clear-button="false"   
                              data-show-export="false" 
                              data-search="false" 
                              data-click-to-select="true" data-show-print="false"  
                              data-virtual-scroll="false"
                              data-show-pagination-switch="false"
                              data-pagination="true" data-page-size=100 data-page-list=[10,25,50,75,100,200,500]
                              data-side-pagination="false" 
                              data-show-refresh="false" data-show-toggle="false"
                              data-show-columns="false"
                              data-search-on-enter-key="true"
                              data-filter-control="false"
                              data-sort-select-options = "true"
                              data-export-data-type="all"
                              data-url="./tables/elementi_piazzola.php?idp=<?php echo $id_piazzola;?>"  
                              data-toolbar="#toolbar"
                              data-show-footer="false"
                              data-query-params="queryParams"
                              data-row-style="rowStyle"
                            >
                            <thead>
                              <tr>
                                    <!--th data-checkbox="true" data-field="id"></th-->  
                                    <!--th data-field="state" data-checkbox="true" ></th--> 
                                    <!--th data-field="ID" data-sortable="true" data-visible="false" data-filter-control="false">ID</th--> 
                                    <th data-field="rifiuto" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="coloreRifiutoFormatter">Rifiuto</th>
                                    <th data-field="tipologia_elemento" data-sortable="true" data-visible="false" data-filter-control="input" data-width="50">Raccolta</th>
                                    <th data-field="tipo_elem" data-sortable="true" data-visible="true" data-filter-control="input" data-formatter="troncaFormatter">Tipo elemento</th>
                                    <th data-field="num" data-sortable="false" data-visible="true" data-filter-control="input" data-width="50">N°<!--i class="fa-solid fa-trash-can"></i--></th>
                                    <th data-field="num_interventi_totali" data-sortable="true" data-visible="true" data-width="50" data-formatter="richiesteFormatter" data-events="richiesteEvents">Interventi</th>
                                    <th data-field="cliente" data-sortable="true" data-visible="true" data-filter-control="input">Cliente</th>
                                    <th data-field="id_utenzapap" data-sortable="false" data-visible="false">Utenza pap</th>
                                    <!--th data-field="percent_riempimento" data-sortable="true" data-visible="true" data-filter-control="false" data-width="50">% Riemp.</th-->
                                    <!--th data-field="freq_stimata" data-sortable="true" data-visible="true" data-filter-control="false" data-width="50">Freq. CdS</th-->
                                    <th data-field="freq_reale" data-sortable="true" data-visible="true" data-filter-control="false" data-width="50">Freq. Reale</th>
                                    <th data-field="freq_consunt" data-sortable="true" data-visible="true" data-filter-control="false" data-width="50">Freq. Consunt.</th>
                                    <th data-visible="true" data-formatter="mostraBottoniEditFormatter" data-events="actionEditEvents" data-class="azioni-col"></th>
                                    <?php if($check_edit_piazzola != 0) {?>
                                      <th data-visible="true" data-formatter="mostraBottoniUteFormatter" data-events="actionUteEvents" data-class="azioni-col"></th>
                                    <?php }?>
                                    <th data-visible="true" data-formatter="mostraBottoniPercFormatter" data-events="actionPercEvents" data-class="azioni-col"></th>
                                    <th data-visible="true" data-formatter="mostraBottoniConsFormatter" data-events="actionConsEvents" data-class="azioni-col"></th>
                              </tr>
                            </thead>
                          </table>
                          <div class="modal fade" id="viewMemberModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
                            <div class="modal-dialog modal-dialog-scrollable modal-xl" >
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h4 class="modal-title" id="exampleModalLabel"><?php echo $id_piazzola; ?> - <?php echo $via; ?>, <?php echo $civico; ?> - <?php echo $riferimento; ?></h4>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="body_modal">
                                          <!-- output data here-->
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card shadow-sm p-3" id="map-card">
                    <div class ="card-body">
                      <div id="mappa_piazzola"></div>
                      <div id="toast_map" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
                    </div>
                        
                    </div>
                  </div>
                </div>
                <!--div class="tab-pane fade" id="posizione-tab-pane" role="tabpanel" aria-labelledby="posizione-tab" tabindex="0">
                  <div id="mappa_piazzola"></div>
                  Qui sarà mostrata la posizione della piazzola con id <?php echo $id_piazzola; ?>, con possibilità di modificarla attraverso una mappa interattiva (es. leaflet)
                </div-->
                <!--div class="tab-pane fade" id="foto-tab-pane" role="tabpanel" aria-labelledby="foto-tab" tabindex="0">
                  Qui saranno mostrate le foto della piazzola con id <?php echo $id_piazzola; ?>, con possibilità di modificarle
                </div-->
                <!--div class="tab-pane fade" id="disabled-tab-pane" role="tabpanel" aria-labelledby="disabled-tab" tabindex="0">...</div-->
              </div>
          <!--/div>
        </div--> 
      </div>     
    </div>

    <?php
      require_once('req_bottom.php');
      require('./footer.php');
    ?>

    <script>

      // funzione triggerata dal bottone aggiungi elemento (anche con tipo_elemnto diverso da quelli già presenti in piazzola), 
      // mostra un overlay con form per selezionare il tipo di elemento da aggiungere alla piazzola
      function aggiungiElementoGenerico(id_piazzola){
        console.log('aggiungo un elemento scegliendo il tipo')
        $("#modalDettaglioPiazzola").append(`
				<div id="overlayAddElemento" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
				style="background: rgba(0,0,0,.2); z-index: 2000;">
					<div class="bg-white rounded shadow p-3 " id="overlayAddElementodiv" style="width: 50%;">
              <h5 class="text-center">Aggiungi elemento alla piazzola ${id_piazzola}</h5>
              <br>
              <form id="aggiungi_elemento_form" name="aggiungi_elemento_form" method="post" autocomplete="off" action="">
                <input type="hidden" name="id_piazzola" value="${id_piazzola}">
                <input type="hidden" name="exists" value="">
                <div class="row align-items-center g-3">
                  <div class="col-md-5">
                    <label class="form-label mb-0" for="rifiuto"><b>Rifiuto</b></label>
                    <select class="form-select w-auto" name="rifiuto" id="rifiuto" <?php echo $edit;?> required>
                      <option value="">Seleziona..</option>
                      <?php
                      $query_rifiuto = 'SELECT tipo_rifiuto, nome FROM elem.tipi_rifiuto ORDER BY nome';
                      $result_rifiuto = pg_query($conn, $query_rifiuto);
                      while ($r = pg_fetch_assoc($result_rifiuto)) {
                        echo "<option value=\"{$r['tipo_rifiuto']}\">{$r['nome']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label mb-0" for="tipoElemento"><b>Tipo elemento</b></label>
                    <select class="form-select w-auto" name="tipoElemento" id="tipoElemento" <?php echo $edit;?> disabled required>
                      <option value="">Seleziona tipo elemento</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label mb-0" for="n_elem"><b>Elementi</b></label>
                    <input type="number" class="form-control" id="n_elem" name="n_elem" placeholder="Inserisci numero di elementi" value="1" required>
                  </div>
                </div>
                <div class="row align-items-center g-3">
                  <div class="col-md-6">
                    <label class="form-label mb-0" for="new_riempimento"><b>Riempimento</b></label>
                    <input type="number" class="form-control" id="new_riempimento" name="new_riempimento" value="0" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label mb-0" for="new_freq"><b>Frequenza CdS</b></label>
                    <input type="number" class="form-control" id="new_freq" name="new_freq" value="0" required>
                  </div>
                </div>
              </form>
              <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
              <div class="text-center">
                <button type="button" class="btn btn-primary text-center" id="add_elemento_gen_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o aggiornamento SIT" onclick="aggiungiElementoGen(this, ${id_piazzola})"><i class="fa-solid fa-display"></i> Nessun intervento</button>
                <button type="button" class="btn btn-primary text-center" id="add_elemento_gen_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzioneGen(this, ${id_piazzola})"><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
              </div>
                <hr>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" id="btnChiudiAddEl" onclick="chiudiAddElemento()"> Chiudi </button>
              </div>
					</div>
				</div>`); 

        $("#rifiuto").on('change', function () {

            let id_rifiuto = this.value;

            console.log('Rifiuto selezionato: ' + id_rifiuto);

            if (id_rifiuto) {
                $('#tipoElemento').prop('disabled', false);
            }

            fetch('backoffice/get_tipo_elemento.php?id_rifiuto=' + id_rifiuto)
                .then(response => response.json())
                .then(data => {

                    let options = '<option value="">Seleziona tipo elemento</option>';

                    data.forEach(function(tipo) {
                        options += `
                            <option value="${tipo.tipo_elemento}">
                                ${tipo.nome}
                            </option>
                        `;
                    });

                    $('#tipoElemento').html(options);

                })
                .catch(error => {
                    console.error(error);
                });

        });

        $("#tipoElemento").on('change', function () {

            let tipoElemento = this.value;

            if (!tipoElemento) {
                $('#new_riempimento').val('0');
                $('#new_freq').val('0');
                return;
            }

            fetch('backoffice/get_dettagli_tipo_elemento.php?tipo_elemento=' + tipoElemento+'&id_piazzola=' + id_piazzola)
                .then(response => response.json())
                .then(data => {


                    data.forEach(function(dettaglio) {
                        $('#new_riempimento').val(dettaglio.percent_riempimento || '');
                        $('#new_freq').val(dettaglio.freq_stimata || '');
                        $('#exists').val(dettaglio.exists || '');
                        if (dettaglio.manutenzione != 't') {
                            $('#add_elemento_gen_intervento_btn').prop('disabled', true);
                        }
                    });
                    
                })
                .catch(error => {
                    console.error(error);
                });

        });
         
        $("#rifiuto").select2({
          width: '100%',
          dropdownParent: $('#overlayAddElementodiv'),
          placeholder: 'Seleziona rifiuto',
          allowClear: true
        });
        
        $("#tipoElemento").select2({
          width: '100%',
          dropdownParent: $('#overlayAddElementodiv'),
          placeholder: 'Seleziona tipo elemento',
          allowClear: true
        });

      }

      function aggiungiElementoGen(btn, id_piazzola) {
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Aggiungerà un elemento nella piazzola ' + id_piazzola + 'senza chiedere alcun intervento' );

        event.preventDefault();

        /*var formData = $('#aggiungi_elemento_form').serialize();
        console.log(formData);*/

        if (!$('#rifiuto').val() || !$('#tipoElemento').val() || !$('#n_elem').val() || !$('#new_riempimento').val() || !$('#new_freq').val()) {
          showToast('Completa tutti i campi per aggiungere l\'elemento.', 'error', 'modal');
          return;
        }


        if ($('#n_elem').val() <= 0 || $('#new_riempimento').val() < 0 || $('#new_freq').val() < 0) {
          showToast('Inserisci valori validi.', 'error', 'modal');
          return;
        }

        let tipo_elemento = $('#tipoElemento').val();
        let n_elem = $('#n_elem').val();
        let new_riempimento = $('#new_riempimento').val();
        let new_freq = $('#new_freq').val();
        let exists = $('#exists').val();
        //let id_utenzapap = null;
        const id = `${id_piazzola}_${tipo_elemento}`;
        console.log('Invio id:', id);
        
        if ($('#add_elemento_gen_intervento_btn').prop('disabled', false)) {
          $('#add_elemento_gen_intervento_btn').prop('disabled', true);
        }

        $.ajax({
          url: 'backoffice/add_elemento.php',
          method: 'POST',
          data: { id_piazzola: id,
            origine: 'dettagli_piazzola',
            n_elem: n_elem,
            new_riempimento: new_riempimento,
            new_freq: new_freq,
            exists: exists
          },
          success: function (response) {
            //console.log(response);
            showToast(response, 'success', 'modal');
            // Aggiorna la tabella degli elementi
            $('#elementi').bootstrapTable('refresh');
            
          },
          error: function (jqXHR, textStatus, errorThrown) {
            showToast('Errore durante l\'aggiunta dell\'elemento.', 'error', 'modal');
            console.error(errorThrown);
          }
        });
      }

      function inviaManutenzioneGen(btn, id_piazzola) {
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Aggiungerà un elemento nella piazzola ' + id_piazzola + ' richiedendo intervento' );

        event.preventDefault();

        if (!$('#rifiuto').val() || !$('#tipoElemento').val() || !$('#n_elem').val() || !$('#new_riempimento').val() || !$('#new_freq').val()) {
          showToast('Completa tutti i campi per aggiungere l\'elemento.', 'error', 'modal');
          return;
        }

        if ($('#n_elem').val() <= 0 || $('#new_riempimento').val() < 0 || $('#new_freq').val() < 0) {
          showToast('Inserisci valori validi.', 'error', 'modal');
          return;
        }

        let tipo_elemento = $('#tipoElemento').val();
        let n_elem = $('#n_elem').val();
        let new_riempimento = $('#new_riempimento').val();
        let new_freq = $('#new_freq').val();
        let exists = $('#exists').val();


        const id = `${id_piazzola}_${tipo_elemento}`;
        console.log('Invio id:', id);

        tipo_intervento = 'add';
        check_piazzola = 0;

        $('#add_elemento_gen_btn').prop('disabled', true);

        $.ajax({
          url: 'backoffice/go_movimentazione.php',
          method: 'POST',
          data: { id_piazzola: id,
            tipo_intervento: tipo_intervento,
            check_piazzola: check_piazzola,
            origine: 'dettagli_piazzola',
            n_elem: n_elem,
            new_riempimento: new_riempimento,
            new_freq: new_freq,
            exists: exists
          },
          success: function (response) {
            //console.log(response);
            showToast(response, 'success', 'modal');
            // Aggiorna la tabella degli elementi
            $('#elementi').bootstrapTable('refresh');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            
            showToast('Errore durante l\'invio della richiesta a manutenzione cassonetti.', 'error', 'modal');
            console.error(errorThrown);
          }
        });
      }

      function chiudiAddElemento() {
        $("#overlayAddElemento").remove();
      }


      var $table = $('#elementi');

      $(function() {
        $table.bootstrapTable()
      });

      function rowStyle(row, index) {
        console.log('rowstyle', row.id_elemento, row.elem_sospesi);
        if (row.elem_sospesi === 't') {
          return {
            css: {
            '--bs-table-bg':'#c0c0c0',
            '--bs-table-color':'#6d6d6d'
            }
          }
        }

        if (row.id_tipo_intervento == '3') {
          return {
            css: {
            '--bs-table-bg':'#ff8e7c'
            }
          }
        }

        if (row.id_negativi == 1) {
				return {
					css: {
					'--bs-table-bg':'#c4ffbc'
					}
				}
			}
      }

      function coloreRifiutoFormatter(value, row, index) {
        return '<i class="fa-solid '+ row.fa_icon +'" style="color: ' + row.colore + '"></i> ' + row.rifiuto;
        /*return `
          <span class="fa-stack d-inline-flex align-items-center justify-content-center">
              <i class="fa-solid fa-square fa-stack-2x" style="color: ${row.colore}"></i>
              <i class="fa-solid ${row.fa_icon} fa-stack-1x fa-inverse"></i>
          </span> ${row.rifiuto}
        `*/
      }

      function troncaFormatter(value, row, index) {
        const max = 20;
        if (!value) return '';
        const tronca = value.length > max ? value.substring(0, max) + '…' : value;
        return `<span title="${value}">${tronca}</span>`;
      }

      /* INIZIO funzioni ed eventi per la colonna Interventi*/
      function richiesteFormatter(value, row, index) {
        if(value > 0){
          return `<i class="fa-solid fa-wrench"></i>
          <button type="button" class="btn btn-sm btn-richieste" data-bs-container="body" data-bs-toggle="popover" style="padding-left: 0px; padding-top:0px; border:none;"
          data-tipo-elemento=${row.tipo_elemento} data-id-piazzola=${<?php echo $id_piazzola;?>}>
            <i class="fa-regular fa-circle-question" style="margin-left: 0px;"></i>
          </button>
          `;
        }else{
          return '';
        }
      }

      window.richiesteEvents = {
        'click .btn-richieste': function (e, value, row, index) {
          const button = e.currentTarget;

          console.log('Richieste intervento per elemento', row.tipo_elemento, 'in piazzola', <?php echo $id_piazzola; ?>);

          $.ajax({
              url: './tables/get_richieste_intervento.php',
              type: 'POST',
              data: {
                id_piazzola: <?php echo $id_piazzola; ?>,
                id_tipo: row.tipo_elemento
              },
              success: function(response) {

                  let popover = bootstrap.Popover.getInstance(button);

                  if (popover) {
                      popover.dispose();
                  }

                  popover = new bootstrap.Popover(button, {
                      html: true,
                      sanitize: false,
                      content: response
                  });

                  popover.show();
              }
          });
        }
      };

      $(document).on('click', '.btn-close-popover', function() {
        $('.btn-richieste').each(function() {
            const popover = bootstrap.Popover.getInstance(this);

            if (popover) {
                popover.hide();
            }
        });

      });
      /* FINE funzioni ed eventi per la colonna Interventi*/

      /*INIZO FUNZIONI ED EVENTI COLONNE BOTTONI*/

      /* EDITING - dettagli elementi, aggiungi elemento, elimina elementi*/
      function mostraBottoniEditFormatter(value, row, index) {
        const disabilita = (row.disabilita == 1) || (row.id_negativi == 1);
        
          return `
            <button class="btn btn-sm btn-primary btn-edit" title="Modifica dettagli elemento" style="cursor:pointer"><i class="fa-solid fa-gears"></i></button>
            <button class="btn btn-sm btn-success btn-add" title="Aggiungi elemento" style="cursor:pointer" <?php echo $hide;?>><i class="fa-solid fa-plus"></i></button>
            <button class="btn btn-sm btn-danger btn-delete" title="Elimina gli elementi" style="cursor:pointer" <?php echo $hide;?> ${disabilita ? 'disabled' : ''} ><i class="fa-solid fa-xmark"></i></button>
          `;
      }

      window.actionEditEvents = {
        'click .btn-edit': function (e, value, row, index) {
          $.ajax({
            url: './dettaglio_elementi.php',
            type: 'POST',
            dataType: 'text',
            data: {
                id_piazzola: <?php echo $id_piazzola; ?>,
                id_tipo: row.tipo_elemento,
                check_edit: <?php echo $check_edit_piazzola; ?>
            },
            success: function(response) {
                //console.log(<?php echo $id_piazzola; ?>);
                $("#body_modal").html(`
                    <h5>Elementi - <b>${row.tipo_elem}</b></h5>
                    ${response}
                `);
                var modal = new bootstrap.Modal(document.getElementById("viewMemberModal"));
                modal.show();
            },
            error: function() {
                alert('Errore nel caricamento dei dati');
            }
          });
        },
        'click .btn-add': function (e, value, row, index) {
          $("#body_modal").html(`
            <div class="text-center">
              <h5>Verrà aggiunto un elemento di tipo <b>${row.tipo_elem}</b></h5>
              <br>
              <button type="button" class="btn btn-primary" id="add_elemento_tipo_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o aggiornamento SIT" onclick="aggiungiElemento(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap})"><i class="fa-solid fa-display"></i> Nessun intervento</button>
              <button type="button" class="btn btn-primary" id="add_elemento_tipo_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, null)" ${row.manutenzione ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
            </div>
            <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
            `);
          var modal = new bootstrap.Modal(document.getElementById("viewMemberModal"));
          modal.show();
        },
        'click .btn-delete': function (e, value, row, index) {
          $("#body_modal").html(`
            <div class="text-center">
              <h5>Verranno eliminati tutti gli elementi di tipo <b>${row.tipo_elem}</b></h5>
              <br>
              <button type="button" class="btn btn-primary" id="del_elementi_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o aggiornamento SIT" onclick="eliminaElementi(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap},'${row.ids_elementi}')"><i class="fa-solid fa-display"></i> Nessun intervento</button>
              <button type="button" class="btn btn-primary" id="del_elementi_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap},'${row.ids_elementi}')" ${row.manutenzione ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
            </div>
            <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
            `);
          var modal = new bootstrap.Modal(document.getElementById("viewMemberModal"));
          modal.show();
        }
      };

      function aggiungiElemento(button, id_piazzola, tipo_elemento, id_utenzapap) {
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Aggiungerà un elemento identico a quello corrispondente nella piazzola ' + id_piazzola + 'senza chiedere alcun intervento' );
        const id = `${id_piazzola}_${tipo_elemento}`;
        console.log('Invio id:', id);
        $('#add_elemento_tipo_intervento_btn').prop('disabled', true);

        $.ajax({
          url: 'backoffice/add_elemento.php',
          method: 'POST',
          data: { id_piazzola: id,
            id_utenzapap: id_utenzapap
          },
          success: function (response) {
            //console.log(response);
            showToast(response, 'success', 'modal');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            showToast('Errore durante l\'aggiunta dell\'elemento.', 'error', 'modal');
            console.error(errorThrown);
          }
        });
      }

      function eliminaElementi(button, id_piazzola, tipo_elemento, id_utenzapap, ids_elementi) {
      
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Eliminerà tutti gli elementi ('+ids_elementi+') di tipo '+tipo_elemento+' nella piazzola ' + id_piazzola + 'senza chiedere alcun intervento' );
        const id = `${id_piazzola}_${tipo_elemento}`;
        
        $('#del_elementi_intervento_btn').prop('disabled', true);

        $.ajax({
          url: 'backoffice/delete_elemento.php',
          method: 'POST',
          data: { id_piazzola: id,
            id_utenzapap: id_utenzapap,
            ids_elementi: ids_elementi
          },
          success: function (response) {
            //console.log(response);
            showToast(response, 'success', 'modal');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            showToast('Errore durante l\'eliminazione dell\'elemento.', 'error', 'modal');
            console.error(errorThrown);
          }
        });
      }

      

      /* FINE BOTTONI EDITING */

      /* UTENZE - cliente, utenza tari (da capire ancora se ricrearlo o no), allinea cliente*/
      function mostraBottoniUteFormatter(value, row, index) {
          /*return `
            <button class="btn btn-sm btn-primary btn-clienti" title="Clienti" style="cursor:pointer"><i class="fa-solid fa-user"></i></button>
            ${row.cliente != ' -  - ' ? `
              <button class="btn btn-sm btn-primary btn-tari" title="Dettagli utenza"><i class="fa-solid fa-building-user"></i></button>
              <button class="btn btn-sm btn-primary btn-allinea" title="Allinea cliente" style="cursor:pointer"><i class="fa-solid fa-users"></i></button>` : ''
            }
          `;*/
          return `
            <button class="btn btn-sm btn-primary btn-clienti" title="Clienti" style="cursor:pointer"><i class="fa-solid fa-user"></i></button>
          `;
      }

      window.actionUteEvents = {
        'click .btn-clienti': function (e, value, row, index) {
          $("#modalDettaglioPiazzola").append(`
				    <div id="overlayAddCliente" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
				    style="background: rgba(0,0,0,.2); z-index: 2000;">
              <div class="bg-white rounded shadow p-3 " id="overlayAddElementodiv" style="width: 50%;">
                <h5>Verrà associato il cliente a tutti gli elementi di tipo <b>${row.tipo_elem}</b></h5>
                <div class="row g-3 align-items-end" style="margin-bottom: unset;">
                  <div class="form-group col-md-5">
                    <label for="tclienteE"> Tipo cliente </label> <font color="red">*</font> 
                    <select name="tclienteE" id="tclienteE" class="form-control" data-live-search="true" data-size="5" <?php echo $edit; ?>>
                      <option name="tclienteE" value="">Seleziona un tipo cliente</option>
                      <?php 
                      $queryc="select * from utenze.macro_categorie mc
                      order by 2";
                      $resultc = pg_query($conn_sit, $queryc); 
                      while($rc = pg_fetch_assoc($resultc)) {
                        $selected_macro = ($rc['id_macro_categoria'] == $id_macro_categoria_selezionata) ? 'selected' : '';           
                      ?>   
                          <option name="tclienteE" value="<?php echo $rc['id_macro_categoria']?>" <?php echo $selected_macro; ?> ><?php echo $rc['descrizione'] ?></option>
                      <?php } ?>
                    </select>
                  </div>
                  <div class="form-group col-md-5">
                    <label for="descE"> Nominativo attività </label> <font color="red">*</font>
                    <input type="text" name="descE" id="descE" class="form-control" value="<?php echo $nome_cliente_selezionato; ?>" <?php echo $edit; ?>>
                  </div>
                  <div class="col-md-2 text-center">
                    <!--?php if ($civico == '') { ?>
                      <div class="alert alert-warning" role="alert" style="margin-bottom: unset;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Inserire un civico per allineare il cliente
                      </div-->
                    <!--?php } else { ?-->
                      <button class="btn btn-primary btn-allinea" title="Associa cliente agli elementi in piazzola" style="cursor:pointer" onclick="allineaCliente(<?php echo $id_piazzola;?>, <?php echo $id_via; ?>, '<?php echo $civico; ?>', <?php echo $id_uu; ?>, <?php echo $id_ut; ?>, 0, ${row.tipo_elemento})" <?php echo $edit; ?>>
                      <i class="fa-solid fa-users"></i> Aggiungi cliente</button>
                    <!--?php } ?-->
                  </div>
                </div>
                <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
                <hr>
                <div class="d-flex justify-content-end gap-2 mt-4">
                  <button type="button" class="btn btn-secondary" id="btnChiudiAddEl" onclick="chiudiAddCliente()"> Chiudi </button>
                </div>
              </div>
            </div>
          `);
          // form per modificare/aggiungere il cliente associato agli elementi di un certo tipo
          // il submit deve richiamare la funzione allineaClienti() per aggiornare il cliente su tutti gli elementi di quel tipo
          //allineaClienti(); devie quindi verificare se la richiesta viene dalla piazzola o dal dettaglio elementi
        },
        'click .btn-tari': function (e, value, row, index) {
          utenzaTariDettagli(<?php echo $id_piazzola; ?>);
        },
        'click .btn-allinea': function (e, value, row, index) {
          allineaClienti(<?php echo $id_piazzola; ?>);
        }
      };

      function chiudiAddCliente() {
        $("#overlayAddCliente").remove();
      }

      function utenzaTariDettagli(id_piazzola) {
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Per modificare dettagli utenza TARI su elementi della piazzola ' + id_piazzola);
      }

      function allineaClienti(id_piazzola) {
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Allineerà i clienti per tutti gli elementi della piazzola ' + id_piazzola);
      }

      /* FINE BOTTONI UTENZE */

      /* PERCORSI - percorsi associati, sospendi elementi */
      function mostraBottoniPercFormatter(value, row, index) {
        const disabilita = (row.disabilita == 1) || (row.id_negativi == 1);
        if(row.elem_sospesi ==='t'){
          return `
            <button class="btn btn-sm btn-primary btn-percorsi" title="Percorsi" style="cursor:pointer"><i class="fa-solid fa-road"></i></i></button> 
            <button class="btn btn-sm btn-secondary btn-riprist" title="Ripristina gli elementi" style="cursor:pointer" <?php echo $hide;?>><i class="fa-solid fa-play"></i></button>
            `;
        }else{
          return `
            <button class="btn btn-sm btn-primary btn-percorsi" title="Percorsi" style="cursor:pointer"><i class="fa-solid fa-road"></i></i></button> 
            <button class="btn btn-sm btn-secondary btn-dismiss" title="Dismetti temporaneamente gli elementi" style="cursor:pointer" <?php echo $hide;?> ${disabilita ? 'disabled' : ''}><i class="fa-solid fa-pause"></i></button>
          `;
        }
      }

      window.actionPercEvents = {
        'click .btn-percorsi': function (e, value, row, index) {
          percorsiDettagli(<?php echo $id_piazzola; ?>);
        },
        'click .btn-dismiss': function (e, value, row, index) {
          $("#body_modal").html(`
            <div class="text-center">
              <h5>Verrnno temporaneamenti dismessi tutti gli elementi di tipo <b>${row.tipo_elem}</b></h5>
              <br>
              <button type="button" class="btn btn-primary" id="dis_elementi_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o aggiornamento SIT" onclick="dismettiElementi(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, '${row.ids_elementi}')"><i class="fa-solid fa-display"></i> Nessun intervento</button>
              <button type="button" class="btn btn-primary" id="dis_elementi_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, '${row.ids_elementi}')" ${row.manutenzione ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
            </div>
            <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
            `);
          var modal = new bootstrap.Modal(document.getElementById("viewMemberModal"));
          modal.show();
        },
        'click .btn-riprist': function (e, value, row, index) {
          $("#body_modal").html(`
            <div class="text-center">
              <h5>Verrnno ripristinati tutti gli elementi di tipo <b>${row.tipo_elem}</b></h5>
              <br>
              <button type="button" class="btn btn-primary" id="riprist_elementi_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o aggiornamento SIT" onclick="ripristinaElementi(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, '${row.ids_elementi}')"><i class="fa-solid fa-display"></i> Nessun intervento</button>
              <button type="button" class="btn btn-primary" id="riprist_elementi_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, '${row.ids_elementi}')" ${row.manutenzione ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
            </div>
            <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
            `);
          var modal = new bootstrap.Modal(document.getElementById("viewMemberModal"));
          modal.show();
        }
      };

      function percorsiDettagli(id_piazzola) {
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Mostrerà i dettagli dei PERCORSI per la piazzola ' + id_piazzola);
      }

      function dismettiElementi(button, id_piazzola, tipo_elemento, id_utenzapap, ids_elementi) {
       
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Dismetterà temporaneamente tutti gli elementi ('+ids_elementi+') di tipo '+tipo_elemento+' nella piazzola ' + id_piazzola + 'senza chiedere alcun intervento' );
        const id = `${id_piazzola}_${tipo_elemento}`;
        
        $('#dis_elementi_intervento_btn').prop('disabled', true);

        $.ajax({
          url: 'backoffice/dismiss_elemento.php',
          method: 'POST',
          data: { id_piazzola: id,
            id_utenzapap: id_utenzapap,
            ids_elementi: ids_elementi
          },
          success: function (response) {
            //console.log(response);
            showToast(response, 'success', 'modal');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            showToast('Errore durante la dismissione temporanea dell\'elemento.', 'error', 'modal');
            console.error(errorThrown);
          }
        });
      }

      function ripristinaElementi(button, id_piazzola, tipo_elemento, id_utenzapap, ids_elementi) {
       
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Ripristinerà tutti gli elementi ('+ids_elementi+') di tipo '+tipo_elemento+' nella piazzola ' + id_piazzola + 'senza chiedere alcun intervento' );
        const id = `${id_piazzola}_${tipo_elemento}`;
        
        $('#riprist_elementi_intervento_btn').prop('disabled', true);

        $.ajax({
          url: 'backoffice/ripristina_elemento.php',
          method: 'POST',
          data: { id_piazzola: id,
            id_utenzapap: id_utenzapap,
            ids_elementi: ids_elementi
          },
          success: function (response) {
            //console.log(response);
            showToast(response, 'success', 'modal');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            showToast('Errore durante la dismissione temporanea dell\'elemento.', 'error', 'modal');
            console.error(errorThrown);
          }
        });
      }

      /* FINE BOTTONI PERCORSI */

      /* CONSUNTIVAZIONE */
      function mostraBottoniConsFormatter(value, row, index) {
          return `
            <button class="btn btn-sm btn-primary btn-consuntivazione" title="Consuntivazione" style="cursor:pointer"><i class="fa-solid fa-clock"></i></button>
          `;
      }

      window.actionConsEvents = {
        'click .btn-consuntivazione': function (e, value, row, index) {
          consuntivazioneDettagli(<?php echo $id_piazzola; ?>);
        }
      };

      function consuntivazioneDettagli(id_piazzola) {
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Mostrerà i dettagli della CONSUNTIVAZIONE per la piazzola ' + id_piazzola);
      }
      
      /* FINE BOTTONI CONSUNTIVAZIONE */

      
      // alla chiusura del modal dei dettagli faccio refresh della tabella
      $('#viewMemberModal').on('hidden.bs.modal', function () {
        $('#elementi').bootstrapTable('refresh', {
            silent: true
        });
      });

      // questa funzione serve a mantenere lo stile delle righe sospese anche dopo il refresh della tabella
      // il refresh infatti aggiorna i dati ma non mantiene lo stile delle righe perchè non ri-applica la funzione rowStyle
      function applicaStiliRighe() {

        const data = $('#elementi').bootstrapTable('getData');

        $('#elementi tbody tr').each(function () {

            const index = $(this).data('index');
            const row = data[index];

            if (!row) return;

            //qualora venissero creati altri stili va creata la classe css in req.php
            //e aggiunta al removeClass qui sotto, in modo da rimuovere eventuali stili precedenti
            // e poi va duplicato il blocco if per aggiungere la nuova classe in base al valore della colonna corrispondente
            $(this).removeClass('riga-sospesa');
            $(this).removeClass('riga-eliminata');
            $(this).removeClass('riga-aggiunta');

            if (row.elem_sospesi === 't') {
                $(this).addClass('riga-sospesa');
            }

            if (row.id_tipo_intervento === '3'){
              $(this).addClass('riga-eliminata');
            }

            if (row.id_negativi === '1'){
              $(this).addClass('riga-aggiunta');
            }
        });
      }

      $('#elementi').on('post-body.bs.table', function () {
          applicaStiliRighe();
      });


    </script>

    <script>
      // funzioni bottoni dettagli piazzola
            
      // submit del form editing dettagli piazzola
      $(document).ready(function () {                 
          $('#dettagli_piazzola').submit(function (event) { 
              event.preventDefault();

              var formData = $(this).serialize();
              console.log(formData);
              $.ajax({ 
                  url: 'backoffice/edit_dettagli_piazzola.php', 
                  method: 'POST', 
                  data: formData,
                  dataType: 'json',
                  success: function (response) {                       
                      console.log(response);
                      showToast(response.messaggio, 'success');
                  }, 
                  error: function (jqXHR, textStatus, errorThrown) {                        
                      console.error(errorThrown);
                      console.error(jqXHR.responseText);

                      showToast(jqXHR.responseText, 'error');
                  } 
              });
          }); 
      }); 

      // dismissione piazzola
      function confermaDismettiPiazzola(id_piazzola, manutenzione) {
        console.log('cliccato bottone dismetti piazzola')
        $("#modalDettaglioPiazzola").append(`
          <div id="overlayDismiss" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
          style="background: rgba(0,0,0,.2); z-index: 2000;">
            <div class="bg-white rounded shadow p-3 text-center">
              <h5>Verrà sospesa temporaneamente la piazzola <b>${id_piazzola}</b></h5>
              <br>
              <button type="button" class="btn btn-primary" id="dis_piazzola_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o aggiornamento SIT" onclick="dismettiPiazzola(this, <?php echo $id_piazzola; ?>)"><i class="fa-solid fa-display"></i> Nessun intervento</button>
                <button type="button" class="btn btn-primary" id="dis_piazzola_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>, 'all', '', '')" ${manutenzione === 't' ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
              <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
              <hr>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" id="btnChiudiDis" onclick="chiudiDismiss()"> Chiudi </button>
              </div>
            </div>
          </div>`
        );
      }

      function confermaRipristinaPiazzola(id_piazzola, manutenzione) {
        console.log('cliccato bottone ripristina piazzola')
        $("#modalDettaglioPiazzola").append(`
          <div id="overlayRipristina" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
          style="background: rgba(0,0,0,.2); z-index: 2000;">
            <div class="bg-white rounded shadow p-3 text-center">
              <h5>Verrà ripristinata la piazzola <b>${id_piazzola}</b></h5>
              <br>
              <button type="button" class="btn btn-primary" id="rip_piazzola_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o aggiornamento SIT" onclick="ripristinaPiazzola(this, <?php echo $id_piazzola; ?>)"><i class="fa-solid fa-display"></i> Nessun intervento</button>
                <button type="button" class="btn btn-primary" id="rip_piazzola_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>, 'all', '', '')" ${manutenzione === 't' ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
              <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
              <hr>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" id="btnChiudiRip" onclick="chiudiRiprist()"> Chiudi </button>
              </div>
            </div>
          </div>`
        );
      }

      function chiudiDismiss() {
        $("#overlayDismiss").remove();
      }

      function dismettiPiazzola(button, id_piazzola) {
      
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Dismetterà temporaneamente la piazzola ' + id_piazzola + ' e tutti i suoielementi senza chiedere alcun intervento' );
        const id = `${id_piazzola}_all`;
        
        $('#dis_elementi_intervento_btn').prop('disabled', true);

        $.ajax({
          url: 'backoffice/dismiss_elemento.php',
          method: 'POST',
          data: { id_piazzola: id
          },
          success: function (response) {
            //console.log(response);
            showToast(response, 'success');
            $("#overlayDismiss").remove();
          },
          error: function (jqXHR, textStatus, errorThrown) {
            showToast('Errore durante la dismissione temporanea della piazzola.', 'error');
            console.error(errorThrown);
          }
        });
      }

      function chiudiRiprist() {
        $("#overlayRipristina").remove();
      }

      function ripristinaPiazzola(button, id_piazzola) {
      
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Ripristinerà la piazzola ' + id_piazzola + ' e tutti i suoielementi senza chiedere alcun intervento' );
        const id = `${id_piazzola}_all`;
        
        $('#rip_piazzola_intervento_btn').prop('disabled', true);

        $.ajax({
          url: 'backoffice/ripristina_elemento.php',
          method: 'POST',
          data: { id_piazzola: id
          },
          success: function (response) {
            //console.log(response);
            showToast(response, 'success');
            $("#overlayRipristina").remove();
          },
          error: function (jqXHR, textStatus, errorThrown) {
            showToast('Errore durante la dismissione temporanea della piazzola.', 'error');
            console.error(errorThrown);
          }
        });
      }

      // eliminazione piazzola
      function confermaEliminaPiazzola(id_piazzola, manutenzione) {
        console.log('cliccato bottone elimina piazzola '+ manutenzione)
        $("#modalDettaglioPiazzola").append(`
          <div id="overlayElimin" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
          style="background: rgba(0,0,0,.2); z-index: 2000;">
            <div class="bg-white rounded shadow p-3 text-center">
              <h5>Verrà eliminata la piazzola <b>${id_piazzola}</b></h5>
              <br>
              <button type="button" class="btn btn-primary" id="del_piazzola_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o aggiornamento SIT" onclick="eliminaPiazzola(this, <?php echo $id_piazzola; ?>)"><i class="fa-solid fa-display"></i> Nessun intervento</button>
              <button type="button" class="btn btn-primary" id="del_piazzola_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>, 'all', '', '')" ${manutenzione === 't' ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
              <div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
              <hr>
              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" id="btnChiudiElim" onclick="chiudiElimina()"> Chiudi </button>
              </div>
            </div>
          </div>`
        );
      }

      function chiudiElimina() {
        $("#overlayElimin").remove();
      }

      function eliminaPiazzola(button, id_piazzola) {
      
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Eliminerà la piazzola ' + id_piazzola + ' e tutti i suoielementi senza chiedere alcun intervento' );
        const id = `${id_piazzola}_all`;
        
        $('#del_piazzola_intervento_btn').prop('disabled', true);

        $.ajax({
          url: 'backoffice/delete_elemento.php',
          method: 'POST',
          data: { id_piazzola: id},
          success: function (response) {
            //console.log(response);
            showToast(response, 'success');
            $("#overlayElimin").remove();
          },
          error: function (jqXHR, textStatus, errorThrown) {
            showToast('Errore durante l\'eliminazione della piazzola.', 'error');
            console.error(errorThrown);
          }
        });
      }

      function allineaCliente(id_piazzola, id_via, civico, id_uu, id_ut, check_piazzola, tipo_elemento) {
        let tcliente;
        let desc;
        if (check_piazzola === 1) {
          console.log('clicco da piazzola')
          tcliente = $('#tcliente').val();
          desc = $('#desc').val();
        }else{
          console.log('clicco da elementi')
          tcliente = $('#tclienteE').val();
          desc = $('#descE').val();
        }
        

        console.log('Allineamento cliente per piazzola', id_piazzola, 'con tipo cliente', tcliente, 'e nominativo', desc, 'in via', id_via, 'civico', civico);

        if (!tcliente || !desc) {
          showToast('Seleziona un tipo cliente e inserisci un nominativo.', 'error', 'modal');
          return;
        }

        $.ajax({
          url: 'backoffice/allinea_cliente.php',
          method: 'POST',
          data: { id_piazzola: id_piazzola, 
          tcliente: tcliente, 
          desc: desc,
          id_via: id_via,
          civico: civico,
          id_uu: id_uu,
          id_ut: id_ut,
          check_piazzola: check_piazzola,
          tipo_elemento: tipo_elemento},
          success: function (response) {
            if (check_piazzola === 1){
              showToast(response, 'success');
            }else{
              showToast(response, 'success', 'modal');
            }
            // Aggiorna la tabella degli elementi
            $('#elementi').bootstrapTable('refresh');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            if (check_piazzola === '1'){
              showToast('Errore durante l\'allineamento del cliente.', 'error');
            }else{
              showToast('Errore durante l\'aggiunta del cliente.', 'error', 'modal');
            }

            console.error(errorThrown);
          }
        });
      }

      function inviaManutenzione(button, id_piazzola, tipo_elemento, id_utenzapap, ids_elementi) {
        //window.location.href = 'dettagli_elemento.php?id=' + id;
        console.log('Invierà richiesta di manutenzione per l\'elemento o la piazzola ' + id_piazzola);
        //showToast(`Invierà richiesta di manutenzione per l\'elemento della piazzola ${id_piazzola} di tipo ${tipo_elemento}`, 'warning', 'modal');
        const id = `${id_piazzola}_${tipo_elemento}`;

        buttonId = button.id; 
        console.log('Bottone ' + buttonId );
        if (buttonId === 'add_elemento_tipo_intervento_btn') {
            tipo_intervento = 'add';
            check_piazzola = 0;
            $('#add_elemento_tipo_btn').prop('disabled', true);
        }else if (buttonId === 'del_elementi_intervento_btn') {
            tipo_intervento = 'del';
            check_piazzola = 0;
            $('#del_elementi_btn').prop('disabled', true);
        }else if (buttonId === 'dis_elementi_intervento_btn') {
            tipo_intervento = 'dis';
            check_piazzola = 0;
            $('#dis_elementi_btn').prop('disabled', true);
        }else if (buttonId === 'dis_piazzola_intervento_btn') {
            tipo_intervento = 'dis';
            check_piazzola = 1;
            $('#dis_piazzola_btn').prop('disabled', true);
        }else if (buttonId === 'del_piazzola_intervento_btn') {
            tipo_intervento = 'del';
            check_piazzola = 1;
            $('#del_piazzola_btn').prop('disabled', true);
        }else if (buttonId === 'rip_piazzola_intervento_btn') {
            tipo_intervento = 'rip';
            check_piazzola = 1;
            $('#rip_piazzola_btn').prop('disabled', true);
        }else if (buttonId === 'riprist_elementi_intervento_btn') {
            tipo_intervento = 'rip';
            check_piazzola = 0;
            $('#riprist_elementi_btn').prop('disabled', true);
        }
        console.log(tipo_intervento)
        $.ajax({
          url: 'backoffice/go_movimentazione.php',
          method: 'POST',
          data: { id_piazzola: id,
            id_utenzapap: id_utenzapap,
            tipo_intervento: tipo_intervento,
            check_piazzola: check_piazzola,
            ids_elementi: ids_elementi

          },
          success: function (response) {
            //console.log(response);
            if (check_piazzola === 1) {
              showToast(response, 'success');
              if (tipo_intervento === 'del') {
                $ ("#overlayElimin").remove();
              }else if (tipo_intervento === 'dis'){
                $("#overlayDismiss").remove();
              }
            }else{
              showToast(response, 'success', 'modal');
            }
          },
          error: function (jqXHR, textStatus, errorThrown) {
            if (check_piazzola === 1) {
              showToast('Errore durante l\'invio della richiesta a manutenzione cassonetti.', 'error');
              if (tipo_intervento === 'del') {
                $ ("#overlayElimin").remove();
              }else if (tipo_intervento === 'dis'){
                $("#overlayDismiss").remove();
              }
            }else{
              showToast('Errore durante l\'invio della richiesta a manutenzione cassonetti.', 'error', 'modal');
            }
            console.error(errorThrown);
          }
        });
      }
    </script>


    <!-- INIZIO FUNZIONI MAPPA -->
    <script>
      geojsonPiazzola = <?php echo json_encode($geojson); ?>;
      id_asta = <?php echo json_encode($id_asta_piazzola); ?>;
      id_piazzola = <?php echo json_encode($id_piazzola); ?>;
    </script>
    <script  src="./layers/mappa_dettagli_piazzola.js"></script>
    <!-- FINE FUNZIONI MAPPA -->

    <script>
      function apriFotoPiazzola(img) {

          const overlay = document.createElement('div');

          overlay.id = 'overlayFotoPiazzola';

          overlay.className =
              'position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center';

          overlay.style.cssText = `
              background: rgba(0,0,0,.7);
              z-index: 2000;
              padding: 20px;
          `;

          overlay.innerHTML = `
            <div class="bg-white rounded shadow p-3" style="max-width: 95%; max-height: 95%; position: relative;">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-2" aria-label="Chiudi" onclick="chiudiFotoPiazzola()"></button>
                <img src="${img.src}" class="img-fluid rounded" style="max-height: 85vh; object-fit: contain; margin-top: 2%;" alt="${img.alt}">
            </div>
          `;

          // Lo aggiungiamo al modal già aperto
          img.closest('.modal').appendChild(overlay);
      }


      function chiudiFotoPiazzola() {
          const overlay = document.getElementById('overlayFotoPiazzola');

          if (overlay) {
              overlay.remove();
          }
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