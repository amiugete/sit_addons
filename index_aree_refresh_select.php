<?php
require_once('./conn.php');
echo "<option value=''>Scegli un'area</option>";
    $query="SELECT id,
                CASE 
                    WHEN ecopunto = true THEN 'ecopunto_' || nome
                    ELSE nome
                END AS nome,
                to_char(data_disegno, 'dd/mm/yyyy - HH24:MI:SS') as data_disegno2
                from etl.aree_4326 where (data_disegno::date>=(NOW() - INTERVAL '30' DAY) and ecopunto is not true) or 
                ecopunto = true order by data_disegno desc;";
    $result = pg_query($conn, $query);

     while($r2 = pg_fetch_assoc($result)) {
        echo "<option value='{$r2['id']}'>{$r2['nome']} ({$r2['data_disegno2']})</option>";
    }
?>
