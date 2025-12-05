document.addEventListener("DOMContentLoaded", function () {

  const newcs = $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);

  const newPlame = $("#tablePlame").DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newPlame);

  const newServidor = $("#tableServidor").DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newServidor);

  const newAnuales = $("#tableAnuales").DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newAnuales);

  const newAfp = $("#tableAfp").DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newAfp);

  const newSire = $("#tableSire").DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newSire);
});

const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  showConfirmButton: true,
  buttonsStyling: false,
});

function viewContribuyentesPdts() {
  $("#modalPdts").modal("show");
  fetch(base_url + "notificacion-pdt-renta")
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
  fetch(base_url + "notificacion-pdt-plame")
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

function viewContribuyentesAnuales() {
  $("#modalPdtsAnuales").modal("show");
  fetch(base_url + "deudores-anuales")
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      const listAnuales = document.getElementById("listAnuales");

      data.forEach((info) => {
        html += `
        <tr>
          <td>${info.ruc} <br> ${info.razon_social}</td>
          <td>${info.anio}</td>
          <td>${info.mensaje}</td>
        </tr>
        `;
      });

      $("#tableAnuales").DataTable().destroy();

      listAnuales.innerHTML = html;

      const newcss = $("#tableAnuales").DataTable(optionsTableDefault);

      new $.fn.dataTable.Responsive(newcss);
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

function viewCertificadosVencer() {
  $("#modalCertificado").modal("show");
  fetch(base_url + "certificados-vencer")
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      const tbodyCertificados = document.getElementById("tbodyCertificados");

      console.log(tbodyCertificados);

      data.forEach((info) => {
        html += `
        <tr>
          <td>${info.fecha_vencimiento} </td>
          <td>${info.fecha_inicio}</td>
          <td>${info.ruc}</td>
          <td>${info.razon_social}</td>
          <td>${info.tipo_certificado}</td>
        </tr>
        `;
      });

      $("#tableCertificados").DataTable().destroy();

      tbodyCertificados.innerHTML = html;

      const newcsss = $("#tableCertificados").DataTable(optionsTableDefault);

      new $.fn.dataTable.Responsive(newcsss);
    });
}

function viewAfps() {
  $("#modalAfp").modal("show");
  fetch(base_url + "faltan-subir-afp")
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      const tbodyAfp = document.getElementById("tbodyAfp");

      data.forEach((info) => {
        html += `
        <tr>
          <td>${info.contribuyente}</td>
          <td>${info.mes} ${info.anio}</td>
        </tr>
        `;
      });

      $("#tableAfp").DataTable().destroy();

      tbodyAfp.innerHTML = html;

      const newcsss = $("#tableAfp").DataTable(optionsTableDefault);

      new $.fn.dataTable.Responsive(newcsss);
    });
}

function viewSire() {
  $("#modalSire").modal("show");
  fetch(base_url + "notificar-sire")
    .then((res) => res.json())
    .then((data) => {
      let html = "";

      const tbodySire = document.getElementById("tbodySire");

      data.forEach((info) => {
        html += `
        <tr>
          <td>${info.contribuyente}</td>
          <td>${info.mes} ${info.anio}</td>
        </tr>
        `;
      });

      $("#tableSire").DataTable().destroy();

      tbodySire.innerHTML = html;

      const newcsss = $("#tableSire").DataTable(optionsTableDefault);

      new $.fn.dataTable.Responsive(newcsss);
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

