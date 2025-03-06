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

const btnModal = document.getElementById("btnModal");
const searchDocumento = document.getElementById("searchDocumento");
const numeroDocumento = document.getElementById("numeroDocumento");
const nombres = document.getElementById("nombres");
const apellidos = document.getElementById("apellidos");
const fechaNacimiento = document.getElementById("fechaNacimiento");
const iduser = document.getElementById("iduser");

const contentBody = document.getElementById("contentBody");
const formDatos = document.getElementById("formDatos");

const staCorreo = document.getElementById("staCorreo");
const staUser = document.getElementById("staUser");

const titleModal = document.getElementById("titleModal");

btnModal.addEventListener("click", (e) => {
  e.preventDefault();

  formDatos.reset();

  $("#modalAddEdit").modal("show");
  titleModal.textContent = "Agregar usuario";
  iduser.value = "0";
  staCorreo.value = "0";
  staUser.value = "0";

  const rutaUser = base_url + "assets/images/user/avatar-2.jpg";

  path.value = rutaUser;

  const preview = document.getElementById("preview");
  preview.src = rutaUser;
  preview.style.display = "block";
});

document.getElementById("foto").addEventListener("change", function (event) {
  const file = event.target.files[0];
  const preview = document.getElementById("preview");

  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = "block"; // Muestra el elemento img
    };
    reader.readAsDataURL(file); // Lee el archivo como una URL de datos
  } else {
    preview.src = "";
    preview.style.display = "none"; // Oculta el elemento img si no hay archivo
  }
});

numeroDocumento.addEventListener("input", (event) => {
  // Reemplaza cualquier carácter no numérico con una cadena vacía
  event.target.value = event.target.value.replace(/[^0-9]/g, "");
});

searchDocumento.addEventListener("click", (e) => {
  const numDoc = numeroDocumento.value;

  if (numDoc.length === 8) {
    const getNombres = document.getElementById("getNombres");
    const getApellidos = document.getElementById("getApellidos");
    const getHappy = document.getElementById("getHappy");

    getNombres.textContent = "Obteniendo nombres...";
    nombres.disabled = true;

    getApellidos.textContent = "Obteniendo apellidos...";
    apellidos.disabled = true;

    getHappy.textContent = "Obteniendo fecha de nacimiento...";
    fechaNacimiento.disabled = true;

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
        nombres.disabled = false;

        getApellidos.textContent = "";
        apellidos.disabled = false;

        getHappy.textContent = "";
        fechaNacimiento.disabled = false;

        searchDocumento.innerHTML = `
                <i class="fas fa-search"></i>
            `;

        numDoc.disabled = false;

        if (data.respuesta === "error") {
          alert("D.N.I. no fue encontrado");
          return false;
        }

        nombres.value = data.data.nombres;
        apellidos.value = data.data.ap_paterno + " " + data.data.ap_materno;

        if (data.data.fecha_nacimiento) {
          const fecha_n = data.data.fecha_nacimiento;
          const partes = fecha_n.split("/");
          const fechaConvertida = `${partes[2]}-${partes[1]}-${partes[0]}`;

          fechaNacimiento.value = fechaConvertida;
        } else {
          fechaNacimiento.value = "";
        }
      });
  } else {
    alert("El número del documento debe tener 8 dígitos");
  }
});

formDatos.addEventListener("submit", async function (e) {
  e.preventDefault();

  let accion = iduser.value === "0" ? "Guardando..." : "Editando...";
  let accionEnd = iduser.value === "0" ? "Guardar" : "Editar";

  spinnerButtonForm(accion);

  const formData = new FormData(this);

  const response = await fetch(base_url + "save-user", {
    method: "POST",
    body: formData,
  });

  const result = await response.json();

  hideSpinnerButtonForm(accionEnd);

  if (result.status === "success") {
    $("#modalAddEdit").modal("hide");
    notifier.show("¡Bien hecho!", result.message, "success", "", 4000);
    renderShowUsers();
  } else {
    notifier.show("¡Error!", result.message, "danger", "", 4000);
  }
});

renderShowUsers();

function renderShowUsers() {
  fetch(base_url + "all-users")
    .then((res) => res.json())
    .then((data) => {
      viewUsers(data);
    });
}

function viewUsers(data) {
  let html = "";

  data.forEach((user, i) => {
    html += `
        <tr>
            <td>${i + 1}</td>
            <td style="width: 250px;">
                <h6 class="mb-1"><a href="">${user.numero_documento}</a></h6>
                <p class="text-muted f-14 mb-0">
                    ${user.nombres} ${user.apellidos}
                </p>
            </td>
            <td>${user.telefono}</td>
            <td>
                ${user.correo}
            </td>
            <td>
                ${user.direccion}
            </td>
            <td>
                ${user.nombre_perfil}
            </td>
            <td class="text-center">
                <a href="#" class="avtar avtar-s btn-link-info btn-pc-default" title="Editar" onclick="editUser(event, ${
                  user.id
                })"><i class="ti ti-edit f-20"></i></a>
                <a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" title="Eliminar" onclick="deleteUser(event, ${
                  user.id
                })"><i class="ti ti-trash f-20"></i></a>
            </td>
        </tr>
        `;
  });

  $($table).DataTable().destroy();

  contentBody.innerHTML = html;

  const newcs = $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);
}

document
  .getElementById("togglePassword")
  .addEventListener("click", function () {
    var passwordInput = document.getElementById("password");
    var icon = this.querySelector("i");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      passwordInput.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  });

function editUser(e, id) {
  e.preventDefault();
  $("#modalAddEdit").modal("show");
  titleModal.textContent = "Editar usuario";
  iduser.value = id;
  staCorreo.value = "1";
  staUser.value = "1";

  fetch(base_url + "get-user/" + id)
    .then((res) => res.json())
    .then((data) => {
      numeroDocumento.value = data.numero_documento;
      nombres.value = data.nombres;
      apellidos.value = data.apellidos;
      fechaNacimiento.value = data.fecha_nacimiento;
      celular.value = data.telefono;
      correo.value = data.correo;
      direccion.value = data.direccion;
      perfil.value = data.perfil_id;
      numeroCuenta.value = data.numero_cuenta;
      sede.value = data.sede_id;
      username.value = data.username;

      const clave = data.alias;

      password.value = clave.slice(5);

      const preview = document.getElementById("preview");
      preview.src = data.path;
      preview.style.display = "block";

      path.value = data.path;
    });
}

function deleteUser(e, id) {
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
        fetch(base_url + "user/delete/" + id)
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              renderShowUsers();
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
