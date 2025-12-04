<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css">

<style>
    .bell-shake {
        display: inline-block;
        animation: ring 2s infinite;
        transform-origin: top center;
    }

    /* Animación tipo "campanita" */
    @keyframes ring {
        0% {
            transform: rotate(0);
        }

        10% {
            transform: rotate(25deg);
        }

        20% {
            transform: rotate(-20deg);
        }

        30% {
            transform: rotate(15deg);
        }

        40% {
            transform: rotate(-10deg);
        }

        50% {
            transform: rotate(5deg);
        }

        60%,
        100% {
            transform: rotate(0);
        }
    }

    @media (max-width: 576px) {
        .list-inline-item {
            display: block !important;
            width: 100% !important;
            margin-bottom: 10px;
            /* opcional para separar inputs */
        }

        .list-inline-item select,
        .list-inline-item .form-search input {
            width: 100% !important;
        }
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">PDT RENTA - ESPECIAL</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <div class="d-sm-flex align-items-center mb-3">

                        <ul class="list-inline me-auto my-1">
                            <li class="list-inline-item">
                                <select class="form-select" id="anios">
                                    <?php foreach ($anios as $key => $value) { ?>
                                        <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                                    <?php } ?>
                                </select>
                            </li>

                            <li class="list-inline-item">
                                <select class="form-select" name="filterTotales" id="filterTotales">
                                    <option value="1">MAYOR TOTALES</option>
                                    <option value="3">MAYOR COMPRAS</option>
                                    <option value="5">MAYOR VENTAS</option>
                                </select>
                            </li>

                            <li class="list-inline-item mt-2">
                                <select name="estado" id="estado" class="form-select">
                                    <option value="1">Activos</option>
                                    <option value="2">Inactivos</option>
                                </select>
                            </li>

                        </ul>

                        <ul class="list-inline ms-auto my-1 mt-2">
                            <li class="list-inline-item">
                                <div class="form-search">
                                    <i class="ti ti-search"></i>
                                    <input
                                        type="search"
                                        class="form-control"
                                        placeholder="Buscar Contribuyente" id="searchContribuyente" />
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="row g-3" id="listaEmpresasMontos">

                    </div>

                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
</div>

<div id="modalPeriodos" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg" id="tamModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleContribuyente"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rucEmpresa" />
                <div class="row">
                    <div class="col-md-12" id="viewTable">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Periodo</th>
                                        <th>Total Ventas</th>
                                        <th>Total Compras</th>
                                        <th>Archivo</th>
                                    </tr>
                                </thead>
                                <tbody id="list-periodos"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-12" id="viewFilePdf"></div>
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

<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>

<script src="<?= base_url() ?>js/declaraciones/pdtRentaTransacciones.js?v=3"></script>

<?= $this->endSection() ?>