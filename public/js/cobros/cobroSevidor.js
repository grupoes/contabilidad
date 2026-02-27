listaContribuyentes();

const selectOpciones = document.getElementById('selectOpciones');
const estados = document.getElementById('estados');

if (selectOpciones) selectOpciones.addEventListener('change', listaContribuyentes);
if (estados) estados.addEventListener('change', listaContribuyentes);

function listaContribuyentes() {
  const servicio = selectOpciones ? selectOpciones.value : 'TODOS';
  const estado = estados ? estados.value : '1';

  fetch(base_url + "render-contribuyentes/" + servicio + "/" + estado)
    .then((res) => res.json())
    .then((data) => {
      viewListContribuyentes(data);
    });
}

function viewListContribuyentes(data) {
  let html = "";

  data.forEach((emp, index) => {
    let htmlSystem = "<ul>";
    emp.sistemas.forEach(element => {
      htmlSystem += `<li>${element.nameSystem}</li>`;
    });
    htmlSystem += `</ul>`;

    html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="row">
                        <div class="col">
                            <h6 class="mb-1"><a href="javascript:void(0);" class="num-doc" data-id="${emp.id}">${emp.ruc}</a></h6>
                            <p class="text-muted f-14 mb-0"> ${emp.razon_social} </p>
                        </div>
                    </div>
                </td>
                <td>${htmlSystem}</td>
                <td>${emp.pagos}</td>
                <td class="text-center">
                    ${emp.cobrar}
                </td>
            </tr>
        `;
  });

  if ($.fn.DataTable.isDataTable('#tableData')) {
    $('#tableData').DataTable().destroy();
  }

  tableBody.innerHTML = html;

  const newcs = $('#tableData').DataTable({
    language: language,
    responsive: true,
    autoWidth: false,
    columnDefs: [
      { targets: "_all", className: "text-wrap" },
    ],
  });

  new $.fn.dataTable.Responsive(newcs);
}