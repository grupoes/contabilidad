const tableBody = document.getElementById('tableBody');
const idContribuyente = document.getElementById('idcontribuyente')

const formPago = document.getElementById('formPago');

renderPagos(idContribuyente.value);
renderPagosHonorarios(idContribuyente.value);

function renderPagos(idcontribuyente) {
    fetch(base_url+"pagos/lista-pagos/"+idcontribuyente)
    .then(res => res.json())
    .then(data => {
        viewPagos(data);
        
    }) 
}

function renderPagosHonorarios(idcontribuyente) {
    fetch(base_url+"pagos/lista-pagos-honorarios/"+idcontribuyente)
    .then(res => res.json())
    .then(data => {
        viewPagosHonorarios(data);
        
    })
}

function viewPagos(data) {
    let html = "";

    data.forEach(pago => {
        
        let estado = ``;
        
        if(pago.estado == 'Pagado') {
            estado = `<span class="badge bg-light-success f-12">${pago.estado}</span>`;
        } else if(pago.estado == 'Pendiente') {
            estado = `<span class="badge bg-light-warning f-12">${pago.estado}</span>`;
        } else {
            estado = `<span class="badge bg-light-danger f-12">${pago.estado}</span>`;
        }

        html += `
        <tr>
            <td>${pago.mesCorrespondiente}</td>
            <td>${pago.fecha_pago}</td>
            <td>${pago.monto_total}</td>
            <td>${pago.montoPagado}</td>
            <td>${pago.montoPendiente}</td>
            <td>${pago.montoExcedente}</td>
            <td>${estado}</td>
        </tr>
        `;
    });

    tableBody.innerHTML = html;
}

function viewPagosHonorarios(data) {
    const tablePagos = document.getElementById('tablePagos');

    let html = "";

    data.forEach(pago => {
        html += `
        <tr>
            <td>${pago.registro}</td>
            <td>${pago.fecha_pago}</td>
            <td>${pago.metodo}</td>
            <td>${pago.monto}</td>
            <td></td>
        </tr>
        `;
    });

    tablePagos.innerHTML = html;
}

const metodoPago = document.getElementById('metodoPago');

formPago.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formPago);

    fetch(`${base_url}pagos/pagar-honorario`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        
        if (data.status === 'success') {
            metodoPago.value = "";

            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            });

            renderPagos(idContribuyente.value);
            renderPagosHonorarios(idContribuyente.value);
            return false;
        }

        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!'
        });
        
    })
})