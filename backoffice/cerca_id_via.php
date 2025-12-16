<?php
session_start();
#require('../validate_input.php');


if ($_SESSION['test']){
    if ($_SESSION['test']==1) {
        require_once ('../conn_test.php');
    } else {
        require_once ('../conn.php');
    }
} else {
    echo 'Sessione scaduta. Si prega di ricaricare la pagina per proseguire';
    exit();
}



$res_ok=0;








$nome = '%'.trim($_POST['nome_ilike']).'%';

$id_comune = intval($_POST['id_comune']) ?? 0;



$select_sit="select id_via, nome,
	v.via_ordinata, 
	v.id_comune, 
	data_ultima_modifica, 
	c.descr_comune, c.prefisso_utenti
	from topo.vie v 
	left join topo.comuni c on c.id_comune = v.id_comune 
	where v.nome ilike $1
    ";

if ($id_comune> 0){
    $select_sit=$select_sit . ' and v.id_comune = $2 order by 6';
} else {
    $select_sit=$select_sit . ' order by 6'; 
}


$result_sit0 = pg_prepare($conn_sit, "select_sit", $select_sit);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

if ($id_comune> 0){
    $result_sit0 = pg_execute($conn_sit, "select_sit", array($nome, $id_comune)); 
} else {
    $result_sit0 = pg_execute($conn_sit, "select_sit", array($nome)); 
}

if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}



while($rs = pg_fetch_assoc($result_sit0)) {
    echo $rs['id_comune'] .' - '. $rs['descr_comune']. ' - <b>'. $rs['id_via'] . '</b> - ' .$rs['nome'].'<br>'; 
}

?>