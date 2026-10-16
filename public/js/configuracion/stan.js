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
const formStan = document.getElementById("formStan");

const idStan = document.getElementById("idStan");
const sede = document.getElementById("sede");
const numero = document.getElementById("numero");
const estado = document.getElementById("estado");

renderStans();

function renderStans() {
  fetch(`${base_url}configuracion/render-stans`)
    .then((res) => res.json())
    .then(viewStans);
}

function viewStans(data) {
  let html = "";

  data.forEach((s, index) => {
    html += `
        <tr>
            <td>${index + 1}</td>
            <td>${esc(s.sede_nombre)}</td>
            <td>${esc(s.stan_numero)}</td>
            <td><span class="badge bg-${s.stan_estado == 1 ? "success" : "secondary"}">${s.stan_estado == 1 ? "Activo" : "Inactivo"}</span></td>
            <td class="text-center">${s.acciones}</td>
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
    formStan.reset();
    idStan.value = 0;
    estado.value = 1;
    titleModal.textContent = "Agregar Stan";

    $("#modalStan").modal("show");
  });
}

tableBody.addEventListener("click", (e) => {
  const btn = e.target.closest("button");
  if (!btn) return;

  const id = btn.dataset.id;

  if (btn.classList.contains("editar")) {
    fetch(`${base_url}configuracion/get-stan/${id}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status !== "success") {
          notifier.show("¡Error!", data.message, "danger", "", 3000);
          return;
        }

        formStan.reset();
        idStan.value = data.stan.stan_id;
        sede.value = data.stan.sede_id || [...sede.options].find((o) => o.text.trim().toLowerCase() === String(data.stan.stan_sede).trim().toLowerCase())?.value || "";
        numero.value = data.stan.stan_numero;
        estado.value = data.stan.stan_estado;
        titleModal.textContent = "Editar Stan";

        $("#modalStan").modal("show");
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

        fetch(`${base_url}configuracion/delete-stan/${id}`)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              renderStans();
              notifier.show("¡Bien hecho!", data.message, "success", "", 3000);
              return;
            }

            swalWithBootstrapButtons.fire("Error!", data.message, "error");
          });
      });
  }
});

formStan.addEventListener("submit", (e) => {
  e.preventDefault();

  fetch(`${base_url}configuracion/save-stan`, {
    method: "POST",
    body: new FormData(formStan),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        renderStans();
        $("#modalStan").modal("hide");
        notifier.show("¡Bien hecho!", data.message, "success", "", 3000);
        return;
      }

      notifier.show("¡Error!", data.message, "danger", "", 3000);
    });
});
