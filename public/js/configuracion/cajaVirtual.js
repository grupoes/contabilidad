const form_ = document.getElementById('formCajaVirtualSede');

form_.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form_);

    fetch(`${base_url}configuracion-caja-virtual/save`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        
        if(data.status === "success") {
            Swal.fire({
                position: 'top-center',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            });

            return false;
        }

        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Ocurrio un error, recargue de nuevo la página o contáctase con el administrador!'
        });
        
    })
})