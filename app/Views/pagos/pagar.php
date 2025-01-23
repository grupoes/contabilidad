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
                                            Honorarios
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
                                            Pagos
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
                                                    <th>F. VENCE</th>
                                                    <th>F. PAGO</th>
                                                    <th>TOTAL</th>
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
                                                    <th>METODO PAGO</th>
                                                    <th>MONTO</th>
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