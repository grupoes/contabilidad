document.addEventListener("DOMContentLoaded", function () {
  loadPdtsSubir();

  const newcs = $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);
});

const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  showConfirmButton: true,
  buttonsStyling: false,
});

function loadPdtsSubir() {
  const listCards = document.getElementById("listCards");

  fetch(base_url + "api/notificacion-pdt-renta")
    .then((res) => res.json())
    .then((data) => {
      const quanty = data.length;

      if (quanty > 0) {
        const html = `
        <div class="col-md-6 col-xl-3">
            <div class="card social-widget-card alerta-card" onclick="viewContribuyentesPdts()">
                <div class="card-body">
                    <h3 class="text-black m-0">${quanty}</h3>
                    <span class="m-t-10 text-black">PDT RENTA</span>
                    <i class="fas fa-book fa-2x mt-2 text-danger"></i>
                </div>
            </div>
        </div>
        `;

        const temp = document.createElement("div");
        temp.innerHTML = html.trim();
        const nuevoNodo = temp.firstElementChild;

        // Insertar como primer hijo del contenedor
        listCards.insertBefore(nuevoNodo, listCards.firstElementChild);
      }
    });
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

        if (pdt.tipo_contrato == "actual") {
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

var e = {
  chart: { height: 250, type: "bar", toolbar: { show: !1 } },
  plotOptions: {
    bar: {
      horizontal: !1,
      columnWidth: "55%",
      borderRadius: 4,
      borderRadiusApplication: "end",
    },
  },
  legend: { show: !0, position: "top", horizontalAlign: "left" },
  dataLabels: { enabled: !1 },
  colors: ["#4680FF", "#4680FF"],
  stroke: { show: !0, width: 3, colors: ["transparent"] },
  fill: { colors: ["#4680FF", "#4680FF"], opacity: [1, 0.5] },
  grid: { strokeDashArray: 4 },
  series: [
    { name: "Net Profit", data: [76, 85, 101, 98, 87, 105, 91] },
    { name: "Revenue", data: [44, 55, 57, 56, 61, 58, 63] },
  ],
  xaxis: { categories: ["Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug"] },
  tooltip: {
    y: {
      formatter: function (e) {
        return "$ " + e + " thousands";
      },
    },
  },
};

new ApexCharts(document.querySelector("#overview-chart-1"), e).render(),
  (e = {
    chart: { height: 250, type: "bar", toolbar: { show: !1 } },
    plotOptions: {
      bar: {
        horizontal: !1,
        columnWidth: "55%",
        borderRadius: 4,
        borderRadiusApplication: "end",
      },
    },
    legend: { show: !0, position: "top", horizontalAlign: "left" },
    dataLabels: { enabled: !1 },
    colors: ["#4680FF", "#4680FF"],
    stroke: { show: !0, width: 3, colors: ["transparent"] },
    fill: { colors: ["#4680FF", "#4680FF"], opacity: [1, 0.5] },
    grid: { strokeDashArray: 4 },
    series: [
      { name: "Net Profit", data: [98, 87, 105, 91, 76, 85, 101] },
      { name: "Revenue", data: [56, 61, 58, 63, 44, 55, 57] },
    ],
    xaxis: { categories: ["Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug"] },
    tooltip: {
      y: {
        formatter: function (e) {
          return "$ " + e + " thousands";
        },
      },
    },
  }),
  new ApexCharts(document.querySelector("#overview-chart-2"), e).render(),
  new ApexCharts(document.querySelector("#overview-chart-4"), e).render();
