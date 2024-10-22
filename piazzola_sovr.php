<?php
//session_set_cookie_params($lifetime);
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Verifica sovrariempimento piazzola</title>
<?php 
require_once('./req.php');

the_page_title();

if ($_SESSION['test']==1) {
  require_once ('./conn_test.php');
} else {
  require_once ('./conn.php');
}
?> 


<style>
#successo { display: none; }
</style>


</head>

<body>

<?php require_once('./navbar_up.php');
$name=dirname(__FILE__);
//echo $id_role_SIT;
//exit;
if ((int)$id_role_SIT == 0) {
  redirect('no_permessi.php');
  //exit;
}

?>


<div class="container">

<script>
  function piazzolaScelta(val) {
    document.getElementById('openpiazzola').submit();
    
   
  }
</script>

<script type="text/javascript">
        
function clickButton2() {
      console.log("Bottone update piazzola cliccato");


     
      var id_piazzola=document.getElementById('id_piazzola').value;
      console.log(id_piazzola);

      var civ=document.getElementById('civ').value;
      localStorage.setItem("civ", civ);
      console.log(civ);
      
      var lciv=document.getElementById('lciv').value;
      localStorage.setItem("lciv", lciv);
      console.log(lciv);

      var cciv=document.getElementById('cciv').value;
      localStorage.setItem("cciv", cciv);
      console.log(cciv);

      var rif=document.getElementById('rif').value;
      localStorage.setItem("rif", rif);
      console.log(rif);
      
      var note=document.getElementById('note').value;
      localStorage.setItem("note", note);
      console.log(note);

      


      if ($('input[name="privato"').is(':checked')){
        var privato=1;
      } else {
        var privato=0;
      }
      console.log(privato);
  
      console.log('LocalStorage');
      console.log(localStorage);

      var http = new XMLHttpRequest();
      var url = 'update_piazzola.php';
      //var params = 'id_piazzola='+encodeURIComponent(id_piazzola)+'&civ='+encodeURIComponent(civ)+'&rif='+encodeURIComponent(rif)+'&note='+encodeURIComponent(note)+'&privato='+encodeURIComponent(privato)+'';
      var params = new FormData();
      params.append('id_piazzola', id_piazzola);
      params.append('civ', civ);
      params.append('lciv', lciv);
      params.append('cciv', cciv);
      params.append('rif', rif);
      params.append('note', note);
      params.append('privato', privato);
      http.open('POST', url, false);  // con il false la richiesta dovrebbe essere sincrona

      //Send the proper header information along with the request
      /*http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

      http.onreadystatechange = function() {//Call a function when the state changes.
          if(http.readyState == 4 && http.status == 200) {
              console.log(http.responseText);
          }
      }*/
      
      //http.onreadystatechange = checkData;
      http.onload = function () {
        // do something to response
        console.log(this.responseText);
      };

    
    
      http.send(params);
    
      //console.log(http.readyState);

      if (http.readyState === XMLHttpRequest.DONE) {
        console.log('Sono qua');      
        window.location.reload(true);
        //return false;
      } else {
        console.log(http.readyState);
        $("#dettagli_piazzola").hide();
        console.log('Nascosto DIV');
        $( "#dettagli_piazzola" ).load(window.location.href + " #dettagli_piazzola");
        console.log('Ricaricato DIV');
        $("#dettagli_piazzola").show();
        console.log('show DIV');
        //return false;
      }
      



      return false;
      
      

  }




</script>


<hr>
<form name="openpiazzola" method="post" id="openpiazzola" autocomplete="off" action="piazzola_sovr.php" >
<div class="row">

<div class="form-group col-lg-6">
  <!--label for="via">Piazzola:</label> <font color="red">*</font-->
				
				
  <select class="selectpicker show-tick form-control" 
  data-live-search="true" name="piazzola" id="piazzola" placeholder="Seleziona una piazzola" onchange="piazzolaScelta(this.value);" required="">

  <!--option name="piazzola" value="NO">Seleziona una piazzola</option-->
  <?php            
  $query2="SELECT vpd.id_piazzola, concat(via, ',',civ, ' - ',riferimento)  as rif
  FROM elem.v_piazzole_dwh vpd
  join sovrariempimenti.programmazione_ispezioni pi on pi.id_piazzola = vpd.id_piazzola;";
  $result2 = pg_query($conn_sovr, $query2);
  //echo $query1;    
  while($r2 = pg_fetch_assoc($result2)) { 
      $valore=  $r2['id_via']. ";".$r2['nome'];            
  ?>
              
          <option name="piazzola" value="<?php echo $r2['id_piazzola'];?>" ><?php echo $r2['id_piazzola'] .' - ' .$r2['rif'];?></option>
  <?php } ?>

  </select>  
  <!--small>L'elenco delle piazzole..  </small-->        
</div>






<div  name="conferma2" id="conferma2" class="form-group col-lg-3 ">
<!--input type="submit" name="submit" id=submit class="btn btn-info" value="Recupera dettagli piazzola"-->
<!--button type="submit" class="btn btn-info">
Recupera dettagli piazzola
</button-->
</div>



</div> <!-- fine row-->
</form>

<br>
<hr>


<?php
$id_piazzola=$_POST['piazzola'];
if (!$id_piazzola){
  $id_piazzola=$_GET['piazzola'];
}
$check_stato_intervento=0;

if ($id_piazzola){
?> 
<h4> Dettagli piazzola <?php echo $id_piazzola?> da SIT </h4>
<div id="refreshDataContainer">
<div id="dettagli_piazzola" class="row">
<?php
$query_piazzola="SELECT v.nome as via, p.numero_civico, p.foto, p.riferimento, p.note,
p.suolo_privato,
st_y(st_transform(p2.geoloc,4326)) as lat, 
st_x(st_transform(p2.geoloc,4326)) as lon,  p.modificata_da , p.data_ultima_modifica,
 p.lettera_civico, p.colore_civico
from elem.piazzole p 
join elem.aste a on a.id_asta = p.id_asta 
join topo.vie v on v.id_via = a.id_via 
join geo.piazzola  p2 on p2.id = p.id_piazzola 
where p.id_piazzola = $1";

$result_p = pg_prepare($conn_sovr, "my_query_p", $query_piazzola);
$result_p = pg_execute($conn_sovr, "my_query_p", array($id_piazzola));
$statusp= pg_result_status($result_p);

$check_foto=0;

// metto qua le varie query  ripetute (per elemento) per migliorare le performance 


$select_elementi="
    select e.id_elemento, matricola, tag,  sum(fo.num_giorni)::int as num_svuotamenti_settimanali
from elem.elementi e 
left join elem.elementi_aste_percorso eap on e.id_elemento = eap.id_elemento 
left join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
left join elem.percorsi p on p.id_percorso = ap.id_percorso 
left join etl.frequenze_ok fo on fo.cod_frequenza = eap.frequenza::int 
where coalesce(p.id_categoria_uso, 3) in (3)
and id_piazzola = $1
and tipo_elemento = $2
group by e.id_elemento, matricola, tag ";
$result_ee = pg_prepare($conn_sovr, "my_query_ee", $select_elementi);




$query_percorsi="select e.id_elemento, p.id_percorso, 
              p.cod_percorso, p.descrizione, fo.descrizione_long,
              t.cod_turno
              from elem.elementi e 
              left join elem.elementi_aste_percorso eap on e.id_elemento = eap.id_elemento 
              left join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
              left join elem.percorsi p on p.id_percorso = ap.id_percorso 
              left join etl.frequenze_ok fo on fo.cod_frequenza = eap.frequenza::int 
              left join elem.turni t on t.id_turno = p.id_turno 
              where p.id_categoria_uso in (3) 
              and e.id_elemento = $1
              order by t.inizio_ora ";

$result_pp = pg_prepare($conn_sovr, "my_query_pp", $query_percorsi);


?>


<form autocomplete="off" id="edit_piazzola" action="" onsubmit="return clickButton2();">
<input type="hidden" id="id_piazzola" name="id_piazzola" value=<?php echo $id_piazzola?>>
<div class="row g-3 align-items-center">
<?php
while($r_p = pg_fetch_assoc($result_p)) {
  $check_foto=$r_p['foto'];
  ?>
  <div class="form-group col-md-4">
      <label for="via"> Via </label>
      <input disabled="" type="text" name="via" id="via" class="form-control" value="<?php echo $r_p['via'];?>">
    </div>
    <div class="form-group col-md-1">
      <label for="via"> Num civ </label>
      <input type="number" name="civ" id="civ" class="form-control" value="<?php echo $r_p['numero_civico'];?>">
    </div>
    <div class="form-group col-md-1">
      <label for="via"> Lettera </label>
      <input type="text" maxlength="1" name="lciv" id="lciv" class="form-control" value="<?php echo $r_p['lettera_civico'];?>">
    </div>
    <div class="form-group col-md-1">
      <label for="via"> Colore </label>
      <input type="text" maxlength="1" name="cciv" id="cciv" class="form-control" value="<?php echo $r_p['colore_civico'];?>">
    </div>
    <div class="form-group  col-md-4">
      <label for="rif"> Riferimento </label> <font color="red">*</font>
      <input type="text" name="rif" id="rif" class="form-control" value="<?php echo $r_p['riferimento'];?>" required="">
    </div>

    <div class="form-group  col-md-6">
      <label for="note"> Note </label>
      <input type="text" name="note" id="note" value="<?php echo $r_p['note'];?>" class="form-control" >
    </div>

    <div class="form-group col-md-2">
      <input class="form-check-input" type="checkbox" value="privato" name="privato" id="privato"
      <?php
      if ($r_p['suolo_privato']==1){
        echo ' checked=';
      }
      ?>
      >
      <label class="form-check-label" for="privato">
        Suolo privato
      </label>
    </div>   
    <div class="form-group  col-md-2">
      <button type="submit" class="btn btn-info btn-sm"
    <?php if ($check_edit==0){echo 'disabled=""';}?>
    >
      <i class="fa-solid fa-pen-to-square"></i>Aggiorna piazzola
      </button>
    </div>
    <div class="form-group  col-md-2">
      <a id="sit_btn1" class="btn btn-info pc btn-sm" href="<?php echo $url_sit?>/#!/home/edit-piazzola/<?php echo $id_piazzola?>/" target="_new">
    <i class="fa-solid fa-arrow-up-right-from-square"></i> Visualizza su SIT
    </a>
    </div>
    <div class="form-group  col-md-12">
    <label for="note"> Modificato da <?php echo $r_p['modificata_da'];?> il <?php echo $r_p['data_ultima_modifica'];?> </label>
    </div>
<?php
}
?>
</form>
<hr>

<!--form autocomplete="off" id="messaggio" action="invio_mail_assterritorio.php" method="post">
<input type="hidden" id="id_piazzola" name="id_piazzola" value=<?php echo $id_piazzola?>>
<div class="row g-3 align-items-center">
<div class="form-group  col-md-10">
  <label for="testo_mail" class="form-label">Messaggio mail per assterritorio</label>
  <textarea class="form-control" id="testo_mail" name="testo_mail" rows="3"></textarea>
</div>
<div class="form-group  col-md-2">
      <button type="submit" class="btn btn-info">
      <i class="fa-solid fa-at"></i>Invia mail
      </button>
</div>
</div>
</form>
<hr-->
</div>
</div>

<div class="row"> 


<?php 
$query_precedenti_ispezioni=" select count(*) as num_isp,
 to_char(current_date, 'YYYY') as anno
 from sovrariempimenti.ispezioni i 
 where id_piazzola = $1 and 
 to_char(data_ora, 'YYYY') = to_char(current_date, 'YYYY')";

$result_p_e = pg_prepare($conn_sovr, "query_precedenti_ispezioni", $query_precedenti_ispezioni);
$result_p_e = pg_execute($conn_sovr, "query_precedenti_ispezioni", array($id_piazzola));

while($r_p_e = pg_fetch_assoc($result_p_e)) {
  $num_isp=intval($r_p_e['num_isp']);
  $anno=intval($r_p_e['anno']);
  
}

if ($num_isp>0){
  ?>
  <div class="col-12">


  <div class="form-check">

  
  <button type="button" class="btn btn-warning dropdown-toggle btn-sm" 
  data-bs-target="#ispezioni" 
  aria-controls="ispezioni" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
  <i class="fa-solid fa-list-check"></i> 
  <?php echo $num_isp;?> precedenti verifiche nel  <?php echo $anno;?>
  <span class="navbar-toggler-icon"></span> <!--svg class="icon-expand icon icon-sm icon-light"><use href="/bootstrap-italia/dist/svg/sprites.svg#it-expand"></use></svg-->
  </button>

  <div id="ispezioni" class="dropdown-menu" >
    <div class="link-list-wrapper">
      <ul class="link-list col-12">
        <?php
        
        $query_precedenti_ispezioni2="   SELECT id, ispettore, 
 to_char(data_ora , 'DD/MM/YYYY') AS data_ora 
          FROM sovrariempimenti.ispezioni i 
          WHERE id_piazzola = $1 AND 
          to_char(data_ora, 'YYYY') = to_char(current_date, 'YYYY') ";

          $result_p_e2 = pg_prepare($conn_sovr, "query_precedenti_ispezioni2", $query_precedenti_ispezioni2);
          $result_p_e2 = pg_execute($conn_sovr, "query_precedenti_ispezioni2", array($id_piazzola));

          while($r_p_e2 = pg_fetch_assoc($result_p_e2)) {
        ?>

        <li><?php echo 'Verifica n '.$r_p_e2['id'] .' del '.$r_p_e2['data_ora']. ' (<i class="fa-solid fa-user-secret"></i> ' .$r_p_e2['ispettore']. ') ' ;?> </li>
        <hr>

        <?php 
        }
        ?>
      </ul>
    </div>
  </div>
</div>

</div>

<?php 
} else {
  echo "Non ci sono precedenti verifiche di quest'anno";
}

?>
</div>
<hr>
<div class="row">

<div class="col-md-8"> 

<?php 
// controllo se la piazzola esiste o se è stata eliminata

$query_eliminata= "select 
case 
  when data_eliminazione is null then 0
  else 1
end eliminata, data_eliminazione
from elem.piazzole p where id_piazzola = $1";
$result_el = pg_prepare($conn_sovr, "my_query_el", $query_eliminata);
$result_el = pg_execute($conn_sovr, "my_query_el", array($id_piazzola));

while($r_el = pg_fetch_assoc($result_el)) {
  $check_eliminata=$r_el['eliminata'];
  $data = $r_el['data_eliminazione'];
}
if ($check_eliminata==1)
{
  ?>
  <div id="comp_piazz">
  <h3><i class="fa-solid fa-trash"></i> Piazzola eliminata il <?php echo $data; ?></h3>
  </div>
<?php
} else {
?>














<div id="comp_piazz">
<h4> Dati verifica sovrariempimenti piazzola <?php echo $id_piazzola;?></h4>
<?php 

$query_elementi= "select 
count(e.id_elemento) as num, 
te.tipo_rifiuto,
tr.ordinamento,
tr.nome as rifiuto,
te.tipologia_elemento,
tr.colore,
te2.descrizione as tipo_raccolta,
te.tipo_elemento,
te.descrizione as tipo_elem, 
concat (ep.descrizione, ' - ', ep.nome_attivita) as cliente, 
string_agg(distinct vi.stato_descrizione, ',') as stato_intervento, 
max(vi.stato) as id_stato_intervento,
max(vi.odl) as odl,
case 
  when te.tipologia_elemento in ('T', 'A') 
  then 1
  else 0
end no_cestino
from elem.elementi e
join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento 
join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto
join elem.tipologie_elemento te2 on te2.tipologia_elemento = te.tipologia_elemento
left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento 
left join gestione_oggetti.v_intervento vi on e.id_elemento = vi.elemento_id and vi.stato in (1,5)
where id_piazzola = $1 and  te.tipo_elemento not in (101 /* punto di lavaggio*/, 180 /*riordino piazzola*/) 
group by 
/*e.id_elemento, */
tr.ordinamento,
te.tipo_rifiuto,
tr.nome,
te.tipologia_elemento,
tr.colore,
te2.descrizione ,
te.tipo_elemento,
te.descrizione , 
ep.descrizione, ep.nome_attivita
order by 3, tr.nome, te.descrizione";

$result_e = pg_prepare($conn_sovr, "my_query_e", $query_elementi);
$result_e = pg_execute($conn_sovr, "my_query_e", array($id_piazzola));
$status1= pg_result_status($result_e);
//echo $status1."<br>";
// recupero i dati dal DB di SIT

?>




<form class="row g-3" id="ispezione">

<!--div class="row g-3" id="ispezione"-->
<input type="hidden" id="id_piazzola" name="id_piazzola" value="<?php echo $id_piazzola;?>">



<?php 
# imposto l'orario del server
$today = new DateTime('now');
$timezone = new DateTimeZone('Europe/Rome');
$today->setTimezone($timezone);
?>

<div class="form-group col-md-2">
    <label for="id" class="form-label">Id</label>
    <input type="number" min=1 class="form-control" id="id" name="id" readonly>
</div>


<div class="form-group col-md-3">
    <label for="data_isp" class="form-label">Data verifica</label>
    <input type="text" class="form-control" id="js-date1" name="data_isp" value="<?php echo $today->format('d/m/Y');?>" required>
</div>

<script type="text/javascript">

</script>

<div class="form-group col-md-3">
  <label class="form-label active" for="ora">Ora verifica </label>
  <input class="form-control" id="ora" name="ora" type="time" value="<?php echo $today->format('H:i');?>" required>
</div>

<div class="form-group col-md-4">
    <label for="ispettore" class="form-label">Verificatore</label>
    <input type="text" class="form-control" name="ispettore" id="ispettore" value="<?php echo $_SESSION['username'];?>" required>
</div>
<hr>
<?php 
echo "<ul>";
while($r = pg_fetch_assoc($result_e)) {

    
    echo '<li style="list-style-type: none">';
    
    if ($r['no_cestino']==1){
      echo '<b><font style="color: '.$r['colore'].';">
      <i class="fa-solid fa-ban"  title="Manca elemento fisico"></i>';
    } else {
      echo '<b><font style="color: '.$r['colore'].';">
      <i class="fa-solid fa-check"></i>';
    }
    echo $r['rifiuto'] .'</b> - ';
    echo $r['num']. ' x ';
    echo $r['tipo_elem']. ' ('.$r['tipo_raccolta'].')';
    if (trim($r['cliente']) !='-'){
      echo ' - '. $r['cliente'];
    }
    /*if ($r['stato_intervento']!=''){
      echo '<b style="color:red"> Intervento '.$r["stato_intervento"].' ';
      if ($r["id_stato_intervento"]==5){
        $check_stato_intervento=1;
        echo '(Ordine di lavoro = '.$r["odl"].')';
      }
      echo '</b>';
    }*/
    echo '</font>';

    ?>






    <!--form id="add_elemento"-->
    <div id="add_elemento_<?php echo $r['tipo_elemento'];?>">
      <!--input type="hidden" id="tipo_elemento" name="tipo_elemento" value="<?php echo $r['tipo_elemento'];?>"-->
      <input type="hidden" id="id_piazzola_<?php echo $r['tipo_elemento'];?>" name="id_piazzola_<?php echo $r['tipo_elemento'];?>" value="<?php echo $id_piazzola.'_'.$r['tipo_elemento'];?>">
      <button type="submit" class="btn btn-success btn-sm">
      <i class="fa-solid fa-plus"></i>
      </button> 
      <!--/form-->
  </div>

<div id=result_add_<?php echo $r['tipo_elemento'];?>></div>


<!-- lancio il form e scrivo il risultato -->
<script> 
            $(document).ready(function () {                 
                $('#add_elemento_<?php echo $r['tipo_elemento'];?>').click(function (event) { 
                    console.log('Bottone add elemento cliccato e finito qua');
                    event.preventDefault();                  
                    id_piazzola=document.getElementById("id_piazzola_<?php echo $r['tipo_elemento'];?>").value;
                    console.log(id_piazzola);
                    var formData = $(this).serialize();
                    //var formData = $('#add_elemento_<?php echo $r['tipo_elemento'];?> input').not( "#ispezione input" ).serialize();
                    //console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/add_elemento.php', 
                        method: 'POST', 
                        data: {'id_piazzola':id_piazzola}, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                              $("#result_add_<?php echo $r['tipo_elemento'];?>").html(response).fadeIn("slow");
                              setTimeout(function(){// wait for 5 secs(2)
                                location.reload(); // then reload the page.(3)
                            }, 1000);
                                      
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    }); 
                });
              }); 

        </script>
            


<!--hr-->






    <?php 
    if ($r['no_cestino']==0){
    
    $result_ee = pg_execute($conn_sovr, "my_query_ee", array($id_piazzola, $r['tipo_elemento']));
    $status1= pg_result_status($result_ee);
   
    while($re = pg_fetch_assoc($result_ee)) {
      
    ?>
    <div class="row row-cols-lg-auto g-3 align-items-center">
      <!--div class="col-12">
        <div class="input-group">
          <div class="input-group-text">id</div>
          <input type="text" readonly="" class="form-control" name="<?php echo $re['id_elemento'];?>" value="<?php echo $re['id_elemento'];?>" required>
        </div>
      </div-->


      <!--div class="col-12">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="<?php echo $re['id_elemento'];?>" checked>
        <label class="form-check-label" for="inlineFormCheck">
          Verificato
        </label>
      </div>
      </div-->


      

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

    



      <div class="col-12">
        <!--div title="Svuotamenti a settimana" class="input-group-text">Freq <?php echo $re['num_svuotamenti_settimanali'];?>
        </div-->

  
        <div class="form-check">

        
        <button type="button" class="btn btn-info dropdown-toggle btn-sm" 
        data-bs-target="#freq_<?php echo $re['id_elemento'];?>" 
        aria-controls="freq_<?php echo $re['id_elemento'];?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Freq <?php echo $re['num_svuotamenti_settimanali'];?>
        <span class="navbar-toggler-icon"></span> <!--svg class="icon-expand icon icon-sm icon-light"><use href="/bootstrap-italia/dist/svg/sprites.svg#it-expand"></use></svg-->
        </button>

        <div id="freq_<?php echo $re['id_elemento'];?>" class="dropdown-menu" >
          <div class="link-list-wrapper">
            <ul class="link-list col-12">
              <?php
              
              $result_pp = pg_execute($conn_sovr, "my_query_pp", array($re['id_elemento']));
              $statusp= pg_result_status($result_pp);
              ?>
              
              <?php
              while($rp = pg_fetch_assoc($result_pp)) {
              ?>

              <li><?php echo $rp['cod_percorso'] .' - '.$rp['descrizione']. ' - ' .$rp['descrizione_long']. ' - ' .$rp['cod_turno']. '<span> </span> ' ;?> </li>
              <hr>

              <?php 
              }
              ?>
            </ul>
          </div>
        </div>
      </div>

      </div>
      
      <div class="col-12">
        <div title="Dettagli elemento" class="btn-sm">Matr: <?php echo $re['matricola'] ;?> - Tag: <?php echo $re['tag'] ;?>
        </div>
      </div>


      <button type="button" class="info btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit_elemento" data-bs-whatever="<?php echo $re['id_elemento'];?>">
      <i class="fa-solid fa-pencil"></i>
      </button>
     
      <!--form id="delete_<?php echo $id_elemento;?>">
      <input type="hidden" id="id_elemento" name="id_elemento" value="<?php echo $id_elemento;?>">
      <button type="submit" class="btn btn-danger btn-sm">
        <i class="fa-solid fa-trash"></i>
      </button>
      </form-->
      
      
      

    </div>



    <div class="modal fade" id="edit_elemento" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-scrollable modal-xl" >
      <div class="modal-content">
        <div class="modal-header">
          <!--h5 class="modal-title" id="exampleModalLabel">Titolo</h5-->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      <div class="modal-body" id="body_dettaglio">

      

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div> 
    <?php
    }
    ?>
    
    <?php 
    } else {
      echo '<br>Chiedere a Longo cosa fare..';
    }
    //echo '<small>Tipo raccolta: '.$r['tipo_raccolta'].') </small><hr>';
    //echo '<hr>';
    echo "</li><hr>";
    }  #fine while
echo "</ul>";

?>
    <!--form id="add_elemento"-->
  




    <div class="col-12">
    <button type="submit" class="btn btn-info">
    <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
    </button>
    </div> 
  </form>
  <!--/div-->




<!-- lancio il form e scrivo il risultato -->
<p><div id="results_verifica"></div></p>

            <script> 
            $(document).ready(function () {                 
                $('#ispezione').submit(function (event) { 
                    console.log('Bottone form dd cliccato e finito qua');
                    event.preventDefault();                  
                    //var formData = $(this).serialize();
                    var formData = $('#ispezione input').not( "#add_elemento input" ).serialize();
                    console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/result_piazzola_sovr.php', 
                        method: 'POST', 
                        data: formData, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                            console.log(response.split('$$').length);
                            if (response.split('$$').length > 1) {
                              document.getElementById("id").value = response.split('$$')[0];
                              $("#results_verifica").html(response.split('$$')[1]).fadeIn("slow");
                            } else {
                              $("#results_verifica").html(response).fadeIn("slow");
                            }
                            
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    }); 
                }); 
            }); 
        </script>


<!--hr-->
</div>
<?php
}

?>


<hr>
    <div class="row g-3" id="add_elemento">
      <!--input type="hidden" id="tipo_elemento" name="tipo_elemento" value="<?php echo $r['tipo_elemento'];?>"-->
      <input type="hidden" id="id_piazzola_ae" name="id_piazzola_ae" value="<?php echo $id_piazzola;?>">
      
      
      <select class="col-10 selectpicker show-tick form-control" 
  data-live-search="true" name="tipo_elemento_ae" id="tipo_elemento_ae" placeholder="Seleziona tipo elemento da aggiungere" required="">

        <!--option name="piazzola" value="NO">Seleziona una piazzola</option-->
        <?php            
        $query_tipo="SELECT tipo_elemento, 
          concat(tr.nome_stampa, ' - ',te.nome_stampa, ' (', te.volume,' l)'/*, ' - ', te.nome*/) as nome_ok/*,
          tr.nome, 
          te.descrizione, te.nome, te.nome_stampa, 
          tr.ordinamento, te.tipologia_elemento*/ 
          FROM elem.tipi_elemento te 
          join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto 
          where te.tipo_rifiuto is not null 
          and in_piazzola = 1 and tipologia_elemento not in ('T', 'I', 'N')
          order by tr.ordinamento, te.volume";
        $result_t = pg_query($conn_sovr, $query_tipo);
        //echo $query1;    
        while($rt = pg_fetch_assoc($result_t)) {             
        ?>
                    
                <option name="tipo_elemento" value="<?php echo $rt['tipo_elemento'];?>" ><?php echo $rt['nome_ok'] .' - ' .$r2['rif'];?></option>
        <?php } ?>

      </select>
      <button type="submit" class="col-2 btn btn-success btn-sm">
      <i class="fa-solid fa-plus"></i>
      </button> 
      <!--/form-->
  </div>

<div id=result_add></div>
<hr>

<!-- lancio il form e scrivo il risultato -->
<script> 
            $(document).ready(function () {                 
                $('#add_elemento').click(function (event) { 
                    console.log('Bottone add elemento  generico cliccato e finito qua');
                    /*event.preventDefault();                  
                    id_piazzola=document.getElementById("id_piazzola_ae").value;
                    console.log(id_piazzola);
                    var formData = $(this).serialize();
                    //var formData = $('#add_elemento_<?php echo $r['tipo_elemento'];?> input').not( "#ispezione input" ).serialize();
                    //console.log(formData);
                    $.ajax({ 
                        url: 'backoffice/add_elemento.php', 
                        method: 'POST', 
                        data: {'id_piazzola':id_piazzola}, 
                        //processData: true, 
                        //contentType: false, 
                        success: function (response) {                       
                            //alert('Your form has been sent successfully.'); 
                            console.log(response);
                              $("#result_add").html(response).fadeIn("slow");
                              setTimeout(function(){// wait for 5 secs(2)
                                location.reload(); // then reload the page.(3)
                            }, 1000);
                                      
                        }, 
                        error: function (jqXHR, textStatus, errorThrown) {                        
                            alert('Your form was not sent successfully.'); 
                            console.error(errorThrown); 
                        } 
                    });*/ 
                });
              }); 

        </script>


</div>



<div class="col-md-4"> 
<?php if ($check_foto == 1) {
//$timemod=filemtime('/foto_SIT/sit/'.$id_piazzola.'.jpg');
//echo 'FIle modificato il '.$timemod;
?>

<img src="../foto/sit/<?php echo $id_piazzola?>.jpg?hash=<?php echo filemtime('/foto_SIT/sit/'.$id_piazzola.'.jpg')?>" class="rounded img-fluid" alt="Immagine piazzola <?php echo $id_piazzola?> non presente">
<hr>
<?php }
?>
<?php if ($check_edit==1){?>
<form  action="upload_foto.php" method="post" enctype="multipart/form-data">
<!--form  action="" onsubmit="return clickButton2();" method="post" enctype="multipart/form-data"-->
<!--form id="form_foto" method="post" enctype="multipart/form-data"-->
<input type="hidden" id="piazzola" name="piazzola" value="<?php echo $id_piazzola?>">
<div class="mb-3">
  <label for="formFile" class="form-label">
  <?php if ($check_foto == 1) {
    echo 'Modifica immagine';
  } else {
    echo 'Aggiungi immagine:';
  }
  ?>
  </label>
  <input type="file" class="form-control form-control-sm" name="fileToUpload" id="fileToUpload" required="">
  </div>
  <div class="mb-3">
  <input type="submit" value="Carica foto" name="submit" class="btn btn-primary mb-3" >
  </div>
</form>
<?php }?>
</div>

</div>






</div>

<?php
} #piazzola
require_once('req_bottom.php');
require('./footer.php');
?>



<script type="text/javascript">

$(function () {
	$('select').selectpicker();
});






$('#js-date1').datepicker({
      format: 'dd/mm/yyyy',
      todayBtn: "linked", // in conflitto con startDate
      language:'it' 
  });


if (!edit_elemento){
  var edit_elemento = document.getElementById('edit_elemento')
}
  edit_elemento.addEventListener('show.bs.modal', function (event) {
        // Button that triggered the modal
        var button = event.relatedTarget
        // Extract info from data-bs-* attributes
        var recipient = button.getAttribute('data-bs-whatever')
        console.log('recipient'+recipient);
        $.ajax({   
            type: "get",
            url: "edit_elem_sovr.php",
            data: {'id': recipient},
            dataType: "text",                  
            success: function(response){                    
                $("#body_dettaglio").html(response);
                
            }, 
              
            
        });
        
})


</script>



</body>

</html>