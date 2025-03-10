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
<li class="pc-item pc-hasmenu">
    <a href="javascript:void(0);" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-setting-2"></use>
            </svg>
        </span>
        <span class="pc-mtext">Configuración</span>
        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="<?= base_url('configuracion/uit') ?>">UIT</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('configuracion/renta') ?>">Renta</a></li>
        <li class="pc-item"><a class="pc-link" href="#">Notificación</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('configuracion/contadores') ?>">Contador</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('configuracion/caja-virtual') ?>">Caja Virtual</a></li>
    </ul>
</li>
<li class="pc-item pc-hasmenu">
    <a href="javascript:void(0);" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-layer"></use>
            </svg>
        </span>
        <span class="pc-mtext">Declaraciones</span>
        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="<?= base_url('declaraciones/pdt-0621') ?>">PDT 0621</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('declaraciones/pdt-plame') ?>">PDT PLAME</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('declaraciones/pdt-anual') ?>">PDT ANUAL</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('declaraciones/boleta-de-pago') ?>">BOLETA DE PAGO</a></li>
    </ul>
</li>
<li class="pc-item pc-hasmenu">
    <a href="javascript:void(0);" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-dollar-square"></use>
            </svg>
        </span>
        <span class="pc-mtext">Caja</span>
        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="<?= base_url('cobros') ?>">Cobros</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('conceptos') ?>">Concepto</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('caja-diaria') ?>">Caja Diaria</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('movimientos') ?>">Movimiento</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('metodos-de-pago') ?>">Métodos de Pago</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('bancos') ?>">Bancos</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('movimiento-bancos') ?>">Movimientos Bancos</a></li>
    </ul>
</li>
<li class="pc-item pc-hasmenu">
    <a href="javascript:void(0);" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-lock-outline"></use>
            </svg>
        </span>
        <span class="pc-mtext">Seguridad</span>
        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="#">Módulos</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('permisos') ?>">Permisos</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('usuarios') ?>">Usuario</a></li>
        <li class="pc-item"><a class="pc-link" href="#">Perfil</a></li>
        <li class="pc-item"><a class="pc-link" href="<?= base_url('asignar-contribuyentes') ?>">Asignar</a></li>
    </ul>
</li>
<li class="pc-item pc-hasmenu">
    <a href="javascript:void(0);" class="pc-link">
        <span class="pc-micon">
            <svg class="pc-icon">
                <use xlink:href="#custom-status-up"></use>
            </svg>
        </span>
        <span class="pc-mtext">Contabilidad</span>
        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
    </a>
    <ul class="pc-submenu">
        <li class="pc-item"><a class="pc-link" href="<?= base_url('contribuyentes') ?>">Contribuyentes</a></li>
        <li class="pc-item"><a class="pc-link" href="#">Declaración</a></li>
        <li class="pc-item"><a class="pc-link" href="#">Cargo</a></li>
        <li class="pc-item"><a class="pc-link" href="#">Generar Guia</a></li>
        <li class="pc-item"><a class="pc-link" href="#">Guia de Arrendamiento</a></li>
        <li class="pc-item"><a class="pc-link" href="#">Libros Electrónicos</a></li>
    </ul>
</li>