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
                        <h3 class="mb-0">Nuevo Servicio</h3>
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
                    <form id="formService">
                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="estado">Estado</label>
                                <select class="form-select" name="estado" id="estado" required>
                                    <option value="">Seleccione...</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="pagado">Pagado</option>
                                </select>

                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="numeroDocumento">N° R.U.C.</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="numeroDocumento" id="numeroDocumento" placeholder="" aria-describedby="searchDocumento" required="">
                                    <button class="btn btn-outline-primary" type="button" id="searchDocumento">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="razon_social">Razón Social</label>
                                <input type="text" class="form-control" name="razon_social" id="razon_social" required="">
                                <small id="razon_social" class="form-text text-success"></small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="metodo_pago">Métodos de Pago</label>
                                <select class="form-select" name="metodo_pago" id="metodo_pago">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($metodos as $metodo) : ?>
                                        <option value="<?= $metodo['id'] ?>"><?= $metodo['metodo'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="monto">Monto</label>
                                <input type="number" class="form-control" name="monto" id="monto" required="">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="comprobante">Comprobante</label>
                                <select class="form-select" name="comprobante" id="comprobante">
                                    <option value="">Nota de Venta</option>
                                    <option value="">Factura Electrónica</option>
                                    <option value="">Boleta de Venta Electrónica</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="description_service">Descripción del Servicio</label>
                                <input type="text" class="form-control" name="description_service" id="description_service" required="">
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4 mt-3 mx-auto text-center">
                                <a href="<?= base_url('servicio') ?>" class="btn btn-danger">Regresar</a>
                                <button type="submit" class="btn btn-success">Guardar</button>
                            </div>
                        </div>

                    </form>


                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
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
<script src="<?= base_url() ?>js/cobros/crearServicio.js?v=1"></script>

<?= $this->endSection() ?>