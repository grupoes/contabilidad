<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <div class="row" id="detalleCajaDia">

    </div>

    <div class="row" id="detalleCajaDiaAll">

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>js/caja/index.js"></script>

<?= $this->endSection() ?>