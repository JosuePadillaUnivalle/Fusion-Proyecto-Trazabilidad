<div class="modal fade" id="modalQrInventario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #1e4620, #2c5530); color: #fff;">
                <h5 class="modal-title font-weight-bold mb-0">
                    <i class="fas fa-qrcode mr-2"></i><span id="modalQrTitulo">Trazabilidad</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-4 text-center">
                <p class="text-muted small mb-3">Escanea el código QR para ver la trazabilidad completa.</p>
                <div id="qrInventarioCanvas" class="qr-box mb-3" style="display:flex;align-items:center;justify-content:center;min-height:220px;background:#f8faf9;border-radius:12px;border:2px dashed #a7f3d0;"></div>
                <p class="small text-muted mb-2" id="modalQrUrl" style="word-break:break-all;"></p>
                <a href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" id="modalQrAbrir">
                    <i class="fas fa-external-link-alt mr-1"></i> Abrir trazabilidad
                </a>
            </div>
        </div>
    </div>
</div>
