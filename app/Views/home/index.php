<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css">

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .alerta-card {
        border: 2px solid red;
        animation: parpadeo 1s infinite;
        box-shadow: 0 0 10px red;
        border-radius: 0.5rem;
        cursor: pointer;
    }

    @keyframes parpadeo {

        0%,
        100% {
            box-shadow: 0 0 10px red;
        }

        50% {
            box-shadow: 0 0 20px red, 0 0 30px rgba(255, 0, 0, 0.5);
        }
    }
</style>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">DASHBOARD</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->
    <div class="row" id="listCards">
        <?php if ($notificacionSire > 0) : ?>
            <div class="col-md-6 col-xl-3">
                <div class="card social-widget-card alerta-card" onclick="viewSire()">
                    <div class="card-body">
                        <h3 class="text-black m-0"><?= $notificacionSire ?></h3>
                        <span class="m-t-10 text-black">SIRE</span>
                        <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($notificacionAfp > 0) : ?>
            <div class="col-md-6 col-xl-3">
                <div class="card social-widget-card alerta-card" onclick="viewAfps()">
                    <div class="card-body">
                        <h3 class="text-black m-0"><?= $notificacionAfp ?></h3>
                        <span class="m-t-10 text-black">AFP</span>
                        <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($notificacionCertificadosVencer > 0) : ?>
            <div class="col-md-6 col-xl-3">
                <div class="card social-widget-card alerta-card" onclick="viewCertificadosVencer()">
                    <div class="card-body">
                        <h3 class="text-black m-0"><?= $notificacionCertificadosVencer ?></h3>
                        <span class="m-t-10 text-black">CERTIFICADOS POR VENCER</span>
                        <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($notificacionDeudoresAnuales > 0) : ?>
            <div class="col-md-6 col-xl-3">
                <div class="card social-widget-card alerta-card" onclick="viewContribuyentesAnuales()">
                    <div class="card-body">
                        <h3 class="text-black m-0"><?= $notificacionDeudoresAnuales ?></h3>
                        <span class="m-t-10 text-black">PDT ANUAL</span>
                        <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($notificacionDeudoresServidor['count'] > 0) : ?>
            <div class="col-md-6 col-xl-3">
                <div class="card social-widget-card alerta-card" onclick="viewContribuyentesServidores()">
                    <div class="card-body">
                        <h3 class="text-black m-0"><?= $notificacionDeudoresServidor['count'] ?></h3>
                        <span class="m-t-10 text-black">SERVIDOR</span>
                        <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($notificacionPdtPlame > 0) : ?>
            <div class="col-md-6 col-xl-3">
                <div class="card social-widget-card alerta-card" onclick="viewContribuyentesPdtsPlame()">
                    <div class="card-body">
                        <h3 class="text-black m-0"><?= $notificacionPdtPlame ?></h3>
                        <span class="m-t-10 text-black">PDT PLAME</span>
                        <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($notificacionPdtRenta > 0) : ?>
            <div class="col-md-6 col-xl-3">
                <div class="card social-widget-card alerta-card" onclick="viewContribuyentesPdts()">
                    <div class="card-body">
                        <h3 class="text-black m-0"><?= $notificacionPdtRenta ?></h3>
                        <span class="m-t-10 text-black">PDT RENTA</span>
                        <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card bg-primary">
                <div class="card-body">
                    <h3 class="text-white m-0" id="empresas_activas"><?= $countCont ?></h3>
                    <span class="m-t-10">Empresas Activas</span>
                    <i class="fas fa-home"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">

                <div class="card-body">
                    <h5>Detalle de Caja Por Sede del Día</h5>
                    <div class="row">


                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body border-bottom pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">CLIENTES MOROSOS</h5>

                    </div>
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
                                MENSUAL
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
                                ANUAL
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
                                SERVIDOR
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
                        <ul class="list-group list-group-flush" id="morosos_mensual" style="height: 300px; overflow-y: auto">

                        </ul>
                    </div>
                    <div
                        class="tab-pane fade"
                        id="analytics-tab-2-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-2"
                        tabindex="0">
                        <ul class="list-group list-group-flush" id="morosos_anual" style="height: 300px; overflow-y: auto">
                        </ul>
                    </div>
                    <div
                        class="tab-pane fade"
                        id="analytics-tab-3-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-3"
                        tabindex="0">
                        <ul class="list-group list-group-flush" id="morosos_servidor" style="height: 300px; overflow-y: auto">

                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- [ Main Content ] end -->
</div>


<div id="modalPdts" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">PDT RENTA SUBIR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                        <thead>
                            <tr>
                                <th>Contribuyente</th>
                                <th>Periodo</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="listPdts">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modalPdtsPlame" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">PDT PLAME SUBIR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tablePlame">
                        <thead>
                            <tr>
                                <th>Contribuyente</th>
                                <th>Periodo</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="listPdtsPlame">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modalPdtsServidores" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">PAGO DE SERVIDORES</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableServidor">
                        <thead>
                            <tr>
                                <th>Contribuyente</th>
                                <th>Periodo</th>
                                <th>Deuda</th>
                            </tr>
                        </thead>
                        <tbody id="listServidores">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modalPdtsAnuales" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">PDT ANUAL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableAnuales">
                        <thead>
                            <tr>
                                <th>Contribuyente</th>
                                <th>Periodo</th>
                                <th>Deuda</th>
                            </tr>
                        </thead>
                        <tbody id="listAnuales">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modalCertificado" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">Certificados Digitales por vencer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table" id="tableCertificados">
                        <thead>
                            <tr>
                                <th>FECHA VENCIMIENTO</th>
                                <th>FECHA INICIO</th>
                                <th>RUC</th>
                                <th>RAZON SOCIAL</th>
                                <th>TIPO CERTIFICADO</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCertificados">

                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modalAfp" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">FALTAN SUBIR AFP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table" id="tableAfp">
                        <thead>
                            <tr>
                                <th>RAZON SOCIAL</th>
                                <th>PERIODO</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyAfp">

                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modalSire" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">FALTAN SUBIR SIRE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table" id="tableSire">
                        <thead>
                            <tr>
                                <th>RAZON SOCIAL</th>
                                <th>PERIODO</th>
                                <th>ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody id="tbodySire">

                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url('assets/js/plugins/apexcharts.min.js') ?>"></script>

<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>

<script src="<?= base_url() ?>js/home/admin.js?v=6"></script>

<?= $this->endSection() ?>