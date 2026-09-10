const url_base        = document.getElementById('url_base').value;
const contribuyenteId = document.getElementById('contribuyente').value;
const rucContribuyente = document.getElementById('ruc_contribuyente').value;
const razon_social    = document.getElementById('razon_social').value;
const ra              = razon_social.replace(/\./g, '');

const options = { year: 'numeric', month: 'long', day: 'numeric' };

const btn_venta     = document.getElementById('btn_venta');
const btn_maq_venta = document.getElementById('btn_maq_venta');
const cont          = document.getElementById('contentVentas');
const cabecera      = document.getElementById('cabecera_table');

// ── Reporte detallado de ventas (DataTable server-side) ─────────────────────
btn_venta.addEventListener('click', function (e) {
    e.preventDefault();

    const inicio   = document.getElementById('fecha_inicio_ventas').value;
    const fin      = document.getElementById('fecha_fin_ventas').value;
    const sucursal = document.getElementById('sucursal_venta').value;
    const cuenta   = document.getElementById('cuenta_ventas').value;
    const glosa    = document.getElementById('glosa_ventas').value;

    if (!inicio) { alert('Ingresar una fecha de inicio'); return; }
    if (!fin)    { alert('Ingresar una fecha de fin');    return; }

    cabecera.innerHTML = `
        <th>N°</th><th>FECHA</th><th>TIPO MONEDA</th><th>DOCUMENTO</th>
        <th>#_DOCUMENTO</th><th>CONDICION</th><th>RUC</th><th>RAZON SOCIAL</th>
        <th>EXONERADA</th><th>GRAVADA</th><th>INAFECTA</th><th>VVENTA</th>
        <th>VALOR VENTA</th><th>IGV</th><th>BOLSA</th><th>ICB</th><th>TOTAL</th>
        <th>TIPO_CAMBIO</th><th>GLOSA</th><th>CUENTA</th><th>AFECTACION</th>
        <th>CONDICION DEL CONTRIBUYENTE</th><th>ESTADO DEL CONTRIBUYENTE</th>
        <th>ESTADO SUNAT</th><th>REFERENCIA</th><th>FECHA REFERENCIA</th>
    `;
    cont.innerHTML = '';

    if ($.fn.DataTable.isDataTable('#data_venta')) {
        $('#data_venta').DataTable().destroy();
    }

    const withSign = (r, field) => {
        const s = r.id_tipodoc_electronico === '07' ? -1 : 1;
        return (s * parseFloat(r[field] || 0)).toFixed(2);
    };

    const calcValorVenta = (r) => {
        const icb = parseFloat(r.total_icbper || 0);
        const sub = parseFloat(r.sub_total    || 0);
        const val = icb > 0 ? (sub - icb) : sub;
        const s   = r.id_tipodoc_electronico === '07' ? -1 : 1;
        return (s * val).toFixed(2);
    };

    $('#data_venta').DataTable({
        serverSide:  true,
        processing:  true,
        ordering:    false,
        lengthMenu:  [[10, 50, 100], [10, 50, 100]],
        pageLength:  10,
        dom:         'Bfrtip',
        language:    { processing: 'Cargando...' },
        ajax: {
            url:  url_base + 'contribuyente/reporte-venta',
            type: 'POST',
            data: function (d) {
                d.sucursal      = sucursal;
                d.inicio        = inicio;
                d.fin           = fin;
                d.contribuyente = contribuyenteId;
                d.ruc           = rucContribuyente;
            },
            error: function () {
                alert('Ocurrió un error al consultar los datos');
            }
        },
        columns: [
            { data: null,                 render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
            { data: 'fecha_comprobante',  defaultContent: '' },
            { data: 'id_codigomoneda',    defaultContent: 'S' },
            { data: 'descripcion',        defaultContent: '' },
            { data: null,                 render: (d, t, r) => (r.serie_comprobante ?? '') + '-' + (r.numero_comprobante ?? '') },
            { data: null,                 render: (d, t, r) => r.estado_envio_sunat === 'anulado' ? 'I' : 'A' },
            { data: 'num_doc',            defaultContent: '' },
            { data: 'razon_social',       defaultContent: '' },
            { data: null,                 render: (d, t, r) => withSign(r, 'total_exoneradas') },
            { data: null,                 render: (d, t, r) => withSign(r, 'total_gravadas') },
            { data: null,                 render: (d, t, r) => withSign(r, 'total_inafecta') },
            { data: null,                 render: (d, t, r) => calcValorVenta(r) },
            { data: null,                 render: (d, t, r) => calcValorVenta(r) },
            { data: null,                 render: (d, t, r) => withSign(r, 'total_igv') },
            { data: null,                 defaultContent: '0.00' },
            { data: null,                 render: (d, t, r) => withSign(r, 'total_icbper') },
            { data: null,                 render: (d, t, r) => withSign(r, 'total') },
            { data: null, render: (d, t, r) => r.id_codigomoneda === 'PEN' ? '1' : (r.tipo_cambio_sunat || '1') },
            { data: null,                 render: () => glosa },
            { data: null,                 render: () => cuenta },
            { data: null,                 render: (d, t, r) => parseFloat(r.total_igv || 0) > 0 ? 'SI' : 'NO' },
            { data: null,                 defaultContent: 'HABIDO' },
            { data: null,                 defaultContent: 'ACTIVO' },
            { data: 'estado_envio_sunat', defaultContent: '' },
            { data: 'referencia',         defaultContent: '' },
            { data: 'fecha_referencia',   defaultContent: '' },
        ],
        buttons: [{
            text: 'Exportar Excel',
            className: 'btn btn-success btn-sm',
            action: function (e, dt, button) {
                button.attr('disabled', true).text('Exportando...');
                setTimeout(() => button.attr('disabled', false).text('Exportar Excel'), 4000);

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url_base + 'contribuyente/reporte-venta-excel';
                [
                    ['sucursal', sucursal], ['inicio', inicio], ['fin', fin],
                    ['contribuyente', contribuyenteId], ['ruc', rucContribuyente],
                    ['cuenta', cuenta], ['glosa', glosa],
                ].forEach(([k, v]) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = k; inp.value = v;
                    form.appendChild(inp);
                });
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }]
    }).buttons().container().appendTo('#data_venta_wrapper .col-md-6:eq(0)');
});

// ── Maqueta de ventas ────────────────────────────────────────────────────────
btn_maq_venta.addEventListener('click', function (e) {
    e.preventDefault();

    const inicio   = document.getElementById('fecha_inicio_ventas').value;
    const fin      = document.getElementById('fecha_fin_ventas').value;
    const sucursal = document.getElementById('sucursal_venta').value;
    const cuenta   = document.getElementById('cuenta_ventas').value;
    const glosa    = document.getElementById('glosa_ventas').value;

    if (!inicio) { alert('Ingresar una fecha de inicio'); return; }
    if (!fin)    { alert('Ingresar una fecha de fin');    return; }

    cabecera.innerHTML = `
        <th>N°</th><th>FECHA</th><th>TIPO MONEDA</th><th>DOCUMENTO</th>
        <th>#_DOCUMENTO</th><th>CONDICION</th><th>RUC</th><th>RAZON SOCIAL</th>
        <th>VVENTA</th><th>VALOR VENTA</th><th>ICB</th><th>BOLSA</th>
        <th>IGV</th><th>TOTAL</th><th>TIPO_CAMBIO</th><th>GLOSA</th>
        <th>CUENTA</th><th>TIPO</th><th>REFERENCIA</th><th>FECHAREF</th>
    `;
    cont.innerHTML = '';

    if ($.fn.DataTable.isDataTable('#data_venta')) {
        $('#data_venta').DataTable().destroy();
    }

    $('#data_venta').DataTable({
        serverSide:  true,
        processing:  true,
        ordering:    false,
        lengthMenu:  [[10, 50, 100], [10, 50, 100]],
        pageLength:  10,
        dom:         'Bfrtip',
        language:    { processing: 'Cargando...' },
        ajax: {
            url:  url_base + 'contribuyente/reporte-maqueta',
            type: 'POST',
            data: function (d) {
                d.sucursal      = sucursal;
                d.inicio        = inicio;
                d.fin           = fin;
                d.contribuyente = contribuyenteId;
                d.ruc           = rucContribuyente;
                d.cuenta        = cuenta;
                d.glosa         = glosa;
            },
            error: function () { alert('Ocurrió un error al consultar los datos'); }
        },
        columns: [
            { data: null,              render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
            { data: 'fecha',           defaultContent: '' },
            { data: 'tipo_moneda',     defaultContent: '' },
            { data: 'documento',       defaultContent: '' },
            { data: 'numero',          defaultContent: '' },
            { data: 'condicion',       defaultContent: 'A' },
            { data: 'ruc',             defaultContent: '' },
            { data: 'razon_social',    defaultContent: '' },
            { data: 'vventa',          defaultContent: '' },
            { data: 'valor_venta',     defaultContent: '' },
            { data: 'icb',             defaultContent: '' },
            { data: 'bolsa',           defaultContent: '0.00' },
            { data: 'igv',             defaultContent: '' },
            { data: 'total',           defaultContent: '' },
            { data: 'tipo_cambio',     defaultContent: '' },
            { data: 'glosa',           defaultContent: '' },
            { data: 'cuenta',          defaultContent: '' },
            { data: 'tipo',            defaultContent: '' },
            { data: 'referencia',      defaultContent: '' },
            { data: 'referenciafecha', defaultContent: '' },
        ],
        buttons: [{
            text: 'Exportar Excel',
            className: 'btn btn-success btn-sm',
            action: function (e, dt, button) {
                button.attr('disabled', true).text('Exportando...');
                setTimeout(() => button.attr('disabled', false).text('Exportar Excel'), 4000);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url_base + 'contribuyente/reporte-maqueta-excel';
                [
                    ['sucursal', sucursal], ['inicio', inicio], ['fin', fin],
                    ['contribuyente', contribuyenteId], ['ruc', rucContribuyente],
                    ['cuenta', cuenta], ['glosa', glosa],
                ].forEach(([k, v]) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = k; inp.value = v;
                    form.appendChild(inp);
                });
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }]
    }).buttons().container().appendTo('#data_venta_wrapper .col-md-6:eq(0)');
});
