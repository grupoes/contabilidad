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
                        <h3 class="mb-0">Inventario de libros</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-sm-12" id="cardAlertas" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-alert-triangle text-warning"></i> Alertas</h5>
                </div>
                <div class="card-body" id="listaAlertas"></div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center pb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <input type="search" id="buscarCliente" class="form-control" placeholder="Buscar cliente por nombre o RUC..." style="max-width: 420px;">
                            <select id="filtroContribuyente" class="form-select" style="max-width: 170px;" title="Estado del contribuyente">
                                <option value="0">Todos</option>
                                <option value="1" selected>Activos</option>
                                <option value="2">Inactivos</option>
                            </select>
                            <select id="filtroEstado" class="form-select" style="max-width: 200px;">
                                <option value="">Todos los estados</option>
                                <option>OK</option>
                                <option>Stock bajo</option>
                                <option>Agotado</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-muted mb-2" id="totalClientes"></div>
                    <div id="listaClientes"></div>
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
</script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/libro/comun.js"></script>
<script src="<?= base_url() ?>js/libro/lista.js"></script>

<?= $this->endSection() ?>
