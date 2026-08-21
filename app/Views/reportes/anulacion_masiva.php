<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">Informe de Anulación Masiva de Comprobantes</h2>
                        <small class="text-muted">Periodo: 9 y 10 de agosto de 2026</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h2 class="mb-1">110,824</h2>
                    <p class="mb-0">Facturas dadas de baja</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h2 class="mb-1">S/ 24,426,970</h2>
                    <p class="mb-0">Monto regularizado</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h2 class="mb-1">107,400</h2>
                    <p class="mb-0">CDR firmados por SUNAT</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h2 class="mb-1">0</h2>
                    <p class="mb-0">Pendientes ante SUNAT</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- ESTADO FINAL -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="ti ti-circle-check me-2"></i>1. Estado Final</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Las 110,824 facturas emitidas por error están <strong>DADAS DE BAJA ante SUNAT</strong>. No queda ninguna pendiente.</p>

                    <h6 class="fw-bold">Comprobantes anulados</h6>
                    <ul class="list-unstyled mb-3">
                        <li><span class="badge bg-light text-dark me-2">Rango</span> F001-6680 a F001-117503</li>
                        <li><span class="badge bg-light text-dark me-2">Fechas emisión</span> 9 y 10 de agosto de 2026</li>
                        <li><span class="badge bg-light text-dark me-2">Monto total</span> S/ 24,426,970.00</li>
                    </ul>

                    <h6 class="fw-bold">Plazos</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Bloque</th>
                                    <th>Vencía</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Facturas del 9 de agosto</td>
                                    <td>16 de agosto</td>
                                    <td><span class="badge bg-success">Cumplido con anticipación</span></td>
                                </tr>
                                <tr>
                                    <td>Facturas del 10 de agosto</td>
                                    <td>17 de agosto</td>
                                    <td><span class="badge bg-success">Cumplido con anticipación</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRABAJO REALIZADO -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="ti ti-list-check me-2"></i>2. Trabajo Realizado</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <span class="badge bg-success me-3 mt-1 p-2"><i class="ti ti-check"></i></span>
                        <div>
                            <h6 class="mb-1">Comunicaciones de baja</h6>
                            <p class="text-muted mb-0">Todas presentadas y aceptadas por SUNAT.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <span class="badge bg-success me-3 mt-1 p-2"><i class="ti ti-check"></i></span>
                        <div>
                            <h6 class="mb-1">Verificación individual — 110,824 comprobantes</h6>
                            <p class="text-muted mb-0">Cada documento fue consultado uno a uno contra el servicio de SUNAT y su respuesta fue guardada. <strong>No es un muestreo.</strong></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <span class="badge bg-info me-3 mt-1 p-2"><i class="ti ti-file-certificate"></i></span>
                        <div>
                            <h6 class="mb-1">Constancias CDR — 107,400 comprobantes</h6>
                            <p class="text-muted mb-0">Constancias de recepción firmadas digitalmente por SUNAT, archivadas y descargables.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <span class="badge bg-primary me-3 mt-1 p-2"><i class="ti ti-database"></i></span>
                        <div>
                            <h6 class="mb-1">Regularización de registros internos</h6>
                            <p class="text-muted mb-0">Los S/ 24,426,970 ya <strong>no figuran como ventas válidas</strong> en el sistema. Sus reportes y su registro de ventas reflejan lo que SUNAT reconoce.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DESCARGA DE CDRs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="ti ti-file-download me-2"></i>3. Descarga de CDRs</h5>
                    <span class="badge bg-white text-info">107,400 constancias disponibles</span>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Descarga las constancias de recepción (CDR) firmadas digitalmente por SUNAT de todos los comprobantes dados de baja.</p>
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <div class="border rounded p-3 d-flex align-items-center gap-3">
                                <i class="ti ti-file-zip fs-2 text-warning"></i>
                                <div>
                                    <div class="fw-semibold">Todos los CDRs</div>
                                    <small class="text-muted">Archivo ZIP · 107,400 archivos</small>
                                </div>
                                <a href="<?= base_url('api/baja-lote/download-all') ?>" class="btn btn-sm btn-warning ms-auto" id="btnDescargarTodosCDR">
                                    <i class="ti ti-download me-1"></i>Descargar
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Nota:</strong> Seis comunicaciones (RA-20260814-90404, 90479, 90486, 90511, 90526 y 90532), que amparan 3,000 comprobantes, no tienen CDR descargable: SUNAT las aceptó, pero el número de ticket no llegó a registrarse y sin ticket no es posible volver a solicitar la constancia.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA DE BAJAS -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between" style="background-color:#495057;">
                    <h5 class="mb-0 text-white"><i class="ti ti-receipt-off me-2"></i>4. Comunicaciones de Baja</h5>
                    <span class="badge bg-light text-dark" id="totalBajas">Cargando...</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablaBajas">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Identificador</th>
                                    <th>Fecha generación</th>
                                    <th class="text-center"># Documentos</th>
                                    <th class="text-center">Estado SUNAT</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="bodyBajas"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- TODOS LOS COMPROBANTES -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex align-items-center justify-content-between border-bottom">
                    <h5 class="mb-0 text-dark"><i class="ti ti-search me-2"></i>5. Consulta de Comprobantes por Lote</h5>
                    <span class="badge bg-secondary" id="totalComprobantes">Cargando...</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablaComprobantes">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Serie</th>
                                    <th>Número</th>
                                    <th>Fecha comprobante</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
                                    <th>Lote</th>
                                </tr>
                            </thead>
                            <tbody id="bodyComprobantes"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- COMUNICACION BAJA VS DOC ELECTRONICO -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-dark"><i class="ti ti-link me-2"></i>6. Comunicaciones de Baja vs Documento Electrónico</h5>
                    <span class="badge bg-secondary" id="totalComunicaciones">Cargando...</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablaComunicaciones">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Archivo XML CPE</th>
                                    <th>Código Ticket</th>
                                    <th>Serie</th>
                                    <th>Número</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado SUNAT</th>
                                    <th class="text-center">CDR</th>
                                    <th class="text-center">Encontrado</th>
                                </tr>
                            </thead>
                            <tbody id="bodyComunicaciones"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal detalle comprobantes de una baja -->
<div class="modal fade" id="modalDetalleBaja" tabindex="-1" aria-labelledby="modalDetalleBajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleBajaLabel">Comprobantes de la baja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive px-3 pt-3">
                    <table class="table table-sm table-hover align-middle mb-0" id="tablaDetalleComprobantes">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Serie</th>
                                <th>Número</th>
                                <th>Fecha comprobante</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody id="bodyDetalleComprobantes"></tbody>
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
<script src="<?= base_url() ?>assets/js/plugins/dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/responsive.bootstrap5.min.js"></script>

<script>
const baseUrl = $('#base_url').val();

$(document).ready(function () {
    const dtBajas = $('#tablaBajas').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: [5] }]
    });

    // cargar bajas desde la API
    $.get(baseUrl + 'api/baja-lote', function (data) {
        $('#totalBajas').text(data.length + ' registros');

        data.forEach(function (baja, index) {
            const estadoBadge = baja.estado === 'aceptado'
                ? '<span class="badge bg-success">Aceptado</span>'
                : '<span class="badge bg-warning text-dark">' + baja.estado + '</span>';

            const acciones = `
                <button class="btn btn-sm btn-outline-secondary btnVerDetalle me-1"
                    data-id="${baja.id_baja_lote}"
                    data-nombre="${baja.identificador_baja}"
                    title="Ver comprobantes">
                    <i class="ti ti-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-success btnDescargarCDR"
                    data-id="${baja.id_baja_lote}"
                    data-nombre="${baja.identificador_baja}"
                    title="Descargar CDR ZIP">
                    <i class="ti ti-download"></i>
                </button>`;

            const partesFecha = baja.fecha_generacion ? baja.fecha_generacion.substring(0, 10).split('-') : null;
            const fechaFormateada = partesFecha ? partesFecha[2] + '-' + partesFecha[1] + '-' + partesFecha[0] : '—';

            dtBajas.row.add([
                index + 1,
                '<code>' + baja.identificador_baja + '</code>',
                fechaFormateada,
                '<span class="badge bg-primary">' + (baja.cantidad_documentos ?? '—') + '</span>',
                estadoBadge,
                acciones
            ]).draw(false);
        });
    });

    // punto 5 — todos los comprobantes (server-side)
    const dtComprobantes = $('#tablaComprobantes').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[0, 'asc']],
        ajax: {
            url: baseUrl + 'api/baja-lote/documentos',
            type: 'GET',
            dataSrc: 'data',
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
            },
            { data: 'serie_comprobante' },
            { data: 'numero_comprobante' },
            {
                data: 'fecha_comprobante',
                render: function (val) {
                    if (!val) return '—';
                    const p = val.substring(0, 10).split('-');
                    return p[2] + '-' + p[1] + '-' + p[0];
                }
            },
            {
                data: 'total',
                className: 'text-end',
                render: function (val) {
                    return val !== null ? 'S/ ' + parseFloat(val).toFixed(2) : '—';
                }
            },
            {
                data: 'estado',
                className: 'text-center',
                render: function (val) {
                    return val ? '<span class="badge bg-success">' + val + '</span>' : '—';
                }
            },
            {
                data: 'identificador_baja',
                render: function (val) {
                    return '<code>' + val + '</code>';
                }
            },
        ],
        initComplete: function () {
            const info = this.api().page.info();
            $('#totalComprobantes').text(info.recordsTotal.toLocaleString('es-PE') + ' comprobantes');
        },
        drawCallback: function () {
            const info = this.api().page.info();
            $('#totalComprobantes').text(info.recordsTotal.toLocaleString('es-PE') + ' comprobantes');
        },
    });

    // ver detalle
    let tablaDetalle = null;
    let idBajaActual = null;

    $(document).on('click', '.btnVerDetalle', function () {
        idBajaActual = $(this).data('id');
        const nombre = $(this).data('nombre');
        $('#modalDetalleBajaLabel').text('Comprobantes de la baja: ' + nombre);

        if (tablaDetalle) {
            tablaDetalle.destroy();
            tablaDetalle = null;
        }
        $('#bodyDetalleComprobantes').empty();

        $('#modalDetalleBaja').modal('show');
    });

    $('#modalDetalleBaja').on('shown.bs.modal', function () {
        tablaDetalle = $('#tablaDetalleComprobantes').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 10,
            order: [[1, 'asc'], [2, 'asc']],
            autoWidth: false,
            columnDefs: [{ orderable: false, targets: [5] }]
        });

        $.get(baseUrl + 'api/baja-lote/' + idBajaActual + '/documentos', function (docs) {
            docs.forEach(function (doc, index) {
                const partes = doc.fecha_comprobante ? doc.fecha_comprobante.substring(0, 10).split('-') : null;
                const fecha  = partes ? partes[2] + '-' + partes[1] + '-' + partes[0] : '—';
                const total  = doc.total !== null ? 'S/ ' + parseFloat(doc.total).toFixed(2) : '—';
                const estado = doc.estado
                    ? '<span class="badge bg-success">' + doc.estado + '</span>'
                    : '—';

                tablaDetalle.row.add([
                    index + 1,
                    doc.serie_comprobante,
                    doc.numero_comprobante,
                    fecha,
                    total,
                    estado
                ]).draw(false);
            });
        });
    });

    // punto 6 — comunicacion baja vs doc electronico (server-side)
    const dtComunicaciones = $('#tablaComunicaciones').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[1, 'asc']],
        ajax: {
            url: baseUrl + 'api/comunicacion-baja-detalle',
            type: 'GET',
            dataSrc: 'data',
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
            },
            {
                data: 'name_file_xml_cpe',
                render: function (val) {
                    return '<small>' + (val ?? '—') + '</small>';
                }
            },
            { data: 'codigo_ticket', defaultContent: '—' },
            { data: 'serie_comprobante', defaultContent: '—' },
            { data: 'numero_comprobante', defaultContent: '—' },
            {
                data: 'total',
                className: 'text-end',
                render: function (val) {
                    return val !== null ? 'S/ ' + parseFloat(val).toFixed(2) : '—';
                }
            },
            {
                data: 'estado_envio_sunat',
                className: 'text-center',
                render: function (val) {
                    return val
                        ? '<span class="badge bg-success">' + val + '</span>'
                        : '<span class="badge bg-secondary">Sin dato</span>';
                }
            },
            {
                data: 'tiene_cdr',
                className: 'text-center',
                orderable: false,
                render: function (val) {
                    return parseInt(val)
                        ? '<i class="ti ti-circle-check text-success fs-5" title="CDR disponible"></i>'
                        : '<i class="ti ti-circle-x text-danger fs-5" title="Sin CDR"></i>';
                }
            },
            {
                data: 'encontrado',
                className: 'text-center',
                orderable: false,
                render: function (val) {
                    return parseInt(val)
                        ? '<span class="badge bg-success">Sí</span>'
                        : '<span class="badge bg-danger">No</span>';
                }
            },
        ],
        initComplete: function () {
            const info = this.api().page.info();
            $('#totalComunicaciones').text(info.recordsTotal.toLocaleString('es-PE') + ' registros');
        },
        drawCallback: function () {
            const info = this.api().page.info();
            $('#totalComunicaciones').text(info.recordsTotal.toLocaleString('es-PE') + ' registros');
        },
    });

    // descargar CDR ZIP
    $(document).on('click', '.btnDescargarCDR', function () {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');
        const btn    = $(this);

        btn.prop('disabled', true).html('<i class="ti ti-loader"></i>');

        $.get(baseUrl + 'api/baja-lote/download/' + id, function (res) {
            if (res.status === 'success') {
                const link = document.createElement('a');
                link.href = 'data:application/zip;base64,' + res.archivo_base64;
                link.download = (res.identificador_baja || ('baja-' + id)) + '.zip';
                link.click();
            } else {
                alert(res.message || 'No hay CDR disponible para este lote.');
            }
        }).fail(function () {
            alert('Error al obtener el CDR.');
        }).always(function () {
            btn.prop('disabled', false).html('<i class="ti ti-download"></i>');
        });
    });
});
</script>
<?= $this->endSection() ?>
