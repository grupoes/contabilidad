const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  showConfirmButton: true,
  buttonsStyling: false,
});

const tableBody = document.getElementById("tableBody");

const btnModal = document.getElementById("btnModal");
const titleModal = document.getElementById("titleModal");

const formConcepto = document.getElementById("formConcepto");
const idConcepto = document.getElementById("idConcepto");
const nameConcepto = document.getElementById("nameConcepto");
const tipoMovimiento = document.getElementById("tipoMovimiento");

renderConceptos();

function renderConceptos() {
  fetch(base_url + "render-conceptos")
    .then((res) => res.json())
    .then((data) => {
      viewConceptos(data);
    });
}

function viewConceptos(data) {
  let html = "";

  data.forEach((concepto, index) => {
    html += `
        <tr>
            <td>${index + 1}</td>
            <td>${concepto.con_descripcion}</td>
            <td>${concepto.tipo_movimiento_descripcion}</td>
            <td>
                ${concepto.acciones}
            </td>
        </tr>
        `;
  });

  $($table).DataTable().destroy();

  tableBody.innerHTML = html;

  const newcs = $($table).DataTable({
    language: language,
    responsive: true,
    autoWidth: false,
    scrollX: false,
    columnDefs: [
      { targets: "_all", className: "text-wrap" },
    ],
  });

  new $.fn.dataTable.Responsive(newcs);
}

btnModal.addEventListener("click", () => {
  $("#modalConcepto").modal("show");
  titleModal.textContent = "AGREGAR CONCEPTO";

  formConcepto.reset();

  idConcepto.value = "0";
});

formConcepto.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formConcepto);

  fetch(base_url + "concepto/guardar", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        $("#modalConcepto").modal("hide");
        swalWithBootstrapButtons.fire("Muy bien!", data.message, "success");

        renderConceptos();

        return false;
      }

      swalWithBootstrapButtons.fire("Error!", data.message, "error");
    });
});

tableBody.addEventListener("click", (e) => {
  if (e.target.classList.contains("modificar")) {
    titleModal.textContent = "EDITAR CONCEPTO";

    $("#modalConcepto").modal("show");

    const id = e.target.getAttribute("data-id");
    idConcepto.value = id;

    const name = e.target.getAttribute("data-name");
    const tipo = e.target.getAttribute("data-tipo");

    nameConcepto.value = name;
    tipoMovimiento.value = tipo;
  }

  if (e.target.classList.contains("eliminar")) {
    const id = e.target.getAttribute("data-id");

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
        if (result.isConfirmed) {
          fetch(base_url + "concepto/delete/" + id)
            .then((res) => res.json())
            .then((data) => {
              if (data.status === "success") {
                renderConceptos();
                swalWithBootstrapButtons.fire(
                  "Muy bien!",
                  data.message,
                  "success"
                );
                return false;
              }

              swalWithBootstrapButtons.fire("Error!", data.message, "error");
            });
        }
      });
  }
});
