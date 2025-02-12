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

const formArchivo = document.getElementById('formArchivo');
const ruc_emp = document.getElementById('ruc_emp');
const loadFiles = document.getElementById('contentPdt');

const anioDescarga = document.getElementById('anioDescarga');
const periodoDescarga = document.getElementById('periodoDescarga');

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
                    <button type="button" class="btn btn-info" title="Descargar archivos" onclick="descargarArchivos(${cont.ruc}, ${cont.id})"> <i class="ti ti-file-download"></i> </button>
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
    
    const idTabla = document.getElementById('idTabla');
    idTabla.value = id;

    const titleModalArchivo = document.getElementById('titleModalArchivo');

    formArchivo.reset();

    const ruc_empresa_save = document.getElementById('ruc_empresa_save');

    fetch(base_url + "contribuyentes/getId/"+ id)
    .then(res => res.json())
    .then(data => {
        titleModalArchivo.textContent = "SUBIR ARCHIVOS - "+data.razon_social;
        ruc_empresa_save.value = data.ruc;
    })
}

function descargarArchivos(ruc, id) {
    $("#modalDescargarArchivo").modal("show");
    ruc_emp.value = ruc;

    anioDescarga.value = "";
    periodoDescarga.value = "";

    loadFiles.innerHTML = "";

    const titleModalDescargar = document.getElementById('titleModalDescargar');

    fetch(base_url + "contribuyentes/getId/"+ id)
    .then(res => res.json())
    .then(data => {
        titleModalDescargar.textContent = "Descargar Archivos - "+data.razon_social;
    })
}

formArchivo.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formArchivo);

    fetch(`${base_url}contribuyentes/file-save-pdtplame`, {
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

periodoDescarga.addEventListener('change', (e) => {
    const valor = e.target.value;
    
    if(valor != "") {
        renderArchivos(valor, anioDescarga.value, ruc_emp.value)
    }
})

anioDescarga.addEventListener('change', (e) => {
    const valor = e.target.value;
    
    if(valor != "") {
        renderArchivos(periodoDescarga.value, valor, ruc_emp.value)
    }
})

function renderArchivos(periodo, anio, ruc) {
    const formData = new FormData();
    formData.append('periodo', periodo);
    formData.append('anio', anio);
    formData.append('ruc', ruc);

    fetch(base_url+"consulta-pdt-plame", {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        
        viewArchivos(data);
        
    })
}

function viewArchivos(data) {
    let html = "";

    data.forEach(archivo => {
        html += `
        <tr>
            <td>${archivo.mes_descripcion}</td>
            <td>${archivo.anio_descripcion}</td>
            <td><a href='${base_url}archivos/pdt/${archivo.archivo_planilla}' class='btn btn-success btn-sm' target='_blank' title='Descargar Renta'>R01</a> <a href='${base_url}archivos/pdt/${archivo.archivo_honorarios}' target='_blank' class='btn btn-info btn-sm' title='Descargar constancia'>R12</a>
                <a href='${base_url}archivos/pdt/${archivo.archivo_constancia}' target='_blank' class='btn btn-warning btn-sm' title='Descargar constancia'>CONST</a>
                <a href='${base_url}archivos/pdt/${archivo.archivo_constancia}' target='_blank' class='btn btn-primary btn-sm' title='Descargar txt'>TXT</a>
            </td>
            <td> <button type='button' class='btn btn-danger' title='Rectificar Archivos' onclick='rectificar(${archivo.id_pdtplame},${archivo.id_archivos_pdtplame},${archivo.periodo},${archivo.anio})'>RECT</button>
                <button type='button' class='btn btn-warning' title='Detalle' onclick='details_archivos(${archivo.id_pdtplame})'>DET</button></td>
        </tr>
        `;
    });

    loadFiles.innerHTML = html;
}