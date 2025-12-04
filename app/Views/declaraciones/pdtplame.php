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
                        <h3 class="mb-0">PDT PLAME</h3>
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
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-1">
                                <select name="estado" id="estado" class="form-control">
                                    <option value="1">Activos</option>
                                    <option value="2">Inactivos</option>
                                </select>
                            </div>
                        </div>
                    </div>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalArchivo">Subir Archivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formArchivo" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="idTabla" id="idTabla" value="0">
                    <input type="hidden" name="ruc_empresa" id="ruc_empresa_save" value="0">
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_r01">Subir R01 (excel o pdf)</label>
                            <input type="file" class="form-control" name="file_r01" id="file_r01" accept=".xlsx, .xls, .pdf">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_r12">Subir R12 (excel o pdf)</label>
                            <input type="file" class="form-control" name="file_r12" id="file_r12" accept=".xlsx, .xls, .pdf">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_constancia">Subir Constancia (word o pdf)</label>
                            <input type="file" class="form-control" accept="doc,.docx, .pdf" name="file_constancia" id="file_constancia">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_r08">Subir R08 (txt, pdf)</label>
                            <input type="file" class="form-control" accept="txt,.pdf" name="file_r08[]" id="file_r08" multiple>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalDescargar">Descargar Archivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formArchivo" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="ruc_emp" id="ruc_emp" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="periodoDescarga">Periodo</label>
                            <select name="periodoDescarga" id="periodoDescarga" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($meses as $key => $value) { ?>
                                    <option value="<?= $value['id_mes'] ?>"><?= $value['mes_descripcion'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="anioDescarga">Año</label>
                            <select name="anioDescarga" id="anioDescarga" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($anios as $key => $value) { ?>
                                    <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                                <?php } ?>
                            </select>

                        </div>
                        <div class="col-md-12 mb-3">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Periodo</th>
                                        <th>Año</th>
                                        <th>Archivos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="contentPdt"></tbody>
                            </table>
                        </div>

                        <div class="col-md-12" id="r08view">

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

<div id="modalRectificacion" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalRectificacion"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRectificacion" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="ruc" id="rucEmpresa" value="0">
                    <input type="hidden" name="idplame" id="idplame" value="0">
                    <input type="hidden" name="idPlameFiles" id="idPlameFiles" value="0">
                    <input type="hidden" name="periodo" id="periodo_rect" value="0">
                    <input type="hidden" name="anio" id="anio_rect" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_r01">Subir R01 (excel o pdf)</label>
                            <input type="file" class="form-control" name="file_r01" id="file_r01_rect" accept=".xlsx, .xls, .pdf" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_r12">Subir R12 (excel o pdf)</label>
                            <input type="file" class="form-control" name="file_r12" id="file_r12_rect" accept=".xlsx, .xls, .pdf" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_constancia">Subir Constancia (word o pdf)</label>
                            <input type="file" class="form-control" accept="doc,.docx, .pdf" name="file_constancia" id="file_constancia_rect" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_r08">Subir R08 (txt, pdf)</label>
                            <input type="file" class="form-control" accept="txt,.pdf" name="file_r08[]" id="file_r08_rect" multiple />
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnRect">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalRectR08" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalRectificacion">Rectificar R08</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRectR08" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="idR08" id="idR08" value="0">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="file_r01">Subir archivo R08</label>
                            <input type="file" class="form-control" name="file_r08" id="file_r08_rect" accept=".pdf" />
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnRect">Guardar</button>
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

<script src="<?= base_url() ?>js/declaraciones/pdtplame.js?v=5"></script>

<?= $this->endSection() ?>