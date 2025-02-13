const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    showConfirmButton: true,
    buttonsStyling: false
});

const tableBody = document.getElementById('tableBody');
const titleModalArchivo = document.getElementById('titleModalArchivo');
const formArchivo = document.getElementById('formArchivo');
const rucEmp = document.getElementById('rucEmp');

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
                </div>
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

function modalArchivo(id) {
    $('#modalArchivo').modal('show');
    //$('#idContribuyente').val(id);

    formArchivo.reset();

    fetch(base_url + "contribuyentes/getId/"+ id)
    .then(res => res.json())
    .then(data => {
        titleModalArchivo.textContent = "SUBIR ARCHIVOS - "+data.razon_social;
        rucEmp.value = data.ruc;
    })
}

function descargarArchivos(id) {
    $("#modalDescargarArchivo").modal("show");
}

formArchivo.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formArchivo);

    fetch(base_url+"boleta-pago-save", {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            $('#modalArchivo').modal('hide');
            Swal.fire({
                position: 'top-center',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }

        $('#modalArchivo').modal('hide');

        swalWithBootstrapButtons.fire({
            title: 'Error!',
            text: data.message,
            icon: 'error'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log("El usuario hizo clic en OK");
                $('#modalArchivo').modal('show');
                // Aquí puedes realizar cualquier acción adicional
            }
        });
        
    })
})