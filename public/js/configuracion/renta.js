const newcs = $($table).DataTable(optionsTableDefault);

new $.fn.dataTable.Responsive(newcs);

const esconder = document.getElementById("esconder");
const porcentaje = document.getElementById("porcentaje");
const porcentaje_despues = document.getElementById("porcentaje_despues");
const titleModalRenta = document.getElementById("titleModalRenta");
const idRenta = document.getElementById("idRenta");
const formRenta = document.getElementById("formRenta");

function rentasAnuales(e, id) {
  e.preventDefault();
  $("#modalRenta").modal("show");

  if (id === 12) {
    esconder.removeAttribute("hidden");
  } else {
    esconder.setAttribute("hidden", "");
  }

  fetch(base_url + "configuracion/rentasAnuales/" + id)
    .then((res) => res.json())
    .then((data) => {
      titleModalRenta.textContent = `Renta anuales - ${data.tri_descripcion}`;
      porcentaje.value = `${data.porcentaje_renta}`;
      porcentaje_despues.value = `${data.porcentaje_renta_segunda}`;
      idRenta.value = data.id_tributo;
    });
}

formRenta.addEventListener("submit", (e) => {
  e.preventDefault();
  const data = new FormData(formRenta);
  const url = base_url + "configuracion/rentasAnuales/actualizar";

  fetch(url, {
    method: "POST",
    body: data,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status) {
        $("#modalRenta").modal("hide");
        Swal.fire("Actualizado", data.msg, "success");
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        Swal.fire("Error", data.msg, "error");
      }
    });
});
