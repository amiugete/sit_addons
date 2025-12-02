<?php
//session_set_cookie_params($lifetime);
session_start();

if ($_SESSION['test']==1) {
    //echo "CONNESSIONE TEST<br>";
    $checkTest=1;
    require_once ('../conn_test.php');
} else {
    //echo "CONNESSIONE ESERCIZIO<br>";
    $checkTest=0;
    require_once ('../conn.php');
}

$sel_ut = $_GET['ut'];

#$sel_ut = 104;

$query_comuni="SELECT cu.id_comune, cu.id_ut, c.descr_comune
from topo.comuni_ut cu
join topo.comuni c on c.id_comune = cu.id_comune 
WHERE id_ut=$1 
ORDER BY c.descr_comune;";
$resultC = pg_prepare($conn_sit, "queryC", $query_comuni);
$resultC = pg_execute($conn_sit, "queryC", array($sel_ut));  
$count_result = pg_num_rows($resultC);
#echo $count_result; 

while($rC = pg_fetch_assoc($resultC)) { 
  ?>
    <div class="comuni-row">
      <label for="inlineCheckbox1"><?php echo $rC['descr_comune']?></label>
      <?php
        if($count_result == 1) {
          // Se c'è un solo comune, lo seleziono automaticamente e metto la checkbox disabled
          //devo però passare il valore dell'id_comune con un input hidden perchè il disabled non passa il valor in post
          echo '<input type="hidden" name="comuni[]" value="' . $rC['id_comune'] . '">';
        }
      ?>
      <input class="comuni-check" type="checkbox" style="border-color:darkgrey;" name="comuni[]" id="comune_<?php echo $rC['id_comune']?>" value="<?php echo $rC['id_comune']?>" <?php if ($count_result == 1) echo "checked disabled"; ?>>
      <input type="number" class="percent-input" name="percentuali[<?= $rC['id_comune'] ?>]" placeholder="%" min="0" max="100" <?php
        if ($count_result == 1) {
          // Se c'è un solo comune, imposto il valore 100 e metto l'input readonly
          //altrimenti lo metto disabled in modo che venga attivato solo se viene checcata la checbox corrispondente
            echo 'value="100" readonly';
        } else {
            echo 'disabled';
        }
      ?>>
    </div>
  <?php } ?>