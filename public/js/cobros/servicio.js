const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const tableBody = document.getElementById("tableBody");

function renderServices() {
  fetch(base_url + "services/all")
    .then((res) => res.json())
    .then((data) => {
      viewServices(data);
    });
}

function viewServices(data) {
  let html = "";

  data.forEach((service, item) => {
    html += `
      <tr>
        <td>${service.created_at}</td>
        <td>${service.razon_social} <br> ${service.ruc}</td>
        <td>${service.descripcion}</td>
        <td>${service.monto}</td>
        <td>${service.url_pdf}</td>
        <td>${service.estado}</td>
        <td>
          <a href="${base_url}servicio/${service.id}" class="btn btn-primary btn-sm">
            <i class="fas fa-eye"></i>
          </a>
        </td>
      </tr>
    `;
  });

  $($table).DataTable().destroy();

  tableBody.innerHTML = html;

  const newcss = $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcss);
}

renderServices();
