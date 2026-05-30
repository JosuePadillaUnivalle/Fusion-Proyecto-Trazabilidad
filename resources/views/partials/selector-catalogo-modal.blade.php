<div class="modal fade" id="modalSelectorCatalogo" tabindex="-1" role="dialog" aria-labelledby="selectorCatalogoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title mb-0" id="selectorCatalogoTitulo">Buscar y seleccionar</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pb-2">
                <p class="text-muted small mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Escriba para buscar entre miles de registros. Use el filtro si está disponible.
                </p>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="search" id="selectorCatalogoBuscar" class="form-control" placeholder="Buscar…" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4" id="selectorCatalogoFiltroWrap" style="display: none;">
                        <select id="selectorCatalogoFiltro" class="form-control form-control-sm"></select>
                    </div>
                </div>
                <div class="table-responsive border rounded">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="selectorCatalogoLista">
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">Abra el buscador para cargar resultados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2 justify-content-between">
                <small class="text-muted" id="selectorCatalogoMeta"></small>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="selectorCatalogoPrev" disabled>
                        <i class="fas fa-chevron-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="selectorCatalogoNext" disabled>
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm ml-1" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
