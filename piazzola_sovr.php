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
<?php $anno =  date("Y")+1 ?>
<h4>Anno corrente : <?php echo $anno;?> </h4>
<div class="form-group col-lg-6">
  <!--label for="via">Piazzola:</label> <font color="red">*</font-->
				
				
  <select class="selectpicker show-tick form-control" 
  data-live-search="true" name="piazzola" id="piazzola" placeholder="Seleziona una piazzola" onchange="piazzolaScelta(this.value);" required="">

  <!--option name="piazzola" value="NO">Seleziona una piazzola</option-->
  <?php 
  
  require_once("./tables/query_piazzole_sovr.php");


  $query2="SELECT 
  case 
    when id_piazzola > 0 then id_piazzola::text
    else concat('C', id_elemento::text)
  end as id_piazzola,
    rif, comune 
    FROM (".$query_ps. ") ip where anno = ". intval($anno) .";";
  
  echo $query2;    

  $result2 = pg_query($conn_sovr, $query2);
  while($r2 = pg_fetch_assoc($result2)) { 

?>
              
          <option name="piazzola" value="<?php echo $r2['id_piazzola'];?>" ><?php echo $r2['id_piazzola'] .' - ' .$r2['rif'] .' ('.$r2['comune'].')';?></option>
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

if (is_numeric($id_piazzola)){
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
   select e.id_elemento, e.matricola, e.tag,  sum(fo.num_giorni)::int as num_svuotamenti_settimanali, 
string_agg(distinct concat(vi.tipo, ' - ', vi.descrizione), ',') as desc_intervento,
string_agg(distinct vi.stato_descrizione, ',') as stato_intervento, 
max(vi.stato) as id_stato_intervento,
max(vi.odl) as odl
from elem.elementi e 
left join gestione_oggetti.v_intervento vi on e.id_elemento = vi.elemento_id and vi.stato in (1,5)
left join elem.elementi_aste_percorso eap on e.id_elemento = eap.id_elemento 
left join elem.aste_percorso ap on ap.id_asta_percorso = eap.id_asta_percorso 
left join elem.percorsi p on p.id_percorso = ap.id_percorso 
left join etl.frequenze_ok fo on fo.cod_frequenza = eap.frequenza::int 
where coalesce(p.id_categoria_uso, 3) in (3)
and id_piazzola = $1
and e.tipo_elemento = $2
group by e.id_elemento, e.matricola, e.tag ";
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
string_agg(distinct concat(vi.tipo, ' - ', vi.descrizione), ',') as desc_intervento,
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
    if ($r['stato_intervento']!=''){
      echo '<i class="fa-solid fa-wrench"></i> ';
      }
      echo '</b>';
  
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
    <div class="col-12">
    <?php
      if ($re['stato_intervento']!=''){
      echo '<i class="fa-solid fa-wrench"></i>  '.$re["desc_intervento"].' ';
        $check_stato_intervento=1;
        if ($r["id_stato_intervento"]==5){
          echo '(Intervento preso in carico con OdL '.$r["odl"].')';
        } else {
          echo '(Intervento aperto)';
        }
      }
      ?>
      </div>
    </div>
    <div class="row row-cols-lg-auto g-3 align-items-center">
           
      
      <?php
        require('tasti_verifica_sovr.php');
      ?>

    



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
      
    <?php
    require('matr_tag_sovr.php');
    ?>
  
    </div>
    <?php
    }
    ?>
    
    <?php 
    } else {
      echo '<br> no_cestino=1 (casistica non gestita.. chiedere a Longo cosa fare)';
    }
    //echo '<small>Tipo raccolta: '.$r['tipo_raccolta'].') </small><hr>';
    //echo '<hr>';
    echo "</li><hr>";
    }  #fine while
echo "</ul>";


?>
   
   <?php
}

?>

  <!--/div-->


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






    



<div class="accordion" id="accordionFlushExample">
<div class="accordion-item">
<h2 class="accordion-header">
  <button class="accordion-button collapsed btn-info" type="button" data-bs-toggle="collapse" 
  data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
  <i class="fa-solid fa-plus"></i> Aggiunta contenitori altro tipo
  </button>
</h2>
<div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
  <div class="accordion-body">
  <!--form id="add_elemento_form"--> 
  <div id="add_elemento">
  <!--input type="hidden" id="tipo_elemento" name="tipo_elemento" value="<?php echo $r['tipo_elemento'];?>"-->
  <input type="hidden" id="id_piazzola_ae" name="id_piazzola_ae" value="<?php echo $id_piazzola;?>">
  
  
  <select class="selectpicker show-tick form-control" 
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
  <button type="submit" id="add_elemento_submit" class="btn btn-success btn-sm">
  <i class="fa-solid fa-plus"></i>
  </button> 
  <!--/form-->

  </div>



  <div id=result_add>  </div>

<!-- lancio il form e scrivo il risultato -->
<script> 
        $(document).ready(function () {                 
            $('#add_elemento_submit').click(function (event) { 
                console.log('Bottone add elemento  generico cliccato e finito qua');
                event.preventDefault();                  
                id_piazzola=document.getElementById("id_piazzola_ae").value+'_'+document.getElementById("tipo_elemento_ae").value;
                console.log(id_piazzola);
                var formData = $(this).serialize();
                //var formData = $('#add_elemento_<?php echo $r['tipo_elemento'];?> input').not( "#ispezione input" ).serialize();
                console.log(formData);
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
                }); 
            });
          }); 

    </script>
  
      </div>
    </div>
  </div>


</div>
</div>

</div>

<!--hr-->





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
// cestino getta carta
} else if (str_replace('C', '', $id_piazzola)) {

  $id_elemento=str_replace('C', '', $id_piazzola);
  // cestino
  


  $query_elemento="select 
  -1 as id_piazzola, 
  e.id_elemento, 
  te.tipo_rifiuto,
tr.nome as rifiuto,
te.tipologia_elemento,
tr.colore,
te2.descrizione as tipo_raccolta,
te.tipo_elemento,
te.descrizione as tipo_elem, 
concat (ep.descrizione, ' - ', ep.nome_attivita) as cliente, 
v.nome as via, 
e.numero_civico, 
e.colore_civico,
e.lettera_civico,
e.riferimento,
c.descr_comune as comune
from elem.elementi e
join elem.aste a on a.id_asta = e.id_asta
join topo.vie v on v.id_via = a.id_via 
join topo.comuni c on c.id_comune = v.id_comune 
join elem.tipi_elemento te on te.tipo_elemento = e.tipo_elemento 
join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto
join elem.tipologie_elemento te2 on te2.tipologia_elemento = te.tipologia_elemento
left join elem.elementi_privati ep on ep.id_elemento = e.id_elemento 
where e.id_elemento = $1 ";

  $result_e = pg_prepare($conn_sovr, "my_query_e", $query_elemento);
  $result_e = pg_execute($conn_sovr, "my_query_e", array($id_elemento));
  $status1= pg_result_status($result_e);

  while($re = pg_fetch_assoc($result_e)) {
    echo '<h4>Dati verifica sovrariempimenti '.$re['tipo_elem'].' '.$id_elemento.' sito in via '.$re['via'].' (' .$re['comune'].')</h4>';
    
?>

  <form class="row g-3" id="ispezione2">

<!--div class="row g-3" id="ispezione"-->
<input type="hidden" id="id_piazzola" name="id_piazzola" value="<?php echo $re['id_piazzola'];?>">



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

<div class="row row-cols-lg-auto g-3 align-items-center">
<?php 
// tasti verifica
require('tasti_verifica_sovr.php');


// frequenza svuotamento

$query_svuotamenti='select e.id_elemento, p.cod_percorso, p.descrizione, t.cod_turno,
fo.descrizione_long, p.id_categoria_uso, sum(fo.num_giorni)::int as num_svuotamenti_settimanali
from elem.elementi e 
join elem.aste a on e.id_asta = a.id_asta
left join elem.aste_percorso ap on ap.id_asta = a.id_asta
left join elem.percorsi p on p.id_percorso = ap.id_percorso 
left join etl.frequenze_ok fo on fo.cod_frequenza = ap.frequenza::int 
left join elem.turni t on t.id_turno = p.id_turno 
where p.id_categoria_uso = 3 and ap.lung_trattamento > 0
and id_elemento = $1
group by e.id_elemento, p.cod_percorso, p.id_categoria_uso, t.cod_turno, fo.descrizione_long, p.descrizione ';


$query_svuot= 'select id_elemento, sum(num_svuotamenti_settimanali) as num_svuotamenti_settimanali 
from ('. $query_svuotamenti.') as s group by id_elemento';

//echo $query_svuot;

$result_p = pg_prepare($conn_sovr, "query_svuot", $query_svuot);
$result_p = pg_execute($conn_sovr, "query_svuot", array($id_elemento));
$statusp= pg_result_status($result_p);
while($rp = pg_fetch_assoc($result_p)) {
?>


<div class="col-12">


  
        <div class="form-check">

        
        <button type="button" class="btn btn-info dropdown-toggle btn-sm" 
        data-bs-target="#freq_<?php echo $re['id_elemento'];?>" 
        aria-controls="freq_<?php echo $re['id_elemento'];?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Freq <?php echo $rp['num_svuotamenti_settimanali'];?>
        <span class="navbar-toggler-icon"></span> <!--svg class="icon-expand icon icon-sm icon-light"><use href="/bootstrap-italia/dist/svg/sprites.svg#it-expand"></use></svg-->
        </button>

        <div id="freq_<?php echo $re['id_elemento'];?>" class="dropdown-menu" >
          <div class="link-list-wrapper">
            <ul class="link-list col-12">
              <?php
              $result_pp = pg_prepare($conn_sovr, "query_svuotamenti", $query_svuotamenti);
              $result_pp = pg_execute($conn_sovr, "query_svuotamenti", array($id_elemento));
              $status= pg_result_status($result_pp);
              ?>
              
              <?php
              while($rpp = pg_fetch_assoc($result_pp)) {
              ?>

              <li><?php echo $rpp['cod_percorso'] .' - '.$rpp['descrizione']. ' - ' .$rpp['descrizione_long']. ' - ' .$rpp['cod_turno']. '<span> </span> ' ;?> </li>
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
}
// matricola / tag

require('matr_tag_sovr.php');
  
    
}?>
</div>
<div class="col-12">
  <button type="submit" class="btn btn-info">
  <i class="fa-solid fa-arrow-up-from-bracket"></i> Salva
  </button>
  </div> 
</form>

<!-- lancio il form e scrivo il risultato -->
<p><div id="results_verifica2"></div></p>

            <script> 
            $(document).ready(function () {                 
                $('#ispezione2').submit(function (event) { 
                    console.log('Bottone form verifica 2 cliccato e finito qua');
                    event.preventDefault();                  
                    //var formData = $(this).serialize();
                    var formData = $('#ispezione2 input').not( "#add_elemento input" ).serialize();
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
                              $("#results_verifica2").html(response.split('$$')[1]).fadeIn("slow");
                            } else {
                              $("#results_verifica2").html(response).fadeIn("slow");
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



<?php
} // fine if id_elemento




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