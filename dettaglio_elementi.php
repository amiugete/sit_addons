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
		<meta name="author" content="roberto" >

		<title>Dettagli elementi piazzola</title>

		<?php 
			$check_modal=1;
		require_once('./req.php');

		the_page_title();

		$id_piazzola = $_POST['id_piazzola'];
		$id_tipo_elemento = $_POST['id_tipo'];
		$check_edit_piazzola = $_POST['check_edit'];

		?> 
	</head>
  	<body>
		<div class="container">
			<div id="tabella_elementi">
				<div class="table-responsive-sm">
					<div id="toolbar"> 
						<!--button id="export-btn-filtered" class="btn btn-primary" title="Esporta file Excel filtrato"><i class="fa-solid fa-filter"></i>Esporta tabella filtrata</button-->
					</div>
					<table  id="dett_elementi" class="table-hover table-sm"
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
						data-url="./tables/get_dettaglio_elementi.php?idp=<?php echo $id_piazzola;?>&idt=<?php echo $id_tipo_elemento;?>"
						data-toolbar="#toolbar"
						data-show-footer="false"
						data-query-params="queryParams"
						data-row-style="rowStyle"
					>
						<thead>
							<tr>
								<th data-field="id_elemento" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="idFormatter" data-events="idEvents">Elemento</th>
								<th data-field="descr_elem" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="editTipoFormatter">Tipo</th>
								<th data-field="tag" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="editTagFormatter">Tag</th>
								<th data-field="matricola" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="editMatrFormatter">Matricola</th>
								<th data-field="serratura" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="editSerrFormatter">Serratura</th>
								<th data-field="matricola_serratura" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="editSerrMatrFormatter">Matricola<br>Serratura</th>
								<th data-field="materiale" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="editMaterialeFormatter">Materiale</th>
								<th data-field="percent_riempimento" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="editRiempFormatter">% Riempimento</th>
								<th data-field="freq_stimata" data-sortable="true" data-visible="true"  data-filter-control="input" data-formatter="editFreqFormatter">Freq.<br>da CDS</th>
								<?php if ($check_edit_piazzola != 0){?>
									<th data-field="id_elemento1" data-sortable="false" data-visible="true"   data-events="editEvents" data-formatter="editFormatter" ></th>
									<th data-field="id_elemento2" data-sortable="false" data-visible="true"   data-events="manEvents" data-formatter="manutenzioneFormatter"></th>
									<th data-field="id_elemento4" data-sortable="false" data-visible="true"   data-events="disEvents" data-formatter="dismissFormatter"></th>
									<th data-field="id_elemento3" data-sortable="false" data-visible="true"   data-events="delEvents" data-formatter="deleteFormatter"></th>
								<?php } ?>
								</tr>
						</thead>
					</table>
					<div class="modal fade" id="viewMemberModal1" tabindex="-1" aria-labelledby="exampleModalLabel1" aria-hidden="true"> 
						<div class="modal-dialog modal-dialog-scrollable modal-xl" >
							<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title" id="exampleModalLabel1"><?php echo $id_piazzola; ?> - <?php echo $via; ?>, <?php echo $civico; ?> - <?php echo $riferimento; ?></h4>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body" id="body_modal_man">
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
			<div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
		</div>

    <script>
      	var $table = $('#dett_elementi');
      
		$(function() {
			$table.bootstrapTable()
		});

		function rowStyle(row, index) {
			if (row.id_elemento < 0) {
				return {
					css: {
					'--bs-table-bg':'#c4ffbc'
					}
				}
			}

			if (row.elem_sospeso) {
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
		}

		function idFormatter(value, row, index) {
			if(row.num_interventi_totali > 0 && value > 0){
			return `<i class="fa-solid fa-wrench"></i>
			<button type="button" class="btn btn-sm btn-richieste-dett" data-bs-container="body" data-bs-toggle="popover" style="padding-left: 0px; padding-top:0px; border:none;"
			data-id-elemento=${value}>
				<i class="fa-regular fa-circle-question" style="margin-left: 0px;"></i>
			</button> ${value}
			`;
			}else{
				return `${value}`;
			}
		}

		window.idEvents = {
        'click .btn-richieste-dett': function (e, value, row, index) {
          const button = e.currentTarget;

          $.ajax({
              url: './tables/get_richieste_intervento.php',
              type: 'POST',
              data: {
                id_elemento: value
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
        $('.btn-richieste-dett').each(function() {
            const popover = bootstrap.Popover.getInstance(this);

            if (popover) {
                popover.hide();
            }
        });

      });

		function editFormatter(value, row, index) {

			const disabilitato = row.id_tipo_intervento != null && row.gestione_sit === 't';

			return `
				<button class="btn btn-sm btn-primary btn-edit" title="Modifica dettagli dell'elemento" ${disabilitato ? 'disabled' : ''}>
					<i class="fa-solid fa-pen"></i>
				</button>

				<button class="btn btn-sm btn-primary btn-save d-none" style="margin-bottom: 5px;" title="Salva le modifiche">
					<i class="fa-solid fa-floppy-disk"></i>
				</button>

				<button class="btn btn-sm btn-primary btn-cancel d-none" title="Annulla le modifiche">
					<i class="fa-solid fa-arrow-rotate-left"></i>
				</button>
			`;
		}

		function manutenzioneFormatter(value, row, index) {
	
			let disabilitato = (row.id_tipo_intervento != null && row.gestione_sit === 't') || ( row.manutenzione !== 't');
			/*console.log('disabilitato = '+ disabilitato)
			console.log('tipo_int = '+ row.id_tipo_intervento)
			console.log('gestione = '+ row.gestione_sit)
			console.log('manutenzione = '+ row.manutenzione)*/

			return `
				<button class="btn btn-sm btn-warning btn-manutenzione" title="Richiedi manutenzione" ${disabilitato ? 'disabled' : ''}>
					<i class="fa-solid fa-wrench"></i>
				</button>
			`;
		}

		/*function dismissFormatter(value, row, index) {
			// aggiunta alla queri dei taggli elemento la data_disattivazione_temp così se popolata mostro il tasto play altrimenti quello pausa
			if (row.id_tipo_intervento == null){
				if (row.elem_sospeso) {
					return `
						<button class="btn btn-sm btn-secondary btn-riprist" title="Ripristina gli elementi" style="cursor:pointer">
							<i class="fa-solid fa-play"></i>
						</button>
					`;
				} else {
					return `
						<button class="btn btn-sm btn-secondary btn-dismiss" title="Dismetti temporaneamente l'elemento">
							<i class="fa-solid fa-pause"></i>
						</button>
					`;
				}
			}else{
				if (row.gestione_sit === 't'){
					if (row.elem_sospeso) {
						return `
							<button class="btn btn-sm btn-secondary btn-riprist" title="Ripristina gli elementi" style="cursor:pointer" disabled>
								<i class="fa-solid fa-play"></i>
							</button>
						`;
					} else {
						return `
							<button class="btn btn-sm btn-secondary btn-dismiss" title="Dismetti temporaneamente l'elemento" disabled>
								<i class="fa-solid fa-pause"></i>
							</button>
						`;
					}
				}else{
					if (row.elem_sospeso) {
					return `
						<button class="btn btn-sm btn-secondary btn-riprist" title="Ripristina gli elementi" style="cursor:pointer">
							<i class="fa-solid fa-play"></i>
						</button>
					`;
					} else {
						return `
							<button class="btn btn-sm btn-secondary btn-dismiss" title="Dismetti temporaneamente l'elemento">
								<i class="fa-solid fa-pause"></i>
							</button>
						`;
					}
				}
			}
		}*/

		function dismissFormatter(value, row, index) {
			// Se l'elemento è sospeso mostro il pulsante "Ripristina",
			// altrimenti mostro il pulsante "Dismetti"
			// !! converte il valore in un booleano JS
			const elementoSospeso = !!row.elem_sospeso;

			// Il pulsante viene disabilitato se esiste un intervento
			// e almeno uno degli interventi è gestito dal SIT
			const disabilitaPulsante = (row.id_tipo_intervento != null && row.gestione_sit === 't') || (row.disabilita == 1);

			let classePulsante;
			let iconaPulsante;
			let titoloPulsante;

			if (elementoSospeso) {
				classePulsante = 'btn-riprist';
				iconaPulsante = 'fa-play';
				titoloPulsante = 'Ripristina gli elementi';
			} else {
				classePulsante = 'btn-dismiss';
				iconaPulsante = 'fa-pause';
				titoloPulsante = 'Dismetti temporaneamente l\'elemento';
			}

			return `
				<button class="btn btn-sm btn-secondary ${classePulsante}"
						title="${titoloPulsante}"
						style="cursor:pointer"
						${disabilitaPulsante ? 'disabled' : ''}>
					<i class="fa-solid ${iconaPulsante}"></i>
				</button>
			`;
		}

		function deleteFormatter(value, row, index) {

			const disabilitato = (row.id_tipo_intervento != null && row.gestione_sit === 't') || (row.disabilita == 1);

			return `
				<button class="btn btn-sm btn-danger btn-delete" title="Elimina elemento" ${disabilitato ? 'disabled' : ''}>
					<i class="fa-solid fa-xmark"></i>
				</button>
			`;
		}

		function editTipoFormatter(value, row, index) {
			return `
				<span class="view-mode view_tipo_elemento">${value}</span>

				<select class="form-select form-select-sm edit_tipo_elemento edit-mode d-none"></select>
			`;
		}

		function editMaterialeFormatter(value, row, index) {
			return `
				<span class="view-mode">${value}</span>

				<select class="form-select form-select-sm edit_materiale edit-mode d-none"></select>
			`;
		}

		function editTagFormatter(value) {
			return `
				<span class="view-mode">${value === null ? '' : value}</span>

				<input type="text" class="form-control form-control-sm edit_tag edit-mode d-none" value="${value=== null ? '' : value}">
			`;
		}

		function editMatrFormatter(value) {
			return `
				<span class="view-mode">${value=== null ? '' : value}</span>

				<input type="text" class="form-control form-control-sm edit_matricola edit-mode d-none" value="${value=== null ? '' : value}">
			`;
		}

		function editSerrFormatter(value) {
			return `
				<span class="view-mode"><input type="checkbox" disabled ${value == 1 ? 'checked' : ''}></span>

				<input type="checkbox" class="edit_serratura edit-mode d-none" value="${value}" ${value == 1 ? 'checked' : ''}>
			`;
		}


		function editSerrMatrFormatter(value) {
			return `
				<span class="view-mode">${value === null ? '' : value}</span>

				<input type="text" class="form-control form-control-sm edit_matricola_serratura edit-mode d-none" value="${value === null ? '' : value}">
			`;
		}

		function editRiempFormatter(value) {
			return `
				<span class="view-mode view_percent_riempimento">${value === null ? '' : value}</span>

				<input type="number" class="form-control form-control-sm edit_percent_riempimento edit-mode d-none" value="${value === null ? '' : value}">
			`;
		}

		function editFreqFormatter(value) {
			return `
				<span class="view-mode view_freq_stimata">${value === null ? '' : value}</span>

				<input type="number" class="form-control form-control-sm edit_freq_stimata edit-mode d-none" value="${value}">
			`;
		}

		window.editEvents = {
			'click .btn-edit': function(e, value, row, index) {

				const riga_cliccata = $(e.currentTarget).closest('tr');

				// disabilito tutti i tasti modifica e ri-abilito solo quello della riga cliccata
				$('.btn-edit').prop('disabled', true);
				riga_cliccata.find('.btn-edit').prop('disabled', false);

				// mi salvo i valori originali delle celle della riga cliccata per poterli ripristinare in caso di annullamento delle modifiche
				riga_cliccata.data('originalValues', {
					tipo_elemento: row.tipo_elemento,
					tipo_elemento_desc: riga_cliccata.find('.view_tipo_elemento').text(),
					tag: row.tag,
					materiale: row.id_materiale,
					materiale_desc: riga_cliccata.find('.view_materiale').text(),
					matricola: row.matricola,
					serratura: row.serratura,
					matricola_serratura: row.matricola_serratura,
					riempimento: row.percent_riempimento,
					frequenza: row.freq_stimata
				});

				console.log('originalValues: ', riga_cliccata.data('originalValues'));

				//popolo select tipo elemento con i valori disponibili per il tipo di rifiuto e tipo di raccolta della riga cliccata
				$.ajax({
					url: './backoffice/get_tipi_elementi.php',
					type: 'POST',
					dataType: 'json',
					data: {
						tipo_rifiuto: row.tipo_rifiuto,
						tipo_raccolta: row.tipo_raccolta
					},
					success: function(response) {

						let html = '';

						response.forEach(r => {
							html += `
								<option value="${r.tipo_elemento}" ${r.tipo_elemento === row.tipo_elemento ? 'selected' : ''}>
									${r.descrizione}
								</option>
							`;
						});

						//riga_cliccata.find('.edit_tipo_elemento').html(html);
						riga_cliccata.find('.edit_tipo_elemento')
						.html(html)
						.off('change')
						.on('change', function() {

							const nuovoTipo = $(this).val();
							const nuovaDescrizione = $(this).find('option:selected').text();
							// modifico tutte le righe della tabella
							$('#tabella_elementi tbody tr').each(function() {

								const tr = $(this);

								// aggiorna la select
								tr.find('.edit_tipo_elemento')
									.val(nuovoTipo);

								// aggiorna il testo visualizzato
								tr.find('.view-mode.view_tipo_elemento')
									.text(nuovaDescrizione);
									

								// evidenzia la cella
								tr.find('.edit_tipo_elemento')
									.closest('td')
									.addClass('dettaglio-elemento-modificato');

								// memorizza che la riga è stata modificata
								tr.data('tipo_elemento_modificato', true);
							});

						});

					}
				});

				//popolo select tipo materiale con i valori disponibili
				$.ajax({
					url: './backoffice/get_tipi_materiali.php',
					type: 'POST',
					dataType: 'json',
					success: function(response) {

						let html = '';

						response.forEach(r => {
							html += `
								<option value="${r.id_materiale}" ${r.id_materiale === row.id_materiale ? 'selected' : ''}>
									${r.descrizione}
								</option>
							`;
						});

						riga_cliccata.find('.edit_materiale').html(html);
					}
				});

				// Aggiorno la percentuale di riempimento per tutte le righe della tabella
				$(document).on('input', '.edit_percent_riempimento', function() {

					const valore = $(this).val();

					$('.edit_percent_riempimento').val(valore);
					$('.view_percent_riempimento').text(valore);

					$('.edit_percent_riempimento')
						.closest('td')
						.addClass('dettaglio-elemento-modificato');
				});
				
				// Aggiorno la frequenza stimata per tutte le righe della tabella
				$(document).on('input', '.edit_freq_stimata', function() {

					const valore = $(this).val();

					$('.edit_freq_stimata').val(valore);
					$('.view_freq_stimata').text(valore);

					$('.edit_freq_stimata')
						.closest('td')
						.addClass('dettaglio-elemento-modificato');
				});

				riga_cliccata.find('.view-mode').addClass('d-none');
				riga_cliccata.find('.edit-mode').removeClass('d-none');

				riga_cliccata.find('.btn-edit').addClass('d-none');
				riga_cliccata.find('.btn-save').removeClass('d-none');
				riga_cliccata.find('.btn-cancel').removeClass('d-none');
			},
			'click .btn-save': function(e, value, row, index) {

				const riga_cliccata = $(e.currentTarget).closest('tr');

				// recupero tutte le righe della tabella
				const dati_tabella = $('#dett_elementi').bootstrapTable('getData');
				const id_elementi_tabella = [];

				for (const dt of dati_tabella) {
					id_elementi_tabella.push(dt.id_elemento);
				}

				$.ajax({
					url: './backoffice/edit_dettagli_elemento.php',
					type: 'POST',
					data: {
						id_elemento: row.id_elemento,
						id_elementi_tabella: id_elementi_tabella,
						tipo_elemento: riga_cliccata.find('.edit_tipo_elemento').val(),
						tag: riga_cliccata.find('.edit_tag').val(),
						matricola: riga_cliccata.find('.edit_matricola').val(),
						serratura: riga_cliccata.find('.edit_serratura').is(':checked') ? 1 : 0,
						matricola_serratura: riga_cliccata.find('.edit_matricola_serratura').val(),
						id_materiale: riga_cliccata.find('.edit_materiale').val(),
						percent_riempimento: riga_cliccata.find('.edit_percent_riempimento').val(),
						freq_stimata: riga_cliccata.find('.edit_freq_stimata').val()
					},
					dataType: 'json',
					success: function(response) {
						console.log(response.messaggio);
						$('#dett_elementi').bootstrapTable('refresh');
						showToast(response.messaggio, 'success', 'modal');
					}, 
					error: function (jqXHR, textStatus, errorThrown) {  
						const messaggio = jqXHR.responseJSON?.messaggio || 'Errore durante il salvataggio delle modifiche!';                      
						showToast(messaggio, 'error', 'modal');
						console.error(errorThrown); 
					}
				});

				// abilito tutti i tasti modifica 
				$('.btn-edit').prop('disabled', false);

				riga_cliccata.find('.edit-mode').addClass('d-none');
				riga_cliccata.find('.view-mode').removeClass('d-none');
				$('.dettaglio-elemento-modificato').removeClass('dettaglio-elemento-modificato');

				riga_cliccata.find('.btn-save').addClass('d-none');
				riga_cliccata.find('.btn-cancel').addClass('d-none');
				riga_cliccata.find('.btn-edit').removeClass('d-none');
			},
			'click .btn-cancel': function(e, value, row, index) {

				const riga_cliccata = $(e.currentTarget).closest('tr');

				//ripristino i valori originali delle celle della riga cliccata
				$('.edit_tipo_elemento').val(riga_cliccata.data('originalValues').tipo_elemento);
				$('.view_tipo_elemento').text(riga_cliccata.data('originalValues').tipo_elemento_desc);
				riga_cliccata.find('.edit_tag').val(riga_cliccata.data('originalValues').tag);
				riga_cliccata.find('.edit_matricola').val(riga_cliccata.data('originalValues').matricola);
				riga_cliccata.find('.edit_serratura').prop('checked', riga_cliccata.data('originalValues').serratura == 1);
				riga_cliccata.find('.edit_matricola_serratura').val(riga_cliccata.data('originalValues').matricola_serratura);
				riga_cliccata.find('.edit_materiale').val(riga_cliccata.data('originalValues').materiale);
				riga_cliccata.find('.view_materiale').text(riga_cliccata.data('originalValues').materiale_desc);
				$('.view_percent_riempimento').text(riga_cliccata.data('originalValues').riempimento);
				$('.view_freq_stimata').text(riga_cliccata.data('originalValues').frequenza);

				//$('#dett_elementi').bootstrapTable('refresh');

				// abilito tutti i tasti modifica 
				$('.btn-edit').prop('disabled', false);

				riga_cliccata.find('.edit-mode').addClass('d-none');
				riga_cliccata.find('.view-mode').removeClass('d-none');
				$('.dettaglio-elemento-modificato').removeClass('dettaglio-elemento-modificato');

				riga_cliccata.find('.btn-save').addClass('d-none');
				riga_cliccata.find('.btn-cancel').addClass('d-none');
				riga_cliccata.find('.btn-edit').removeClass('d-none');
			}

		};

		window.manEvents = {
			'click .btn-manutenzione': function (e, value, row, index) {
			$("#body_modal").append(`
				<div id="overlayManutenzione"
				class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
				style="background: rgba(0,0,0,.2); z-index: 2000;">
					<div class="bg-white rounded shadow p-3 text-center">
						<form id="richiesta_manutenzione" name="richiesta_manutenzione" method="post" autocomplete="off" action="">
							<input type="hidden" name="id_piazzola" value="<?php echo $id_piazzola; ?>">
							<input type="hidden" name="tipo_elemento" value="${row.tipo_elemento}">
							<input type="hidden" name="id_elemento" value="${row.id_elemento}">
							<div class="d-flex align-items-center gap-2 mb-3">
							<label class="form-label mb-0" for="priorita"><b>Priorità</b></label>
							<select class="form-select w-auto" name="priorita" id="priorita" <?php echo $edit; ?>>
								<option value="">Seleziona..</option>
								<?php
								$query_priorita = 'SELECT id, descrizione FROM gestione_oggetti.tipo_priorita ORDER BY ordine';
								$result_priorita = pg_query($conn, $query_priorita);
								while ($r = pg_fetch_assoc($result_priorita)) {
									echo "<option value=\"{$r['id']}\">{$r['descrizione']}</option>";
								}
								?>
							</select>
							</div>
							<div class="d-flex align-items-center gap-2 mb-3">
							<label class="form-label mb-0" for="tipo_int"><b>Tipo Intervento</b></label>
							<select class="form-select w-auto" name="tipo_int" id="tipo_int" <?php echo $edit; ?>>
								<option value="">Seleziona..</option>
								<?php
								$query_tipo_int = 'SELECT id, descrizione FROM gestione_oggetti.tipo_intervento
								where gestione_sit = false ORDER BY id';
								$result_tipo_int = pg_query($conn, $query_tipo_int);
								while ($r = pg_fetch_assoc($result_tipo_int)) {
									echo "<option value=\"{$r['id']}\">{$r['descrizione']}</option>";
								}
								?>
							</select>
							</div>
							<div class="d-flex align-items-center gap-2 mb-3">
								<label class="form-label mb-0" for="note_man"><b>Note</b></label>
								<input type="text" class="form-control" id="note_man" name="note_man" placeholder="Inserisci note per la manutenzione" required>
							</div>
						</form>
						<div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
						<div class="d-flex justify-content-end gap-2 mt-4">
							<button type="button" class="btn btn-primary" id="btnSalvaManutenzione" onclick="inviaRichiestaMan()"> Invia </button>
							<button type="button" class="btn btn-secondary" id="btnChiudiManutenzione" onclick="chiudiRichiestaMan()"> Chiudi </button>
						</div>
					</div>
				</div>`);
			}
		};

		window.delEvents = {
			'click .btn-delete': function (e, value, row, index) {
				console.log('Cliccato elimina elemento con id_elemento:', row.id_elemento);
				$("#body_modal").append(`
				<div id="overlayDelete" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
				style="background: rgba(0,0,0,.2); z-index: 2000;">
					<div class="bg-white rounded shadow p-3 text-center">
						<h5>Verrà eliminato l'elemento <b>${row.id_elemento}</b></h5>
						<br>
						<button type="button" class="btn btn-primary" id="del_elemento_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o per aggiornamento SIT" onclick="eliminaElemento(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, ${row.id_elemento})"><i class="fa-solid fa-display"></i> Nessun intervento</button>
						<button type="button" class="btn btn-primary" id="del_elemento_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, ${row.id_elemento})" ${row.manutenzione ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
						<div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
						<hr>
						<div class="d-flex justify-content-end gap-2 mt-4">
							<button type="button" class="btn btn-secondary" id="btnChiudiDel" onclick="chiudiDelete()"> Chiudi </button>
						</div>
					</div>
				</div>
            `);
			}
		};

		window.disEvents = {
			'click .btn-dismiss': function (e, value, row, index) {
				console.log('Cliccato dismetti temporaneamente elemento con id_elemento:', row.id_elemento);
				$("#body_modal").append(`
				<div id="overlayDismiss" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
				style="background: rgba(0,0,0,.2); z-index: 2000;">
					<div class="bg-white rounded shadow p-3 text-center">
						<h5>Verrà dismesso temporaneamente l'elemento <b>${row.id_elemento}</b></h5>
						<br>
						<button type="button" class="btn btn-primary" id="dis_elemento_btn" style="cursor:pointer; margin-right: 10px;" title="Intervento da svolgere in autonomia o per aggiornamento SIT" onclick="dismettiElementi(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, ${row.id_elemento})"><i class="fa-solid fa-display"></i> Nessun intervento</button>
						<button type="button" class="btn btn-primary" id="dis_elemento_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, ${row.id_elemento})" ${row.manutenzione ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
						<div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
						<hr>
						<div class="d-flex justify-content-end gap-2 mt-4">
							<button type="button" class="btn btn-secondary" id="btnChiudiDis" onclick="chiudiDismiss()"> Chiudi </button>
						</div>
					</div>
				</div>
            `);
			},
			'click .btn-riprist': function (e, value, row, index) {
				console.log('Cliccato ripristina elemento con id_elemento:', row.id_elemento);
				$("#body_modal").append(`
				<div id="overlayRipristina" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
				style="background: rgba(0,0,0,.2); z-index: 2000;">
					<div class="bg-white rounded shadow p-3 text-center">
						<h5>Verrà ripristinato l'elemento <b>${row.id_elemento}</b></h5>
						<br>
						<button type="button" class="btn btn-primary" id="riprist_elemento_btn" style="cursor:pointer; margin-right: 10px;" title="Ripristina elemento" onclick="ripristinaElementi(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, ${row.id_elemento})"><i class="fa-solid fa-display"></i> Nessun intervento</button>
						<button type="button" class="btn btn-primary" id="riprist_elemento_intervento_btn" style="cursor:pointer; margin-left: 10px;" title="Richiedi intervento a manutenzione contenitori" onclick="inviaManutenzione(this, <?php echo $id_piazzola; ?>,${row.tipo_elemento},${row.id_utenzapap}, ${row.id_elemento})" ${row.manutenzione ? '' : 'disabled'}><i class="fa-solid fa-wrench"></i> Richiedi intervento</button>
						<div id="toast_modal" class="toast hidden" style="text-align: center; margin-top: 1%; margin-bottom: 1%; width: auto;"></div>
						<hr>
						<div class="d-flex justify-content-end gap-2 mt-4">
							<button type="button" class="btn btn-secondary" id="btnChiudiDis" onclick="chiudiRipristina()"> Chiudi </button>
						</div>
					</div>
				</div>
            `);
			}

		};


		function chiudiDelete() {
			$("#overlayDelete").remove();
		}


		function eliminaElemento(button, id_piazzola, tipo_elemento, id_utenzapap, id_elemento) {
			//window.location.href = 'dettagli_elemento.php?id=' + id;
			console.log('Eliminerà l\' elemento nella piazzola ' + id_piazzola + 'senza chiedere alcun intervento' );
			const id = `${id_piazzola}_${tipo_elemento}`;
			console.log('Invio id:', id);
			$('#del_elemento_intervento_btn').prop('disabled', true);

			ids_elementi = []
			ids_elementi=id_elemento

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
				$("#overlayDelete").remove();
				$('#dett_elementi').bootstrapTable('refresh');
			},
			error: function (jqXHR, textStatus, errorThrown) {
				showToast('Errore durante l\'eliminazione dell\'elemento.', 'error', 'modal');
				console.error(errorThrown);
			}
			});
		}

		function chiudiDismiss() {
			$("#overlayDismiss").remove();
		}


		function dismettiElementi(button, id_piazzola, tipo_elemento, id_utenzapap, id_elemento) {
       
			//window.location.href = 'dettagli_elemento.php?id=' + id;
			console.log('Dismetterà temporaneamente l\'elemento ('+id_elemento+') di tipo '+tipo_elemento+' nella piazzola ' + id_piazzola + 'senza chiedere alcun intervento' );
			const id = `${id_piazzola}_${tipo_elemento}`;
			
			$('#dis_elemento_intervento_btn').prop('disabled', true);

			ids_elementi = []
			ids_elementi=id_elemento

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
				$("#overlayDismiss").remove();
				$('#dett_elementi').bootstrapTable('refresh');
			},
			error: function (jqXHR, textStatus, errorThrown) {
				showToast('Errore durante la dismissione temporanea dell\'elemento.', 'error', 'modal');
				console.error(errorThrown);
			}
			});
		}

		function chiudiRipristina() {
			$("#overlayRipristina").remove();
		}

		function ripristinaElementi(button, id_piazzola, tipo_elemento, id_utenzapap, id_elemento) {
       
			//window.location.href = 'dettagli_elemento.php?id=' + id;
			console.log('Ripristinerà l\'elemento ('+id_elemento+') di tipo '+tipo_elemento+' nella piazzola ' + id_piazzola + 'senza chiedere alcun intervento' );
			const id = `${id_piazzola}_${tipo_elemento}`;
			
			$('#riprist_elemento_intervento_btn').prop('disabled', true);

			ids_elementi = []
			ids_elementi=id_elemento

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
				$("#overlayRipristina").remove();
				$('#dett_elementi').bootstrapTable('refresh');
			},
			error: function (jqXHR, textStatus, errorThrown) {
				showToast('Errore durante il ripristino dell\'elemento.', 'error', 'modal');
				console.error(errorThrown);
			}
			});
		}

		function inviaManutenzione(button, id_piazzola, tipo_elemento, id_utenzapap, id_elemento) {
			//window.location.href = 'dettagli_elemento.php?id=' + id;
			console.log('Invierà richiesta di manutenzione per l\'elemento della piazzola ' + id_piazzola + ' di tipo ' + tipo_elemento);
			//showToast(`Invierà richiesta di manutenzione per l\'elemento della piazzola ${id_piazzola} di tipo ${tipo_elemento}`, 'warning', 'modal');
			const id = `${id_piazzola}_${tipo_elemento}`;

			var buttonId = button.id; 
			console.log('Bottone ' + buttonId );
			if (buttonId === 'del_elemento_intervento_btn') {
				tipo_intervento = 'del';
				$('#del_elemento_btn').prop('disabled', true);
			}else if (buttonId === 'dis_elemento_intervento_btn') {
				tipo_intervento = 'dis';
				$('#dis_elemento_btn').prop('disabled', true);
			}else if (buttonId === 'riprist_elemento_intervento_btn') {
				tipo_intervento = 'rip';
				$('#riprist_elemento_btn').prop('disabled', true);
			}

			ids_elementi = []
			ids_elementi=id_elemento
			
			$.ajax({
			url: 'backoffice/go_movimentazione.php',
			method: 'POST',
			data: { id_piazzola: id,
				id_utenzapap: id_utenzapap,
				tipo_intervento: tipo_intervento,
				ids_elementi: ids_elementi

			},
			success: function (response) {
				//console.log(response);
				if (tipo_intervento === 'del') {
                $ ("#overlayDelete").remove();
              }else if (tipo_intervento === 'dis'){
                $("#overlayDismiss").remove();
              }
				showToast(response, 'success', 'modal');
			},
			error: function (jqXHR, textStatus, errorThrown) {
				showToast('Errore durante l\'invio della richiesta a manutenzione cassonetti.', 'error', 'modal');
				console.error(errorThrown);
			}
			});
		}


		function chiudiRichiestaMan() {
			$("#overlayManutenzione").remove();
		}

		function inviaRichiestaMan() {
			let formData = $("#richiesta_manutenzione").serialize();

			formData += '&tipo_int_desc=' + $('#tipo_int option:selected').text();

			$.ajax({
				url: './backoffice/go_riparazione.php',
				type: 'POST',
				data: formData,
				success: function(response) {
					showToast(response, 'success', 'modal');
					$("#overlayManutenzione").remove();
				},
				error: function (jqXHR, textStatus, errorThrown) {                        
					showToast(response, 'error', 'modal');
					console.error(errorThrown); 
				}
			});
		}
	</script>

	<script>
		function showToast(message, type = 'success', pos='page') {

			let toast;
			if (pos === 'modal') {
				toast = document.getElementById('toast_modal');
			} else {
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




	