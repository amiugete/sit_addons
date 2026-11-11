<?php
function getClientiPiazzola($conn_sit, $id_piazzola){
    $query_clienti = "SELECT id_macro_categoria, 
    descrizione,
    count(id_elemento) as n_elem
    from elem.elementi_privati 
    where id_elemento in (
        select id_elemento from elem.elementi where id_piazzola = $1
    )
    group by
    id_macro_categoria, 
    descrizione";

$result_clienti = pg_prepare($conn_sit, "select_clienti", $query_clienti);
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$result_clienti = pg_execute($conn_sit, "select_clienti", array($id_piazzola));
if (!pg_last_error($conn_sit)){
    #$res_ok=0;
} else {
    pg_last_error($conn_sit);
    $res_ok= $res_ok+1;
}

$clienti = [];

while($row = pg_fetch_assoc($result_clienti)) {

    $clienti[] = $row;
}

return $clienti;
};


?>