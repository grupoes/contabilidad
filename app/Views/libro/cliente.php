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
                    <div class="page-header-title d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Libros electrónicos</h3>
                        <a href="<?= base_url('libros-electronicos') ?>" class="btn btn-secondary btn-sm"><i class="ti ti-arrow-left"></i> Volver a clientes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->
    <input type="hidden" id="clienteId" value="<?= $contribuyente['id'] ?>">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="mb-0"><?= esc($contribuyente['razon_social']) ?></h5>
                            <span class="text-muted">RUC <?= esc($contribuyente['ruc']) ?> · <span class="text-capitalize"><?= esc($regimenCliente ?: 'Sin régimen') ?></span></span>
                        </div>
                        <?php if ($permiso_crear) { ?>
                            <button type="button" id="btnModal" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i class="ti ti-plus f-18"></i> Nuevo libro
                            </button>
                        <?php } ?>
                    </div>

                    <div id="listaLibros"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>

<?= view('libro/_modal_libro', compact('contribuyentes', 'tipos', 'sedes', 'regimenes', 'tributos', 'notarias', 'stans')) ?>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script>
    const PERMISO_VER = <?= $permiso_ver ? 'true' : 'false' ?>;
    const PERMISO_EDITAR = <?= $permiso_editar ? 'true' : 'false' ?>;
    const PERMISO_ELIMINAR = <?= $permiso_eliminar ? 'true' : 'false' ?>;
</script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/libro/comun.js"></script>
<script src="<?= base_url() ?>js/libro/cliente.js"></script>

<?= $this->endSection() ?>
