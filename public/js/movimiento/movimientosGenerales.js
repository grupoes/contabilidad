$($table).DataTable({
  language: language,
  paging: false,
  info: false,
  ordering: false,
});

const formConsulta = document.getElementById("formConsulta");
const tableBody = document.getElementById("tableBody");

formConsulta.addEventListener("submit", (e) => {
  e.preventDefault();
  const data = new FormData(formConsulta);
  fetch(base_url + "movimientos/getMovimientosGenerales", {
    method: "POST",
    body: data,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status == "error") {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.message,
        });

        return;
      }

      viewMovimientos(data);
    });
});

function viewMovimientos(data) {
  let html = "";

  const registros = data.length;

  data.forEach((mov, indice) => {
    const banks = mov.bancos;

    let htmlBanks = "";

    for (let i = 0; i < banks.length; i++) {
      if (registros - 1 == indice) {
        htmlBanks += `
            <th>${banks[i]}</th>
          `;
      } else {
        htmlBanks += `
          <td>${banks[i]}</td>
        `;
      }
    }

    if (registros - 1 == indice) {
      html += `
        <tr>
            <th>${mov.fecha_proceso}</th>
            <th>${mov.fecha_pago}</th>
            <th>${mov.tipo}</th>
            <th>${mov.concepto}</th>
            <th>${mov.descripcion}</th>
            <th>${mov.metodo}</th>
            <th>${mov.efectivo}</th>
            ${htmlBanks}
        </tr>
        `;
    } else {
      html += `
        <tr>
            <td>${mov.fecha_proceso}</td>
            <td>${mov.fecha_pago}</td>
            <td>${mov.tipo}</td>
            <td>${mov.concepto}</td>
            <td>${mov.descripcion}</td>
            <td>${mov.metodo}</td>
            <td>${mov.efectivo}</td>
            ${htmlBanks}
        </tr>
        `;
    }
  });

  $($table).DataTable().destroy();

  tableBody.innerHTML = html;

  $($table).DataTable({
    language: language,
    paging: false,
    info: false,
    ordering: false,
    dom: "Bfrtip",
    buttons: [
      {
        extend: "excelHtml5",
        text: '<i class="fas fa-file-excel"></i> Excel',
        titleAttr: "Exportar a Excel",
        className: "btn btn-success",
        customize: function (xlsx) {
          var sheet = xlsx.xl.worksheets["sheet1.xml"];

          // 1. Definir anchos de columna (ejemplo para 3 columnas)
          const colWidths = [15, 15, 17, 20, 20]; // Ajusta los valores según necesites
          $("col", sheet).each(function (index) {
            $(this).attr("width", colWidths[index] || 15); // Default: 15
          });

          // 2. Crear un estilo personalizado (alineación izquierda + sin wrap)
          var styleElement =
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">' +
            '<alignment horizontal="left" vertical="center" wrapText="0"/>' +
            "</xf>";

          // Agregar el estilo al XML
          $("cellXfs", xlsx.xl["styles.xml"]).append(styleElement);
          var newStyleIndex = $("cellXfs xf", xlsx.xl["styles.xml"]).length - 1;

          // 3. Aplicar el estilo a todas las celdas
          $("row", sheet).each(function (rowIndex) {
            if (rowIndex > 0) {
              // Saltar la primera fila (índice 0)
              $("c", this).attr("s", newStyleIndex);
            }
          });
        },
      },
    ],
    columnDefs: [
      {
        targets: 3, // Índice de la columna "CONCEPTO" (empieza en 0)
        width: "200px", // Ancho fijo
        className: "columna-concepto", // Clase adicional
      },
      {
        targets: 4,
        width: "200px", // Ancho fijo
        className: "columna-description",
      },
      {
        targets: 5,
        width: "200px", // Ancho fijo
        className: "columna-metodo",
      },
    ],
    //autoWidth: false, // ¡IMPORTANTE!
  });
}
