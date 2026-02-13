<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivo a Google Drive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Subir Archivo a Google Drive</h4>
                    </div>

                    <div class="card-body">
                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger">
                                <?= session('error') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('errors')): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach (session('errors') as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Info de espacio -->
                        <?php if (isset($storageInfo)): ?>
                            <div class="alert alert-info">
                                <strong>Espacio en Drive:</strong>
                                <?= number_format($storageInfo['free'] / 1024 / 1024 / 1024, 2) ?> GB
                                disponibles de <?= number_format($storageInfo['total'] / 1024 / 1024 / 1024, 2) ?> GB
                                <div class="progress mt-2">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: <?= $storageInfo['percent'] ?>%">
                                        <?= $storageInfo['percent'] ?>%
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form action="<?= site_url('drive/upload') ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label for="user_email" class="form-label">Tu Email</label>
                                <input type="email" class="form-control" id="user_email"
                                    name="user_email" required>
                            </div>

                            <div class="mb-3">
                                <label for="file" class="form-label">Archivo</label>
                                <input type="file" class="form-control" id="file"
                                    name="file" required>
                                <div class="form-text">
                                    Tamaño máximo: 10 MB. Se añadirá tu email y timestamp al nombre.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Subir a Google Drive</button>

                            <a href="<?= site_url('drive/list') ?>" class="btn btn-secondary">
                                Ver Archivos Subidos
                            </a>
                        </form>

                        <!-- Para AJAX upload (opcional) -->
                        <div class="mt-4">
                            <h5>Subida con AJAX</h5>
                            <div id="ajaxUpload">
                                <input type="file" id="ajaxFile" class="form-control">
                                <button onclick="uploadAjax()" class="btn btn-success mt-2">
                                    Subir con AJAX
                                </button>
                                <div id="ajaxResult" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function uploadAjax() {
            const fileInput = document.getElementById('ajaxFile');
            const resultDiv = document.getElementById('ajaxResult');

            if (!fileInput.files.length) {
                resultDiv.innerHTML = '<div class="alert alert-warning">Selecciona un archivo</div>';
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('user_email', 'usuario@ejemplo.com');

            resultDiv.innerHTML = '<div class="alert alert-info">Subiendo...</div>';

            fetch('<?= site_url("drive/api-upload") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <strong>¡Subido!</strong><br>
                        Archivo: ${data.data.file_name}<br>
                        <a href="${data.data.web_view_link}" target="_blank">Ver en Drive</a>
                    </div>
                `;
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error}</div>`;
                });
        }
    </script>
</body>

</html>