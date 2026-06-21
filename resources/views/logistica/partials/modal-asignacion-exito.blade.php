<div class="modal fade" id="modalAsignacionExito" tabindex="-1" role="dialog" aria-labelledby="modalAsignacionExitoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 py-4 px-4" style="background: linear-gradient(135deg, #1e4620, #2c5530); color: #fff;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-1" id="modalAsignacionExitoTitulo">
                        <i class="fas fa-check-circle mr-2"></i>Asignación guardada
                    </h5>
                    <p class="mb-0 small" style="opacity: .9;" id="modalAsignacionExitoSubtitulo">Los envíos quedaron asignados correctamente.</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <small class="text-muted text-uppercase d-block mb-1">Transportista</small>
                        <strong id="modalAsignacionExitoTransportista">—</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase d-block mb-1">Vehículo</small>
                        <strong id="modalAsignacionExitoVehiculo">—</strong>
                    </div>
                </div>
                <small class="text-muted text-uppercase d-block mb-2">Envíos asignados (<span id="modalAsignacionExitoCantidad">0</span>)</small>
                <div class="bg-light rounded p-3" style="max-height: 160px; overflow-y: auto;">
                    <ul class="mb-0 pl-3" id="modalAsignacionExitoEnvios"></ul>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3 flex-wrap">
                <a href="#" class="btn btn-success px-4 font-weight-bold" id="modalAsignacionExitoVerListado">
                    <i class="fas fa-list mr-1"></i>Ver envíos asignados
                </a>
                <a href="#" class="btn btn-outline-primary px-4" id="modalAsignacionExitoDocumentos">
                    <i class="fas fa-file-alt mr-1"></i>Documentos de entrega
                </a>
                <button type="button" class="btn btn-outline-secondary px-4" id="modalAsignacionExitoNueva">
                    <i class="fas fa-plus mr-1"></i>Nueva asignación
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.ModalAsignacionExito) return;

    window.ModalAsignacionExito = {
        abrir(data) {
            const transportista = document.getElementById('modalAsignacionExitoTransportista');
            const vehiculo = document.getElementById('modalAsignacionExitoVehiculo');
            const cantidad = document.getElementById('modalAsignacionExitoCantidad');
            const lista = document.getElementById('modalAsignacionExitoEnvios');
            const subtitulo = document.getElementById('modalAsignacionExitoSubtitulo');
            const btnListado = document.getElementById('modalAsignacionExitoVerListado');
            const btnDocs = document.getElementById('modalAsignacionExitoDocumentos');
            const btnNueva = document.getElementById('modalAsignacionExitoNueva');

            if (transportista) transportista.textContent = data.transportista || '—';
            if (vehiculo) vehiculo.textContent = data.vehiculo || '—';
            if (cantidad) cantidad.textContent = String(data.cantidad || 0);
            if (subtitulo) subtitulo.textContent = data.message || 'Los envíos quedaron asignados correctamente.';
            if (lista) {
                const envios = data.envios || [];
                lista.innerHTML = envios.length
                    ? envios.map(function (c) { return '<li><code>' + c + '</code></li>'; }).join('')
                    : '<li class="text-muted">Sin códigos</li>';
            }
            if (btnListado && data.urls?.listado) btnListado.href = data.urls.listado;
            if (btnDocs && data.urls?.documentos) btnDocs.href = data.urls.documentos;
            if (btnNueva && data.urls?.nueva) {
                btnNueva.onclick = function () { window.location.href = data.urls.nueva; };
            }

            if (window.jQuery) {
                window.jQuery('#modalAsignacionExito').modal('show');
            }
        },
    };
})();
</script>
@endpush
@endonce
