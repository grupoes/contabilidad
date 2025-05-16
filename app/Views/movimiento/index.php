<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/buttons.bootstrap5.min.css" />

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
                    <form id="formMov">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="sede" class="form-label">Sede</label>
                                    <select name="sede" id="sede" class="form-select">
                                        <option value="0">TODOS</option>
                                        <?php foreach ($sedes as $key => $value) { ?>
                                            <option value="<?= $value['id'] ?>"><?= $value['nombre_sede'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="desde" class="form-label">Desde</label>
                                    <input type="date" class="form-control" name="desde" id="desde">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="hasta" class="form-label">Hasta</label>
                                    <input type="date" class="form-control" name="hasta" id="hasta">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary mb-3 mt-4 w-50" id="btnForm">Buscar</button>
                            </div>
                        </div>

                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Metodo</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Descripción</th>
                                    <th>Sede</th>
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

<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.buttons.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/jszip.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/buttons.html5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/buttons.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>js/movimiento/admin.js"></script>

<?= $this->endSection() ?>