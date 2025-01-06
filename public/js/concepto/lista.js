const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

const tableBody = document.getElementById('tableBody');

renderConceptos();

function renderConceptos()
{
    fetch(base_url+"render-conceptos")
    .then(res => res.json())
    .then(data => {
        viewConceptos(data);
        
    })
}

function viewConceptos(data) {
    let html = "";

    data.forEach((concepto, index) => {

        let opciones = "";

        if(concepto.con_id > 4) {
            opciones = `<button type="button" class="btn btn-info">MODIFICAR</button> <button type="button" class="btn btn-danger">ELIMINAR</button>`;
        }

        html += `
        <tr>
            <td>${index + 1}</td>
            <td>${concepto.con_descripcion}</td>
            <td>${concepto.tipo_movimiento_descripcion}</td>
            <td>
                ${opciones}
            </td>
        </tr>
        `;
    });

    $($table).DataTable().destroy();

    tableBody.innerHTML = html;

    const newcs = $($table).DataTable(
        optionsTableDefault
    );
    
    new $.fn.dataTable.Responsive(newcs);
}