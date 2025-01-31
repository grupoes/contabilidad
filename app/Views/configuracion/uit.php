<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">Configuración de UIT</h3>
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
                    <form id="formUit">
                        <input type="hidden" name="id" id="id" value="<?= $monto_uit['id_uit'] ?>">
                        <div class="col-md-4 mx-auto">
                            <label class="form-label">Ingresar UIT</label>
                            <input type="text" name="uit" id="uit" class="form-control" value="<?= $monto_uit['uit_monto'] ?>" required>

                            <div class="row mt-3">
                                <div class="col-md-12 d-flex justify-content-center">
                                    <button type="submit" class="btn btn-success">Guardar</button>
                                </div>
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
<script src="<?= base_url() ?>js/configuracion/uit.js"></script>

<?= $this->endSection() ?>