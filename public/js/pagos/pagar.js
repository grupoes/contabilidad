const tableBody = document.getElementById('tableBody');
const idContribuyente = document.getElementById('idcontribuyente')

renderPagos(idContribuyente.value);

function renderPagos(idcontribuyente) {
    fetch(base_url+"pagos/lista-pagos/"+idcontribuyente)
    .then(res => res.json())
    .then(data => {
        viewPagos(data);
        
    }) 
}

function viewPagos(data) {
    let html = "";

    data.forEach(pago => {
        
        let estado = `<span class="badge bg-light-danger f-12">${pago.estado}</span>`;
        
        if(pago.estado == 'Pagado') {
            estado = `<span class="badge bg-light-success f-12">${pago.estado}</span>`;
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