<li class="pc-item pc-caption">
    <label>Navegación</label>
</li>
<li class="pc-item">
    <a href="<?= base_url('home') ?>" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-home"></use>
            </svg>
        </span>
        <span class="pc-mtext">Inicio</span>
    </a>
</li>

<?php foreach ($menu as $key => $value) { ?>

    <?php

    $hijos = "";
    foreach ($value['hijos'] as $key1 => $hijo) {
        $hijos .= "<li class='pc-item'><a class='pc-link' href='" . base_url($hijo['url']) . "'>" . $hijo['nombre'] . "</a></li>";
    }

    ?>

    <li class="pc-item pc-hasmenu">
        <a href="javascript:void(0);" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#<?= $value['modulo_padre_icono'] ?>"></use>
                </svg>
            </span>
            <span class="pc-mtext"><?= $value['modulo_padre_nombre'] ?></span>
            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
        </a>
        <ul class="pc-submenu">
            <?= $hijos ?>
        </ul>
    </li>

<?php } ?>