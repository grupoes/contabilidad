<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carrusel de PDFs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= base_url() ?>img/grupoes.ico" type="image/x-icon">

    <!-- ✅ BOOTSTRAP 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            background: black;
        }

        /* ✅ Visor ocupa TODA la pantalla */
        .visor-pdf {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background: #000;
            overflow: hidden;
            z-index: 1;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* ✅ Botón flotante */
        .btn-cerrar {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
        }
    </style>
</head>

<body>

    <!-- ✅ VISOR FULL SCREEN -->
    <div class="visor-pdf">

        <!-- ✅ CARRUSEL -->
        <div id="carouselPDF" class="carousel slide h-100" data-bs-ride="false">

            <!-- ✅ INDICADORES -->
            <div class="carousel-indicators">
                <?php foreach ($data as $i => $value): ?>
                    <button
                        type="button"
                        data-bs-target="#carouselPDF"
                        data-bs-slide-to="<?= $i ?>"
                        class="<?= ($value['nameFile'] == $name) ? 'active' : '' ?>">
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- ✅ CONTENIDO -->
            <div class="carousel-inner h-100">

                <?php
                $foundActive = false;
                foreach ($data as $key => $value):
                    if ($value['nameFile'] == $name && !$foundActive) {
                        $active = 'active';
                        $foundActive = true;
                    } else {
                        $active = '';
                    }
                ?>

                    <div class="carousel-item <?= $active ?> h-100">
                        <iframe
                            src="<?= base_url('archivos/pdt/' . $value['nameFile']); ?>"
                            data-name="<?= $value['nameFile']; ?>"
                            data-id="<?= $value['id']; ?>">
                        </iframe>
                    </div>

                <?php endforeach; ?>

            </div>


            <!-- ✅ CONTROLES -->
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselPDF" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#carouselPDF" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>

        </div>
    </div>

    <!-- ✅ BOOTSTRAP 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ✅ FUNCIÓN CERRAR -->
    <script>
        const carouselElement = document.getElementById('carouselPDF');

        carouselElement.addEventListener('slid.bs.carousel', function() {
            // Buscar el iframe activo
            const activeItem = document.querySelector('.carousel-item.active iframe');

            if (!activeItem) return;

            const pdfName = activeItem.getAttribute('data-name');
            const pdfId = activeItem.getAttribute('data-id');

            if (!pdfName) return;

            // Cambiar el query string SIN recargar la página
            const newUrl = `${window.location.pathname}?id=${pdfId}&name=${pdfName}`;
            window.history.replaceState({}, '', newUrl);
        })
    </script>

</body>

</html>