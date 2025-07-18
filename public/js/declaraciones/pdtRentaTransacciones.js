document.addEventListener("DOMContentLoaded", function () {
  obtenerDatos();
});

const anios = document.getElementById("anios");
const searchContribuyente = document.getElementById("searchContribuyente");
const filterTotales = document.getElementById("filterTotales");

const listaEmpresasMontos = document.getElementById("listaEmpresasMontos");

function obtenerDatos() {
  listaEmpresasMontos.innerHTML = `<p class="text-center fw-bold">Cargando datos...</p>`;

  const formData = new FormData();
  formData.append("anio", anios.value);
  formData.append("search", searchContribuyente.value);
  formData.append("filter", filterTotales.value);

  fetch(`${base_url}declaraciones/obtenerDatosPdtRentaTransacciones`, {
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
    let style = "";

    if (item.total_compras >= 300000 || item.total_ventas >= 300000) {
      style = 'style="border: 1px solid red; border-radius: 10px;"';
    }

    html += `
    <div class="col-md-4 col-xxl-3">
        <div class="card shadow-none border mb-0">
            <div class="card-body p-3" ${style}>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h5 class="mb-0">${item.razon_social}</h5>
                    <button type="button" class="btn btn-light btn-sm" title="Ver periodos" onclick="loadPeriodos('${item.ruc}')"><i class="ti ti-eye f-18"></i></button>

                </div>

                <p class="mb-0">${item.ruc}</p>

                <div class="row g-3 mt-1">
                    <div class="col-sm-6 mt-1">
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
                    <div class="col-sm-6 mt-1">
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
  const title = document.getElementById("titleContribuyente");
  title.innerHTML = "";
  listaPeriodos.innerHTML = "";

  fetch(`${base_url}declaraciones/periodosPdtRenta/${ruc}/${anios.value}`)
    .then((response) => response.json())
    .then((data) => {
      title.innerHTML = `Periodos ${data[0].anio_descripcion} - ${data[0].razon_social}`;
      viewPeriodos(data);
    });
}

function viewPeriodos(data) {
  let html = "";

  let total_compras = 0;
  let total_ventas = 0;

  data.forEach((item) => {
    total_compras += parseFloat(item.total_compras);
    total_ventas += parseFloat(item.total_ventas);

    html += `
    <tr>
        <td>${item.mes_descripcion}</td>
        <td>${item.total_ventas_decimal}</td>
        <td>${item.total_compras_decimal}</td>
        <td>
            <a href="${base_url}archivos/pdt/${item.nombre_pdt}" target="__blank" class="btn btn-danger btn-sm" title="Ver">
                <i class="ti ti-file-text f-26"></i>
            </a>
        </td>
    </tr>
    `;
  });

  html += `
    <tr>
        <td></td>
        <td> <strong>${total_ventas.toLocaleString("en-US", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })}</strong></td>
        <td> <strong>${total_compras.toLocaleString("en-US", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })}</strong></>
        <td></td>
    </tr>
    `;

  listaPeriodos.innerHTML = html;
}
