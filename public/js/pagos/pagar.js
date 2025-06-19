const tableBody = document.getElementById("tableBody");
const idContribuyente = document.getElementById("idcontribuyente");

const div_voucher = document.getElementById("div-voucher");
const voucher = document.getElementById("voucher");

const formPago = document.getElementById("formPago");

renderPagos(idContribuyente.value);
renderPagosHonorarios(idContribuyente.value);

function renderPagos(idcontribuyente) {
  fetch(base_url + "pagos/lista-pagos/" + idcontribuyente)
    .then((res) => res.json())
    .then((data) => {
      viewPagos(data);
    });
}

function renderPagosHonorarios(idcontribuyente) {
  fetch(base_url + "pagos/lista-pagos-honorarios/" + idcontribuyente)
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
    } else if (pago.estado == "Pendiente") {
      estado = `<span class="badge bg-light-warning f-12">${pago.estado}</span>`;
    } else {
      estado = `<span class="badge bg-light-danger f-12">${pago.estado}</span>`;
    }

    html += `
        <tr>
            <td>${pago.mesCorrespondiente}</td>
            <td>${pago.fecha_proceso}</td>
            <td>${pago.fecha_pago}</td>
            <td>${pago.monto_total}</td>
            <td>${pago.montoPagado}</td>
            <td>${pago.montoPendiente}</td>
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
      pagosHtml += `<li> ${item.mesCorrespondiente} (${item.monto})</li>`;
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
              <a href="#" class="ms-2" onclick="editPago(event, ${pago.id})" title="Editar Pago"> <i class="fas fa-edit text-info"></i> </a>
              `;

    html += `
        <tr>
            <td>${pago.registro}</td>
            <td>${pago.fecha_pago}</td>
            <td>${pagosHtml}</td>
            <td>${pago.metodo}</td>
            <td>${pago.monto}</td>
            <td> 
                <a href="#" data-lightbox="${base_url}vouchers/${pago.voucher}" onclick="verVaucher(event, ${pago.id})"> Ver vaucher </a>
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
      fetch(`${base_url}pagos/pagar-honorario`, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            metodoPago.value = "";
            voucher.value = "";

            Swal.fire({
              position: "top-end",
              icon: "success",
              title: data.message,
              showConfirmButton: false,
              timer: 1500,
            });

            if (countPagos.value == 0) {
              location.reload();
            } else {
              renderPagos(idContribuyente.value);
              renderPagosHonorarios(idContribuyente.value);

              getMontoPendiente();
            }

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
  } else {
    div_voucher.removeAttribute("hidden");
    voucher.setAttribute("required", true);
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
  btnDescargar.setAttribute("download", "voucher.jpg");

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
        fetch(`${base_url}pagos/delete-pago/${id}`)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              renderPagos(idContribuyente.value);
              renderPagosHonorarios(idContribuyente.value);
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

const generarMovimiento = document.getElementById("generarMovimiento");

generarMovimiento.addEventListener("change", (e) => {
  if (e.target.checked) {
    document.getElementById("proceso").removeAttribute("hidden");
    document.getElementById("fecha_proceso").setAttribute("required", true);
  } else {
    document.getElementById("proceso").setAttribute("hidden", true);
    document.getElementById("fecha_proceso").removeAttribute("required");
  }
});

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

  fetch(`${base_url}pagos/update-voucher`, {
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

        renderPagosHonorarios(idContribuyente.value);
        return false;
      }

      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!",
      });
    });
});

function editPago(e, id) {
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

  fetch(`${base_url}pagos/update-pago`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalPago").modal("hide");

        Swal.fire({
          position: "top-end",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        renderPagosHonorarios(idContribuyente.value);
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

  fetch(`${base_url}pagos/get-monto-pendiente/${idContribuyente.value}`)
    .then((res) => res.json())
    .then((data) => {
      monto.value = data.montoPagar;
    });
}

getMontoPendiente();
