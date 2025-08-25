const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

listaContribuyentes();

function listaContribuyentes() {
  fetch(base_url + "render-contribuyentes")
    .then((res) => res.json())
    .then((data) => {
        
      viewListContribuyentes(data);
    });
}

function viewListContribuyentes(data) {
  let html = "";

  data.forEach((emp, index) => {

    let sistemas = "";

    const systems = emp.sistemas;

    let htmlSystem = "<ul>";

    systems.forEach(element => {
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
                    <a href="${base_url}cobrar-servidor/${emp.id}" class="btn btn-success">COBRAR</a>
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