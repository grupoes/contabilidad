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

        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card bg-primary">
                <div class="card-body">
                    <h3 class="text-white m-0">88</h3>
                    <span class="m-t-10">Empresas Activas</span>
                    <i class="fas fa-home"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card bg-info">
                <div class="card-body">
                    <h3 class="text-white m-0">1323949.77</h3>
                    <span class="m-t-10">Ingresos Totales</span>
                    <i class="ti ti-trending-up"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card bg-danger">
                <div class="card-body">
                    <h3 class="text-white m-0">1317165.44</h3>
                    <span class="m-t-10">Egresos Totales</span>
                    <i class="ti ti-trending-down"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card bg-success">
                <div class="card-body">
                    <h3 class="text-white m-0">6784.33</h3>
                    <span class="m-t-10">Saldo Actual</span>
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div
                        class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Estado de Caja</h5>
                        <div class="dropdown">
                            <a
                                class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                href="#"
                                data-bs-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Name</a>
                                <a class="dropdown-item" href="#">Date</a>
                                <a class="dropdown-item" href="#">Ratting</a>
                                <a class="dropdown-item" href="#">Unread</a>
                            </div>
                        </div>
                    </div>
                    <div class="my-3">
                        <div id="overview-product-graph"></div>
                    </div>
                    <div class="row g-3 text-center">
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-dark mb-1"><span>Apps</span></p>
                                <h6 class="mb-0">10+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-dark mb-1"><span>Other</span></p>
                                <h6 class="mb-0">5+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-secondary mb-1"><span>Widgets</span></p>
                                <h6 class="mb-0">150+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-secondary mb-1"><span>Forms</span></p>
                                <h6 class="mb-0">50+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-primary mb-1"><span>Components</span></p>
                                <h6 class="mb-0">200+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-primary mb-1"><span>Pages</span></p>
                                <h6 class="mb-0">150+</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body border-bottom pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Estados Clientes</h5>

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
                                MOROSOS
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
                                MOROSOS ANUAL
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
                                DEFECIT
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
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item pt-2 pb-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item pt-2 pb-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item pt-2 pb-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item pt-2 pb-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div
                        class="tab-pane fade"
                        id="analytics-tab-2-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-2"
                        tabindex="0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 05</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div
                        class="tab-pane fade"
                        id="analytics-tab-3-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-3"
                        tabindex="0">
                        <ul class="list-group list-group-flush">

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

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url('assets/js/plugins/apexcharts.min.js') ?>"></script>

<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>

<script src="<?= base_url() ?>js/home/cajero.js?v=2"></script>

<?= $this->endSection() ?>