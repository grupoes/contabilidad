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
                        <h3 class="mb-0">Lista de Bancos</h3>
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
                    <div class="d-flex justify-content-between align-items-center p-2 pb-sm-2">


                        <!-- Contenedor para los botones -->
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <?php if ($crear) { ?>
                                <button type="button" id="btnModal" class="btn btn-success d-inline-flex align-items-center gap-2">
                                    <i class="ti ti-plus f-18"></i> Nuevo Concepto
                                </button>
                            <?php } ?>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre del Banco</th>
                                    <th>Moneda</th>
                                    <th>Nombre del Titular</th>
                                    <th>Numero de Cuenta</th>
                                    <th>Saldo Inicial</th>
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

<div id="modalBancos" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBanco">
                <div class="modal-body">
                    <input type="hidden" name="idBanco" id="idBanco" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="nameBanco">Nombre del Banco</label>
                            <input type="text" class="form-control" name="nameBanco" id="nameBanco" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="titular">Nombre del Titular</label>
                            <input type="text" class="form-control" name="titular" id="titular" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="numeroCuenta">Número de Cuenta</label>
                            <input type="text" class="form-control" name="numeroCuenta" id="numeroCuenta">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="moneda">Moneda</label>
                            <select name="moneda" id="moneda" class="form-select">
                                <option value="SOLES">SOLES</option>
                                <option value="DOLARES">DOLARES</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="saldo_inicial">Saldo Inicial</label>
                            <input type="number" class="form-control" id="saldo_inicial" name="saldo_inicial" value="0.00" step="0.01">
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

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>js/banco/lista.js"></script>

<?= $this->endSection() ?>