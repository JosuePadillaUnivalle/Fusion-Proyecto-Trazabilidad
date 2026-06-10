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
                <div class="selector-filtros-panel mb-3" id="selectorCatalogoFiltroAlmacenWrap" style="display: none;">
                    <div class="selector-filtros-panel-head">
                        <div class="selector-filtros-icon"><i class="fas fa-warehouse"></i></div>
                        <div>
                            <div class="selector-filtros-title">Seleccionar almacén</div>
                            <div class="selector-filtros-sub">Elija uno de la lista o escriba para buscar entre muchos registros</div>
                        </div>
                    </div>
                    <input type="hidden" id="selectorCatalogoAlmacenId" value="">
                    <div class="selector-almacen-toolbar">
                        <div class="input-group input-group-sm selector-almacen-search">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="search" id="selectorCatalogoAlmacenBuscar" class="form-control" placeholder="Buscar almacén por nombre…" autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-success" id="selectorCatalogoAlmacenLimpiar" title="Ver todos los almacenes">
                                    <i class="fas fa-th-large mr-1"></i> Todos
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="selectorCatalogoAlmacenLista" class="selector-almacen-lista">
                        <div class="selector-almacen-loading text-muted small py-2">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Cargando almacenes…
                        </div>
                    </div>
                    <div class="selector-almacen-seleccionado d-none" id="selectorCatalogoAlmacenActivo">
                        <i class="fas fa-check-circle"></i>
                        <span>Filtrando por: <strong id="selectorCatalogoAlmacenActivoNombre"></strong></span>
                    </div>
                </div>
                <div class="selector-producto-panel mb-3">
                    <div class="row">
                        <div class="col-md-8">
                            <label class="selector-campo-label"><i class="fas fa-box mr-1"></i> Buscar producto</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="search" id="selectorCatalogoBuscar" class="form-control" placeholder="Buscar…" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4" id="selectorCatalogoFiltroWrap" style="display: none;">
                            <label class="selector-campo-label" for="selectorCatalogoFiltro"><i class="fas fa-seedling mr-1"></i> Cultivo</label>
                            <select id="selectorCatalogoFiltro" class="form-control form-control-sm selector-cultivo-select"></select>
                        </div>
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
