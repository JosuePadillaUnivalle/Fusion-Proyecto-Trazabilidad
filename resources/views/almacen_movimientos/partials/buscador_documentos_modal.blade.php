<div class="modal fade" id="modalBuscadorDocs" tabindex="-1" role="dialog" aria-labelledby="modalBuscadorDocsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="modalBuscadorDocsLabel">
                    <i class="fas fa-search mr-1"></i> Buscar documento y destino
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2">
                <ul class="nav nav-tabs" id="buscadorTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-ref-link" data-toggle="tab" href="#tab-referencia" role="tab">
                            Referencia <span class="badge badge-light" id="badge-count-ref">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-dest-link" data-toggle="tab" href="#tab-destino" role="tab">
                            Destino / motivo <span class="badge badge-light" id="badge-count-dest">0</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content border border-top-0 rounded-bottom p-3 bg-white" id="buscadorTabsContent">
                    <div class="tab-pane fade show active" id="tab-referencia" role="tabpanel">
                        <div class="form-row mb-2">
                            <div class="col-md-6">
                                <input type="search" id="buscar-ref-texto" class="form-control"
                                       placeholder="Buscar por código, detalle, envío, pedido…" autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <select id="buscar-ref-categoria" class="form-control">
                                    <option value="">Todas las categorías</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary btn-block" id="buscar-ref-limpiar">Limpiar</button>
                            </div>
                        </div>
                        <p class="small text-muted mb-2" id="buscar-ref-resumen">Escriba para filtrar resultados.</p>
                        <div class="table-responsive" style="max-height: 420px;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th>Código</th>
                                        <th>Categoría</th>
                                        <th>Detalle</th>
                                        <th class="text-center" style="width: 100px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="buscar-ref-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-destino" role="tabpanel">
                        <div class="form-row mb-2">
                            <div class="col-md-6">
                                <input type="search" id="buscar-dest-texto" class="form-control"
                                       placeholder="Buscar destino, cliente, área…" autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <select id="buscar-dest-categoria" class="form-control">
                                    <option value="">Todas las categorías</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary btn-block" id="buscar-dest-limpiar">Limpiar</button>
                            </div>
                        </div>
                        <p class="small text-muted mb-2" id="buscar-dest-resumen">Escriba para filtrar resultados.</p>
                        <div class="table-responsive" style="max-height: 420px;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th>Destino / motivo</th>
                                        <th>Categoría</th>
                                        <th>Detalle</th>
                                        <th class="text-center" style="width: 100px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="buscar-dest-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
