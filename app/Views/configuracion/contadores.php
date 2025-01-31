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
                        <h3 class="mb-0">Lista de Contadores</h3>
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
                    <div class="d-flex justify-content-between align-items-center p-2 pb-sm-2">
                        <!-- Contenedor para los botones -->
                        <div class="d-flex align-items-center gap-2 ms-auto">

                            <button type="button" id="btnModal" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i class="ti ti-plus f-18"></i> Nuevo Contador
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NOMBRE Y APELLIDOS</th>
                                    <th>DNI</th>
                                    <th>N° DE COLEGIATURA</th>
                                    <th>ELEGIR</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
</div>

<div id="modalContadores" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTarifa">
                    <input type="hidden" name="idTableTarifa" id="idTableTarifa" value="0">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <label class="form-label" for="numeroDocumento">D.N.I.</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="numeroDocumento" id="numeroDocumento" placeholder="" aria-describedby="searchDocumento" required="">
                                    <button class="btn btn-outline-primary" type="button" id="searchDocumento">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label" for="nombresApellidos">Nombres y Apellidos</label>
                            <input type="text" class="form-control" name="nombresApellidos" id="nombresApellidos" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="num_colegiatura">Número de Colegiatura</label>
                            <input type="number" class="form-control" name="num_colegiatura" id="num_colegiatura" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="domicilio">Domicilio</label>
                            <input type="number" class="form-control" name="domicilio" id="domicilio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="ubigeo">Ubigeo</label>
                            <select name="ubigeo" id="ubigeo" class="form-select"></select>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" id="btnForm">Guardar</button>
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

<script src="<?= base_url() ?>js/configuracion/contadores.js"></script>

<?= $this->endSection() ?>