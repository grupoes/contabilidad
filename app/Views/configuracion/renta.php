<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css" >
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css" >

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">Lista de Renta Anuales</h3>
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

                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre de Tributo</th>
                                    <th>Código</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php foreach ($rentas as $key => $renta) : ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td><?= $renta['tri_descripcion'] ?></td>
                                    <td><?= $renta['tri_codigo'] ?></td>
                                    <td class="text-center">
                                        <a href="#"> <i class="fas fa-pencil-alt"></i> </a>
                                    </td>
                                </tr>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
</div>

<div id="modalTipoServicio" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalTarifa"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTarifa">
                    <input type="hidden" name="idTableTarifa" id="idTableTarifa" value="0">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="fechaInicioTarifa">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fechaInicioTarifa" id="fechaInicioTarifa" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="montoMensualTarifa">Monto Mensual</label>
                            <input type="number" class="form-control" name="montoMensualTarifa" id="montoMensualTarifa" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="montoAnualTarifa">Monto Anual</label>
                            <input type="number" class="form-control" name="montoAnualTarifa" id="montoAnualTarifa" required>
                        </div>
                        <div class="col-md-3 mb-3 mt-4">
                            <button type="submit" class="btn btn-success">Agregar Tarifa</button>
                        </div>
                    </div>
                </form>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Monto Mensual</th>
                            <th>Monto Anual</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableTarifa">
                        
                    </tbody>
                </table>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
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

<script src="<?= base_url() ?>js/configuracion/renta.js"></script>

<?= $this->endSection() ?>