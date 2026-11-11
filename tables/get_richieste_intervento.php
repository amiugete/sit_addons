<?php

require_once '../session.php';

require_once '../conn_ok.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {

	/*$query= "select i.id, 
	ti.id, ti.descrizione, 
	i.data_creazione, i.elemento_id, 
	i.rifiuto, i.tipo_elemento, 
	i.elemento_privato_id, i.priorita, 
	itsi.descrizione as stato, e.tipo_elemento
	from gestione_oggetti.v_intervento i
	left join elem.elementi e on e.id_piazzola = i.piazzola_id and i.tipo_elemento = (select te.nome from elem.tipi_elemento te where te.tipo_elemento  = $1)
	left join gestione_oggetti.tipo_stato_intervento itsi on i.stato = itsi.id
	left join gestione_oggetti.tipo_intervento ti on i.tipo_intervento_id  = ti.id 
	where i.piazzola_id = $2 and i.stato in (1, 5) and e.tipo_elemento  = $3
	group by i.id, i.piazzola_id, ti.id, ti.descrizione, i.data_creazione, i.elemento_id, i.rifiuto,  
	i.tipo_elemento, i.elemento_privato_id, i.priorita, itsi.descrizione, e.tipo_elemento
	order by i.data_creazione desc";*/

	if (isset($_POST['id_piazzola']) and isset($_POST['id_tipo'])){
		$id_piazola = $_POST['id_piazzola'];
		$id_tipo_elemento = $_POST['id_tipo'];

		/*$query="select count(distinct i.id) as num_intereventi, 
		case when i.descrizione ilike 'Rip%' or i.descrizione ilike 'Dis%' then i.descrizione
		else ti.descrizione
		end descrizione, 
		itsi.descrizione as descr_stato, i.priorita
		from gestione_oggetti.v_intervento i
		left join elem.elementi e on e.id_piazzola = i.piazzola_id and i.tipo_elemento = (select te.nome from elem.tipi_elemento te where te.tipo_elemento  = $1)
		left join gestione_oggetti.tipo_stato_intervento itsi on i.stato = itsi.id
		left join gestione_oggetti.tipo_intervento ti on i.tipo_intervento_id  = ti.id 
		where i.piazzola_id = $2 and i.stato in (1, 5) and e.tipo_elemento  = $3
		group by ti.id, i.descrizione, itsi.descrizione, i.stato, e.tipo_elemento, i.priorita";*/

		$query = "SELECT count(distinct i.id) as num_intereventi,
		case when i.descrizione ILIKE 'Rip%' or i.descrizione ILIKE 'Dis%' then i.descrizione
		else ti.descrizione
		end as descrizione,
		itsi.descrizione as descr_stato,
		i.priorita
		from gestione_oggetti.v_intervento i
		left join gestione_oggetti.tipo_stato_intervento itsi on i.stato = itsi.id
		left join gestione_oggetti.tipo_intervento ti on i.tipo_intervento_id = ti.id
		where i.piazzola_id = $1 and i.stato in (1,5) and i.tipo_elemento = (select nome from elem.tipi_elemento where tipo_elemento = $2)
		group by ti.id, i.descrizione, itsi.descrizione, i.stato, i.priorita";

		$params = array($id_piazola, $id_tipo_elemento);
	}else{
		$id_elemento = $_POST['id_elemento'];

		$query="SELECT count(distinct i.id) as num_intereventi, 
		case when i.descrizione ilike 'Rip%' or i.descrizione ilike 'Dis%' then i.descrizione
		else ti.descrizione
		end descrizione, 
		itsi.descrizione as descr_stato, i.priorita
		from gestione_oggetti.v_intervento i
		left join elem.elementi e on e.id_elemento = i.elemento_id
		left join gestione_oggetti.tipo_stato_intervento itsi on i.stato = itsi.id
		left join gestione_oggetti.tipo_intervento ti on i.tipo_intervento_id  = ti.id 
		where e.id_elemento  = $1 and (i.tipo_intervento_id <> 1 or ( i.tipo_intervento_id = 1 and i.descrizione ilike 'Rip%'))
		and i.stato in (1, 5)
		group by ti.id, i.descrizione, itsi.descrizione, i.stato, e.tipo_elemento, i.priorita";

		$params = array($id_elemento);
	}

	
		
	

	$html = '<div class="text-end">
		<button type="button"
				class="btn btn-sm btn-close btn-close-popover">
		</button>
	</div>';
	$html .= '<table class="table table-sm">';
	$html .= '<table class="table table-sm table-bordered mb-0">
			<thead>
				<tr>
					<th>N.Int</th>
					<th>Descrizione</th>
					<th>Stato</th>
					<th>Priorità</th>
				</tr>
			</thead>
			<tbody>';

	$result = pg_prepare($conn_sit, "my_query", $query);
	$result = pg_execute($conn_sit, "my_query", $params);
	$rows = array();
	while($r = pg_fetch_assoc($result)) {
		$rows[] = $r;
		$html .= '<tr>
				<td>'.$r['num_intereventi'].'</td>
				<td>'.$r['descrizione'].'</td>
				<td>'.$r['descr_stato'].'</td>
				<td>'.$r['priorita'].'</td>
			</tr>';
	};
		

	if (empty($rows)==TRUE){
		//print $rows;
		$html .= '<tr>
				<td colspan="3">Nessuna richiesta</td>
            </tr>';
	} 

	$html .= '</tbody>
	</table>';

	echo $html;
}
?>