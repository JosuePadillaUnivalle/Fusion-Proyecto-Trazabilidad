@php
    $lote = $eval->loteProduccionPedido;
    $inspector = $eval->inspector
        ? trim($eval->inspector->nombre.' '.$eval->inspector->apellido)
        : null;
    $codigo = $lote?->codigo_lote ?? ('LP-'.$eval->loteproduccionpedidoid);
@endphp

<style>
    .cert-det-v2__hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 50%, #3b82f6 100%);
        border-radius: 14px;
        color: #fff;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1.1rem;
        box-shadow: 0 8px 24px rgba(30, 58, 95, .2);
    }
    .cert-det-v2__hero-kicker {
        font-size: .68rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .85;
        margin-bottom: .35rem;
    }
    .cert-det-v2__hero-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.2;
        word-break: break-all;
    }
    .cert-det-v2__hero-date {
        font-size: .82rem;
        opacity: .9;
        margin-top: .5rem;
    }
    .cert-det-v2__section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        margin-bottom: .85rem;
    }
    .cert-det-v2__section-title {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: .75rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .cert-det-v2__section-title i { color: #2563eb; }
    .cert-det-v2__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem .85rem;
    }
    @media (max-width: 576px) {
        .cert-det-v2__grid { grid-template-columns: 1fr; }
    }
    .cert-det-v2__field-label {
        display: block;
        font-size: .72rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: .15rem;
    }
    .cert-det-v2__field-value {
        font-size: .92rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.35;
    }
    .cert-det-v2__field-value--muted { font-weight: 500; color: #475569; }
    .cert-det-v2__field--wide { grid-column: 1 / -1; }
    .cert-det-v2__emitido {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .cert-det-v2__avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }
    .cert-det-v2__obs {
        background: #fff;
        border-left: 3px solid #3b82f6;
        border-radius: 0 8px 8px 0;
        padding: .65rem .85rem;
        font-size: .88rem;
        color: #334155;
        margin-top: .5rem;
    }
    .cert-det-v2__actions .btn {
        padding: .5rem 1rem;
        font-weight: 600;
        border-radius: 10px;
    }
    .cert-det-v2__traz {
        font-family: ui-monospace, monospace;
        font-size: .78rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: .15rem .45rem;
        margin-left: .35rem;
    }
</style>

<div class="cert-det-v2">
    <div class="cert-det-v2__hero text-center">
        <div class="cert-det-v2__hero-kicker">Evaluación de lote de planta</div>
        <div class="mb-2">
            @if($eval->esNoConforme())
                <span class="badge badge-warning px-3 py-2" style="font-size:.95rem;">No conforme</span>
            @else
                <span class="badge badge-success px-3 py-2" style="font-size:.95rem;">Certificado</span>
            @endif
        </div>
        <div class="cert-det-v2__hero-code">{{ $codigo }}</div>
        <div class="cert-det-v2__hero-date">
            <i class="far fa-clock mr-1"></i>{{ $eval->fecha_evaluacion?->format('d/m/Y H:i') ?? '—' }}
        </div>
    </div>

    <div class="cert-det-v2__section">
        <div class="cert-det-v2__section-title">
            <i class="fas fa-certificate"></i> Certificación
        </div>
        <div class="cert-det-v2__emitido mb-2">
            <div class="cert-det-v2__avatar">
                {{ $inspector ? mb_strtoupper(mb_substr($inspector, 0, 1)) : '?' }}
            </div>
            <div>
                <span class="cert-det-v2__field-label">Inspector</span>
                <span class="cert-det-v2__field-value d-block">
                    {{ $inspector ?? '—' }}
                </span>
                @if($eval->inspector?->email)
                    <span class="small text-muted">{{ $eval->inspector->email }}</span>
                @endif
            </div>
        </div>
        @if($eval->observaciones)
            <div class="cert-det-v2__obs">
                <span class="cert-det-v2__field-label d-block mb-1">Observaciones</span>
                {{ $eval->observaciones }}
            </div>
        @endif
    </div>

    @if($lote)
        <div class="cert-det-v2__section">
            <div class="cert-det-v2__section-title">
                <i class="fas fa-industry"></i> Lote de producción
            </div>
            <div class="cert-det-v2__grid">
                <div class="cert-det-v2__field--wide">
                    <span class="cert-det-v2__field-label">Nombre</span>
                    <span class="cert-det-v2__field-value">{{ $lote->nombre }}</span>
                </div>
                <div>
                    <span class="cert-det-v2__field-label">Producto</span>
                    <span class="cert-det-v2__field-value">{{ $lote->producto ?? '—' }}</span>
                </div>
                <div>
                    <span class="cert-det-v2__field-label">Código lote</span>
                    <span class="cert-det-v2__field-value">
                        @if($lote->codigo_lote)
                            <span class="cert-det-v2__traz">{{ $lote->codigo_lote }}</span>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div>
                    <span class="cert-det-v2__field-label">Plantilla</span>
                    <span class="cert-det-v2__field-value">{{ $lote->plantillaTransformacion->nombre ?? '—' }}</span>
                </div>
                <div>
                    <span class="cert-det-v2__field-label">Pedido interno</span>
                    <span class="cert-det-v2__field-value">{{ $lote->pedido->numero_solicitud ?? ('#'.$lote->pedidoid) }}</span>
                </div>
                <div>
                    <span class="cert-det-v2__field-label">Resultado</span>
                    @if($eval->esNoConforme())
                        <span class="badge badge-warning px-2 py-1">No conforme — sin almacenaje</span>
                    @else
                        <span class="badge badge-success px-2 py-1">Certificado</span>
                    @endif
                </div>
            </div>
        </div>

        @can('lote_produccion.view')
            <div class="cert-det-v2__actions">
                <a href="{{ route('procesamiento.show', $lote) }}" class="btn btn-outline-primary btn-block">
                    <i class="fas fa-external-link-alt mr-1"></i>Abrir procesamiento del lote
                </a>
            </div>
        @endcan
    @else
        <div class="alert alert-warning mb-0">
            El lote de producción asociado ya no está disponible.
        </div>
    @endif
</div>
