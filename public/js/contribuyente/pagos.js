const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

const tableBody = document.querySelector("#tableBody");

renderPagos();

function renderPagos() {
    fetch(`${base_url}pagos/renderPagos`)
    .then(res => res.json())
    .then(data => {
        
        viewPagos(data);
        
    })
}

function viewPagos(data) {
    let html = "";

    data.forEach((pago, index) => {
        html += `
        <tr>
            <td>${index + 1}</td>
            <td>${pago.razon_social}</td>
            <td>${pago.fecha_pago}</td>
            <td>${pago.montoPagado}</td>
            <td>${pago.metodo}</td>
            <td>
                <button type="button" class="btn btn-info">Recepcionar</button>
            </td>
        </tr>
        `;
    });

    tableBody.innerHTML = html;
}

