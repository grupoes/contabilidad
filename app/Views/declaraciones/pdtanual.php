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
                        <h3 class="mb-0">PDT ANUAL</h3>
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
                    <input type="hidden" name="idruc" id="idruc" value="0">
                    <div class="row" id="notingConfig" hidden>
                        <h5>No tiene configuraciones</h5>
                    </div>

                    <div class="row" id="widthConfig" hidden>
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
                            <label class="form-label" for="typePdt">Tipo Pdt</label>
                            <select name="typePdt" id="typePdt" class="form-select" required>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="pdt">Subir Pdt</label>
                            <input type="file" class="form-control" name="pdt" id="pdt" accept="application/pdf" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="constancia">Subir Constancia</label>
                            <input type="file" class="form-control" name="constancia" id="constancia" accept="application/pdf" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cargo" id="cargo" value="1" checked>
                                <label class="form-check-label" for="cargo">CARGO GRUPO ES CONSULTORES</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3" id="divMonto">
                            <label class="form-label" for="monto">Monto</label>
                            <input type="number" class="form-control" name="monto" id="monto">
                        </div>

                        <div class="col-md-12 mb-3" id="divDescripcion">
                            <label class="form-label" for="descripcion">Descripción Factura</label>
                            <input type="text" class="form-control" name="descripcion" id="descripcion">
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

            <div class="modal-body">
                <input type="hidden" name="numRuc" id="numRuc" value="0">
                <div class="row" id="noConfig" hidden>
                    <h5>No tiene configuraciones</h5>
                </div>
                <div class="row" id="opciones" hidden>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="anioDescarga">Año</label>
                        <select name="anio" id="anioDescarga" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($anios as $key => $value) { ?>
                                <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="tipoPdt">Tipo Pdt</label>
                        <select name="tipoPdt" id="tipoPdt" class="form-select" required>
                        </select>
                    </div>
                </div>

                <div class="row" id="tableFiles" hidden>
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>Año</td>
                                    <td>Tipo Pdt</td>
                                    <td>Archivos</td>
                                </tr>
                            </thead>
                            <tbody id="listFiles"></tbody>
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
                <h5 class="modal-title h4" id="titleModalMasivo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formConsulta" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="rucNum" id="rucNum" value="0">
                    <div class="row" id="noConfigMasivo" hidden>
                        <h5>No tiene configuraciones</h5>
                    </div>
                    <div class="row" id="consulting" hidden>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="desde">Desde</label>
                            <select name="desde" id="desde" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($anios as $key => $value) { ?>
                                    <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="hasta">hasta</label>
                            <select name="hasta" id="hasta" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($anios as $key => $value) { ?>
                                    <option value="<?= $value->id_anio ?>"><?= $value->anio_descripcion ?></option>
                                <?php } ?>
                            </select>

                        </div>

                        <div class="col-md-4 mb-3">
                            <button type="submit" class="btn btn-primary mt-4">Consultar</button>
                        </div>

                        <div class="col-md-12">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Año</th>
                                        <th>Pdt</th>
                                        <th>Constancia</th>
                                    </tr>
                                </thead>
                                <tbody id="list-files">

                                </tbody>
                            </table>
                        </div>

                    </div>

                    <hr>

                    <div class="row" id="btnsSend">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Escribe el correo electrónico" aria-label="Recipient's username" aria-describedby="button-addon2">
                                <button class="btn btn-outline-danger" type="button" id="button-addon2">Email</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Escribe el numero de whatsapp" aria-label="Recipient's username" aria-describedby="button-addon2">
                                <button class="btn btn-outline-success" type="button" id="button-addon2">Whatsapp</button>
                            </div>
                        </div>
                    </div>

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

<script src="<?= base_url() ?>js/declaraciones/pdtanual.js?v=1"></script>

<?= $this->endSection() ?>