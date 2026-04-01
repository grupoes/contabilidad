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
    const date = new Date(service.created_at);
    const formattedDate =
      String(date.getDate()).padStart(2, "0") +
      "-" +
      String(date.getMonth() + 1).padStart(2, "0") +
      "-" +
      String(date.getFullYear()).slice(-2) +
      " " +
      String(date.getHours()).padStart(2, "0") +
      ":" +
      String(date.getMinutes()).padStart(2, "0") +
      ":" +
      String(date.getSeconds()).padStart(2, "0");

    let badgeEstado = "";
    if (service.estado === "pendiente") {
      badgeEstado = `<span class="badge bg-warning text-dark">${service.estado.toUpperCase()}</span>`;
    } else {
      badgeEstado = `<span class="badge bg-success">${service.estado.toUpperCase()}</span>`;
    }

    html += `
      <tr>
        <td>${formattedDate}</td>
        <td>${service.razon_social} <br> ${service.ruc}</td>
        <td>${service.descripcion}</td>
        <td>${service.monto}</td>
        <td>${service.url_pdf}</td>
        <td>${badgeEstado}</td>
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
