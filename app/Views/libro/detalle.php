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
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h5 class="mb-2" id="clienteLibro"></h5>
                            <div class="d-flex flex-wrap align-items-center gap-2 text-muted">
                                <span id="rucLibro"></span>
                                <span class="badge bg-light-primary text-primary" id="regimenLibro"></span>
                            </div>
                        </div>
                        <span class="badge fs-6" id="estadoLibro"></span>
                    </div>

                    <hr class="my-4">

                    <p class="small text-muted mb-3">Datos del libro</p>
                    <div class="row g-4" id="datosLibro"></div>

                    <div id="seccionHojas">
                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <p class="small text-muted mb-0">Hojas</p>
                        <p class="small text-muted mb-0" id="usoHojas"></p>
                    </div>
                    <div class="progress mb-3" style="height: 8px; background-color: #e9ecef;">
                        <div class="progress-bar" id="barraLibro" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="row g-3" id="tarjetasHojas"></div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <p class="small text-muted mb-0" id="auditoriaLibro"></p>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if ($permiso_crear) { ?>
                                <button type="button" id="btnSiguiente" class="btn btn-outline-primary btn-sm"><i class="ti ti-copy-plus"></i> Legalizar siguiente</button>
                            <?php } ?>
                            <?php if ($permiso_eliminar) { ?>
                                <button type="button" id="btnQuitar" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash"></i> Quitar libro</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12" id="avisoElectronico" style="display: none;">
            <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
                <i class="ti ti-info-circle fs-4"></i>
                <div>Este es un libro <b>electrónico</b>: no usa hojas, por lo que no registra impresiones ni simulaciones.</div>
            </div>
        </div>

        <div class="col-sm-12" id="avisoIncompleto" style="display: none;">
            <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
                <i class="ti ti-info-circle fs-4"></i>
                <div class="flex-grow-1">Este libro está <b>por completar</b>. Faltan: <b id="textoFaltantes"></b>. Mientras tanto no se puede registrar impresiones ni simular.</div>
                <?php if ($permiso_editar) { ?>
                    <button type="button" id="btnCompletar" class="btn btn-info btn-sm"><i class="ti ti-edit"></i> Completar datos</button>
                <?php } ?>
            </div>
        </div>

        <div class="col-sm-12" id="avisoBloqueo" style="display: none;">
            <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                <i class="ti ti-alert-triangle fs-4"></i>
                <div id="textoBloqueo"></div>
            </div>
        </div>

        <div class="col-md-6" id="colImpresiones">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Historial de impresiones</h5>
                    <?php if ($permiso_impresion) { ?>
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
                                    <th>Observación</th>
                                    <th>Registrado por</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="bodyImpresiones"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6" id="colSimulador">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Simulador</h5>
                </div>
                <div class="card-body">
                    <?php if ($permiso_simular) { ?>
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
                            <div class="col-12 text-danger small" id="avisoPeriodoSimulacion" style="display: none;"></div>
                        </div>
                    <?php } ?>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead>
                                <tr>
                                    <th>Periodo</th>
                                    <th>Hojas</th>
                                    <th>Registrado por</th>
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
                        <div class="col-12 text-danger small mb-2" id="avisoPeriodoImpresion" style="display: none;"></div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="observacionImpresion">Observación</label>
                            <textarea class="form-control" id="observacionImpresion" rows="2" maxlength="255" placeholder="Opcional"></textarea>
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

<?= view('libro/_modal_libro', compact('contribuyentes', 'tipos', 'sedes', 'regimenes', 'tributos', 'notarias', 'stans')) ?>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/libro/comun.js"></script>
<script src="<?= base_url() ?>js/libro/detalle.js"></script>

<?= $this->endSection() ?>
