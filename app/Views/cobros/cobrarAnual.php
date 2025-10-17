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
                        <h3 class="mb-0">Pago de Servidor - <?= $datos['razon_social'] ?></h3>
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
                        <div class="col-md-12 order-md-1 order-2">

                            <form id="formPago" enctype="multipart/form-data">
                                <input type="hidden" name="idcontribuyente" id="idcontribuyente" value="<?= $id ?>">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="metodoPago">Metodo de Pago</label>
                                            <select class="form-select" id="metodoPago" name="metodoPago" required="true">
                                                <option value="">Selecionar...</option>
                                                <?php foreach ($metodos as $metodo) : ?>
                                                    <option value="<?= $metodo['id'] ?>"><?= $metodo['metodo'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="monto">Monto</label>
                                            <input type="text" class="form-control" id="monto" name="monto" />
                                        </div>
                                    </div>

                                    <div class="col-md-4" id="proceso">
                                        <div class="mb-3">
                                            <label class="form-label" for="fecha_proceso">Fecha Pago</label>
                                            <input type="date" class="form-control" id="fecha_proceso" name="fecha_proceso" max="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>

                                    <div id="sedeEfectivo">

                                    </div>

                                    <div class="col-md-12" id="div-voucher" hidden>
                                        <div class="mb-3">
                                            <label for="voucher" class="form-label">Voucher</label>
                                            <input type="file" class="form-control" name="voucher" id="voucher" accept="image/*">
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-4 mx-auto">
                                        <div class="mb-3 text-center">
                                            <button type="submit" class="btn btn-success" id="btnSubmit">Guardar</button>
                                            <button type="button" id="linkTab" class="btn btn-danger">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-body border-bottom pb-0">

                                <ul
                                    class="nav nav-tabs analytics-tab"
                                    id="myTab"
                                    role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link active"
                                            id="analytics-tab-1"
                                            data-bs-toggle="tab"
                                            data-bs-target="#analytics-tab-1-pane"
                                            type="button"
                                            role="tab"
                                            aria-controls="analytics-tab-1-pane"
                                            aria-selected="true">
                                            Pagos Servidor
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link"
                                            id="analytics-tab-2"
                                            data-bs-toggle="tab"
                                            data-bs-target="#analytics-tab-2-pane"
                                            type="button"
                                            role="tab"
                                            aria-controls="analytics-tab-2-pane"
                                            aria-selected="false">
                                            Movimientos
                                        </button>
                                    </li>

                                </ul>
                            </div>
                            <div class="tab-content" id="myTabContent">
                                <div
                                    class="tab-pane fade show active"
                                    id="analytics-tab-1-pane"
                                    role="tabpanel"
                                    aria-labelledby="analytics-tab-1"
                                    tabindex="0">

                                    <div class="table-responsive mt-3">
                                        <table class="table" id="tableData">
                                            <thead>
                                                <tr>
                                                    <th>AÑO</th>
                                                    <th>F. PAGO</th>
                                                    <th>F. PROCESO</th>
                                                    <th>PDF</th>
                                                    <th>TOTAL</th>
                                                    <th>M. PAGADO</th>
                                                    <th>M. PENDIENTE</th>
                                                    <th>ESTADO</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tableBody">

                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                                <div
                                    class="tab-pane fade"
                                    id="analytics-tab-2-pane"
                                    role="tabpanel"
                                    aria-labelledby="analytics-tab-2"
                                    tabindex="0">

                                    <div class="table-responsive mt-3">
                                        <table class="table" id="tableData">
                                            <thead>
                                                <tr>
                                                    <th>REGISTRO</th>
                                                    <th>FECHA</th>
                                                    <th>PERIODO</th>
                                                    <th>METODO PAGO</th>
                                                    <th>MONTO</th>
                                                    <th>VOUCHER</th>
                                                    <th>ESTADO</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tablePagos">

                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
</div>

<div
    class="modal fade modal-lightbox"
    id="lightboxModal"
    tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
            <div class="modal-body p-0">
                <input type="hidden" id="pagoId">
                <div class="image-wrapper position-relative">
                    <img
                        src="../assets/images/light-box/l1.jpg"
                        alt="images"
                        class="modal-image img-fluid" />
                    <button
                        class="btn btn-sm btn-primary position-absolute top-0 end-0 m-2"
                        onclick="editarVoucher()"
                        title="Editar Vaucher">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a
                        id="btnDescargarVoucher"
                        class="btn btn-sm btn-success position-absolute top-0 end-0 m-2 me-5"
                        title="Descargar Voucher"
                        download>
                        <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditVoucher" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4">Editar Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditImage" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="idPago" id="idPago">
                    <div class="mb-3">
                        <label for="imagenVoucher" class="form-label">Imagen</label>
                        <input type="file" class="form-control" name="imagenVoucher" id="imagenVoucher" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4">Editar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditPago">
                <div class="modal-body">
                    <input type="hidden" name="id_Pago" id="id_Pago">
                    <input type="hidden" name="montoActual" id="montoActual">
                    <div class="mb-3">
                        <label class="form-label" for="metodo_pago">Metodo de Pago</label>
                        <select class="form-select" id="metodo_pago" name="metodo_pago" required="true">
                            <option value="">Selecionar...</option>
                            <?php foreach ($metodos as $metodo) : ?>
                                <option value="<?= $metodo['id'] ?>"><?= $metodo['metodo'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="idMonto">
                        <label class="form-label" for="monto_mov">Monto</label>
                        <input type="text" class="form-control" id="monto_mov" name="monto_mov" />
                    </div>
                    <div class="mb-3" id="idFechaPago">
                        <label class="form-label" for="datePago">Fecha Pago</label>
                        <input type="date" class="form-control" id="datePago" name="datePago" max="<?= date('Y-m-d') ?>" required />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
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
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/pagos/anuales.js?v=1"></script>

<?= $this->endSection() ?>