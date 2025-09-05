const tableBody = document.getElementById("tableBody");
const idContribuyente = document.getElementById("idcontribuyente");

const div_voucher = document.getElementById("div-voucher");
const voucher = document.getElementById("voucher");

const sedeEfectivo = document.getElementById("sedeEfectivo");

const formPago = document.getElementById("formPago");

renderPagos(idContribuyente.value);
renderPagosServidor(idContribuyente.value);

function renderPagos(idcontribuyente) {
  fetch(base_url + "render-pagos-servidor/" + idcontribuyente)
    .then((res) => res.json())
    .then((data) => {
      viewPagos(data);
    });
}

function renderPagosServidor(idcontribuyente) {
  fetch(base_url + "pagos/render-amortizacion-servidor/" + idcontribuyente)
    .then((res) => res.json())
    .then((data) => {
      viewPagosHonorarios(data);
    });
}

function viewPagos(data) {
  let html = "";

  const length = data.length;

  const currentDate = new Date()
    .toLocaleString("en-CA", {
      timeZone: "America/Lima",
    })
    .split(",")[0];

  data.forEach((pago, index) => {
    console.log(pago.fechaPago);

    if (currentDate == pago.fechaPago) {
      console.log("hola");
    }

    let estado = ``;

    if (pago.estado == "pagado") {
      estado = `<span class="badge bg-light-success f-12">${pago.estado}</span>`;
    } else if (pago.estado == "pendiente") {
      estado = `<span class="badge bg-light-warning f-12">${pago.estado}</span>`;
    } else {
      estado = `<span class="badge bg-light-danger f-12">${pago.estado}</span>`;
    }

    html += `
        <tr>
            <td>${pago.fecha_inicio}</td>
            <td>${pago.fecha_fin}</td>
            <td>${pago.fecha_proceso !== null ? pago.fecha_proceso : ""}</td>
            <td>${pago.fecha_pago !== null ? pago.fecha_pago : ""}</td>
            <td><span class="badge bg-light-success f-14" onclick="verNotaVenta(${
              pago.id
            })" style="cursor: pointer">${pago.monto_total}</span></td>
            <td>${pago.monto_pagado}</td>
            <td>${pago.monto_pendiente}</td>
            <td>${estado}</td>
        </tr>
        `;
  });

  tableBody.innerHTML = html;
}

function viewPagosHonorarios(data) {
  const tablePagos = document.getElementById("tablePagos");

  let html = "";

  const currentDate = new Date()
    .toLocaleString("en-CA", {
      timeZone: "America/Lima",
    })
    .split(",")[0];

  data.forEach((pago, index) => {
    let pagos = pago.pagos;

    let pagosHtml = `<ul>`;

    pagos.forEach((item) => {
      pagosHtml += `<li> ${item.fecha_inicio} - ${item.fecha_fin} (${item.monto})</li>`;
    });

    pagosHtml += `</ul>`;

    let botonDelete = "";

    if (index === 0) {
      if (currentDate == pago.fecha) {
        botonDelete += `
              <a href="#" onclick="deletePago(event, ${pago.id})" title="Eliminar Pago"> <i class="fas fa-trash text-danger"></i> </a>
              `;
      }
    }

    botonDelete += `
              <a href="#" class="ms-2" onclick="editPago(event, ${pago.id}, ${index})" title="Editar Pago"> <i class="fas fa-edit text-info"></i> </a>
              `;

    html += `
        <tr>
            <td>${pago.registro}</td>
            <td>${pago.fecha_pago}</td>
            <td>${pagosHtml}</td>
            <td>${pago.metodo}</td>
            <td>${pago.monto}</td>
            <td> 
                <a href="#" data-lightbox="${base_url}servidor/${pago.vaucher}" onclick="verVaucher(event, ${pago.id})"> Ver vaucher </a>
            </td>
            <td>
                ${botonDelete}
            </td>
        </tr>
        `;
  });

  tablePagos.innerHTML = html;
}

const metodoPago = document.getElementById("metodoPago");
const countPagos = document.getElementById("countPagos");

formPago.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formPago);

  Swal.fire({
    title: "¿Estas seguro?",
    text: "No podrá revertir después!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Cobrar!",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      showLoader();

      const messageSpinner = document.getElementById("messageSpinner");
      messageSpinner.textContent = "Registrando pago del servidor...";

      fetch(`${base_url}pagos/pagar-servidor`, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          hideLoader();
          if (data.status === "success") {
            metodoPago.value = "";
            voucher.value = "";

            formPago.reset();

            div_voucher.setAttribute("hidden", true);
            voucher.removeAttribute("required");

            Swal.fire({
              position: "top-end",
              icon: "success",
              title: data.message,
              showConfirmButton: false,
              timer: 1500,
            });

            renderPagos(idContribuyente.value);
            renderPagosServidor(idContribuyente.value);

            getMontoPendiente();

            return false;
          }

          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!",
          });
        });
    }
  });
});

metodoPago.addEventListener("change", (e) => {
  const valor = e.target.value;

  if (valor == 1 || valor == "") {
    div_voucher.setAttribute("hidden", true);
    voucher.removeAttribute("required");

    sedeEfectivo.classList.add("col-md-4");

    let select = "";

    fetch(`${base_url}all-sedes`)
      .then((res) => res.json())
      .then((data) => {
        console.log(data);
        data.forEach((sede) => {
          select += `
                <option value="${sede.id}"> ${sede.nombre_sede} </option>
            `;
        });

        let html = `
      <div class="mb-3">
        <label class="form-label" for="selectSede">Sede</label>
        <select class="form-select" id="selectSede" name="selectSede" required="true">
          <option value="">Selecionar...</option>
          ${select}
        </select>
      </div>
      `;

        sedeEfectivo.innerHTML = html;
      });
  } else {
    div_voucher.removeAttribute("hidden");
    voucher.setAttribute("required", true);

    sedeEfectivo.innerHTML = "";
  }
});

var lightboxModal = new bootstrap.Modal(
  document.getElementById("lightboxModal")
);

function verVaucher(e, idPago) {
  e.preventDefault();

  const pagoId = document.getElementById("pagoId");
  pagoId.value = idPago;

  var images_path = e.target;

  if (images_path.tagName == "IMG") {
    images_path = images_path.parentNode;
  }

  var recipient = images_path.getAttribute("data-lightbox");
  var image = document.querySelector(".modal-image");
  image.setAttribute("src", recipient);
  lightboxModal.show();

  const btnDescargar = document.getElementById("btnDescargarVoucher");
  btnDescargar.setAttribute("href", recipient);
  btnDescargar.setAttribute("download", "vaucher.jpg");

  image.style.transform = "scale(1)";
  let scale = 1;

  image.onwheel = function (e) {
    e.preventDefault();
    scale += e.deltaY * -0.001;
    scale = Math.min(Math.max(1, scale), 3); // Zoom entre 1x y 3x
    image.style.transform = `scale(${scale})`;
  };
}

function deletePago(e, id) {
  e.preventDefault();

  const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: "btn btn-success",
      cancelButton: "btn btn-danger",
    },
    buttonsStyling: false,
  });
  swalWithBootstrapButtons
    .fire({
      title: "¿Está seguro?",
      text: "¡No podrás revertir esto!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar!",
      cancelButtonText: "No, cancelar!",
      reverseButtons: true,
    })
    .then((result) => {
      if (result.isConfirmed) {
        showLoader();

        const messageSpinner = document.getElementById("messageSpinner");
        messageSpinner.textContent = "Eliminando pago...";
        fetch(`${base_url}pagos/delete-pago-servidor/${id}`)
          .then((res) => res.json())
          .then((data) => {
            hideLoader();
            if (data.status === "success") {
              renderPagos(idContribuyente.value);
              renderPagosServidor(idContribuyente.value);
              getMontoPendiente();

              Swal.fire({
                position: "top-end",
                icon: "success",
                title: data.message,
                showConfirmButton: false,
                timer: 1500,
              });
            }
          });
      }
    });
}

function editarVoucher() {
  $("#lightboxModal").modal("hide");
  $("#modalEditVoucher").modal("show");

  const pagoId = document.getElementById("pagoId");
  const idPago = document.getElementById("idPago");

  idPago.value = pagoId.value;
}

const formEditImage = document.getElementById("formEditImage");

formEditImage.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formEditImage);

  fetch(`${base_url}pagos/update-voucher-servidor`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalEditVoucher").modal("hide");

        Swal.fire({
          position: "top-end",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        renderPagosServidor(idContribuyente.value);
        return false;
      }

      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!",
      });
    });
});

function editPago(e, id, index) {
  e.preventDefault();

  $("#modalPago").modal("show");

  const pagoId = document.getElementById("id_Pago");
  pagoId.value = id;

  const currentDate = new Date()
    .toLocaleString("en-CA", {
      timeZone: "America/Lima",
    })
    .split(",")[0];

  fetch(`${base_url}pagos/get-pago/${id}`)
    .then((res) => res.json())
    .then((data) => {
      const metodoPago = document.getElementById("metodo_pago");
      const monto = document.getElementById("monto_mov");
      const datePago = document.getElementById("datePago");
      const montoActual = document.getElementById("montoActual");

      const idMonto = document.getElementById("idMonto");
      const idFechaPago = document.getElementById("idFechaPago");

      if (currentDate == data.fecha) {
        idMonto.removeAttribute("hidden");
        idFechaPago.removeAttribute("hidden");

        if (index != 0) {
          idMonto.setAttribute("hidden", true);
        }
      } else {
        idMonto.setAttribute("hidden", true);
        idFechaPago.setAttribute("hidden", true);
      }

      montoActual.value = data.monto;

      metodoPago.value = data.metodo_pago_id;
      monto.value = data.monto;
      datePago.value = data.fecha_pago;
    });
}

const formEditPago = document.getElementById("formEditPago");

formEditPago.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formEditPago);

  showLoader();

  const messageSpinner = document.getElementById("messageSpinner");
  messageSpinner.textContent = "Actualizando pago...";

  fetch(`${base_url}pagos/update-pago-servidor`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      hideLoader();
      if (data.status === "success") {
        $("#modalPago").modal("hide");

        Swal.fire({
          position: "top-end",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        renderPagosServidor(idContribuyente.value);
        renderPagos(idContribuyente.value);
        return false;
      }

      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!",
      });
    });
});

function getMontoPendiente() {
  const monto = document.getElementById("monto");

  fetch(`${base_url}pagos/monto-servidor/${idContribuyente.value}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.status == "success") {
        monto.value = data.monto;
      } else {
        alert(data.message);
      }
    });
}

getMontoPendiente();

function historialPagos() {
  const historialSuscripcion = document.getElementById("historialSuscripcion");

  fetch(`${base_url}pagos/historial-pagos/${idContribuyente.value}`)
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      data.forEach((item) => {
        html += `
          <li class="list-group-item d-flex justify-content-between align-items-center" style="padding: 10px"> ${item.fechaInicio} <span class="badge bg-primary rounded-pill">${item.monto_mensual}</span></li>
        `;
      });

      historialSuscripcion.innerHTML = html;
    });
}

//historialPagos();
const firstDate = document.getElementById("firstDate");

function addMontoServidor(e, id) {
  e.preventDefault();

  $("#modalAddMonto").modal("show");

  fetch(`${base_url}render-montos/${idContribuyente.value}`)
    .then((res) => res.json())
    .then((data) => {
      $("#modalAddMonto").modal("show");

      const length = data.length;

      if (length == 0) {
        firstDate.innerHTML = `
          <label class="form-label" for="primeraFecha">Primera fecha</label>
          <input type="date" class="form-control" id="primeraFecha" name="primeraFecha" required />
        `;
      } else {
        firstDate.innerHTML = "";
      }
    });
}

const renderMontos = document.getElementById("renderMontos");
const btnSubmit = document.getElementById("btnSubmit");

function renderMontosServidor() {
  fetch(`${base_url}render-montos/${idContribuyente.value}`)
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      const length = data.length;

      if (length != 0) {
        data.forEach((item) => {
          html += `
            <li class="list-group-item d-flex justify-content-between align-items-center">${item.fecha_inicio} <span class="badge bg-primary rounded-pill">S/ ${item.monto}</span></li>
          `;
        });

        btnSubmit.removeAttribute("disabled");
      } else {
        btnSubmit.setAttribute("disabled", true);
      }

      renderMontos.innerHTML = html;
    });
}

renderMontosServidor();

const formAddMonto = document.getElementById("formAddMonto");

formAddMonto.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formAddMonto);

  fetch(`${base_url}montos/add-monto`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalAddMonto").modal("hide");

        Swal.fire({
          position: "top-center",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        renderMontosServidor();
        renderPagos(idContribuyente.value);
        getMontoPendiente();
        return false;
      }

      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!",
      });
    });
});

function verNotaVenta(id) {
  alert(id);
}

const linkServidorTab = document.getElementById("linkServidorTab");

linkServidorTab.addEventListener("click", (e) => {
  window.location.href = `${base_url}cobros?tab=servidor`;
});
