<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="pc-content">

    <!-- [ breadcrumb ] start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">DASHBOARD</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->
    <!-- [ Main Content ] start -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div
                        class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Estado de Caja</h5>
                        <div class="dropdown">
                            <a
                                class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                href="#"
                                data-bs-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Name</a>
                                <a class="dropdown-item" href="#">Date</a>
                                <a class="dropdown-item" href="#">Ratting</a>
                                <a class="dropdown-item" href="#">Unread</a>
                            </div>
                        </div>
                    </div>
                    <div class="my-3">
                        <div id="overview-product-graph"></div>
                    </div>
                    <div class="row g-3 text-center">
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-dark mb-1"><span>Apps</span></p>
                                <h6 class="mb-0">10+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-dark mb-1"><span>Other</span></p>
                                <h6 class="mb-0">5+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-secondary mb-1"><span>Widgets</span></p>
                                <h6 class="mb-0">150+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-secondary mb-1"><span>Forms</span></p>
                                <h6 class="mb-0">50+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-primary mb-1"><span>Components</span></p>
                                <h6 class="mb-0">200+</h6>
                            </div>
                        </div>
                        <div class="col-6 col-lg-4 col-xxl-4">
                            <div class="overview-product-legends">
                                <p class="text-primary mb-1"><span>Pages</span></p>
                                <h6 class="mb-0">150+</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body border-bottom pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Estados Clientes</h5>

                    </div>
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
                                MOROSOS
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
                                MOROSOS ANUAL
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="analytics-tab-3"
                                data-bs-toggle="tab"
                                data-bs-target="#analytics-tab-3-pane"
                                type="button"
                                role="tab"
                                aria-controls="analytics-tab-3-pane"
                                aria-selected="false">
                                DEFECIT
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
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item pt-2 pb-2">
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
                            <li class="list-group-item pt-2 pb-2">
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
                            <li class="list-group-item pt-2 pb-2">
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
                            <li class="list-group-item pt-2 pb-2">
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
                        </ul>
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
                    <div
                        class="tab-pane fade"
                        id="analytics-tab-3-pane"
                        role="tabpanel"
                        aria-labelledby="analytics-tab-3"
                        tabindex="0">
                        <ul class="list-group list-group-flush">

                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- [ Main Content ] end -->
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>

<script src="<?= base_url('assets/js/plugins/apexcharts.min.js') ?>"></script>
<script src="<?= base_url() ?>js/home/cajero.js"></script>

<?= $this->endSection() ?>