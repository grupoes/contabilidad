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

const tableBody = document.getElementById("tableBody");
const btnModal = document.getElementById("btnModal");
const titleModal = document.getElementById("titleModal");
const formNotaria = document.getElementById("formNotaria");

const idNotaria = document.getElementById("idNotaria");
const ruc = document.getElementById("ruc");
const razonSocial = document.getElementById("razonSocial");
const estado = document.getElementById("estado");

renderNotarias();

function renderNotarias() {
  fetch(`${base_url}configuracion/render-notarias`)
    .then((res) => res.json())
    .then(viewNotarias);
}

function viewNotarias(data) {
  let html = "";

  data.forEach((n, index) => {
    html += `
        <tr>
            <td>${index + 1}</td>
            <td>${esc(n.not_ruc)}</td>
            <td>${esc(n.not_razon_social)}</td>
            <td><span class="badge bg-${n.not_estado == 1 ? "success" : "secondary"}">${n.not_estado == 1 ? "Activo" : "Inactivo"}</span></td>
            <td class="text-center">${n.acciones}</td>
        </tr>
        `;
  });

  $($table).DataTable().destroy();
  // destroy() deja los <colgroup> anteriores y descuadran el ancho de las columnas
  $($table).find("colgroup").remove();

  tableBody.innerHTML = html;

  $($table).DataTable(optionsTableDefault);
}

if (btnModal) {
  btnModal.addEventListener("click", () => {
    formNotaria.reset();
    idNotaria.value = 0;
    estado.value = 1;
    titleModal.textContent = "Agregar Notaría";

    $("#modalNotaria").modal("show");
  });
}

tableBody.addEventListener("click", (e) => {
  const btn = e.target.closest("button");
  if (!btn) return;

  const id = btn.dataset.id;

  if (btn.classList.contains("editar")) {
    fetch(`${base_url}configuracion/get-notaria/${id}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status !== "success") {
          notifier.show("¡Error!", data.message, "danger", "", 3000);
          return;
        }

        formNotaria.reset();
        idNotaria.value = data.notaria.not_id;
        ruc.value = data.notaria.not_ruc;
        razonSocial.value = data.notaria.not_razon_social;
        estado.value = data.notaria.not_estado;
        titleModal.textContent = "Editar Notaría";

        $("#modalNotaria").modal("show");
      });
  }

  if (btn.classList.contains("eliminar")) {
    swalWithBootstrapButtons
      .fire({
        title: "¿Seguro desea eliminarlo?",
        text: "¡No podrá revertir después!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar!",
        cancelButtonText: "No, cancelar!",
        reverseButtons: true,
      })
      .then((result) => {
        if (!result.isConfirmed) return;

        fetch(`${base_url}configuracion/delete-notaria/${id}`)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              renderNotarias();
              notifier.show("¡Bien hecho!", data.message, "success", "", 3000);
              return;
            }

            swalWithBootstrapButtons.fire("Error!", data.message, "error");
          });
      });
  }
});

// Busca el RUC en la API (SUNAT) y completa la razón social
const searchRuc = document.getElementById("searchRuc");
const getRazonSocial = document.getElementById("getRazonSocial");

function buscarRuc() {
  const numero = ruc.value.trim();

  if (!/^\d{11}$/.test(numero)) {
    notifier.show("¡Atención!", "El RUC debe tener 11 dígitos", "warning", "", 3500);
    return;
  }

  getRazonSocial.textContent = "Obteniendo razón social...";
  searchRuc.disabled = true;
  ruc.disabled = true;
  searchRuc.innerHTML = `<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>`;

  const terminar = () => {
    getRazonSocial.textContent = "";
    searchRuc.disabled = false;
    ruc.disabled = false;
    searchRuc.innerHTML = `<i class="fas fa-search"></i>`;
  };

  fetch(`${base_url}api/dni-ruc/ruc/${numero}`)
    .then((res) => res.json())
    .then((data) => {
      terminar();

      if (data.respuesta === "error" || !data.data || !data.data.razon_social) {
        notifier.show("¡Error!", "El R.U.C. no fue encontrado", "danger", "", 3500);
        return;
      }

      razonSocial.value = data.data.razon_social;
    })
    .catch(() => {
      terminar();
      notifier.show("¡Error!", "No se pudo consultar el R.U.C. Ingrese la razón social manualmente.", "danger", "", 4000);
    });
}

searchRuc.addEventListener("click", buscarRuc);

// Enter en el campo RUC busca en lugar de enviar el formulario
ruc.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    buscarRuc();
  }
});

formNotaria.addEventListener("submit", (e) => {
  e.preventDefault();

  fetch(`${base_url}configuracion/save-notaria`, {
    method: "POST",
    body: new FormData(formNotaria),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        renderNotarias();
        $("#modalNotaria").modal("hide");
        notifier.show("¡Bien hecho!", data.message, "success", "", 3000);
        return;
      }

      notifier.show("¡Error!", data.message, "danger", "", 3000);
    });
});
