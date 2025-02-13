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
                        <h3 class="mb-0">BOLETA DE PAGO</h3>
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

                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>RUC</th>
                                    <th>RAZON SOCIAL</th>
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

<div id="modalArchivo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalArchivo">Subir Archivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formArchivo" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="rucEmp" id="rucEmp" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="periodo">Periodo</label>
                            <select name="periodo" id="periodo" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($meses as $key => $value) { ?>
                                    <option value="<?= $value['id_mes'] ?>"><?= $value['mes_descripcion'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="anio">Año</label>
                            <select name="anio" id="anio" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($anios as $key => $value) { ?>
                                    <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                                <?php } ?>
                            </select>

                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="file_pdt">Subir PDT</label>
                            <input type="file" class="form-control" name="file_pdt[]" id="file_pdt" multiple required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnForm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalDescargarArchivo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalArchivo">Descargar Archivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formArchivo" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="idTableTarifa" id="idTableTarifa" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="periodo">Periodo</label>
                            <select name="periodo" id="periodo" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($meses as $key => $value) { ?>
                                    <option value="<?= $value['id_mes'] ?>"><?= $value['mes_descripcion'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="anio">Año</label>
                            <select name="anio" id="anio" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($anios as $key => $value) { ?>
                                    <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                                <?php } ?>
                            </select>

                        </div>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
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

<script src="<?= base_url() ?>js/declaraciones/boletaPago.js"></script>

<?= $this->endSection() ?>