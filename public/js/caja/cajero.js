const swalWithBootstrapButtons = Swal.mixin({
  customClass: {
    confirmButton: "btn btn-success",
    cancelButton: "btn btn-danger",
  },
  showConfirmButton: true,
  buttonsStyling: false,
});

/*const statusCaja = document.getElementById('statusCaja');

statusCaja.addEventListener('click', (e) => {
    const data = e.target.getAttribute('data-text');

    if(data === 'abrir') {
        fetch(base_url+"caja/apertura")
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                location.reload(true);
            } else {
                swalWithBootstrapButtons.fire('Advertencia!', data.message, 'error');
            }
            
        })
    }

    if(data == 'cerrar') {

        swalWithBootstrapButtons
        .fire({
            title: '¿Seguro desea cerrar caja?',
            text: "¡Compruebe que los montos se han entregado correctamente!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar caja!',
            cancelButtonText: 'No, cancelar!',
            reverseButtons: true
        })
        .then((result) => {
            if (result.isConfirmed) {
                fetch(base_url+"caja/cierreCaja")
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    location.reload(true);
                }
                
            })
                
            }
        });
    }
})*/

const ingresos_caja_fisica = document.getElementById("ingresos_caja_fisica");
const egresos_caja_fisica = document.getElementById("egresos_caja_fisica");
const utilidad_fisica = document.getElementById("utilidad_fisica");

const ingresos_caja_virtual = document.getElementById("ingresos_caja_virtual");
const egresos_caja_virtual = document.getElementById("egresos_caja_virtual");
const utilidad_virtual = document.getElementById("utilidad_caja_virtual");
const utilidad_hoy = document.getElementById("utilidad_hoy");

renderResumenCaja();

function renderResumenCaja() {
  fetch(base_url + "caja/resumen-cajero")
    .then((res) => res.json())
    .then((data) => {
      ingresos_caja_fisica.textContent = parseFloat(
        data.ingresosFisicos
      ).toFixed(2);
      egresos_caja_fisica.textContent = parseFloat(data.egresosFisicos).toFixed(
        2
      );
      utilidad_fisica.textContent = parseFloat(data.utilidadFisica).toFixed(2);

      ingresos_caja_virtual.textContent = parseFloat(
        data.ingresosVirtual
      ).toFixed(2);
      egresos_caja_virtual.textContent = parseFloat(
        data.egresosVirtual
      ).toFixed(2);
      utilidad_virtual.textContent = parseFloat(data.utilidadVirtual).toFixed(
        2
      );

      utilidad_hoy.textContent = parseFloat(data.utilidadHoy).toFixed(2);
    });
}
