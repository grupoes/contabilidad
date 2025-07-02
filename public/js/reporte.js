const url_base = document.getElementById("url").value;
const sucursal = document.getElementById("sucursal_venta");
const shema = document.getElementById("shema");
const idempresa = document.getElementById("idempresa");

document.addEventListener("DOMContentLoaded", function () {
  const formData = new FormData();

  formData.append("shema", shema.value);
  formData.append("idempresa", idempresa.value);

  fetch(url_base + "/sucursales", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      let html = `<option value="0">TODOS</option>`;

      data.forEach((sucursal) => {
        html += `<option value="${sucursal.sede_id}">${sucursal.sede_descripcion}</option>`;
      });

      sucursal.innerHTML = html;
    });
});

const ruc = document.getElementById("ruc_empresa");
const fecha_inicio = document.getElementById("fecha_inicio_ventas");
const fecha_fin = document.getElementById("fecha_fin_ventas");
const cuenta = document.getElementById("cuenta_ventas");
const glosa = document.getElementById("glosa_ventas");

const btn_venta = document.getElementById("btn_venta");
const btn_maq_venta = document.getElementById("btn_maq_venta");

const cont = document.getElementById("contentVentas");
const cabecera = document.getElementById("cabecera_table");

var options = { year: "numeric", month: "long", day: "numeric" };

$("#data_venta").DataTable();

const rz = document.getElementById("razon_social").value;

const ra = rz.replace(".", "");

btn_venta.addEventListener("click", (e) => {
  e.preventDefault();

  if (fecha_inicio.value == "") {
    alert("Ingresar una fecha de inicio");
    return false;
  }

  if (fecha_fin.value == "") {
    alert("Ingresar una fecha de fin");
    return false;
  }

  if (cuenta.value == "") {
    alert("Ingresar una cuenta");
    return false;
  }

  if (glosa.value == "") {
    alert("Ingresar una glosa");
    return false;
  }

  cabecera.innerHTML = "";
  cont.innerHTML = "";

  $("#cover-spin").show(0);

  const formData = new FormData();

  formData.append("sucursal", sucursal.value);
  formData.append("fecha_inicio", fecha_inicio.value);
  formData.append("fecha_fin", fecha_fin.value);
  formData.append("cuenta", cuenta.value);
  formData.append("glosa", glosa.value);
  formData.append("ruc", ruc.value);
  formData.append("shema", shema.value);

  fetch(url_base + "/reporte-detallado", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      $("#cover-spin").hide(0);

      $("#data_venta").DataTable().destroy();

      cabecera.innerHTML = `
            <th>N°</th>
            <th>FECHA</th>
            <th>TIPO MONEDA</th>
            <th>DOCUMENTO</th>
            <th>#_DOCUMENTO</th>
            <th>CONDICION</th>
            <th>RUC</th>
            <th>RAZON SOCIAL</th>
            <th>EXONERADA</th>
            <th>GRAVADA</th>
            <th>INAFECTA</th>
            <th>VVENTA</th>
            <th>VALOR VENTA</th>
            <th>IGV</th>
            <th>BOLSA</th>
            <th>ICB</th>
            <th>TOTAL</th>
            <th>TIPO_CAMBIO</th>
            <th>GLOSA</th>
            <th>CUENTA</th>
            <th>AFECTACION</th>
            <th>CONDICION DEL CONTRIBUYENTE</th>
            <th>ESTADO DEL CONTRIBUYENTE</th>
            <th>ESTADO SUNAT</th>
            <th>REFERENCIA</th>
            <th>FECHA REFERENCIA</th>
        `;

      let html = ``;

      data.forEach((venta, index) => {
        let condicion = "A";

        let total_exonerado = venta.total_exonerado;
        let total_gravado = venta.total_gravado;
        let total_inafecto = venta.total_inafecto;
        let subtotal = venta.subtotal;
        let total_igv = venta.total_igv;
        let total_icbper = venta.total_icbper;
        let total = venta.total;

        if (venta.estado == "f") {
          condicion = "I";
          total_exonerado = "0.00";
          total_gravado = "0.00";
          total_inafecto = "0.00";
          subtotal = "0.00";
          total_igv = "0.00";
          total_icbper = "0.00";
          total = "0.00";
        }

        let afectacion = "NO";

        if (parseFloat(venta.total_igv) > 0) {
          afectacion = "SI";
        }

        let numIdentidad = venta.clie_numero_documento;

        if (venta.clie_numero_documento === "00000000") {
          numIdentidad = "00000001";
        }

        html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${venta.fecha}</td>
                    <td>${venta.tipo_moneda}</td>
                    <td>${venta.tico_descripcion}</td>
                    <td>${venta.numero_documento}</td>
                    <td>${condicion}</td>
                    <td>${numIdentidad}</td>
                    <td>${venta.clie_nombre_razon_social}</td>
                    <td>${total_exonerado}</td>
                    <td>${total_gravado}</td>
                    <td>${total_inafecto}</td>
                    <td>${subtotal}</td>
                    <td>${subtotal}</td>
                    <td>${total_igv}</td>
                    <td>0.00</td>
                    <td>${total_icbper}</td>
                    <td>${total}</td>
                    <td>1</td>
                    <td>${glosa.value}</td>
                    <td>${cuenta.value}</td>
                    <td>${afectacion}</td>
                    <td>HABIDO</td>
                    <td>ACTIVO</td>
                    <td>${venta.homologacion_estado}</td>
                    <td>${venta.referencia}</td>
                    <td>${venta.fecha_referencia}</td>
                </tr>
            `;
      });

      cont.innerHTML = html;

      const ini_venta = new Date(fecha_inicio.value);
      const f_venta = new Date(fecha_fin.value);

      ini_venta.setDate(ini_venta.getDate() + 1);
      f_venta.setDate(f_venta.getDate() + 1);

      const ini = ini_venta.toLocaleDateString("es-ES", options);
      const f = f_venta.toLocaleDateString("es-ES", options);

      $("#data_venta")
        .DataTable({
          ordering: false,
          lengthChange: !1,
          buttons: [
            {
              extend: "excelHtml5",
              title:
                "REPORTE DE VENTAS DETALLADO " + ra + " - " + ini + " AL " + f,
            },
          ],
        })
        .buttons()
        .container()
        .appendTo("#data_venta_wrapper .col-md-6:eq(0)");
    });
});

btn_maq_venta.addEventListener("click", (e) => {
  e.preventDefault();

  if (fecha_inicio.value == "") {
    alert("Ingresar una fecha de inicio");
    return false;
  }

  if (fecha_fin.value == "") {
    alert("Ingresar una fecha de fin");
    return false;
  }

  if (cuenta.value == "") {
    alert("Ingresar una cuenta");
    return false;
  }

  if (glosa.value == "") {
    alert("Ingresar una glosa");
    return false;
  }

  $("#cover-spin").show(0);

  cabecera.innerHTML = "";
  cont.innerHTML = "";

  const formData = new FormData();

  formData.append("sucursal", sucursal.value);
  formData.append("fecha_inicio", fecha_inicio.value);
  formData.append("fecha_fin", fecha_fin.value);
  formData.append("cuenta", cuenta.value);
  formData.append("glosa", glosa.value);
  formData.append("ruc", ruc.value);
  formData.append("shema", shema.value);

  fetch(url_base + "/maqueta-ventas", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      $("#cover-spin").hide(0);

      $("#data_venta").DataTable().destroy();

      cabecera.innerHTML = `
            <th>N°</th>
            <th>FECHA</th>
            <th>TIPO MONEDA</th>
            <th>DOCUMENTO</th>
            <th>#_DOCUMENTO</th>
            <th>CONDICION</th>
            <th>RUC</th>
            <th>RAZON SOCIAL</th>
            <th>VVENTA</th>
            <th>VALOR VENTA</th>
            <th>IGV</th>
            <th>BOLSA</th>
            <th>ICB</th>
            <th>TOTAL</th>
            <th>TIPO_CAMBIO</th>
            <th>GLOSA</th>
            <th>CUENTA</th>
            <th>TIPO</th>
            <th>REFERENCIA</th>
            <th>FECHAREF</th>
        `;

      let html = ``;

      data.forEach((venta, index) => {
        let numRuc = venta.ruc;

        if (venta.ruc === "00000000") {
          numRuc = "00000001";
        }

        html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${venta.fecha}</td>
                    <td>${venta.tipo_moneda}</td>
                    <td>${venta.documento}</td>
                    <td>${venta.numero}</td>
                    <td>A</td>
                    <td>${numRuc}</td>
                    <td>${venta.razon_social}</td>
                    <td>${venta.vventa}</td>
                    <td>${venta.valor_venta}</td>
                    <td>${venta.igv}</td>
                    <td>0.00</td>
                    <td>${venta.icb}</td>
                    <td>${venta.total}</td>
                    <td>${venta.tipo_cambio}</td>
                    <td>${venta.glosa}</td>
                    <td>${venta.cuenta}</td>
                    <td>${venta.tipo}</td>
                    <td>${venta.referencia}</td>
                    <td>${venta.referenciafecha}</td>
                </tr>
            `;
      });

      cont.innerHTML = html;

      const ini_venta = new Date(fecha_inicio.value);
      const f_venta = new Date(fecha_fin.value);

      ini_venta.setDate(ini_venta.getDate() + 1);
      f_venta.setDate(f_venta.getDate() + 1);

      const ini = ini_venta.toLocaleDateString("es-ES", options);
      const f = f_venta.toLocaleDateString("es-ES", options);

      $("#data_venta")
        .DataTable({
          ordering: false,
          lengthChange: !1,
          dom: "Bfrtip",
          buttons: [
            {
              extend: "excel",
              filename: "MAQUETA DE VENTAS " + ra + " - " + ini + " AL " + f,
              title: "",
              autoFilter: true,
              customize: function (xlsx, row) {
                var sheet = xlsx.xl.worksheets["sheet1.xml"];
                $('row c[r^="G"]', sheet).attr("s", "0");
              },
            },
          ],
        })
        .buttons()
        .container()
        .appendTo("#data_venta_wrapper .col-md-6:eq(0)");
    });
});
