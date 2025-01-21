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
                        <h3 class="mb-0">Pago de Honorario <?= $datos['razon_social'] ?></h3>
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
                    <form id="formPago">
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
                                    <input type="text" class="form-control" id="monto" name="monto" value="<?= $monto_mensual ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="fechaPago">Fecha</label>
                                    <input type="date" class="form-control" id="fechaPago" name="fechaPago" value="<?= date('Y-m-d') ?>" min="<?= $fechaRestada ?>" max="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mx-auto">
                                <div class="mb-3 text-center">
                                    <button type="submit" class="btn btn-success">Guardar</button>
                                    <a href="<?= base_url('cobros') ?>" class="btn btn-danger">Cancelar</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table" id="tableData">
                            <thead>
                                <tr>
                                    <th>F. VENCE</th>
                                    <th>F. PAGO</th>
                                    <th>TOTAL</th>
                                    <th>F. PAGO</th>
                                    <th>M. PAGADO</th>
                                    <th>M. PENDIENTE</th>
                                    <th>M. EXCEDENTE</th>
                                    <th>ESTADO</th>
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
<script src="<?= base_url() ?>js/pagos/pagar.js"></script>

<?= $this->endSection() ?>