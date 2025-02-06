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
                        <h3 class="mb-0">Lista de Movimientos por Bancos</h3>
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
                        <div class="col-md-3 mb-3">
                            <input type="month" class="form-control">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>FECHA</th>
                                    <th>CONCEPTO</th>
                                    <th>BCP</th>
                                    <th>BBVA</th>
                                    <th>INTERBANK</th>
                                    <th>SCOTIABANK</th>
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
<script src="<?= base_url() ?>js/movimiento/bancos.js"></script>

<?= $this->endSection() ?>