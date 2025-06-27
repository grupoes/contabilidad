const newcs = $($table).DataTable(optionsTableDefault);

$("#tableList").DataTable();

const tableBody = document.querySelector("#tableBody");

function listarFacturasPeriodo() {
  fetch(`${base_url}/facturas/listar-periodo`)
    .then((res) => res.json())
    .then((data) => {
      let html = "";
      data.forEach((factura, index) => {
        html += `
        <tr>
          <td>${index + 1}</td>
          <td>${factura.descripcion}</td>
          <td>${factura.total_facturas}</td>
          <td>
            <button type="button" class="btn btn-info btn-sm" onclick="viewFacturas(${
              factura.id
            }, '${factura.descripcion}')">Ver</button>
          </td>
        </tr>
      `;
      });

      $($table).DataTable().destroy();

      tableBody.innerHTML = html;

      $($table).DataTable({
        language: language,
        columns: [
          { type: "string" },
          { type: "string" },
          { type: "string" },
          { type: "string" },
        ],
      });
    });
}

listarFacturasPeriodo();

function viewFacturas(id, periodo) {
  $("#modalLista").modal("show");

  const tituloLista = document.querySelector("#tituloLista");
  tituloLista.textContent = `Lista de facturas - ${periodo}`;

  fetch(`${base_url}/facturas/ver/${id}`)
    .then((res) => res.json())
    .then((data) => {
      const listaComprobantes = document.getElementById("listaComprobantes");

      let html = "";

      data.forEach((comp, index) => {
        html += `
        <tr>
          <td>${index + 1}</td>
          <td>${comp.serie_comprobante}-${comp.numero_comprobante}</td>
          <td>${comp.ruc} <br> ${comp.razon_social} </td>
          <td>${comp.tipoServicio}</td>
          <td>${comp.tipoPago}</td>
          <td>${comp.monto}</td>
          <td>
            <a href="${
              comp.url_absoluta_a4
            }" target="__blank" title="PDF" style="font-size: 24px; color: #e26f6f"><i class="ph-duotone ph-file-pdf"></i></a>
            <a href="${
              comp.url_absoluta_ticket
            }" target="__blank" title="TICKET" style="font-size: 24px; color:rgb(133, 188, 240)"><i class="ph-duotone ph-article"></i></a>
          </a>
        </tr>
      `;
      });

      $("#tableList").DataTable().destroy();
      listaComprobantes.innerHTML = html;
      $("#tableList").DataTable({
        language: language,
        responsive: true, // Hace que la tabla sea responsiva
        autoWidth: false, // Desactiva el ajuste automático de ancho
        scrollX: false, // Evita el scroll horizontal
        ordering: false,
        columnDefs: [
          { targets: "_all", className: "text-wrap" }, // Permite el ajuste de texto en las columnas
        ],
      });
    });
}
