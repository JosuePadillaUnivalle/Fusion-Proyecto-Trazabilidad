<div class="modal fade" id="modalSelectorPlantilla" tabindex="-1" role="dialog" aria-labelledby="selectorPlantillaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 text-white" style="background: linear-gradient(135deg, #1e4620, #4a7c59);">
                <div>
                    <h5 class="modal-title mb-0" id="selectorPlantillaTitulo">
                        <i class="fas fa-project-diagram mr-2"></i>Elegir proceso de transformación
                    </h5>
                    <p class="mb-0 small opacity-90">Busque, filtre y revise las etapas antes de seleccionar</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pb-2">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="small font-weight-bold text-muted mb-1"><i class="fas fa-search mr-1"></i> Buscar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="search" id="selectorPlantillaBuscar" class="form-control" placeholder="Nombre, producto ejemplo, palabras clave…" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="small font-weight-bold text-muted mb-1" for="selectorPlantillaDisponibilidad"><i class="fas fa-filter mr-1"></i> Disponibilidad</label>
                        <select id="selectorPlantillaDisponibilidad" class="form-control form-control-sm">
                            <option value="operativas">Solo disponibles</option>
                            <option value="todas">Todas</option>
                            <option value="mantenimiento">En mantenimiento</option>
                        </select>
                    </div>
                </div>
                <div class="row selector-plantilla-layout">
                    <div class="col-lg-5">
                        <div id="selectorPlantillaLista" class="selector-plantilla-lista border rounded">
                            <div class="text-center text-muted py-4 small">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Cargando procesos…
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted" id="selectorPlantillaMeta"></small>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="selectorPlantillaPrev" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="selectorPlantillaNext" disabled>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div id="selectorPlantillaDetalle" class="selector-plantilla-detalle border rounded h-100">
                            <div class="selector-plantilla-detalle-vacio text-muted text-center py-5 px-3">
                                <i class="fas fa-hand-pointer fa-2x mb-2 d-block opacity-50"></i>
                                Seleccione un proceso de la lista para ver sus etapas y detalles.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 justify-content-between">
                <a href="{{ route('plantillas-transformacion.index') }}" target="_blank" class="btn btn-link btn-sm text-success">
                    <i class="fas fa-cog mr-1"></i> Gestionar procesos
                </a>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success btn-sm" id="selectorPlantillaConfirmar" disabled>
                        <i class="fas fa-check mr-1"></i> Usar este proceso
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
