const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

//validarCaja();

flatpickr(document.querySelector("#rango-fecha-movimientos"), {
  mode: "range",
  dateFormat: "d-m-Y",
  defaultDate: getDefaultDate(),
  allowInput: true,
  onClose: function (selectedDates, dateStr, instance) {
    const selectedDate = instance.selectedDates[0];
    console.log("Fecha seleccionada:", selectedDate);
    renderMovimientos();
  },
});

const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  showConfirmButton: true,
  buttonsStyling: false,
});

function getDefaultDate() {
  const today = new Date();
  const startDate = new Date(
    today.getFullYear(),
    today.getMonth(),
    today.getDate() - 30
  );
  const endDate = today;
  return [startDate, endDate];
}

const btnNuevoIngreso = document.getElementById("btnNuevoIngreso");
const btnNuevoEgreso = document.getElementById("btnNuevoEgreso");
const titleModalMovimiento = document.getElementById("titleModalMovimiento");
const conceptoCaja = document.getElementById("conceptoCaja");
const tipo_movimiento = document.getElementById("tipo_movimiento");

const formMovimiento = document.getElementById("formMovimiento");

const ocultarMov = document.querySelectorAll(".ocultarMov");

const rangoFechaMovimientos = document.getElementById(
  "rango-fecha-movimientos"
);
const tableBody = document.getElementById("tableBody");

const verMovimientosVirtual = document.getElementById("verMovimientosVirtual");
const tableBodyVirtual = document.getElementById("tableBodyVirtual");

const comprobante = document.getElementById("comprobante");
const serieMov = document.getElementById("serieMov");
const numero = document.getElementById("correlativo");
const vaucher = document.getElementById("vaucher");
const fileVaucher = document.getElementById("fileVaucher");
const metodoPago = document.getElementById("metodoPago");

metodoPago.addEventListener("change", (e) => {
  const metodo = e.target.value;

  if (tipo_movimiento.value == "1") {
    if (metodo == "1" || metodo == "") {
      vaucher.removeAttribute("required");
      fileVaucher.setAttribute("hidden", true);
    } else {
      fileVaucher.removeAttribute("hidden");
      vaucher.setAttribute("required", "true");
    }
  }
});

btnNuevoIngreso.addEventListener("click", (e) => {
  $("#modalTipoMovimiento").modal("show");
  titleModalMovimiento.textContent = "AGREGAR UN INGRESO";
  tipo_movimiento.value = 1;

  formMovimiento.reset();

  conceptosTipoMovimiento(1);

  ocultarMov.forEach((ocultar) => {
    ocultar.classList.remove("d-none");
  });

  comprobante.setAttribute("required", "true");
  serieMov.setAttribute("required", "true");
  numero.setAttribute("required", "true");
});

btnNuevoEgreso.addEventListener("click", (e) => {
  $("#modalTipoMovimiento").modal("show");
  titleModalMovimiento.textContent = "AGREGAR UN EGRESO";
  tipo_movimiento.value = 2;

  formMovimiento.reset();

  conceptosTipoMovimiento(2);

  ocultarMov.forEach((ocultar) => {
    ocultar.classList.add("d-none");
  });

  comprobante.removeAttribute("required");
  serieMov.removeAttribute("required");
  numero.removeAttribute("required");
  fileVaucher.setAttribute("hidden", "true");
});

function conceptosTipoMovimiento(tipo) {
  fetch(base_url + "conceptos-tipo-movimiento/" + tipo)
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      html += `<option value="">Seleccione...</option>`;

      data.forEach((concep) => {
        html += `<option value="${concep.con_id}">${concep.con_descripcion}</option>`;
      });

      conceptoCaja.innerHTML = html;
    });
}

formMovimiento.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formMovimiento);

  fetch(base_url + "movimiento/guardar", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalTipoMovimiento").modal("hide");
        renderMovimientos();

        swalWithBootstrapButtons.fire("Muy bien!", data.message, "success");
      }
    });
});

renderMovimientos();

function renderMovimientos() {
  fetch(base_url + "movimientos/lista-cajero/" + rangoFechaMovimientos.value)
    .then((res) => res.json())
    .then((data) => {
      tableMovimientos(data);
    });
}

function tableMovimientos(data) {
  let html = "";

  const currentDate = new Date()
    .toLocaleString("en-CA", {
      timeZone: "America/Lima",
    })
    .split(",")[0];

  const total = data.length;

  data.forEach((mov, i) => {
    let botonExtornar = "";

    if (currentDate === mov.mov_fecha) {
      botonExtornar = `
          <ul class="list-inline me-auto mb-0">
            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="CAMBIAR METODO DE PAGO">
              <a href="#" onclick="changePago(event,${mov.mov_id}, ${mov.id_metodo_pago})" class="avtar avtar-xs btn-link-success btn-pc-default"><i class="ti ti-edit-circle f-18"></i></a>
            </li>
            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="EXTORNAR">
              <a href="#" onclick="extornar(event,${mov.mov_id})" class="avtar avtar-xs btn-link-danger btn-pc-default"><i class="ti ti-trash f-18"></i></a>
            </li>
          </ul>
          `;
    }

    html += `
        <tr>
            <td>${total - i}</td>
            <td>${mov.caja_descripcion}</td>
            <td>${mov.metodo}</td>
            <td>${mov.tipo_movimiento_descripcion}</td>
            <td>${mov.con_descripcion}</td>
            <td>${mov.mov_monto}</td>
            <td>${mov.mov_descripcion}</td>
            <td>${mov.fecha}</td>
            <td>${botonExtornar}</td>
        </tr>
        `;
  });

  $($table).DataTable().destroy();

  tableBody.innerHTML = html;

  const newcs = $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);
}

rangoFechaMovimientos.addEventListener("change", (e) => {
  renderMovimientos();
});

function extornar(e, id) {
  e.preventDefault();
  swalWithBootstrapButtons
    .fire({
      title: "¿Está seguro?",
      text: "¡No podrás revertir esto!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, extornar!",
      cancelButtonText: "No, cancelar!",
      reverseButtons: true,
    })
    .then((result) => {
      if (result.isConfirmed) {
        fetch(base_url + "movimiento/extornar/" + id)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              swalWithBootstrapButtons.fire(
                "Extornado!",
                data.message,
                "success"
              );
              renderMovimientos();
            }
          });
      }
    });
}

function changePago(e, idmov, idMetodoPago) {
  e.preventDefault();

  $("#modalChangePago").modal("show");

  const idmovi = document.getElementById("idmov");
  idmovi.value = idmov;

  fetch(base_url + "movimientos/metodos-pagos")
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      data.forEach((metodo) => {
        let seleted = "";

        if (metodo.id == idMetodoPago) {
          seleted = `selected="true"`;
        }

        html += `<option value="${metodo.id}" ${seleted}>${metodo.metodo}</option>`;
      });

      const nuevo_metodo_pago = document.getElementById("nuevo_metodo_pago");
      nuevo_metodo_pago.innerHTML = html;
    });
}

const formCambioPago = document.getElementById("formCambioPago");

formCambioPago.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formCambioPago);

  fetch(base_url + "movimiento/cambio-pago", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalChangePago").modal("hide");
        renderMovimientos();

        swalWithBootstrapButtons.fire("Muy bien!", data.message, "success");

        return false;
      }

      swalWithBootstrapButtons.fire("Error!", data.message, "danger");
    });
});
