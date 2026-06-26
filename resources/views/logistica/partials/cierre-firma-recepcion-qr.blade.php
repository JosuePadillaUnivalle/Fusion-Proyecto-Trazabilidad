@php
    $pollingUrl = $pollingUrl ?? route('cierre.firmas-estado');
    $qrUrl = $qrUrl ?? ($resumen['qr_recepcion_url'] ?? null);
    $pollingRuta = $pollingRuta ?? null;
    $pollingAsignacion = $pollingAsignacion ?? null;
@endphp

@if($qrUrl)
<div class="cierre-ag-qr-recepcion" id="cierre-qr-recepcion"
     data-polling-url="{{ $pollingUrl }}"
     @if($pollingRuta) data-polling-ruta="{{ $pollingRuta }}" @endif
     @if($pollingAsignacion) data-polling-asignacion="{{ $pollingAsignacion }}" @endif>
    <div class="cierre-ag-status cierre-ag-status--firma mb-3">
        <span class="cierre-ag-status__icon"><i class="fas fa-qrcode"></i></span>
        <div class="w-100">
            <strong class="d-block">Firma de recepción desde móvil</strong>
            <span class="small text-muted d-block mb-2">
                El receptor debe escanear el código QR con su teléfono y firmar. Esta pantalla se actualizará automáticamente.
            </span>
            <div id="cierre-qr-canvas" class="d-inline-block p-2 bg-white border rounded" style="line-height:0;"></div>
            <p class="small text-muted mt-2 mb-0">
                <i class="fas fa-sync-alt fa-spin mr-1" id="cierre-qr-polling-icon" style="display:none;"></i>
                <span id="cierre-qr-polling-text">Esperando firma del receptor…</span>
            </p>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="{{ asset('js/cierre-recepcion-qr-polling.js') }}?v=1"></script>
@endpush
@endonce

@push('scripts')
<script>
(function () {
    const box = document.getElementById('cierre-qr-canvas');
    if (!box || typeof QRCode === 'undefined') return;
    box.innerHTML = '';
    new QRCode(box, {
        text: @json($qrUrl),
        width: 168,
        height: 168,
        colorDark: '#1e293b',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M,
    });
})();
</script>
@endpush
@endif
