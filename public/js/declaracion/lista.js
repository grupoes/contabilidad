const formSelect = document.getElementById("formSelect");
const alertMessage = document.getElementById("alertMessage");
const id_declaracion = document.getElementById("id_declaracion");
const calendario = document.getElementById("calendario");

function declaracion(id, nombre) {
  $("#modalDeclaracion").modal("show");

  formSelect.reset();
  alertMessage.innerHTML = "";
  id_declaracion.value = id;

  const titleModal = document.getElementById("titleModal");
  titleModal.innerHTML = nombre;

  const listDeclaracion = document.getElementById("listDeclaracion");

  fetch(base_url + "listaDeclaracion/" + id)
    .then((response) => response.json())
    .then((data) => {
      let html = "";

      data.forEach((decla) => {
        if (decla.id_pdt == 1 || decla.id_pdt == 3) {
          html += `
          <div class="form-check mb-3">
              <input class="form-check-input" type="radio" name="lista" id="list-${decla.id_pdt}" value="${decla.id_pdt}">
              <label class="form-check-label" for="list-${decla.id_pdt}">${decla.pdt_descripcion}</label>
          </div>
          `;
        }
      });

      listDeclaracion.innerHTML = html;
    });
}

formSelect.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formSelect);

  fetch(base_url + "declaracion/calendario", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        viewConfiguracion(data);
        $("#modalDeclaracion").modal("hide");
        return false;
      }

      alertMessage.innerHTML = `
      <div class="alert alert-danger" role="alert">${data.message}</div>
      `;
    });
});

function viewConfiguracion(data) {
  const anios = data.anios;
  let aniosHtml = `<option value="">Seleccione un año</option>`;

  anios.forEach((anio) => {
    aniosHtml += `<option value="${anio.id_anio}">${anio.anio_descripcion}</option>`;
  });

  const numeros = data.numeros;
  let numerosHtml = "";

  numeros.forEach((numero) => {
    numerosHtml += `<th>${numero.num_descripcion}</th>`;
  });

  const meses = data.meses;
  let mesesHtml = "";

  if (data.lista === "1") {
    meses.forEach((mes) => {
      mesesHtml += `<tr>
                      <td class="sticky-col" style="background: #f1eeee;"><b>${mes.mes_descripcion}</b> se declara (${mes.mes_declaracion})</td>`;

      numeros.forEach((number) => {
        mesesHtml += `<td> <input type="text" maxlength='2' name="datos[]" id="datos${number.id_numero}${mes.id_mes}" class="form-control form-control-sm" onBlur="enviar_datos(${number.id_numero}, ${mes.id_mes})"> </td>`;
      });

      mesesHtml += `</tr>`;
    });
  }

  if (data.lista === "3") {
    let htmlFila = "";

    numeros.forEach((number) => {
      htmlFila += `<td> <input type="date" maxlength='2' name="datos[]" id="datos${number.id_numero}1" class="form-control form-control-sm" onBlur="enviar_datos(${number.id_numero}, 1)"> </td>`;
    });

    mesesHtml += `
      <tr>
        <td class="sticky-col" style="background: #f1eeee;"><b>Fecha</b></td>
        ${htmlFila}
      </tr>`;
  }

  let html = `
        <div class="card">
            <div class="card-body">
                <input type="hidden" id="lista" value="${data.lista}" />
                <h5 class="text-center">CONFIGURACION ${data.declaracion} - ${data.pdtNombre}</h5>
                <div class="row mb-3">
                    <label for="anio" class="col-sm-4 col-form-label">Seleccionar el año en el que desea trabajar:</label>
                    <div class="col-sm-4">
                        <select name="" id="anio" class="form-select" onchange="getAnio(event)" >${aniosHtml}</select>
                    </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-sm">
                      <thead>
                          <tr class="text-center">
                              <th class="sticky-col" style="background: #f1eeee;">Periodo</th>
                              ${numerosHtml}
                          </tr>
                      </thead>
                      <tbody>
                          ${mesesHtml}
                      </tbody>
                  </table>
                </div>
            </div>
        </div>
    `;

  calendario.innerHTML = html;
}

function getAnio(e) {
  const valor = e.target.value;

  if (valor === "") {
    $('input[name="datos[]"]')
      .map(function (n, i) {
        $(this).val("");
      })
      .get();
    return false;
  }

  const lista = document.getElementById("lista").value;

  const formData = new FormData();
  formData.append("anio", valor);
  formData.append("lista", lista);

  fetch(base_url + "declaracion/extraer_data", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      $('input[name="datos[]"]')
        .map(function (n, i) {
          $(this).val("");
        })
        .get();

      data.forEach((dia) => {
        if (dia.dia_exacto == 0) {
          $("#datos" + dia.numeracion).val("");
        } else {
          if (lista == 3) {
            $("#datos" + dia.numeracionBalance).val(dia.fecha_exacta);
          } else {
            $("#datos" + dia.numeracion).val(dia.dia_exacto);
          }
        }
      });
    });
}

function enviar_datos(id_numero, id_mes) {
  let idn = id_numero.toString();
  let idm = id_mes.toString();

  let anio = $("#anio").val();
  let fecha = $("#datos" + idn + idm);

  if (anio != "") {
    let maximo = mes(id_mes);

    if (fecha.val() > maximo) {
      fecha.val(maximo);
    }

    //if (fecha.val() != "" && fecha.val().length >= 1) {
    const list = document.getElementById("lista").value;
    const formData = new FormData();
    formData.append("id_anio", anio);
    formData.append("id_mes", idm);
    formData.append("id_numero", idn);
    formData.append("dia", fecha.val());
    formData.append("lista", list);

    fetch(base_url + "declaracion/guardar_datos", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          notifier.show("¡Bien hecho!", data.message, "success", "", 4000);
          return false;
        }

        notifier.show("¡Sorry!", data.message, "danger", "", 4000);
      });
    //}
  }
}

function mes(id_mes) {
  if (id_mes == 1) {
    return 31;
  }
  if (id_mes == 2) {
    return 28;
  }
  if (id_mes == 3) {
    return 31;
  }
  if (id_mes == 4) {
    return 30;
  }
  if (id_mes == 5) {
    return 31;
  }
  if (id_mes == 6) {
    return 30;
  }
  if (id_mes == 7) {
    return 31;
  }
  if (id_mes == 8) {
    return 31;
  }
  if (id_mes == 9) {
    return 30;
  }
  if (id_mes == 10) {
    return 31;
  }
  if (id_mes == 11) {
    return 30;
  }
  if (id_mes == 12) {
    return 31;
  }
}

function enviar_datos_balance(e, id_numero, digito) {
  const valor = e.target.value;

  const fecha = new Date(valor);
  if (isNaN(fecha.getTime())) {
    console.log("Fecha inválida.");
    return;
  }

  console.log("fecha valida");
}
