const url_base   = document.getElementById('url').value;
const rucEmpresa = document.getElementById('ruc_empresa').value;
const shema      = document.getElementById('shema').value;
const idempresa  = document.getElementById('idempresa').value;

const options = { year: 'numeric', month: 'long', day: 'numeric' };

const btn_venta     = document.getElementById('btn_venta');
const btn_maq_venta = document.getElementById('btn_maq_venta');
const cont          = document.getElementById('contentVentas');
const cabecera      = document.getElementById('cabecera_table');

// ── Poblar sucursales al cargar ──────────────────────────────────────────────
(function loadSucursales() {
    const body = new URLSearchParams({ shema, idempresa });
    fetch(url_base + 'sucursales', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('sucursal_venta');
            select.innerHTML = '<option value="0">TODAS</option>';
            data.forEach(sede => {
                select.innerHTML += `<option value="${sede.sede_id}">${sede.sede_descripcion}</option>`;
            });
        })
        .catch(err => console.error('Error cargando sucursales:', err));
})();

function getParams() {
    return {
        sucursal:     document.getElementById('sucursal_venta').value,
        fecha_inicio: document.getElementById('fecha_inicio_ventas').value,
        fecha_fin:    document.getElementById('fecha_fin_ventas').value,
        cuenta:       document.getElementById('cuenta_ventas').value,
        glosa:        document.getElementById('glosa_ventas').value,
    };
}

// ── Reporte detallado ────────────────────────────────────────────────────────
btn_venta.addEventListener('click', function (e) {
    e.preventDefault();

    const { sucursal, fecha_inicio, fecha_fin, cuenta, glosa } = getParams();

    if (!fecha_inicio) { alert('Ingresar una fecha de inicio'); return; }
    if (!fecha_fin)    { alert('Ingresar una fecha de fin');    return; }

    cabecera.innerHTML = '';
    cont.innerHTML     = '';

    if ($.fn.DataTable.isDataTable('#data_venta')) {
        $('#data_venta').DataTable().destroy();
    }

    $('#cover-spin').show(0);

    const body = new URLSearchParams({ sucursal, fecha_inicio, fecha_fin, cuenta, glosa, ruc: rucEmpresa, shema });

    fetch(url_base + 'reporte-detallado', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
        .then(res => res.json())
        .then(data => {
            $('#cover-spin').hide(0);

            if (data.error) { alert('Error: ' + data.error); return; }

            cabecera.innerHTML = `
                <th>N°</th><th>FECHA</th><th>TIPO MONEDA</th><th>DOCUMENTO</th>
                <th>#_DOCUMENTO</th><th>CONDICION</th><th>RUC</th><th>RAZON SOCIAL</th>
                <th>EXONERADA</th><th>GRAVADA</th><th>INAFECTA</th><th>VVENTA</th>
                <th>VALOR VENTA</th><th>IGV</th><th>BOLSA</th><th>ICB</th><th>TOTAL</th>
                <th>TIPO_CAMBIO</th><th>GLOSA</th><th>CUENTA</th><th>AFECTACION</th>
                <th>CONDICION DEL CONTRIBUYENTE</th><th>ESTADO DEL CONTRIBUYENTE</th>
                <th>ESTADO SUNAT</th><th>REFERENCIA</th><th>FECHA REFERENCIA</th>
            `;

            let html = '';
            data.forEach((v, i) => {
                const condicion  = v.estado === 'f' ? 'I' : 'A';
                const afectacion = parseFloat(v.total_igv) > 0 ? 'SI' : 'NO';

                const valorVenta = parseFloat(v.total_icbper) > 0
                    ? (parseFloat(v.subtotal) - parseFloat(v.total_icbper)).toFixed(2)
                    : (v.subtotal ?? '0.00');

                html += `<tr>
                    <td>${i + 1}</td>
                    <td>${v.fecha ?? ''}</td>
                    <td>${v.tipo_moneda ?? 'S'}</td>
                    <td>${v.tico_descripcion ?? ''}</td>
                    <td>${v.numero_documento ?? ''}</td>
                    <td>${condicion}</td>
                    <td>${v.clie_numero_documento ?? ''}</td>
                    <td>${v.clie_nombre_razon_social ?? ''}</td>
                    <td>${v.total_exonerado ?? '0.00'}</td>
                    <td>${v.total_gravado ?? '0.00'}</td>
                    <td>${v.total_inafecto ?? '0.00'}</td>
                    <td>${valorVenta}</td>
                    <td>${valorVenta}</td>
                    <td>${v.total_igv ?? '0.00'}</td>
                    <td>0.00</td>
                    <td>${v.total_icbper ?? '0.00'}</td>
                    <td>${v.total ?? '0.00'}</td>
                    <td>1</td>
                    <td>${glosa}</td>
                    <td>${cuenta}</td>
                    <td>${afectacion}</td>
                    <td>HABIDO</td>
                    <td>ACTIVO</td>
                    <td>${v.homologacion_estado ?? ''}</td>
                    <td>${v.referencia ?? ''}</td>
                    <td>${v.fecha_referencia ?? ''}</td>
                </tr>`;
            });

            cont.innerHTML = html;

            const ini = new Date(fecha_inicio); ini.setDate(ini.getDate() + 1);
            const fin = new Date(fecha_fin);    fin.setDate(fin.getDate() + 1);
            const iniStr = ini.toLocaleDateString('es-ES', options);
            const finStr = fin.toLocaleDateString('es-ES', options);

            $('#data_venta').DataTable({
                ordering: false,
                lengthChange: false,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    title: `REPORTE DE VENTAS DETALLADO - ${iniStr} AL ${finStr}`,
                }],
            }).buttons().container().appendTo('#data_venta_wrapper .col-md-6:eq(0)');
        })
        .catch(err => {
            $('#cover-spin').hide(0);
            console.error(err);
            alert('Ocurrió un error al consultar los datos');
        });
});

// ── Maqueta de ventas ────────────────────────────────────────────────────────
btn_maq_venta.addEventListener('click', function (e) {
    e.preventDefault();

    const { sucursal, fecha_inicio, fecha_fin, cuenta, glosa } = getParams();

    if (!fecha_inicio) { alert('Ingresar una fecha de inicio'); return; }
    if (!fecha_fin)    { alert('Ingresar una fecha de fin');    return; }

    cabecera.innerHTML = '';
    cont.innerHTML     = '';

    if ($.fn.DataTable.isDataTable('#data_venta')) {
        $('#data_venta').DataTable().destroy();
    }

    $('#cover-spin').show(0);

    const body = new URLSearchParams({ sucursal, fecha_inicio, fecha_fin, cuenta, glosa, ruc: rucEmpresa, shema });

    fetch(url_base + 'maqueta-ventas', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
        .then(res => res.json())
        .then(data => {
            $('#cover-spin').hide(0);

            if (data.error) { alert('Error: ' + data.error); return; }

            cabecera.innerHTML = `
                <th>N°</th><th>FECHA</th><th>TIPO MONEDA</th><th>DOCUMENTO</th>
                <th>#_DOCUMENTO</th><th>CONDICION</th><th>RUC</th><th>RAZON SOCIAL</th>
                <th>VVENTA</th><th>VALOR VENTA</th><th>ICB</th><th>BOLSA</th>
                <th>IGV</th><th>TOTAL</th><th>TIPO_CAMBIO</th><th>GLOSA</th>
                <th>CUENTA</th><th>TIPO</th><th>REFERENCIA</th><th>FECHAREF</th>
            `;

            let html = '';
            data.forEach((row, i) => {
                let numRuc = row.ruc ?? '';
                if (numRuc === '00000000') numRuc = '00000001';
                html += `<tr>
                    <td>${i + 1}</td>
                    <td>${row.fecha ?? ''}</td>
                    <td>${row.tipo_moneda ?? 'S'}</td>
                    <td>${row.documento ?? ''}</td>
                    <td>${row.numero ?? ''}</td>
                    <td>${row.condicion ?? 'A'}</td>
                    <td>${numRuc}</td>
                    <td>${row.razon_social ?? ''}</td>
                    <td>${row.vventa ?? ''}</td>
                    <td>${row.valor_venta ?? ''}</td>
                    <td>${row.icb ?? ''}</td>
                    <td>${row.bolsa ?? '0.00'}</td>
                    <td>${row.igv ?? ''}</td>
                    <td>${row.total ?? ''}</td>
                    <td>${row.tipo_cambio ?? '1'}</td>
                    <td>${row.glosa ?? ''}</td>
                    <td>${row.cuenta ?? ''}</td>
                    <td>${row.tipo ?? ''}</td>
                    <td>${row.referencia ?? ''}</td>
                    <td>${row.referenciafecha ?? ''}</td>
                </tr>`;
            });

            cont.innerHTML = html;

            const ini = new Date(fecha_inicio); ini.setDate(ini.getDate() + 1);
            const fin = new Date(fecha_fin);    fin.setDate(fin.getDate() + 1);
            const iniStr = ini.toLocaleDateString('es-ES', options);
            const finStr = fin.toLocaleDateString('es-ES', options);

            $('#data_venta').DataTable({
                ordering: false,
                lengthChange: false,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excel',
                    filename: `MAQUETA DE VENTAS - ${iniStr} AL ${finStr}`,
                    title: '',
                    autoFilter: true,
                }],
            }).buttons().container().appendTo('#data_venta_wrapper .col-md-6:eq(0)');
        })
        .catch(err => {
            $('#cover-spin').hide(0);
            console.error(err);
            alert('Ocurrió un error al consultar los datos');
        });
});
