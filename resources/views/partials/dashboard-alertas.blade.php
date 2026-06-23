@if(!empty($alertas) && $alertas->count())
<div class="card border-0 mb-3" style="border-left:4px solid #10b981 !important; box-shadow:0 4px 14px rgba(16,185,129,.12);">
    <div class="card-body py-3">
        <div class="d-flex align-items-center justify-content-between mb-2" style="gap:.5rem;">
            <div class="d-flex align-items-center" style="gap:.5rem;">
                <i class="fas fa-bell" style="color:#10b981;"></i>
                <strong style="font-size:.92rem;">Tienes {{ $totalAlertas ?? $alertas->count() }} aviso(s) nuevo(s)</strong>
            </div>
            <form action="{{ route('notificaciones.descartar-todas') }}" method="POST" class="m-0 p-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-link text-muted p-0" title="Cerrar todos los avisos" aria-label="Cerrar todos los avisos" style="line-height:1;">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
        <ul class="list-unstyled mb-0">
            @foreach($alertas as $alerta)
            <li class="mb-2">
                @if($alerta->enlace)
                <form action="{{ route('notificaciones.leer', $alerta) }}" method="POST" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="btn btn-link p-0 text-left border-0 bg-transparent w-100 text-body" style="font-size:.86rem;line-height:1.45;text-decoration:none;">
                        <i class="fas fa-circle" style="font-size:.45rem;color:#10b981;vertical-align:middle;"></i>
                        <span class="font-weight-normal" style="text-decoration:underline;text-decoration-color:rgba(16,185,129,.35);">{{ $alerta->titulo }}</span>
                        @if($alerta->mensaje)
                            <span class="text-muted d-block pl-3" style="font-size:.78rem;text-decoration:none;">{{ Str::limit($alerta->mensaje, 120) }}</span>
                        @endif
                    </button>
                </form>
                @else
                <div>
                    <i class="fas fa-circle" style="font-size:.45rem;color:#10b981;vertical-align:middle;"></i>
                    <span style="font-size:.86rem;">{{ $alerta->titulo }}</span>
                    @if($alerta->mensaje)
                        <span class="text-muted d-block pl-3" style="font-size:.78rem;">{{ Str::limit($alerta->mensaje, 120) }}</span>
                    @endif
                </div>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endif
