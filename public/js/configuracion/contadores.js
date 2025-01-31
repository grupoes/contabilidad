const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

const tableBody = document.getElementById('tableBody');
const btnModal = document.getElementById('btnModal');
const titleModal = document.getElementById('titleModal');

renderContadores();

function renderContadores() {
    fetch(`${base_url}configuracion/render-contadores`)
    .then(res => res.json())
    .then(data => {
        viewContadores(data)
        
    })
}

function viewContadores(data) {
    let html = "";

    data.forEach((contador, index) => {

        let check_radio = "";

        if(contador.estado == 2) {
            check_radio = 'checked';
        }

        html += `
        <tr>
            <td>${index + 1}</td>
            <td>${contador.nombre_apellidos}</td>
            <td>${contador.dni}</td>
            <td>${contador.numero_colegiatura}</td>
            <td>
                <div class="form-check">
                    <input class="form-check-input" type="radio" onclick="elegirContador(${contador.id_contador})" name="elegir" id="elegir-${contador.id_contador}" value="${contador.id_contador}" ${check_radio}>
                </div>
            </td>
            <td>
                <ul class="list-inline me-auto mb-0">
                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Editar">
                        <a href="ecom_product-add.html" class="avtar avtar-xs btn-link-success btn-pc-default"><i class="ti ti-edit-circle f-18"></i></a>
                    </li>
                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Eliminar">
                        <a href="#" class="avtar avtar-xs btn-link-danger btn-pc-default"><i class="ti ti-trash f-18"></i></a>
                    </li>
                </ul>
            </td>
        </tr>
        `;
    });

    tableBody.innerHTML = html;
}

function elegirContador(id) {
    fetch(`${base_url}configuracion/elegir-contador/${id}`)
    .then(res => res.json())
    .then(data => {

        if(data.status === 'success') {
            renderContadores();

            notifier.show('¡Bien hecho!', data.message, 'success', '', 2000);
        }
        
    })
}

btnModal.addEventListener('click', (e) => {
    $('#modalContadores').modal('show');
    titleModal.textContent = "Agregar Contador";
})