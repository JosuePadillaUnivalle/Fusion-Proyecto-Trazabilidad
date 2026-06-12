@php
    use App\Support\DashboardFiltros;

    $filtros = $filtros ?? DashboardFiltros::desdeRequest(request());
    $cultivos = $cultivos ?? collect();
    $lotes = $lotes ?? collect();
    $estadosLote = $estadosLote ?? collect();
    $mostrarCultivo = $mostrarCultivo ?? false;
    $mostrarLote = $mostrarLote ?? false;
    $mostrarEstadoLote = $mostrarEstadoLote ?? false;
    $mostrarRangoFechas = $mostrarRangoFechas ?? false;
    $actionUrl = $actionUrl ?? url()->current();
    $paramsBase = $filtros->queryParams();
    $cultivoLabel = ($filtros->cultivoId && $cultivos->isNotEmpty())
        ? ($cultivos->firstWhere('cultivoid', $filtros->cultivoId)?->nombre ?? '')
        : '';
    $loteLabel = ($filtros->loteId && $lotes->isNotEmpty())
        ? ($lotes->firstWhere('loteid', $filtros->loteId)?->nombre ?? '')
        : '';
    $usarSelectorCampo = ($mostrarCultivo || $mostrarLote);
@endphp

<style>
.dash-filtros-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem 1.15rem 1.1rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .06);
}
.dash-filtros-card__head {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
    gap: .5rem; margin-bottom: .85rem;
}
.dash-filtros-card__title {
    display: flex; align-items: center; gap: .5rem;
    font-weight: 800; font-size: .92rem; color: #1e293b;
}
.dash-filtros-card__title i {
    width: 30px; height: 30px; border-radius: 8px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .8rem;
}
.dash-filtros-chips { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .85rem; }
.dash-filtros-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .35rem .75rem; border-radius: 999px;
    font-size: .8rem; font-weight: 600; text-decoration: none !important;
    border: 1px solid #cbd5e1; color: #475569; background: #f8fafc;
    transition: all .15s ease;
}
.dash-filtros-chip:hover { border-color: #2563eb; color: #1d4ed8; background: #eff6ff; }
.dash-filtros-chip.is-active {
    background: linear-gradient(135deg, #16a34a, #22c55e);
    border-color: transparent; color: #fff;
    box-shadow: 0 3px 10px rgba(34, 197, 94, .3);
}
.dash-filtros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: .65rem .75rem;
    align-items: end;
}
.dash-filtros-field label {
    display: block; font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    color: #64748b; margin-bottom: .25rem;
}
.dash-filtros-field .form-control {
    border-radius: 8px; font-size: .86rem;
}
.dash-filtros-field select.form-control-sm {
    height: 38px;
    line-height: 1.35;
    padding: .45rem .65rem;
}
.dash-filtros-actions {
    display: flex; flex-wrap: wrap; gap: .5rem; align-items: center;
    margin-top: .75rem; padding-top: .75rem; border-top: 1px solid #f1f5f9;
}
.dash-filtros-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .25rem .6rem; border-radius: 999px;
    background: #eff6ff; color: #1d4ed8; font-size: .75rem; font-weight: 600;
}
.dash-filtros-hint { font-size: .76rem; color: #94a3b8; margin: 0; }
.dash-filtros-field--selector { grid-column: span 2; min-width: 200px; }
.dash-filtros-field--selector .selector-catalogo-wrapper { margin-bottom: 0; }
.dash-filtros-field--selector .selector-catalogo-label { cursor: pointer; font-size: .86rem; }
.dash-filtros-field--selector .input-group .btn { font-size: .78rem; font-weight: 700; white-space: nowrap; }
</style>

<div class="dash-filtros-card" id="dash-filtros-panel">
    <div class="dash-filtros-card__head">
        <div class="dash-filtros-card__title">
            <i class="fas fa-filter"></i>
            <span>¿Qué quieres ver?</span>
        </div>
        @if($filtros->tieneFiltrosActivos())
        <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-undo mr-1"></i>Limpiar todo
        </a>
        @endif
    </div>

    {{-- Atajos de periodo (un clic) --}}
    <div class="dash-filtros-chips" role="group" aria-label="Periodo rápido">
        @foreach(DashboardFiltros::PERIODOS_RAPIDOS as $key => $label)
            @php
                $chipParams = array_filter([
                    'periodo' => $key,
                    'cultivo' => $filtros->cultivoId,
                    'lote' => $filtros->loteId,
                    'estado_lote' => $filtros->estadoLoteId,
                ], fn ($v) => $v !== null && $v !== '');
                $activo = $filtros->periodo === $key && ! $filtros->anioHistorico && ! $filtros->usaRangoPersonalizado();
            @endphp
            <a href="{{ $actionUrl.'?'.http_build_query(array_filter($chipParams)) }}"
               class="dash-filtros-chip {{ $activo ? 'is-active' : '' }}">
                @if($activo)<i class="fas fa-check" style="font-size:.65rem"></i>@endif
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ $actionUrl }}" class="m-0" id="dash-filtros-form">
        <div class="dash-filtros-grid">
            <div class="dash-filtros-field">
                <label for="dash-filtro-anio">Año específico</label>
                <select name="anio" id="dash-filtro-anio" class="form-control form-control-sm">
                    <option value="">Sin filtrar por año</option>
                    @foreach(DashboardFiltros::aniosDisponibles() as $y => $label)
                        <option value="{{ $y }}" @selected($filtros->anioHistorico === $y)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if($mostrarCultivo)
            <div class="dash-filtros-field dash-filtros-field--selector">
                <label>Cultivo</label>
                @include('partials.selector-catalogo', [
                    'id' => 'dash_filtro_cultivo',
                    'name' => 'cultivo',
                    'value' => $filtros->cultivoId ?? '',
                    'labelSelected' => $cultivoLabel,
                    'endpoint' => route('catalogo-selector.cultivos'),
                    'allowEmpty' => true,
                    'emptyLabel' => 'Todos los cultivos',
                    'placeholderEmpty' => 'Todos los cultivos',
                    'title' => 'Filtrar por cultivo',
                    'searchPlaceholder' => 'Nombre o detalle del cultivo…',
                    'searchLabel' => 'Buscar cultivo',
                    'modalIcon' => 'fa-seedling',
                    'rowIcon' => 'fa-seedling',
                    'inputGroup' => true,
                ])
            </div>
            @endif

            @if($mostrarLote)
            <div class="dash-filtros-field dash-filtros-field--selector">
                <label>Lote / parcela</label>
                @include('partials.selector-catalogo', [
                    'id' => 'dash_filtro_lote',
                    'name' => 'lote',
                    'value' => $filtros->loteId ?? '',
                    'labelSelected' => $loteLabel,
                    'endpoint' => route('catalogo-selector.lotes'),
                    'params' => array_filter(['cultivoid' => $filtros->cultivoId]),
                    'allowEmpty' => true,
                    'emptyLabel' => 'Todos los lotes',
                    'placeholderEmpty' => 'Todos los lotes',
                    'title' => 'Filtrar por lote',
                    'searchPlaceholder' => 'Nombre, código TRAZ o ubicación…',
                    'searchLabel' => 'Buscar lote',
                    'modalIcon' => 'fa-map-marked-alt',
                    'rowIcon' => 'fa-map-marked-alt',
                    'colDetalle' => 'Cultivo · código',
                    'inputGroup' => true,
                ])
            </div>
            @endif

            @if($mostrarEstadoLote && $estadosLote->isNotEmpty())
            <div class="dash-filtros-field">
                <label for="dash-filtro-estado">Estado del lote</label>
                <select name="estado_lote" id="dash-filtro-estado" class="form-control form-control-sm">
                    <option value="">Todos</option>
                    @foreach($estadosLote as $e)
                        <option value="{{ $e->estadolotetipoid }}" @selected($filtros->estadoLoteId === (int) $e->estadolotetipoid)>{{ ucfirst($e->nombre) }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($mostrarRangoFechas)
            <div class="dash-filtros-field">
                <label for="dash-filtro-desde">Desde</label>
                <input type="date" name="desde" id="dash-filtro-desde" class="form-control form-control-sm"
                       value="{{ $filtros->desde }}">
            </div>
            <div class="dash-filtros-field">
                <label for="dash-filtro-hasta">Hasta</label>
                <input type="date" name="hasta" id="dash-filtro-hasta" class="form-control form-control-sm"
                       value="{{ $filtros->hasta }}">
            </div>
            @endif

            <input type="hidden" name="periodo" value="{{ $filtros->periodo }}">
        </div>

        <div class="dash-filtros-actions">
            <button type="submit" class="btn btn-primary btn-sm font-weight-bold">
                <i class="fas fa-search mr-1"></i>Actualizar vista
            </button>
            @if($filtros->tieneFiltrosActivos())
            <div class="d-flex flex-wrap" style="gap:.35rem">
                <span class="dash-filtros-badge"><i class="fas fa-clock"></i> {{ $filtros->etiquetaPeriodo() }}</span>
                @if($filtros->cultivoId && $cultivos->isNotEmpty())
                    @php $cNom = $cultivos->firstWhere('cultivoid', $filtros->cultivoId)?->nombre; @endphp
                    @if($cNom)<span class="dash-filtros-badge"><i class="fas fa-seedling"></i> {{ $cNom }}</span>@endif
                @endif
                @if($filtros->loteId && $lotes->isNotEmpty())
                    @php $lNom = $lotes->firstWhere('loteid', $filtros->loteId)?->nombre; @endphp
                    @if($lNom)<span class="dash-filtros-badge"><i class="fas fa-map"></i> {{ $lNom }}</span>@endif
                @endif
            </div>
            @else
            <p class="dash-filtros-hint mb-0">Mostrando resumen de los últimos 6 meses por defecto.</p>
            @endif
        </div>
    </form>
</div>

@if($usarSelectorCampo)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.CatalogoSelector) return;

    var form = document.getElementById('dash-filtros-form');
    var wrapCultivo = document.getElementById('selector_wrap_dash_filtro_cultivo');
    var wrapLote = document.getElementById('selector_wrap_dash_filtro_lote');

    function limpiarBackdropModal() {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        document.querySelectorAll('.modal-backdrop').forEach(function (el) {
            el.remove();
        });
    }

    function aplicarFiltrosDashboard() {
        if (!form) return;
        var modal = document.getElementById('modalSelectorCatalogo');
        var $modal = window.jQuery && modal ? window.jQuery(modal) : null;

        function enviar() {
            limpiarBackdropModal();
            form.submit();
        }

        if ($modal && $modal.hasClass('show')) {
            $modal.one('hidden.bs.modal', enviar);
            $modal.modal('hide');
            return;
        }

        enviar();
    }

    document.querySelectorAll('#dash-filtros-form .selector-catalogo-label').forEach(function (el) {
        el.addEventListener('click', function () {
            var btn = el.closest('.input-group')?.querySelector('[data-selector-open]');
            if (btn) btn.click();
        });
    });

    if (wrapCultivo) {
        wrapCultivo.addEventListener('selector-catalogo:change', function () {
            if (wrapLote && CatalogoSelector.instances.dash_filtro_lote) {
                var cid = wrapCultivo.querySelector('.selector-catalogo-value')?.value || '';
                CatalogoSelector.instances.dash_filtro_lote.params = cid ? { cultivoid: cid } : {};
                if (wrapLote.querySelector('.selector-catalogo-value')?.value) {
                    CatalogoSelector.clear('dash_filtro_lote');
                    return;
                }
            }
            aplicarFiltrosDashboard();
        });
    }

    if (wrapLote) {
        wrapLote.addEventListener('selector-catalogo:change', aplicarFiltrosDashboard);
    }
});
</script>
@endpush
@endif
