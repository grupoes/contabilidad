<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>GRUPO ES | REPORTE DE VENTAS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url() ?>/publicreportes/assets/images/icon.png">

    <!-- DataTables -->
    <link href="<?= base_url() ?>reportes/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <!-- plugin css -->
    <link href="<?= base_url() ?>reportes/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

    <!-- preloader css -->
    <link rel="stylesheet" href="<?= base_url() ?>reportes/assets/css/preloader.min.css" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="<?= base_url() ?>reportes/assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?= base_url() ?>reportes/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?= base_url() ?>reportes/assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <style>
        #cover-spin {
            position: fixed;
            width: 100%;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            display: none;
        }

        @-webkit-keyframes spin {
            from {
                -webkit-transform: rotate(0deg);
            }

            to {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        #cover-spin::after {
            content: '';
            display: block;
            position: absolute;
            left: 48%;
            top: 40%;
            width: 40px;
            height: 40px;
            border-style: solid;
            border-color: black;
            border-top-color: transparent;
            border-width: 4px;
            border-radius: 50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
        }
    </style>

</head>

<body data-layout="horizontal">
    <input type="hidden" id="url_base" value="<?= base_url() ?>">
    <!-- Begin page -->
    <div id="layout-wrapper">

        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="#" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="<?= base_url() ?>reportes/assets/images/icon.png" alt="" height="24">
                            </span>
                            <span class="logo-lg">
                                <img src="<?= base_url() ?>reportes/assets/images/icon.png" alt="" height="24">
                            </span>
                        </a>

                        <a href="#" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="<?= base_url() ?>reportes/assets/images/icon.png" alt="" height="24">
                            </span>
                            <span class="logo-lg">
                                <img src="<?= base_url() ?>reportes/assets/images/icon.png" alt="" height="24"> <span class="logo-txt">Minia</span>
                            </span>
                        </a>
                    </div>

                </div>

                <div class="d-flex w-100">
                    <div style="justify-content: center; width: 100%">
                        <h3>GRUPO ES CONSULTORES</h3>
                    </div>
                </div>


            </div>
        </header>

        <div class="topnav">
            <div class="container-fluid">
                <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

                    <div class="collapse navbar-collapse" id="topnav-menu-content">
                        <ul class="navbar-nav">

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-dashboard" role="button">
                                    <i data-feather="home"></i><span data-key="t-dashboards"><?= $data_c['razon_social'] ?></span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <input type="hidden" id="razon_social" value="<?= $data_c['razon_social'] ?>">
        <input type="hidden" id="contribuyente" value="<?= $data_c['id_contribuyente'] ?>">
        <input type="hidden" id="ruc_contribuyente" value="<?= $data_c['ruc'] ?>">

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div id="cover-spin"></div>
                <div class="row">
                    <div class="col-xl-12 col-md-12">
                        <!-- card -->
                        <div class="card card-h-100">
                            <!-- card body -->
                            <div class="card-body">

                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#home" role="tab">
                                            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                            <span class="d-none d-sm-block">Reporte de Compras</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#profile" role="tab">
                                            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                            <span class="d-none d-sm-block">Reporte de Ventas</span>
                                        </a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content p-3 text-muted">
                                    <div class="tab-pane active" id="home" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">SUCURSAL</label>
                                                    <select name="sucursal_compra" id="sucursal_compra" class="form-control">
                                                        <option value="0">TODOS</option>
                                                        <?php foreach ($data_s as $key => $value) { ?>
                                                            <option value="<?= $value['idsucursal'] ?>"><?= $value['nombre'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">FECHA DE INICIO</label>
                                                    <input type="date" class="form-control" name="fecha_inicio_compras" id="fecha_incio_compras">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">FECHA DE FIN</label>
                                                    <input type="date" class="form-control" name="fecha_fin_compras" id="fecha_fin_compras">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">PERIODO</label>
                                                    <input type="date" class="form-control" name="periodo" id="periodo">
                                                </div>
                                            </div>

                                            <div class="col-md-1">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">CUENTA</label>
                                                    <input type="number" class="form-control" name="cuenta_compra" id="cuenta_compra">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">GLOSA</label>
                                                    <input type="text" class="form-control" name="glosa_compra" id="glosa_compra">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <a href="#" id="btn_maq_compra" class="btn btn-success">CONSULTAR</a>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="table-responsive">
                                            <table class="table" id="table_compra">
                                                <thead>
                                                    <tr>
                                                        <th>N°</th>
                                                        <th>PERIODO</th>
                                                        <th>FECHA</th>
                                                        <th>TIPO MONEDA</th>
                                                        <th>DOCUMENTO</th>
                                                        <th>#_DOCUMENTO</th>
                                                        <th>CONDICION</th>
                                                        <th>RUC</th>
                                                        <th>RAZON SOCIAL</th>
                                                        <th>VVENTA</th>
                                                        <th>VALOR VENTA</th>
                                                        <th>IGV</th>
                                                        <th>BOLSA</th>
                                                        <th>ICB</th>
                                                        <th>TOTAL</th>
                                                        <th>TIPO_CAMBIO</th>
                                                        <th>GLOSA</th>
                                                        <th>CUENTA</th>
                                                        <th>AFECTACION</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contentCompras"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="profile" role="tabpanel">

                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">SUCURSAL</label>
                                                    <select name="sucursal_venta" id="sucursal_venta" class="form-control">
                                                        <option value="0">TODOS</option>
                                                        <?php foreach ($data_s as $key => $value) { ?>
                                                            <option value="<?= $value['idsucursal'] ?>"><?= $value['nombre'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">FECHA DE INICIO</label>
                                                    <input type="date" class="form-control" name="fecha_inicio_ventas" id="fecha_inicio_ventas">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">FECHA DE FIN</label>
                                                    <input type="date" class="form-control" name="fecha_fin_ventas" id="fecha_fin_ventas">
                                                </div>
                                            </div>

                                            <div class="col-md-1">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">CUENTA</label>
                                                    <input type="number" class="form-control" name="cuenta_ventas" id="cuenta_ventas">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="" class="form-label">GLOSA</label>
                                                    <input type="text" class="form-control" name="glosa_ventas" id="glosa_ventas">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-success dropdown-toggle mt-4" data-bs-toggle="dropdown" aria-expanded="false">CONSULTAR <i class="mdi mdi-chevron-down"></i></button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="#" id="btn_venta">REPORTE DETALLADO</a>
                                                            <a class="dropdown-item" href="#" id="btn_maq_venta">MAQUETA DE VENTA</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="table-responsive" id="contentData">
                                            <table class="table" id="data_venta">
                                                <thead>
                                                    <tr id="cabecera_table">
                                                        <th>N°</th>
                                                        <th>FECHA</th>
                                                        <th>TIPO MONEDA</th>
                                                        <th>DOCUMENTO</th>
                                                        <th>#_DOCUMENTO</th>
                                                        <th>CONDICION</th>
                                                        <th>RUC</th>
                                                        <th>RAZON SOCIAL</th>
                                                        <th>EXONERADA</th>
                                                        <th>GRAVADA</th>
                                                        <th>INAFECTA</th>
                                                        <th>VVENTA</th>
                                                        <th>VALOR VENTA</th>
                                                        <th>IGV</th>
                                                        <th>BOLSA</th>
                                                        <th>ICB</th>
                                                        <th>TOTAL</th>
                                                        <th>TIPO_CAMBIO</th>
                                                        <th>GLOSA</th>
                                                        <th>CUENTA</th>
                                                        <th>AFECTACION</th>
                                                        <th>CONDICION DEL CONTRIBUYENTE</th>
                                                        <th>ESTADO DEL CONTRIBUYENTE</th>
                                                        <th>ESTADO SUNAT</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contentVentas"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                </div>

            </div>
            <!-- End Page-content -->

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="<?= base_url() ?>reportes/assets/libs/jquery/jquery.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/node-waves/waves.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/feather-icons/feather.min.js"></script>
    <!-- pace js -->
    <script src="<?= base_url() ?>reportes/assets/libs/pace-js/pace.min.js"></script>

    <!-- Required datatable js -->
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/jszip/jszip.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

    <!-- apexcharts -->
    <script src="<?= base_url() ?>reportes/assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Plugins js-->
    <script src="<?= base_url() ?>reportes/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

    <script src="<?= base_url() ?>reportes/assets/libs/table-edits/build/table-edits.min.js"></script>
    <!-- dashboard init -->
    <script src="<?= base_url() ?>/js/reporte.js"></script>

</body>

</html>