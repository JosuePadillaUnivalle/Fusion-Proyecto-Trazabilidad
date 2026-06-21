<div class="modal fade" id="modalMaquinariaEtapa" tabindex="-1" role="dialog" aria-labelledby="modalMaquinariaEtapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalMaquinariaEtapaLabel">
                    <i class="fas fa-cogs mr-2"></i>Elegir maquinaria
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="small font-weight-bold text-muted mb-1" for="modalMaquinariaBuscar">Buscar</label>
                        <input type="search" class="form-control form-control-sm" id="modalMaquinariaBuscar"
                               placeholder="Nombre, código o descripción…" autocomplete="off">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <p class="small text-muted mb-2" id="modalMaquinariaResultados">Seleccione un proceso primero.</p>
                    </div>
                </div>
                <div class="row" id="modalMaquinariaGrid"></div>
                <div class="text-center py-4 text-muted d-none" id="modalMaquinariaVacio">
                    <i class="fas fa-tools fa-2x mb-2 opacity-25 d-block"></i>
                    No hay maquinaria compatible con el proceso elegido.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
