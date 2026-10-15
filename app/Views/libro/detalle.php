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
                    <div class="page-header-title d-flex justify-content-between align-items-center">
                        <h3 class="mb-0" id="tituloLibro">Libro</h3>
                        <a href="<?= base_url("libros-electronicos") ?>" id="volverCliente" class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left"></i> Volver a los libros</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->
    <input type="hidden" id="libroId" value="<?= $libroId ?>">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0" id="clienteLibro"></h5>
                        <span class="badge" id="estadoLibro"></span>
                    </div>
                    <p class="text-muted mb-2" id="datosLibro"></p>
                    <p class="text-muted mb-2" id="foliosLibro"></p>
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar" id="barraLibro" role="progressbar" style="width: 0%"></div>
                    </div>
                    <p class="mb-1" id="resumenImpresas"></p>
                    <p class="mb-0" id="resumenSimulado"></p>

                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <?php if ($permiso_crear) { ?>
                            <button type="button" id="btnSiguiente" class="btn btn-outline-primary btn-sm">Legalizar siguiente</button>
                        <?php } ?>
                        <?php if ($permiso_editar) { ?>
                            <button type="button" id="btnReponer" class="btn btn-outline-success btn-sm"><i class="ti ti-plus"></i> Reponer hojas</button>
                        <?php } ?>
                        <?php if ($permiso_eliminar) { ?>
                            <button type="button" id="btnQuitar" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash"></i> Quitar libro</button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Historial de impresiones</h5>
                    <?php if ($permiso_editar) { ?>
                        <button type="button" id="btnImpresion" class="btn btn-success btn-sm"><i class="ti ti-plus"></i> Nueva impresión</button>
                    <?php } ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead>
                                <tr>
                                    <th>Folios</th>
                                    <th>Hojas</th>
                                    <th>Periodo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="bodyImpresiones"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Simulador</h5>
                </div>
                <div class="card-body">
                    <?php if ($permiso_editar) { ?>
                        <div class="row g-2 mb-3">
                            <div class="col-md-5">
                                <?= view('libro/_periodo', ['pid' => 'sim', 'vacio' => false]) ?>
                            </div>
                            <div class="col-md-4">
                                <input type="number" class="form-control" id="simHojas" min="1" placeholder="Hojas a imprimir">
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="btnSimular" class="btn btn-primary w-100">Agregar</button>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead>
                                <tr>
                                    <th>Periodo</th>
                                    <th>Hojas</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="bodySimulaciones"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>

<div id="modalImpresion" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4">Nueva impresión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formImpresion">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="folioDesde">Folio desde</label>
                            <input type="number" class="form-control" id="folioDesde" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="folioHasta">Folio hasta</label>
                            <input type="number" class="form-control" id="folioHasta" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periodo desde</label>
                            <?= view('libro/_periodo', ['pid' => 'i1', 'vacio' => true]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periodo hasta</label>
                            <?= view('libro/_periodo', ['pid' => 'i2', 'vacio' => true]) ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Registrar impresión</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('libro/_modal_libro', compact('contribuyentes', 'tipos', 'sedes', 'regimenes', 'tributos')) ?>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/libro/comun.js"></script>
<script src="<?= base_url() ?>js/libro/detalle.js"></script>

<?= $this->endSection() ?>
