@php
    $lote = $cert->lote;
@endphp

<div class="cert-detalle">
    <div class="text-center mb-4 p-3 rounded" style="background: linear-gradient(135deg, #28a745, #20c997); color: #fff;">
        <div class="small text-uppercase opacity-75 mb-1">Código de certificado</div>
        <div class="h4 font-weight-bold mb-0" style="font-family: ui-monospace, monospace;">{{ $cert->codigo_certificado }}</div>
        <div class="small mt-2"><i class="far fa-clock mr-1"></i>{{ $cert->fecha_certificacion?->format('d/m/Y H:i') ?? '—' }}</div>
    </div>

    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-certificate text-success mr-1"></i>Certificación</h6>
    <table class="table table-sm table-borderless mb-4">
        <tr>
            <td class="text-muted w-40">Emitido por</td>
            <td class="font-weight-bold">
                @if($cert->usuario)
                    {{ trim($cert->usuario->nombre.' '.$cert->usuario->apellido) }}
                    <span class="text-muted small d-block">{{ $cert->usuario->email }}</span>
                @else
                    —
                @endif
            </td>
        </tr>
        @if($cert->observaciones)
            <tr>
                <td class="text-muted align-top">Observaciones</td>
                <td>{{ $cert->observaciones }}</td>
            </tr>
        @endif
    </table>

  @if($lote)
        <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-seedling text-success mr-1"></i>Lote certificado</h6>
        <table class="table table-sm table-borderless mb-3">
            <tr>
                <td class="text-muted w-40">Nombre</td>
                <td class="font-weight-bold">{{ $lote->nombre }}</td>
            </tr>
            <tr>
                <td class="text-muted">ID / Trazabilidad</td>
                <td>
                    #{{ $lote->loteid }}
                    @if($lote->codigo_trazabilidad)
                        <span class="badge badge-light border ml-1">{{ $lote->codigo_trazabilidad }}</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-muted">Cultivo</td>
                <td>{{ $lote->cultivo->nombre ?? '—' }}</td>
            </tr>
            <tr>
                <td class="text-muted">Estado actual</td>
                <td>
                    <span class="badge badge-success">{{ $lote->estadoTipo->nombre ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Ubicación</td>
                <td>{{ $lote->ubicacion ?? '—' }}</td>
            </tr>
            <tr>
                <td class="text-muted">Superficie</td>
                <td>
                    @if($lote->superficie)
                        {{ number_format((float) $lote->superficie, 2) }}
                        {{ $lote->unidadSuperficie->nombre ?? 'ha' }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-muted">Fecha siembra</td>
                <td>{{ $lote->fechasiembra?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            @if($lote->actorAbastecimiento)
                <tr>
                    <td class="text-muted">Actor</td>
                    <td>{{ $lote->actorAbastecimiento->nombre ?? '—' }}</td>
                </tr>
            @endif
        </table>

        @can('lotes.view')
            <a href="{{ route('lotes.show', $lote) }}" class="btn btn-outline-secondary btn-sm btn-block">
                <i class="fas fa-external-link-alt mr-1"></i>Abrir ficha completa del lote
            </a>
        @endcan
    @else
        <div class="alert alert-warning mb-0">El lote asociado (#{{ $cert->loteid }}) ya no está disponible.</div>
    @endif
</div>
