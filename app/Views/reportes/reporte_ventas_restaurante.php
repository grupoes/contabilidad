<!doctype html>
<html lang="en">


<!-- Mirrored from themesbrand.com/minia/layouts/layouts-horizontal.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 27 Jun 2021 19:34:22 GMT -->

<head>

    <meta charset="utf-8" />
    <title>REPORTES | GRUPO ES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url() ?>public/assets/images/favicon.ico">

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

    <!-- Begin page -->
    <div id="layout-wrapper">

        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="index-2.html" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="<?= base_url() ?>reportes/assets/images/logo-sm.svg" alt="" height="24">
                            </span>
                            <span class="logo-lg">
                                <img src="<?= base_url() ?>reportes/assets/images/logo-sm.svg" alt="" height="24"> <span class="logo-txt">REPORTES DE VENTAS ES RESTAURANTE</span>
                            </span>
                        </a>

                        <a href="index-2.html" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="<?= base_url() ?>reportes/assets/images/logo-sm.svg" alt="" height="24">
                            </span>
                            <span class="logo-lg">
                                <img src="<?= base_url() ?>reportes/assets/images/logo-sm.svg" alt="" height="24"> <span class="logo-txt">REPORTES DE VENTAS ES RESTAURANTE</span>
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item waves-effect waves-light" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                </div>


            </div>
        </header>

        <div class="topnav">
            <div class="container-fluid">
                <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

                    <div class="collapse navbar-collapse" id="topnav-menu-content">
                        <ul class="navbar-nav">

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle arrow-none" href="" role="button">
                                    <?= $rpta->empr_razon_social ?>
                                </a>
                            </li>

                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div id="cover-spin"></div>
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <input type="hidden" id="razon_social" value="<?= $rpta->empr_razon_social ?>">


                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                </div> <!-- container-fluid -->

                <div class="row">

                    <div class="col-md-12">
                        <!-- card -->
                        <div class="card">
                            <!-- card body -->
                            <div class="card-body">
                                <div class="row">
                                    <input type="hidden" id="ruc_empresa" value="<?= $rpta->empr_ruc ?>">
                                    <input type="hidden" id="url" value="<?= base_url() ?>">
                                    <input type="hidden" name="shema" id="shema" value="<?= $schemaName ?>">
                                    <input type="hidden" name="idempresa" id="idempresa" value="<?= $rpta->empr_id ?>">
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="" class="form-label">SUCURSAL</label>
                                            <select name="sucursal_venta" id="sucursal_venta" class="form-control">

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
                                                <th>REFERENCIA</th>
                                                <th>FECHA REFERENCIA</th>
                                            </tr>
                                        </thead>
                                        <tbody id="contentVentas"></tbody>
                                    </table>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div><!-- end row-->
            </div>
            <!-- End Page-content -->


            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © Minia.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Design & Develop by <a href="#!" class="text-decoration-underline">Themesbrand</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
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
    <!-- Required datatable js -->
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/jszip/jszip.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?= base_url() ?>reportes/assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>


    <script src="<?= base_url() ?>js/reporte.js"></script>

</body>

<!-- Mirrored from themesbrand.com/minia/layouts/layouts-horizontal.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 27 Jun 2021 19:34:22 GMT -->

</html>