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
                        <h3 class="mb-0">Lista de Números de Whatsapp</h3>
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

                            <button type="button" id="btnModal" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i class="ti ti-plus f-18"></i> Nuevo Número de Whatsapp
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Número</th>
                                    <th>Nombre</th>
                                    <th>Api</th>
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

<div id="modalNumeroWhatasapp" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNumeroWhatsapp">
                <div class="modal-body" id="contentRenta">
                    <input type="hidden" name="idNumero" id="idNumero">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label" for="numero_whatsapp">Numero Whatsapp</label>
                                <input type="text" minlength="9" maxlength="9" class="form-control" id="numero_whatsapp" name="numero_whatsapp" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label" for="nombre_whatsapp">Nombre</label>
                                <input type="text" class="form-control" id="nombre_whatsapp" name="nombre_whatsapp" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label" for="link">Link Api Envio</label>
                                <input type="text" class="form-control" id="link" name="link" required>
                            </div>
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

<script src="<?= base_url() ?>js/configuracion/numeroWhatsapp.js"></script>

<?= $this->endSection() ?>