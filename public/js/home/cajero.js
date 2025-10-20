document.addEventListener("DOMContentLoaded", function () {
  loadPdtsSubir();

  const newcs = $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);

  const newPlame = $("#tablePlame").DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newPlame);

  const newServidor = $("#tableServidor").DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newServidor);
});

const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  showConfirmButton: true,
  buttonsStyling: false,
});

async function loadPdtsSubir() {
  const listCards = document.getElementById("listCards");

  try {
    const responseRenta = await fetch(base_url + "api/notificacion-pdt-renta");
    const dataRenta = await responseRenta.json();

    const quantyRenta = dataRenta.length;

    if (quantyRenta > 0) {
      const html = `
        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card alerta-card" onclick="viewContribuyentesPdts()">
                <div class="card-body">
                    <h3 class="text-black m-0">${quantyRenta}</h3>
                    <span class="m-t-10 text-black">PDT RENTA</span>
                    <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                </div>
            </div>
        </div>
      `;

      const temp = document.createElement("div");
      temp.innerHTML = html.trim();
      const nuevoNodo = temp.firstElementChild;
      listCards.insertBefore(nuevoNodo, listCards.firstElementChild);
    }

    const responsePlame = await fetch(base_url + "api/notificacion-pdt-plame");
    const dataPlame = await responsePlame.json();

    const quantyPlame = dataPlame.length;

    if (quantyPlame > 0) {
      const htmlPlame = `
        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card alerta-card" onclick="viewContribuyentesPdtsPlame()">
                <div class="card-body">
                    <h3 class="text-black m-0">${quantyPlame}</h3>
                    <span class="m-t-10 text-black">PDT PLAME</span>
                    <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                </div>
            </div>
        </div>
      `;

      const tempPlame = document.createElement("div");
      tempPlame.innerHTML = htmlPlame.trim();
      const nuevoNodoPlame = tempPlame.firstElementChild;
      listCards.insertBefore(nuevoNodoPlame, listCards.firstElementChild);
    }

    const deudores = await fetch(base_url + "deudores-servidor");
    const dataDeudores = await deudores.json();

    const quantyDeudor = dataDeudores.length;

    if (quantyDeudor > 0) {
      const htmlDeudores = `
        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card alerta-card" onclick="viewContribuyentesServidores()">
                <div class="card-body">
                    <h3 class="text-black m-0">${quantyDeudor}</h3>
                    <span class="m-t-10 text-black">SERVIDOR</span>
                    <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                </div>
            </div>
        </div>
      `;

      const tempDeudores = document.createElement("div");
      tempDeudores.innerHTML = htmlDeudores.trim();
      const nuevoNodoPlame = tempDeudores.firstElementChild;
      listCards.insertBefore(nuevoNodoPlame, listCards.firstElementChild);
    }
  } catch (error) {
    console.error("Error al cargar notificaciones PDT:", error);
  }
}

function viewContribuyentesPdts() {
  $("#modalPdts").modal("show");
  fetch(base_url + "api/notificacion-pdt-renta")
    .then((res) => res.json())
    .then((data) => {
      const listPdts = document.getElementById("listPdts");

      let html = "";

      data.forEach((pdt) => {
        let button = "";

        if (pdt.registro == 0) {
          button = `
            <button type="button" class="btn btn-info btn-sm" onclick="excluirPeriodo('${pdt.ruc}', ${pdt.id_mes}, ${pdt.id_anio})">
              <i class="fas fa-minus"></i>
            </button>
          `;
        }

        html += `
        <tr>
          <td>${pdt.razon_social}</td>
          <td>${pdt.mes} ${pdt.anio}</td>
          <td>
            ${button}
          </td>
        </tr>
        `;
      });

      $($table).DataTable().destroy();

      listPdts.innerHTML = html;

      const newcs = $($table).DataTable(optionsTableDefault);

      new $.fn.dataTable.Responsive(newcs);
    });
}

function excluirPeriodo(ruc, id_mes, id_anio) {
  $("#modalPdts").modal("hide");

  swalWithBootstrapButtons
    .fire({
      title: "¿Estás seguro de excluir este periodo?",
      text: "¡No podrá revertir después!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Si, excluir!",
      cancelButtonText: "No, cancelar!",
      reverseButtons: true,
      allowOutsideClick: false,
    })
    .then((result) => {
      if (result.isConfirmed) {
        const params = {
          ruc: ruc,
          id_mes: id_mes,
          id_anio: id_anio,
        };

        fetch(base_url + "api/excluir-periodo-pdt-renta", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(params),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              swalWithBootstrapButtons
                .fire({
                  title: "¡Eliminado!",
                  text: data.message,
                  icon: "success",
                  confirmButtonText: "Entendido",
                  allowOutsideClick: false,
                })
                .then((result) => {
                  if (result.isConfirmed) {
                    viewContribuyentesPdts();
                  }
                });
            } else {
              swalWithBootstrapButtons.fire("Error", data.msg, "error");
            }
          });
      } else {
        $("#modalPdts").modal("show");
      }
    });
}

function viewContribuyentesPdtsPlame() {
  $("#modalPdtsPlame").modal("show");
  fetch(base_url + "api/notificacion-pdt-plame")
    .then((res) => res.json())
    .then((data) => {
      const listPdts = document.getElementById("listPdtsPlame");

      let html = "";

      data.forEach((pdt) => {
        let button = "";

        if (pdt.tipo_contrato == "actual") {
          button = `
            <button type="button" class="btn btn-info btn-sm" onclick="excluirPeriodoPlame('${pdt.ruc}', ${pdt.id_mes}, ${pdt.id_anio})">
              <i class="fas fa-minus"></i>
            </button>
          `;
        }

        html += `
        <tr>
          <td>${pdt.razon_social}</td>
          <td>${pdt.mes} ${pdt.anio}</td>
          <td>
            ${button}
          </td>
        </tr>
        `;
      });

      $("#tablePlame").DataTable().destroy();

      listPdts.innerHTML = html;

      const newPlame = $("#tablePlame").DataTable(optionsTableDefault);

      new $.fn.dataTable.Responsive(newPlame);
    });
}

function excluirPeriodoPlame(ruc, id_mes, id_anio) {
  $("#modalPdtsPlame").modal("hide");

  swalWithBootstrapButtons
    .fire({
      title: "¿Estás seguro de excluir este periodo?",
      text: "¡No podrá revertir después!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Si, excluir!",
      cancelButtonText: "No, cancelar!",
      reverseButtons: true,
      allowOutsideClick: false,
    })
    .then((result) => {
      if (result.isConfirmed) {
        const params = {
          ruc: ruc,
          id_mes: id_mes,
          id_anio: id_anio,
        };

        fetch(base_url + "api/excluir-periodo-pdt-plame", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(params),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              swalWithBootstrapButtons
                .fire({
                  title: "¡Eliminado!",
                  text: data.message,
                  icon: "success",
                  confirmButtonText: "Entendido",
                  allowOutsideClick: false,
                })
                .then((result) => {
                  if (result.isConfirmed) {
                    viewContribuyentesPdtsPlame();
                  }
                });
            } else {
              swalWithBootstrapButtons.fire("Error", data.msg, "error");
            }
          });
      } else {
        $("#modalPdtsPlame").modal("show");
      }
    });
}

function viewContribuyentesServidores() {
  $("#modalPdtsServidores").modal("show");
  fetch(base_url + "deudores-servidor")
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      const listServidores = document.getElementById("listServidores");

      data.forEach((server) => {
        html += `
        <tr>
          <td>${server.ruc} <br> ${server.razon_social}</td>
          <td>${server.fechas_vencidas}</td>
          <td>${server.total_deuda}</td>
        </tr>
        `;
      });

      $("#tableServidor").DataTable().destroy();

      listServidores.innerHTML = html;

      const newcs = $("#tableServidor").DataTable(optionsTableDefault);

      new $.fn.dataTable.Responsive(newcs);
    });
}

const morosos_mensual = document.getElementById("morosos_mensual");

morososMensual();

function morososMensual() {
  const tipo = "TODOS";
  const estado = 1;
  fetch(base_url + `listaCobros/${tipo}/${estado}`)
    .then((res) => res.json())
    .then((data) => {
      const mensual_ = document.getElementById("analytics-tab-1");

      const clientesConDeuda = data.filter(
        (cliente) =>
          cliente.debe !== "No debe" && cliente.debe !== "No tiene pagos"
      );

      if (clientesConDeuda.length > 0) {
        mensual_.innerHTML = `MENSUAL <span class="badge bg-danger text-white" style="margin-left: 5px;">${clientesConDeuda.length}</span>`;
      }

      viewMorososMensual(clientesConDeuda);
    });
}

function viewMorososMensual(deuda) {
  let html = "";

  deuda.forEach((item) => {
    html += `
      <li class="list-group-item pt-2 pb-2">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <div class="avtar avtar-s border text-danger" style="width: 70px">${item.debe}</div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="row g-1">
                    <div class="col-12">
                        <h6 class="mb-0">${item.razon_social}</h6>
                    </div>
                </div>
            </div>
        </div>
      </li>
    `;
  });

  morosos_mensual.innerHTML = html;
}

const morosos_anual = document.getElementById("morosos_anual");

morososAnual();

function morososAnual() {
  const tipo = "TODOS";
  const estado = 1;
  fetch(base_url + `deudas-anuales/${tipo}/${estado}`)
    .then((res) => res.json())
    .then((data) => {
      const anual = document.getElementById("analytics-tab-2");

      const clientesConDeuda = data.filter(
        (cliente) => cliente.pagos_pendientes !== "0"
      );

      if (clientesConDeuda.length > 0) {
        anual.innerHTML = `ANUAL <span class="badge bg-danger text-white" style="margin-left: 5px;">${clientesConDeuda.length}</span>`;
      }

      viewMorososAnual(clientesConDeuda);
    });
}

function viewMorososAnual(deuda) {
  let html = "";

  deuda.forEach((item) => {
    html += `
      <li class="list-group-item pt-2 pb-2">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <div class="avtar avtar-s border text-danger" style="width: 70px">${item.pagos_pendientes}</div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="row g-1">
                    <div class="col-12">
                        <h6 class="mb-0">${item.razon_social}</h6>
                    </div>
                </div>
            </div>
        </div>
      </li>
    `;
  });

  morosos_anual.innerHTML = html;
}

const morosos_servidor = document.getElementById("morosos_servidor");

morososServidor();

function morososServidor() {
  const tipo = "TODOS";
  const estado = 1;
  fetch(base_url + `render-contribuyentes`)
    .then((res) => res.json())
    .then((data) => {
      const servidor = document.getElementById("analytics-tab-3");

      const clientesConDeuda = data.filter(
        (cliente) =>
          cliente.pagos !== "NO TIENE REGISTROS" && cliente.pagos !== "NO DEBE"
      );

      if (clientesConDeuda.length > 0) {
        servidor.innerHTML = `SERVIDOR <span class="badge bg-danger text-white" style="margin-left: 5px;">${clientesConDeuda.length}</span>`;
      }

      viewMorososServidor(clientesConDeuda);
    });
}

function viewMorososServidor(deuda) {
  let html = "";

  deuda.forEach((item) => {
    html += `
      <li class="list-group-item pt-2 pb-2">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <div class="avtar avtar-s border text-danger" style="width: 90px">${item.pagos}</div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="row g-1">
                    <div class="col-12">
                        <h6 class="mb-0">${item.razon_social}</h6>
                    </div>
                </div>
            </div>
        </div>
      </li>
    `;
  });

  morosos_servidor.innerHTML = html;
}

new ApexCharts(document.querySelector("#overview-product-graph"), {
  chart: { height: 350, type: "pie" },
  labels: ["Components", "Widgets", "Pages", "Forms", "Other", "Apps"],
  series: [40, 20, 10, 15, 5, 10],
  colors: ["#4680FF", "#4680FF", "#212529", "#212529", "#212529", "#212529"],
  fill: { opacity: [1, 0.6, 0.4, 0.6, 0.8, 1] },
  legend: { show: !1 },
  dataLabels: { enabled: !0, dropShadow: { enabled: !1 } },
  responsive: [
    {
      breakpoint: 575,
      options: { chart: { height: 250 }, dataLabels: { enabled: !1 } },
    },
  ],
}).render();
