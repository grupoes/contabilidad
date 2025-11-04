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
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="mb-0">Perfiles:</h4>
                        <button type="button" id="addButton" class="btn btn-sm btn-outline-success d-flex align-items-center gap-2">
                            <i class="ph-duotone ph-plus-circle"></i> Agregar
                        </button>
                    </div>

                    <div id="listProfiles">

                    </div>

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

<div id="modalProfile" class="modal fade" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-labelledby="exampleModalLiveLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titlePerfil">
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formProfile">
                <input type="hidden" name="perfil_id" id="idperfil" value="0">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="nombre_perfil" class="form-label">Nombre del perfil</label>
                                <input type="text" class="form-control" id="nombre_perfil" name="nombre_perfil" placeholder="Nombre del perfil">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" id="btnForm" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
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
<script src="<?= base_url() ?>js/permisos/lista.js?v=1"></script>

<?= $this->endSection() ?>