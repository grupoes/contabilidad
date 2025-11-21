<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

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
                        <h3 class="mb-0">SIRE</h3>
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
                <h5 class="modal-title h4" id="titleModalArchivo"></h5>
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
                            <label class="form-label" for="constancia_ventas">Constancia de Ventas</label>
                            <input type="file" class="form-control" name="constancia_ventas" accept=".pdf" id="constancia_ventas" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="constancia_compras">Constancia de Compras</label>
                            <input type="file" class="form-control" name="constancia_compras" accept=".pdf" id="constancia_compras" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="detalle_preliminar">Detalle Preliminar</label>
                            <input type="file" class="form-control" name="detalle_preliminar" accept=".pdf" id="detalle_preliminar">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="archivos">Archivos txt o zip</label>
                            <input type="file" class="form-control" name="archivos[]" accept=".txt,.zip" id="archivos" multiple required>
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
                <h5 class="modal-title h4" id="titleModalDownload"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <input type="hidden" id="rucEmpresa" value="0">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="periodo_file">Periodo</label>
                        <select name="periodo_file" id="periodo_file" class="form-select" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($meses as $key => $value) { ?>
                                <option value="<?= $value['id_mes'] ?>"><?= $value['mes_descripcion'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="anio_file">Año</label>
                        <select name="anio_file" id="anio_file" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($anios as $key => $value) { ?>
                                <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                            <?php } ?>
                        </select>

                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Periodo</th>
                                        <th>Año</th>
                                        <th>Archivos</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="loadFiles"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-12" id="archivos_sire">

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<div id="modalDescargarArchivoMasivo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalConsult"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="formConsulta">
                    <input type="hidden" name="empresa_ruc" id="empresa_ruc" value="0">
                    <input type="hidden" name="idcont" id="idcont" value="0">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="anio_consulta">Año</label>
                            <select name="anio_consulta" id="anio_consulta" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($anios as $key => $value) { ?>
                                    <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                                <?php } ?>
                            </select>

                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="desde">Desde</label>
                            <select name="desde" id="desde" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($meses as $key => $value) { ?>
                                    <option value="<?= $value['id_mes'] ?>"><?= $value['mes_descripcion'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="hasta">hasta</label>
                            <select name="hasta" id="hasta" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($meses as $key => $value) { ?>
                                    <option value="<?= $value['id_mes'] ?>"><?= $value['mes_descripcion'] ?></option>
                                <?php } ?>
                            </select>

                        </div>

                        <div class="col-md-3 mb-3">
                            <button type="submit" class="btn btn-primary mt-4">Consultar</button>
                        </div>

                    </div>
                </form>

                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Periodo</th>
                                    <th>Archivo Reporte</th>
                                    <th>Archivo ticket</th>
                                    <th>Archivo Plantilla</th>
                                </tr>
                            </thead>
                            <tbody id="contentAfps"></tbody>
                        </table>
                    </div>
                </div>

                <div class="row" id="envio_archivos" hidden>
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="correo" placeholder="Escribe el correo electrónico" aria-label="Recipient's username" aria-describedby="button-addon2">
                            <button class="btn btn-outline-danger" type="button" id="button-addon2">Email</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="whatsapp" maxlength="9" oninput="validarNumero(this)" placeholder="Escribe el numero de whatsapp">
                            <button class="btn btn-outline-success" type="button" id="sendWhatsapp" onclick="verificarInput()">Whatsapp</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-sm" id="modalRectificacion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleRectArchivos"></h5>
                <button type="button" class="btn-close modalDescargar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRectificacion" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="idpdtrenta" id="idpdtrenta" value="0" />
                    <input type="hidden" name="idarchivos" id="idarchivos" value="0" />
                    <input type="hidden" name="periodoRectificacion" id="periodoRectificacion" value="0" />
                    <input type="hidden" name="anioRectificacion" id="anioRectificacion" value="0" />
                    <input type="hidden" name="rucRect" id="rucRect" value="0" />
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="rectConstanciaVentas" class="form-label">Elige la nueva constancia de ventas</label>
                            <input type="file" name="rectConstanciaVentas" id="rectConstanciaVentas" class="form-control" accept=".pdf" required />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="rectConstanciaCompras" class="form-label">Elige la nueva constancia de Compras</label>
                            <input type="file" name="rectConstanciaCompras" id="rectConstanciaCompras" class="form-control" accept=".pdf" required />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="rectDetallePreliminar" class="form-label">Elige el nuevo detalle Preliminar</label>
                            <input type="file" name="rectDetallePreliminar" id="rectDetallePreliminar" class="form-control" accept=".pdf" />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="rectAjustePosterior" class="form-label">Elige el ajuste posterior</label>
                            <input type="file" name="rectAjustePosterior" id="rectAjustePosterior" class="form-control" accept=".pdf" required />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="rectArchivos" class="form-label">Archivos</label>
                            <input type="file" name="rectArchivos[]" id="rectArchivos" class="form-control" accept=".txt, .zip" multiple required />
                        </div>

                        <div class="col-md-12" id="viewAlert">

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary modalDescargar" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnFormRectificacion">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-sm" id="modalDetalle" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleDetallePdt"></h5>
                <button type="button" class="btn-close closeDetalle" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Constancia de Ventas</th>
                                <th>Constancia de Compras</th>
                                <th>Detalle Preliminar</th>
                                <th>Ajuste Posterior</th>
                            </tr>
                        </thead>
                        <tbody id="getFilesDetails">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary closeDetalle" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" id="btnFormRectificacion">Guardar</button>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>

<script src="<?= base_url() ?>js/declaraciones/sire.js"></script>

<?= $this->endSection() ?>