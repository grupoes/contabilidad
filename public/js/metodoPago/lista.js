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
const btnModal = document.getElementById('btnModal');
const titleModal = document.getElementById('titleModal');
const formMetodo = document.getElementById('formMetodo');
const idMetodo = document.getElementById('idMetodo');

const nameMetodo = document.getElementById('nameMetodo');
const banco = document.getElementById('banco');
const descripcion = document.getElementById('descripcion');

btnModal.addEventListener('click', () => {
    $("#modalMetodo").modal('show');
    titleModal.textContent = 'Agregar Método de Pago'
})

renderMetodos();

function renderMetodos() {
    fetch(base_url+"metodos/all")
    .then(res => res.json())
    .then(data => {
        viewMetodos(data);
        console.log(data);
        
    })
}

function viewMetodos(data) {
    let html = "";

    data.forEach((metodo, index) => {
        let nombreBanco = "";
        let opciones = "";

        if(metodo.nombre_banco != null) {
            nombreBanco = metodo.nombre_banco;
        }

        if(metodo.id != 1) {
            opciones = `
            <ul class="list-inline me-auto mb-0">
                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Editar">
                    <a href="#" onclick="editarMetodo(event, ${metodo.id})" class="avtar avtar-xs btn-link-success btn-pc-default"><i class="ti ti-edit-circle f-18"></i></a>
                </li>
                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Eliminar">
                    <a href="#" onclick="deleteMetodo(event, ${metodo.id})" class="avtar avtar-xs btn-link-danger btn-pc-default"><i class="ti ti-trash f-18"></i></a>
                </li>
            </ul>
            `;
        }

        html += `
        <tr>
            <td>${index + 1}</td>
            <td>${ metodo.metodo }</td>
            <td>${ nombreBanco }</td>
            <td>${ metodo.descripcion }</td>
            <td>
                ${opciones}
            </td>
        </tr>
        `;
    });

    $($table).DataTable().destroy()

    tableBody.innerHTML = html;

    const newcs = $($table).DataTable(
        optionsTableDefault
    );
    
    new $.fn.dataTable.Responsive(newcs);
}

formMetodo.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formMetodo);

    fetch(base_url+"metodo-pago/guardar", {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        
        if(data.status === 'success') {
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            });

            $("#modalMetodo").modal('hide');

            renderMetodos();

            return false;
        }

        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!'
        });
        
    })
})

function editarMetodo(e, id) {
    e.preventDefault();

    idMetodo.value = id;
    titleModal.textContent = "Editar Método de Pago";

    $("#modalMetodo").modal('show');

    fetch(base_url+"metodo-pago/get-metodo/"+id)
    .then(res => res.json())
    .then(data => {
        nameMetodo.value = data.metodo;
        banco.value = data.id_banco;
        descripcion.value = data.descripcion;
    })
}

function deleteMetodo(e, id) {
    e.preventDefault();

    swalWithBootstrapButtons
        .fire({
            title: '¿Seguro desea eliminarlo?',
            text: "¡No podrá revertir después!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar!',
            cancelButtonText: 'No, cancelar!',
            reverseButtons: true
        })
        .then((result) => {
            if (result.isConfirmed) {
                
                fetch(base_url+"metodo-pago/delete/"+id)
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        renderMetodos();
                        Swal.fire({
                            position: 'top-center',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        return false;
                    }

                    swalWithBootstrapButtons.fire('Error!', data.message, 'error');
                })
            }
        });
}
