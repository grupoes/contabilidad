document.addEventListener('DOMContentLoaded', function() {
    const btnOpenAmortizar = document.getElementById('btnOpenAmortizar');
    const modalAmObject = document.getElementById('modalAmortizar');
    const modalAmortizar = new bootstrap.Modal(modalAmObject);
    const formAmortizar = document.getElementById('formAmortizar');
    const inputMonto = document.getElementById('monto_amortizar');
    const labelTotalPendiente = document.getElementById('total_pendiente_label');
    const selectMetodo = document.getElementById('metodo_pago_id');
    const viewVoucher = document.getElementById('viewVoucher');

    // Voucher modal components
    const modalVoucherObj = document.getElementById('modalVoucher');
    const modalVoucher = new bootstrap.Modal(modalVoucherObj);
    const voucherImage = document.getElementById('voucherImage');
    const voucherPdfCont = document.getElementById('voucherPdf');
    const voucherIframe = document.getElementById('voucherIframe');
    const btnDownloadVoucher = document.getElementById('btnDownloadVoucher');

    function initViewEvents() {
        // Re-attach view voucher events as table body changes
        document.querySelectorAll('.btnViewVoucher').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const url = this.getAttribute('data-url');
                const metodo = this.getAttribute('data-metodo');

                document.getElementById('amorIdDetalle').value = id;
                document.getElementById('edit_metodo_pago_id').value = metodo;
                document.getElementById('editAmorContainer').style.display = 'none';

                voucherImage.style.display = 'none';
                voucherPdfCont.style.display = 'none';
                
                if (url) {
                    const fileExtension = url.split('.').pop().toLowerCase();
                    btnDownloadVoucher.style.display = 'inline-block';
                    btnDownloadVoucher.href = url;

                    if (fileExtension === 'pdf') {
                        voucherIframe.src = url;
                        voucherPdfCont.style.display = 'block';
                    } else {
                        voucherImage.src = url;
                        voucherImage.style.display = 'inline-block';
                    }
                } else {
                    btnDownloadVoucher.style.display = 'none';
                }

                modalVoucher.show();
            };
        });

        document.querySelectorAll('.btnDeleteAmortization').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const monto = parseFloat(this.getAttribute('data-monto')).toFixed(2);

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Se eliminará esta amortización por S/. ${monto}. Se restaurarán las cuotas correspondientes al estado pendiente.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        showLoader();
                        fetch(`${base_url}delete-amortization-service/${id}`)
                            .then(res => res.json())
                            .then(data => {
                                hideLoader();
                                if (data.status === 'success') {
                                    Swal.fire('¡Eliminado!', data.message, 'success');
                                    updateServiceView();
                                } else {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            })
                            .catch(err => {
                                hideLoader();
                                Swal.fire('Error', 'Ocurrió un error al procesar la solicitud', 'error');
                            });
                    }
                });
            };
        });
    }

    // Static events (not dynamic)
    document.getElementById('btnShowEditVoucher').onclick = function() {
        const container = document.getElementById('editAmorContainer');
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
    };

    const formEditAmor = document.getElementById('formEditAmor');
    if (formEditAmor) {
        formEditAmor.onsubmit = function(e) {
            e.preventDefault();
            const idAmor = document.getElementById('amorIdDetalle').value;
            
            if (!idAmor) {
                Swal.fire('Error', 'No se ha detectado el ID del registro a actualizar', 'error');
                return;
            }

            const formData = new FormData(this);
            
            showLoader();
            modalVoucher.hide(); 
            
            fetch(`${base_url}update-voucher-service`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                hideLoader();
                if (data.status === 'success') {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#4e73df'
                    });
                    updateServiceView();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(err => {
                hideLoader();
                Swal.fire('Error', 'No se pudo procesar la solicitud de actualización', 'error');
            });
        };
    }

    initViewEvents();

    if (btnOpenAmortizar) {
        btnOpenAmortizar.addEventListener('click', () => {
            modalAmortizar.show();
            labelTotalPendiente.innerHTML = `Pendiente Total: S/. ${globalTotalPendiente.toFixed(2)}`;
            inputMonto.value = firstPendingAmount.toFixed(2);
            inputMonto.max = globalTotalPendiente;
        });
    }

    selectMetodo.addEventListener('change', () => {
        const divSedeSelect = document.getElementById('divSedeSelect');
        if (selectMetodo.value == '1') {
            viewVoucher.style.display = 'none';
            document.getElementById('vaucher').removeAttribute('required');
            divSedeSelect.style.display = 'block';
        } else {
            viewVoucher.style.display = 'block';
            document.getElementById('vaucher').setAttribute('required', 'required');
            divSedeSelect.style.display = 'none';
        }
    });

    formAmortizar.addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(formAmortizar);
        const monto = parseFloat(formData.get('monto'));

        if (monto <= 0) {
            notifier.show('Error', 'El monto debe ser mayor a 0', 'danger');
            return;
        }

        if (monto > globalTotalPendiente) {
            notifier.show('Error', 'El monto supera el pendiente total', 'danger');
            return;
        }

        modalAmortizar.hide();

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se registrará una amortización por S/. ${monto.toFixed(2)}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#e74a3b',
            confirmButtonText: 'Sí, registrar pago',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
            backdrop: true
        }).then((result) => {
            if (result.isConfirmed) {
                showLoader();
                fetch(`${base_url}save-amortization`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    hideLoader();
                    if (data.status === 'success') {
                        // Modal already hidden
                        Swal.fire({
                            title: '¡Éxito!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#4e73df'
                        });
                        
                        formAmortizar.reset();
                        viewVoucher.style.display = 'none';
                        document.getElementById('divSedeSelect').style.display = 'none';

                        updateServiceView();
                    } else {
                        modalAmortizar.show();
                        notifier.show('Error', data.message, 'danger');
                    }
                })
                .catch(error => {
                    hideLoader();
                    modalAmortizar.show();
                    console.error('Error:', error);
                    notifier.show('Error', 'Ocurrió un error inesperado', 'danger');
                });
            } else {
                modalAmortizar.show();
            }
        });
    });

    function updateServiceView() {
        fetch(`${base_url}service-data/${serviceId}`)
            .then(res => res.json())
            .then(data => {
                // Update Global Total and first pending
                globalTotalPendiente = data.pagos.reduce((acc, curr) => acc + parseFloat(curr.monto_pendiente), 0);
                
                const firstPending = data.pagos.find(p => p.estado === 'pendiente');
                firstPendingAmount = firstPending ? parseFloat(firstPending.monto_pendiente) : 0;
                
                // Update Header
                const headerTotalPendiente = document.getElementById('headerTotalPendiente');
                if (headerTotalPendiente) {
                    headerTotalPendiente.innerHTML = `S/. ${formatNumber(globalTotalPendiente)}`;
                }
                
                // Update Status Badge
                const statusContainer = document.getElementById('serviceStatusContainer');
                if (data.service.estado === 'pagado') {
                    statusContainer.innerHTML = `<span class="badge badge-soft-success py-2 px-3"><i class="fas fa-check-circle me-1"></i> PAGADO</span>`;
                    document.getElementById('btnAmortizarContainer').innerHTML = '';
                } else {
                    statusContainer.innerHTML = `<span class="badge badge-soft-warning py-2 px-3"><i class="fas fa-clock me-1"></i> PENDIENTE</span>`;
                }

                // Update Cronograma Table
                const cronogramaBody = document.getElementById('cronogramaBody');
                let cronoHtml = '';
                data.pagos.forEach(p => {
                    cronoHtml += `
                        <tr class="hover-row">
                            <td>${formatDate(p.fecha_programacion)}</td>
                            <td class="fw-bold fs-6">S/. ${formatNumber(p.monto)}</td>
                            <td class="text-success small fw-bold">S/. ${formatNumber(p.monto_pagado)}</td>
                            <td class="text-danger small fw-bold">S/. ${formatNumber(p.monto_pendiente)}</td>
                            <td>
                                <span class="badge ${p.estado === 'pagado' ? 'badge-soft-success' : 'badge-soft-warning'}">${p.estado.toUpperCase()}</span>
                            </td>
                        </tr>
                    `;
                });
                cronogramaBody.innerHTML = cronoHtml;

                // Update Amortizaciones Table
                const amorBody = document.getElementById('amortizacionesBody');
                let amorHtml = '';
                if (data.amortizaciones.length === 0) {
                    amorHtml = '<tr><td colspan="5" class="text-center py-4 text-muted">No se registran amortizaciones.</td></tr>';
                } else {
                    data.amortizaciones.forEach((am, index) => {
                        amorHtml += `
                            <tr class="hover-row">
                                <td>${formatDateTime(am.registro)}</td>
                               <td>${formatDate(am.fecha_pago)}</td>
                                <td><span class="small badge bg-light text-dark fw-normal">${am.metodo_nombre}</span></td>
                                <td class="fw-bold text-success">S/. ${formatNumber(am.monto)}</td>
                                <td class="text-center d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-primary btnViewVoucher" data-id="${am.id}" data-url="${am.vaucher ? base_url + 'vouchers/' + am.vaucher : ''}" data-metodo="${am.metodo_pago_id}">
                                        <i class="fas ${am.vaucher ? 'fa-eye' : 'fa-edit'}"></i>
                                    </button>
                                    ${index === 0 && am.registro.split(' ')[0] === new Date().toLocaleDateString('en-CA') ? `
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger btnDeleteAmortization" data-id="${am.id}" data-monto="${am.monto}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    });
                }
                amorBody.innerHTML = amorHtml;
                
                // Re-bind events for new rows
                initViewEvents();
            });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr + 'T00:00:00');
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + 
               d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
});
