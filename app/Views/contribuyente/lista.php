<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css" >
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css" >

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">Lista de Contribuyentes</h3>
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
                    <div class="d-flex justify-content-between align-items-center p-2 pb-sm-2">
                        <!-- Contenedor para el select y el botón -->
                        <div class="d-flex align-items-center gap-2">
                            <select id="selectOpciones" class="form-select w-auto">
                                <option value="TODOS">TODOS</option>
                                <option value="CONTABLE">CONTABLE</option>
                                <option value="ALQUILER">ALQUILER</option>
                            </select>

                            <a href="folio" target="_blank" class="btn btn-outline-primary d-inline-flex gap-2">
                                <i class="ti ti-file f-18"></i> Folio
                            </a>

                            <?php if(count($consulta_certificado_por_vencer) > 0) { ?>
                                <button class="btn btn-danger d-inline-flex gap-2" id="btnCertificadoVencer">
                                    <i class="ti ti-file f-18"></i> Certificados Digitales por vencer
                                </button>
                            <?php } ?>

                        </div>

                        <!-- Botón alineado a la derecha -->
                        <button type="button" id="btnModal" class="btn btn-primary d-inline-flex align-items-center gap-2">
                            <i class="ti ti-plus f-18"></i> Nueva Empresa
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle datatable dt-responsive table-hover table-check display" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;" id="tableData">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Razón Social</th>
                                    <th>Servicio</th>
                                    <th>Montos</th>
                                    <th>Sistema</th>
                                    <th>Activo</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">

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
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">Agregar Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDatos">
                <div class="modal-body">
                    <input type="hidden" name="idTable" id="idTable" value="0">
                    <div class="row">
                        <h5>Datos Empresa</h5>
                        <hr>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="numeroDocumento">R.U.C.</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="numeroDocumento" id="numeroDocumento" placeholder="" aria-describedby="searchDocumento" required>
                                <button class="btn btn-outline-primary" type="button" id="searchDocumento">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label" for="razonSocial">Razón Social</label>
                            <input type="text" class="form-control" name="razonSocial" id="razonSocial" required>
                            <small id="getRazonSocial" class="form-text text-success"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="nombreComercial">Nombre Comercial</label>
                            <input type="text" class="form-control" name="nombreComercial" id="nombreComercial" required>
                            <small id="getNombreComercial" class="form-text text-success"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="direccionFiscal">Dirección Fiscal</label>
                            <input type="text" class="form-control" name="direccionFiscal" id="direccionFiscal" required>
                            <small id="getDireccionFiscal" class="form-text text-success"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="ubigeo">Ubigeo</label>
                            <select class="form-select" name="ubigeo" id="ubigeo" required>
                                <option value="">Seleccione</option>
                            </select>
                            <small id="getUbigeo" class="form-text text-success"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="urbanizacion">Urbanización</label>
                            <input type="text" class="form-control" name="urbanizacion" id="urbanizacion">
                        </div>
                        <h5>Servicio</h5>
                        <hr>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="tipoSuscripcion">Tipo de Suscripción</label>
                            <select class="form-select" name="tipoSuscripcion" id="tipoSuscripcion" required>
                                <option value="NO GRATUITO">NO GRATUITO</option>
                                <option value="SI GRATUITO">GRATUITO</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="tipoServicio">Tipo de Servicio</label>
                            <select class="form-select" name="tipoServicio" id="tipoServicio" required>
                                <option value="CONTABLE">CONTABLE</option>
                                <option value="ALQUILER">ALQUILER</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 costos">
                            <label class="form-label" for="tipoPago">Tipo de Pago</label>
                            <select class="form-select" name="tipoPago" id="tipoPago" required>
                                <option value="ADELANTADO">ADELANTADO</option>
                                <option value="ATRASADO">ATRASADO</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 costos">
                            <label class="form-label" for="costoMensual">Costo Mensual</label>
                            <input type="number" class="form-control" name="costoMensual" id="costoMensual">
                        </div>
                        <div class="col-md-4 mb-3 costos">
                            <label class="form-label" for="costoAnual">Costo Anual</label>
                            <input type="number" class="form-control" name="costoAnual" id="costoAnual">
                        </div>

                        <div class="col-md-4 mb-3 costos">
                            <label class="form-label" for="diaCobro">Seleccione dia de cobro</label>
                            <select class="form-select" name="diaCobro" id="diaCobro">
                                <option value="01">01</option>
                                <option value="02">02</option>
                                <option value="03">03</option>
                                <option value="04">04</option>
                                <option value="05">05</option>
                                <option value="06">06</option>
                                <option value="07">07</option>
                                <option value="08">08</option>
                                <option value="09">09</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="13">13</option>
                                <option value="14">14</option>
                                <option value="15">15</option>
                                <option value="16">16</option>
                                <option value="17">17</option>
                                <option value="18">18</option>
                                <option value="19">19</option>
                                <option value="20">20</option>
                                <option value="21">21</option>
                                <option value="22">22</option>
                                <option value="23">23</option>
                                <option value="24">24</option>
                                <option value="25">25</option>
                                <option value="26">26</option>
                                <option value="27">27</option>
                                <option value="28">28</option>
                                <option value="29">29</option>
                                <option value="30">30</option>
                                <option value="31">31</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="fechaContrato">Fecha de Contrato</label>
                            <input type="date" class="form-control" name="fechaContrato" id="fechaContrato">
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label" for="choices-system">Seleccione Sistema</label>
                            <select class="form-control" name="nameSystem[]" id="choices-system"  multiple>
                                <?php foreach ($sistemas as $key => $value) { ?>
                                    <option value="<?= $value['id'] ?>"><?= $value['nameSystem'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <h5>Boletas</h5>
                        <hr>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="clientesVarios">Clientes varios</label>
                            <input type="text" class="form-control" name="clientesVarios" id="clientesVarios" value="00000001" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="boletaAnulado">Anulado</label>
                            <input type="text" class="form-control" name="boletaAnulado" id="boletaAnulado" value="00000000" required>
                        </div>
                        <h5>Facturas</h5>
                        <hr>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="facturaAnulado">Anulado</label>
                            <input type="text" class="form-control" name="facturaAnulado" id="facturaAnulado" value="00000000001" required>
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

<div id="modalTipoServicio" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalTarifa"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTarifa">
                    <input type="hidden" name="idTableTarifa" id="idTableTarifa" value="0">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="fechaInicioTarifa">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fechaInicioTarifa" id="fechaInicioTarifa" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="montoMensualTarifa">Monto Mensual</label>
                            <input type="number" class="form-control" name="montoMensualTarifa" id="montoMensualTarifa" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="montoAnualTarifa">Monto Anual</label>
                            <input type="number" class="form-control" name="montoAnualTarifa" id="montoAnualTarifa" required>
                        </div>
                        <div class="col-md-3 mb-3 mt-4">
                            <button type="submit" class="btn btn-success">Agregar Tarifa</button>
                        </div>
                    </div>
                </form>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Monto Mensual</th>
                            <th>Monto Anual</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableTarifa">
                        
                    </tbody>
                </table>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modalSistema" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModalCertificado"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCertificado" enctype="multipart/form-data">
                    <input type="hidden" name="idTableCertificado" id="idTableCertificado" value="0">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="tipo_certificado">Tipo Certificado</label>
                            <select class="form-select" name="tipo_certificado" id="tipo_certificado" required>
                                <option value="">Seleccione</option>
                                <option value="PROPIO">PROPIO</option>
                                <option value="PSE">PSE</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="fechaInicioCertificado">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fechaInicioCertificado" id="fechaInicioCertificado" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="fechaVencimientoCertificado">Fecha Vencimiento</label>
                            <input type="date" class="form-control" name="fechaVencimientoCertificado" id="fechaVencimientoCertificado" required>
                        </div>
                        <div class="col-md-4 mb-3 pse">
                            <label class="form-label" for="claveCertificado">Clave Certificado</label>
                            <input type="text" class="form-control" name="claveCertificado" id="claveCertificado" required>
                        </div>
                        <div class="col-md-8 mb-3 pse">
                            <label class="form-label" for="file_certificado">Archivo Certificado Digital( pfx, cer, p12 )</label>
                            <input type="file" class="form-control" name="file_certificado" id="file_certificado">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3 mx-auto text-center">
                            <button type="submit" class="btn btn-success">Agregar Certificado</button>
                        </div>
                    </div>
                </form>
                
                <h5>Lista de Certificados Digitales</h5>
                <hr>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tipo Certificado</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Vencimiento</th>
                            <th>Clave Certificado</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableCertificado">
                        
                    </tbody>
                </table>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="modalCertificado" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal">Certificados Digitales por vencer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>FECHA VENCIMIENTO</th>
                                <th>FECHA INICIO</th>
                                <th>RUC</th>
                                <th>RAZON SOCIAL</th>
                                <th>TIPO CERTIFICADO</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach ($consulta_certificado_por_vencer as $key => $value) { ?>
                            <tr>
                                <td><?= $value->fecha_vencimiento ?></td>
                                <td><?= $value->fecha_inicio ?></td>
                                <td><?= $value->ruc ?></td>
                                <td><?= $value->razon_social ?></td>
                                <td><?= $value->tipo_certificado ?></td>
                            </tr>
                        <?php } ?>
                            
                        </tbody>
                    </table>
                </div>
            
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/choices.min.js"></script>
<script src="<?= base_url() ?>js/contribuyente/lista.js"></script>

<?= $this->endSection() ?>