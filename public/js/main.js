const base_url = document.getElementById("base_url").value;
const salir = document.getElementById("salir_sistema");

const $table = "#tableData";

const language = {
  sProcessing: "Procesando...",
  sLengthMenu: "Mostrar _MENU_ registros",
  sZeroRecords: "No se encontraron resultados",
  sEmptyTable: "Ningún dato disponible en esta tabla",
  sInfo:
    "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
  sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
  sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
  sInfoPostFix: "",
  sSearch: "Buscar:",
  sUrl: "",
  sInfoThousands: ",",
  sLoadingRecords: "Cargando...",
  oAria: {
    sSortAscending: ": Activar para ordenar la columna de manera ascendente",
    sSortDescending: ": Activar para ordenar la columna de manera descendente",
  },
};

const optionsTableDefault = {
  language: language,
  responsive: true, // Hace que la tabla sea responsiva
  autoWidth: false, // Desactiva el ajuste automático de ancho
  scrollX: false, // Evita el scroll horizontal
  columnDefs: [
    { targets: "_all", className: "text-wrap" }, // Permite el ajuste de texto en las columnas
  ],
};

salir.addEventListener("click", (e) => {
  fetch(base_url + "auth/logout")
    .then((response) => response.json())
    .then((data) => {
      window.location.href = base_url;
    })
    .catch((error) => console.error("Error:", error));
});

function showLoader() {
  document.getElementById("loader").classList.add("custom-loader");
  document.querySelector(".contentLoader").style.display = "block";
}

function hideLoader() {
  document.getElementById("loader").classList.remove("custom-loader");
  document.querySelector(".contentLoader").style.display = "none";
}

function spinnerButtonForm(accion) {
  const btnForm = document.getElementById("btnForm");

  btnForm.disabled = true;

  btnForm.innerHTML = `
    <span class="spinner-border spinner-border-sm" role="status"></span>
    ${accion}`;
}

function hideSpinnerButtonForm(accion) {
  btnForm.disabled = false;
  btnForm.innerHTML = accion;
}

function validarCaja() {
  fetch(base_url + "caja/validar-caja")
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "warning") {
        Swal.fire({
          title: "Atención Usuario!",
          text: data.message,
          icon: "error",
          showConfirmButton: false, // Oculta el botón "OK"
          html: `
                    <p style="font-size: 20px">${data.message}</p>
                    <a href="${base_url}caja-diaria" class="btn btn-danger">IR A CAJA</a>
                `,
        });
      }
    });
}

const menuList = document.getElementById("nav-menu");

function navMenu() {
  fetch(base_url + "menu-nav")
    .then((response) => response.json())
    .then((data) => {
      viewMenu(data);
    });
}

navMenu();

function viewMenu(data) {
  let html = `
    <li class="pc-item pc-caption">
        <label>Navegación</label>
    </li>
    <li class="pc-item">
        <a href="${base_url}home" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-home"></use>
                </svg>
            </span>
            <span class="pc-mtext">Inicio</span>
        </a>
    </li>
    `;

  data.forEach((itemMenu) => {
    let htmlHijos = "";
    let hijos = itemMenu.hijos;

    hijos.forEach((itemHijos) => {
      htmlHijos += `
        <li class="pc-item"><a class="pc-link" href="${base_url}${itemHijos.url}">${itemHijos.nombre}</a></li>
        `;
    });

    html += `
    <li class="pc-item pc-hasmenu">
        <a href="javascript:void(0);" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#${itemMenu.modulo_padre_icono}"></use>
                </svg>
            </span>
            <span class="pc-mtext">${itemMenu.modulo_padre_nombre}</span>
            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
        </a>
        <ul class="pc-submenu">
            ${htmlHijos}
        </ul>
    </li>
    `;
  });

  menuList.innerHTML = html;

  feather.replace();

  menu_click();
}
