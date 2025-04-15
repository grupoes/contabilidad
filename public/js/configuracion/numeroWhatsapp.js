const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const btnModal = document.getElementById("btnModal");
const titleModal = document.getElementById("titleModal");
const btnForm = document.getElementById("btnForm");
const tableBody = document.getElementById("tableBody");
const formNumeroWhatsapp = document.getElementById("formNumeroWhatsapp");
const idNumero = document.getElementById("idNumero");
const numero_whatsapp = document.getElementById("numero_whatsapp");
const nombre_whatsapp = document.getElementById("nombre_whatsapp");
const link = document.getElementById("link");

renderNumeroWhatsapp();

function renderNumeroWhatsapp() {
  fetch(`${base_url}configuracion/numeroWhatsapp/all`)
    .then((res) => res.json())
    .then((data) => {
      viewNumeroWhatsapp(data);
    });
}

function viewNumeroWhatsapp(data) {
  let html = "";

  data.forEach((numero, index) => {
    html += `
      <tr>
        <td>${index + 1}</td>
        <td>${numero.numero}</td>
        <td>${numero.titulo}</td>
        <td>${numero.link}</td>
        <td class="text-center">
            <ul class="list-inline me-auto mb-0">
                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Editar">
                    <a href="#" onclick="editarWhatsapp(event, ${
                      numero.id
                    })" class="avtar avtar-xs btn-link-success btn-pc-default"><i class="ti ti-edit-circle f-18"></i></a>
                </li>
                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Eliminar">
                    <a href="#" onclick="deleteWhatsapp(event, ${
                      numero.id
                    })" class="avtar avtar-xs btn-link-danger btn-pc-default"><i class="ti ti-trash f-18"></i></a>
                </li>
            </ul>
      </tr>
    `;
  });

  $($table).DataTable().destroy();
  tableBody.innerHTML = html;

  $($table).DataTable(optionsTableDefault);

  new $.fn.dataTable.Responsive(newcs);
}

btnModal.addEventListener("click", () => {
  $("#modalNumeroWhatasapp").modal("show");
  titleModal.textContent = "Agregar número de whatsapp";
  idNumero.value = 0;
  btnForm.textContent = "Guardar";
  formNumeroWhatsapp.reset();
});

formNumeroWhatsapp.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formNumeroWhatsapp);

  btnForm.textContent = "Guardando...";
  btnForm.disabled = true;

  fetch(`${base_url}configuracion/numeroWhatsapp/store`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      btnForm.textContent = "Guardar";
      btnForm.disabled = false;
      if (data.status === "success") {
        renderNumeroWhatsapp();
        $("#modalNumeroWhatasapp").modal("hide");
        Swal.fire("¡Éxito!", data.message, "success");
        return false;
      }

      Swal.fire("¡Ocurrio un error!", data.message, "error");
    });
});

function editarWhatsapp(e, id) {
  e.preventDefault();

  $("#modalNumeroWhatasapp").modal("show");
  titleModal.textContent = "Editar número de whatsapp";
  btnForm.textContent = "Actualizar";
  idNumero.value = id;

  fetch(`${base_url}configuracion/numeroWhatsapp/${id}`)
    .then((res) => res.json())
    .then((data) => {
      numero_whatsapp.value = data.numero;
      nombre_whatsapp.value = data.titulo;
      link.value = data.link;
    });
}

function deleteWhatsapp(e, id) {
  e.preventDefault();

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
      fetch(`${base_url}configuracion/deleteWhatsapp/${id}`)
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            renderNumeroWhatsapp();
            Swal.fire("¡Eliminado!", data.message, "success");
            return false;
          }

          Swal.fire("¡Ocurrio un error!", data.message, "error");
        });
    }
  });
}
