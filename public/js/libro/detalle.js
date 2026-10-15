const libroId = document.getElementById("libroId").value;
const formImpresion = document.getElementById("formImpresion");

let LIBRO = null;

cargar();

window.alGuardarLibro = (d) => {
  // "Legalizar siguiente" crea otro libro: se abre su detalle
  if (d.id && String(d.id) !== String(libroId)) {
    window.location.href = base_url + "libros-electronicos/" + d.id;
    return;
  }
  cargar();
};

function cargar() {
  fetch(base_url + "libro/data/" + libroId)
    .then((res) => res.json())
    .then((data) => {
      if (data.status !== "success") return;
      LIBRO = data.libro;
      viewLibro(data.libro);
      viewImpresiones(data.impresiones);
      viewSimulaciones(data.simulaciones);
    });
}

function viewLibro(l) {
  // Volver a la página del cliente dueño del libro
  const volver = base_url + "libros-electronicos/cliente/" + l.con_id;
  document.getElementById("volverCliente").href = volver;
  window.urlCliente = volver;

  const nombre = l.tli_nombre + (l.lib_numero ? " N° " + l.lib_numero : "");

  document.getElementById("tituloLibro").textContent = nombre;
  document.getElementById("clienteLibro").textContent =
    l.razon_social + " — RUC " + l.ruc + " — Régimen: " + (l.regimen || "Sin régimen configurado");

  const badge = document.getElementById("estadoLibro");
  badge.className = "badge bg-" + l.color;
  badge.textContent = l.estado;

  document.getElementById("datosLibro").textContent = [
    l.lib_sede,
    l.lib_notaria && "Notaría: " + l.lib_notaria,
    l.lib_fecha_legalizacion && "Legalizado: " + fechaDMY(l.lib_fecha_legalizacion),
    l.lib_kardex && "Kardex " + l.lib_kardex,
    l.lib_stan && "Stan " + l.lib_stan,
  ].filter(Boolean).join(" · ");

  document.getElementById("foliosLibro").textContent =
    `Folios ${l.lib_folio_inicial} al ${l.lib_folio_final} · Total ${l.lib_total_hojas} hojas · Alerta ≤ ${l.lib_minimo}` +
    (l.lib_observacion ? " · Obs: " + l.lib_observacion : "");

  document.getElementById("auditoriaLibro").textContent = [
    autoria(l.usuario_add, l.lib_fecha_add) && "Registrado por " + autoria(l.usuario_add, l.lib_fecha_add),
    autoria(l.usuario_edit, l.lib_fecha_edit) && "Última edición por " + autoria(l.usuario_edit, l.lib_fecha_edit),
  ].filter(Boolean).join(" — ");

  const barra = document.getElementById("barraLibro");
  barra.className = "progress-bar bg-" + l.color;
  barra.style.width = l.porcentaje + "%";

  document.getElementById("resumenImpresas").innerHTML =
    `<i class="ti ti-printer"></i> <b>Impresas ${l.impresas}</b> &nbsp; <i class="ti ti-file"></i> <b>En blanco ${l.en_blanco}</b>` +
    (l.en_blanco > 0 ? ` (folios ${l.folio_sig} al ${l.lib_folio_final})` : "");

  document.getElementById("resumenSimulado").innerHTML =
    `<span class="text-${l.color}"><i class="ti ti-calculator"></i> Simulado <b>${l.simuladas}</b>` +
    (l.ultima_simulacion ? " hasta " + l.ultima_simulacion : "") +
    ` → quedarían <b>${l.restantes}</b> en blanco${l.proyeccion ? " · " + l.proyeccion : ""}</span>`;
}

function viewImpresiones(data) {
  const btnBorrar = (id) =>
    document.getElementById("btnImpresion") ? `<button class="btn btn-danger btn-sm" data-imp="${id}"><i class="ti ti-trash"></i></button>` : "";

  document.getElementById("bodyImpresiones").innerHTML = data.length
    ? data.map((x) => `
      <tr>
        <td>${x.lim_folio_desde} al ${x.lim_folio_hasta}</td>
        <td>${x.lim_folio_hasta - x.lim_folio_desde + 1}</td>
        <td>${x.lim_periodo_desde ? periodoTexto(x.lim_periodo_desde) + " a " : ""}${periodoTexto(x.lim_periodo_hasta)}</td>
        <td><small class="text-muted">${esc(autoria(x.usuario_add, x.lim_fecha_add))}</small></td>
        <td class="text-end">${btnBorrar(x.lim_id)}</td>
      </tr>`).join("")
    : '<tr><td colspan="5" class="text-muted">Sin impresiones registradas.</td></tr>';
}

function viewSimulaciones(data) {
  const btnBorrar = (id) =>
    document.getElementById("btnSimular") ? `<button class="btn btn-danger btn-sm" data-sim="${id}"><i class="ti ti-trash"></i></button>` : "";

  document.getElementById("bodySimulaciones").innerHTML = data.length
    ? data.map((x) => `
      <tr>
        <td>${esc(x.texto)}</td>
        <td>${x.lsi_hojas} hojas</td>
        <td><small class="text-muted">${esc(autoria(x.usuario_add, x.lsi_fecha_add))}</small></td>
        <td class="text-end">${btnBorrar(x.lsi_id)}</td>
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

const btnImpresion = document.getElementById("btnImpresion");
if (btnImpresion) {
  btnImpresion.addEventListener("click", () => {
    formImpresion.reset();
    document.getElementById("folioDesde").value = LIBRO.folio_sig;
    $("#modalImpresion").modal("show");
  });

  formImpresion.addEventListener("submit", (e) => {
    e.preventDefault();

    const fd = new FormData();
    fd.append("libroId", libroId);
    fd.append("folioDesde", document.getElementById("folioDesde").value);
    fd.append("folioHasta", document.getElementById("folioHasta").value);
    fd.append("periodoDesde", getPeriodo("i1"));
    fd.append("periodoHasta", getPeriodo("i2"));

    fetch(base_url + "libro/impresion/guardar", { method: "POST", body: fd })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") $("#modalImpresion").modal("hide");
        manejarRespuesta(data, cargar);
      });
  });
}

const btnSimular = document.getElementById("btnSimular");
if (btnSimular) {
  btnSimular.addEventListener("click", () => {
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

const btnReponer = document.getElementById("btnReponer");
if (btnReponer) {
  btnReponer.addEventListener("click", () => {
    Swal.fire({
      title: "Reponer hojas",
      input: "number",
      inputAttributes: { min: 1 },
      inputLabel: "Hojas nuevas",
      showCancelButton: true,
      confirmButtonText: "Reponer",
      cancelButtonText: "Cancelar",
      customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-danger" },
      buttonsStyling: false,
    }).then((r) => {
      if (!r.isConfirmed) return;

      const fd = new FormData();
      fd.append("libroId", libroId);
      fd.append("hojas", r.value);

      fetch(base_url + "libro/reponer", { method: "POST", body: fd })
        .then((res) => res.json())
        .then((data) => manejarRespuesta(data, cargar));
    });
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
      lib_sede: LIBRO.lib_sede,
      lib_notaria: LIBRO.lib_notaria,
      lib_minimo: LIBRO.lib_minimo,
    };
    abrirModalLibro("LEGALIZAR SIGUIENTE LIBRO", base);
  });
}

const btnQuitar = document.getElementById("btnQuitar");
if (btnQuitar) {
  btnQuitar.addEventListener("click", () => {
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
