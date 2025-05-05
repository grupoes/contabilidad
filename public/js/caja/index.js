renderResumenCajaDia();

function renderResumenCajaDia() {
  fetch(base_url + "/resumenCajaDia")
    .then((response) => response.json())
    .then((data) => {
      viewDetalleCajaDia(data);
    })
    .catch((error) => console.error(error));
}

function viewDetalleCajaDia(data) {
  let html = "";

  const detalleCajaDia = document.getElementById("detalleCajaDia");

  data.forEach((sede) => {
    let utilidadFisica = sede.utilidadFisica;
    let utilidadVirtual = sede.utilidadVirtual;
    let utilidadHoy = sede.utilidadHoy;

    utilidadFisica = parseFloat(utilidadFisica).toFixed(2);
    utilidadVirtual = parseFloat(utilidadVirtual).toFixed(2);
    utilidadHoy = parseFloat(utilidadHoy).toFixed(2);

    html += `
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-2">Caja Grupo ESconsultores (${sede.nombre_sede})</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span style="font-size: 18px">Estado de la caja Grupo ESconsultores</span>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-12">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ ${sede.ingresosFisicos}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ ${sede.egresosFisicos}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ ${utilidadFisica}</span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja virtual</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ ${sede.ingresosVirtual}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Virtual</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ ${sede.egresosVirtual}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Virtual</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ ${utilidadVirtual}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-outline-secondary d-grid"><span class="text-truncate w-100">Saldo en Caja</span></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-primary d-grid"><span class="text-truncate w-100">S/ ${utilidadHoy}</span></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
      `;
  });

  detalleCajaDia.innerHTML = html;
}

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
    <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-2">Caja Grupo ESconsultores</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span style="font-size: 18px">Estado de la caja Grupo ESconsultores</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-12">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ ${
                                      data.ingresosFisicos
                                    }</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ ${
                                      data.egresosFisicos
                                    }</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Fisica</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ ${parseFloat(
                                      data.utilidadFisica
                                    ).toFixed(2)}</span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Ingresos Caja virtual</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">S/ ${
                                      data.ingresosVirtual
                                    }</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div>Egresos Caja Virtual</div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">S/ ${
                                      data.egresosVirtual
                                    }</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">Utilidad Caja Virtual</div>
                                    </div>
                                    <span class="badge bg-success rounded-pill">S/ ${parseFloat(
                                      data.utilidadVirtual
                                    ).toFixed(2)}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-outline-secondary d-grid"><span class="text-truncate w-100">Saldo en Caja</span></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button class="btn btn-primary d-grid"><span class="text-truncate w-100">S/ ${parseFloat(
                                  data.utilidadHoy
                                ).toFixed(2)}</span></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    `;

  detalleCajaDiaAll.innerHTML = html;
}

renderDetalleCajaDia();
