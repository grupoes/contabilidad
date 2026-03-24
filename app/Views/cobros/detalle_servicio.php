<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .badge-soft-success {
        background: linear-gradient(135deg, #28a745, #218838);
        color: white;
        border: none;
        box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2);
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .badge-soft-warning {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: #212529;
        border: none;
        box-shadow: 0 4px 6px rgba(255, 193, 7, 0.2);
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .hover-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .am-title {
        background: linear-gradient(45deg, #4e73df, #224abe);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }

    .nav-tabs-custom {
        border-bottom: 2px solid #f0f2f5;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 12px 20px;
        position: relative;
    }

    .nav-tabs-custom .nav-link.active {
        color: #4e73df;
        background: transparent;
    }

    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #4e73df;
    }

    /* Fix SweetAlert2 z-index when modal is open */
    .swal2-container {
        z-index: 2000 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title d-flex align-items-center justify-content-between">
                        <h3 class="mb-0 am-title">Detalle de Servicio</h3>
                        <a href="<?= base_url('cobros?tab=servicios') ?>" class="btn btn-danger shadow-sm">
                            <i class="fas fa-arrow-left me-1"></i> Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info del Servicio -->
    <div class="row">
        <div class="col-md-12">
            <div class="card glass-card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1 small text-uppercase fw-bold">Razón Social</h6>
                            <h5 class="mb-0 font-weight-bold"><?= $service['razon_social'] ?></h5>
                            <small class="text-muted">RUC: <?= $service['ruc'] ?></small>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted mb-1 small text-uppercase fw-bold">Costo Total</h6>
                            <h4 class="mb-0 text-primary">S/. <?= number_format($service['monto'], 2) ?></h4>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted mb-1 small text-uppercase fw-bold">Pendiente Total</h6>
                            <h4 class="mb-0 text-danger" id="headerTotalPendiente">S/. <?= number_format(array_reduce($pagos, function ($carry, $item) {
                                                                                                            return $carry + $item['monto_pendiente'];
                                                                                                        }, 0), 2) ?></h4>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1 small text-uppercase fw-bold">Estado de Servicio</h6>
                            <div id="serviceStatusContainer">
                                <?php if ($service['estado'] == 'pagado') : ?>
                                    <span class="badge badge-soft-success py-2 px-3"><i class="fas fa-check-circle me-1"></i> PAGADO</span>
                                <?php else : ?>
                                    <span class="badge badge-soft-warning py-2 px-3"><i class="fas fa-clock me-1"></i> PENDIENTE</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-2 text-end" id="btnAmortizarContainer">
                            <?php if ($service['estado'] == 'pendiente') : ?>
                                <button class="btn btn-primary d-flex align-items-center gap-2 float-end shadow-sm" id="btnOpenAmortizar">
                                    <i class="fas fa-hand-holding-usd"></i> Amortizar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr class="my-3 opacity-25">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-1 small text-uppercase fw-bold">Descripción del Servicio</h6>
                            <p class="mb-0"><?= $service['descripcion'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card glass-card">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs nav-tabs-custom px-4 pt-2" id="serviceTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="cronograma-tab" data-bs-toggle="tab" data-bs-target="#cronograma" type="button" role="tab" aria-controls="cronograma" aria-selected="true">
                                <i class="fas fa-calendar-alt me-2"></i>Cronograma de Pagos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="amortizaciones-tab" data-bs-toggle="tab" data-bs-target="#amortizaciones" type="button" role="tab" aria-controls="amortizaciones" aria-selected="false">
                                <i class="fas fa-history me-2"></i>Amortizaciones Realizadas
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content p-4" id="serviceTabsContent">
                        <!-- Cronograma -->
                        <div class="tab-pane fade show active" id="cronograma" role="tabpanel" aria-labelledby="cronograma-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="text-muted small text-uppercase">
                                        <tr>
                                            <th>F. Programada</th>
                                            <th>Monto</th>
                                            <th>Pagado</th>
                                            <th>Pendiente</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cronogramaBody">
                                        <?php foreach ($pagos as $pago) : ?>
                                            <tr class="hover-row">
                                                <td><?= date('d/m/Y', strtotime($pago['fecha_programacion'])) ?></td>
                                                <td class="fw-bold fs-6">S/. <?= number_format($pago['monto'], 2) ?></td>
                                                <td class="text-success small fw-bold">S/. <?= number_format($pago['monto_pagado'], 2) ?></td>
                                                <td class="text-danger small fw-bold">S/. <?= number_format($pago['monto_pendiente'], 2) ?></td>
                                                <td>
                                                    <?php if ($pago['estado'] == 'pagado') : ?>
                                                        <span class="badge badge-soft-success">PAGADO</span>
                                                    <?php else : ?>
                                                        <span class="badge badge-soft-warning">PENDIENTE</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Amortizaciones -->
                        <div class="tab-pane fade" id="amortizaciones" role="tabpanel" aria-labelledby="amortizaciones-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="text-muted small text-uppercase">
                                        <tr>
                                            <th>Fecha Registro</th>
                                            <th>F. Pago</th>
                                            <th>Método de Pago</th>
                                            <th>Monto</th>
                                            <th class="text-center">Voucher</th>
                                        </tr>
                                    </thead>
                                    <tbody id="amortizacionesBody">
                                        <?php if (empty($amortizaciones)) : ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No se registran amortizaciones.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php foreach ($amortizaciones as $am) : ?>
                                            <tr class="hover-row">
                                                <td><?= date('d/m/Y H:i', strtotime($am['registro'])) ?></td>
                                                <td><?= date('d/m/Y', strtotime($am['fecha_pago'])) ?></td>
                                                <td><span class="small badge bg-light text-dark fw-normal"><?= $am['metodo_nombre'] ?></span></td>
                                                <td class="fw-bold text-success">S/. <?= number_format($am['monto'], 2) ?></td>
                                                <td class="text-center">
                                                    <?php if ($am['vaucher']) : ?>
                                                        <button type="button" class="btn btn-sm btn-icon btn-light-primary btnViewVoucher" data-url="<?= base_url() ?>vouchers/<?= $am['vaucher'] ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    <?php else : ?>
                                                        <span class="text-muted small">Sin voucher</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Amortizar -->
<div class="modal fade" id="modalAmortizar" tabindex="-1" aria-labelledby="modalAmortizarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title text-white" id="modalAmortizarLabel">Nueva Amortización</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAmortizar" enctype="multipart/form-data">
                <input type="hidden" name="service_id" id="service_id_input" value="<?= $service['id'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Monto a Amortizar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">S/.</span>
                            <input type="number" step="0.01" class="form-control bg-light border-0" name="monto" id="monto_amortizar" required>
                        </div>
                        <small class="text-muted mt-1 d-block" id="total_pendiente_label">Pendiente Total: S/. </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Método de Pago</label>
                        <select class="form-select bg-light border-0" name="metodo_pago_id" id="metodo_pago_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($metodos as $m) : ?>
                                <option value="<?= $m['id'] ?>"><?= $m['metodo'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="divSedeSelect" style="display: none;">
                        <label class="form-label font-weight-bold">Sucursal / Sede</label>
                        <select class="form-select bg-light border-0" name="sede_id" id="sede_id">
                            <?php foreach ($sedes as $s) : ?>
                                <option value="<?= $s['id'] ?>" <?= $s['id'] == session()->sede_id ? 'selected' : '' ?>><?= $s['nombre_sede'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Fecha de Pago</label>
                        <input type="date" class="form-control bg-light border-0" name="fecha_pago" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-0" id="viewVoucher" style="display: none;">
                        <label class="form-label font-weight-bold">Voucher / Comprobante</label>
                        <input type="file" class="form-control bg-light border-0" name="vaucher" id="vaucher">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Voucher -->
<div class="modal fade" id="modalVoucher" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <h5 class="mb-3 font-weight-bold">Voucher / Comprobante de Pago</h5>
                <img src="" id="voucherImage" class="img-fluid rounded shadow-sm" style="max-height: 500px;" alt="Voucher">
                <div id="voucherPdf" style="display: none;">
                    <iframe src="" id="voucherIframe" width="100%" height="500px" style="border: none;"></iframe>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <a href="" id="btnDownloadVoucher" download class="btn btn-outline-primary shadow-sm px-4">
                    <i class="fas fa-download me-2"></i>Descargar
                </a>
                <button type="button" class="btn btn-primary px-4 shadow" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="<?= base_url() ?>assets/js/plugins/notifier.js"></script>
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script>
    let globalTotalPendiente = <?= array_reduce($pagos, function($carry, $item) {
                                return $carry + $item['monto_pendiente'];
                            }, 0) ?>;
    const serviceId = <?= $service['id'] ?>;
</script>
<script src="<?= base_url() ?>js/cobros/detalle_servicio.js?v=4"></script>
<?= $this->endSection() ?>