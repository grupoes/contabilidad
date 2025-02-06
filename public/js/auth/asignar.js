// Función para mover elementos seleccionados
function moverSeleccionados(origen, destino) {
    let listaOrigen = document.getElementById(origen);
    let listaDestino = document.getElementById(destino);
    
    [...listaOrigen.selectedOptions].forEach(option => {
        listaDestino.appendChild(option);
    });
}

// Función para mover todos los elementos
function moverTodos(origen, destino) {
    let listaOrigen = document.getElementById(origen);
    let listaDestino = document.getElementById(destino);

    [...listaOrigen.options].forEach(option => {
        listaDestino.appendChild(option);
    });
}

// Función para filtrar opciones en tiempo real
function filtrarLista(inputId, listaId) {
    let filtro = document.getElementById(inputId).value.toLowerCase();
    let opciones = document.getElementById(listaId).options;

    for (let i = 0; i < opciones.length; i++) {
        let texto = opciones[i].text.toLowerCase();
        opciones[i].style.display = texto.includes(filtro) ? '' : 'none';
    }
}

// Agregar eventos a los inputs de búsqueda
document.getElementById('buscar1').addEventListener('input', function() {
    filtrarLista('buscar1', 'lista1');
});
document.getElementById('buscar2').addEventListener('input', function() {
    filtrarLista('buscar2', 'lista2');
});

const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    showConfirmButton: true,
    buttonsStyling: false
});

const usuarios = document.getElementById('usuarios');

const lista1 = document.getElementById('lista1');
const lista2 = document.getElementById('lista2');

usuarios.addEventListener('change', (e) => {
    const valor = e.target.value;

    fetch(base_url+"auth/asignar/"+valor)
    .then(res => res.json())
    .then(data => {
        
        let asig = "";

        let asignados = data.asignados;

        asignados.forEach(asignado => {
            asig += `<option value="${asignado.id}">${asignado.razon_social}</option>`;
        });

        lista2.innerHTML = asig;

        let no_asig = "";

        let no_asignados = data.no_asignados;

        no_asignados.forEach(no_asignado => {
            no_asig += `<option value="${no_asignado.id}">${no_asignado.razon_social}</option>`;
        });

        lista1.innerHTML = no_asig;
        
    })
})

const formAsignar = document.getElementById('formAsignar');

formAsignar.addEventListener('submit', (e) => {
    e.preventDefault();

    let listaDerecha = document.querySelectorAll('#lista2 option');
    
    listaDerecha.forEach(option => {
        option.selected = true;
    });

    const formData = new FormData(formAsignar);

    swalWithBootstrapButtons
        .fire({
            title: '¿Seguro desea asignar?',
            text: "¡No podrá revertir después!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, asignar!',
            cancelButtonText: 'No, cancelar!',
            reverseButtons: true
        })
        .then((result) => {
            if (result.isConfirmed) {
                
                fetch(base_url+"save-asignar", {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    
                    if(data.status === 'success') {
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

    
})