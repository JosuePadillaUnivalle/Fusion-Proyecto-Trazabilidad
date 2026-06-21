@php
    use App\Support\TipoVehiculoCatalogo;
    use App\Support\TiposLicenciaBolivia;
    use App\Support\VehiculoTamanoCatalogo;

    $tiposLista = TipoVehiculoCatalogo::ordenar(collect($tipos ?? []));
    $embebido = $embebido ?? false;

    $badgeTamano = fn (?string $tamano) => match ($tamano) {
        'pequeno' => 'badge-secondary',
        'mediano' => 'badge-warning',
        'grande' => 'badge-success',
        default => 'badge-light',
    };
@endphp
@if($tiposLista->isNotEmpty())
@push('styles')
<style>
.modulo-env .tv-catalogo {
    margin-top: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    background: #fff;
    overflow: hidden;
}
.modulo-env .tv-catalogo--embebido {
    margin-top: 0;
    border: none;
    border-radius: 0;
}
.modulo-env .tv-catalogo__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.modulo-env .tv-catalogo__title {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #343a40;
}
.modulo-env .tv-catalogo__sub {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
    color: #6c757d;
}
.modulo-env .tv-catalogo__count {
    font-size: 0.75rem;
}
.modulo-env .tv-catalogo__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
}
@media (max-width: 991px) {
    .modulo-env .tv-catalogo__grid { grid-template-columns: 1fr; }
}
.modulo-env .tv-catalogo__item {
    padding: 1rem;
    border-right: 1px solid #dee2e6;
}
.modulo-env .tv-catalogo__grid .tv-catalogo__item:last-child { border-right: none; }
@media (max-width: 991px) {
    .modulo-env .tv-catalogo__item { border-right: none; border-bottom: 1px solid #dee2e6; }
    .modulo-env .tv-catalogo__grid .tv-catalogo__item:last-child { border-bottom: none; }
}
.modulo-env .tv-catalogo__item-head {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.65rem;
    border-bottom: 1px solid #f1f3f5;
}
.modulo-env .tv-catalogo__icon {
    width: 36px;
    height: 36px;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    color: #495057;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}
.modulo-env .tv-catalogo__name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #212529;
    line-height: 1.2;
}
.modulo-env .tv-catalogo__tamano {
    font-size: 0.7rem;
    margin-top: 0.2rem;
}
.modulo-env .tv-catalogo__table {
    width: 100%;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
}
.modulo-env .tv-catalogo__table th {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    padding: 0.25rem 0;
    border: none;
    width: 50%;
}
.modulo-env .tv-catalogo__table td {
    font-weight: 600;
    color: #343a40;
    padding: 0.15rem 0 0.35rem;
    border: none;
}
.modulo-env .tv-catalogo__licencia {
    font-size: 0.78rem;
    color: #6c757d;
    line-height: 1.4;
}
.modulo-env .tv-catalogo__foot {
    padding: 0.65rem 1rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    font-size: 0.78rem;
    color: #6c757d;
    line-height: 1.45;
}
.modulo-env .tv-catalogo__escala {
    margin-top: 0.35rem;
    color: #495057;
    font-size: 0.78rem;
}
.modulo-env .tv-catalogo__escala strong {
    font-weight: 600;
    color: #343a40;
}
</style>
@endpush

<div class="tv-catalogo tipos-vehiculo-catalogo @if($embebido) tv-catalogo--embebido @endif">
    @unless($embebido)
    <div class="tv-catalogo__head">
        <div>
            <h6 class="tv-catalogo__title"><i class="fas fa-book mr-1 text-muted"></i> Catálogo de tipos de vehículo</h6>
            <p class="tv-catalogo__sub">Referencia: pequeño → mediano → grande</p>
        </div>
        <span class="badge badge-secondary tv-catalogo__count">{{ $tiposLista->count() }} tipos</span>
    </div>
    @endunless

    <div class="tv-catalogo__grid">
        @foreach($tiposLista as $tipo)
            @php
                $meta = TipoVehiculoCatalogo::metaUi($tipo->codigo);
                $tamano = $tipo->tamano ?? TipoVehiculoCatalogo::TAMANO_POR_CODIGO[$tipo->codigo] ?? null;
            @endphp
            <article class="tv-catalogo__item">
                <div class="tv-catalogo__item-head">
                    <span class="tv-catalogo__icon">
                        <i class="fas {{ $meta['icon'] }}"></i>
                    </span>
                    <div>
                        <div class="tv-catalogo__name">{{ $tipo->nombre }}</div>
                        @if($tamano)
                            <span class="badge {{ $badgeTamano($tamano) }} tv-catalogo__tamano">
                                {{ VehiculoTamanoCatalogo::etiqueta($tamano) }}
                            </span>
                        @endif
                    </div>
                </div>

                <table class="tv-catalogo__table">
                    <tr>
                        <th>Peso máx.</th>
                        <th>Volumen máx.</th>
                    </tr>
                    <tr>
                        <td>{{ $tipo->capacidad_kg ? number_format((float) $tipo->capacidad_kg, 0, ',', '.').' kg' : '—' }}</td>
                        <td>{{ $tipo->capacidad_m3 ? number_format((float) $tipo->capacidad_m3, 1, ',', '.').' m³' : '—' }}</td>
                    </tr>
                </table>

                <div class="tv-catalogo__licencia">
                    <strong>Licencia mínima:</strong>
                    @if($tipo->licencia_requerida)
                        {{ TiposLicenciaBolivia::etiqueta($tipo->licencia_requerida) }}
                    @else
                        No definida
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <div class="tv-catalogo__foot">
        Cada unidad hereda capacidad y licencia de su tipo.
        <div class="tv-catalogo__escala">
            <strong>Pequeño</strong> · Camioneta
            <span class="text-muted mx-1">→</span>
            <strong>Mediano</strong> · Camión pequeño
            <span class="text-muted mx-1">→</span>
            <strong>Grande</strong> · Camión grande
        </div>
    </div>
</div>
@endif
