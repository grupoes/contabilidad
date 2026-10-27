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

// ---- Importación desde Excel (plantilla -> validar -> confirmar)
const btnImportar = document.getElementById("btnImportar");

if (btnImportar) {
  const archivo = document.getElementById("archivoImportar");
  const btnValidar = document.getElementById("btnValidarArchivo");
  const btnConfirmar = document.getElementById("btnConfirmarImportar");
  const btnErrores = document.getElementById("btnDescargarErrores");
  const resumen = document.getElementById("resumenImportar");
  const tabla = document.getElementById("tablaImportar");
  const cuerpo = document.getElementById("bodyImportar");

  let tokenImportacion = null;

  const limpiar = () => {
    tokenImportacion = null;
    resumen.style.display = "none";
    tabla.style.display = "none";
    cuerpo.innerHTML = "";
    btnConfirmar.style.display = "none";
    btnErrores.style.display = "none";
  };

  btnImportar.addEventListener("click", () => {
    archivo.value = "";
    limpiar();
    $("#modalImportar").modal("show");
  });

  archivo.addEventListener("change", limpiar);

  btnValidar.addEventListener("click", () => {
    if (!archivo.files.length) {
      swalWithBootstrapButtons.fire("Atención", "Seleccione primero el archivo de Excel.", "warning");
      return;
    }

    const fd = new FormData();
    fd.append("archivo", archivo.files[0]);

    btnValidar.disabled = true;
    btnValidar.textContent = "Validando...";

    fetch(base_url + "libro/importar/previa", { method: "POST", body: fd })
      .then((res) => res.json())
      .then((data) => {
        if (data.status !== "success") {
          limpiar();
          swalWithBootstrapButtons.fire("Error", data.message, "error");
          return;
        }

        tokenImportacion = data.token;
        const r = data.resumen;
        const correctas = r.libros_ok + r.libros_incompletos + r.impresiones_ok;
        const errores = r.libros_error + r.impresiones_error;

        resumen.innerHTML = `
          <div class="alert alert-${errores ? "warning" : "success"} mb-0">
            <b>Libros:</b> ${r.libros_ok} completos, ${r.libros_incompletos} por completar, ${r.libros_error} con error &nbsp;·&nbsp;
            <b>Impresiones:</b> ${r.impresiones_ok} correctas, ${r.impresiones_error} con error.
            ${errores ? "Solo se importarán las filas sin error." : "Ninguna fila tiene error."}
          </div>`;
        resumen.style.display = "";

        cuerpo.innerHTML = data.filas
          .map(
            (f) => `
          <tr>
            <td>${f.tipo === "libro" ? "Libro" : "Impresión"}</td>
            <td>${f.fila}</td>
            <td>${esc(f.cliente || f.ruc)}</td>
            <td>${esc(f.descripcion)}</td>
            <td><span class="badge bg-${f.estado === "ok" ? "success" : f.estado === "incompleto" ? "warning" : "danger"}">${f.estado === "ok" ? "Correcta" : f.estado === "incompleto" ? "Por completar" : "Error"}</span></td>
            <td class="${f.estado === "error" ? "text-danger" : "text-muted"}">${esc(f.mensaje)}${f.faltan ? `<div class="text-warning-emphasis"><b>Faltan:</b> ${esc(f.faltan)}</div>` : ""}</td>
          </tr>`
          )
          .join("");
        tabla.style.display = "";

        btnConfirmar.style.display = correctas ? "" : "none";
        btnErrores.style.display = errores ? "" : "none";
        btnErrores.href = base_url + "libro/importar/errores?token=" + encodeURIComponent(data.token);
        btnConfirmar.dataset.libros = r.libros_ok + r.libros_incompletos;
        btnConfirmar.dataset.impresiones = r.impresiones_ok;
      })
      .catch(() => swalWithBootstrapButtons.fire("Error", "No se pudo validar el archivo.", "error"))
      .finally(() => {
        btnValidar.disabled = false;
        btnValidar.textContent = "Validar archivo";
      });
  });

  btnConfirmar.addEventListener("click", () => {
    if (!tokenImportacion) return;

    swalWithBootstrapButtons
      .fire({
        title: "¿Importar las filas correctas?",
        text: `Se registrarán ${btnConfirmar.dataset.libros} libro(s) y ${btnConfirmar.dataset.impresiones} impresión(es). Los libros por completar quedan marcados hasta que se completen.`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, importar",
        cancelButtonText: "Cancelar",
        reverseButtons: true,
      })
      .then((r) => {
        if (!r.isConfirmed) return;

        const fd = new FormData();
        fd.append("token", tokenImportacion);

        btnConfirmar.disabled = true;

        fetch(base_url + "libro/importar/confirmar", { method: "POST", body: fd })
          .then((res) => res.json())
          .then((data) => {
            if (data.status !== "success") {
              swalWithBootstrapButtons.fire("Error", data.message, "error");
              return;
            }

            const x = data.resumen;
            $("#modalImportar").modal("hide");
            swalWithBootstrapButtons.fire(
              "Importación terminada",
              `Libros: ${x.libros_ok + x.libros_incompletos} importados (${x.libros_incompletos} por completar), ${x.libros_error} omitidos. Impresiones: ${x.impresiones_ok} importadas, ${x.impresiones_error} omitidas.`,
              "success"
            );
            renderClientes();
          })
          .finally(() => (btnConfirmar.disabled = false));
      });
  });
}