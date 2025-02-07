<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body border-bottom pb-0">

                    <ul
                        class="nav nav-tabs analytics-tab"
                        id="myTab"
                        role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="analytics-tab-1"
                                data-bs-toggle="tab"
                                data-bs-target="#analytics-tab-1-pane"
                                type="button"
                                role="tab"
                                aria-controls="analytics-tab-1-pane"
                                aria-selected="true">
                                ASIGNAR
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="analytics-tab-2"
                                data-bs-toggle="tab"
                                data-bs-target="#analytics-tab-2-pane"
                                type="button"
                                role="tab"
                                aria-controls="analytics-tab-2-pane"
                                aria-selected="false">
                                REASIGNAR
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="myTabContent">
                    <div
                        class="tab-pane fade show active"
                        id="analytics-tab-1-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-1"
                        tabindex="0">

                        <form id="formAsignar">
                            <div class="row pc-content mb-4">

                                <div class="col-md-12 mb-3">
                                    <div class="row">
                                        <div class="col-md-4 mx-auto">
                                            <label class="form-label" for="usuarios">Usuarios</label>
                                            <select name="usuarios" id="usuarios" class="form-select">
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($usuarios as $key => $value) { ?>
                                                    <option value="<?= $value['id'] ?>"><?= $value['nombres'] . " " . $value['apellidos'] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-5">
                                    <label>Disponibles</label>
                                    <input type="text" id="buscar1" class="form-control mb-2" placeholder="Buscar...">
                                    <select id="lista1" class="form-select" multiple size="10">

                                    </select>
                                </div>

                                <!-- Botones para mover -->
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                    <button type="button" class="btn btn-primary btn-sm mb-2" onclick="moverSeleccionados('lista1', 'lista2')" title="Asignar"> <i class="fas fa-long-arrow-alt-right"></i> </button>
                                    <button type="button" class="btn btn-success btn-sm mb-2" onclick="moverTodos('lista1', 'lista2')" title="Asignar Todos"> <i class="fas fa-arrow-alt-circle-right"></i> </button>
                                    <button type="button" class="btn btn-danger btn-sm mb-2" onclick="moverSeleccionados('lista2', 'lista1')" title="Regresar"> <i class="fas fa-long-arrow-alt-left"></i> </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="moverTodos('lista2', 'lista1')" title="Regresar todos"> <i class="fas fa-arrow-alt-circle-left"></i> </button>
                                </div>

                                <!-- Lista de seleccionados -->
                                <div class="col-md-5">
                                    <label>Seleccionados</label>
                                    <input type="text" id="buscar2" class="form-control mb-2" placeholder="Buscar...">
                                    <select id="lista2" name="seleccionados[]" class="form-select" multiple size="10">

                                    </select>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <div class="row">
                                        <div class="col-md-4 mx-auto text-center">
                                            <button type="submit" class="btn btn-success">Guardar</button>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </form>
                    </div>
                    <div
                        class="tab-pane fade"
                        id="analytics-tab-2-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-2"
                        tabindex="0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 01</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avtar avtar-s border text-danger">2</div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="row g-1">
                                            <div class="col-6">
                                                <h6 class="mb-0">Cliente 05</h6>
                                            </div>
                                            <div class="col-6 text-end">
                                                <h6 class="mb-1">$210,000</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>

<script src="<?= base_url() ?>js/auth/asignar.js"></script>

<?= $this->endSection() ?>