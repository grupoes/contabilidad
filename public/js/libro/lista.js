const filtroContribuyente = document.getElementById("filtroContribuyente");
const filtroEstado = document.getElementById("filtroEstado");
const buscarCliente = document.getElementById("buscarCliente");

let CLIENTES = [];

renderClientes();

window.alGuardarLibro = renderClientes;

function renderClientes() {
  fetch(base_url + "render-libros-clientes")
    .then((res) => res.json())
    .then((data) => {
      CLIENTES = data.clientes;
      viewAlertas(data.alertas);
      viewClientes();
    });
}

function viewAlertas(alertas) {
  const card = document.getElementById("cardAlertas");

  card.style.display = alertas.length ? "" : "none";

  document.getElementById("listaAlertas").innerHTML = alertas
    .map(
      (l) => `
      <${PERMISO_VER ? `a href="${base_url}libros-electronicos/${l.lib_id}"` : "div"} class="d-block text-reset border-start border-4 border-${l.color} ps-3 py-1 mb-2">
        <b>${esc(l.razon_social)}</b> — ${esc(l.tli_nombre)}${l.lib_numero ? " N° " + esc(l.lib_numero) : ""}:
        ${l.estado === "Agotado" ? "sin hojas disponibles" : `quedan <b>${l.restantes}</b> hojas en blanco tras la simulación (mínimo ${l.lib_minimo})`}.
        Solicita impresión o compra de hojas.
      </${PERMISO_VER ? "a" : "div"}>`
    )
    .join("");
}

function viewClientes() {
  const q = buscarCliente.value.trim().toLowerCase();
  const estado = filtroEstado.value;
  const estadoCliente = filtroContribuyente.value; // 0 = todos, 1 = activos, 2 = inactivos

  const data = CLIENTES.filter(
    (c) =>
      (estadoCliente === "0" || String(c.estado) === estadoCliente) &&
      (!q || (c.razon_social + " " + c.ruc).toLowerCase().includes(q)) &&
      (!estado || c.estados.includes(estado))
  );

  document.getElementById("totalClientes").textContent = data.length ? data.length + " cliente(s)" : "";

  document.getElementById("listaClientes").innerHTML = data.length
    ? data
        .map(
          (c) => `
      <a href="${base_url}libros-electronicos/cliente/${c.con_id}" class="d-flex justify-content-between align-items-center border rounded p-3 mb-2 text-reset">
        <div>
          <b>${esc(c.razon_social)}</b>${c.estado === 2 ? ' <span class="badge bg-secondary">Inactivo</span>' : ""}
          <div class="text-muted">RUC ${esc(c.ruc)} · <span class="text-capitalize">${esc(c.regimen) || "Sin régimen"}</span> · ${c.libros} libro(s)</div>
        </div>
        <div class="d-flex align-items-center gap-2">
          ${c.libros === 0 ? '<span class="badge rounded-pill bg-danger">Falta configuración</span>' : ""}
          ${c.alertas ? `<span class="badge rounded-pill bg-warning">${c.alertas} alerta(s)</span>` : '<i class="ti ti-chevron-right"></i>'}
        </div>
      </a>`
        )
        .join("")
    : `<p class="text-muted">${CLIENTES.length ? "Sin resultados." : "No hay clientes contables."}</p>`;
}

buscarCliente.addEventListener("input", viewClientes);
filtroEstado.addEventListener("change", viewClientes);
filtroContribuyente.addEventListener("change", viewClientes);
