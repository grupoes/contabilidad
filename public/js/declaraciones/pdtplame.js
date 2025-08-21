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

const estado = document.getElementById("estado");

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
    r08view.innerHTML = "";
  } else {
    loadFiles.innerHTML = "";
    r08view.innerHTML = "";
  }
});

anioDescarga.addEventListener("change", (e) => {
  const valor = e.target.value;

  if (valor != "" && periodoDescarga.value != "") {
    renderArchivos(periodoDescarga.value, valor, ruc_emp.value);
    r08view.innerHTML = "";
  } else {
    loadFiles.innerHTML = "";
    r08view.innerHTML = "";
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
        r08view.innerHTML = "";
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
            <td> 
              <button type='button' class='btn btn-danger btn-sm' title='Rectificar Archivos' onclick='rectificar(${data.id_pdtplame},${data.id_archivos_pdtplame},${data.periodo},${data.anio}, "${data.mes_descripcion}", "${data.anio_descripcion}")'>RECT</button>
              
            </td>
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
            <td><a href="${base_url}archivos/pdt/${item.nameFile}" target="__blank">${item.nameFile}</a></td>
            <td>
              <a href="#" style="font-size: 16px" title="RECTIFICAR" onclick="rectificarR08(event, ${item.id})"> <i class="fas fa-edit"> </i> </a>
              <a href="#" style="font-size: 16px" title="ELIMINAR" onclick="eliminarR08(event, ${item.id})"> <i class="fas fa-trash-alt text-danger" style="font-size: 16px" > </i> </a>
            </td>
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

const formRectificacion = document.getElementById("formRectificacion");

let cerroPorGuardar = false;

function rectificar(idPlame, idArchivoPlame, periodo, anio, mes, year) {
  $("#modalDescargarArchivo").modal("hide");
  $("#modalRectificacion").modal("show");

  const rucEmpresa = document.getElementById("rucEmpresa");
  const idplame = document.getElementById("idplame");
  const idPlameFiles = document.getElementById("idPlameFiles");
  const periodo_rect = document.getElementById("periodo_rect");
  const anio_rect = document.getElementById("anio_rect");

  rucEmpresa.value = ruc_emp.value;
  idplame.value = idPlame;
  idPlameFiles.value = idArchivoPlame;
  periodo_rect.value = periodo;
  anio_rect.value = anio;

  const titleModalRectificacion = document.getElementById(
    "titleModalRectificacion"
  );
  titleModalRectificacion.textContent = `Rectificar Archivos - ${mes} ${year}`;
}

const modalRect = document.getElementById("modalRectificacion");

modalRect.addEventListener("hidden.bs.modal", function () {
  formRectificacion.reset();

  if (!cerroPorGuardar) {
    $("#modalDescargarArchivo").modal("show");
  }

  // Reinicia la bandera
  cerroPorGuardar = false;
});

formRectificacion.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formRectificacion);

  fetch(`${base_url}rectificar-pdt-plame`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "ok") {
        cerroPorGuardar = true;

        $("#modalRectificacion").modal("hide");
        swalWithBootstrapButtons
          .fire({
            icon: "success",
            title: "Bien...",
            text: data.message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonText: "OK",
          })
          .then((result) => {
            if (result.isConfirmed) {
              $("#modalDescargarArchivo").modal("show");
              renderArchivos(
                periodo_rect.value,
                anio_rect.value,
                ruc_emp.value
              );
            }
          });

        return false;
      }

      cerroPorGuardar = true;
      $("#modalRectificacion").modal("hide");

      swalWithBootstrapButtons
        .fire({
          icon: "error",
          title: "Oops...",
          text: data.message,
          allowOutsideClick: false,
          allowEscapeKey: false,
          confirmButtonText: "OK",
        })
        .then((result) => {
          if (result.isConfirmed) {
            $("#modalRectificacion").modal("show");
          }
        });
    });
});

function rectificarR08(e, id) {
  e.preventDefault();

  const idR08 = document.getElementById("idR08");

  idR08.value = id;

  $("#modalDescargarArchivo").modal("hide");

  $("#modalRectR08").modal("show");
}

let cerrarR08 = false;

const formRectR08 = document.getElementById("formRectR08");

const modalRectR08 = document.getElementById("modalRectR08");

modalRectR08.addEventListener("hidden.bs.modal", function () {
  formRectR08.reset();

  if (!cerrarR08) {
    $("#modalDescargarArchivo").modal("show");
  }

  // Reinicia la bandera
  cerrarR08 = false;
});

formRectR08.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formRectR08);

  fetch(`${base_url}rectificar-pdt-plame/r08`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "ok") {
        cerrarR08 = true;

        $("#modalRectR08").modal("hide");
        swalWithBootstrapButtons
          .fire({
            icon: "success",
            title: "Bien...",
            text: data.message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            confirmButtonText: "OK",
          })
          .then((result) => {
            if (result.isConfirmed) {
              $("#modalDescargarArchivo").modal("show");
              viewR08(data.idplame);
            }
          });

        return false;
      }

      cerrarR08 = true;
      $("#modalRectR08").modal("hide");

      swalWithBootstrapButtons
        .fire({
          icon: "error",
          title: "Oops...",
          text: data.message,
          allowOutsideClick: false,
          allowEscapeKey: false,
          confirmButtonText: "OK",
        })
        .then((result) => {
          if (result.isConfirmed) {
            $("#modalRectR08").modal("show");
          }
        });
    });
});

function eliminarR08(e, id) {
  e.preventDefault();

  $("#modalDescargarArchivo").modal("hide");

  swalWithBootstrapButtons
    .fire({
      title: "¿Esta seguro de eliminar el archivo R08?",
      text: "Ya no podrá revertir después!",
      icon: "error",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si, eliminar!",
      cancelButtonText: "Cancelar",
      allowOutsideClick: false,
      allowEscapeKey: false,
    })
    .then((result) => {
      if (result.isConfirmed) {
        fetch(`${base_url}eliminar-pdt-plame/r08/${id}`)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "ok") {
              $("#modalDescargarArchivo").modal("show");
              viewR08(data.idplame);
            }
          });
      } else {
        $("#modalDescargarArchivo").modal("show");
      }
    });
}
