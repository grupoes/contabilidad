const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const tableBody = document.getElementById("tableBody");

const titleModalArchivo = document.getElementById("titleModalArchivo");
const formArchivo = document.getElementById("formArchivo");
const anioDescarga = document.getElementById("anioDescarga");
const tipoPdt = document.getElementById("tipoPdt");

const listFiles = document.getElementById("listFiles");

const desde = document.getElementById("desde");
const hasta = document.getElementById("hasta");

renderContribuyentes();

function renderContribuyentes() {
  fetch(`${base_url}contribuyentes/render`)
    .then((res) => res.json())
    .then((data) => {
      vistaContribuyentes(data);
    });
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
                    <button type="button" class="btn btn-success" title="Subir archivos" onclick="modalArchivo(${
                      cont.id
                    }, ${
      cont.ruc
    })"> <i class="ti ti-file-upload"></i> </button> 
                    <button type="button" class="btn btn-info" title="Descargar archivos" onclick="descargarArchivos(${
                      cont.id
                    })"> <i class="ti ti-file-download"></i> </button> 
                    <button type="button" class="btn btn-primary" title="Descargar archivos" onclick="descargaMasiva(${
                      cont.id
                    })"> <i class="ti ti-file-export"></i> </button>
                </div>
            </td>
        </tr>
        `;
  });

  $($table).DataTable().destroy();

  tableBody.innerHTML = html;

  const newcs = $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);
}

const idRuc = document.getElementById("idruc");

function modalArchivo(id, ruc) {
  $("#modalArchivo").modal("show");

  idRuc.value = ruc;

  formArchivo.reset();

  fetch(base_url + "contribuyentes/getId/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleModalArchivo.textContent = "Subir Archivos - " + data.razon_social;

      verificarConfiguracion(ruc);
    });
}

const notingConfig = document.getElementById("notingConfig");
const widthConfig = document.getElementById("widthConfig");

const typePdt = document.getElementById("typePdt");
const cargo = document.getElementById("cargo");
const divMonto = document.getElementById("divMonto");
const divDescripcion = document.getElementById("divDescripcion");

function verificarConfiguracion(ruc) {
  fetch(base_url + "pdtAnual/verificar/" + ruc)
    .then((res) => res.json())
    .then((data) => {
      const monto = document.getElementById("monto");
      monto.value = data.montoAnual;

      const pdts = data.tipo_pdt;
      if (pdts.length > 0) {
        widthConfig.removeAttribute("hidden");
        notingConfig.setAttribute("hidden", true);

        let html = `<option value="">Seleccione...</option>`;

        pdts.forEach((pdt) => {
          html += `<option value="${pdt.id_pdt}">${pdt.pdt_descripcion}</option>`;
        });

        typePdt.innerHTML = html;
      } else {
        notingConfig.removeAttribute("hidden");
        widthConfig.setAttribute("hidden", true);

        typePdt.innerHTML = "";
      }
    });
}

const titleModalDescargar = document.getElementById("titleModalDescargar");
const numRuc = document.getElementById("numRuc");
const noConfig = document.getElementById("noConfig");
const opciones = document.getElementById("opciones");
const tableFiles = document.getElementById("tableFiles");

function descargarArchivos(id) {
  $("#modalDescargarArchivo").modal("show");

  anioDescarga.value = "";
  listFiles.innerHTML = "";

  fetch(base_url + "contribuyentes/getId/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleModalDescargar.textContent =
        "Descargar Archivos - " + data.razon_social;
      numRuc.value = data.ruc;

      fetch(base_url + "pdtAnual/verificar/" + data.ruc)
        .then((res) => res.json())
        .then((data) => {
          const pdts = data.tipo_pdt;
          let html = "";

          if (pdts.length > 0) {
            html += `<option value="0">TODOS</option>`;

            pdts.forEach((pdt) => {
              html += `<option value="${pdt.id_pdt}">${pdt.pdt_descripcion}</option>`;
            });

            tipoPdt.innerHTML = html;

            noConfig.setAttribute("hidden", true);
            opciones.removeAttribute("hidden");
            tableFiles.removeAttribute("hidden");
          } else {
            tipoPdt.innerHTML = html;

            noConfig.removeAttribute("hidden");
            opciones.setAttribute("hidden", true);
            tableFiles.setAttribute("hidden", true);
          }
        });
    });
}

const titleModalMasivo = document.getElementById("titleModalMasivo");
const rucNum = document.getElementById("rucNum");
const noConfigMasivo = document.getElementById("noConfigMasivo");
const consulting = document.getElementById("consulting");
const btnsSend = document.getElementById("btnsSend");

function descargaMasiva(id) {
  $("#modalDescargarArchivoMasivo").modal("show");

  fetch(base_url + "contribuyentes/getId/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleModalMasivo.textContent =
        "Descargar Archivos - " + data.razon_social;
      rucNum.value = data.ruc;

      fetch(base_url + "pdtAnual/verificar/" + data.ruc)
        .then((res) => res.json())
        .then((data) => {
          const pdts = data.tipo_pdt;

          if (pdts.length > 0) {
            noConfigMasivo.setAttribute("hidden", true);
            consulting.removeAttribute("hidden");
            btnsSend.removeAttribute("hidden");
          } else {
            noConfigMasivo.removeAttribute("hidden");
            consulting.setAttribute("hidden", true);
            btnsSend.setAttribute("hidden", true);
          }
        });
    });
}

cargo.addEventListener("click", (e) => {
  if (e.target.checked) {
    divMonto.removeAttribute("hidden");
    divDescripcion.removeAttribute("hidden");
  } else {
    divMonto.setAttribute("hidden", true);
    divDescripcion.setAttribute("hidden", true);
  }
});

const formConsulta = document.getElementById("formConsulta");
const listfiles = document.getElementById("list-files");

formConsulta.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formConsulta);

  fetch(base_url + "pdtAnual-consulta", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      const datos = data.data;

      datos.forEach((pdt) => {
        html += `
            <tr>
                <td>${pdt.anio_descripcion}</td>
                <td>
                <a href='${base_url}archivos/pdt/${pdt.pdt}' target='_blank'>PDT</a>
                </td>
                <td>
                <a href='${base_url}archivos/pdt/${pdt.constancia}' target='_blank'>CONSTANCIA</a>
                </td>
            </tr>
            `;
      });

      listfiles.innerHTML = html;
    });
});

anioDescarga.addEventListener("change", (e) => {
  const anio = e.target.value;

  const tipo_pdt = document.getElementById("tipoPdt");

  getBalance(anio, tipo_pdt.value);
});

tipoPdt.addEventListener("change", (e) => {
  const pdt = e.target.value;

  getBalance(anioDescarga.value, pdt);
});

function getBalance(anio, tipopdt) {
  const formData = new FormData();
  formData.append("anio", anio);
  formData.append("pdt", tipopdt);
  formData.append("ruc", numRuc.value);

  fetch(base_url + "pdtAnual/getBalance", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      viewBalance(data);
    });
}

function viewBalance(data) {
  let html = "";

  data.forEach((pdt) => {
    html += `
        <tr>
            <td>${pdt.anio_descripcion}</td>
            <td>${pdt.pdt_descripcion}</td>
            <td>
              <a href="${base_url}public/archivos/pdt/${pdt.pdt}" class='btn btn-success btn-sm' target='_blank' title='Descargar PDT'>PDT</a> 
              <a href="${base_url}public/archivos/pdt/${pdt.constancia}" target='_blank' class='btn btn-primary btn-sm' title='Descargar constancia'>CONSTANCIA</a>

              <button type='button' class='btn btn-danger btn-sm' title='Rectificar Archivos' onclick='rectificar(${pdt.id_pdt_anual},${pdt.id_archivo_anual},${pdt.periodo},${pdt.id_pdt_tipo})'>RECT</button>
              <button type='button' class='btn btn-warning btn-sm' title='Detalle' onclick='details_archivos(${pdt.id_pdt_anual})'>DET</button>
            </td>
        </tr>
        `;
  });

  listFiles.innerHTML = html;
}

formArchivo.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formArchivo);

  fetch(base_url + "pdtAnual/guardar", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      console.log(data);
    });
});
