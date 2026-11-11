<?php
require_once '../session.php';
#require('../validate_input.php');

require_once '../conn_ok.php';


header('Content-Type: application/json; charset=utf-8');





// Leggo il JSON ricevuto
$input = json_decode(file_get_contents('php://input'), true);


// Controllo che sia un JSON valido
if ($input === null && json_last_error() !== JSON_ERROR_NONE) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'messaggio' => 'JSON non valido'
    ]);

    exit;
}


// Recupero i dati
$azione = $input['azione'] ?? null;
$id_percorso = $input['id_percorso'] ?? null;
$id_elementi = $input['id_elementi'] ?? [];


// Controlli minimi
if (
    !in_array($azione, ['aggiungi', 'rimuovi'], true) ||
    $id_percorso === null ||
    !is_array($id_elementi)
) {

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'messaggio' => 'Parametri mancanti o non validi'
    ]);

    exit;
}


// Da qui in poi:
// $azione
// $id_percorso
// $id_elementi
//
// sono disponibili per la logica del backend.



// NON USIAMO $res_ok o roba del genere.. 
// facciamo un try. In caso di errore throw new Exception(pg_last_error($NOME_CONNESSIONE));
// che poi è un die fatto bene 
// in fondo questo pezzo di codice per gestire le eccezioni

/*catch (Exception $e) {
    pg_query($conn_sit, "ROLLBACK");
    echo "Errore: " . $e->getMessage();
    http_response_code(400);
} */ 



try {
    /* FACIO TUTTO IN UNA TRANSACTION*/
    pg_query($conn_sit, "BEGIN");


    foreach ($id_elementi as $id_elemento) {
        $id_elemento = intval($id_elemento);
        $messaggio= "$azione elem $id_elemento al percorso: $id_percorso <br>";
        
        //echo $messaggio;
        //exit();

        if ($azione == 'aggiungi'){
            
            
            // controllo se l'asta è già presente al percorso altrimenti la aggiungo e mi faccio ritornare id_asta_percorso


            $query_select_ap = "select ap.id_asta_percorso  
            from elem.aste_percorso ap where id_percorso = $1
            and id_asta in (select coalesce(p.id_asta, e.id_asta)
            from elem.elementi e 
            left join elem.piazzole p on p.id_piazzola = e.id_piazzola
            where id_elemento = $2);";
        
            $result_ap1 = pg_prepare($conn_sit, "query_select_ap", $query_select_ap);
            if (pg_last_error($conn_sit)) {
                throw new Exception(pg_last_error($conn_sit));
            }

            $result_ap1 = pg_execute($conn_sit, "query_select_ap", array($id_percorso, $id_elemento));
            if (pg_last_error($conn_sit)) {
                throw new Exception(pg_last_error($conn_sit));
            }



            // controllo presenza asta percorso
            $row = pg_fetch_assoc($result_ap1);
            if ($row['id_asta_percorso']) {
                // asta c'è
                $id_ap= $row['id_asta_percorso'];
            } else {
                $query_insert_ap="INSERT INTO elem.aste_percorso (
                num_seq,  
                id_asta, 
                tipo, 
                carico_scarico,
                id_percorso, 
                metri_trasf, /* da capire cosa faccia */
                tempo_trasf, /* metri:6.944 */ 
                senso_perc, 
                lung_trattamento,
                data_inserimento
                ) 
                VALUES
                (
                (select coalesce(max(num_seq),0)+1 from elem.aste_percorso where id_percorso = $1),
                (select coalesce(p.id_asta, e.id_asta)
                from elem.elementi e 
                left join elem.piazzole p on p.id_piazzola = e.id_piazzola
                where id_elemento = $2),
                'servizio',
                0,
                $1,
                0,/* Metri di trasferimento.. francamente non so come sia calcolato ma mi sembra una cavolata */
                0,/* metri/6.944 */
                -1, /* da capire se mettendo nullo incasino ciò che c'è ora */
                0, /* da capire se mettendo nullo incasino ciò che c'è ora */
                now()
                ) RETURNING id_asta_percorso;";


                $result_ap2 = pg_prepare($conn_sit, "query_insert_ap", $query_insert_ap);
                if (pg_last_error($conn_sit)) {
                    echo pg_last_error($conn_sit);
                    $res_ok = $res_ok + 1;
                }

                $result_ap2 = pg_execute($conn_sit, "query_insert_ap", array($id_percorso, $id_elemento));
                
                if (pg_last_error($conn_sit)) {
                    throw new Exception(pg_last_error($conn_sit));
                } else {
                    // leggo il returning
                    $row = pg_fetch_assoc($result_ap2);
                    $id_ap = $row['id_asta_percorso'];
                }
            


            }
            //echo $id_ap;
            //exit();

            // aggiungo elemento con frequenza del percorso 
            $insert_elemento="INSERT INTO elem.elementi_aste_percorso (
                id_asta_percorso, 
                id_elemento, 
                frequenza, 
                ripasso, 
                data_inserimento) 
                VALUES(
                    $1, 
                    $2, 
                    (select frequenza::text  from  elem.percorsi where id_percorso = $3), 
                    (select coalesce(count(id_elemento), 0) 
                        from elem.elementi_aste_percorso 
                        where id_elemento = $2 
                        and id_asta_percorso in 
                        (select id_asta_percorso from elem.aste_percorso where id_percorso = $3)
                    ), 
                    now()
                );";


            $result_eap = pg_prepare($conn_sit, "insert_elemento", $insert_elemento);
            if (pg_last_error($conn_sit)) {
                throw new Exception(pg_last_error($conn_sit));
            }

            $result_eap = pg_execute($conn_sit, "insert_elemento", array($id_ap, $id_elemento, $id_percorso));
            
            if (pg_last_error($conn_sit)) {
                throw new Exception(pg_last_error($conn_sit));
            } 

            


            
        } else if ($azione == 'rimuovi'){
            
        
            $id_elemento_ap=$id_elemento; 
            
            $messaggio = 'Rimosso elem_ap '.$id_elemento_ap.' da percorso '.$id_percorso;
        
            // rimuovo elemento_asta_percorso returning asta_percorso e id_elemento
            $query_delete = "
                DELETE FROM elem.elementi_aste_percorso
                WHERE id_elemento_asta_percorso = $1
                RETURNING id_elemento, id_asta_percorso;
            ";

            $result_delete = pg_prepare($conn_sit, "query_delete",  $query_delete);

            if (!$result_delete) {
                throw new Exception(pg_last_error($conn_sit));
            }
            
            
            $result_delete = pg_execute($conn_sit, "query_delete", array($id_elemento_asta_percorso) );

            if (!$result_delete) {
                throw new Exception(pg_last_error($conn_sit));
            }

            if (pg_num_rows($result_delete) == 0) {
                throw new Exception("Nessuna riga cancellata");
            }
            $row = pg_fetch_assoc($result_delete);

            // attenzione id_elemento qua lo sovrascrivo per la history
            $id_elemento      = $row['id_elemento'];
            $id_asta_percorso = $row['id_asta_percorso'];
            

            // controllo se asta percorso ancora usata in elementi asta percorso
            $esiste_sql = "SELECT EXISTS (
                SELECT 1
                FROM elem.elementi_aste_percorso
                WHERE id_asta_percorso = $1
            );";

            $result_check = pg_prepare($conn_sit, "esiste_sql",  $esiste_sql);

            if (!$result_check) {
                throw new Exception(pg_last_error($conn_sit));
            }
            
            
            $result_check = pg_execute($conn_sit, "esiste_sql", array($id_asta_percorso) );

            if (!$result_check) {
                throw new Exception(pg_last_error($conn_sit));
            }


            $row = pg_fetch_row($result_check);
            
            
            // se utilizzata non faccio nulla, altrimenti rimuovo anche l'asta
            if ($row[0] === 'f') {
                // rimuovo elemento_asta_percorso returning asta_percorso e id_elemento
                $query_delete2 = "
                    DELETE FROM elem.aste_percorso
                    WHERE id_asta_percorso = $1;
                ";

                $result_delete2 = pg_prepare($conn_sit, "query_delete2",  $query_delete2);

                if (!$result_delete2) {
                    throw new Exception(pg_last_error($conn_sit));
                }
                
                
                $result_delete2 = pg_execute($conn_sit, "query_delete2", array($id_asta_percorso) );

                if (!$result_delete2) {
                    throw new Exception(pg_last_error($conn_sit));
                }

                if (pg_num_rows($result_delete2) == 0) {
                    throw new Exception("Nessuna asta percorso cancellata");
                }


            }



        }

        // La history è unica per aggiunta / rimozione

        $sql_history= "INSERT INTO util.sys_history (\"type\", \"action\", 
                description, datetime, 
                id_user,
                id_piazzola, id_percorso, id_elemento, id_utenzapap) 
                VALUES('PERCORSO', 'UPDATE_ELEM', $1, now(),
                (select id_user from util.sys_users su where \"name\" ilike $2), 
                null, $3, $4, null);";
    
        $result_h = pg_prepare($conn_sit, "sql_history", $sql_history);
        if (pg_last_error($conn_sit)){
            throw new Exception(pg_last_error($conn_sit));
        }


        $result_h = pg_execute($conn_sit, "sql_history", array($messaggio, 
                                                    $_SESSION['username'],
                                                    $id_percorso,
                                                    $id_elemento));
        if (pg_last_error($conn_sit)) {
            throw new Exception(pg_last_error($conn_sit));
        }


    } //fine ciclo


    pg_query($conn_sit, "COMMIT");
    http_response_code(200);
    //echo '<font color="green">Elemento aggiunto correttamente. </font>';

    echo json_encode([
        'ok' => true,
        'messaggio' => $messaggio
    ]);
}   
            
catch (Exception $e) {
    pg_query($conn_sit, "ROLLBACK");
    echo "Errore: " . $e->getMessage();
    http_response_code(400);
}  


?>