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
                        <?php if ($permiso_crear) { ?>
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?= base_url('libro/plantilla') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="ti ti-download f-18"></i> Descargar plantilla</a>
                                <button type="button" id="btnImportar" class="btn btn-success d-inline-flex align-items-center gap-2"><i class="ti ti-upload f-18"></i> Importar libros</button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="text-muted mb-2" id="totalClientes"></div>
                    <div id="listaClientes"></div>
                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
</div>

<?php if ($permiso_crear) { ?>
<div id="modalImportar" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4">Importar libros desde Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Descargue la plantilla, llénela con sus clientes y libros (y el historial de impresiones) y súbala aquí. Primero se valida el archivo: cada fila sale <b>correcta</b>, <b>por completar</b> (importa con lo que tiene y anota qué falta) o con <b>error</b>. Después confirma la importación.</p>
                <div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
                    <input type="file" class="form-control" id="archivoImportar" accept=".xlsx" style="max-width: 420px;">
                    <button type="button" class="btn btn-primary" id="btnValidarArchivo">Validar archivo</button>
                </div>
                <div id="resumenImportar" class="mb-3" style="display: none;"></div>
                <div class="table-responsive" id="tablaImportar" style="display: none; max-height: 420px; overflow-y: auto;">
                    <table class="table table-sm align-middle">
                        <thead class="table-light" style="position: sticky; top: 0;">
                            <tr>
                                <th>Tipo</th>
                                <th>Fila</th>
                                <th>Cliente</th>
                                <th>Libro</th>
                                <th>Resultado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="bodyImportar"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" class="btn btn-outline-danger" id="btnDescargarErrores" style="display: none;"><i class="ti ti-download"></i> Descargar filas con error</a>
                <button type="button" class="btn btn-success" id="btnConfirmarImportar" style="display: none;"><i class="ti ti-check"></i> Importar filas válidas</button>
            </div>
        </div>
    </div>
</div>
<?php } ?>

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
