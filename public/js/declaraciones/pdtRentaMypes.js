document.addEventListener("DOMContentLoaded", function () {
    obtenerDatos();
});

const anios = document.getElementById("anios");
const searchContribuyente = document.getElementById("searchContribuyente");
const filterTotales = document.getElementById("filterTotales");
const estado = document.getElementById("estado");

const tamModal = document.getElementById("tamModal");
const viewTable = document.getElementById("viewTable");
const viewFilePdf = document.getElementById("viewFilePdf");

const rucEmpresa = document.getElementById("rucEmpresa");
const title = document.getElementById("titleContribuyente");

const listaEmpresasMontos = document.getElementById("listaEmpresasMontos");

estado.addEventListener("change", obtenerDatos);

function obtenerDatos() {
    listaEmpresasMontos.innerHTML = `<p class="text-center fw-bold">Cargando datos...</p>`;

    const formData = new FormData();
    formData.append("anio", anios.value);
    formData.append("search", searchContribuyente.value);
    formData.append("filter", filterTotales.value);
    formData.append("estado", estado.value);

    fetch(`${base_url}customer-mypes-list`, {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            viewEmpresas(data);
        });
}

function viewEmpresas(data) {
    let html = "";

    data.forEach((item) => {

        let campanita = "";

        if (item.tiene_estado_cero == 1) {
            campanita = `<i class="material-icons-two-tone text-danger fs-2 bell-shake"> notifications</i>`;
        }

        html += `
    <div class="col-md-4 col-xxl-3">
        <div class="card shadow-none border mb-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h5 class="mb-0">${item.razon_social}</h5>
                    <button type="button" class="btn btn-light btn-sm" title="Ver periodos" onclick="loadPeriodos('${item.ruc}')"><i class="ti ti-eye f-18"></i></button>

                </div>

                <p class="mb-0 d-flex justify-content-between align-items-center">${item.ruc}  ${campanita} </p>

                <div class="row g-3 mt-1">
                    <div class="col-6 col-sm-6 mt-1">
                        <div class="bg-body py-3 px-2 rounded">
                            <div class="d-flex align-items-center mb-2">
                                <div class="flex-grow-1 ms-2">
                                    <p class="mb-0">COMPRAS</p>
                                </div>
                            </div>
                            <h6 class="mb-0 text-warning">
                                S/ ${item.total_compras_decimal}
                            </h6>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6 mt-1">
                        <div class="bg-body py-3 px-2 rounded">
                            <div class="d-flex align-items-center mb-2">
                                <div class="flex-grow-1 ms-2">
                                    <p class="mb-0">VENTAS</p>
                                </div>
                            </div>
                            <h6 class="mb-0 text-success">
                                S/ ${item.total_ventas_decimal}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;
    });

    listaEmpresasMontos.innerHTML = html;
}

anios.addEventListener("change", obtenerDatos);
searchContribuyente.addEventListener("keyup", obtenerDatos);
filterTotales.addEventListener("change", obtenerDatos);

const listaPeriodos = document.getElementById("list-periodos");

searchContribuyente.addEventListener("input", function (event) {
    if (event.target.value === "") {
        obtenerDatos();
    }
});

function loadPeriodos(ruc) {
    $("#modalPeriodos").modal("show");

    rucEmpresa.value = ruc;
    title.innerHTML = "";
    listaPeriodos.innerHTML = "";

    viewFilePdf.innerHTML = "";

    tamModal.classList.remove("modal-fullscreen");
    tamModal.classList.add("modal-lg");

    viewTable.classList.remove("col-md-6");
    viewTable.classList.add("col-md-12");
    viewTable.removeAttribute("style");

    viewFilePdf.classList.remove("col-md-6");
    viewFilePdf.classList.add("col-md-12");

    loadTablePeriodos(ruc, anios.value);
}

function loadTablePeriodos(ruc, anio) {
    fetch(`${base_url}customer-mypes-periodos/${ruc}/${anio}`)
        .then((response) => response.json())
        .then((data) => {
            title.innerHTML = `Periodos ${data[0].anio_descripcion} - ${data[0].razon_social}`;
            viewPeriodos(data, ruc, anio);
        });
}

function viewPeriodos(data, ruc, anio) {
    let html = "";

    let total_compras = 0;
    let total_ventas = 0;

    data.forEach((item) => {
        total_compras += parseFloat(item.total_compras);
        total_ventas += parseFloat(item.total_ventas);

        let boton_edit = "";

        if (item.estado_datos == 0) {
            boton_edit = `<button class="btn btn-primary btn-sm btn-edit" data-id="${item.id_pdt_renta}"> <i class="ti ti-pencil"></i> </button>`;
        }

        html += `
    <tr>
        <td>${item.mes_descripcion}</td>
        <td>${item.ventas_gravadas_decimal}</td>
        <td>${item.ventas_no_gravadas_decimal}</td>
        <td>${item.total_ventas_decimal}</td>
        <td>${item.compras_gravadas_decimal}</td>
        <td>${item.compras_no_gravadas_decimal}</td>
        <td>${item.total_compras_decimal}</td>
        <td>
            <a href="${base_url}archivos/pdt/${item.nombre_pdt}" class="btn btn-danger btn-sm viewPdf" title="Ver">
                <i class="ti ti-file-text f-26"></i>
            </a>
            ${boton_edit}
        </td>
    </tr>
    `;
    });

    html += `
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td> <strong>${total_ventas.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}</strong></td>
        <td></td>
        <td></td>
        <td> <strong>${total_compras.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}</strong></>
        <td>
            <button class="btn btn-success btn-sm" title="Descargar en Excel" onclick="descargarExcelMypes('${ruc}', ${anio})"> <i class="fas fa-file-excel"></i> Excel </button>
        </td>
    </tr>
    `;

    listaPeriodos.innerHTML = html;
}

listaPeriodos.addEventListener("click", (e) => {
    console.log(e.target);
    const editBtn = e.target.closest(".btn-edit");

    if (editBtn) {
        const row = e.target.closest("tr");
        const ventasGravadasCell = row.children[1];
        const ventasNoGravadasCell = row.children[2];
        const totalVentasCell = row.children[3];
        const comprasGravadasCell = row.children[4];
        const comprasNoGravadasCell = row.children[5];
        const totalComprasCell = row.children[6];

        const isEditing = row.classList.toggle("editing");

        const id = editBtn.getAttribute("data-id");

        if (isEditing) {
            // Guardamos valores actuales
            const ventasGravadasValue = ventasGravadasCell.textContent.trim().replace(/,/g, "");
            const ventasNoGravadasValue = ventasNoGravadasCell.textContent.trim().replace(/,/g, "");
            const totalVentasValue = totalVentasCell.textContent.trim().replace(/,/g, "");
            const ComprasGravadasValue = comprasGravadasCell.textContent.trim().replace(/,/g, "");
            const comprasNoGravadasValue = comprasNoGravadasCell.textContent.trim().replace(/,/g, "");
            const totalComprasValue = totalComprasCell.textContent.trim().replace(/,/g, "");

            // Reemplazamos por inputstotal_compras_decimal 
            ventasGravadasCell.innerHTML = `<input type="number" class="form-control form-control-sm" id="ventasGravadas-${id}" value="${ventasGravadasValue}">`;
            ventasNoGravadasCell.innerHTML = `<input type="number" class="form-control form-control-sm" id="ventasNoGravadas-${id}" value="${ventasNoGravadasValue}">`;

            totalVentasCell.innerHTML = `<input type="number" class="form-control form-control-sm" id="ventastotal-${id}" value="${totalVentasValue}">`;
            comprasGravadasCell.innerHTML = `<input type="number" class="form-control form-control-sm" id="comprasGravadas-${id}" value="${ComprasGravadasValue}">`;

            comprasNoGravadasCell.innerHTML = `<input type="number" class="form-control form-control-sm" id="comprasNoGravadas-${id}" value="${comprasNoGravadasValue}">`;
            totalComprasCell.innerHTML = `<input type="number" class="form-control form-control-sm" id="totalCompras-${id}" value="${totalComprasValue}">`;

            // Cambiamos el botón
            editBtn.innerHTML = '<i class="ti ti-check"></i>';
            editBtn.classList.replace("btn-primary", "btn-success");
            editBtn.classList.add("btn-save");
            editBtn.classList.remove("btn-edit");

            // Agregamos botón cancelar
            const cancelBtn = document.createElement("button");
            cancelBtn.className = "btn btn-outline-secondary btn-sm ms-1 btn-cancel";
            cancelBtn.innerHTML = '<i class="ti ti-x"></i>';
            editBtn.after(cancelBtn);
        } else {
            // Guardar cambios
            const newCompras = comprasCell.querySelector("input").value;
            const newVentas = ventasCell.querySelector("input").value;

            comprasCell.textContent = parseFloat(newCompras).toLocaleString("es-PE", {
                minimumFractionDigits: 2,
            });
            ventasCell.textContent = parseFloat(newVentas).toLocaleString("es-PE", {
                minimumFractionDigits: 2,
            });

            // Restaurar botón
            editBtn.innerHTML = '<i class="ti ti-pencil"></i>';
            editBtn.classList.replace("btn-success", "btn-primary");

            row.querySelector(".btn-cancel")?.remove();
        }
    }

    // Cancelar edición
    const cancelBtn = e.target.closest(".btn-cancel");
    if (cancelBtn) {
        const row = cancelBtn.closest("tr");
        const ventasGravadasCell = row.children[1];
        const ventasNoGravadasCell = row.children[2];
        const ventasCell = row.children[3];
        const comprasGravadasCell = row.children[4];
        const comprasNoGravadasCell = row.children[5];
        const comprasCell = row.children[6];

        const ventasGravadas = ventasGravadasCell.querySelector("input").defaultValue;
        const ventasNoGravadas = ventasNoGravadasCell.querySelector("input").defaultValue;
        const ventas = ventasCell.querySelector("input").defaultValue;
        const comprasGravadas = comprasGravadasCell.querySelector("input").defaultValue;
        const comprasNoGravadas = comprasNoGravadasCell.querySelector("input").defaultValue;
        const compras = comprasCell.querySelector("input").defaultValue;

        ventasGravadasCell.textContent = parseFloat(ventasGravadas).toLocaleString("es-PE", {
            minimumFractionDigits: 2,
        });
        ventasNoGravadasCell.textContent = parseFloat(ventasNoGravadas).toLocaleString("es-PE", {
            minimumFractionDigits: 2,
        });
        ventasCell.textContent = parseFloat(ventas).toLocaleString("es-PE", {
            minimumFractionDigits: 2,
        });
        comprasGravadasCell.textContent = parseFloat(comprasGravadas).toLocaleString("es-PE", {
            minimumFractionDigits: 2,
        });
        comprasNoGravadasCell.textContent = parseFloat(comprasNoGravadas).toLocaleString("es-PE", {
            minimumFractionDigits: 2,
        });
        comprasCell.textContent = parseFloat(compras).toLocaleString("es-PE", {
            minimumFractionDigits: 2,
        });

        row.querySelector(".btn-save").classList.add("btn-edit");

        row.querySelector(".btn-edit").innerHTML = '<i class="ti ti-pencil"></i>';
        row
            .querySelector(".btn-edit")
            .classList.replace("btn-success", "btn-primary");

        row.querySelector(".btn-edit").classList.remove("btn-save");

        cancelBtn.remove();
        row.classList.remove("editing");
    }

    const btnSave = e.target.closest(".btn-save");
    if (btnSave) {
        const idp = btnSave.getAttribute("data-id");

        const compras = document.getElementById(`totalCompras-${idp}`).value;
        const ventas = document.getElementById(`ventastotal-${idp}`).value;

        const ventasGravadas = document.getElementById(`ventasGravadas-${idp}`).value;
        const ventasNoGravadas = document.getElementById(`ventasNoGravadas-${idp}`).value;

        const comprasGravadas = document.getElementById(`comprasGravadas-${idp}`).value;
        const comprasNoGravadas = document.getElementById(`comprasNoGravadas-${idp}`).value;

        const formData = new FormData();
        formData.append("idPdt", idp);
        formData.append("monto_compra", compras);
        formData.append("monto_venta", ventas);
        formData.append("ventas_gravadas", ventasGravadas);
        formData.append("ventas_no_gravadas", ventasNoGravadas);
        formData.append("compras_gravadas", comprasGravadas);
        formData.append("compras_no_gravadas", comprasNoGravadas);

        fetch(`${base_url}pdt-0621/save-montos-mypes`, {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status == "success") {
                    loadTablePeriodos(rucEmpresa.value, anios.value);
                    obtenerDatos();
                    return false;
                }

                console.log("error");
            });
    }

    const viewPdf = e.target.closest(".viewPdf");
    if (viewPdf) {
        e.preventDefault();
        const url = viewPdf.href;

        tamModal.classList.remove("modal-xl");
        tamModal.classList.add("modal-fullscreen");

        viewTable.classList.remove("col-md-12");
        viewTable.classList.add("col-md-6");
        viewTable.style.overflowY = "scroll";
        viewTable.style.height = "75vh";

        viewFilePdf.classList.remove("col-md-12");
        viewFilePdf.classList.add("col-md-6");

        viewFilePdf.innerHTML = `
      <div class="pdf-viewer mt-3" style="height: 75vh;">
        <div class="text-end">
            <button class="btn btn-danger btn-sm" onclick="closePdf()" style="margin-top: -60px;">Cerrar</button>
        </div>
        <iframe src="${url}" id="iframe-pdf" width="100%" height="100%" style="border: none;"></iframe>
      </div>
    `;

        const iframe = document.getElementById("iframe-pdf");
    }
});

function descargarExcelMypes(ruc, anio) {
    fetch(`${base_url}download-excel-mypes/${ruc}/${anio}`)
        .then((response) => response.json())
        .then((data) => {

            if (data.status == "success") {
                // Descarga automática
                window.open(data.downloadUrl, "_blank");
            } else {
                console.log('error');

            }
        });
}

function closePdf() {
    tamModal.classList.remove("modal-fullscreen");
    tamModal.classList.add("modal-xl");

    viewTable.classList.remove("col-md-6");
    viewTable.classList.add("col-md-12");
    viewTable.style.overflowY = "scroll";
    viewTable.style.height = "75vh";

    viewFilePdf.classList.remove("col-md-6");
    viewFilePdf.innerHTML = "";
}