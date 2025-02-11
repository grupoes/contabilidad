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

const periodo_file = document.getElementById('periodo_file');
const anio_file = document.getElementById('anio_file');
const loadFiles = document.getElementById('loadFiles');

const formConsulta = document.getElementById('formConsulta');
const contentPdts = document.getElementById('contentPdts');

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
                    <button type="button" class="btn btn-success" title="Subir archivos" onclick="modalArchivo(${cont.id}, '${cont.ruc}')"> <i class="ti ti-file-upload"></i> </button> 
                    <button type="button" class="btn btn-info" title="Descargar archivos" onclick="descargarArchivos(${cont.id},'${cont.ruc}')"> <i class="ti ti-file-download"></i> </button> 
                    <button type="button" class="btn btn-primary" title="Descargar archivos" onclick="descargaMasiva(${cont.id})"> <i class="ti ti-file-export"></i> </button>
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

function modalArchivo(id, ruc) {
    $('#modalArchivo').modal('show');
    const idTabla = document.getElementById('idTabla');
    idTabla.value = id;

    const titleModalArchivo = document.getElementById('titleModalArchivo');

    formArchivo.reset();

    const ruc_empresa_save = document.getElementById('ruc_empresa_save');
    ruc_empresa_save.value = ruc;

    fetch(base_url + "contribuyentes/getId/"+ id)
    .then(res => res.json())
    .then(data => {
        titleModalArchivo.textContent = "SUBIR ARCHIVOS - "+data.razon_social;
        
    })
}

function descargarArchivos(id,ruc) {
    $("#modalDescargarArchivo").modal("show");
    const rucEmpresa = document.getElementById('rucEmpresa');
    rucEmpresa.value = ruc;

    periodo_file.value = "";
    anio_file.value = "";

    loadFiles.innerHTML = "";

    const titleModalDownload = document.getElementById('titleModalDownload');

    fetch(base_url + "contribuyentes/getId/"+ id)
    .then(res => res.json())
    .then(data => {
        titleModalDownload.textContent = "DESCARGAR ARCHIVOS - "+data.razon_social;
        
    })
}

function descargaMasiva(id) {
    $("#modalDescargarArchivoMasivo").modal("show");

    formConsulta.reset();
    contentPdts.innerHTML = "";

    const correo = document.getElementById('correo');
    const whatsapp = document.getElementById('whatsapp');

    correo.value = "";
    whatsapp.value = "";

    const titleModalConsult = document.getElementById('titleModalConsult');
    const empresa_ruc = document.getElementById('empresa_ruc');

    fetch(base_url + "contribuyentes/getId/"+ id)
    .then(res => res.json())
    .then(data => {
        titleModalConsult.textContent = "DESCARGAR PDT - "+data.razon_social;
        empresa_ruc.value = data.ruc;
        
    })
}

formArchivo.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formArchivo);

    fetch(`${base_url}contribuyentes/file-save-pdt0621`, {
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

const rucEmpresa = document.getElementById('rucEmpresa');

periodo_file.addEventListener('change', (e) => {
    const valor = e.target.value;

    if(anio_file.value != 0) {
        renderArchivos(valor, anio_file.value, rucEmpresa.value);
    }

})

anio_file.addEventListener('change', (e) => {
    const valor = e.target.value;

    if(periodo_file.value != 0) {
        renderArchivos(periodo_file.value, valor, rucEmpresa.value);
    }

    
})

function renderArchivos(periodo, anio, ruc) {
    const formData = new FormData();
    formData.append('periodo', periodo);
    formData.append('anio', anio);
    formData.append('ruc', ruc);

    fetch(base_url+"consulta-pdt-renta", {
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
            <td>
            
                <a href='${base_url}archivos/pdt/${archivo.nombre_pdt}' class='btn btn-success btn-sm' target='_blank' title='Descargar Renta'>PDT</a> <a href='${base_url}archivos/pdt/${archivo.nombre_constancia}' target='_blank' class='btn btn-primary btn-sm' title='Descargar constancia'>CONSTANCIA</a>
                <button type='button' class='btn btn-danger' title='Rectificar Archivos' onclick='rectificar(${archivo.id_pdt_renta},${archivo.id_archivos_pdt},${archivo.periodo},${archivo.anio})'>RECT</button>
                <button type='button' class='btn btn-warning' title='Detalle' onclick='details_archivos(${archivo.id_pdt_renta})'>DET</button>
            </td>
        </tr>
        `;
    });

    loadFiles.innerHTML = html;
}

formConsulta.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formConsulta);

    fetch(base_url+"consulta-pdt-rango", {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        viewPdts(data.data);
        
    })
})

function viewPdts(data) {

    let html = "";

    data.forEach(pdt => {
        html += `
        <tr>
            <td>${pdt.mes_descripcion}</td>
            <td>
              <a href='${base_url}archivos/pdt/${pdt.nombre_pdt}' target='_blank'>PDT</a>
            </td>
            <td>
            <a href='${base_url}archivos/pdt/${pdt.nombre_constancia}' target='_blank'>CONSTANCIA</a>
            </td>
        </tr>
        `;
    });

    contentPdts.innerHTML = html;
}

