<?php 

if($_GET['limit']){
    $page_size= intval($_GET['limit']);
} else {    
    $page_size=count($rows);
}

if(intval($_GET['offset'])>=0){
    $page_n= intval($_GET['offset']);
} else {   
    $page_n=0;
}

if (count($rows)>0) {
    $columnsNames = array_keys($rows[0]);

    //echo json_encode($columnsNames);
    //exit();
    //echo '{ "meta": {"page_index":'.$page_n.', "page_max_size":'.$page_size.', ';
    echo '{"total":'.count($rows).', ';
    
    echo '"rows": '.json_encode(array_values(array_splice($rows, $page_n, $page_size))) ; //JSON_FORCE_OBJECT rimuove le quadre
    echo '}';
    echo '';
} else {
    echo '{"total":'.count($rows).', "columns":[]},';
    echo '"rows": []';
    echo '}';
    echo '';
}
?>