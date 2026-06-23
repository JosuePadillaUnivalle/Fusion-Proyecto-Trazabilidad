@php
    $dims = $capacidadResumen['dimensiones'] ?? [];
    $largo = (float) ($dims['largo_m'] ?? 2);
    $ancho = (float) ($dims['ancho_m'] ?? 1.6);
    $alto = (float) ($dims['alto_m'] ?? 1.2);
    $tipoCodigo = strtoupper($vehiculo->tipoVehiculo?->codigo ?? 'CAMIONETA');
    $tipoNombre = $vehiculo->tipoVehiculo?->nombre ?? 'Vehículo';
@endphp
<div class="card veh-det-panel h-100">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <span><i class="fas fa-truck mr-1 text-success"></i> Vehículo — proporciones de carga</span>
        <span class="badge badge-success">{{ $tipoNombre }}</span>
    </div>
    <div class="card-body">
        <div id="veh-caja-3d"
             class="veh-caja-3d"
             data-largo="{{ $largo }}"
             data-ancho="{{ $ancho }}"
             data-alto="{{ $alto }}"
             data-tipo="{{ $tipoCodigo }}"
             data-nombre="{{ $tipoNombre }}"></div>
        <div class="veh-caja-3d__leyenda small text-muted text-center mt-2 mb-1">
            <span class="veh-caja-3d__leyenda-item"><i class="veh-caja-3d__swatch veh-caja-3d__swatch--cabina"></i> Cabina</span>
            <span class="veh-caja-3d__leyenda-item ml-3"><i class="veh-caja-3d__swatch veh-caja-3d__swatch--carga"></i> Caja ({{ number_format($largo, 2) }} × {{ number_format($ancho, 2) }} × {{ number_format($alto, 2) }} m)</span>
        </div>
        <div class="veh-caja-3d__medidas mt-2">
            <div class="row text-center small">
                <div class="col-4">
                    <span class="text-muted d-block">Largo carga</span>
                    <strong>{{ number_format($largo, 2) }} m</strong>
                </div>
                <div class="col-4">
                    <span class="text-muted d-block">Ancho</span>
                    <strong>{{ number_format($ancho, 2) }} m</strong>
                </div>
                <div class="col-4">
                    <span class="text-muted d-block">Alto</span>
                    <strong>{{ number_format($alto, 2) }} m</strong>
                </div>
            </div>
            @if(($dims['factor_volumen_util'] ?? null) !== null)
            <p class="text-muted small mb-0 mt-2 text-center">
                Volumen bruto {{ number_format($dims['volumen_m3'] ?? 0, 1) }} m³ ·
                útil {{ number_format($dims['m3_util'] ?? 0, 1) }} m³
                ({{ number_format((float) ($dims['factor_volumen_util'] ?? 0.85) * 100, 0) }}%)
            </p>
            @endif
        </div>
        <p class="text-muted small mb-0 mt-2"><i class="fas fa-mouse mr-1"></i> Arrastre para rotar · rueda para zoom</p>
    </div>
</div>

@push('scripts')
<script type="module">
import { mountVehCaja3d } from '{{ asset('js/veh-caja-3d.js') }}';

(function () {
    const host = document.getElementById('veh-caja-3d');
    if (!host) return;
    mountVehCaja3d(host, {
        largo: host.dataset.largo,
        ancho: host.dataset.ancho,
        alto: host.dataset.alto,
        tipo: host.dataset.tipo,
        nombre: host.dataset.nombre,
    });
})();
</script>
@endpush
