const newcs = $($table).DataTable(
    optionsTableDefault
);

new $.fn.dataTable.Responsive(newcs);

const tableBody = document.querySelector("#tableBody");
const selectOpciones = document.getElementById('selectOpciones');

listaContribuyentes();

function listaContribuyentes() {

    fetch(base_url+"listaCobros/"+selectOpciones.value)
    .then(res => res.json())
    .then(data => {
        viewListContribuyentes(data);
        
    })
}

function viewListContribuyentes(data) {
    let html = "";

    data.forEach((emp, index) => {

        let monto;

        if (emp.tipoSuscripcion === 'SI GRATUITO') {
            monto = `GRATUITO`;
        } else {
            if(emp.tipoServicio === 'ALQUILER') {
                monto = `
                    <p class="f-14 mb-0">M: ${emp.costoMensual}</p>
                `;
            } else {
                monto = `
                    <p class="f-14 mb-0">M: ${emp.costoMensual}</p>
                    <p class="f-14 mb-0">A: ${emp.costoAnual}</p>
                `;
            }
            
        }

        let deuda = "";

        if(emp.meses_deuda == 0 || emp.meses_deuda == 1) {
            deuda = `${emp.debe}`;
        } else {
            deuda = `${emp.debe}`;
        }

        html += `
            <tr>
                <td>${index + 1}</td>
                <td>
                    <div class="row">
                        <div class="col">
                            <h6 class="mb-1"><a href="#" class="num-doc" data-id="${emp.id}">${emp.ruc}</a></h6>
                            <p class="text-muted f-14 mb-0"> ${emp.razon_social} </p>
                        </div>
                    </div>
                </td>
                <td>${emp.diaCobro}</td>
                <td><a href="#" class="tipoServicio" data-id="${emp.id}">${emp.tipoServicio}</a></td>
                <td>${deuda}</td>
                <td>
                    <a href="${base_url}pago-honorario/${emp.id}" class="btn btn-success">COBRAR</a>
                </td>
            </tr>
        `;
    });

    $($table).DataTable().destroy();

    tableBody.innerHTML = html;

    const newcs = $($table).DataTable(
        optionsTableDefault
    );
    
    new $.fn.dataTable.Responsive(newcs);
}

selectOpciones.addEventListener('change', (e) => {
    listaContribuyentes();
})

//fecha de contrato es diferente a la fecha de cobro
//clientes con pagos adelantados que son los nuevos, y los antiguos que tienen pagos atrasados
