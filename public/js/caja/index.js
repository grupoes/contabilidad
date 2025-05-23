function renderDetalleCajaDia() {
  fetch(base_url + "/resumenCajaDiaAll")
    .then((response) => response.json())
    .then((data) => {
      viewDetalleCajaDiaAll(data);
    })
    .catch((error) => console.error(error));
}

function viewDetalleCajaDiaAll(data) {
  const detalleCajaDiaAll = document.getElementById("detalleCajaDiaAll");
  let html = `
    
    `;

  detalleCajaDiaAll.innerHTML = html;
}

//renderDetalleCajaDia();
