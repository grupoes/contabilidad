<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/flatpickr.min.css">
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
                        <h3 class="mb-0">Lista de Movimientos</h3>
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
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch gap-2 p-2 pb-sm-2">
                        <!-- Contenedor para los botones -->
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" id="btnNuevoEgreso" class="btn btn-danger d-inline-flex align-items-center gap-2">
                                <i class="ti ti-minus f-18"></i> Egreso
                            </button>

                            <button type="button" id="btnNuevoIngreso" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i class="ti ti-plus f-18"></i> Ingreso
                            </button>
                        </div>

                        <!-- Contenedor para el select de fechas -->
                        <div class="d-flex align-items-center gap-2 flex-nowrap">
                            <button type="button" id="btnExportExcel" class="btn btn-outline-success d-inline-flex align-items-center gap-2">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>

                            <div class="input-group">
                                <input type="text" id="rango-fecha-movimientos" class="form-control" placeholder="Select date range">
                                <span class="input-group-text"><i class="feather icon-calendar"></i></span>
                            </div>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Caja</th>
                                    <th>Forma Pago</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
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

<div id="modalTipoMovimiento" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalMovimiento"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMovimiento" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="tipo_movimiento" id="tipo_movimiento" value="0">
                    <input type="hidden" name="idMovimiento" id="idMovimiento" value="0">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="conceptoCaja">Concepto de Caja</label>
                            <select name="conceptoCaja" id="conceptoCaja" class="form-select"></select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="metodoPago">Método de Pago</label>
                            <select name="metodoPago" id="metodoPago" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($metodos as $key => $value) { ?>
                                    <option value="<?= $value['id'] ?>"><?= $value['metodo'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label" for="descripcion">Descripción</label>
                            <input type="text" class="form-control" name="descripcion" id="descripcion" placeholder="Ingrese una descripción" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="monto">Monto</label>
                            <input type="number" class="form-control" placeholder="Ingrese el monto" name="monto" id="monto" required>
                        </div>
                        <div class="col-md-3 mb-3 ocultarMov d-none">
                            <label class="form-label" for="comprobante">Comprobante</label>
                            <select name="comprobante" id="comprobante" class="form-select">
                                <option value="">Seleccione...</option>
                                <?php foreach ($comprobantes as $key => $value) { ?>
                                    <option value="<?= $value['id_tipo_comprobante'] ?>"><?= $value['tipo_comprobante_descripcion'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3 ocultarMov">
                            <label class="form-label" for="serieMov">Serie</label>
                            <input type="text" class="form-control" placeholder="Ingrese la serie" name="serie" id="serieMov" />
                        </div>
                        <div class="col-md-3 mb-3 ocultarMov">
                            <label class="form-label" for="correlativo">Número</label>
                            <input type="text" class="form-control" placeholder="Ingrese el número" name="correlativo" id="correlativo" />
                        </div>

                        <div class="col-md-12 mb-3" id="fileVaucher">
                            <label for="vaucher" class="form-label">Ingrese el vaucher</label>
                            <input type="file" class="form-control" name="vaucher" id="vaucher" />
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

<div class="modal fade bd-example-modal-sm" id="modalChangePago" tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="mySmallModalLabel">Cambiar Método de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCambioPago">
                <div class="modal-body">
                    <input type="hidden" name="idmov" id="idmov" value="0">
                    <div class="row">
                        <div class="mb-3">
                            <div class="col-md-12">
                                <label for="nuevo_metodo_pago" class="form-label">Elige el Método de Pago</label>
                                <select name="nuevo_metodo_pago" id="nuevo_metodo_pago" class="form-select">

                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnFormPago">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-sm" id="movimientosVirtuales" tabindex="-1" role="dialog"
    aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="mySmallModalLabel">Movimientos Virtuales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCambioPago">
                <div class="modal-body">
                    <input type="hidden" name="idmov" id="idmov" value="0">
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableDataVirtual">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Forma Pago</th>
                                        <th>Monto</th>
                                        <th>Descripción</th>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBodyVirtual">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnFormPago">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/flatpickr.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/movimiento/cajero.js"></script>

<?= $this->endSection() ?>