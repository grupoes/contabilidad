const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

allMensajes();

function allMensajes() {
  fetch(`${base_url}all-mensaje`)
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      viewMensajes(data);
    });
}

function viewMensajes(data) {
  let template = "";
  data.forEach((mensaje, index) => {
    template += `
            <tr>
                <td>${index + 1}</td>
                <td>${mensaje.titulo}</td>
                <td>
                  <p style="width: 250px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" data-bs-toggle="popover" title="${mensaje.contenido}">${mensaje.contenido}</p>
                </td>
                <td>${mensaje.fechaCreacion}</td>
                <td>${mensaje.typeContri}</td>
                <td>
                    <ul class="list-inline me-auto mb-0">
                        <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Ver Mensajes">
                            <a href="#" onclick="verMensajes(event, ${
                              mensaje.id
                            })" class="avtar avtar-xs btn-link-success btn-pc-default"><i class="ti ti-eye f-18"></i></a>
                        </li>
                    </ul>
                </td>
            </tr>
        `;
  });

  $($table).DataTable().destroy();

  document.querySelector("#tableBody").innerHTML = template;

  $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);
}

function verMensajes(e, id) {
  e.preventDefault();
  fetch(`${base_url}mensajes-all-id/${id}`)
    .then((response) => response.json())
    .then((data) => {
      $("#modalMensajes").modal("show");

      viewMensajesId(data);
    });
}

const tableBodyMessages = document.getElementById("tableBodyMessages");

function viewMensajesId(data) {
  let template = "";
  data.forEach((message, index) => {

    let estado = "";

    if(message.estado === 'pendiente') {
      estado = `<span class="badge bg-primary">${message.estado}</span>`;
    } else {
      estado = `<span class="badge bg-success">${message.estado}</span>`;
    }

    template += `
        <tr>
            <td>${index + 1}</td>
            <td>
              <p style="width: 250px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" data-bs-toggle="popover" title="${message.message}">${message.message}</p>
            </td>
            <td>${message.fecha_envio}</td>
            <td>${estado}</td>
        </tr>
        `;
  });

  tableBodyMessages.innerHTML = template;
}
