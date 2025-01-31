const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

const tableBody = document.getElementById('tableBody');

renderContribuyentes();

function renderContribuyentes() {
    fetch(`${base_url}contribuyentes/render`)
    .then(res => res.json())
    .then(data => {
        vistaContribuyentes(data);
        
    })
}

function vistaContribuyentes(data) {
    let html = "";

    data.forEach((cont, index) => {
        html += `
        <tr>
            <td>${index + 1}</td>
            <td>${cont.ruc}</td>
            <td>${cont.razon_social}</td>
            <td class="text-center">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <button type="button" class="btn btn-success" title="Subir archivos" onclick="modalArchivo(${cont.id})"> <i class="ti ti-file-upload"></i> </button> 
                    <button type="button" class="btn btn-info" title="Descargar archivos" onclick="descargarArchivos(${cont.id})"> <i class="ti ti-file-download"></i> </button> 
                    <button type="button" class="btn btn-primary" title="Descargar archivos" onclick="descargaMasiva(${cont.id})"> <i class="ti ti-file-export"></i> </button>
                </div>
            </td>
        </tr>
        `;
    });

    tableBody.innerHTML = html;
}

function modalArchivo(id) {
    $('#modalArchivo').modal('show');
    //$('#idContribuyente').val(id);
}

function descargarArchivos(id) {
    $("#modalDescargarArchivo").modal("show");
}

function descargaMasiva(id) {
    $("#modalDescargarArchivoMasivo").modal("show");
}