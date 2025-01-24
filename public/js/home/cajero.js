new ApexCharts(document.querySelector("#overview-product-graph"), {
    chart: { height: 350, type: "pie" },
    labels: ["Components", "Widgets", "Pages", "Forms", "Other", "Apps"],
    series: [40, 20, 10, 15, 5, 10],
    colors: [
      "#4680FF",
      "#4680FF",
      "#212529",
      "#212529",
      "#212529",
      "#212529",
    ],
    fill: { opacity: [1, 0.6, 0.4, 0.6, 0.8, 1] },
    legend: { show: !1 },
    dataLabels: { enabled: !0, dropShadow: { enabled: !1 } },
    responsive: [
      {
        breakpoint: 575,
        options: { chart: { height: 250 }, dataLabels: { enabled: !1 } },
      },
    ],
  }).render()