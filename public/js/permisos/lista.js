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
      let checked = hij.permiso == 1 ? "checked" : "";

      if (hij.permiso == 1) {
        contPermisos++;
      }

      htmlHijos += `
        <li class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="permisos[]" id="hijo-${hij.id}" value="${hij.id}" ${checked}>
          <label class="form-check-label" for="hijo-${hij.id}">${hij.nombre}</label>
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
