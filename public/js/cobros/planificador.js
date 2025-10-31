const formService = document.getElementById('formService');

formService.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(formService);

    fetch(`${base_url}save-service`, {
        method: 'POST',
        body: formData
    })
    .then( res => res.json())
    .then(data => {
        console.log(data);
        
    })
})