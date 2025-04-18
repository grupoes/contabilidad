<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css" />

<style>
    #pickerContainer {
        display: none;
        position: absolute;
        z-index: 9999;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        background: white;
        border-radius: 8px;
    }
</style>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1.26.3/index.js"></script>



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

    .toolbar i {
        background-color: white;
        border: none;
        border-radius: 3px;
        padding: 5px 10px;
        cursor: pointer;
    }

    .toolbar i:hover {
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
                                        <i id="btnEmojiTemplate" class="ti ti-mood-empty" title="emoji"></i>
                                        <button type="button" title="Negrita (Ctrl + b)" id="addNegrita" onclick="wrapWithAsterisks()"><b>B</b></button>
                                        <button type="button" title="Cursiva (Ctrl + i)" id="addCursiva" onclick="wrapWithCursive()"><em>I</em></button>
                                        <button type="button" title="Tachado" id="addTachado" onclick="wrapWithCross()"><s>T</s></button>
                                        <button type="button" title="Add Variable" id="addVariable"> + Agregar variable</button>

                                    </div>

                                    <textarea name="message" id="editorTemplate" cols="30" rows="7"
                                        class="form-control" maxlength="1024" required=""></textarea>

                                    <div id="pickerContainer">
                                        <emoji-picker locale="es"></emoji-picker>
                                    </div>

                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Programar envío</label>
                                    <div class="d-flex gap-3 mb-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="schedulingType" id="schedulingImmediate" value="INMEDIATO" checked>
                                            <label class="form-check-label" for="schedulingImmediate">
                                                Enviar ahora
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="schedulingType" id="schedulingProgrammed" value="PROGRAMADO">
                                            <label class="form-check-label" for="schedulingProgrammed">
                                                Programar envío
                                            </label>
                                        </div>
                                    </div>

                                    <div id="schedulingOptions" class="row" style="display: none;">
                                        <!-- Selección de fecha -->
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Fecha de envío</label>
                                            <input type="date" class="form-control" name="scheduledDate" id="scheduledDate">
                                        </div>
                                        
                                        <!-- Selección de hora -->
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Hora de envío</label>
                                            <input type="time" class="form-control" name="scheduledTime" id="scheduledTime">
                                        </div>
                                        
                                        <!-- Repetición (opcional) -->
                                        <div class="col-md-12 mb-2">
                                            <label class="form-label">Repetir envío</label>
                                            <select class="form-select" name="repeatSchedule" id="repeatSchedule">
                                                <option value="NONE">No repetir</option>
                                                <option value="DAILY">Diariamente</option>
                                                <option value="WEEKLY">Semanalmente</option>
                                                <option value="MONTHLY">Mensualmente</option>
                                            </select>
                                        </div>
                                    </div>
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

                            <div class="col-md-10 mx-auto">
                                <div class="mb-3 text-center">
                                    <a href="<?= base_url('lista-mensajes')?>" class="btn btn-danger">Cancelar</a>
                                    <button type="submit" class="btn btn-success">Enviar Mensaje</button>
                                </div>

                            </div>

                        </div>
                    </form>

                </div>
            </div>

        </div>
        <!-- [ Main Content ] end -->
    </div>

    <div id="modalVariables" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="titleModal">Elegir Variable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <button type="button" class="btn btn-outline-success d-inline-flex variable" data-info="RAZON_SOCIAL">RAZON SOCIAL</button>
                    <button type="button" class="btn btn-outline-success d-inline-flex variable" data-info="RUC">RUC</button>
                    <button type="button" class="btn btn-outline-success d-inline-flex variable" data-info="NOMBRE_CONTACTO">NOMBRE CONTACTO</button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>
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