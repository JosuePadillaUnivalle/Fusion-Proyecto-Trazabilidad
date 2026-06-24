<div class="modal fade rpt-modal-accent rpt-accent--{{ $item['accent'] ?? 'forest' }}" id="modalRptPdfPreview" tabindex="-1" role="dialog" aria-labelledby="modalRptPdfTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 920px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-3 px-4 border-0 text-white">
                <h5 class="modal-title font-weight-bold mb-0" id="modalRptPdfTitulo">
                    <i class="fas fa-file-pdf mr-2"></i>Vista previa del reporte
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 bg-light" style="min-height: 420px;">
                <iframe id="rptPdfPreviewFrame" title="Vista previa PDF" style="width:100%; height:70vh; border:0; background:#fff;"></iframe>
            </div>
            <div class="modal-footer border-0 bg-white py-3">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn rpt-btn-accent" onclick="rptExportPdf()"><i class="fas fa-download mr-1"></i>Descargar PDF</button>
            </div>
        </div>
    </div>
</div>