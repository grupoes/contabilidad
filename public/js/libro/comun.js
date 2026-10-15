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

// Preselecciona el régimen de Renta que el contribuyente tiene configurado (se puede cambiar)
function sugerirRegimen() {
  const id = document.getElementById("contribuyente").value;
  document.getElementById("regimen").value = REGIMENES[id] || "";
}

document.getElementById("contribuyente").addEventListener("change", sugerirRegimen);

const tipoLibro = document.getElementById("tipoLibro");
const tipoOtro = document.getElementById("tipoOtro");

tipoLibro.addEventListener("change", () => {
  tipoOtro.style.display = tipoLibro.value === "otro" ? "" : "none";
  tipoOtro.required = tipoLibro.value === "otro";
});

// formLibro.reset() hace que Choices vuelva a su estado inicial y pierda las opciones: se guardan para restaurarlas
const opcionesContribuyente = selectContribuyente._store.choices.map((c) => ({ value: c.value, label: c.label }));

function abrirModalLibro(titulo, libro) {
  formLibro.reset();
  selectContribuyente.setChoices(opcionesContribuyente, "value", "label", true);
  tipoOtro.style.display = "none";
  tipoOtro.required = false;
  selectContribuyente.removeActiveItems();
  selectContribuyente.setChoiceByValue("");
  document.getElementById("idLibro").value = "0";
  document.getElementById("minimo").value = "100";
  document.getElementById("folioInicial").value = "1";
  titleModal.textContent = titulo;

  if (libro) {
    document.getElementById("idLibro").value = libro.lib_id || "0";
    selectContribuyente.setChoiceByValue(String(libro.con_id));
    sugerirRegimen();
    // El régimen guardado en el libro manda sobre el del contribuyente
    if (libro.tri_id) document.getElementById("regimen").value = libro.tri_id;
    if (libro.tli_id) document.getElementById("tipoLibro").value = libro.tli_id;
    document.getElementById("numero").value = libro.lib_numero || "";
    if (libro.lib_sede) document.getElementById("sede").value = libro.lib_sede;
    document.getElementById("notaria").value = libro.lib_notaria || "";
    document.getElementById("fechaLegalizacion").value = libro.lib_fecha_legalizacion || "";
    document.getElementById("kardex").value = libro.lib_kardex || "";
    document.getElementById("stan").value = libro.lib_stan || "";
    document.getElementById("folioInicial").value = libro.lib_folio_inicial || "1";
    document.getElementById("folioFinal").value = libro.lib_folio_final || "";
    document.getElementById("minimo").value = libro.lib_minimo || 100;
    document.getElementById("observacion").value = libro.lib_observacion || "";
  }

  calcularTotal();

  $("#modalLibro").modal("show");
}

formLibro.addEventListener("submit", (e) => {
  e.preventDefault();

  if (!document.getElementById("contribuyente").value) {
    swalWithBootstrapButtons.fire("Error!", "Seleccione un contribuyente.", "error");
    return;
  }

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
