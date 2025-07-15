document.addEventListener("DOMContentLoaded", function () {
  loadPdtsSubir();
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
            <div class="card social-widget-card alerta-card">
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
