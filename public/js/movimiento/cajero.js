const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

flatpickr(document.querySelector('#rango-fecha-movimientos'),{
    mode: "range",
    dateFormat: 'd-m-Y',
    defaultDate: getDefaultDate(),
    allowInput: true,
    /*onClose: function (selectedDates, dateStr, instance) {
        const selectedDate = instance.selectedDates[0];
        console.log("Fecha seleccionada:", selectedDate);
        renderVentas();
    }*/
});

const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    buttonsStyling: false
});

function getDefaultDate() {
    const today = new Date();
    const startDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30);
    const endDate = today;
    return [startDate, endDate];
}

const btnNuevoIngreso = document.getElementById('btnNuevoIngreso');
const btnNuevoEgreso = document.getElementById('btnNuevoEgreso');
const titleModalMovimiento = document.getElementById('titleModalMovimiento');
const conceptoCaja = document.getElementById('conceptoCaja');
const tipo_movimiento = document.getElementById('tipo_movimiento');

const formMovimiento = document.getElementById('formMovimiento');

const rangoFechaMovimientos = document.getElementById('rango-fecha-movimientos');
const tableBody = document.getElementById('tableBody');

btnNuevoIngreso.addEventListener('click', (e) => {
    $("#modalTipoMovimiento").modal('show');
    titleModalMovimiento.textContent = "AGREGAR UN INGRESO";
    tipo_movimiento.value = 1;

    formMovimiento.reset();

    conceptosTipoMoviemiento(1);
})

btnNuevoEgreso.addEventListener('click', (e) => {
    $("#modalTipoMovimiento").modal('show');
    titleModalMovimiento.textContent = "AGREGAR UN EGRESO";
    tipo_movimiento.value = 2;

    formMovimiento.reset();

    conceptosTipoMoviemiento(2);
})

function conceptosTipoMoviemiento(tipo) {
    fetch(base_url+"conceptos-tipo-movimiento/"+tipo)
    .then(res => res.json())
    .then(data => {
        let html = "";

        html += `<option value="">Seleccione...</option>`;

        data.forEach(concep => {
            html += `<option value="${concep.con_id}">${concep.con_descripcion}</option>`;
        });

        conceptoCaja.innerHTML = html;
        
    })
}

formMovimiento.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formMovimiento);

    fetch(base_url+"movimiento/guardar", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if(data.status === 'success') {
            $("#modalTipoMovimiento").modal('hide');
            renderMovimientos();

            swalWithBootstrapButtons.fire('Muy bien!', data.message, 'success');
        }
        
    })
})

renderMovimientos();

function renderMovimientos() {
    fetch(base_url+"movimientos/lista-cajero/"+rangoFechaMovimientos.value)
    .then(res => res.json())
    .then(data => {
        tableMovimientos(data);
        
    })
}

function tableMovimientos(data) {
    let html = "";

    const currentDate = new Date().toISOString().split('T')[0];

    data.forEach((mov, i) => {
        let botonExtornar = "";

        if(currentDate === mov.mov_fecha) {
            botonExtornar = `
            <div class="btn-group" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-danger" onclick="extornar(${mov.mov_id})" title="EXTORNAR"><i class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-info" onclick="changePago(${mov.mov_id}, ${mov.id_metodo_pago})" title="CAMBIAR METODO DE PAGO"><i class="fas fa-arrows-alt-h"></i></button>
            </div>`;
        }

        html += `
        <tr>
            <td>${i + 1}</td>
            <td>${mov.caja_descripcion}</td>
            <td>${mov.metodo}</td>
            <td>${mov.tipo_movimiento_descripcion}</td>
            <td>${mov.con_descripcion}</td>
            <td>${mov.mov_monto}</td>
            <td>${mov.mov_descripcion}</td>
            <td>${mov.fecha}</td>
            <td>${botonExtornar}</td>
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

rangoFechaMovimientos.addEventListener('change', (e) => {
    renderMovimientos();
})

function extornar(id) {
    
    swalWithBootstrapButtons
    .fire({
        title: '¿Está seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, extornar!',
        cancelButtonText: 'No, cancelar!',
        reverseButtons: true
    })
    .then((result) => {
        if (result.isConfirmed) {
            fetch(base_url+"movimiento/extornar/"+id)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    swalWithBootstrapButtons.fire('Extornado!', data.message, 'success');
                    renderMovimientos();
                }
            })
            
        }
    });
}

function changePago(idmov, idMetodoPago) {
    $("#modalChangePago").modal('show');

    const idmovi = document.getElementById('idmov');
    idmovi.value = idmov;

    fetch(base_url+"movimientos/metodos-pagos")
    .then(res => res.json())
    .then(data => {
        let html = "";

        data.forEach(metodo => {
            let seleted = "";

            if(metodo.id == idMetodoPago) {
                seleted = `selected="true"`;
            }

            html += `<option value="${metodo.id}" ${seleted}>${metodo.metodo}</option>`;
        });
        
        const nuevo_metodo_pago = document.getElementById('nuevo_metodo_pago');
        nuevo_metodo_pago.innerHTML = html;
        
    })
}

const formCambioPago = document.getElementById('formCambioPago');

formCambioPago.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formCambioPago);

    fetch(base_url+"movimiento/cambio-pago", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            $("#modalChangePago").modal('hide');
            renderMovimientos();

            swalWithBootstrapButtons.fire('Muy bien!', data.message, 'success');

            return false;
        }

        swalWithBootstrapButtons.fire('Error!', data.message, 'danger');
        
    })
})