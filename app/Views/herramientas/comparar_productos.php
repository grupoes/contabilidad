<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comparar Productos</title>
    <link href="<?= base_url() ?>reportes/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .match-exact   { background: #d4edda; }
        .match-starts  { background: #fff3cd; }
        .match-fuzzy   { background: #f8d7da; }
        .match-none    { background: #f8d7da; }
        td, th { white-space: nowrap; }
        td:nth-child(2), td:nth-child(3) { white-space: normal; max-width: 400px; }
    </style>
</head>
<body class="p-4">
    <h5>Comparación ingresados.xlsx vs <code>producto.nombre</code> (facturador)</h5>
    <p class="text-muted mb-3">
        Total filas Excel: <strong><?= count($resultados) ?></strong> &nbsp;|&nbsp;
        <span class="badge bg-success">Verde = exact / starts_with bueno</span>&nbsp;
        <span class="badge bg-warning text-dark">Amarillo = starts_with / fuzzy ≥70%</span>&nbsp;
        <span class="badge bg-danger">Rojo = fuzzy &lt;70% / sin match</span>
    </p>
    <table class="table table-bordered table-sm" style="font-size:0.8rem;">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>DESCRIPCION EXCEL</th>
                <th>MEJOR COINCIDENCIA DB</th>
                <th>IDPRODUCTO</th>
                <th>%</th>
                <th>TIPO</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($resultados as $i => $r): ?>
            <?php
                $cls = match ($r['match_type']) {
                    'exact'        => 'match-exact',
                    'core_exact'   => 'match-exact',
                    'prefix_match' => 'match-starts',
                    'fuzzy'        => ($r['score'] >= 70 ? 'match-starts' : 'match-fuzzy'),
                    default        => 'match-none',
                };
            ?>
            <tr class="<?= $cls ?>">
                <td><?= $i + 1 ?></td>
                <td><?= esc($r['excel']) ?></td>
                <td><?= esc($r['db_match']) ?></td>
                <td><?= $r['db_id'] ?? '' ?></td>
                <td><?= $r['score'] ?>%</td>
                <td><?= $r['match_type'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
