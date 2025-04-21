const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

allMensajes();

function allMensajes() {
  fetch(`${base_url}all-mensaje`)
    .then((response) => response.json())
    .then((data) => {
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
                  <p style="width: 250px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" data-bs-toggle="popover" title="${
                    mensaje.contenido
                  }">${mensaje.contenido}</p>
                </td>
                <td>${mensaje.fecha}</td>
                <td>${mensaje.typeContri}</td>
                <td>
                    <ul class="list-inline me-auto mb-0">
                        <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Ver Mensajes">
                            <a href="#" onclick="verMensajes(event, ${
                              mensaje.id
                            }, '${
      mensaje.titulo
    }')" class="avtar avtar-xs btn-link-success btn-pc-default"><i class="ti ti-eye f-18"></i></a>
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

const titleModalMessage = document.getElementById("titleModalMessage");

function verMensajes(e, id, titulo) {
  e.preventDefault();

  titleModalMessage.textContent = `Lista de Mensajes: ${titulo}`;

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

    if (message.estado === "pendiente") {
      estado = `<span class="badge bg-primary">${message.estado}</span>`;
    } else {
      estado = `<span class="badge bg-success">${message.estado}</span>`;
    }

    let mensaje = message.message;

    template += `
        <tr>
            <td>${index + 1}</td>
            <td>${message.numero_whatsapp}</td>
            <td>
              <p style="width: 200px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" 
                 data-bs-toggle="tooltip" data-bs-html="true" title="${mensaje}">
                 ${mensaje}
              </p>
            </td>
            <td>${message.fecha_envio}</td>
            <td>${message.razon_social}</td>
            <td>${estado}</td>
        </tr>
        `;
  });

  $("#messages").DataTable().destroy();

  tableBodyMessages.innerHTML = template;

  $("#messages").DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);
}
