const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  showConfirmButton: true,
  buttonsStyling: false,
});

const esc = (s) =>
  String(s ?? "").replace(
    /[&<>"]/g,
    (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c])
  );

const TIPOS_HOJA = { manual: "Manual", computarizado: "Computarizado", electronico: "Electrónico" };

const fechaDMY = (f) => (f ? f.split("-").reverse().join("/") : "");

// "2026-10-15 14:30:00" -> "15/10/2026 14:30"
const fechaHora = (f) => (f ? fechaDMY(f.slice(0, 10)) + " " + f.slice(11, 16) : "");

// "Nombre · 15/10/2026 14:30" (vacío si no hay registro de quién lo hizo)
const autoria = (usuario, fecha) => (usuario || fecha ? [usuario, fechaHora(fecha)].filter(Boolean).join(" · ") : "");

const NOMBRES_PERIODO = ["Apertura", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre", "Cierre 13", "Cierre 14"];

function periodoTexto(p) {
  if (!p) return "";
  const [y, m] = p.split("-");
  return (NOMBRES_PERIODO[+m] || m) + " " + y;
}

// Lee un selector de periodo (_periodo.php) y devuelve "YYYY-MM" o ""
function getPeriodo(pid) {
  const m = document.getElementById(pid + "Mes").value;
  const y = document.getElementById(pid + "Anio").value;
  return m && y ? y + "-" + m : "";
}

function confirmar(titulo) {
  return swalWithBootstrapButtons
    .fire({
      title: titulo,
      text: "¡No podrá revertir después!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar!",
      cancelButtonText: "No, cancelar!",
      reverseButtons: true,
    })
    .then((r) => r.isConfirmed);
}

// Respuesta estándar {status, message}
function manejarRespuesta(data, alExito) {
  if (data.status === "success") {
    swalWithBootstrapButtons.fire("Muy bien!", data.message, "success");
    alExito && alExito(data);
    return;
  }
  swalWithBootstrapButtons.fire("Error!", data.message, "error");
}

// ---- Modal de libro (registrar / editar / legalizar siguiente) ----
const formLibro = document.getElementById("formLibro");
const titleModal = document.getElementById("titleModal");

// Select con buscador para contribuyentes
const selectContribuyente = new Choices("#contribuyente", {
  searchPlaceholderValue: "Buscar por razón social o RUC",
  searchResultLimit: 20,
  allowHTML: true,
  itemSelectText: "",
  shouldSort: false,
  noResultsText: "Sin resultados",
});

// Total de hojas = folio final - folio inicial + 1 (solo lectura)
function calcularTotal() {
  const fi = parseInt(document.getElementById("folioInicial").value, 10);
  const ff = parseInt(document.getElementById("folioFinal").value, 10);

  document.getElementById("totalHojas").value = fi >= 1 && ff >= fi ? ff - fi + 1 : "";
}

document.getElementById("folioInicial").addEventListener("input", calcularTotal);
document.getElementById("folioFinal").addEventListener("input", calcularTotal);

// Stan: solo los activos de la sede elegida
const selectStan = document.getElementById("stan");
const avisoStan = document.getElementById("avisoStan");

function cargarStans() {
  const sede = document.getElementById("sede").value;
  const delaSede = STANS.filter((s) => String(s.sede_id) === String(sede));

  selectStan.innerHTML =
    '<option value="">Seleccione...</option>' +
    delaSede.map((s) => `<option value="${s.stan_id}">${esc(s.stan_numero)}</option>`).join("");

  avisoStan.style.display = delaSede.length ? "none" : "";
}

document.getElementById("sede").addEventListener("change", cargarStans);

// Régimen de Renta configurado hoy en el contribuyente (se consulta al abrir el modal).
// El del libro debe coincidir: para cambiarlo hay que hacerlo antes en Contribuyentes.
let regimenConfigurado = "";
let regimenConfiguradoNombre = "";

const selectRegimen = document.getElementById("regimen");
const avisoRegimen = document.getElementById("avisoRegimen");
const btnForm = document.getElementById("btnForm");

const DONDE_CAMBIAR_REGIMEN =
  'Contribuyentes &gt; menú de acciones (⋮) del contribuyente &gt; <b>Configurar declaraciones</b> &gt; PDT &gt; RENTA';

// Devuelve true si el régimen elegido no coincide con el configurado; muestra el aviso y bloquea Guardar
function validarRegimen() {
  const elegido = selectRegimen.value;
  let mensaje = "";

  if (!regimenConfigurado) {
    mensaje = "Este contribuyente no tiene régimen configurado. Configúrelo primero en: ";
  } else if (elegido && elegido !== regimenConfigurado) {
    mensaje = `El régimen del libro debe ser el configurado en el contribuyente (<b>${esc(regimenConfiguradoNombre)}</b>). Para cambiarlo, hágalo primero en: `;
  }

  const bloqueado = mensaje !== "";

  avisoRegimen.innerHTML = bloqueado
    ? `${mensaje}${DONDE_CAMBIAR_REGIMEN}. Luego vuelva aquí. <a href="${base_url}contribuyentes" target="_blank">Ir a Contribuyentes</a>`
    : "";
  avisoRegimen.style.display = bloqueado ? "" : "none";
  btnForm.disabled = bloqueado;

  return bloqueado;
}

selectRegimen.addEventListener("change", () => {
  if (validarRegimen()) {
    swalWithBootstrapButtons.fire(
      "Cambio de régimen",
      "Primero debe cambiar el régimen en la lista de Contribuyentes (menú de acciones > Configurar declaraciones > PDT > RENTA). Si vuelve al régimen configurado podrá guardar.",
      "warning"
    );
  }
});

const tipoLibro = document.getElementById("tipoLibro");
const tipoOtro = document.getElementById("tipoOtro");

tipoLibro.addEventListener("change", () => {
  tipoOtro.style.display = tipoLibro.value === "otro" ? "" : "none";
  tipoOtro.required = tipoLibro.value === "otro";
});

// Con tipo de hoja "Electrónico" no hay hojas, folios, sede ni numeración: esos campos se ocultan y no se envían
const CAMPOS_CON_HOJAS = ["numero", "sede", "notaria", "fechaLegalizacion", "kardex", "stan", "folioInicial", "folioFinal", "totalHojas", "minimo"];

function aplicarTipoHoja() {
  const electronico = document.getElementById("tipoHoja").value === "electronico";

  CAMPOS_CON_HOJAS.forEach((id) => {
    const campo = document.getElementById(id);
    campo.disabled = electronico;
    campo.closest('[class*="col-md-"]').style.display = electronico ? "none" : "";
  });
}

document.getElementById("tipoHoja").addEventListener("change", aplicarTipoHoja);

// formLibro.reset() hace que Choices vuelva a su estado inicial y pierda las opciones: se guardan para restaurarlas
const opcionesContribuyente = selectContribuyente._store.choices.map((c) => ({ value: c.value, label: c.label }));

function abrirModalLibro(titulo, libro) {
  formLibro.reset();

  // Fecha de legalización: hasta hoy (los días futuros quedan bloqueados en el selector)
  const hoy = new Date();
  document.getElementById("fechaLegalizacion").max =
    hoy.getFullYear() + "-" + String(hoy.getMonth() + 1).padStart(2, "0") + "-" + String(hoy.getDate()).padStart(2, "0");

  cargarStans();
  selectContribuyente.setChoices(opcionesContribuyente, "value", "label", true);
  tipoOtro.style.display = "none";
  tipoOtro.required = false;
  selectContribuyente.removeActiveItems();
  selectContribuyente.setChoiceByValue("");
  document.getElementById("contribuyenteNombre").value = "";
  document.getElementById("idLibro").value = "0";
  document.getElementById("minimo").value = "100";
  document.getElementById("folioInicial").value = "1";
  titleModal.textContent = titulo;

  if (libro) {
    document.getElementById("idLibro").value = libro.lib_id || "0";
    selectContribuyente.setChoiceByValue(String(libro.con_id));
    const opcion = opcionesContribuyente.find((o) => String(o.value) === String(libro.con_id));
    document.getElementById("contribuyenteNombre").value = opcion ? opcion.label : "";
    if (libro.tli_id) document.getElementById("tipoLibro").value = libro.tli_id;
    document.getElementById("tipoHoja").value = libro.lib_tipo_hoja || "";
    document.getElementById("numero").value = libro.lib_numero || "";
    // Sede por id; los libros antiguos sin sede_id se buscan por nombre
    const selSede = document.getElementById("sede");
    if (libro.sede_id) {
      selSede.value = libro.sede_id;
    } else if (libro.lib_sede) {
      const op = [...selSede.options].find((o) => o.text.trim().toLowerCase() === String(libro.lib_sede).trim().toLowerCase());
      if (op) selSede.value = op.value;
    }
    // Notaría elegida; en libros antiguos (solo texto) se busca una notaría registrada con ese mismo nombre
    let notariaId = libro.not_id || "";
    const textoNotaria = String(libro.notaria_nombre || libro.lib_notaria || "").trim().toLowerCase();
    if (!notariaId && textoNotaria) {
      const op = [...document.getElementById("notaria").options].find(
        (o) => o.value && o.text.split(" - ")[0].trim().toLowerCase() === textoNotaria
      );
      if (op) notariaId = op.value;
    }
    document.getElementById("notaria").value = notariaId;
    document.getElementById("fechaLegalizacion").value = libro.lib_fecha_legalizacion || "";
    document.getElementById("kardex").value = libro.lib_kardex || "";
    cargarStans();
    document.getElementById("stan").value = libro.stan_id || "";
    document.getElementById("folioInicial").value = libro.lib_folio_inicial || "1";
    document.getElementById("folioFinal").value = libro.lib_folio_final || "";
    document.getElementById("minimo").value = libro.lib_minimo || 100;
  }

  calcularTotal();
  aplicarTipoHoja();

  regimenConfigurado = "";
  regimenConfiguradoNombre = "";
  btnForm.disabled = false;
  avisoRegimen.style.display = "none";

  const mostrar = () => {
    // Siempre parte del régimen vigente del contribuyente, también al editar
    selectRegimen.value = regimenConfigurado;
    validarRegimen();
    $("#modalLibro").modal("show");
  };

  if (!libro || !libro.con_id) {
    mostrar();
    return;
  }

  fetch(base_url + "libro/regimen/" + libro.con_id)
    .then((res) => res.json())
    .then((d) => {
      regimenConfigurado = d.regimen ? String(d.regimen) : "";
      regimenConfiguradoNombre = d.nombre || "";
    })
    .catch(() => {})
    .finally(mostrar);
}

formLibro.addEventListener("submit", (e) => {
  e.preventDefault();

  if (!document.getElementById("contribuyente").value) {
    swalWithBootstrapButtons.fire("Error!", "Seleccione un contribuyente.", "error");
    return;
  }

  if (validarRegimen()) return;

  fetch(base_url + "libro/guardar", {
    method: "POST",
    body: new FormData(formLibro),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalLibro").modal("hide");
      }
      manejarRespuesta(data, (d) => window.alGuardarLibro && window.alGuardarLibro(d));
    });
});
