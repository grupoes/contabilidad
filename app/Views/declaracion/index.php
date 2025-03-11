<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .bg-indigo-800 {
        background-color: #283593;
        border-color: #283593;
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
                            <div class="col-md-4">
                                <div class="card social-widget-card <?= $value['decl_color'] ?>">
                                    <div class="card-body">
                                        <h3 class="text-white m-0"><?= $value['decl_nombre'] ?></h3>
                                        <span class="m-t-10"><?= $value['decl_descripcion'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>


                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>js/declaracion/lista.js"></script>

<?= $this->endSection() ?>