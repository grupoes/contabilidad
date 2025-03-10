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
                        <h3 class="mb-0">Permisos</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->
    <div class="row">

        <div class="col-sm-4" id="perfilesCard">
            <div class="card">
                <div class="card-body">

                    <h4 class="mb-3">Perfiles</h4>

                    <?php foreach ($perfiles as $perfil) { ?>

                        <div class="form-check mb-3">
                            <input class="form-check-input perfil-radio" type="radio" name="perfil" id="perf<?= $perfil['id'] ?>" value="<?= $perfil['id'] ?>">
                            <label class="form-check-label" for="perf<?= $perfil['id'] ?>"><?= $perfil['nombre_perfil'] ?></label>
                        </div>

                    <?php } ?>

                </div>
            </div>
        </div>

        <div class="col-sm-8 d-none d-sm-block" id="perfilInfo">
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-primary mb-3 d-sm-none" id="volverLista">← Volver</button>

                    <form id="formPermisos">
                        <input type="hidden" name="perfil_id" id="perfil_id" value="0">

                        <h5 id="titleProfile"></h5>

                        <ul class="list-unstyled mb-2" id="listPermisos">
                        </ul>

                        <button type="submit" class="btn btn-primary" id="btnGuardar" hidden>Guardar</button>

                    </form>

                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="https://raw.githack.com/SortableJS/Sortable/master/Sortable.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/permisos/lista.js"></script>

<?= $this->endSection() ?>