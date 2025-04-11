<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css">

<style>
    .toolbar {
        margin-bottom: 10px;
    }

    .toolbar button {
        background-color: white;
        border: none;
        border-radius: 3px;
        padding: 5px 10px;
        cursor: pointer;
    }

    .toolbar button:hover {
        background-color: #e8e8e8;
    }

    .char-counter {
        text-align: right;
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">Envío de Mensajes Masivos</h3>
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
                    <form id="formMessageMasivos">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <p>Ingresa el texto del mensaje.</p>

                                    <div class="toolbar">

                                        <!-- Icons or text can be added inside the buttons -->
                                        <button type="button" title="Insertar un emoji" id="btnEmojiTemplate"> <i class="ti ti-mood-empty"></i> </button>
                                        <button type="button" title="Negrita (Ctrl + b)" id="addNegrita"><b>B</b></button>
                                        <button type="button" title="Cursiva (Ctrl + i)" id="addCursiva"><i>I</i></button>
                                        <button type="button" title="Tachado" id="addTachado"><s>T</s></button>
                                        <button type="button" title="Add Variable" id="addVariable"> <i class="ti ti-plus"></i> Agregar
                                            variable</button>

                                    </div>

                                    <textarea name="message" id="editorTemplate" cols="30" rows="7"
                                        class="form-control" maxlength="1024" required=""></textarea>
                                    <div class="char-counter">Caracteres: 0 de 1024</div>

                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tipo de Contribuyente</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="contribuyenteType" id="contribuyenteType1" value="TODOS" checked>
                                            <label class="form-check-label" for="contribuyenteType1">
                                                Todos
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="contribuyenteType" id="contribuyenteType2" value="CONTABLE">
                                            <label class="form-check-label" for="contribuyenteType2">
                                                Contable
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="contribuyenteType" id="contribuyenteType3" value="ALQUILER">
                                            <label class="form-check-label" for="contribuyenteType3">
                                                Alquiler
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Titulo del Envio</label>
                                    <input type="text" class="form-control" name="titulo" id="titulo" placeholder="Agregue un nombre del envío" required>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="tableContribuyentes">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    <input type="checkbox" class="form-check-input" id="checkAll" checked>
                                                </th>
                                                <th>Razón Social</th>
                                            </tr>
                                        </thead>
                                        <tbody id="listContri">

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-md-3 mx-auto">
                                <div class="mb-3 text-center">
                                    <button type="submit" class="btn btn-success">Enviar</button>
                                </div>

                            </div>

                        </div>
                    </form>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <h5>Historial de Envíos</h5>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm" id="tableEnvios">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Titulo</th>
                                            <th>Contribuyente</th>
                                            <th>Contacto</th>
                                            <th>Whatsapp</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listEnvios">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- [ Main Content ] end -->
    </div>

    <?= $this->endSection() ?>

    <?= $this->section('js') ?>

    <script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
    <script src="<?= base_url() ?>js/mensajes/masivo.js"></script>

    <?= $this->endSection() ?>