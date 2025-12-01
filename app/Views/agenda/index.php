<?= $this->extend('layouts/main') ?>

<?= $this->section('css') ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/notifier.css">
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= base_url() ?>assets/css/plugins/responsive.bootstrap5.min.css">

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h3 class="mb-0">Mi Agenda</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row"><!-- [ sample-page ] start -->
        <div class="col-12">
            <div class="card">
                <div class="card-body position-relative">
                    <div id="calendar" class="calendar"></div>
                </div>
            </div>
        </div><!-- [ sample-page ] end -->
    </div>
</div>

<div class="modal fade" id="calendar-modal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="calendar-modal-title f-w-600 text-truncate">Modal title</h3><a href="#"
                    class="avtar avtar-s btn-link-danger btn-pc-default ms-auto" data-bs-dismiss="modal"><i
                        class="ti ti-x f-20"></i></a>
            </div>
            <div class="modal-body">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-xs bg-light-secondary"><i class="ti ti-heading f-20"></i></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1"><b>Título</b></h5>
                        <p class="pc-event-title text-muted"></p>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-xs bg-light-danger"><i class="ti ti-calendar-event f-20"></i></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1"><b>Fecha y Hora</b></h5>
                        <p class="pc-event-date text-muted"></p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-xs bg-light-primary"><i class="ti ti-file-text f-20"></i></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1"><b>Descripción</b></h5>
                        <p class="pc-event-description text-muted"></p>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-xs bg-light-warning"><i class="ti ti-bell f-20"></i></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1"><b>Notificar</b></h5>
                        <p class="pc-event-notificar text-muted"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <ul class="list-inline me-auto mb-0">
                    <li class="list-inline-item align-bottom"><a href="#" id="pc_event_remove"
                            class="avtar avtar-s btn-link-danger btn-pc-default w-sm-auto" data-bs-toggle="tooltip"
                            title="Delete"><i class="ti ti-trash f-18"></i></a></li>
                    <li class="list-inline-item align-bottom"><a href="#" id="pc_event_edit"
                            class="avtar avtar-s btn-link-success btn-pc-default" data-bs-toggle="tooltip"
                            title="Edit"><i class="ti ti-edit-circle f-18"></i></a></li>
                </ul>
                <div class="flex-grow-1 text-end"><button type="button" class="btn btn-primary"
                        data-bs-dismiss="modal">Cerrar</button></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="offcanvas offcanvas-end cal-event-offcanvas" tabindex="-1" id="calendar-add_edit_event">
    <div class="offcanvas-header">
        <h3 class="f-w-600 text-truncate" id="calendar-add_event-title">Agregar Actividad</h3>
        <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default ms-auto" data-bs-dismiss="offcanvas"><i class="ti ti-x f-20"></i></a>
    </div>
    <div class="offcanvas-body">
        <form id="pc-form-event">
            <input type="hidden" name="agenda_id" id="agenda_id" value="0">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="date" id="pc-e-date" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Hora</label>
                        <input type="time" class="form-control" name="time" id="pc-e-time" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" class="form-control" name="title" id="pc-e-title" placeholder="Escribe un título a la actividad" autofocus required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" placeholder="Escribe una descripción de la actividad" rows="3" name="description" id="pc-e-description" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="mb-3">
                        <div class="form-check form-check-inline mb-3 mt-2">
                            <input class="form-check-input" type="radio" name="opcion" id="por_dia" value="1" checked> <label class="form-check-label" for="por_dia">Por Día</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="opcion" id="por_hora" value="0"> <label class="form-check-label" for="por_hora">Por hora</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="mb-3">
                        <label class="form-label">Notificar </label>
                        <input type="text" class="form-control" name="notify_time" id="pc-e-notify-time" placeholder="00:00">
                    </div>
                </div>
            </div>
            <div class="row justify-content-between">
                <div class="col-auto">
                    <button type="button" class="btn btn-link-danger btn-pc-default" data-bs-dismiss="offcanvas">
                        <i class="align-text-bottom me-1 ti ti-circle-x"></i>Cerrar</button>
                </div>
                <div class="col-auto">
                    <button id="pc_event_add" type="submit" class="btn btn-secondary" data-pc-action="add"><span id="pc-e-btn-text"><i class="align-text-bottom me-1 ti ti-calendar-plus"></i> Agregar</span></button>
                </div>
            </div>
        </form>
    </div>
</div>


<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url() ?>assets/js/plugins/index.global.min.js"></script><!-- Sweet Alert -->
<script src="<?= base_url() ?>assets/js/plugins/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>assets/js/pages/calendar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/es.global.min.js"></script>

<script src="<?= base_url() ?>js/agenda/index.js"></script>

<?= $this->endSection() ?>