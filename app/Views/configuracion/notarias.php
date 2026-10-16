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
                        <h3 class="mb-0">Notarias</h3>
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
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <?php if ($isCrear) { ?>
                                <button type="button" id="btnModal" class="btn btn-success d-inline-flex align-items-center gap-2">
                                    <i class="ti ti-plus f-18"></i> Nueva Notaría
                                </button>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>RUC</th>
                                    <th>RAZÓN SOCIAL</th>
                                    <th>ESTADO</th>
                                    <th class="text-center">Acción</th>
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

<div id="modalNotaria" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNotaria">
                <div class="modal-body">
                    <input type="hidden" name="idNotaria" id="idNotaria" value="0">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="ruc">RUC <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="ruc" id="ruc" maxlength="11" inputmode="numeric" pattern="\d{11}" title="11 dígitos" aria-describedby="searchRuc" required>
                                <button class="btn btn-outline-primary" type="button" id="searchRuc" title="Buscar RUC">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <small id="getRazonSocial" class="form-text text-success"></small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="razonSocial">Razón social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="razonSocial" id="razonSocial" maxlength="200" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="estado">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="1">Activo</option>
                                <option value="2">Inactivo</option>
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

<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>

<script src="<?= base_url() ?>js/configuracion/notarias.js"></script>

<?= $this->endSection() ?>
