const clienteId = document.getElementById("clienteId").value;
const listaLibros = document.getElementById("listaLibros");
const btnModal = document.getElementById("btnModal");

renderLibros();

window.alGuardarLibro = renderLibros;

function renderLibros() {
  fetch(base_url + "render-libros/" + clienteId)
    .then((res) => res.json())
    .then(viewLibros);
}

function viewLibros(data) {
  listaLibros.innerHTML = data.length
    ? data
        .map(
          (l) => `
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border rounded p-3 mb-2">
        <${PERMISO_VER ? `a href="${base_url}libros-electronicos/${l.lib_id}"` : "div"} class="text-reset flex-grow-1">
          <b>${esc(l.tli_nombre)}${l.lib_numero ? " N° " + esc(l.lib_numero) : ""}</b>
          <div class="text-muted">${(l.lib_tipo_hoja === "electronico"
            ? [l.regimen && "Régimen " + l.regimen, "Libro electrónico"]
            : l.lib_faltantes
            ? [l.sede_nombre, l.regimen && "Régimen " + l.regimen, "Faltan: " + l.lib_faltantes]
            : [
                l.sede_nombre,
                l.regimen && "Régimen " + l.regimen,
                l.lib_fecha_legalizacion && "Legalizado " + fechaDMY(l.lib_fecha_legalizacion),
                "Quedan " + l.restantes + " hojas",
              ]
          )
            .filter(Boolean)
            .map(esc)
            .join(" · ")}</div>
        </${PERMISO_VER ? "a" : "div"}>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-${l.color}">${l.estado}</span>
          ${PERMISO_VER ? `<a href="${base_url}libros-electronicos/${l.lib_id}" class="btn btn-primary btn-sm" title="ver"><i class="ti ti-eye f-18"></i></a>` : ""}
          ${PERMISO_EDITAR ? `<button type="button" class="btn btn-info btn-sm modificar" title="editar" data-id="${l.lib_id}"><i class="ti ti-edit-circle f-18"></i></button>` : ""}
          ${PERMISO_ELIMINAR ? `<button type="button" class="btn btn-danger btn-sm eliminar ${l.impresas > 0 ? "opacity-50" : ""}" title="${l.impresas > 0 ? "No se puede eliminar: el libro ya tiene impresiones" : "eliminar"}" data-id="${l.lib_id}" ${l.impresas > 0 ? 'data-bloqueado="1" aria-disabled="true"' : ""}><i class="ti ti-trash f-18"></i></button>` : ""}
        </div>
      </div>`
        )
        .join("")
    : '<p class="text-muted">Este cliente aún no tiene libros. Registra el primero con «Nuevo libro».</p>';
}

if (btnModal) {
  // El contribuyente ya viene elegido: solo se completan los datos del libro
  btnModal.addEventListener("click", () => abrirModalLibro("REGISTRAR LIBRO", { con_id: clienteId }));
}

listaLibros.addEventListener("click", (e) => {
  const btn = e.target.closest("button");
  if (!btn) return;

  const id = btn.getAttribute("data-id");

  if (btn.classList.contains("modificar")) {
    fetch(base_url + "libro/data/" + id)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          abrirModalLibro("EDITAR LIBRO", data.libro);
        }
      });
  }

  if (btn.classList.contains("eliminar")) {
    if (btn.dataset.bloqueado) {
      swalWithBootstrapButtons.fire("No se puede eliminar", "El libro ya tiene impresiones registradas en su historial.", "warning");
      return;
    }

    confirmar("¿Seguro desea eliminarlo?").then((ok) => {
      if (!ok) return;

      fetch(base_url + "libro/delete/" + id)
        .then((res) => res.json())
        .then((data) => manejarRespuesta(data, renderLibros));
    });
  }
});
