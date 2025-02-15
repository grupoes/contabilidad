const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

const btnModal = document.getElementById('btnModal');
const searchDocumento = document.getElementById('searchDocumento');
const titleModal = document.getElementById('titleModal');

const razonSocial = document.getElementById('razonSocial');
const nombreComercial = document.getElementById('nombreComercial');
const direccionFiscal  = document.getElementById('direccionFiscal');
const ubigeo = document.getElementById('ubigeo');
const tipoSuscripcion = document.getElementById('tipoSuscripcion');
const costoMensual = document.getElementById('costoMensual');
const constAnual = document.getElementById('constAnual');
const numeroDocumento = document.getElementById('numeroDocumento');
const idTable = document.getElementById('idTable');
const urbanizacion = document.getElementById('urbanizacion');
const tipoServicio = document.getElementById('tipoServicio');
const tipoPago = document.getElementById('tipoPago');
const costoAnual = document.getElementById('costoAnual');
const diaCobro = document.getElementById('diaCobro');
const fechaContrato = document.getElementById('fechaContrato');
const clientesVarios = document.getElementById('clientesVarios');
const boletaAnulado = document.getElementById('boletaAnulado');
const facturaAnulado = document.getElementById('facturaAnulado');

const formDatos = document.getElementById('formDatos');

const titleModalTarifa = document.getElementById('titleModalTarifa');
const tableTarifa = document.getElementById('tableTarifa');
const formTarifa = document.getElementById('formTarifa');

const titleModalCertificado = document.getElementById('titleModalCertificado');
const formCertificado = document.getElementById('formCertificado');
const idTableCertificado = document.getElementById('idTableCertificado');
const tableCertificado = document.getElementById('tableCertificado');

var multipleSystem = new Choices('#choices-system', {
    removeItemButton: true,
    placeholderValue: 'Seleccione una o más opciones',
    allowHTML: true
});

var listaUbigeo = new Choices("#ubigeo", {
    removeItemButton: true,
    searchPlaceholderValue: "Buscar aqui el distrito, provincia o departamento",
    allowHTML: true
});

btnModal.addEventListener('click', (e) => {

    $("#modalAddEdit").modal('show');

    titleModal.textContent = "Agregar Empresa";

    idTable.value = 0;
    numeroDocumento.value = "";
    razonSocial.value = "";
    nombreComercial.value = "";
    direccionFiscal.value = "";
    listaUbigeo.removeActiveItems();
    urbanizacion.value = "";
    tipoSuscripcion.value = "NO GRATUITO";
    tipoServicio.value = "CONTABLE";
    tipoPago.value = "ADELANTADO";
    costoMensual.value = "";
    costoAnual.value = "";
    diaCobro.value = "01";
    fechaContrato.value = "";
    multipleSystem.removeActiveItems();
    clientesVarios.value = "00000001";
    boletaAnulado.value = "00000000";
    facturaAnulado.value = "00000000001";

})

// Función para cargar datos de ubigeo
async function cargarUbigeo() {
    try {
        const response = await fetch(`${base_url}all-ubigeo`);
        if (!response.ok) throw new Error("Error en la respuesta de la API");
        
        const data = await response.json();

        // Cargar datos en Choices
        listaUbigeo.setChoices(data.map(release => ({
            label: `${release.distrito} - ${release.provincia} - ${release.departamento}`,
            value: release.codigo_ubigeo,
        })), 'value', 'label', true);

    } catch (error) {
        console.error("Error al cargar ubigeo:", error);
        alert("No se pudieron cargar los datos de ubigeo.");
    }
}

// Llamar a cargarUbigeo para inicializar
cargarUbigeo();

searchDocumento.addEventListener('click',  (e) => {
    const numDoc = numeroDocumento.value;

    if (numDoc.length === 11) {
        const getRazonSocial = document.getElementById('getRazonSocial');
        const getNombreComercial = document.getElementById('getNombreComercial');
        const getDireccionFiscal = document.getElementById('getDireccionFiscal');
        const getUbigeo = document.getElementById('getUbigeo');

        getRazonSocial.textContent = "Obteniendo razón social...";
        razonSocial.disabled = true;

        getNombreComercial.textContent = "Obteniendo nombre comercial...";
        nombreComercial.disabled = true;

        getDireccionFiscal.textContent = "Obteniendo dirección fiscal...";
        direccionFiscal.disabled = true;

        getUbigeo.textContent = "Obteniendo ubigeo...";
        ubigeo.disabled = true;

        searchDocumento.innerHTML = `
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        `;

        numDoc.disabled = true;

        fetch(base_url+"api/dni-ruc/ruc/"+numDoc)
        .then(res => res.json())
        .then(data => {

            getRazonSocial.textContent = "";
            razonSocial.disabled = false;

            getNombreComercial.textContent = "";
            nombreComercial.disabled = false;

            getDireccionFiscal.textContent = "";
            direccionFiscal.disabled = false;

            getUbigeo.textContent = "";
            ubigeo.disabled = false;

            searchDocumento.innerHTML = `
                <i class="fas fa-search"></i>
            `;

            numDoc.disabled = false;

            if(data.respuesta === 'error') {
                alert("R.U.C. no fue encontrado")
                return false;
            }

            razonSocial.value = data.data.razon_social;
            nombreComercial.value = data.data.nombre_comercial;
            direccionFiscal.value = data.data.direccion;
            
            return cargarUbigeo().then(() => {
                listaUbigeo.setChoiceByValue(data.data.codigo_ubigeo);
            });
            
            
        })
    } else {
        alert('El número del documento debe tener 11 dígitos');
    }
})

tipoSuscripcion.addEventListener('change', (e) => {
    const tipo = e.target.value;
    const costos = document.getElementsByClassName('costos');

    if (tipo === 'SI GRATUITO') {
        [...costos].forEach(costo => costo.setAttribute('hidden', true))
    } else {
        [...costos].forEach(costo => costo.removeAttribute('hidden'));
    }
})

formDatos.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formDatos);

    fetch(base_url+"contribuyente/add", {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            $("#modalAddEdit").modal('hide');
            notifier.show('¡Bien hecho!', data.message, 'success', '', 4000);

            listaContribuyentes();
        } else {
            notifier.show('¡Sorry!', data.message, 'danger', '', 4000);

            setTimeout(() => {
                const notifierElement = document.querySelector('.notifier-container');
                if (notifierElement) {
                    notifierElement.style.zIndex = '1100'; // Nivel mayor al modal
                }
            }, 0);
        }
    })

})

const selectOpciones = document.getElementById('selectOpciones');
const tableBody = document.getElementById('tableBody');

listaContribuyentes();

function listaContribuyentes() {

    fetch(base_url+"contribuyente/all/"+selectOpciones.value)
    .then(res => res.json())
    .then(data => {
        viewListContribuyentes(data);
        
    })
}

function optionsTable(id) {
    return `
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">book</i>Lista de Boletas</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">library_books</i>Registrar Boletas</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">import_export</i>Importar Boletas</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">delete</i>Eliminar Empresa</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">settings</i>Configurar declaraciones</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">settings_applications</i>Declaración tributaria</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">vpn_key</i>Ver contraseña</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">insert_drive_file</i>Escanear y generar maquetas de compras</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">file_copy</i>Reporte Comercial</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">restaurant</i>Reporte Restaurante</a>
        <a class="dropdown-item" href="#"><i class="material-icons-two-tone">attach_file</i>Enviar archivos</a>
    `;
}

function viewListContribuyentes(data) {
    let html = "";

    data.forEach((emp, index) => {
        let opciones = optionsTable(emp.id);

        let tieneSistema = emp.tiene_sistema === 'SI' ? `<a href="#"><span class="badge bg-success tiene-sistema" data-id="${emp.id}">SI</span></a>` : `<span class="badge bg-warning">NO</span>`;

        let tiene_certificado = '';

        if (emp.tiene_sistema === 'SI') {

            if(emp.tiene_certificado === 'SI' && emp.certificado_vencido === 'SI') {
                tiene_certificado = '<span class="badge bg-danger" title="tiene certificado digital vencido">C</span>';
            } else if(emp.tiene_certificado === 'SI' && emp.certificado_vencido === 'NO') {
                tiene_certificado = '<span class="badge bg-success" title="tiene certificado digital">C</span>';
            } else {
                tiene_certificado = '<span class="badge bg-warning" title="No cuenta con certificado digital">C</span>';
            }
        }
        
        let estado = emp.estado === '1' ? 'checked' : '';

        let monto;

        if (emp.tipoSuscripcion === 'SI GRATUITO') {
            monto = `GRATUITO`;
        } else {
            if(emp.tipoServicio === 'ALQUILER') {
                monto = `
                    <p class="f-14 mb-0">M: ${emp.costoMensual}</p>
                `;
            } else {
                monto = `
                    <p class="f-14 mb-0">M: ${emp.costoMensual}</p>
                    <p class="f-14 mb-0">A: ${emp.costoAnual}</p>
                `;
            }
            
        }
        
        let estadoEmpresa = "";

        switch(emp.respuesta) {
            case 0: 
                estadoEmpresa = `<span class="badge bg-success" title="">${emp.tipo}</span>`;
                break;
            case 1: 
                estadoEmpresa = `<span class="badge bg-primary" title="">${emp.tipo}</span>`;
                break;
            case 2: 
                estadoEmpresa = `<span class="badge bg-warning" title="">${emp.tipo}</span>`;
                break;
            case 3: 
                estadoEmpresa = `<span class="badge bg-danger" title="">${emp.tipo}</span>`;
                break;
            default:
                break;
        }

        html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="row">
                        <div class="col">
                            <h6 class="mb-1"><a href="#" class="num-doc" data-id="${emp.id}">${emp.ruc}</a></h6>
                            <p class="text-muted f-14 mb-0"> ${emp.razon_social} </p>
                        </div>
                    </div>
                </td>
                <td><a href="#" class="tipoServicio" data-id="${emp.id}">${emp.tipoServicio}</a></td>
                <td>
                    ${monto}
                </td>
                <td>
                    ${tieneSistema}
                    <a href="#">${tiene_certificado}</a>
                </td>
                <td> 
                    <div class="form-check form-switch custom-switch-v1 mb-2">
                        <input type="checkbox" class="form-check-input input-success" name="estado" id="estado${emp.id}" ${estado} onchange="toggleSwitchStatus(this, ${emp.id})">
                    </div>

                </td>
                <td>
                    ${estadoEmpresa}
                </td>
                <td>
                    <div class="dropdown">
                        <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <i class="ti ti-dots-vertical f-18"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="position: fixed;z-index: 1050;top: 50px;left: auto;right: 20px;overflow-y: auto;">
                            ${opciones}          
                        </div>
                    </div>
                </td>
            </tr>
        `;
    });

    $($table).DataTable().destroy();

    tableBody.innerHTML = html

    const newcs = $($table).DataTable(
        optionsTableDefault
    );
    
    new $.fn.dataTable.Responsive(newcs);
}

selectOpciones.addEventListener('change', (e) => {
    listaContribuyentes();
})

tableBody.addEventListener('click', (e) => {

    if(e.target.classList.contains('num-doc')) {

        idTable.value = e.target.dataset.id;

        $("#modalAddEdit").modal('show');

        titleModal.textContent = "Editar Empresa";

        fetch(base_url+"contribuyente/get/"+e.target.dataset.id)
        .then(res => res.json())
        .then(data => {
            const empresa = data.data;

            numeroDocumento.value = empresa.ruc;
            razonSocial.value = empresa.razon_social;
            nombreComercial.value = empresa.nombre_comercial;
            direccionFiscal.value = empresa.direccion_fiscal;
            urbanizacion.value = empresa.urbanizacion;
            tipoSuscripcion.value = empresa.tipoSuscripcion;
            tipoServicio.value = empresa.tipoServicio;
            tipoPago.value = empresa.tipoPago;
            costoMensual.value = empresa.costoMensual;
            costoAnual.value = empresa.costoAnual;
            diaCobro.value = empresa.diaCobro;
            fechaContrato.value = empresa.fechaContrato;

            const sistemas = data.sistemas;
            
            const sistemasArray = sistemas.map(sistema => sistema.system_id);

            sistemasArray.forEach(value => {
                multipleSystem.setChoiceByValue(value.toString());
            });
            
            listaUbigeo.setChoiceByValue(empresa.ubigeo_id);
        })
    }

    if(e.target.classList.contains('tipoServicio')) {
        const idTableTarifa = document.getElementById('idTableTarifa');
        idTableTarifa.value = e.target.dataset.id;

        $("#modalTipoServicio").modal('show');

        renderTarifas(e.target.dataset.id);
    }

    if(e.target.classList.contains('tiene-sistema')) {
        const valor = e.target.getAttribute('data-id');
        
        idTableCertificado.value = valor;
        
        $("#modalSistema").modal('show');

        renderCertificados(valor);
    }

});

function renderTarifas(id) {
    fetch(base_url+"contribuyente/historial-tarifa/"+id)
        .then(res => res.json())
        .then(data => {
           
            const empresa = data.data_contribuyente;
            titleModalTarifa.textContent = `Historial de tarifas de ${empresa.razon_social}`;
            
            const tarifas = data.data_tarifa;

            const cantidadTarifas = tarifas.length;
            
            let html = "";

            tarifas.forEach((tarifa, index) => {
                let deleteTarifa = (index == (cantidadTarifas - 1)) ? "" : `<a href="#"><i class="fas fa-trash text-danger fs-14 btnDeleteTarifa" data-id="${tarifa.id}"></i></a>`;

                let fechaFin;

                if(fechaFin === null) {
                    fechaFin = "";
                } else {
                    fechaFin = tarifa.fecha_fin;
                }

                html += `
                    <tr>
                        <td>${tarifa.fecha_inicio}</td>
                        <td>${fechaFin}</td>
                        <td>${tarifa.monto_mensual}</td>
                        <td>${tarifa.monto_anual}</td>
                        <td>
                            ${deleteTarifa}
                        </td>
                    </tr>
                `;
            });

            tableTarifa.innerHTML = html;

        })
}

function renderCertificados(id) {
    fetch(base_url+"contribuyente/certificado-digital/"+id)
        .then(res => res.json())
        .then(data => {
           
            const empresa = data.data_contribuyente;
            titleModalCertificado.textContent = `Historial de Certificados de ${empresa.razon_social}`;
            
            const certificados = data.data_certificado;
            
            let html = "";

            certificados.forEach((certi, index) => {

                let estado = certi.estado == 1 ? `<span class="badge bg-success">VIGENTE</span>` : `<span class="badge bg-danger">VENCIDO</span>`;

                let opciones = "";

                if(certi.estado == 1) {

                    let descargar = "";

                    if(certi.tipo_certificado === 'PROPIO') {
                        descargar = `<a href="#" onclick="descargarCertificadoDigital(event, '${certi.nameFile}')" class="descargar-certificado-digital" title="descargar certificado digital"><i class="fas fa-cloud-download-alt text-success fs-3"></i></a>`;
                    }

                    opciones = `
                        ${descargar}

                        <a href="#" onclick="deleteCertificadoDigital(event, ${certi.id})" class="delete-certificado-digital" title="eliminar certificado digital"><i class="fas fa-trash text-danger fs-3"></i></a>
                    `;
                }

                html += `
                    <tr>
                        <td>${certi.tipo_certificado}</td>
                        <td>${certi.fecha_inicio}</td>
                        <td>${certi.fecha_vencimiento}</td>
                        <td>${certi.clave}</td>
                        <td>${estado}</td>
                        <td>
                            ${opciones}
                        </td>
                    </tr>
                `;
            });

            tableCertificado.innerHTML = html;

        })
}

formTarifa.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formTarifa);

    fetch(base_url+"contribuyente/add-tarifa", {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if(data.status === 'error') {
            alert(data.message)
            return false;
        }

        formTarifa.reset();

        renderTarifas(idTableTarifa.value);
        
    })
});

tableTarifa.addEventListener('click', (e) => {

    if(e.target.classList.contains('btnDeleteTarifa')) {
        const valor = e.target.getAttribute('data-id');
        
        if (confirm("¿Estás seguro de eliminar esta tarifa?")) {
            fetch(base_url+"contribuyente/delete-tarifa/"+valor)
            .then(res => res.json())
            .then(data => {
                renderTarifas(idTableTarifa.value);
            })
        }
        
    }
})

formCertificado.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formCertificado);

    fetch(base_url+"contribuyente/add-certificado", {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if(data.status === 'error') {
            alert(data.message);
            return false;
        }

        formCertificado.reset();

        renderCertificados(idTableCertificado.value);
        listaContribuyentes();
        
    })
})

function descargarCertificadoDigital(e, fileName) {
    e.preventDefault();

    const url = `${base_url}/descargar-certificado/${encodeURIComponent(fileName)}`;
    const link = document.createElement('a');
    link.href = url;
    link.download = fileName;
    link.click();
}

function deleteCertificadoDigital(e, id) {
    if (confirm("¿Estás seguro de eliminar esta tarifa?")) {
        fetch(base_url+"contribuyente/delete-certificado-digital/"+id)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                renderCertificados(idTableCertificado.value);
                alert(data.message);
            } else {
                alert("Error")
            }
            
        })
    }
}

if(document.getElementById('btnCertificadoVencer')) {
    const btnCertificadoVencer = document.getElementById('btnCertificadoVencer');

    btnCertificadoVencer.addEventListener('click', (e) => {
        $("#modalCertificado").modal("show");
    })
}

const tipo_certificado = document.getElementById('tipo_certificado');
const file_certificado = document.getElementById('file_certificado');
const claveCertificado = document.getElementById('claveCertificado');

tipo_certificado.addEventListener('change', (e) => {
    const valor = e.target.value;

    const pse = document.getElementsByClassName('pse');

    if(valor === 'PROPIO') {
        file_certificado.setAttribute('required', true);
        claveCertificado.setAttribute('required', true);

        [...pse].forEach(p => p.removeAttribute('hidden', true));

        claveCertificado.value = "";
        file_certificado.value = "";

    } else {
        file_certificado.removeAttribute('required');
        claveCertificado.removeAttribute('required');

        [...pse].forEach(p => p.setAttribute('hidden', true))
    }
})

function toggleSwitchStatus(switchElement, id) {

    let checked = switchElement.checked ? 1 : 2;

    fetch(base_url+"contribuyente/status/"+id+"/"+checked)
    .then(res => res.json())
    .then(data => {
        notifier.show('¡Bien hecho!', data.message, 'success', '', 2000);
        
    })
}
