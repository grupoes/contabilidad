const libroId = document.getElementById("libroId").value;
const formImpresion = document.getElementById("formImpresion");

let LIBRO = null;
let IMPRESIONES = [];
let SIMULACIONES = [];
let BLOQUEO = null;

cargar();

window.alGuardarLibro = (d) => {
  // "Legalizar siguiente" crea otro libro: se abre su detalle
  if (d.id && String(d.id) !== String(libroId)) {
    window.location.href = base_url + "libros-electronicos/" + d.id;
    return;
  }
  cargar();
};

// sugerir = false evita mover el periodo del simulador (p. ej. al agregar una simulación)
function cargar(sugerir = true) {
  fetch(base_url + "libro/data/" + libroId)
    .then((res) => res.json())
    .then((data) => {
      if (data.status !== "success") return;
      LIBRO = data.libro;
      IMPRESIONES = data.impresiones;
      SIMULACIONES = data.simulaciones;
      BLOQUEO = data.bloqueo;
      if (sugerir !== false) sugerirPeriodoSimulador();
      else validarSimulacion();
      viewLibro(data.libro);
      actualizarMaximoSimulador();
      viewImpresiones(data.impresiones);
      viewSimulaciones(data.simulaciones);
      aplicarBloqueo();
      aplicarQuitarLibro();
    });
}

// El simulador parte del último mes (y año) de las simulaciones registradas; si no hay,
// del último periodo del historial de impresiones. Nunca antes de la última impresión (no se puede registrar ahí).
function sugerirPeriodoSimulador() {
  const mes = document.getElementById("simMes");
  const ultimaSimulacion = SIMULACIONES.reduce((max, s) => (s.lsi_periodo > max ? s.lsi_periodo : max), "");
  const ultimaImpresion = ultimoPeriodoImpreso();
  const periodo = ultimaSimulacion > ultimaImpresion ? ultimaSimulacion : ultimaImpresion;

  if (!mes || !periodo) return;

  const [anio, m] = periodo.split("-");
  mes.value = m;
  document.getElementById("simAnio").value = anio;
  validarSimulacion();
}

// Mientras el libro anterior de la serie tenga hojas en blanco, este libro no puede imprimir ni simular
function aplicarBloqueo() {
  const aviso = document.getElementById("avisoBloqueo");
  const bloqueado = !!BLOQUEO;

  if (bloqueado) {
    const enlace = `<a href="${base_url}libros-electronicos/${BLOQUEO.lib_id}">${esc(BLOQUEO.nombre)}</a>`;
    document.getElementById("textoBloqueo").innerHTML =
      `El libro anterior (${enlace}) todavía tiene <b>${BLOQUEO.en_blanco}</b> hojas en blanco. ` +
      "Mientras tanto no se puede registrar impresiones ni simular en este libro.";
  }

  aviso.style.display = bloqueado ? "" : "none";

  ["btnImpresion", "btnSimular", "simHojas", "simMes", "simAnio"].forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.disabled = bloqueado;
    el.title = bloqueado ? "Bloqueado: el libro anterior aún tiene hojas en blanco" : "";
  });
}

// Un libro con historial de impresiones no se puede quitar
function aplicarQuitarLibro() {
  const btn = document.getElementById("btnQuitar");
  if (!btn) return;

  // Se ve atenuado pero responde al clic con un aviso (no se usa disabled para poder mostrarlo)
  const conImpresiones = IMPRESIONES.length > 0;
  btn.classList.toggle("opacity-50", conImpresiones);
  btn.setAttribute("aria-disabled", conImpresiones ? "true" : "false");
  btn.title = conImpresiones ? "No se puede quitar: el libro ya tiene impresiones registradas" : "";
}

// Muestra cuántas hojas se pueden simular como máximo (las que aún están disponibles)
function actualizarMaximoSimulador() {
  const campo = document.getElementById("simHojas");
  if (!campo || !LIBRO) return;

  campo.max = LIBRO.restantes;
  campo.placeholder = `Hojas a imprimir (máx. ${LIBRO.restantes})`;
  validarSimulacion();
}

function viewLibro(l) {
  // Volver a la página del cliente dueño del libro
  const volver = base_url + "libros-electronicos/cliente/" + l.con_id;
  document.getElementById("volverCliente").href = volver;
  window.urlCliente = volver;

  const nombre = l.tli_nombre + (l.lib_numero ? " N° " + l.lib_numero : "");

  document.getElementById("tituloLibro").textContent = nombre;
  document.getElementById("clienteLibro").textContent = l.razon_social;
  document.getElementById("rucLibro").textContent = "RUC " + l.ruc;
  document.getElementById("regimenLibro").textContent = "Régimen " + (l.regimen || "sin configurar");

  const badge = document.getElementById("estadoLibro");
  badge.className = "badge fs-6 bg-" + l.color;
  badge.textContent = l.estado;

  // Datos del libro: etiqueta pequeña arriba y valor debajo
  const dato = (etiqueta, valor, col) => `
    <div class="${col}">
      <p class="small text-muted mb-1">${etiqueta}</p>
      <p class="mb-0">${esc(valor) || "—"}</p>
    </div>`;

  document.getElementById("datosLibro").innerHTML =
    dato("Sede", l.sede_nombre, "col-6 col-md-3") +
    dato("Notaría", l.notaria_nombre, "col-12 col-md-6") +
    dato("Legalizado", fechaDMY(l.lib_fecha_legalizacion), "col-6 col-md-3") +
    dato("Kardex", l.lib_kardex, "col-6 col-md-3") +
    dato("Stan", l.stan_nombre, "col-6 col-md-3") +
    dato("Folios", `${l.lib_folio_inicial} al ${l.lib_folio_final}`, "col-6 col-md-3") +
    dato("Alerta desde", `${l.lib_minimo} hojas`, "col-6 col-md-3");

  document.getElementById("auditoriaLibro").textContent = [
    autoria(l.usuario_add, l.lib_fecha_add) && "Registrado por " + autoria(l.usuario_add, l.lib_fecha_add),
    autoria(l.usuario_edit, l.lib_fecha_edit) && "Última edición por " + autoria(l.usuario_edit, l.lib_fecha_edit),
  ].filter(Boolean).join(" — ");

  // Hojas: barra de avance y tres tarjetas
  document.getElementById("usoHojas").textContent =
    `${Number(l.impresas) + Number(l.simuladas)} de ${l.lib_total_hojas} hojas usadas`;

  const barra = document.getElementById("barraLibro");
  barra.className = "progress-bar bg-" + l.color;
  barra.style.width = l.porcentaje + "%";

  document.getElementById("tarjetasHojas").innerHTML = `
    <div class="col-12 col-md-4">
      <div class="bg-light rounded p-3 h-100">
        <p class="small text-muted mb-1"><i class="ti ti-printer"></i> Impresas</p>
        <p class="fs-3 fw-medium mb-0">${l.impresas}</p>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="bg-light rounded p-3 h-100">
        <p class="small text-muted mb-1"><i class="ti ti-file"></i> En blanco</p>
        <p class="fs-3 fw-medium mb-0">${l.en_blanco}</p>
        ${l.en_blanco > 0 ? `<p class="small text-muted mb-0">folios ${l.folio_sig} al ${l.lib_folio_final}</p>` : ""}
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="bg-light-${l.color} text-${l.color} rounded p-3 h-100">
        <p class="small mb-1"><i class="ti ti-calculator"></i> Simulado: ${l.simuladas} hojas${l.ultima_simulacion ? " hasta " + esc(l.ultima_simulacion) : ""}</p>
        <p class="fs-3 fw-medium mb-0">${l.restantes}</p>
        <p class="small mb-0">quedarían en blanco</p>
        ${l.proyeccion ? `<p class="small mt-2 mb-0">${esc(l.proyeccion)}</p>` : ""}
      </div>
    </div>`;
}
function viewImpresiones(data) {
  const btnBorrar = (id) =>
    document.getElementById("btnImpresion") ? `<button class="btn btn-danger btn-sm" data-imp="${id}"><i class="ti ti-trash"></i></button>` : "";

  // Solo la última impresión del historial se puede eliminar
  document.getElementById("bodyImpresiones").innerHTML = data.length
    ? data.map((x, i) => `
      <tr>
        <td>${x.lim_folio_desde} al ${x.lim_folio_hasta}</td>
        <td>${x.lim_folio_hasta - x.lim_folio_desde + 1}</td>
        <td>${x.lim_periodo_desde ? periodoTexto(x.lim_periodo_desde) + " a " : ""}${periodoTexto(x.lim_periodo_hasta)}</td>
        <td>${esc(x.lim_observacion) || '<span class="text-muted">—</span>'}</td>
        <td><small class="text-muted">${esc(autoria(x.usuario_add, x.lim_fecha_add))}</small></td>
        <td class="text-end">${i === data.length - 1 ? btnBorrar(x.lim_id) : ""}</td>
      </tr>`).join("")
    : '<tr><td colspan="6" class="text-muted">Sin impresiones registradas.</td></tr>';
}

function viewSimulaciones(data) {
  const btnBorrar = (id) =>
    document.getElementById("btnSimular") ? `<button class="btn btn-danger btn-sm" data-sim="${id}"><i class="ti ti-trash"></i></button>` : "";

  // El servidor entrega la más reciente primero; se muestran en orden ascendente (de la más antigua a la más reciente)
  // y solo la última de la lista (la más reciente) se puede eliminar
  const ascendente = [...data].reverse();

  document.getElementById("bodySimulaciones").innerHTML = ascendente.length
    ? ascendente.map((x, i) => `
      <tr>
        <td>${esc(x.texto)}</td>
        <td>${x.lsi_hojas} hojas</td>
        <td><small class="text-muted">${esc(autoria(x.usuario_add, x.lsi_fecha_add))}</small></td>
        <td class="text-end">${i === ascendente.length - 1 ? btnBorrar(x.lsi_id) : ""}</td>
      </tr>`).join("")
    : '<tr><td colspan="4" class="text-muted">Sin simulaciones.</td></tr>';
}

function borrar(url, titulo) {
  confirmar(titulo).then((ok) => {
    if (!ok) return;

    fetch(base_url + url)
      .then((res) => res.json())
      .then((data) => manejarRespuesta(data, cargar));
  });
}

document.getElementById("bodyImpresiones").addEventListener("click", (e) => {
  const b = e.target.closest("button[data-imp]");
  if (b) borrar("libro/impresion/delete/" + b.dataset.imp, "¿Eliminar esta impresión?");
});

document.getElementById("bodySimulaciones").addEventListener("click", (e) => {
  const b = e.target.closest("button[data-sim]");
  if (b) borrar("libro/simulacion/delete/" + b.dataset.sim, "¿Eliminar esta simulación?");
});

// Periodo de la última impresión que lo tenga (mes y año): ninguna impresión nueva puede ir antes
function ultimoPeriodoImpreso() {
  const ultima = [...IMPRESIONES].reverse().find((x) => x.lim_periodo_hasta || x.lim_periodo_desde);
  return ultima ? ultima.lim_periodo_hasta || ultima.lim_periodo_desde : "";
}

// Devuelve el mensaje de error del periodo elegido (vacío si es válido) y lo muestra bajo los selectores
function validarPeriodoImpresion() {
  const p1 = getPeriodo("i1");
  const p2 = getPeriodo("i2");
  const ultimo = ultimoPeriodoImpreso();
  const inicio = p1 || p2;
  let msg = "";

  if (p1 && p2 && p2 < p1) {
    msg = "El periodo hasta no puede ser anterior al periodo desde.";
  } else if (ultimo && inicio && inicio < ultimo) {
    msg = `El periodo no puede ser anterior a ${periodoTexto(ultimo)}, donde quedó la última impresión.`;
  }

  const aviso = document.getElementById("avisoPeriodoImpresion");
  aviso.textContent = msg;
  aviso.style.display = msg ? "" : "none";

  return msg;
}

["i1Mes", "i1Anio", "i2Mes", "i2Anio"].forEach((id) => {
  document.getElementById(id).addEventListener("input", validarPeriodoImpresion);
  document.getElementById(id).addEventListener("change", validarPeriodoImpresion);
});

const btnImpresion = document.getElementById("btnImpresion");
if (btnImpresion) {
  btnImpresion.addEventListener("click", () => {
    formImpresion.reset();
    document.getElementById("folioDesde").value = LIBRO.folio_sig;

    // Periodo desde: donde quedó la última impresión (mes y año). Sin impresiones con periodo, queda vacío.
    // IMPRESIONES viene ordenado por folio; se toma la más reciente que tenga periodo.
    const ultima = [...IMPRESIONES].reverse().find((x) => x.lim_periodo_hasta || x.lim_periodo_desde);
    if (ultima) {
      const [anio, mes] = (ultima.lim_periodo_hasta || ultima.lim_periodo_desde).split("-");
      document.getElementById("i1Mes").value = mes;
      document.getElementById("i1Anio").value = anio;
    }

    validarPeriodoImpresion();
    $("#modalImpresion").modal("show");
  });

  formImpresion.addEventListener("submit", (e) => {
    e.preventDefault();

    if (validarPeriodoImpresion()) return;

    const fd = new FormData();
    fd.append("libroId", libroId);
    fd.append("folioDesde", document.getElementById("folioDesde").value);
    fd.append("folioHasta", document.getElementById("folioHasta").value);
    fd.append("periodoDesde", getPeriodo("i1"));
    fd.append("periodoHasta", getPeriodo("i2"));
    fd.append("observacion", document.getElementById("observacionImpresion").value);

    fetch(base_url + "libro/impresion/guardar", { method: "POST", body: fd })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") $("#modalImpresion").modal("hide");
        manejarRespuesta(data, cargar);
      });
  });
}

// Mensaje de error si el periodo del simulador es anterior al de la última impresión (vacío si es válido)
function validarSimulacion() {
  const aviso = document.getElementById("avisoPeriodoSimulacion");
  if (!aviso) return "";

  const periodo = getPeriodo("sim");
  const ultimo = ultimoPeriodoImpreso();
  let msg = "";

  if (ultimo && periodo && periodo < ultimo) {
    msg = `El periodo no puede ser anterior a ${periodoTexto(ultimo)}, donde quedó la última impresión.`;
  }

  // Las hojas a simular no pueden superar las que quedan disponibles (total - impresas - ya simuladas)
  const hojas = parseInt(document.getElementById("simHojas").value, 10);
  if (!msg && LIBRO && hojas > LIBRO.restantes) {
    msg = LIBRO.restantes > 0
      ? `Solo quedan ${LIBRO.restantes} hojas disponibles para simular.`
      : "No quedan hojas disponibles para simular.";
  }

  aviso.textContent = msg;
  aviso.style.display = msg ? "" : "none";

  return msg;
}

["simMes", "simAnio", "simHojas"].forEach((id) => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener("input", validarSimulacion);
  el.addEventListener("change", validarSimulacion);
});

const btnSimular = document.getElementById("btnSimular");
if (btnSimular) {
  btnSimular.addEventListener("click", () => {
    if (validarSimulacion()) return;

    const fd = new FormData();
    fd.append("libroId", libroId);
    fd.append("periodo", getPeriodo("sim"));
    fd.append("hojas", document.getElementById("simHojas").value);

    fetch(base_url + "libro/simulacion/guardar", { method: "POST", body: fd })
      .then((res) => res.json())
      .then((data) =>
        manejarRespuesta(data, () => {
          document.getElementById("simHojas").value = "";
          cargar();
        })
      );
  });
}

const btnSiguiente = document.getElementById("btnSiguiente");
if (btnSiguiente) {
  btnSiguiente.addEventListener("click", () => {
    // Copia los datos del libro actual como base del nuevo libro legalizado
    const base = {
      con_id: LIBRO.con_id,
      tli_id: LIBRO.tli_id,
      tri_id: LIBRO.tri_id,
      lib_numero: LIBRO.lib_numero && !isNaN(+LIBRO.lib_numero) ? String(+LIBRO.lib_numero + 1) : "",
      sede_id: LIBRO.sede_id,
      lib_sede: LIBRO.lib_sede,
      not_id: LIBRO.not_id,
      notaria_nombre: LIBRO.notaria_nombre,
      stan_id: LIBRO.stan_id,
      lib_minimo: LIBRO.lib_minimo,
    };
    abrirModalLibro("LEGALIZAR SIGUIENTE LIBRO", base);
  });
}

const btnQuitar = document.getElementById("btnQuitar");
if (btnQuitar) {
  btnQuitar.addEventListener("click", () => {
    if (IMPRESIONES.length > 0) {
      swalWithBootstrapButtons.fire("No se puede eliminar", "El libro ya tiene impresiones registradas en su historial.", "warning");
      return;
    }

    confirmar("¿Seguro desea quitar este libro?").then((ok) => {
      if (!ok) return;

      fetch(base_url + "libro/delete/" + libroId)
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            window.location.href = window.urlCliente || base_url + "libros-electronicos";
            return;
          }
          manejarRespuesta(data);
        });
    });
  });
}
