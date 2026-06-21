<div class="sim-progreso-transportista" data-sim-tipo="{{ $tipo }}" data-sim-id="{{ $id }}">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="small font-weight-bold text-primary"><i class="fas fa-shipping-fast mr-1"></i>En ruta</span>
        <span class="small text-muted sim-progreso-pct">—</span>
    </div>
    <div class="progress mb-1" style="height:8px;border-radius:4px;">
        <div class="progress-bar bg-success sim-progreso-bar" style="width:0%"></div>
    </div>
    <p class="small text-muted mb-0 sim-progreso-eta">Calculando llegada…</p>
    <p class="small text-info mb-0 mt-1"><i class="fas fa-info-circle mr-1"></i>Simulación acelerada: el recorrido en mapa dura {{ (int) (\App\Support\SimulacionRutaCatalogo::DURACION_DEMO_SEG / 60) }} min. La hora estimada del envío usa el tiempo real del trayecto.</p>
</div>

@once
    @push('scripts')
    <script src="{{ asset('js/simulacion-ruta.js') }}?v=8"></script>
    @endpush
@endonce
