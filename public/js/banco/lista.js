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
const formBanco = document.getElementById("formBanco");
const idBanco = document.getElementById("idBanco");

const nameBanco = document.getElementById("nameBanco");
const titular = document.getElementById("titular");
const numeroCuenta = document.getElementById("numeroCuenta");
const moneda = document.getElementById("moneda");
const saldo_inicial = document.getElementById("saldo_inicial");

if (btnModal) {
  btnModal.addEventListener("click", () => {
    $("#modalBancos").modal("show");
    titleModal.textContent = "Agregar Banco";
  });
}

renderBancos();

function renderBancos() {
  fetch(base_url + "bancos/all")
    .then((res) => res.json())
    .then((data) => {
      viewBancos(data);
    });
}

function viewBancos(data) {
  let html = "";

  data.forEach((banco, index) => {
    html += `
        <tr>
            <td>${index + 1}</td>
            <td>${banco.nombre_banco}</td>
            <td>${banco.moneda}</td>
            <td>${banco.nombre_titular}</td>
            <td>${banco.numero_cuenta}</td>
            <td>${banco.saldo_inicial}</td>
            <td>
                <ul class="list-inline me-auto mb-0">
                    ${banco.acciones}
                </ul>
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

formBanco.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formBanco);

  fetch(base_url + "banco/guardar", {
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

        $("#modalBancos").modal("hide");

        renderBancos();

        return false;
      }

      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!",
      });
    });
});

function editarBanco(e, id) {
  e.preventDefault();

  idBanco.value = id;
  titleModal.textContent = "Editar Banco";

  $("#modalBancos").modal("show");

  fetch(base_url + "banco/get-banco/" + id)
    .then((res) => res.json())
    .then((data) => {
      nameBanco.value = data.nombre_banco;
      titular.value = data.nombre_titular;
      numeroCuenta.value = data.numero_cuenta;
      moneda.value = data.moneda;
      saldo_inicial.value = data.saldo_inicial;
    });
}

function deleteBanco(e, id) {
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
        fetch(base_url + "banco/delete/" + id)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              renderBancos();
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
