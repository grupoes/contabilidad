const urlbase = document.getElementById('urlbase').value;

const formLogin = document.getElementById('formLogin');

formLogin.addEventListener('submit', async function(e) {
    e.preventDefault();

    const alertBox = document.getElementById('alert');
    alertBox.style.display = 'none';

    //const username = document.getElementById('username').value;
    //const password = document.getElementById('password').value;

    const formData = new FormData(this);

    const response = await fetch(`${urlbase}auth/login`, {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    if (result.status === 'success') {
        alertBox.className = 'alert alert-success';
        alertBox.textContent = result.message;
        alertBox.style.display = 'block';

        setTimeout(() => {
            window.location.href = `${urlbase}home`;
        }, 1500);
    } else {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = result.message;
        alertBox.style.display = 'block';
    }
});