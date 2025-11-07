const formService = document.getElementById("formService");

const estado = document.getElementById("estado");
const metodo_pago = document.getElementById("metodo_pago");

formService.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formService);

  fetch(`${base_url}save-service`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        Swal.fire({
          position: "top-center",
          icon: "success",
          title: data.message,
          showConfirmButton: false,
          timer: 1500,
        });

        setTimeout(() => {
          window.location.href = base_url + "servicio";
        }, 1600);
      } else {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: data.message,
        });
      }
    });
});

const searchDocumento = document.getElementById("searchDocumento");
const numeroDocumento = document.getElementById("numeroDocumento");
const razon_social = document.getElementById("razon_social");

searchDocumento.addEventListener("click", () => {
  const ruc = numeroDocumento.value;

  if (ruc.length == 11) {
    fetch(base_url + "api/dni-ruc/ruc/" + ruc)
      .then((res) => res.json())
      .then((data) => {
        if (data.respuesta === "ok") {
          razon_social.value = data.data.razon_social;
        } else {
          alert(data.data_resp.mensaje);
        }
      });
  } else {
    alert("Agregue un R.U.C. de 11 dígitos");
  }
});

estado.addEventListener("change", () => {
  if (estado.value == "pendiente") {
    metodo_pago.removeAttribute("required");
  } else {
    metodo_pago.setAttribute("required", "true");
  }
});
