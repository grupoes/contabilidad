<!-- <script>console.log('URL: /codeigniter/default/login-v1')</script> -->
<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<!-- Mirrored from ableproadmin.com/codeigniter/default/login-v1 by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 29 Aug 2024 13:54:26 GMT -->
<!-- Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->

<head>

    <title>Login | Grupo Es Contabilidad</title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Grupo Es Contabilidad">
    <meta name="keywords" content="contabilidad tarapoto">
    <meta name="author" content="grupoes">

    <!-- [Favicon] icon -->
    <link rel="icon" href="<?= base_url() ?>img/grupoes.ico" type="image/x-icon">
    <!-- [Google Font] Family -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/fonts/inter/inter.css" id="main-font-link" />
    <!-- [phosphor Icons] https://phosphoricons.com/ -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/fonts/phosphor/duotone/style.css" />
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/fonts/tabler-icons.min.css">
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/fonts/feather.css">
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/fonts/fontawesome.css">
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/fonts/material.css">
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/style.css" id="main-style-link">
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/style-preset.css">
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast="" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End --> <!-- [ Main Content ] start -->
    <div class="auth-main">
        <div class="auth-wrapper v1">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">
                        <form id="formLogin">
                            <input type="hidden" id="base_url" value="<?= base_url() ?>">
                            <div class="text-center">
                                <a href="#"><img src="<?= base_url() ?>img/logo-dark.svg" alt="img" style="width: 50%;"></a>
                            </div>
                            <div class="saprator my-3">
                                <span></span>
                            </div>
                            <h4 class="text-center f-w-500 mb-3">Inicie sesión</h4>
                            <div id="alert" style="display:none;" class="alert"></div>
                            <div class="mb-3">
                                <label class="form-label" for="username">Correo Electrónico</label>
                                <input type="email" class="form-control" name="username" id="username" placeholder="Ingrese su usuario">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese su contraseña">
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">Acceder</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
    <!-- Required Js -->
    <script src="<?= base_url() ?>assets/js/plugins/popper.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/simplebar.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/bootstrap.min.js"></script>
    <script src="<?= base_url() ?>assets/js/fonts/custom-font.js"></script>
    <script src="<?= base_url() ?>assets/js/pcoded.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/feather.min.js"></script>
    
    <script>
        layout_change('light');
    </script>
    <script>
        layout_theme_contrast_change('false');
    </script>
    <script>
        change_box_container('false');
    </script>
    <script>
        layout_caption_change('true');
    </script>
    <script>
        layout_rtl_change('false');
    </script>
    <script>
        preset_change('preset-1');
    </script>

<script src="<?= base_url() ?>js/auth/login.js"></script>
    
</body>
<!-- [Body] end -->

<!-- Mirrored from ableproadmin.com/codeigniter/default/login-v1 by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 29 Aug 2024 13:54:27 GMT -->

</html>