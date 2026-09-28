const formUit = document.getElementById('formUit');
const tableBody = document.getElementById('tableBody');

if (tableBody) {
    renderUit();

    tableBody.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-editar-uit');
        if (!btn) return;

        document.getElementById('id').value = btn.dataset.id;
        document.getElementById('anio').value = btn.dataset.anio;
        document.getElementById('uit').value = btn.dataset.monto;
    });
}

function renderUit() {
    fetch(base_url + 'configuracion/render-uit')
        .then(res => res.json())
        .then(data => viewUit(data));
}

function viewUit(data) {
    let html = '';

    data.forEach((u) => {
        html += `
        <tr>
            <td>${u.anio}</td>
            <td>${u.uit_monto}</td>
            ${isEditUit ? `<td>${u.acciones}</td>` : ''}
        </tr>
        `;
    });

    tableBody.innerHTML = html;
}

if (formUit) {
    formUit.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formUit);

        fetch(base_url + 'configuracion/save-uit', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    position: 'top-center',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });

                formUit.reset();
                document.getElementById('id').value = '';
                renderUit();
            } else {
                Swal.fire({
                    position: 'top-center',
                    icon: 'error',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        })
    });
}
