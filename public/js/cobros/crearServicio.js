const formService = document.getElementById("formService");

const estado = document.getElementById("estado");
const metodo_pago = document.getElementById("metodo_pago");
const comprobante = document.getElementById("comprobante");

formService.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formService);

  fetch(`${base_url}save-service`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        Swal.fire({
          position: "top-center",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        setTimeout(() => {
          window.location.href = `${base_url}cobros?tab=servicios`;
        }, 1600);
      } else {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: data.message,
        });
      }
    });
});

const searchDocumento = document.getElementById("searchDocumento");
const numeroDocumento = document.getElementById("numeroDocumento");
const razon_social = document.getElementById("razon_social");

searchDocumento.addEventListener("click", () => {
  const ruc = numeroDocumento.value;

  if (ruc.length == 11) {
    fetch(base_url + "api/dni-ruc/ruc/" + ruc)
      .then((res) => res.json())
      .then((data) => {
        if (data.respuesta === "ok") {
          razon_social.value = data.data.razon_social;
        } else {
          alert(data.data_resp.mensaje);
        }
      });
  } else {
    alert("Agregue un R.U.C. de 11 dígitos");
  }
});

estado.addEventListener("change", () => {
  if (estado.value == "pendiente") {
    metodo_pago.removeAttribute("required");
    metodo_pago.setAttribute("disabled", "true");
    comprobante.removeAttribute("required");
    comprobante.setAttribute("disabled", "true");

    viewSelectEstado(estado.value);
  } else {
    metodo_pago.setAttribute("required", "true");
    metodo_pago.removeAttribute("disabled");
    comprobante.setAttribute("required", "true");
    comprobante.removeAttribute("disabled");

    viewSelectEstado(estado.value);
  }
});

const linkServicioTab = document.getElementById("linkServicioTab");

linkServicioTab.addEventListener("click", (e) => {
  window.location.href = `${base_url}cobros?tab=servicios`;
});

const changeEstado = document.getElementById("viewSelectEstado");

function viewSelectEstado(estadoValue) {
  if (estadoValue == "pendiente") {
    viewSelectPendiente();
  }

  if (estadoValue == "pagado") {
    viewSelectPagado();
  }
}

function viewSelectPendiente() {
  const montoServicio = document.getElementById("monto").value;

  let html = `
  <div class="col-md-3">
      <button type="button" class="btn btn-light-primary d-flex align-items-center gap-2" onclick="addProgramacion()">
          <i class="ti ti-plus"></i> Agregar Programación
      </button>
  </div>

  <div class="col-md-9">
      <div class="table-responsive">
          <table class="table table-hover mb-0">
              <thead>
                  <tr>
                      <th>#</th>
                      <th>Fecha</th>
                      <th>Monto</th>
                      <th class="text-center">Acción</th>
                  </tr>
              </thead>
              <tbody id="tableProgramacionBody">
                  <tr>
                      <td>1</td>
                      <td>
                          <input type="date" name="fecha_programacion[]" class="form-control" required>
                      </td>
                      <td>
                          <input type="number" name="montos[]" class="form-control" placeholder="Monto" value="${montoServicio}" onkeyup="sumarMontos()" required>
                      </td>
                      <td class="text-center">
                          <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" onclick="deleteRow(event)"><i class="ti ti-trash f-20"></i></a>
                      </td>
                  </tr>
              </tbody>
              <tfoot>
                  <tr>
                      <th colspan="2" class="text-end" id="totalServicio">Total:</th>
                      <th id="montoTotalServicio">${montoServicio}</th>
                      <th></th>
                  </tr>
              </tfoot>
          </table>
      </div>
  </div>
  `;

  changeEstado.innerHTML = html;
}

function addProgramacion() {
  const tableProgramacionBody = document.getElementById(
    "tableProgramacionBody"
  );
  const rowCount = tableProgramacionBody.rows.length;

  let html = `
  <tr>
      <td>${rowCount + 1}</td>
      <td>
          <input type="date" name="fecha_programacion[]" class="form-control" required>
      </td>
      <td>
          <input type="number" name="montos[]" class="form-control" placeholder="Monto" value="0.00" onkeyup="sumarMontos()" required>
      </td>
      <td class="text-center">
          <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" onclick="deleteRow(event)"><i class="ti ti-trash f-20"></i></a>
      </td>
  </tr>
  `;

  tableProgramacionBody.insertAdjacentHTML("beforeend", html);
}

function deleteRow(el) {
  el.preventDefault();
  const fila = el.target.closest("tr");
  fila.remove();
}

function viewSelectPagado() {
  const montoServicio = document.getElementById("monto").value;

  let html = `
  <div class="col-md-3">
      <button type="button" class="btn btn-light-primary d-flex align-items-center gap-2" onclick="addPagos()">
          <i class="ti ti-plus"></i> Agregar Método de Pago
      </button>
  </div>

  <div class="col-md-9">
      <div class="table-responsive">
          <table class="table table-hover mb-0">
              <thead>
                  <tr>
                      <th>#</th>
                      <th>Método de Pago</th>
                      <th>Monto</th>
                      <th class="text-center">Acción</th>
                  </tr>
              </thead>
              <tbody id="tableProgramacionBody">
                  <tr>
                      <td>1</td>
                      <td>
                          <input type="hidden" name="metodo_pago[]" value="1">
                          EFECTIVO
                      </td>
                      <td>
                          <input type="number" name="montos[]" class="form-control" placeholder="Monto" value="${montoServicio}" onkeyup="sumarMontos()" required>
                      </td>
                      <td class="text-center">
                          <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" onclick="deleteRow(event)"><i class="ti ti-trash f-20"></i></a>
                      </td>
                  </tr>
              </tbody>
              <tfoot>
                  <tr>
                      <th colspan="2" class="text-end" id="totalServicio">Total:</th>
                      <th id="montoTotalServicio">${montoServicio}</th>
                      <th></th>
                  </tr>
              </tfoot>
          </table>
      </div>
  </div>
  `;

  changeEstado.innerHTML = html;
}

function addPagos() {
  const textSelectMetodo = metodo_pago.options[metodo_pago.selectedIndex].text;

  if (metodo_pago.value == "") {
    alert("Seleccione un método de pago");
    return;
  }

  const tableProgramacionBody = document.getElementById(
    "tableProgramacionBody"
  );
  const rowCount = tableProgramacionBody.rows.length;

  let html = `
  <tr>
      <td>${rowCount + 1}</td>
      <td>
        <input type="hidden" name="metodo_pago[]" value="${metodo_pago.value}">
        ${textSelectMetodo}
      </td>
      <td>
          <input type="number" name="montos[]" class="form-control" placeholder="Monto" value="0.00" onkeyup="sumarMontos()" required>
      </td>
      <td class="text-center">
          <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" onclick="deleteRow(event)"><i class="ti ti-trash f-20"></i></a>
      </td>
  </tr>
  `;

  tableProgramacionBody.insertAdjacentHTML("beforeend", html);
}

function sumarMontos() {
  let total = 0;

  document.querySelectorAll('input[name="montos[]"]').forEach((input) => {
    total += Number(input.value) || 0;
  });

  console.log(document.querySelectorAll('input[name="montos[]"]'));

  document.getElementById("montoTotalServicio").textContent = total.toFixed(2);
}
