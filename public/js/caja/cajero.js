const statusCaja = document.getElementById('statusCaja');

statusCaja.addEventListener('click', (e) => {
    const data = e.target.getAttribute('data-text');

    if(data === 'abrir') {
        fetch(base_url+"caja/apertura")
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                location.reload(true);
            }
            
        })
    }

    if(data == 'cerrar') {
        fetch(base_url+"caja/cierreCaja")
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                location.reload(true);
            }
            
        })
    }
})