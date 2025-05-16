const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const formMov = document.getElementById("formMov");
const tableBody = document.getElementById("tableBody");

formMov.addEventListener("submit", (e) => {
  e.preventDefault();
  const data = new FormData(formMov);

  fetch(`${base_url}movimientos/consulta`, {
    method: "POST",
    body: data,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        viewMovi(data.data);
        return;
      }
      Swal.fire({
        title: "Error",
        text: data.message,
        icon: "error",
      });
    });
});

function viewMovi(data) {
  let html = "";
  data.forEach((mov) => {
    html += `
        <tr>
          <td>${mov.fecha}</td>
          <td>${mov.tipo_movimiento_descripcion}</td>
          <td>${mov.metodo}</td>
          <td>${mov.con_descripcion}</td>
          <td>${mov.mov_monto}</td>
          <td>${mov.mov_descripcion}</td>
          <td>${mov.nombre_sede}</td>
        </tr>
      `;
  });

  $($table).DataTable().destroy();

  tableBody.innerHTML = html;

  $($table).DataTable({
    language: language,
    responsive: true, // Hace que la tabla sea responsiva
    autoWidth: false, // Desactiva el ajuste automático de ancho
    scrollX: false, // Evita el scroll horizontal
    columnDefs: [
      { targets: "_all", className: "text-wrap" }, // Permite el ajuste de texto en las columnas
    ],
    dom: "Blfrtip",
    dom:
      "<'row mb-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-end'B>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row mt-2'<'col-sm-12 col-md-6'i><'col-sm-12 col-md-6 text-end'p>>",

    buttons: [
      {
        extend: "excelHtml5",
        text: '<i class="fas fa-file-excel"></i> Excel',
        titleAttr: "Exportar a Excel",
        className: "btn btn-success",
      },
    ],
  });

  new $.fn.dataTable.Responsive(newcs);
}
