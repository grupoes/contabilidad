<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>public/assets/css/plugins/notifier.css" >
<link rel="stylesheet" href="<?= base_url() ?>public/assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>public/assets/css/plugins/responsive.bootstrap5.min.css">

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">Lista de Usuarios</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->
    <div class="row">

        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="text-end p-2 pb-sm-2">
                        <a href="#" class="btn btn-primary d-inline-flex align-items-center gap-2" id="btnModal">
                            <i class="ti ti-plus f-18"></i> Nuevo Usuario
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th style="width: 250px;">Nombres</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Dirección</th>
                                    <th>Perfil</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="contentBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- [ Main Content ] end -->
</div>

<div id="modalAddEdit" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">Agregar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDatos" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="iduser" id="iduser" value="0">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="tipoDocumento">Tipo Documento</label>
                            <select class="form-select" name="tipoDocumento" id="tipoDocumento">
                                <option value="1">D.N.I.</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="numeroDocumento">N° Documento</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="numeroDocumento" id="numeroDocumento" placeholder="" aria-describedby="searchDocumento" required>
                                <button class="btn btn-outline-primary" type="button" id="searchDocumento">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="nombres">Nombres</label>
                            <input type="text" class="form-control" name="nombres" id="nombres" required>
                            <small id="getNombres" class="form-text text-success"></small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="apellidos">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" id="apellidos" required>
                            <small id="getApellidos" class="form-text text-success"></small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="fechaNacimiento">Fecha Nacimiento</label>
                            <input type="date" class="form-control" name="fechaNacimiento" id="fechaNacimiento" required>
                            <small id="getHappy" class="form-text text-success"></small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="direccion">Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="direccion">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="celular">Celular</label>
                            <input type="text" class="form-control" name="celular" id="celular">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="numeroCuenta">Numero de Cuenta</label>
                            <input type="text" class="form-control" name="numeroCuenta" id="numeroCuenta">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="sede">Sede</label>
                            <select class="form-select" name="sede" id="sede" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($sedes as $key => $value) { ?>
                                <option value="<?= $value['id'] ?>"><?= $value['nombre_sede'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="perfil">Perfil</label>
                            <select class="form-select" name="perfil" id="perfil" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($profiles as $key => $value) { ?>
                                <option value="<?= $value['id'] ?>"><?= $value['nombre_perfil'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="correo">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" id="correo" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="password">Contraseña</label>
                            <input type="text" class="form-control" name="password" id="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="foto">Foto</label>
                            <input type="file" class="form-control" name="foto" id="foto" accept="image/*">
                            
                        </div>
                        <div class="col-md-6">
                            <div class="mt-3">
                                <img id="preview" src="<?= base_url('public/assets/images/user/avatar-2.jpg')?>" alt="Vista previa" style="width: 200px; height: 200px; object-fit: cover;" />
                            </div>       
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnForm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>public/assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>public/assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>public/assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>public/assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>public/assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>public/assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>public/js/auth/listaUsuario.js"></script>

<?= $this->endSection() ?>