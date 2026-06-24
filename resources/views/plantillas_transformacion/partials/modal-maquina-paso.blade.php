<div class="modal fade" id="modalMaquinaPasoPlantilla" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#1e4620,#2c5530)">
                <h5 class="modal-title"><i class="fas fa-industry mr-2"></i>Elegir máquina para este paso</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <input type="search" class="form-control" id="modalMaquinaPasoBuscar" placeholder="Buscar por nombre o código…" autocomplete="off">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <p class="small text-muted mb-0" id="modalMaquinaPasoContador"></p>
                    </div>
                </div>
                <div class="row" id="modalMaquinaPasoGrid"></div>
                <div class="text-center py-4 text-muted d-none" id="modalMaquinaPasoVacio">
                    <i class="fas fa-tools fa-2x mb-2 opacity-25"></i>
                    <p class="mb-0">No hay máquinas compatibles con el proceso seleccionado.</p>
                </div>
            </div>
        </div>
    </div>
</div>
