<div class="modal fade sel-modal" id="modalSelectorCatalogo" tabindex="-1" role="dialog" aria-labelledby="selectorCatalogoTitulo" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable sel-modal-dialog" role="document">

        <div class="modal-content sel-modal-content">

            <div class="modal-header sel-modal-header">

                <h5 class="modal-title mb-0" id="selectorCatalogoTitulo">Buscar y seleccionar</h5>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body sel-modal-body pb-2">

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



                <div class="sel-modal-search-panel mb-3">

                    <div class="row align-items-end">

                        <div class="col-md-8">

                            <label class="sel-modal-search-label" id="selectorCatalogoBuscarLabel">Buscar</label>

                            <div class="input-group sel-modal-search-input">

                                <div class="input-group-prepend">

                                    <span class="input-group-text"><i class="fas fa-search"></i></span>

                                </div>

                                <input type="search" id="selectorCatalogoBuscar" class="form-control" placeholder="Buscar…" autocomplete="off">

                            </div>

                        </div>

                        <div class="col-md-4" id="selectorCatalogoFiltroWrap" style="display: none;">

                            <label class="sel-modal-search-label" for="selectorCatalogoFiltro">Filtro</label>

                            <select id="selectorCatalogoFiltro" class="form-control form-control-sm selector-cultivo-select"></select>

                        </div>

                    </div>

                </div>



                <div class="sel-modal-table-wrap">

                    <table class="table sel-modal-table mb-0">

                        <thead>

                            <tr>

                                <th id="selectorCatalogoColNombre">Nombre</th>

                                <th id="selectorCatalogoColDetalle">Detalle</th>

                            </tr>

                        </thead>

                        <tbody id="selectorCatalogoLista">

                            <tr>

                                <td colspan="2" class="sel-modal-empty">

                                    <i class="fas fa-inbox d-block mb-2"></i>

                                    Abra el buscador para cargar resultados.

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

            <div class="modal-footer sel-modal-footer justify-content-between">

                <small class="sel-modal-meta" id="selectorCatalogoMeta"></small>

                <div class="sel-modal-pager">

                    <button type="button" class="btn btn-outline-secondary btn-sm sel-pager-btn" id="selectorCatalogoPrev" disabled>

                        <i class="fas fa-chevron-left"></i> Anterior

                    </button>

                    <button type="button" class="btn btn-outline-secondary btn-sm sel-pager-btn" id="selectorCatalogoNext" disabled>

                        Siguiente <i class="fas fa-chevron-right"></i>

                    </button>

                    <button type="button" class="btn btn-secondary btn-sm sel-close-btn ml-1" data-dismiss="modal">Cerrar</button>

                </div>

            </div>

        </div>

    </div>

</div>

