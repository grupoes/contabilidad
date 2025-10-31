const formService = document.getElementById("formService");

formService.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(formService);

  fetch(`${base_url}save-service`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      console.log(data);
    });
});

const searchDocumento = document.getElementById("searchDocumento");
const numeroDocumento = document.getElementById("numeroDocumento");
const razon_social = document.getElementById("razon_social");

searchDocumento.addEventListener("click", () => {
  console.log("hola");

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
