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
                    <?php if ($isEdit) { ?>
                    <form id="formUit">
                        <input type="hidden" name="id" id="id" value="">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Año</label>
                                <input type="number" name="anio" id="anio" class="form-control" value="<?= $anioActual ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Monto UIT</label>
                                <input type="text" name="uit" id="uit" class="form-control" required>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success">Guardar</button>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <?php } ?>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th>Monto UIT</th>
                                <?php if ($isEdit) { ?>
                                <th>Acciones</th>
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
    <!-- [ Main Content ] end -->
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script>
    const isEditUit = <?= $isEdit ? 'true' : 'false' ?>;
</script>
<script src="<?= base_url() ?>js/configuracion/uit.js"></script>

<?= $this->endSection() ?>