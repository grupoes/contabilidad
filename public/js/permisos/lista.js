document.addEventListener("DOMContentLoaded", function () {
  let radios = document.querySelectorAll(".perfil-radio");
  let perfilInfo = document.getElementById("perfilInfo");
  let perfilesCard = document.getElementById("perfilesCard");
  let volverBtn = document.getElementById("volverLista");
  let infoTexto = document.getElementById("infoTexto");

  let btnGuardar = document.getElementById("btnGuardar");

  radios.forEach((radio) => {
    radio.addEventListener("change", function (e) {
      const idperfil = e.target.value;

      if (window.innerWidth < 576) {
        // Modo móvil
        perfilesCard.classList.add("d-none"); // Oculta la lista de perfiles
        perfilInfo.classList.remove("d-none"); // Muestra la info del perfil
        infoTexto.innerText =
          "Información de: " + this.nextElementSibling.innerText;
      }

      fetch(base_url + "permisos-perfil/" + idperfil)
        .then((response) => response.json())
        .then((data) => {
          titleProfile.innerText = data.perfil;
          perfil_id.value = data.idperfil;
          viewPermisos(data);

          btnGuardar.removeAttribute("hidden");
        });
    });
  });

  // Botón para volver a la lista de perfiles en móvil
  volverBtn.addEventListener("click", function () {
    perfilesCard.classList.remove("d-none");
    perfilInfo.classList.add("d-none");
  });
});

/*Sortable.create(modulo, {
  group: "modulo",
  animation: 100,
});*/

//cargar los perfiles

const listProfiles = document.getElementById("listProfiles");

loadProfile();

function loadProfile() {
  fetch(base_url + "perfiles")
    .then((response) => response.json())
    .then((data) => {
      let html = "";

      data.forEach((perfil) => {
        let detalle = "";

        if (perfil.id == 1 || perfil.id == 2 || perfil.id == 3) {
          detalle = "";
        } else {
          detalle = `
            <div class="dropdown">
                <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="#" onclick="editarPerfil(event, ${perfil.id}, '${perfil.nombre_perfil}')"> <i class="fas fa-pencil-alt"></i> Editar</a>
                    <a class="dropdown-item" href="#" onclick="eliminarPerfil(event,${perfil.id})"> <i class="fas fa-trash"></i> Eliminar</a>
                </div>
            </div>
          `;
        }

        html += `
          <div class="d-flex align-items-center justify-content-between">
            <div class="form-check mb-3">
                <input class="form-check-input perfil-radio" onclick="permisos_details(${perfil.id})" type="radio" name="perfil" id="perf${perfil.id}" value="${perfil.id}">
                <label class="form-check-label" for="perf${perfil.id}">${perfil.nombre_perfil}</label>
            </div>
            ${detalle}
          </div>
        `;
      });

      listProfiles.innerHTML = html;
    });
}

const listPermisos = document.getElementById("listPermisos");

function viewPermisos(data) {
  let html = "";

  const modulos = data.modulos;

  modulos.forEach((modulo) => {
    let hijos = modulo.hijos;

    let htmlHijos = "";

    let cantidadHijos = hijos.length;
    let contPermisos = 0;

    hijos.forEach((hij) => {
      let acciones = hij.acciones;

      let htmlAcciones = "";

      if (acciones.length > 0) {
        htmlAcciones += `<ul class="list-inline mx-1">`;

        acciones.forEach((accion) => {
          let checked = accion.permiso == 1 ? "checked" : "";

          htmlAcciones += `
            <li class="list-inline-item ms-4">
              <input class="form-check-input" type="checkbox" id="accion${hij.id}${accion.accion_id}" name="permisosAcciones-${hij.id}[]" value="${accion.accion_id}" ${checked} />
              <label class="form-check-label" for="accion${hij.id}${accion.accion_id}">${accion.nombre_accion}</label>
            </li>
          `;
        });

        htmlAcciones += `</ul>`;
      }

      let checked = hij.permiso == 1 ? "checked" : "";

      if (hij.permiso == 1) {
        contPermisos++;
      }

      htmlHijos += `
        <li class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="permisos[]" id="hijo-${hij.id}" value="${hij.id}" ${checked}>
          <label class="form-check-label" for="hijo-${hij.id}">${hij.nombre}</label>

          ${htmlAcciones}
        </li>
      `;
    });

    let checkPadre = "";

    if (contPermisos == cantidadHijos) {
      checkPadre = "checked";
    }

    html += `
      <li class="mb-3">
        <strong>→ <input class="form-check-input" type="checkbox" ${checkPadre} id="modulo-${modulo.id}"> ${modulo.nombre}</strong>
        <ul class="ms-2" id="modulo">
          ${htmlHijos}
        </ul>
      </li>
    `;
  });

  listPermisos.innerHTML = html;
}

const formPermisos = document.getElementById("formPermisos");

formPermisos.addEventListener("submit", (e) => {
  e.preventDefault();

  let formData = new FormData(formPermisos);

  fetch(base_url + "save-permisos", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status == "success") {
        Swal.fire({
          icon: "success",
          title: "Guardado",
          text: "Permisos guardados correctamente",
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Ocurrió un error al guardar los permisos",
        });
      }
    });
});

function permisos_details(id) {
  const idperfil = id;

  if (window.innerWidth < 576) {
    // Modo móvil
    perfilesCard.classList.add("d-none"); // Oculta la lista de perfiles
    perfilInfo.classList.remove("d-none"); // Muestra la info del perfil
    infoTexto.innerText =
      "Información de: " + this.nextElementSibling.innerText;
  }

  fetch(base_url + "permisos-perfil/" + idperfil)
    .then((response) => response.json())
    .then((data) => {
      titleProfile.innerText = data.perfil;
      perfil_id.value = data.idperfil;
      viewPermisos(data);

      btnGuardar.removeAttribute("hidden");
    });
}

const addButton = document.getElementById("addButton");
const titlePerfil = document.getElementById("titlePerfil");
const formProfile = document.getElementById("formProfile");
const perfilId = document.getElementById("idperfil");
const btnForm = document.getElementById("btnForm");
const nombre_perfil = document.getElementById("nombre_perfil");

addButton.addEventListener("click", () => {
  $("#modalProfile").modal("show");
  titlePerfil.textContent = "Nuevo perfil";
  perfilId.value = 0;
  formProfile.reset();
});

formProfile.addEventListener("submit", (e) => {
  e.preventDefault();

  btnForm.setAttribute("disabled", true);
  btnForm.innerHTML =
    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

  let formData = new FormData(formProfile);

  fetch(base_url + "save-perfil", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      btnForm.removeAttribute("disabled");
      btnForm.innerHTML = "Guardar";

      if (data.status == "success") {
        Swal.fire({
          position: "top-center",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        loadProfile();
        $("#modalProfile").modal("hide");
      }
    });
});

function editarPerfil(event, id, namePerfil) {
  event.preventDefault();

  perfilId.value = id;

  $("#modalProfile").modal("show");
  titlePerfil.textContent = "Editar perfil";

  nombre_perfil.value = namePerfil;
}

function eliminarPerfil(event, id) {
  event.preventDefault();

  Swal.fire({
    title: "¿Estás seguro?",
    text: "¡No podrás revertir esto!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "¡Sí, bórralo!",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(base_url + "delete-perfil/" + id)
        .then((response) => response.json())
        .then((data) => {
          if (data.status == "success") {
            Swal.fire({
              position: "top-center",
              icon: "success",
              title: data.message,
              showConfirmButton: false,
              timer: 1500,
            });
            loadProfile();
          }
        });
    }
  });
}
