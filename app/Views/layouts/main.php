<!-- <script>console.log('URL: /codeigniter/default/sample-page')</script> -->
<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<!-- Mirrored from ableproadmin.com/codeigniter/default/sample-page by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 29 Aug 2024 13:54:39 GMT -->
<!-- Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->

<head>

    <title>Home | Grupo ES Contabilidad</title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Admin Grupo ES">
    <meta name="keywords" content="admin Grupo ES">
    <meta name="author" content="Phoenixcoded">

    <!-- [Favicon] icon -->
    <link rel="icon" href="<?= base_url() ?>img/grupoes.ico" type="image/x-icon">

    <?= $this->renderSection('css') ?>

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

    <style>
        .custom-loader {
            z-index: 1040;
        }

        .contentLoader {
            display: none;
        }
    </style>

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

    <div id="loader" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-light bg-opacity-75">
        <div class="text-center contentLoader">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 fw-bold">Cargando...</div>
        </div>
    </div>
    <!-- [ Pre-loader ] End --><!-- [ Sidebar Menu ] start -->

    <input type="hidden" id="base_url" value="<?= base_url() ?>">
    <input type="hidden" id="perfilId" value="<?= session()->perfil_id ?>">
    <nav class="pc-sidebar">
        <div class="navbar-wrapper">
            <div class="m-header" style="margin-bottom: 15px;">
                <a href="dashboard-default.html" class="b-brand text-primary" style="padding-top: 20px;">
                    <!-- ========   Change your logo from here   ============ -->
                    <img src="<?= base_url('assets/images/logo-dark.svg') ?>" alt="logo" class="img-fluid logo-lg" style="width: 70%;">
                </a>
            </div>
            <div class="navbar-content">
                <?= $this->include('layouts/cardUser') ?>

                <ul class="pc-navbar" id="nav-menu">
                    <?= $this->include('layouts/menu') ?>
                </ul>
            </div>
        </div>
    </nav>
    <!-- [ Sidebar Menu ] end -->
    <!-- [ Header Topbar ] start -->
    <header class="pc-header">
        <?= $this->include('layouts/header') ?>
    </header>

    <!-- [ Header ] end --> <!-- [ Main Content ] start -->
    <div class="pc-container">
        <?= $this->renderSection('content') ?>
    </div>
    <!-- [ Main Content ] end -->
    <!-- [ footer ] start -->
    <footer class="pc-footer">
        <?= $this->include('layouts/footer') ?>
    </footer>
    <!-- [ footer ] End -->

    <!-- Required Js -->
    <script src="<?= base_url() ?>js/jquery.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/popper.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/simplebar.min.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/bootstrap.min.js"></script>
    <script src="<?= base_url() ?>assets/js/fonts/custom-font.js"></script>
    <script src="<?= base_url() ?>assets/js/pcoded.js"></script>
    <script src="<?= base_url() ?>assets/js/plugins/feather.min.js"></script>
    <script src="<?= base_url() ?>js/main.js"></script>

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

    <?= $this->renderSection('js') ?>
</body>
<!-- [Body] end -->

</html>