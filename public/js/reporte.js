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

// ── Reporte detallado de ventas ──────────────────────────────────────────────
btn_venta.addEventListener('click', function (e) {
    e.preventDefault();

    const inicio   = document.getElementById('fecha_inicio_ventas').value;
    const fin      = document.getElementById('fecha_fin_ventas').value;
    const sucursal = document.getElementById('sucursal_venta').value;
    const cuenta   = document.getElementById('cuenta_ventas').value;
    const glosa    = document.getElementById('glosa_ventas').value;

    if (!inicio) { alert('Ingresar una fecha de inicio'); return; }
    if (!fin)    { alert('Ingresar una fecha de fin');    return; }

    cabecera.innerHTML = '';
    cont.innerHTML     = '';

    if ($.fn.DataTable.isDataTable('#data_venta')) {
        $('#data_venta').DataTable().destroy();
    }

    $('#cover-spin').show(0);

    const body = new URLSearchParams({ sucursal, inicio, fin, contribuyente: contribuyenteId, ruc: rucContribuyente });

    fetch(url_base + 'contribuyente/reporte-venta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
        .then((res) => res.json())
        .then((data) => {
            $('#cover-spin').hide(0);

            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            cabecera.innerHTML = `
                <th>N°</th>
                <th>FECHA</th>
                <th>TIPO MONEDA</th>
                <th>DOCUMENTO</th>
                <th>#_DOCUMENTO</th>
                <th>CONDICION</th>
                <th>RUC</th>
                <th>RAZON SOCIAL</th>
                <th>EXONERADA</th>
                <th>GRAVADA</th>
                <th>INAFECTA</th>
                <th>VVENTA</th>
                <th>VALOR VENTA</th>
                <th>IGV</th>
                <th>BOLSA</th>
                <th>ICB</th>
                <th>TOTAL</th>
                <th>TIPO_CAMBIO</th>
                <th>GLOSA</th>
                <th>CUENTA</th>
                <th>AFECTACION</th>
                <th>CONDICION DEL CONTRIBUYENTE</th>
                <th>ESTADO DEL CONTRIBUYENTE</th>
                <th>ESTADO SUNAT</th>
                <th>REFERENCIA</th>
                <th>FECHA REFERENCIA</th>
            `;

            let html = '';

            data.forEach((venta, index) => {
                // Nota de crédito (07) → valores negativos
                const esNotaCredito = venta.id_tipodoc_electronico === '07';
                const sign          = esNotaCredito ? '-' : '';
                const afectacion    = parseFloat(venta.total_igv) > 0 ? 'SI' : 'NO';

                let numDoc = venta.num_doc ?? '';
                if (numDoc === '00000000') numDoc = '00000001';

                // sub_total en el facturador incluye ICBper cuando hay bolsas plásticas
                const valorVenta = parseFloat(venta.total_icbper) > 0
                    ? (parseFloat(venta.sub_total) - parseFloat(venta.total_icbper)).toFixed(2)
                    : (venta.sub_total ?? '0.00');

                html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${venta.fecha_comprobante ?? ''}</td>
                    <td>${venta.id_codigomoneda ?? ''}</td>
                    <td>${venta.descripcion ?? ''}</td>
                    <td>${(venta.serie_comprobante ?? '') + '-' + (venta.numero_comprobante ?? '')}</td>
                    <td>A</td>
                    <td>${numDoc}</td>
                    <td>${venta.razon_social ?? ''}</td>
                    <td>${sign}${venta.total_exoneradas ?? '0.00'}</td>
                    <td>${sign}${venta.total_gravadas ?? '0.00'}</td>
                    <td>${sign}${venta.total_inafecta ?? '0.00'}</td>
                    <td>${sign}${valorVenta}</td>
                    <td>${sign}${valorVenta}</td>
                    <td>${sign}${venta.total_igv ?? '0.00'}</td>
                    <td>0.00</td>
                    <td>${sign}${venta.total_icbper ?? '0.00'}</td>
                    <td>${sign}${venta.total ?? '0.00'}</td>
                    <td>${venta.tipo_cambio_sunat ?? '1'}</td>
                    <td>${glosa}</td>
                    <td>${cuenta}</td>
                    <td>${afectacion}</td>
                    <td>HABIDO</td>
                    <td>ACTIVO</td>
                    <td>${venta.estado_envio_sunat ?? ''}</td>
                    <td>${venta.referencia ?? ''}</td>
                    <td>${venta.fecha_referencia ?? ''}</td>
                </tr>`;
            });

            cont.innerHTML = html;

            const ini_venta = new Date(inicio);
            const f_venta   = new Date(fin);
            ini_venta.setDate(ini_venta.getDate() + 1);
            f_venta.setDate(f_venta.getDate() + 1);

            const ini_str = ini_venta.toLocaleDateString('es-ES', options);
            const fin_str = f_venta.toLocaleDateString('es-ES', options);

            $('#data_venta')
                .DataTable({
                    ordering: false,
                    lengthChange: false,
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            title: 'REPORTE DE VENTAS DETALLADO ' + ra + ' - ' + ini_str + ' AL ' + fin_str,
                        },
                    ],
                })
                .buttons()
                .container()
                .appendTo('#data_venta_wrapper .col-md-6:eq(0)');
        })
        .catch((err) => {
            $('#cover-spin').hide(0);
            console.error(err);
            alert('Ocurrió un error al consultar los datos');
        });
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

    cabecera.innerHTML = '';
    cont.innerHTML     = '';

    if ($.fn.DataTable.isDataTable('#data_venta')) {
        $('#data_venta').DataTable().destroy();
    }

    $('#cover-spin').show(0);

    const body = new URLSearchParams({
        sucursal, inicio, fin, cuenta, glosa,
        contribuyente: contribuyenteId,
        ruc: rucContribuyente,
    });

    fetch(url_base + 'contribuyente/reporte-maqueta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
        .then((res) => res.json())
        .then((data) => {
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
                    <td>${row.tipo_moneda ?? ''}</td>
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
                    <td>${row.tipo_cambio ?? ''}</td>
                    <td>${row.glosa ?? ''}</td>
                    <td>${row.cuenta ?? ''}</td>
                    <td>${row.tipo ?? ''}</td>
                    <td>${row.referencia ?? ''}</td>
                    <td>${row.referenciafecha ?? ''}</td>
                </tr>`;
            });

            cont.innerHTML = html;

            const ini_venta = new Date(inicio);
            const f_venta   = new Date(fin);
            ini_venta.setDate(ini_venta.getDate() + 1);
            f_venta.setDate(f_venta.getDate() + 1);

            const ini_str = ini_venta.toLocaleDateString('es-ES', options);
            const fin_str = f_venta.toLocaleDateString('es-ES', options);

            $('#data_venta')
                .DataTable({
                    ordering: false,
                    lengthChange: false,
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            filename: 'MAQUETA DE VENTAS ' + ra + ' - ' + ini_str + ' AL ' + fin_str,
                            title: '',
                            autoFilter: true,
                        },
                    ],
                })
                .buttons()
                .container()
                .appendTo('#data_venta_wrapper .col-md-6:eq(0)');
        })
        .catch((err) => {
            $('#cover-spin').hide(0);
            console.error(err);
            alert('Ocurrió un error al consultar los datos');
        });
});
