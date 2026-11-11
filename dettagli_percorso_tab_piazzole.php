<div class="row g-3 align-items-stretch" id="row-tabella-piazzole"> 
<div id="tabella">
    <div class="table-responsive-sm">
      <table  id="piazzole_percorso" class="table-hover table-sm"
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
        data-filter-control="true"
        data-sort-select-options = "true"
        data-export-data-type="all"
        data-url="./tables/get_piazzole_percorso.php?cp=<?php echo $cod_percorso;?>"  
        data-toolbar="#toolbar"
        data-show-footer="false"
        data-query-params="queryParams"
      >
        <thead>
          <tr>
            <!--th data-checkbox="true" data-field="id"></th-->  
            <!--th data-field="state" data-checkbox="true" ></th--> 
            <th data-field="num_seq" data-sortable="true" data-visible="true"  data-filter-control="input">Seq.</th>
            <th data-field="id" data-sortable="true" data-visible="true"  data-filter-control="input">Id</th>
            <th data-field="via" data-sortable="true" data-visible="true" data-filter-control="input">Via</th>
            <th data-field="civico" data-sortable="true" data-visible="true" data-filter-control="input">Civico</th>
            <th data-field="riferimento" data-sortable="true" data-visible="true" data-filter-control="input">Riferimento</th>
            <th data-field="tipo_elemento" data-sortable="true" data-visible="true" data-filter-control="input">Tipo elemento</th>
            <th data-field="elementi" data-sortable="true" data-visible="true" data-filter-control="input">Elem.</th>
            <th data-field="cliente" data-sortable="true" data-visible="true" data-filter-control="input">Cliente</th>
            <th data-field="descrizione_long" data-sortable="true" data-visible="true" data-filter-control="input">Frequenza</th>
            <?php if($check_edit_piazzola != 0) {?>
              <th data-visible="true" data-formatter="mostraEditingPercorso" data-events="actionEditEvents">Modifica</th>
            <?php } ?>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<script>

  function mostraEditingPercorso(value, row, index) {
    return `
      <button class="btn btn-sm btn-primary btn-editing-percorsi"><i class="fa-solid fa-pencil"></i></button>
    `;
  }

  window.actionEditEvents = {
    'click .btn-editing-percorsi': function (e, value, row, index) {
      modificaPercorso(row, index);
    }
  }

  function modificaPercorso(row, index){
    console.log(index);
    console.log(row.id);
    console.log(row.id_asta);

    posizioneTab = document.getElementById("posizione-tab");

    if (!posizioneTab) {
        console.error('Tab non trovata:', "posizione-tab");
        return;
    }


    $(posizioneTab).one('shown.bs.tab', function () {
        mappercorso.invalidateSize();
        evidenziaAsta(row.id_asta, row.id);
    });

    bootstrap.Tab.getOrCreateInstance(posizioneTab).show();

  }

//   $('#piazzole_percorso').on('click-row.bs.table', function (e, row, $element) {
//     let indice = $element.data('index');

//     console.log(indice);
//     console.log(row.id);
//     console.log(row.id_asta);
// });
</script>