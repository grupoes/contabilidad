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

let listaUbigeo = new Choices("#ubigeo", {
  removeItemButton: true,
  searchPlaceholderValue: "Buscar aqui el distrito, provincia o departamento",
  allowHTML: true,
  itemSelectText: "",
});

async function cargarUbigeo() {
  try {
    const response = await fetch(`${base_url}all-ubigeo`);
    if (!response.ok) throw new Error("Error en la respuesta de la API");

    const data = await response.json();

    // Cargar datos en Choices
    listaUbigeo.setChoices(
      data.map((release) => ({
        label: `${release.distrito} - ${release.provincia} - ${release.departamento}`,
        value: release.codigo_ubigeo,
      })),
      "value",
      "label",
      true
    );
  } catch (error) {
    console.error("Error al cargar ubigeo:", error);
    alert("No se pudieron cargar los datos de ubigeo.");
  }
}

cargarUbigeo();

const tableBody = document.getElementById("tableBody");
const btnModal = document.getElementById("btnModal");
const titleModal = document.getElementById("titleModal");

const numeroDocumento = document.getElementById("numeroDocumento");
const nombresApellidos = document.getElementById("nombresApellidos");
const num_colegiatura = document.getElementById("num_colegiatura");
const domicilio = document.getElementById("domicilio");
const idContador = document.getElementById("idContador");
const estado = document.getElementById("estado");

renderContadores();

function renderContadores() {
  fetch(`${base_url}configuracion/render-contadores`)
    .then((res) => res.json())
    .then((data) => {
      viewContadores(data);
    });
}

function viewContadores(data) {
  let html = "";

  data.forEach((contador, index) => {
    html += `
        <tr>
            <td>${index + 1}</td>
            <td>${contador.nombre_apellidos}</td>
            <td>${contador.dni}</td>
            <td>${contador.numero_colegiatura}</td>
            <td>
                ${contador.elegir}
            </td>
            <td>
                <ul class="list-inline me-auto mb-0">
                    ${contador.acciones}
                </ul>
            </td>
        </tr>
        `;
  });

  $($table).DataTable().destroy();

  tableBody.innerHTML = html;

  $($table).DataTable(optionsTableDefault);
}

function elegirContador(id) {
  fetch(`${base_url}configuracion/elegir-contador/${id}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        renderContadores();

        notifier.show("¡Bien hecho!", data.message, "success", "", 2000);
      }
    });
}

btnModal.addEventListener("click", (e) => {
  $("#modalContadores").modal("show");
  titleModal.textContent = "Agregar Contador";
  formContador.reset();
  listaUbigeo.removeActiveItems();
  cargarUbigeo();

  estado.value = 1;
  idContador.value = 0;
});

function editContador(e, id) {
  e.preventDefault();

  $("#modalContadores").modal("show");

  idContador.value = id;

  fetch(`${base_url}configuracion/get-contador/${id}`)
    .then((res) => res.json())
    .then((data) => {
      titleModal.textContent = "Editar Contador";

      numeroDocumento.value = data.dni;
      nombresApellidos.value = data.nombre_apellidos;
      num_colegiatura.value = data.numero_colegiatura;
      domicilio.value = data.domicilio;
      estado.value = data.estado;

      listaUbigeo.setChoiceByValue(data.ubigeo);
    });
}

const formContador = document.getElementById("formContador");

formContador.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formContador);

  fetch(`${base_url}configuracion/save-contador`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        renderContadores();

        $("#modalContadores").modal("hide");

        notifier.show("¡Bien hecho!", data.message, "success", "", 2000);
        return false;
      }

      notifier.show("¡Error!", data.message, "danger", "", 2000);
    });
});

searchDocumento.addEventListener("click", (e) => {
  const numDoc = numeroDocumento.value;

  if (numDoc.length === 8) {
    const getNombres = document.getElementById("getNombres");

    getNombres.textContent = "Obteniendo nombres...";
    nombresApellidos.disabled = true;

    searchDocumento.innerHTML = `
              <div class="spinner-border spinner-border-sm" role="status">
                  <span class="sr-only">Loading...</span>
              </div>
          `;

    numDoc.disabled = true;

    fetch(base_url + "api/dni-ruc/dni/" + numDoc)
      .then((res) => res.json())
      .then((data) => {
        getNombres.textContent = "";
        nombresApellidos.disabled = false;

        searchDocumento.innerHTML = `
                  <i class="fas fa-search"></i>
              `;

        numDoc.disabled = false;

        if (data.respuesta === "error") {
          alert("D.N.I. no fue encontrado");
          return false;
        }

        nombresApellidos.value =
          data.data.nombres +
          " " +
          data.data.ap_paterno +
          " " +
          data.data.ap_materno;
      });
  } else {
    alert("El número del documento debe tener 8 dígitos");
  }
});

function deleteContador(e, id) {
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
        fetch(base_url + "configuracion/delete-contador/" + id)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              renderContadores();
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
