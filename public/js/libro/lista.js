const btnModal = document.getElementById("btnModal");
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

  const data = CLIENTES.filter(
    (c) =>
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
          <b>${esc(c.razon_social)}</b>
          <div class="text-muted">RUC ${esc(c.ruc)} · <span class="text-capitalize">${esc(c.regimen) || "Sin régimen"}</span> · ${c.libros} libro(s)</div>
        </div>
        ${c.alertas ? `<span class="badge rounded-pill bg-warning">${c.alertas} alerta(s)</span>` : '<i class="ti ti-chevron-right"></i>'}
      </a>`
        )
        .join("")
    : `<p class="text-muted">${CLIENTES.length ? "Sin resultados." : "Aún no hay libros registrados. Agrega el primero con «Nuevo libro»."}</p>`;
}

buscarCliente.addEventListener("input", viewClientes);
filtroEstado.addEventListener("change", viewClientes);

if (btnModal) {
  btnModal.addEventListener("click", () => abrirModalLibro("REGISTRAR LIBRO", null));
}
