<script>
    // Stan activos (se filtran por la sede elegida en el modal)
    const STANS = <?= json_encode($stans ?? [], JSON_UNESCAPED_UNICODE) ?>;
</script>
<div id="modalLibro" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formLibro">
                <div class="modal-body">
                    <input type="hidden" name="idLibro" id="idLibro" value="0">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="contribuyenteNombre">Contribuyente</label>
                            <input type="text" class="form-control" id="contribuyenteNombre" readonly tabindex="-1">
                            <!-- El contribuyente ya viene definido por la página desde la que se abre el modal: el select solo guarda el valor -->
                            <div class="d-none">
                                <select name="contribuyente" id="contribuyente" class="form-select">
                                    <option value="">Buscar contribuyente...</option>
                                    <?php foreach ($contribuyentes as $c) { ?>
                                        <option value="<?= $c['id'] ?>"><?= esc($c['razon_social']) ?> - <?= esc($c['ruc']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label" for="regimen">Régimen (PDT Renta) <span class="text-danger">*</span></label>
                            <select name="regimen" id="regimen" class="form-select text-capitalize" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($tributos as $t) { ?>
                                    <option value="<?= $t['id_tributo'] ?>"><?= esc($t['tri_descripcion']) ?></option>
                                <?php } ?>
                            </select>
                            <div class="text-danger small mt-1" id="avisoRegimen" style="display: none;"></div>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label" for="tipoLibro">Tipo de libro <span class="text-danger">*</span></label>
                            <select name="tipoLibro" id="tipoLibro" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($tipos as $t) { ?>
                                    <option value="<?= $t['tli_id'] ?>"><?= esc($t['tli_nombre']) ?></option>
                                <?php } ?>
                                <option value="otro">Otro...</option>
                            </select>
                            <input type="text" class="form-control mt-2" name="tipoOtro" id="tipoOtro" placeholder="Nombre del libro" style="display: none;">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label" for="numero">N° de libro <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="numero" id="numero" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="sede">Sede <span class="text-danger">*</span></label>
                            <select name="sede" id="sede" class="form-select" required>
                                <?php foreach ($sedes as $s) { ?>
                                    <option value="<?= $s['id'] ?>"><?= esc($s['nombre_sede']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="notaria">Notaría <span class="text-danger">*</span></label>
                            <select name="notaria" id="notaria" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($notarias as $n) { ?>
                                    <option value="<?= $n['not_id'] ?>"><?= esc($n['not_razon_social']) ?> - <?= esc($n['not_ruc']) ?></option>
                                <?php } ?>
                            </select>
                            <?php if (empty($notarias)) { ?>
                                <div class="text-danger small mt-1">No hay notarías activas. Regístrelas en Configuración &gt; Notarias.</div>
                            <?php } ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="fechaLegalizacion">Fecha de legalización <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fechaLegalizacion" id="fechaLegalizacion" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="kardex">N° de kardex <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="kardex" id="kardex" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="stan">N° de stan <span class="text-danger">*</span></label>
                            <select name="stan" id="stan" class="form-select" required>
                                <option value="">Seleccione...</option>
                            </select>
                            <div class="text-danger small mt-1" id="avisoStan" style="display: none;">No hay stan activos en esta sede. Regístrelos en Configuración &gt; Stan.</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="folioInicial">Folio inicial <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="folioInicial" id="folioInicial" min="1" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="folioFinal">Folio final <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="folioFinal" id="folioFinal" min="1" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="totalHojas">Total de hojas <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="totalHojas" readonly tabindex="-1" placeholder="Se calcula con los folios" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label" for="minimo">Alerta: mínimo en blanco <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="minimo" id="minimo" min="1" value="100" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnForm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>