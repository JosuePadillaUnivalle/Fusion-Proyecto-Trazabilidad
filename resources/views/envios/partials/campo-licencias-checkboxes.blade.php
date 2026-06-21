@php
    $tiposLicencia = $tiposLicencia ?? \App\Support\TiposLicenciaBolivia::todos();
    $licenciasSeleccionadas = \App\Support\TiposLicenciaBolivia::normalizarLista(
        old('licencias', $licenciasActuales ?? [])
    );
    $todasLicencias = (bool) old('licencias_todas', $licenciasTodas ?? false);
    $inputPrefix = $inputPrefix ?? '';
    $licenciasTema = $licenciasTema ?? 'dark';
    $iconosLicencia = [
        'P' => 'fa-car',
        'A' => 'fa-shuttle-van',
        'B' => 'fa-bus',
        'C' => 'fa-truck',
        'T' => 'fa-tractor',
    ];
    $ordenLicencias = ['P', 'A', 'B', 'C', 'T'];
@endphp

<div class="licencias-picker js-licencias-bloque licencias-picker--{{ $licenciasTema }}">
    <div class="licencias-picker__header">
        <span class="licencias-picker__title">Licencias De Conducir:</span>
    </div>

    <label class="licencias-picker__todas">
        <input type="checkbox" class="js-licencias-todas" id="{{ $inputPrefix }}licencias_todas"
               name="licencias_todas" value="1" @checked($todasLicencias)>
        <span class="licencias-picker__todas-card">
            <span class="licencias-picker__todas-icon"><i class="fas fa-id-card-alt"></i></span>
            <span class="licencias-picker__todas-text">
                <strong>Todas las categorías</strong>
                <small>P, A, B, C y T — vehículo completo</small>
            </span>
            <span class="licencias-picker__check"><i class="fas fa-check"></i></span>
        </span>
    </label>

    <div class="licencias-picker__divider">
        <span>o elige categorías</span>
    </div>

    <div class="licencias-picker__grid js-licencias-lista @if($todasLicencias) is-muted @endif">
        @foreach($ordenLicencias as $codigo)
            @php $descripcion = $tiposLicencia[$codigo] ?? ''; @endphp
            @continue($descripcion === '')
            <label class="licencias-picker__item">
                <input type="checkbox" class="js-licencia-item"
                       id="{{ $inputPrefix }}licencia_{{ $codigo }}" name="licencias[]"
                       value="{{ $codigo }}" @checked(in_array($codigo, $licenciasSeleccionadas, true))>
                <span class="licencias-picker__card">
                    <span class="licencias-picker__code">{{ $codigo }}</span>
                    <span class="licencias-picker__icon">
                        <i class="fas {{ $iconosLicencia[$codigo] ?? 'fa-road' }}"></i>
                    </span>
                    <span class="licencias-picker__desc">{{ $descripcion }}</span>
                    <span class="licencias-picker__check"><i class="fas fa-check"></i></span>
                </span>
            </label>
        @endforeach
    </div>

    <p class="licencias-picker__foot">Marque una o más categorías que posea actualmente.</p>
</div>

@once
@push('styles')
<style>
.licencias-picker {
    --lp-border: rgba(255, 255, 255, .1);
    --lp-bg: rgba(255, 255, 255, .04);
    --lp-bg-hover: rgba(255, 255, 255, .07);
    --lp-text: #e2e8f0;
    --lp-muted: #94a3b8;
    --lp-accent: #10b981;
    --lp-accent-soft: rgba(16, 185, 129, .14);
    --lp-accent-glow: rgba(16, 185, 129, .28);
    --lp-divider: rgba(255, 255, 255, .08);
    margin-top: 4px;
}

.licencias-picker--light {
    --lp-border: #d1d5db;
    --lp-bg: #f8fafc;
    --lp-bg-hover: #f1f5f9;
    --lp-text: #1e293b;
    --lp-muted: #64748b;
    --lp-accent: #059669;
    --lp-accent-soft: #ecfdf5;
    --lp-accent-glow: rgba(5, 150, 105, .22);
    --lp-divider: #e2e8f0;
}

.licencias-picker__header {
    margin-bottom: 12px;
}

.licencias-picker__title {
    font-size: .9rem;
    font-weight: 600;
    color: var(--lp-text);
}

.licencias-picker__todas,
.licencias-picker__item {
    position: relative;
    display: block;
    margin: 0;
    cursor: pointer;
}

.licencias-picker__todas input,
.licencias-picker__item input {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
    pointer-events: none;
}

.licencias-picker__todas-card,
.licencias-picker__card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border: 2px solid var(--lp-border);
    border-radius: 12px;
    background: var(--lp-bg);
    color: var(--lp-text);
    transition: border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .12s ease;
}

.licencias-picker__todas-card:hover,
.licencias-picker__item:not(.is-disabled) .licencias-picker__card:hover {
    border-color: var(--lp-accent-glow);
    background: var(--lp-bg-hover);
}

.licencias-picker__todas input:checked + .licencias-picker__todas-card,
.licencias-picker__item input:checked + .licencias-picker__card {
    border-color: var(--lp-accent);
    background: var(--lp-accent-soft);
    box-shadow: 0 0 0 3px var(--lp-accent-glow);
}

.licencias-picker__todas-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(16, 185, 129, .18);
    color: var(--lp-accent);
    flex-shrink: 0;
}

.licencias-picker__todas-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    min-width: 0;
}

.licencias-picker__todas-text strong {
    font-size: .88rem;
    font-weight: 700;
}

.licencias-picker__todas-text small,
.licencias-picker__desc {
    font-size: .76rem;
    color: var(--lp-muted);
    line-height: 1.35;
}

.licencias-picker__check {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid var(--lp-border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    font-size: .62rem;
    flex-shrink: 0;
    transition: all .15s ease;
}

.licencias-picker__todas input:checked + .licencias-picker__todas-card .licencias-picker__check,
.licencias-picker__item input:checked + .licencias-picker__card .licencias-picker__check {
    border-color: var(--lp-accent);
    background: var(--lp-accent);
    color: #fff;
}

.licencias-picker__divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 14px 0 12px;
    color: var(--lp-muted);
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .06em;
}

.licencias-picker__divider::before,
.licencias-picker__divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--lp-divider);
}

.licencias-picker__grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: opacity .2s ease;
}

.licencias-picker__grid.is-muted {
    opacity: .45;
    pointer-events: none;
}

.licencias-picker__item.is-disabled {
    pointer-events: none;
}

.licencias-picker__code {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(148, 163, 184, .16);
    color: var(--lp-text);
    font-size: .82rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.licencias-picker__icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--lp-muted);
    flex-shrink: 0;
}

.licencias-picker__item input:checked + .licencias-picker__card .licencias-picker__code,
.licencias-picker__item input:checked + .licencias-picker__card .licencias-picker__icon {
    color: var(--lp-accent);
}

.licencias-picker__item input:checked + .licencias-picker__card .licencias-picker__code {
    background: rgba(16, 185, 129, .2);
}

.licencias-picker__card {
    flex-direction: row;
    align-items: center;
}

.licencias-picker__desc {
    flex: 1;
    min-width: 0;
}

.licencias-picker__foot {
    margin: 10px 0 0;
    font-size: .74rem;
    color: var(--lp-muted);
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    function syncLicenciasBloque(bloque) {
        if (!bloque || bloque.dataset.licenciasInit === '1') return;
        bloque.dataset.licenciasInit = '1';

        var todas = bloque.querySelector('.js-licencias-todas');
        var lista = bloque.querySelector('.js-licencias-lista');
        var items = bloque.querySelectorAll('.js-licencia-item');
        if (!todas) return;

        function allItemsChecked() {
            return items.length > 0 && Array.from(items).every(function (cb) { return cb.checked; });
        }

        function aplicar() {
            var deshabilitar = todas.checked;
            if (lista) {
                lista.classList.toggle('is-muted', deshabilitar);
            }
            items.forEach(function (cb) {
                cb.disabled = deshabilitar;
                var label = cb.closest('.licencias-picker__item');
                if (label) label.classList.toggle('is-disabled', deshabilitar);
                if (deshabilitar) cb.checked = false;
            });
        }

        todas.addEventListener('change', aplicar);
        items.forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (allItemsChecked()) {
                    todas.checked = true;
                } else {
                    todas.checked = false;
                }
                aplicar();
            });
        });

        if (allItemsChecked()) {
            todas.checked = true;
        }
        aplicar();
    }

    document.querySelectorAll('.js-licencias-bloque').forEach(syncLicenciasBloque);
})();
</script>
@endpush
@endonce
