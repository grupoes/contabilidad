const newcs = $($table).DataTable({
  language: language,
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
      viewMovimientos(data);
    });
});

function viewMovimientos(data) {
  let html = "";

  data.forEach((mov) => {
    const banks = mov.bancos;

    let htmlBanks = "";

    for (let i = 0; i < banks.length; i++) {
      htmlBanks += `
          <td>${banks[i]}</td>
        `;
    }

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
  });

  tableBody.innerHTML = html;
}
