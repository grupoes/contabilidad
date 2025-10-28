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
const contentPdts = document.getElementById("contentPdts");

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
                    <button type="button" class="btn btn-success" title="Subir archivos" onclick="modalArchivo(${
                      cont.id
                    }, '${
      cont.ruc
    }')"> <i class="ti ti-file-upload"></i> </button> 
                    <button type="button" class="btn btn-info" title="Descargar archivos" onclick="descargarArchivos(${
                      cont.id
                    },'${
      cont.ruc
    }')"> <i class="ti ti-file-download"></i> </button> 
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

function descargarArchivos(id, ruc) {
  $("#modalDescargarArchivo").modal("show");

  rucEmpresa.value = ruc;

  periodo_file.value = "";
  anio_file.value = "";

  loadFiles.innerHTML = "";

  const titleModalDownload = document.getElementById("titleModalDownload");

  fetch(base_url + "contribuyentes/getId/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleModalDownload.innerHTML = `DESCARGAR ARCHIVOS - <span class="text-primary" id="nameComp">${data.razon_social}</span>`;
    });
}

function descargaMasiva(id) {
  $("#modalDescargarArchivoMasivo").modal("show");

  formConsulta.reset();
  contentPdts.innerHTML = "";

  const correo = document.getElementById("correo");
  const whatsapp = document.getElementById("whatsapp");

  correo.value = "";
  whatsapp.value = "";

  const titleModalConsult = document.getElementById("titleModalConsult");
  const empresa_ruc = document.getElementById("empresa_ruc");

  fetch(base_url + "contribuyentes/getId/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleModalConsult.textContent = "DESCARGAR PDT - " + data.razon_social;
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

  fetch(`${base_url}contribuyentes/file-save-pdt0621`, {
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

        if (data.texto == "") {
          setTimeout(() => {
            $("#modalIngresarMontos").modal("show");

            const link_pdt = document.getElementById("link_pdt");
            link_pdt.href = `${base_url}${data.ruta}`;

            const idPdt = document.getElementById("idPdt");
            idPdt.value = data.idpdt;
          }, 2000);
        }

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

function renderArchivos(periodo, anio, ruc) {
  const formData = new FormData();
  formData.append("periodo", periodo);
  formData.append("anio", anio);
  formData.append("ruc", ruc);

  fetch(base_url + "consulta-pdt-renta", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      viewArchivos(data, ruc);
    });
}

function viewArchivos(data, ruc) {
  let html = "";

  data.forEach((archivo) => {
    html += `
        <tr>
            <td>${archivo.mes_descripcion}</td>
            <td>${archivo.anio_descripcion}</td>
            <td>
                <a href='${base_url}archivos/pdt/${archivo.nombre_pdt}' class='btn btn-success btn-sm' target='_blank' title='Descargar Renta'>PDT</a> <a href='${base_url}archivos/pdt/${archivo.nombre_constancia}' target='_blank' class='btn btn-primary btn-sm' title='Descargar constancia'>CONSTANCIA</a>
            </td>
            <td>
              <button type='button' class='btn btn-danger' title='Rectificar Archivos' onclick='rectificar(${archivo.id_pdt_renta},${archivo.id_archivos_pdt},${archivo.periodo},${archivo.anio}, ${ruc}, "${archivo.mes_descripcion}", "${archivo.anio_descripcion}")'>RECT</button>
                <button type='button' class='btn btn-warning' title='Detalle' onclick='details_archivos(${archivo.id_pdt_renta}, "${archivo.mes_descripcion}", "${archivo.anio_descripcion}")'>DET</button>
            </td>
        </tr>
        `;
  });

  loadFiles.innerHTML = html;
}

formConsulta.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formConsulta);

  fetch(base_url + "consulta-pdt-rango", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      viewPdts(data.data);
    });
});

function viewPdts(data) {
  let html = "";

  if (data.length > 0) {
    data.forEach((pdt) => {
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

  contentPdts.innerHTML = html;
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

  fetch(base_url + "rectificacion-pdt-renta", {
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

      if (data.texto == "") {
        Swal.fire({
          position: "top-center",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        if (data.texto == "") {
          setTimeout(() => {
            $("#modalIngresarMontos").modal("show");

            const link_pdt = document.getElementById("link_pdt");
            link_pdt.href = `${base_url}${data.ruta}`;

            const idPdt = document.getElementById("idPdt");
            idPdt.value = data.idpdt;
          }, 2000);
        }
      } else {
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
      }
    });
});

const getFilesDetails = document.getElementById("getFilesDetails");
const titleDetallePdt = document.getElementById("titleDetallePdt");

function details_archivos(id_pdt_renta, periodo, anio) {
  $("#modalDescargarArchivo").modal("hide");
  $("#modalDetalle").modal("show");

  const nameComp = document.getElementById("nameComp").textContent;

  titleDetallePdt.innerHTML = `<span class="text-secondary">DETALLE ARCHIVOS</span> | ${nameComp} | ${periodo} - ${anio}`;

  getFilesDetails.innerHTML = "";

  fetch(base_url + "pdt-0621/get-files-details/" + id_pdt_renta)
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      data.forEach((file) => {
        if (file.estado == 1) {
          html += `
            <tr>
                <td>
                    <a href='${base_url}archivos/pdt/${file.nombre_pdt}' target='_blank'>${file.nombre_pdt}</a>
                </td>
                <td>
                  <a href='${base_url}archivos/pdt/${file.nombre_constancia}' target='_blank'>${file.nombre_constancia}</a>
                </td>
            </tr>
            `;
        } else {
          html += `
            <tr>
                <td>${file.nombre_pdt}</td>
                <td>${file.nombre_constancia}</td>
            </tr>
            `;
        }
      });

      getFilesDetails.innerHTML = html;
    });
}

const formMontosComprasVentas = document.getElementById(
  "formMontosComprasVentas"
);

formMontosComprasVentas.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formMontosComprasVentas);

  fetch(base_url + "pdt-0621/save-montos", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalIngresarMontos").modal("hide");

        Swal.fire({
          position: "top-center",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });
        return false;
      }

      swalWithBootstrapButtons
        .fire({
          title: "Error!",
          text: data.message,
          icon: "error",
        })
        .then((result) => {
          if (result.isConfirmed) {
            $("#modalIngresarMontos").modal("show");
          }
        });
    });
});

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
