<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="pc-content">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-2">Caja Grupo ESconsultores - TARAPOTO</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span style="font-size: 18px">Estado de la caja física Grupo ESconsultores</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Saldo Inicial Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ 0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ <?= $ingresosFisicos ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ <?= $egresosFisicos ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ <?= number_format($utilidadFisica, 2) ?></span>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="row" id="detalleCajaDiaAll">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-2">Caja Grupo ESconsultores - Bancos</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span style="font-size: 18px">Estado de la caja Grupo ESconsultores</span>
                    </div>
                    <div class="row g-4">

                        <div class="col-md-12">
                            <ul class="list-group">
                                <!-- Saldo Inicial -->
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between pb-1 mb-1">
                                        <strong>SALDO INICIAL CAJA VIRTUAL</strong>
                                        <span class="badge bg-primary rounded-pill">S/ <?= $saldos['total'] ?></span>
                                    </div>

                                    <div class="border-top pt-1">
                                        <?php foreach ($saldos['bancos'] as $key => $value) { ?>
                                            <div class="d-flex justify-content-between border-bottom py-1">
                                                <span><?= $value['nombre_banco'] ?></span>
                                                <span>S/ <?= $value['saldo'] ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </li>

                                <!-- Ingreso Virtual -->
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between pb-1 mb-1">
                                        <strong>INGRESO VIRTUAL</strong>
                                        <span class="badge bg-primary rounded-pill">S/ <?= $ingresosBancos['total'] ?></span>
                                    </div>

                                    <div class="border-top pt-1">
                                        <?php foreach ($ingresosBancos['bancos'] as $key => $value) { ?>
                                            <div class="d-flex justify-content-between border-bottom py-1">
                                                <span><?= $value['nombre_banco'] ?></span>
                                                <span>S/ <?= $value['saldo'] ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </li>

                                <!-- Egreso Virtual -->
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between pb-1 mb-1">
                                        <strong>EGRESO VIRTUAL</strong>
                                        <span class="badge bg-danger rounded-pill">S/ <?= $egresosBancos['total'] ?></span>
                                    </div>

                                    <div class="border-top pt-1">
                                        <?php foreach ($egresosBancos['bancos'] as $key => $value) { ?>
                                            <div class="d-flex justify-content-between border-bottom py-1">
                                                <span><?= $value['nombre_banco'] ?></span>
                                                <span>S/ <?= $value['saldo'] ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </li>

                                <!-- Utilidad Virtual -->
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between pb-1 mb-1">
                                        <strong>UTILIDAD CAJA VIRTUAL</strong>
                                        <span class="badge bg-success rounded-pill">S/ <?= number_format($utilidadVirtual, 2) ?></span>
                                    </div>

                                    <div class="border-top pt-1">
                                        <?php foreach ($saldos['bancos'] as $key => $value) { ?>
                                            <div class="d-flex justify-content-between border-bottom py-1">
                                                <span><?= $value['nombre_banco'] ?></span>
                                                <span>S/ <?= number_format(floatval(str_replace(',', '', $value['saldo'])) + floatval(str_replace(',', '', $ingresosBancos['bancos'][$key]['saldo'])) - floatval(str_replace(',', '', $egresosBancos['bancos'][$key]['saldo'])), 2) ?></span>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </li>
                            </ul>


                        </div>

                    </div>
                </div>

                <!--<div class="card-footer">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-outline-secondary d-grid"><span class="text-truncate w-100">Saldo en Caja</span></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-primary d-grid"><span class="text-truncate w-100">S/ 0.00</span></button>
                            </div>
                        </div>
                    </div>
                </div>-->

            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>js/caja/index.js"></script>

<?= $this->endSection() ?>