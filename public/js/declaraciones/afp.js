const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
        confirmButton: "btn btn-success",
        cancelButton: "btn btn-danger",
    },
    showConfirmButton: true,
    buttonsStyling: false,
});

const tableBody = document.getElementById("tableBody");

const formArchivo = document.getElementById("formArchivo");

const periodo_file = document.getElementById("periodo_file");
const anio_file = document.getElementById("anio_file");
const loadFiles = document.getElementById("loadFiles");

const formConsulta = document.getElementById("formConsulta");
const contentAfps = document.getElementById("contentAfps");

const envio_archivos = document.getElementById("envio_archivos");

const rucEmpresa = document.getElementById("rucEmpresa");

const estado = document.getElementById("estado");

function validarNumero(input) {
    input.value = input.value.replace(/\D/g, "").slice(0, 9);
}

renderContribuyentes();

function renderContribuyentes() {
    fetch(`${base_url}contribuyentes/contables/${estado.value}`)
        .then((res) => res.json())
        .then((data) => {
            vistaContribuyentes(data);
        });
}

estado.addEventListener("change", (e) => {
    renderContribuyentes();
});

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
                    <button type="button" class="btn btn-success" title="Subir archivos" onclick="modalArchivo(${cont.id
            }, '${cont.ruc
            }')"> <i class="ti ti-file-upload"></i> </button> 
                    <button type="button" class="btn btn-info" title="Descargar archivos" onclick="descargarArchivos(${cont.id
            })"> <i class="ti ti-file-download"></i> </button>
            </button> 
                    <button type="button" class="btn btn-primary" title="Descargar archivos" onclick="descargaMasiva(${cont.id
            })"> <i class="ti ti-file-export"></i> </button>
                </div>
            </td>
        </tr>
        `;
    });

    $($table).DataTable().destroy();

    tableBody.innerHTML = html;

    const newcs = $($table).DataTable({
        language: language,
        responsive: true, // Hace que la tabla sea responsiva
        autoWidth: false, // Desactiva el ajuste automático de ancho
        scrollX: false, // Evita el scroll horizontal
        columnDefs: [
            { targets: "_all", className: "text-wrap" }, // Permite el ajuste de texto en las columnas
        ],
    });

    new $.fn.dataTable.Responsive(newcs);
}

function modalArchivo(id, ruc) {
    $("#modalArchivo").modal("show");
    const idTabla = document.getElementById("idTabla");
    idTabla.value = id;

    const titleModalArchivo = document.getElementById("titleModalArchivo");

    formArchivo.reset();

    const ruc_empresa_save = document.getElementById("ruc_empresa_save");
    ruc_empresa_save.value = ruc;

    fetch(base_url + "contribuyentes/getId/" + id)
        .then((res) => res.json())
        .then((data) => {
            titleModalArchivo.textContent = "SUBIR ARCHIVOS - " + data.razon_social;
        });
}

function descargarArchivos(id) {
    $("#modalDescargarArchivo").modal("show");

    rucEmpresa.value = id;

    periodo_file.value = "";
    anio_file.value = "";

    loadFiles.innerHTML = "";

    const titleModalDownload = document.getElementById("titleModalDownload");

    fetch(base_url + "contribuyentes/getId/" + id)
        .then((res) => res.json())
        .then((data) => {
            titleModalDownload.innerHTML = `DESCARGAR ARCHIVOS AFP - <span class="text-primary" id="nameComp">${data.razon_social}</span>`;
        });
}

function descargaMasiva(id) {
    $("#modalDescargarArchivoMasivo").modal("show");

    formConsulta.reset();
    contentAfps.innerHTML = "";

    const correo = document.getElementById("correo");
    const whatsapp = document.getElementById("whatsapp");

    correo.value = "";
    whatsapp.value = "";

    const titleModalConsult = document.getElementById("titleModalConsult");
    const empresa_ruc = document.getElementById("empresa_ruc");
    const idcont = document.getElementById("idcont");
    idcont.value = id;

    fetch(base_url + "contribuyentes/getId/" + id)
        .then((res) => res.json())
        .then((data) => {
            titleModalConsult.textContent = "DESCARGAR AFP - " + data.razon_social;
            empresa_ruc.value = data.ruc;
        });
}

const btnForm = document.getElementById("btnForm");

formArchivo.addEventListener("submit", (e) => {
    e.preventDefault();

    btnForm.setAttribute("disabled", true);
    btnForm.innerHTML =
        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

    const formData = new FormData(formArchivo);

    fetch(`${base_url}afp`, {
        method: "POST",
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            btnForm.removeAttribute("disabled");
            btnForm.innerHTML = "Guardar";

            if (data.status === "success") {
                $("#modalArchivo").modal("hide");
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: data.message,
                    showConfirmButton: false,
                    timer: 1500,
                });

                return false;
            }

            $("#modalArchivo").modal("hide");

            swalWithBootstrapButtons
                .fire({
                    title: "Error!",
                    text: data.message,
                    icon: "error",
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $("#modalArchivo").modal("show");
                        // Aquí puedes realizar cualquier acción adicional
                    }
                });
        });
});

periodo_file.addEventListener("change", (e) => {
    const valor = e.target.value;

    if (anio_file.value != 0) {
        renderArchivos(valor, anio_file.value, rucEmpresa.value);
    }
});

anio_file.addEventListener("change", (e) => {
    const valor = e.target.value;

    if (periodo_file.value != 0) {
        renderArchivos(periodo_file.value, valor, rucEmpresa.value);
    }
});

function renderArchivos(periodo, anio, id) {
    const formData = new FormData();
    formData.append("periodo", periodo);
    formData.append("anio", anio);
    formData.append("contribuyente_id", id);

    fetch(base_url + "consulta-afp", {
        method: "POST",
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            viewArchivos(data, id);
        });
}

function viewArchivos(data, id) {
    let html = "";

    data.forEach((archivo) => {
        html += `
        <tr>
            <td>${archivo.mes_descripcion}</td>
            <td>${archivo.anio_descripcion}</td>
            <td>
                <a href='${base_url}archivos/afp/${archivo.archivo_reporte}' class='btn btn-success btn-sm' target='_blank' title='Descargar reporte'>REPORTE</a> 
                <a href='${base_url}archivos/afp/${archivo.archivo_ticket}' target='_blank' class='btn btn-primary btn-sm' title='Descargar ticket'>TICKET</a>
                <a href='${base_url}archivos/afp/${archivo.archivo_plantilla}' target='_blank' class='btn btn-info btn-sm' title='Descargar plantilla'>PLANTILLA</a>
            </td>
            <td>
              ${archivo.acciones}
            </td>
        </tr>
        `;
    });

    loadFiles.innerHTML = html;
}

formConsulta.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(formConsulta);

    fetch(base_url + "consulta-afp-rango", {
        method: "POST",
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            viewAfps(data.data);
        });
});

function viewAfps(data) {
    let html = "";

    if (data.length > 0) {
        data.forEach((afp) => {
            html += `
            <tr>
                <td>${afp.mes_descripcion}</td>
                <td>
                  <a href='${base_url}archivos/afp/${afp.archivo_reporte}' target='_blank'>REPORTE</a>
                </td>
                <td>
                    <a href='${base_url}archivos/afp/${afp.archivo_ticket}' target='_blank'>TICKET</a>
                </td>
                <td>
                    <a href='${base_url}archivos/afp/${afp.archivo_plantilla}' target='_blank'>PLANTILLA</a>
                </td>
            </tr>
            `;
        });

        envio_archivos.removeAttribute("hidden");
    } else {
        html += `
            <tr>
                <td colspan="3">
                    <h4 class="text-center">No se encontraron resultados</h4>
                </td>
            </tr>
        `;

        envio_archivos.setAttribute("hidden", true);
    }

    contentAfps.innerHTML = html;
}

function verificarInputs() {
    let input = document.getElementById("whatsapp");
    if (input.value.length !== 9) {
        alert("El número debe tener exactamente 9 dígitos.");
        input.focus();
    } else {
        const formData = new FormData();
        formData.append("numero", input.value);
        formData.append("anio", anio_consulta.value);
        formData.append("desde", desde.value);
        formData.append("hasta", hasta.value);
        formData.append("empresa_ruc", empresa_ruc.value);

        fetch(base_url + "send-message", {
            method: "POST",
            body: formData,
        })
            .then((res) => res.json())
            .then((data) => {
                console.log(data);
                return false;
            });
    }
}

async function verificarInput() {
    let input = document.getElementById("whatsapp");

    if (input.value.length !== 9) {
        alert("El número debe tener exactamente 9 dígitos.");
        input.focus();
        return;
    }

    const formData = new FormData();
    formData.append("numero", input.value);
    formData.append("anio", anio_consulta.value);
    formData.append("desde", desde.value);
    formData.append("hasta", hasta.value);
    formData.append("empresa_ruc", empresa_ruc.value);

    try {
        const response = await fetch(base_url + "send-file-google-cloud-storage", {
            method: "POST",
            body: formData,
        });

        const data = await response.json();

        const itemFormData = new FormData();
        itemFormData.append("anio", data.anio);
        itemFormData.append("links", JSON.stringify(data.links));
        itemFormData.append("meses", JSON.stringify(data.meses));
        itemFormData.append("numero", input.value);

        const sendResponse = await fetch(base_url + "send-file-pdt621", {
            method: "POST",
            body: itemFormData,
        });

        const sendData = await sendResponse.json();

        if (sendData.success === true) {
            $("#modalDescargarArchivoMasivo").modal("hide");
            Swal.fire({
                position: "top-center",
                icon: "success",
                title: sendData.message,
                showConfirmButton: false,
                timer: 1500,
            });

            return false;
        }

        $("#modalDescargarArchivoMasivo").modal("hide");

        swalWithBootstrapButtons
            .fire({
                title: "Error!",
                text: data.message,
                icon: "error",
            })
            .then((result) => {
                if (result.isConfirmed) {
                    $("#modalDescargarArchivoMasivo").modal("show");
                }
            });
    } catch (error) {
        console.error("Error al enviar la solicitud:", error);
    }
}

const idpdtrenta = document.getElementById("idpdtrenta");
const idarchivos = document.getElementById("idarchivos");
const periodoRectificacion = document.getElementById("periodoRectificacion");
const anioRectificacion = document.getElementById("anioRectificacion");
const rucRect = document.getElementById("rucRect");
const alertRect = document.getElementById("alertRect");
const viewAlert = document.getElementById("viewAlert");

const formRectificacion = document.getElementById("formRectificacion");

function rectificar(
    id_pdt_renta,
    id_archivos_pdt,
    periodo,
    anio,
    ruc,
    name_periodo,
    name_anio
) {
    $("#modalDescargarArchivo").modal("hide");
    $("#modalRectificacion").modal("show");
    idpdtrenta.value = id_pdt_renta;
    idarchivos.value = id_archivos_pdt;
    periodoRectificacion.value = periodo;
    anioRectificacion.value = anio;
    rucRect.value = ruc;

    const titleRectArchivos = document.getElementById("titleRectArchivos");
    const nameComp = document.getElementById("nameComp").textContent;
    titleRectArchivos.innerHTML = `<span class="text-secondary">RECTIFICAR ARCHIVOS</span> | ${nameComp} | ${name_periodo} - ${name_anio}`;

    formRectificacion.reset();
}

const btnFormRectificacion = document.getElementById("btnFormRectificacion");

formRectificacion.addEventListener("submit", (e) => {
    e.preventDefault();

    btnFormRectificacion.setAttribute("disabled", true);
    btnFormRectificacion.innerHTML =
        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

    const formData = new FormData(formRectificacion);

    fetch(base_url + "rectificar-afp", {
        method: "POST",
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            btnFormRectificacion.removeAttribute("disabled");
            btnFormRectificacion.innerHTML = "Guardar";

            if (data.status === "error") {
                viewAlert.innerHTML = `<div class="alert alert-danger" role="alert" id="alertRect">${data.message}</div>`;
                return false;
            }

            $("#modalRectificacion").modal("hide");
            $("#modalDescargarArchivo").modal("hide");

            swalWithBootstrapButtons
                .fire({
                    title: "Exitoso!",
                    text: data.message,
                    icon: "success",
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        renderArchivos(
                            periodoRectificacion.value,
                            anioRectificacion.value,
                            rucEmpresa.value
                        );
                        $("#modalDescargarArchivo").modal("show");
                    }
                });
        });
});

const getFilesDetails = document.getElementById("getFilesDetails");
const titleDetallePdt = document.getElementById("titleDetallePdt");

function details_archivos(id_afp, periodo, anio) {
    $("#modalDescargarArchivo").modal("hide");
    $("#modalDetalle").modal("show");

    const nameComp = document.getElementById("nameComp").textContent;

    titleDetallePdt.innerHTML = `<span class="text-secondary">DETALLE ARCHIVOS</span> | ${nameComp} | ${periodo} - ${anio}`;

    getFilesDetails.innerHTML = "";

    fetch(base_url + "afp/get-files-details/" + id_afp)
        .then((res) => res.json())
        .then((data) => {
            let html = "";

            data.forEach((file) => {
                if (file.estado == 1) {
                    html += `
                    <tr class="text-center">
                        <td>
                            <a href='${base_url}archivos/afp/${file.archivo_reporte}' target='_blank'> <i class="fas fa-file-pdf fs-4 text-danger"></i> </a>
                        </td>
                        <td>
                            <a href='${base_url}archivos/afp/${file.archivo_ticket}' target='_blank'> <i class="fas fa-file-pdf fs-4 text-warning"></i> </a>
                        </td>
                        <td>
                            <a href='${base_url}archivos/afp/${file.archivo_plantilla}' target='_blank'> <i class="fas fa-file-pdf fs-4 text-success"></i> </a>
                        </td>
                    </tr>
                    `;
                } else {
                    html += `
                    <tr class="text-center">
                        <td>
                            <a href='${base_url}archivos/afp/${file.archivo_reporte}' target='_blank'> <i class="fas fa-file-pdf fs-4"></i> </a>
                        </td>
                        <td>
                            <a href='${base_url}archivos/afp/${file.archivo_ticket}' target='_blank'> <i class="fas fa-file-pdf fs-4"></i> </a>
                        </td>
                        <td>
                            <a href='${base_url}archivos/afp/${file.archivo_plantilla}' target='_blank'> <i class="fas fa-file-pdf fs-4"></i> </a>
                        </td>
                    </tr>
                    `;
                }
            });

            getFilesDetails.innerHTML = html;
        });
}

const modalRectificacion = document.getElementById("modalRectificacion");

modalRectificacion.addEventListener("click", (e) => {
    if (e.target.classList.contains("modalDescargar")) {
        $("#modalDescargarArchivo").modal("show");
    }
});

const modalDetalle = document.getElementById("modalDetalle");

modalDetalle.addEventListener("click", (e) => {
    if (e.target.classList.contains("closeDetalle")) {
        $("#modalDescargarArchivo").modal("show");
    }
});

function eliminar(afp_id, id) {
    $("#modalDescargarArchivo").modal("hide");
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "No, cancelar!",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(
                base_url + "afp/delete/" + afp_id + "/" + id
            )
                .then((res) => res.json())
                .then((data) => {
                    if (data.status === "success") {
                        Swal.fire({
                            position: "top-center",
                            icon: "success",
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500,
                        });

                        setTimeout(() => {
                            $("#modalDescargarArchivo").modal("show");
                            renderArchivos(
                                periodo_file.value,
                                anio_file.value,
                                rucEmpresa.value
                            );
                        }, 1600);
                    }
                });
        } else {
            $("#modalDescargarArchivo").modal("show");
        }
    });
}
