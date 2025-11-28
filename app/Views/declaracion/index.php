<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .bg-indigo-800 {
        background-color: #283593;
        border-color: #283593;
        color: #fff;
    }

    .bg-success-800 {
        background-color: #269726ff;
        border-color: #269726ff;
        color: #fff;
    }

    .bg-danger-800 {
        background-color: #C62828;
        border-color: #C62828;
        color: #fff;
    }

    .bg-orange-800 {
        background-color: #EF6C00;
        border-color: #EF6C00;
        color: #fff;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        background-color: white;
        /* Evita superposiciones visuales */
        z-index: 1020;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        /* Efecto sutil */
    }
</style>

<div class="pc-content">

    <!-- [ Main Content ] start -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <h4 class="text-center mb-3">DECLARACIONES TRIBUTARIAS</h4>

                    <p>En este modulo se configuracion de las notificaciones de cada uno de las declaraciones tributarias </p>

                    <div class="row">
                        <?php foreach ($declaraciones as $key => $value) { ?>
                            <div class="col-md-4" onclick="declaracion(<?= $value['id_declaracion'] ?>, '<?= $value['decl_nombre'] ?>')" style="margin-bottom: 20px;">
                                <div class="cursor-pointer card social-widget-card <?= $value['decl_color'] ?>">
                                    <div class="card-body" style="padding: 20px;">
                                        <h3 class="text-white m-0"><?= $value['decl_nombre'] ?></h3>
                                        <span class="m-t-10" style="font-size: 12px;"><?= $value['decl_descripcion'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12" id="calendario">

        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>

<div id="modalDeclaracion" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSelect">
                <input type="hidden" name="id_declaracion" id="id_declaracion" value="0">
                <div class="modal-body">
                    <h5 class="mb-3">Seleccionar cual de opciones desea configurar</h5>
                    <div id="alertMessage"></div>
                    <div id="listDeclaracion">


                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnForm">Configurar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>js/declaracion/lista.js?v=1"></script>

<?= $this->endSection() ?>