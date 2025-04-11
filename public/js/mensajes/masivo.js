const configTable = {
  language: language,
  paging: false, // Hace que la tabla sea responsiva
  autoWidth: false, // Desactiva el ajuste automático de ancho
  scrollX: false,
  scrollY: "330px",
  lengthChange: false,
  info: false,
  scrollCollapse: true,
  columnDefs: [{ orderable: false, targets: [0, 1] }],
};

const configEnvio = {
  language: language,
  paging: false, // Hace que la tabla sea responsiva
  autoWidth: false, // Desactiva el ajuste automático de ancho
  scrollX: false,
  lengthChange: false,
  info: false,
  scrollCollapse: true,
  columnDefs: [{ orderable: false, targets: [0, 1] }],
};

$("#tableContribuyentes").DataTable(configTable);

const listContri = document.getElementById("listContri");

contribuyentesActivos();

function contribuyentesActivos() {
  const seleccionado = document.querySelector(
    'input[name="contribuyenteType"]:checked'
  );

  fetch(base_url + "contribuyentes/contribuyentesActivos/" + seleccionado.value)
    .then((res) => res.json())
    .then((data) => {
      viewContribuyentesActivos(data);
    });
}

function viewContribuyentesActivos(data) {
  let html = "";
  data.forEach((contri) => {
    html += `
    <tr>
        <td>
            <input type="checkbox" class="form-check-input" name="contribuyentes[]" value="${contri.id}" checked>
        </td>
        <td>${contri.razon_social}</td>
    </tr>`;
  });

  $("#tableContribuyentes").DataTable().destroy();

  listContri.innerHTML = html;

  $("#tableContribuyentes").DataTable(configTable);

  checkContribuyente();
}

document
  .querySelectorAll('input[name="contribuyenteType"]')
  .forEach((radio) => {
    radio.addEventListener("change", () => {
      contribuyentesActivos();
    });
  });

const formMessageMasivos = document.getElementById("formMessageMasivos");

formMessageMasivos.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formMessageMasivos);

  fetch(base_url + "mensajes/guardarMensajeMasivo", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status) {
        Swal.fire({
          position: "top-end",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        fetch(base_url + "mensajes/enviarMessagesMasivos")
          .then((res) => res.json())
          .then((data) => {
            viewEnvios(data);
          });

        return;
      }
      Swal.fire({
        position: "top-end",
        icon: "error",
        title: data.message,
      });
    });
});

const checkAll = document.getElementById("checkAll");

function checkContribuyente() {
  const checks = document.querySelectorAll('input[name="contribuyentes[]"]');

  checks.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      const todosSeleccionados = Array.from(checks).every(
        (checkbox) => checkbox.checked
      );

      if (todosSeleccionados) {
        checkAll.checked = true;
      } else {
        checkAll.checked = false;
      }
    });
  });
}

checkAll.addEventListener("change", (e) => {
  const checkboxes = document.querySelectorAll(
    'input[name="contribuyentes[]"]'
  );
  checkboxes.forEach((checkbox) => {
    checkbox.checked = e.target.checked;
  });
});

$("#tableEnvios").DataTable(configTable);
