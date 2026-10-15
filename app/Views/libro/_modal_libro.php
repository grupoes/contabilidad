<script>
    const REGIMENES = <?= json_encode($regimenes ?? [], JSON_UNESCAPED_UNICODE) ?>;
</script>
<div id="modalLibro"class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="titleModal"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formLibro">
                <div class="modal-body">
                    <input type="hidden" name="idLibro" id="idLibro" value="0">
                    <input type="hidden" name="periodoDesde" id="periodoDesde">
                    <input type="hidden" name="periodoHasta" id="periodoHasta">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="contribuyente">Contribuyente</label>
                            <select name="contribuyente" id="contribuyente" class="form-select">
                                <option value="">Buscar contribuyente...</option>
                                <?php foreach ($contribuyentes as $c) { ?>
                                    <option value="<?= $c['id'] ?>"><?= esc($c['razon_social']) ?> - <?= esc($c['ruc']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="regimen">Régimen (PDT Renta)</label>
                            <select name="regimen" id="regimen" class="form-select text-capitalize">
                                <option value="">Sin régimen</option>
                                <?php foreach ($tributos as $t) { ?>
                                    <option value="<?= $t['id_tributo'] ?>"><?= esc($t['tri_descripcion']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label" for="tipoLibro">Tipo de libro</label>
                            <select name="tipoLibro" id="tipoLibro" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($tipos as $t) { ?>
                                    <option value="<?= $t['tli_id'] ?>"><?= esc($t['tli_nombre']) ?></option>
                                <?php } ?>
                                <option value="otro">Otro...</option>
                            </select>
                            <input type="text" class="form-control mt-2" name="tipoOtro" id="tipoOtro" placeholder="Nombre del libro" style="display: none;">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="numero">N° de libro</label>
                            <input type="text" class="form-control" name="numero" id="numero">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="sede">Sede</label>
                            <select name="sede" id="sede" class="form-select">
                                <?php foreach ($sedes as $s) { ?>
                                    <option><?= $s ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="notaria">Notaría</label>
                            <input type="text" class="form-control" name="notaria" id="notaria">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="fechaLegalizacion">Fecha de legalización</label>
                            <input type="date" class="form-control" name="fechaLegalizacion" id="fechaLegalizacion">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="kardex">N° de kardex</label>
                            <input type="text" class="form-control" name="kardex" id="kardex">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="stan">N° de stan</label>
                            <input type="text" class="form-control" name="stan" id="stan">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="folioInicial">Folio inicial</label>
                            <input type="number" class="form-control" name="folioInicial" id="folioInicial" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="folioFinal">Folio final</label>
                            <input type="number" class="form-control" name="folioFinal" id="folioFinal" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="totalHojas">Total de hojas</label>
                            <input type="number" class="form-control" name="totalHojas" id="totalHojas" min="1">
                        </div>
                        <div id="bloqueImpresas" class="col-12">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="impresasHasta">Impresas hasta el folio</label>
                                    <input type="number" class="form-control" name="impresasHasta" id="impresasHasta" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Periodo impresiones: desde</label>
                                    <?= view('libro/_periodo', ['pid' => 'pd', 'vacio' => true]) ?>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Periodo impresiones: hasta</label>
                                    <?= view('libro/_periodo', ['pid' => 'ph', 'vacio' => true]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="minimo">Alerta: mínimo en blanco</label>
                            <input type="number" class="form-control" name="minimo" id="minimo" min="1" value="100">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label" for="observacion">Observaciones</label>
                            <input type="text" class="form-control" name="observacion" id="observacion">
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
