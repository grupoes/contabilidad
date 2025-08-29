<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css">


<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">Lista de cobros</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body border-bottom pb-0">

                    <ul
                        class="nav nav-tabs analytics-tab"
                        id="myTab"
                        role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="analytics-tab-1"
                                data-bs-toggle="tab"
                                data-bs-target="#analytics-tab-1-pane"
                                type="button"
                                role="tab"
                                aria-controls="analytics-tab-1-pane"
                                aria-selected="true">
                                Honorarios Mensuales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="analytics-tab-2"
                                data-bs-toggle="tab"
                                data-bs-target="#analytics-tab-2-pane"
                                type="button"
                                role="tab"
                                aria-controls="analytics-tab-2-pane"
                                aria-selected="false">
                                Honorarios Anuales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="analytics-tab-3"
                                data-bs-toggle="tab"
                                data-bs-target="#analytics-tab-3-pane"
                                type="button"
                                role="tab"
                                aria-controls="analytics-tab-3-pane"
                                aria-selected="false">
                                Servidor
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="myTabContent">
                    <div
                        class="tab-pane fade show active"
                        id="analytics-tab-1-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-1"
                        tabindex="0">
                        <div class="card">
                            <div class="card-body pt-2">
                                <div class="d-flex justify-content-between align-items-center p-2 pb-sm-2">
                                    <!-- Contenedor para el select y el botón -->
                                    <div class="d-flex align-items-center gap-2">
                                        <select id="selectOpciones" class="form-select w-auto">
                                            <option value="TODOS">TODOS</option>
                                            <option value="CONTABLE">CONTABLE</option>
                                            <option value="ALQUILER">ALQUILER</option>
                                        </select>

                                        <select id="estados" class="form-select w-auto">
                                            <option value="1">ACTIVOS</option>
                                            <option value="2">INACTIVOS</option>
                                        </select>

                                    </div>

                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Razón Social</th>
                                                <th>Dia de Cobro</th>
                                                <th>Tipo de Pago</th>
                                                <th>Servicio</th>
                                                <th>Deuda</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="tab-pane fade"
                        id="analytics-tab-2-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-2"
                        tabindex="0">
                        <div class="card">
                            <div class="card-body pt-2">
                                <div class="d-flex justify-content-between align-items-center p-2 pb-sm-2">
                                    <!-- Contenedor para el select y el botón -->
                                    <div class="d-flex align-items-center gap-2">
                                        <select id="selectOpciones" class="form-select w-auto">
                                            <option value="TODOS">TODOS</option>
                                            <option value="CONTABLE">CONTABLE</option>
                                            <option value="ALQUILER">ALQUILER</option>
                                        </select>

                                        <select id="estados" class="form-select w-auto">
                                            <option value="1">ACTIVOS</option>
                                            <option value="2">INACTIVOS</option>
                                        </select>

                                    </div>

                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Razón Social</th>
                                                <th>Dia de Cobro</th>
                                                <th>Tipo de Pago</th>
                                                <th>Servicio</th>
                                                <th>Deuda</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBodyAnual">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="tab-pane fade"
                        id="analytics-tab-3-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-3"
                        tabindex="0">
                        <div class="card">
                            <div class="card-body pt-2">


                                <div class="table-responsive">
                                    <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableDataServidor">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Razón Social</th>
                                                <th>Sistemas</th>
                                                <th>Deuda</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBodyServidor">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/contribuyente/listaCobros.js?v=1"></script>

<?= $this->endSection() ?>