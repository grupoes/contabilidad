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
                        <h3 class="mb-0">AFP</h3>
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
                            <label class="form-label" for="file_renta">Subir Renta</label>
                            <input type="file" class="form-control" name="file_renta" accept=".pdf" id="file_renta" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="file_constancia">Subir Constancia</label>
                            <input type="file" class="form-control" name="file_constancia" accept=".pdf" id="file_constancia" required>
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
                                    <th>Archivo pdt</th>
                                    <th>Archivo Constancia</th>
                                </tr>
                            </thead>
                            <tbody id="contentPdts"></tbody>
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
                            <label for="filePdt" class="form-label">Elige el nuevo pdt</label>
                            <input type="file" name="filePdt" id="filePdt" class="form-control" accept=".pdf" />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="fileConstancia" class="form-label">Elige la nueva constancia</label>
                            <input type="file" name="fileConstancia" id="fileConstancia" class="form-control" accept=".pdf" />
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
    <div class="modal-dialog modal-xl">
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
                                <th>Archivo Pdt</th>
                                <th>Archivo Constancia</th>
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

<div class="modal fade bd-example-modal-sm" id="modalIngresarMontos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="mySmallModalLabel">Agregar los Montos de Compras y Ventas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formMontosComprasVentas">
                <input type="hidden" name="idPdt" id="idPdt" />
                <div class="modal-body">
                    <h4>Hemos detectado que los montos de compras y ventas son 0.00 - <a href="#" class="text-danger" id="link_pdt" target="_blank"><i class="fas fa-file-pdf"></a></i> </h4>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="monto_compra" class="form-label">Ingrese el Monto de las Compras</label>
                            <input type="number" name="monto_compra" id="monto_compra" class="form-control" value="0.00" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="monto_venta" class="form-label">Ingrese el Monto de las ventas</label>
                            <input type="number" name="monto_venta" id="monto_venta" class="form-control" value="0.00" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnFormRectificacion">Guardar</button>
                </div>
            </form>
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

<script src="<?= base_url() ?>js/declaraciones/afp.js"></script>

<?= $this->endSection() ?>