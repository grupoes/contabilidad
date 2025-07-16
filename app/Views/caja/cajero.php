<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-2">Estado de la caja hoy</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ <span id="ingresos_caja_fisica"></span></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ <span id="egresos_caja_fisica"></span></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ <span id="utilidad_fisica"></span></span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja virtual</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ <span id="ingresos_caja_virtual"></span></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Virtual</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ <span id="egresos_caja_virtual"></span></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Virtual</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ <span id="utilidad_caja_virtual"></span></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-outline-secondary d-grid"><span class="text-truncate w-100">Utilidad Hoy</span></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-primary d-grid"><span class="text-truncate w-100">S/ <span id="utilidad_hoy"></span></span></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-2">Caja Grupo ESconsultores (Tarapoto)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span style="font-size: 18px">Estado de la caja Grupo ESconsultores</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ 14.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ 14.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ 14.00</span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja virtual</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ 14.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Virtual</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ 14.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Virtual</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ 14.00</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-outline-secondary d-grid"><span class="text-truncate w-100">Saldo en Caja</span></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-primary d-grid"><span class="text-truncate w-100">S/ 100.00</span></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>

<script src="<?= base_url() ?>js/caja/cajero.js?v=1"></script>

<?= $this->endSection() ?>