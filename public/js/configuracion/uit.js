const formUit = document.getElementById('formUit');

formUit.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(formUit);

    fetch(base_url + 'configuracion/save-uit', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        Swal.fire({
            position: 'top-center',
            icon: 'success',
            title: data.message,
            showConfirmButton: false,
            timer: 1500
        });
        
    })
});