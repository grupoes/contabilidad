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

  data.forEach((pago) => {
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

  const currentDate = new Date().toISOString().split("T")[0];

  data.forEach((pago, index) => {
    let botonDelete = "";

    if (index === 0) {
      if (currentDate === pago.fecha) {
        botonDelete = `
              <a href="#" onclick="deletePago(event, ${pago.id})" title="Eliminar Pago"> <i class="fas fa-trash text-danger"></i> </a>
              <a href="#" class="ms-2" onclick="EditPago(event, ${pago.id})" title="Editar Pago"> <i class="fas fa-edit text-info"></i> </a>`;
      }
    }

    html += `
        <tr>
            <td>${pago.registro}</td>
            <td>${pago.fecha_pago}</td>
            <td>${pago.metodo}</td>
            <td>${pago.monto}</td>
            <td> 
                <a href="#" data-lightbox="${base_url}vouchers/${pago.voucher}" onclick="verVaucher(event)"> Ver vaucher </a>
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

function verVaucher(e) {
  e.preventDefault();

  var images_path = e.target;

  if (images_path.tagName == "IMG") {
    images_path = images_path.parentNode;
  }

  var recipient = images_path.getAttribute("data-lightbox");
  var image = document.querySelector(".modal-image");
  image.setAttribute("src", recipient);
  lightboxModal.show();
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
