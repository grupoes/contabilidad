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
const ruc_emp = document.getElementById("ruc_emp");
const loadFiles = document.getElementById("contentPdt");

const anioDescarga = document.getElementById("anioDescarga");
const periodoDescarga = document.getElementById("periodoDescarga");

const r08view = document.getElementById("r08view");

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
                    })"> <i class="ti ti-file-upload"></i> </button> 
                    <button type="button" class="btn btn-info" title="Descargar archivos" onclick="descargarArchivos(${
                      cont.ruc
                    }, ${
      cont.id
    })"> <i class="ti ti-file-download"></i> </button>
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

function modalArchivo(id) {
  $("#modalArchivo").modal("show");

  const idTabla = document.getElementById("idTabla");
  idTabla.value = id;

  const titleModalArchivo = document.getElementById("titleModalArchivo");

  formArchivo.reset();

  const ruc_empresa_save = document.getElementById("ruc_empresa_save");

  fetch(base_url + "contribuyentes/getId/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleModalArchivo.textContent = "SUBIR ARCHIVOS - " + data.razon_social;
      ruc_empresa_save.value = data.ruc;
    });
}

function descargarArchivos(ruc, id) {
  $("#modalDescargarArchivo").modal("show");
  ruc_emp.value = ruc;

  anioDescarga.value = "";
  periodoDescarga.value = "";

  loadFiles.innerHTML = "";
  r08view.innerHTML = "";

  const titleModalDescargar = document.getElementById("titleModalDescargar");

  fetch(base_url + "contribuyentes/getId/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleModalDescargar.textContent =
        "Descargar Archivos - " + data.razon_social;
    });
}

formArchivo.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formArchivo);

  fetch(`${base_url}contribuyentes/file-save-pdtplame`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
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
            console.log("El usuario hizo clic en OK");
            $("#modalArchivo").modal("show");
            // Aquí puedes realizar cualquier acción adicional
          }
        });
    });
});

periodoDescarga.addEventListener("change", (e) => {
  const valor = e.target.value;

  if (valor != "" && anioDescarga.value != "") {
    renderArchivos(valor, anioDescarga.value, ruc_emp.value);
  } else {
    loadFiles.innerHTML = "";
  }
});

anioDescarga.addEventListener("change", (e) => {
  const valor = e.target.value;

  if (valor != "" && periodoDescarga.value != "") {
    renderArchivos(periodoDescarga.value, valor, ruc_emp.value);
  } else {
    loadFiles.innerHTML = "";
  }
});

function renderArchivos(periodo, anio, ruc) {
  const formData = new FormData();
  formData.append("periodo", periodo);
  formData.append("anio", anio);
  formData.append("ruc", ruc);

  fetch(base_url + "consulta-pdt-plame", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data != null) {
        viewArchivos(data);
      } else {
        loadFiles.innerHTML = "";
      }
    });
}

function viewArchivos(data) {
  let html = "";

  let r01 = "";
  let r12 = "";
  let constancia = "";
  let r08 = "";

  if (data.archivo_planilla != "") {
    r01 = `<a href='${base_url}archivos/pdt/${data.archivo_planilla}' class='btn btn-success btn-sm' target='_blank' title='Descargar Renta'>R01</a>`;
  }

  if (data.archivo_honorarios != "") {
    r12 = `<a href='${base_url}archivos/pdt/${data.archivo_honorarios}' target='_blank' class='btn btn-info btn-sm' title='Descargar constancia'>R12</a>`;
  }

  if (data.archivo_constancia != "") {
    constancia = `<a href='${base_url}archivos/pdt/${data.archivo_constancia}' target='_blank' class='btn btn-warning btn-sm' title='Descargar constancia'>CONST</a>`;
  }

  if (data.r08 == "1") {
    r08 = `<button type="button" class='btn btn-primary btn-sm' onclick="viewR08(${data.id_pdtplame})" data-id="${data.id_pdtplame}" title='Descargar R08'>R08</button>`;
  }

  html += `
        <tr>
            <td>${data.mes_descripcion}</td>
            <td>${data.anio_descripcion}</td>
            <td>
              ${r01}
              ${r12}
              ${constancia}
              ${r08}
            </td>
            <td> <button type='button' class='btn btn-danger' title='Rectificar Archivos' onclick='rectificar(${data.id_pdtplame},${data.id_archivos_pdtplame},${data.periodo},${data.anio})'>RECT</button>
                <button type='button' class='btn btn-warning' title='Detalle' onclick='details_archivos(${data.id_pdtplame})'>DET</button></td>
        </tr>
        `;

  loadFiles.innerHTML = html;
}

function viewR08(id) {
  fetch(base_url + "consulta-pdt-plame/r08/" + id)
    .then((res) => res.json())
    .then((data) => {
      viewR08_archivo(data);
    });
}

function viewR08_archivo(data) {
  let tr = "";

  data.forEach((item) => {
    tr += `
        <tr>
            <td><a href="${base_url}archivos/pdt/${item.nameFile}" target="__blank">${item.nameFile}</a></>
        </tr>
        `;
  });

  let html = `
    <h4 class="d-flex justify-content-between align-items-center">
        R08
        <a href="${base_url}descargarR08All/${data[0].plameId}" download title="Descargar Todo">
            <i class="fas fa-cloud-download-alt"></i>
        </a>
    </h4>
    <table class="table">
        <tbody>
            ${tr}
        </tbody>
    </table>
    `;

  r08view.innerHTML = html;
}
