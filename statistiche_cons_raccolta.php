<?php

require_once __DIR__ . '/session.php';

$pageTitle = 'Statistiche raccolta';
$pageDescription = 'Statistiche ottenute dai dati della consuntivazione raccolta';

require_once __DIR__ . '/includes/header.php';

$name = dirname(__FILE__);

if ((int)$id_role_SIT === 0) {
    ///echo'Non ho i permessi..';
    redirect('no_permessi.php');
    exit;
}

?>
<!-- FLUID è più esteso su tutto lo schermo -->
<div class="container-fluid">
<!--div class="container"-->
<?php require_once './redirect2wip.php'; ?>

<!-- CONTENUTO DELLA PAGINA -->
<!-- CONTENUTO DELLA PAGINA -->
<main class="dashboard-container">

    <!-- FILTRI -->

    <div class="card shadow-sm dashboard-filter-card">

        <div class="card-header">
            <h4 class="mb-0">
                Statistiche consuntivazione raccolta
            </h4>
        </div>

        <div class="card-body dashboard-filters">

            <form id="frm_filtri">

                <div class="row g-3">


                    <?php
                    $oggi = new DateTime();

                    // Ultimo giorno del mese precedente
                    $dataFine = (clone $oggi)->modify('first day of this month')->modify('-1 day');

                    // Primo giorno del mese, 12 mesi prima
                    $dataInizio = (clone $dataFine)->modify('first day of this month')->modify('-11 months');
                    ?>
                    <!-- DATA DA -->

                    <div class="form-group col-lg-4">
                    <label for="daterange">Periodo</label>
                    <input
                        type="text"
                        class="form-control"
                        id="daterange"
                        name="daterange"
                        value="<?php echo $dataInizio->format('d/m/Y') . ' - ' . $dataFine->format('d/m/Y'); ?>"
                    />
                    <small>Massimo 1 anno e 6 mesi</small>
                </div>

                    <!-- COMUNE -->

                    <div class="col-md-2">
                        <label for="comune" class="form-label">
                            Comune
                        </label>

                        <select
                            class="form-select"
                            id="comune"
                            name="comune">

                            <option value="">
                                Tutti
                            </option>

                        </select>
                    </div>

                    <!-- ZONA -->

                    <div class="col-md-2">
                        <label for="zona" class="form-label">
                            Zona
                        </label>

                        <select
                            class="form-select"
                            id="zona"
                            name="zona">

                            <option value="">
                                Tutte
                            </option>

                        </select>
                    </div>

                    <!-- UT -->

                    <div class="col-md-2">
                        <label for="ut" class="form-label">
                            UT
                        </label>

                        <select
                            class="form-select"
                            id="ut"
                            name="ut">

                            <option value="">
                                Tutte
                            </option>

                        </select>
                    </div>

                </div>

                <hr>

                <!-- CHECKBOX -->

                <div class="row align-items-center">

                    <div class="col-md-12">

                        <div class="d-flex flex-wrap gap-4">

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="solo_arera"
                                    name="solo_arera"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="solo_arera">

                                    Solo servizi ARERA

                                </label>
                            </div>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="escludi_non_previsti"
                                    name="escludi_non_previsti"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="escludi_non_previsti">

                                    Escludi non previsti da SIT

                                </label>
                            </div>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="escludi_causali_non_previste"
                                    name="escludi_causali_non_previste"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="escludi_causali_non_previste">

                                    Escludi causali "Festivo" e "Non previsto"

                                </label>
                            </div>

                        </div>

                    </div>

                </div>

                <hr>

                <!-- AZIONI -->

                <div class="dashboard-actions">

                    <button
                        type="button"
                        class="btn btn-primary"
                        id="btn_aggiorna">

                        Visualiza i dati 

                    </button>

                    <button
                        type="button"
                        id="btn_excel"
                        class="btn btn-success"
                        onclick="esportaExcel()"
                        disabled
                    >
                        <i class="bi bi-file-earmark-excel"></i>
                        Esporta Excel
                    </button>

                </div>

            </form>

        </div>

    </div>

    
    
    
    <!-- KPI -->

    <div class="row mb-3">

        <div class="col-md-3">

            <div class="card shadow-sm dashboard-kpi-card">

                <div class="card-body">

                    <div class="dashboard-kpi-title">
                        Servizi analizzati
                    </div>

                    <div
                        id="kpi_servizi"
                        class="dashboard-kpi-value">

                        -

                    </div>

                    <div class="dashboard-kpi-subtitle">
                        Totale servizi nel periodo
                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm dashboard-kpi-card">

                <div class="card-body">

                    <div class="dashboard-kpi-title">
                        Disservizi
                    </div>

                    <div
                        id="kpi_disservizi"
                        class="dashboard-kpi-value text-danger">

                        -

                    </div>

                    <div class="dashboard-kpi-subtitle">
                        Totale disservizi
                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm dashboard-kpi-card">

                <div class="card-body">

                    <div class="dashboard-kpi-title">
                        % Disservizi
                    </div>

                    <div
                        id="kpi_perc_disservizi"
                        class="dashboard-kpi-value text-warning">

                        -

                    </div>

                    <div class="dashboard-kpi-subtitle">
                        Sul totale dei servizi
                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm dashboard-kpi-card">

                <div class="card-body">

                    <div class="dashboard-kpi-title">
                        Causali
                    </div>

                    <div
                        id="kpi_causali"
                        class="dashboard-kpi-value text-primary">

                        -

                    </div>

                    <div class="dashboard-kpi-subtitle">
                        Numero causali presenti
                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- TREND MENSILE DISSERVIZI -->

    <div class="card shadow-sm dashboard-chart-card">

        <div class="card-header">
            Andamento mensile normalizzato dei disservizi
        </div>

        <div class="card-body">

            <div
                id="chart_disservizi_mese"
                class="dashboard-chart">
            </div>

        </div>

    </div>

    <!-- TREND MENSILE CAUSALI -->

    <div class="card shadow-sm dashboard-chart-card">

        <div class="card-header">
            Andamento mensile normalizzato delle causali
        </div>

        <div class="card-body">

            <div
                id="chart_causali_mese"
                class="dashboard-chart-lg">
            </div>

        </div>

    </div>

    <!-- DISSERVIZI PER GIORNO DELLA SETTIMANA -->

    <div class="card shadow-sm dashboard-chart-card">

        <div class="card-header">
            Disservizi normalizzati per giorno della settimana
        </div>

        <div class="card-body">

            <div
                id="chart_dow"
                class="dashboard-chart">
            </div>

        </div>

    </div>

    <!-- HEATMAP CAUSALI -->

    <div class="card shadow-sm dashboard-chart-card">

        <div class="card-header">
            Heatmap causali per giorno della settimana
        </div>

        <div class="card-body">

            <div
                id="chart_heatmap"
                class="dashboard-heatmap">
            </div>

        </div>

    </div>

</main>


<?php require_once './includes/footer.php'; ?>
<script src="./node_modules/echarts/dist/echarts.min.js"></script>
<script src="./js/statistiche_raccolta.js"></script>
</body>
</html>