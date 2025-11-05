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
                        <h3 class="mb-0">Configuración Caja Virtual</h3>
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
                    <form id="formCajaVirtualSede">
                        <div>
                            <label class="form-label">Seleccione la sede para Caja Virtual</label>
                            <div class="row mb-2">
                                <?php foreach ($sedes as $key => $value) {
                                    $check = "";

                                    if ($value['caja_virtual'] == 1) {
                                        $check = "checked";
                                    }

                                ?>
                                    <div class="col-lg-6">
                                        <div class="border card p-3">
                                            <div class="form-check">
                                                <input type="radio" name="sede_id" class="form-check-input input-primary" id="sede-<?= $value['id'] ?>" value="<?= $value['id'] ?>" <?= $check ?> />
                                                <label class="form-check-label d-block" for="sede-<?= $value['id'] ?>">
                                                    <span class="h5 d-block"><?= $value['nombre_sede'] ?></span>
                                                    <span class="f-12 text-muted"><?= $value['direccion_sede'] ?></span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <?php if ($isEdit) { ?>
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-center">
                                        <button type="submit" class="btn btn-success">Guardar</button>
                                    </div>
                                </div>
                            <?php } ?>

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
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>js/configuracion/cajaVirtual.js"></script>

<?= $this->endSection() ?>