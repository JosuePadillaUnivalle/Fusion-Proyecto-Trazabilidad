<div class="modal fade" id="modalVehiculoCargaPreview" tabindex="-1" role="dialog" aria-labelledby="modalVehiculoCargaPreviewTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable veh-carga-preview-dialog" role="document">
        <div class="modal-content border-0 shadow-lg veh-carga-preview-modal veh-det-panel">
            <div class="modal-header veh-carga-preview-modal__head veh-det-panel__head">
                <div class="d-flex justify-content-between align-items-start w-100 flex-wrap gap-2">
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalVehiculoCargaPreviewTitulo">
                            <i class="fas fa-truck mr-1 text-success"></i> Vehículo — proporciones de carga
                        </h5>
                        <p class="text-muted small mb-0 mt-1" id="vehCargaPreviewSubtitulo">—</p>
                    </div>
                    <span class="badge badge-success veh-carga-preview-modal__tipo" id="vehCargaPreviewTipoBadge">—</span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body veh-carga-preview-modal__body">
                <div id="veh-carga-preview-3d" class="veh-caja-3d"></div>

                <div class="veh-caja-3d__leyenda small text-muted text-center mt-2 mb-1">
                    <span class="veh-caja-3d__leyenda-item">
                        <i class="veh-caja-3d__swatch veh-caja-3d__swatch--cabina"></i> Cabina
                    </span>
                    <span class="veh-caja-3d__leyenda-item ml-3">
                        <i class="veh-caja-3d__swatch veh-caja-3d__swatch--carga"></i>
                        <span id="vehCargaPreviewLeyendaCaja">Caja</span>
                    </span>
                    <span class="veh-caja-3d__leyenda-item ml-3">
                        <i class="veh-caja-3d__swatch veh-caja-3d__swatch--fill"></i> Carga
                    </span>
                </div>

                <div class="veh-caja-3d__medidas mt-2">
                    <div class="row text-center small">
                        <div class="col-4">
                            <span class="text-muted d-block">Largo carga</span>
                            <strong id="vehCargaPreviewDimLargo">—</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block">Ancho</span>
                            <strong id="vehCargaPreviewDimAncho">—</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block">Alto</span>
                            <strong id="vehCargaPreviewDimAlto">—</strong>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-2 text-center" id="vehCargaPreviewVolText"></p>
                </div>

                <p class="text-muted small mb-3"><i class="fas fa-mouse mr-1"></i> Arrastre para rotar · rueda para zoom</p>

                <div class="veh-carga-preview-stats">
                    <div class="veh-carga-preview-stat">
                        <div class="veh-carga-preview-stat__head">
                            <span><i class="fas fa-weight-hanging mr-1"></i> Peso del pedido</span>
                            <strong id="vehCargaPreviewPctPeso">—</strong>
                        </div>
                        <div class="veh-carga-preview-bar">
                            <div class="veh-carga-preview-bar__fill veh-carga-preview-bar__fill--peso" id="vehCargaPreviewBarPeso" style="width:0%"></div>
                        </div>
                        <div class="veh-carga-preview-stat__foot" id="vehCargaPreviewTxtPeso">—</div>
                    </div>
                    <div class="veh-carga-preview-stat">
                        <div class="veh-carga-preview-stat__head">
                            <span><i class="fas fa-cube mr-1"></i> Volumen del pedido</span>
                            <strong id="vehCargaPreviewPctVol">—</strong>
                        </div>
                        <div class="veh-carga-preview-bar">
                            <div class="veh-carga-preview-bar__fill veh-carga-preview-bar__fill--vol" id="vehCargaPreviewBarVol" style="width:0%"></div>
                        </div>
                        <div class="veh-carga-preview-stat__foot" id="vehCargaPreviewTxtVol">—</div>
                    </div>
                </div>

                <div class="veh-carga-preview-alert veh-carga-preview-alert--ok" id="vehCargaPreviewOk" style="display:none"></div>
                <div class="veh-carga-preview-alert veh-carga-preview-alert--err" id="vehCargaPreviewError" style="display:none"></div>
            </div>
            <div class="modal-footer border-0 veh-carga-preview-modal__foot">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
