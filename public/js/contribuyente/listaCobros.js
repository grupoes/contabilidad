document.addEventListener("DOMContentLoaded", function () {
  // Obtener parámetros de la URL
  const urlParams = new URLSearchParams(window.location.search);
  const tabParam = urlParams.get("tab");

  // Si hay un parámetro 'tab' en la URL
  if (tabParam) {
    let targetTab;

    // Determinar qué pestaña activar según el parámetro
    switch (tabParam) {
      case "honorarios-mensuales":
        targetTab = "analytics-tab-1";
        break;
      case "honorarios-anuales":
        targetTab = "analytics-tab-2";
        break;
      case "servidor":
        targetTab = "analytics-tab-3";
        break;
    }

    // Activar la pestaña correspondiente
    if (targetTab) {
      const tabElement = document.getElementById(targetTab);
      if (tabElement) {
        const tab = new bootstrap.Tab(tabElement);
        tab.show();
      }
    }
  }
});

const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const tableBody = document.querySelector("#tableBody");
const selectOpciones = document.getElementById("selectOpciones");
const estados = document.getElementById("estados");

listaContribuyentes();

function listaContribuyentes() {
  fetch(base_url + "listaCobros/" + selectOpciones.value + "/" + estados.value)
    .then((res) => res.json())
    .then((data) => {
      viewListContribuyentes(data);
    });
}

function viewListContribuyentes(data) {
  let html = "";

  data.forEach((emp, index) => {
    let monto;

    if (emp.tipoSuscripcion === "SI GRATUITO") {
      monto = `GRATUITO`;
    } else {
      if (emp.tipoServicio === "ALQUILER") {
        monto = `<p class="f-14 mb-0">M: ${emp.costoMensual}</p>`;
      } else {
        monto = `<p class="f-14 mb-0">M: ${emp.costoMensual}</p>
        <p class="f-14 mb-0">A: ${emp.costoAnual}</p>`;
      }
    }

    let deuda = "";

    if (emp.amortizo == 1) {
      deuda = `<span class="badge bg-danger" title="">${emp.debe}</span>`;
    } else {
      deuda = `${emp.debe}`;
    }

    html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="row">
                        <div class="col">
                            <h6 class="mb-1"><a href="javascript:void(0);" class="num-doc" data-id="${emp.id
      }">${emp.ruc}</a></h6>
                            <p class="text-muted f-14 mb-0"> ${emp.razon_social
      } </p>
                        </div>
                    </div>
                </td>
                <td>${emp.diaCobro} cada mes</td>
                <td>${emp.tipoPago}</td>
                <td><a href="#" class="tipoServicio" data-id="${emp.id}">${emp.tipoServicio
      }</a></td>
                <td>${deuda}</td>
                <td>
                    ${emp.cobrar}
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

selectOpciones.addEventListener("change", (e) => {
  listaContribuyentes();
});

estados.addEventListener("change", (e) => {
  listaContribuyentes();
});

//Servidor
const tableBodyServidor = document.querySelector("#tableBodyServidor");

const newcsServidor = $("#tableDataServidor").DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcsServidor);

const tipoServicio = document.getElementById("selectOpcionesServidor");
const estado = document.getElementById('estadosServidor');

listaContribuyentesServidor();

function listaContribuyentesServidor() {

  fetch(base_url + "render-contribuyentes" + "/" + tipoServicio.value + "/" + estado.value)
    .then((res) => res.json())
    .then((data) => {
      viewListContribuyentesServidor(data);
    });
}

tipoServicio.addEventListener("change", (e) => {
  listaContribuyentesServidor();
});

estado.addEventListener("change", (e) => {
  listaContribuyentesServidor();
});

function viewListContribuyentesServidor(data) {
  let html = "";

  data.forEach((emp, index) => {
    let sistemas = "";

    const systems = emp.sistemas;

    let htmlSystem = "<ul>";

    systems.forEach((element) => {
      htmlSystem += `<li>${element.nameSystem}</li>`;
    });

    htmlSystem += `</ul>`;

    html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="row">
                        <div class="col">
                            <h6 class="mb-1"><a href="javascript:void(0);" class="num-doc" data-id="${emp.id
      }">${emp.ruc}</a></h6>
                            <p class="text-muted f-14 mb-0"> ${emp.razon_social
      } </p>
                        </div>
                    </div>
                </td>
                <td>${htmlSystem}</td>
                <td>${emp.monto}</td>
                <td>${emp.fecha_inicio}</td>
                <td>${emp.fecha_fin}</td>
                <td>${emp.pagos}</td>
                <td>
                    ${emp.cobrar}
                </td>
            </tr>
        `;
  });

  $("#tableDataServidor").DataTable().destroy();

  tableBodyServidor.innerHTML = html;

  const newcsServidor = $("#tableDataServidor").DataTable({
    language: language,
    responsive: true, // Hace que la tabla sea responsiva
    autoWidth: false, // Desactiva el ajuste automático de ancho
    scrollX: false, // Evita el scroll horizontal
    columnDefs: [
      { targets: "_all", className: "text-wrap" }, // Permite el ajuste de texto en las columnas
    ],
  });

  new $.fn.dataTable.Responsive(newcsServidor);
}

//para los pagos anuales
const selectOpcionesAnual = document.getElementById("selectOpcionesAnual");
const estado_anual = document.getElementById("estado_anual");
const tableBodyAnual = document.querySelector("#tableBodyAnual");

const tanual = $("#tableDataAnual").DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(tanual);

estado_anual.addEventListener("change", (e) => {
  loadDeudasAnuales();
});

selectOpcionesAnual.addEventListener("change", (e) => {
  loadDeudasAnuales();
});

loadDeudasAnuales();

function loadDeudasAnuales() {
  fetch(
    base_url +
    "deudas-anuales/" +
    selectOpcionesAnual.value +
    "/" +
    estado_anual.value
  )
    .then((res) => res.json())
    .then((data) => {
      viewDeudasAnuales(data);
    });
}

function viewDeudasAnuales(data) {
  let html = "";

  data.forEach((emp, index) => {
    html += `
    <tr>
      <td>${index + 1}</td>
      <td>
        <div class="row">
            <div class="col">
                <h6 class="mb-1"><a href="javascript:void(0);" class="num-doc" data-id="${emp.id
      }">${emp.ruc}</a></h6>
                <p class="text-muted f-14 mb-0"> ${emp.razon_social} </p>
            </div>
        </div>
      </td>
      <td>${emp.pagos_pendientes}</td>
      <td>
        ${emp.cobrar}
      </td>
    </tr>
    `;
  });

  $("#tableDataAnual").DataTable().destroy();

  tableBodyAnual.innerHTML = html;

  const tanual1 = $("#tableDataAnual").DataTable({
    language: language,
    responsive: true, // Hace que la tabla sea responsiva
    autoWidth: false, // Desactiva el ajuste automático de ancho
    scrollX: false, // Evita el scroll horizontal
    processing: true,
    columnDefs: [
      { targets: "_all", className: "text-wrap" }, // Permite el ajuste de texto en las columnas
    ],
  });

  new $.fn.dataTable.Responsive(tanual1);
}
