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
                            <h6 class="mb-1"><a href="javascript:void(0);" class="num-doc" data-id="${
                              emp.id
                            }">${emp.ruc}</a></h6>
                            <p class="text-muted f-14 mb-0"> ${
                              emp.razon_social
                            } </p>
                        </div>
                    </div>
                </td>
                <td>${emp.diaCobro} cada mes</td>
                <td>${emp.tipoPago}</td>
                <td><a href="#" class="tipoServicio" data-id="${emp.id}">${
      emp.tipoServicio
    }</a></td>
                <td>${deuda}</td>
                <td>
                    <a href="${base_url}pago-honorario/${
      emp.id
    }" class="btn btn-success">COBRAR</a>
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

listaContribuyentesServidor();

function listaContribuyentesServidor() {
  fetch(base_url + "render-contribuyentes")
    .then((res) => res.json())
    .then((data) => {
      viewListContribuyentesServidor(data);
    });
}

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
                            <h6 class="mb-1"><a href="javascript:void(0);" class="num-doc" data-id="${
                              emp.id
                            }">${emp.ruc}</a></h6>
                            <p class="text-muted f-14 mb-0"> ${
                              emp.razon_social
                            } </p>
                        </div>
                    </div>
                </td>
                <td>${htmlSystem}</td>
                <td>NO TIENE REGISTROS</td>
                <td>
                    <a href="${base_url}cobrar-servidor/${
      emp.id
    }" class="btn btn-success">COBRAR</a>
                </td>
            </tr>
        `;
  });

  $("#tableDataServidor").DataTable().destroy();

  tableBodyServidor.innerHTML = html;

  const newcsServidor1 = $("#tableDataServidor").DataTable({
    language: language,
    responsive: true, // Hace que la tabla sea responsiva
    autoWidth: false, // Desactiva el ajuste automático de ancho
    scrollX: false, // Evita el scroll horizontal
    columnDefs: [
      { targets: "_all", className: "text-wrap" }, // Permite el ajuste de texto en las columnas
    ],
  });

  new $.fn.dataTable.Responsive(newcsServidor1);
}
