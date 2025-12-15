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
const formMetodo = document.getElementById("formMetodo");
const idMetodo = document.getElementById("idMetodo");

const nameMetodo = document.getElementById("nameMetodo");
const banco = document.getElementById("banco");
const descripcion = document.getElementById("descripcion");

if (btnModal) {
  btnModal.addEventListener("click", () => {
    $("#modalMetodo").modal("show");
    titleModal.textContent = "Agregar Método de Pago";
  });
}

renderMetodos();

function renderMetodos() {
  fetch(base_url + "metodos/all")
    .then((res) => res.json())
    .then((data) => {
      viewMetodos(data);
    });
}

function viewMetodos(data) {
  let html = "";

  data.forEach((metodo, index) => {
    let nombreBanco = "";

    if (metodo.nombre_banco != null) {
      nombreBanco = metodo.nombre_banco;
    }

    html += `
        <tr>
            <td>${index + 1}</td>
            <td>${metodo.metodo}</td>
            <td>${nombreBanco}</td>
            <td>${metodo.descripcion}</td>
            <td>
                ${metodo.acciones}
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

formMetodo.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formMetodo);

  fetch(base_url + "metodo-pago/guardar", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        Swal.fire({
          position: "top-end",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        $("#modalMetodo").modal("hide");

        renderMetodos();

        return false;
      }

      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!",
      });
    });
});

function editarMetodo(e, id) {
  e.preventDefault();

  idMetodo.value = id;
  titleModal.textContent = "Editar Método de Pago";

  $("#modalMetodo").modal("show");

  fetch(base_url + "metodo-pago/get-metodo/" + id)
    .then((res) => res.json())
    .then((data) => {
      nameMetodo.value = data.metodo;
      banco.value = data.id_banco;
      descripcion.value = data.descripcion;
    });
}

function deleteMetodo(e, id) {
  e.preventDefault();

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
        fetch(base_url + "metodo-pago/delete/" + id)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              renderMetodos();
              Swal.fire({
                position: "top-center",
                icon: "success",
                title: data.message,
                showConfirmButton: false,
                timer: 1500,
              });
              return false;
            }

            swalWithBootstrapButtons.fire("Error!", data.message, "error");
          });
      }
    });
}
