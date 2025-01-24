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