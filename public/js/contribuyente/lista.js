const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const btnModal = document.getElementById("btnModal");
const searchDocumento = document.getElementById("searchDocumento");
const titleModal = document.getElementById("titleModal");

const razonSocial = document.getElementById("razonSocial");
const nombreComercial = document.getElementById("nombreComercial");
const direccionFiscal = document.getElementById("direccionFiscal");
const ubigeo = document.getElementById("ubigeo");
const tipoSuscripcion = document.getElementById("tipoSuscripcion");
const costoMensual = document.getElementById("costoMensual");
const constAnual = document.getElementById("constAnual");
const numeroDocumento = document.getElementById("numeroDocumento");
const idTable = document.getElementById("idTable");
const urbanizacion = document.getElementById("urbanizacion");
const tipoServicio = document.getElementById("tipoServicio");
const tipoPago = document.getElementById("tipoPago");
const costoAnual = document.getElementById("costoAnual");
const diaCobro = document.getElementById("diaCobro");
const fechaContrato = document.getElementById("fechaContrato");
const clientesVarios = document.getElementById("clientesVarios");
const boletaAnulado = document.getElementById("boletaAnulado");
const facturaAnulado = document.getElementById("facturaAnulado");
const numeroNotificacion = document.getElementById("numeroNotificacion");

const formDatos = document.getElementById("formDatos");

const titleModalTarifa = document.getElementById("titleModalTarifa");
const tableTarifa = document.getElementById("tableTarifa");
const formTarifa = document.getElementById("formTarifa");

const titleModalCertificado = document.getElementById("titleModalCertificado");
const formCertificado = document.getElementById("formCertificado");
const idTableCertificado = document.getElementById("idTableCertificado");
const tableCertificado = document.getElementById("tableCertificado");

let multipleSystem = new Choices("#choices-system", {
  removeItemButton: true,
  placeholderValue: "Seleccione una o más opciones",
  allowHTML: true,
});

let listaUbigeo = new Choices("#ubigeo", {
  removeItemButton: true,
  searchPlaceholderValue: "Buscar aqui el distrito, provincia o departamento",
  allowHTML: true,
  itemSelectText: "",
});

let prefijo = new Choices("#selectPais", {
  removeItemButton: true,
  searchPlaceholderValue: "Buscar el país",
  allowHTML: true,
  itemSelectText: "",
});

btnModal.addEventListener("click", (e) => {
  $("#modalAddEdit").modal("show");

  titleModal.textContent = "Agregar Empresa";

  idTable.value = 0;
  numeroDocumento.value = "";
  razonSocial.value = "";
  nombreComercial.value = "";
  direccionFiscal.value = "";
  listaUbigeo.removeActiveItems();
  urbanizacion.value = "";
  tipoSuscripcion.value = "NO GRATUITO";
  tipoServicio.value = "CONTABLE";
  tipoPago.value = "ADELANTADO";
  costoMensual.value = "";
  costoAnual.value = "";
  diaCobro.value = "01";
  fechaContrato.value = "";
  multipleSystem.removeActiveItems();
  clientesVarios.value = "00000001";
  boletaAnulado.value = "00000000";
  facturaAnulado.value = "00000000001";
});

// Función para cargar datos de ubigeo
async function cargarUbigeo() {
  try {
    const response = await fetch(`${base_url}all-ubigeo`);
    if (!response.ok) throw new Error("Error en la respuesta de la API");

    const data = await response.json();

    // Cargar datos en Choices
    listaUbigeo.setChoices(
      data.map((release) => ({
        label: `${release.distrito} - ${release.provincia} - ${release.departamento}`,
        value: release.codigo_ubigeo,
      })),
      "value",
      "label",
      true
    );
  } catch (error) {
    console.error("Error al cargar ubigeo:", error);
    alert("No se pudieron cargar los datos de ubigeo.");
  }
}

// Llamar a cargarUbigeo para inicializar
cargarUbigeo();

searchDocumento.addEventListener("click", (e) => {
  const numDoc = numeroDocumento.value;

  if (numDoc.length === 11) {
    const getRazonSocial = document.getElementById("getRazonSocial");
    const getNombreComercial = document.getElementById("getNombreComercial");
    const getDireccionFiscal = document.getElementById("getDireccionFiscal");
    const getUbigeo = document.getElementById("getUbigeo");

    getRazonSocial.textContent = "Obteniendo razón social...";
    razonSocial.disabled = true;

    getNombreComercial.textContent = "Obteniendo nombre comercial...";
    nombreComercial.disabled = true;

    getDireccionFiscal.textContent = "Obteniendo dirección fiscal...";
    direccionFiscal.disabled = true;

    getUbigeo.textContent = "Obteniendo ubigeo...";
    ubigeo.disabled = true;

    searchDocumento.innerHTML = `
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        `;

    numDoc.disabled = true;

    fetch(base_url + "api/dni-ruc/ruc/" + numDoc)
      .then((res) => res.json())
      .then((data) => {
        getRazonSocial.textContent = "";
        razonSocial.disabled = false;

        getNombreComercial.textContent = "";
        nombreComercial.disabled = false;

        getDireccionFiscal.textContent = "";
        direccionFiscal.disabled = false;

        getUbigeo.textContent = "";
        ubigeo.disabled = false;

        searchDocumento.innerHTML = `
                <i class="fas fa-search"></i>
            `;

        numDoc.disabled = false;

        if (data.respuesta === "error") {
          alert("R.U.C. no fue encontrado");
          return false;
        }

        razonSocial.value = data.data.razon_social;
        nombreComercial.value = data.data.nombre_comercial;
        direccionFiscal.value = data.data.direccion;

        return cargarUbigeo().then(() => {
          listaUbigeo.setChoiceByValue(data.data.codigo_ubigeo);
        });
      });
  } else {
    alert("El número del documento debe tener 11 dígitos");
  }
});

tipoSuscripcion.addEventListener("change", (e) => {
  const tipo = e.target.value;
  const costos = document.getElementsByClassName("costos");

  if (tipo === "GRATUITO") {
    [...costos].forEach((costo) => costo.setAttribute("hidden", true));
  } else {
    [...costos].forEach((costo) => costo.removeAttribute("hidden"));
  }
});

formDatos.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formDatos);

  fetch(base_url + "contribuyente/add", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalAddEdit").modal("hide");
        notifier.show("¡Bien hecho!", data.message, "success", "", 4000);

        listaContribuyentes();
      } else {
        notifier.show("¡Sorry!", data.message, "danger", "", 4000);

        setTimeout(() => {
          const notifierElement = document.querySelector(".notifier-container");
          if (notifierElement) {
            notifierElement.style.zIndex = "1100"; // Nivel mayor al modal
          }
        }, 0);
      }
    });
});

const selectOpciones = document.getElementById("selectOpciones");
const selectEstado = document.getElementById("selectEstado");
const tableBody = document.getElementById("tableBody");

listaContribuyentes();

function listaContribuyentes() {
  fetch(
    base_url +
      "contribuyente/all/" +
      selectOpciones.value +
      "/" +
      selectEstado.value
  )
    .then((res) => res.json())
    .then((data) => {
      viewListContribuyentes(data);
    });
}

function optionsTable(id, ruc) {
  return `
        <a class="dropdown-item" href="#"><i class="ti ti-notebook"></i>Lista de Boletas</a>
        <a class="dropdown-item" href="#"><i class="ti ti-book"></i>Registrar Boletas</a>
        <a class="dropdown-item" href="#" onclick="importarBoletas(event, ${id})"><i class="ti ti-file-import"></i>Importar Boletas</a>
        <a class="dropdown-item" href="#" onclick="deleteEmpresa(event, ${id})"><i class="ti ti-trash"></i>Eliminar Empresa</a>
        <a class="dropdown-item" href="#" onclick="configurarDeclaraciones(event, ${id})"><i class="ti ti-settings"></i>Configurar declaraciones</a>
        <a class="dropdown-item" href="#"><i class="ti ti-settings-automation"></i>Declaración tributaria</a>
        <a class="dropdown-item" href="#" onclick="verAcceso(event, ${id})"><i class="ti ti-key"></i>Ver contraseña</a>
        <a class="dropdown-item" href="https://esconsultoresyasesores.com:9094/maqueta-compras/${ruc}" target="__blank"><i class="ti ti-file-download"></i>Escanear y generar maquetas de compras</a>
        <a class="dropdown-item" href="https://esconsultoresyasesores.com:9093/reportes/${ruc}" target="__blank"><i class="ti ti-file-analytics"></i>Reporte Comercial</a>
        <a class="dropdown-item" href="https://esconsultoresyasesores.com:9092/reportes/${ruc}" target="__blank"><i class="ti ti-file-text"></i>Reporte Restaurante</a>
        <a class="dropdown-item" href="https://grupoesconsultores.com/contagrupoes/maqueta-compras/${ruc}" target="__blank"><i class="ti ti-file-symlink"></i>Enviar archivos</a>
        <a class="dropdown-item" href="#" onclick="loadModalContactos(event, ${id})"><i class="ti ti-accessible"></i>Contactos</a>
    `;
}

function viewListContribuyentes(data) {
  let html = "";

  data.forEach((emp, index) => {
    let opciones = optionsTable(emp.id, emp.ruc);

    let tieneSistema =
      emp.tiene_sistema === "SI"
        ? `<a href="#"><span class="badge bg-success tiene-sistema" data-id="${emp.id}">SI</span></a>`
        : `<span class="badge bg-warning">NO</span>`;

    let tiene_certificado = "";

    if (emp.tiene_sistema === "SI") {
      if (emp.tiene_certificado === "SI" && emp.certificado_vencido === "SI") {
        tiene_certificado =
          '<span class="badge bg-danger" title="tiene certificado digital vencido">C</span>';
      } else if (
        emp.tiene_certificado === "SI" &&
        emp.certificado_vencido === "NO"
      ) {
        tiene_certificado =
          '<span class="badge bg-success" title="tiene certificado digital">C</span>';
      } else {
        tiene_certificado =
          '<span class="badge bg-warning" title="No cuenta con certificado digital">C</span>';
      }
    }

    let estado = emp.estado === "1" ? "checked" : "";

    let monto;

    if (emp.tipoSuscripcion === "SI GRATUITO") {
      monto = `GRATUITO`;
    } else {
      if (emp.tipoServicio === "ALQUILER") {
        monto = `
                    <p class="f-14 mb-0">M: ${emp.costoMensual}</p>
                `;
      } else {
        monto = `
                    <p class="f-14 mb-0">M: ${emp.costoMensual}</p>
                    <p class="f-14 mb-0">A: ${emp.costoAnual}</p>
                `;
      }
    }

    let estadoEmpresa = "";

    switch (emp.respuesta) {
      case 0:
        estadoEmpresa = `<span class="badge bg-success" title="">${emp.tipo}</span>`;
        break;
      case 1:
        estadoEmpresa = `<span class="badge bg-primary" title="">${emp.tipo}</span>`;
        break;
      case 2:
        estadoEmpresa = `<span class="badge bg-warning" title="">${emp.tipo}</span>`;
        break;
      case 3:
        estadoEmpresa = `<span class="badge bg-danger" title="">${emp.tipo}</span>`;
        break;
      default:
        break;
    }

    html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="row">
                        <div class="col">
                            <h6 class="mb-1"><a href="#" class="num-doc" data-id="${
                              emp.id
                            }">${emp.ruc}</a></h6>
                            <p class="text-muted f-14 mb-0"> ${
                              emp.razon_social
                            } </p>
                        </div>
                    </div>
                </td>
                <td><a href="#" class="tipoServicio" data-id="${emp.id}">${
      emp.tipoServicio
    }</a></td>
                <td>
                    ${monto}
                </td>
                <td>
                    ${tieneSistema}
                    <a href="#">${tiene_certificado}</a>
                </td>
                <td> 
                    <div class="form-check form-switch custom-switch-v1 mb-2">
                        <input type="checkbox" class="form-check-input input-success" name="estado" id="estado${
                          emp.id
                        }" ${estado} onchange="toggleSwitchStatus(this, ${
      emp.id
    })">
                    </div>

                </td>
                <td>
                    ${estadoEmpresa}
                </td>
                <td>
                    <div class="dropdown">
                        <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <i class="ti ti-dots-vertical f-18"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="position: fixed;z-index: 1050;top: 50px;left: auto;right: 20px;overflow-y: auto;">
                            ${opciones}          
                        </div>
                    </div>
                </td>
            </tr>
        `;
  });

  $($table).DataTable().destroy();

  tableBody.innerHTML = html;

  const newcs = $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);
}

selectOpciones.addEventListener("change", (e) => {
  listaContribuyentes();
});

selectEstado.addEventListener("change", (e) => {
  listaContribuyentes();
});

tableBody.addEventListener("click", (e) => {
  if (e.target.classList.contains("num-doc")) {
    e.preventDefault();
    idTable.value = e.target.dataset.id;

    $("#modalAddEdit").modal("show");

    titleModal.textContent = "Editar Empresa";

    fetch(base_url + "contribuyente/get/" + e.target.dataset.id)
      .then((res) => res.json())
      .then((data) => {
        const empresa = data.data;

        const costos = document.getElementsByClassName("costos");

        if (empresa.tipoSuscripcion === "GRATUITO") {
          [...costos].forEach((costo) => costo.setAttribute("hidden", true));
        } else {
          [...costos].forEach((costo) => costo.removeAttribute("hidden"));
        }

        numeroDocumento.value = empresa.ruc;
        razonSocial.value = empresa.razon_social;
        nombreComercial.value = empresa.nombre_comercial;
        direccionFiscal.value = empresa.direccion_fiscal;
        urbanizacion.value = empresa.urbanizacion;
        tipoSuscripcion.value = empresa.tipoSuscripcion;
        tipoServicio.value = empresa.tipoServicio;
        tipoPago.value = empresa.tipoPago;
        costoMensual.value = empresa.costoMensual;
        costoAnual.value = empresa.costoAnual;
        diaCobro.value = empresa.diaCobro;
        fechaContrato.value = empresa.fechaContrato;
        numeroNotificacion.value = empresa.numeroWhatsappId;

        const sistemas = data.sistemas;

        const sistemasArray = sistemas.map((sistema) => sistema.system_id);

        sistemasArray.forEach((value) => {
          multipleSystem.setChoiceByValue(value.toString());
        });

        listaUbigeo.setChoiceByValue(empresa.ubigeo_id);
      });
  }

  if (e.target.classList.contains("tipoServicio")) {
    e.preventDefault();
    const idTableTarifa = document.getElementById("idTableTarifa");
    idTableTarifa.value = e.target.dataset.id;

    $("#modalTipoServicio").modal("show");

    renderTarifas(e.target.dataset.id);
  }

  if (e.target.classList.contains("tiene-sistema")) {
    const valor = e.target.getAttribute("data-id");

    idTableCertificado.value = valor;

    $("#modalSistema").modal("show");

    renderCertificados(valor);
  }
});

function renderTarifas(id) {
  fetch(base_url + "contribuyente/historial-tarifa/" + id)
    .then((res) => res.json())
    .then((data) => {
      const empresa = data.data_contribuyente;
      titleModalTarifa.textContent = `Historial de tarifas de ${empresa.razon_social}`;

      const tarifas = data.data_tarifa;

      const cantidadTarifas = tarifas.length;

      let html = "";

      tarifas.forEach((tarifa, index) => {
        let deleteTarifa =
          index == cantidadTarifas - 1
            ? ""
            : `<a href="#"><i class="fas fa-trash text-danger fs-14 btnDeleteTarifa" data-id="${tarifa.id}"></i></a>`;

        let fechaFin;

        if (fechaFin === null) {
          fechaFin = "";
        } else {
          fechaFin = tarifa.fecha_fin;
        }

        html += `
                    <tr>
                        <td>${tarifa.fecha_inicio}</td>
                        <td>${fechaFin}</td>
                        <td>${tarifa.monto_mensual}</td>
                        <td>${tarifa.monto_anual}</td>
                        <td>
                            ${deleteTarifa}
                        </td>
                    </tr>
                `;
      });

      tableTarifa.innerHTML = html;
    });
}

function renderCertificados(id) {
  fetch(base_url + "contribuyente/certificado-digital/" + id)
    .then((res) => res.json())
    .then((data) => {
      const empresa = data.data_contribuyente;
      titleModalCertificado.textContent = `Historial de Certificados de ${empresa.razon_social}`;

      const certificados = data.data_certificado;

      let html = "";

      certificados.forEach((certi, index) => {
        let estado =
          certi.estado == 1
            ? `<span class="badge bg-success">VIGENTE</span>`
            : `<span class="badge bg-danger">VENCIDO</span>`;

        let opciones = "";

        if (certi.estado == 1) {
          let descargar = "";

          if (certi.tipo_certificado === "PROPIO") {
            descargar = `<a href="#" onclick="descargarCertificadoDigital(event, '${certi.nameFile}')" class="descargar-certificado-digital" title="descargar certificado digital"><i class="fas fa-cloud-download-alt text-success fs-3"></i></a>`;
          }

          opciones = `
                        ${descargar}

                        <a href="#" onclick="deleteCertificadoDigital(event, ${certi.id})" class="delete-certificado-digital" title="eliminar certificado digital"><i class="fas fa-trash text-danger fs-3"></i></a>
                    `;
        }

        html += `
                    <tr>
                        <td>${certi.tipo_certificado}</td>
                        <td>${certi.fecha_inicio}</td>
                        <td>${certi.fecha_vencimiento}</td>
                        <td>${certi.clave}</td>
                        <td>${estado}</td>
                        <td>
                            ${opciones}
                        </td>
                    </tr>
                `;
      });

      tableCertificado.innerHTML = html;
    });
}

formTarifa.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formTarifa);

  fetch(base_url + "contribuyente/add-tarifa", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "error") {
        alert(data.message);
        return false;
      }

      formTarifa.reset();

      renderTarifas(idTableTarifa.value);
    });
});

tableTarifa.addEventListener("click", (e) => {
  if (e.target.classList.contains("btnDeleteTarifa")) {
    const valor = e.target.getAttribute("data-id");

    if (confirm("¿Estás seguro de eliminar esta tarifa?")) {
      fetch(base_url + "contribuyente/delete-tarifa/" + valor)
        .then((res) => res.json())
        .then((data) => {
          renderTarifas(idTableTarifa.value);
        });
    }
  }
});

formCertificado.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formCertificado);

  fetch(base_url + "contribuyente/add-certificado", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "error") {
        alert(data.message);
        return false;
      }

      formCertificado.reset();

      renderCertificados(idTableCertificado.value);
      listaContribuyentes();
    });
});

function descargarCertificadoDigital(e, fileName) {
  e.preventDefault();

  const url = `${base_url}/descargar-certificado/${encodeURIComponent(
    fileName
  )}`;
  const link = document.createElement("a");
  link.href = url;
  link.download = fileName;
  link.click();
}

function deleteCertificadoDigital(e, id) {
  if (confirm("¿Estás seguro de eliminar esta tarifa?")) {
    fetch(base_url + "contribuyente/delete-certificado-digital/" + id)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          renderCertificados(idTableCertificado.value);
          alert(data.message);
        } else {
          alert("Error");
        }
      });
  }
}

if (document.getElementById("btnCertificadoVencer")) {
  const btnCertificadoVencer = document.getElementById("btnCertificadoVencer");

  btnCertificadoVencer.addEventListener("click", (e) => {
    $("#modalCertificado").modal("show");
  });
}

const tipo_certificado = document.getElementById("tipo_certificado");
const file_certificado = document.getElementById("file_certificado");
const claveCertificado = document.getElementById("claveCertificado");

tipo_certificado.addEventListener("change", (e) => {
  const valor = e.target.value;

  const pse = document.getElementsByClassName("pse");

  if (valor === "PROPIO") {
    file_certificado.setAttribute("required", true);
    claveCertificado.setAttribute("required", true);

    [...pse].forEach((p) => p.removeAttribute("hidden", true));

    claveCertificado.value = "";
    file_certificado.value = "";
  } else {
    file_certificado.removeAttribute("required");
    claveCertificado.removeAttribute("required");

    [...pse].forEach((p) => p.setAttribute("hidden", true));
  }
});

function toggleSwitchStatus(switchElement, id) {
  let checked = switchElement.checked ? 1 : 2;
  let messageStatus = "";
  let buttonText = "";

  if (checked == 1) {
    messageStatus = "¿Estás seguro de activar este contribuyente?";
    buttonText = "Si, activar";
  } else {
    messageStatus = "¿Estás seguro de desactivar este contribuyente?";
    buttonText = "Si, desactivar";
  }

  swalWithBootstrapButtons
    .fire({
      title: messageStatus,
      text: "¡No podrá revertir después!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: buttonText,
      cancelButtonText: "No, cancelar!",
      reverseButtons: true,
    })
    .then((result) => {
      if (result.isConfirmed) {
        fetch(base_url + "contribuyente/status/" + id + "/" + checked)
          .then((res) => res.json())
          .then((data) => {
            notifier.show("¡Bien hecho!", data.message, "success", "", 2000);
          });
      } else {
        if (checked == 1) {
          switchElement.checked = false;
        } else {
          switchElement.checked = true;
        }
      }
    });
}

const contribuyente_id = document.getElementById("contribuyente_id");
const contacto_id = document.getElementById("contacto_id");
const btnFormContacto = document.getElementById("btnFormContacto");

function loadModalContactos(e, id) {
  e.preventDefault();

  $("#modalContacto").modal("show");

  btnFormContacto.textContent = "Agregar";

  contribuyente_id.value = id;

  cargarPaises().then(() => {
    prefijo.setChoiceByValue("51");
  });

  renderContactos(id);
}

async function cargarPaises() {
  try {
    const response = await fetch(`${base_url}contribuyentes/paises`);
    if (!response.ok) throw new Error("Error en la respuesta de la API");

    const data = await response.json();

    // Cargar datos en Choices
    prefijo.setChoices(
      data.map((release) => ({
        label: `${release.pais}(+${release.codigo})`,
        value: release.codigo,
      })),
      "value",
      "label",
      true
    );
  } catch (error) {
    console.error("Error al cargar ubigeo:", error);
    alert("No se pudieron cargar los datos de ubigeo.");
  }
}

cargarPaises();

const formContacto = document.getElementById("formContacto");
const tableContacto = document.getElementById("tableContacto");
const numero_whatsapp = document.getElementById("numero_whatsapp");
const numero_llamadas = document.getElementById("numero_llamadas");
const nombre_contacto = document.getElementById("nombre_contacto");
const correo = document.getElementById("correo");

formContacto.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formContacto);

  fetch(base_url + "contribuyente/add-contacto", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "error") {
        alert(data.message);
        return false;
      }

      formContacto.reset();

      prefijo.removeActiveItems();

      cargarPaises().then(() => {
        prefijo.setChoiceByValue("51");
      });

      renderContactos(contribuyente_id.value);
    });
});

function renderContactos(id) {
  fetch(base_url + "contribuyente/contactos/" + id)
    .then((res) => res.json())
    .then((data) => {
      const contactos = data.contactos;
      const contrib = data.data_contribuyente;

      titleModalContactos.textContent = `Contactos de ${contrib.razon_social}`;

      let html = "";

      contactos.forEach((contacto) => {
        html += `
                    <tr>
                        <td>${contacto.nombre_contacto}</td>
                        <td>${contacto.numero_whatsapp}</td>
                        <td>${contacto.telefono}</td>
                        <td>${contacto.correo}</td>
                        <td>
                            <a href="#" onclick="deleteContacto(event, ${contacto.id})" class="text-danger"><i class="fas fa-trash"></i></a>
                            <a href="#" onclick="editContacto(event, ${contacto.id})" class="text-primary"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                `;
      });

      tableContacto.innerHTML = html;
    });
}

const cleanForm = document.getElementById("cleanForm");

cleanForm.addEventListener("click", (e) => {
  formContacto.reset();
  prefijo.removeActiveItems();

  contacto_id.value = 0;

  btnFormContacto.textContent = "Agregar";

  cargarPaises().then(() => {
    prefijo.setChoiceByValue("51");
  });
});

function editContacto(e, id) {
  e.preventDefault();

  btnFormContacto.textContent = "Editar";

  fetch(base_url + "contribuyente/get-contacto/" + id)
    .then((res) => res.json())
    .then((data) => {
      const contacto = data;

      const prefijo = contacto.prefijo;
      let whatsapp = contacto.numero_whatsapp;

      if (whatsapp.startsWith(prefijo)) {
        whatsapp = whatsapp.slice(prefijo.length);
      }

      contacto_id.value = contacto.id;
      contribuyente_id.value = contacto.contribuyente_id;
      nombre_contacto.value = contacto.nombre_contacto;
      numero_whatsapp.value = whatsapp;
      numero_llamadas.value = contacto.telefono;
      correo.value = contacto.correo;

      prefijo.setChoiceByValue(contacto.prefijo);
    });
}

function deleteContacto(e, id) {
  e.preventDefault();

  if (confirm("¿Estás seguro de que deseas eliminarlo?")) {
    fetch(base_url + "contribuyente/delete-contacto/" + id)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          renderContactos(contribuyente_id.value);
        } else {
          alert("Error");
        }
      });
  }
}

numero_whatsapp.addEventListener("paste", function (e) {
  e.preventDefault(); // Evita el pegado normal

  // Obtiene el texto del portapapeles
  let texto = (e.clipboardData || window.clipboardData).getData("text");

  // Limpia el texto (elimina TODOS los espacios)
  let textoLimpio = texto.replace(/\s+/g, "");

  // O si quieres eliminar solo al inicio y al final: texto.trim()
  // O si quieres reemplazar múltiples espacios por uno solo: texto.trim().replace(/\s+/g, ' ')

  // Pega el texto limpio en el input
  document.execCommand("insertText", false, textoLimpio);
  numero_llamadas.value = textoLimpio;
});

const titleImportBoletas = document.getElementById("titleImportBoletas");
const numero_ruc = document.getElementById("numero_ruc");
const rucEmpresa = document.getElementById("rucEmpresa");

function importarBoletas(e, id) {
  e.preventDefault();
  $("#modalImportBoletas").modal("show");

  fetch(base_url + "contribuyentes/getId/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleImportBoletas.textContent = `Importar boletas de ${data.razon_social}`;
      numero_ruc.value = data.ruc;
      rucEmpresa.value = data.ruc;
    });
}

const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  showConfirmButton: true,
  buttonsStyling: false,
});

function deleteEmpresa(e, id) {
  e.preventDefault();

  swalWithBootstrapButtons
    .fire({
      title: `¿Seguro desea eliminarlo?`,
      text: "¡No podrá revertir después!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar!",
      cancelButtonText: "No, cancelar!",
      reverseButtons: true,
    })
    .then((result) => {
      if (result.isConfirmed) {
        fetch(base_url + "contribuyente/delete/" + id)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              listaContribuyentes();
              Swal.fire({
                position: "top-center",
                icon: "success",
                title: data.message,
                showConfirmButton: false,
                timer: 1500,
              });
              return false;
            }

            swalWithBootstrapButtons.fire("Error!", data.message, "error");
          });
      }
    });
}

function configurarDeclaraciones(e, id) {
  e.preventDefault();

  $("#modalConfigurarDeclaracion").modal("show");

  fetch(base_url + "contribuyente/declaracion/" + id)
    .then((res) => res.json())
    .then((data) => {
      const ruc_empresa = document.getElementById("ruc_empresa");
      ruc_empresa.value = data.contribuyente.ruc;

      const titleModalConfigurar = document.getElementById(
        "titleModalConfigurar"
      );
      titleModalConfigurar.textContent = `Configurar Declaraciones de ${data.contribuyente.razon_social}`;
      viewDeclaraciones(data.configuraciones);
    });
}

function viewDeclaraciones(data) {
  const bodyDeclaracion = document.getElementById("bodyDeclaracion");

  let html = "";

  data.forEach((decla) => {
    html += `
    <div>
      <h4 class="mb-3">${decla.decl_nombre}</h4>

      <div class="row px-3">`;

    decla.pdts.forEach((pdt) => {
      html += `
        <div class="col-md-6">
          <h6 class="mb-2">${pdt.pdt_descripcion}</h6>

          <div class="row px-3">`;

      pdt.tributos.forEach((tributo) => {
        let checked = tributo.configuracion === 1 ? "checked" : "";

        html += `
          <div class="col-md-6 mb-3">
            <input type="checkbox" class="form-check-input" name="declaracion[]" value="${tributo.id_tributo}" id="tributo${tributo.id_tributo}${pdt.id_pdt}${decla.id_declaracion}" ${checked}>
            <label for="tributo${tributo.id_tributo}${pdt.id_pdt}${decla.id_declaracion}">${tributo.tri_descripcion}</label>
          </div>
          `;
      });

      html += `</div>
        </div>
      `;
    });

    html += `</div>

    </div>
    `;
  });

  bodyDeclaracion.innerHTML = html;
}

const formDeclaracion = document.getElementById("formDeclaracion");

formDeclaracion.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formDeclaracion);

  fetch(base_url + "contribuyente/configurar-declaracion", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalConfigurarDeclaracion").modal("hide");
        notifier.show("¡Bien hecho!", data.message, "success", "", 4000);
      } else {
        notifier.show("¡Sorry!", data.message, "danger", "", 4000);
      }
    });
});

const formExcel = document.getElementById("formExcel");
const importComprobantes = document.getElementById("importComprobantes");

formExcel.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(formExcel);

  importComprobantes.textContent = "Importando...";
  importComprobantes.disabled = true;

  fetch(base_url + "contribuyente/importar-boletas", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      importComprobantes.textContent = "Importar";
      importComprobantes.disabled = false;

      if (data.status === "success") {
        descargarBoletas(e, data.numero_ruc, data.minimo, data.maximo);
        formExcel.reset();
        return false;
      } else {
        alert(data.message);
      }
    });
});

function descargarBoletas(e, ruc, minimo, maximo) {
  e.preventDefault();

  const form = document.createElement("form");
  form.method = "POST";
  form.action = base_url + "descargar/excelComprobantes";
  form.target = "_blank";

  // Agregar parámetros como campos ocultos si es necesario
  const input = document.createElement("input");
  input.type = "hidden";
  input.name = "ruc";
  input.value = ruc;

  const inputMin = document.createElement("input");
  inputMin.type = "hidden";
  inputMin.name = "minimo";
  inputMin.value = minimo;

  const inputMax = document.createElement("input");
  inputMax.type = "hidden";
  inputMax.name = "maximo";
  inputMax.value = maximo;

  form.appendChild(input);
  form.appendChild(inputMin);
  form.appendChild(inputMax);

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

const formVacear = document.getElementById("formVacear");
const alertMessage = document.getElementById("alertMessage");
const btnVacear = document.getElementById("btnVacear");

formVacear.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formVacear);

  btnVacear.textContent = "Vaciando data...";
  btnVacear.disabled = true;

  fetch(base_url + "contribuyente/vacear-boletas", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      btnVacear.textContent = "Vaciar";
      btnVacear.disabled = false;

      if (data.status === "success") {
        alertMessage.innerHTML = `<div class="alert alert-success" role="alert">${data.message}</div>`;
        formVacear.reset();

        setTimeout(() => {
          alertMessage.innerHTML = "";
        }, 5000);

        return false;
      }

      alertMessage.innerHTML = `<div class="alert alert-danger" role="alert">${data.message}</div>`;
    });
});

const titleModalAcceso = document.getElementById("titleModalAcceso");
const usuario = document.getElementById("usuario");
const password = document.getElementById("password");
const idcon = document.getElementById("idcon");

function verAcceso(e, id) {
  e.preventDefault();

  $("#modalAcceso").modal("show");

  idcon.value = id;

  fetch(base_url + "contribuyente/ver-acceso/" + id)
    .then((res) => res.json())
    .then((data) => {
      const datos = data.datos;

      titleModalAcceso.textContent = `Acceso de ${datos.razon_social}`;
      usuario.value = datos.ruc;
      password.value = datos.acceso;
    });
}

document
  .getElementById("togglePassword")
  .addEventListener("click", function () {
    var passwordInput = document.getElementById("password");
    var icon = this.querySelector("i");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      passwordInput.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  });

const formClave = document.getElementById("formClave");

formClave.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formClave);

  fetch(base_url + "contribuyente/actualizar-clave", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        notifier.show("¡Bien hecho!", data.message, "success", "", 4000);
        $("#modalAcceso").modal("hide");
        return false;
      }

      notifier.show("¡Sorry!", data.message, "danger", "", 4000);
    });
});
