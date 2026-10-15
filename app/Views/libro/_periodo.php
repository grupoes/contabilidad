<?php
// Selector de periodo (00 Apertura ... 14 Cierre + año). Params: $pid (prefijo de ids), $vacio (bool)
$nombres = ['Apertura', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre', 'Cierre 13', 'Cierre 14'];
$vacio = $vacio ?? true;
?>
<div class="input-group">
    <select class="form-select" id="<?= $pid ?>Mes">
        <?php if ($vacio) { ?><option value="">Periodo...</option><?php } ?>
        <?php foreach ($nombres as $i => $n) { ?>
            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= (!$vacio && $i == date('n')) ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?> · <?= $i >= 13 ? 'Cierre' : $n ?></option>
        <?php } ?>
    </select>
    <input type="number" class="form-control" id="<?= $pid ?>Anio" min="2000" max="2100" placeholder="Año" value="<?= $vacio ? '' : date('Y') ?>" style="max-width: 100px;">
</div>
