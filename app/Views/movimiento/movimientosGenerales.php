<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/buttons.bootstrap5.min.css" />

<style>
    /* En tu archivo CSS */
    table#tableData th.columna-concepto,
    table#tableData td.columna-concepto {
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
        white-space: normal;
        /* Permite que el texto se divida en varias líneas */
        word-wrap: break-word;
        /* Divide palabras largas si es necesario */
        overflow-wrap: break-word;
    }

    table#tableData th.columna-description,
    table#tableData td.columna-description {
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
        white-space: normal;
        /* Permite que el texto se divida en varias líneas */
        word-wrap: break-word;
        /* Divide palabras largas si es necesario */
        overflow-wrap: break-word;
    }

    table#tableData th.columna-metodo,
    table#tableData td.columna-metodo {
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">Lista de Movimientos Generales</h3>
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

                    <form id="formConsulta">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="desde" class="form-label">Desde</label>
                                <input type="date" class="form-control" id="desde" name="desde">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="hasta" class="form-label">Hasta</label>
                                <input type="date" class="form-control" id="hasta" name="hasta">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="filtro" class="form-label">Seleccione Entidad</label>
                                <select name="filtro" id="filtro" class="form-select">
                                    <option value="todos">TODOS</option>
                                    <option value="efectivo">EFECTIVO</option>
                                    <?php foreach ($bancos as $key => $value) { ?>
                                        <option value="<?= $value['id'] ?>"><?= $value['nombre_banco'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <button type="submit" class="btn btn-primary mt-4">Consultar</button>
                            </div>
                        </div>

                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle datatable table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>FECHA PROCESO</th>
                                    <th>FECHA PAGO</th>
                                    <th>TIPO</th>
                                    <th>CONCEPTO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>METODO DE PAGO</th>
                                    <th>EFECTIVO</th>
                                    <?php foreach ($bancos as $key => $value) { ?>
                                        <th><?= $value['nombre_banco'] ?></th>
                                    <?php } ?>
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

<script src="<?= base_url() ?>js/movimiento/movimientosGenerales.js"></script>

<?= $this->endSection() ?>