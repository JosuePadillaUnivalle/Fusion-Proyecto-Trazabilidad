@if(!empty($alertas) && $alertas->count())
<div class="card border-0 mb-3" style="border-left:4px solid #10b981 !important; box-shadow:0 4px 14px rgba(16,185,129,.12);">
    <div class="card-body py-3">
        <div class="d-flex align-items-center mb-2" style="gap:.5rem;">
            <i class="fas fa-bell" style="color:#10b981;"></i>
            <strong style="font-size:.92rem;">Tienes {{ $totalAlertas ?? $alertas->count() }} aviso(s) nuevo(s)</strong>
        </div>
        <ul class="list-unstyled mb-0">
            @foreach($alertas as $alerta)
            <li class="mb-2">
                <form action="{{ route('notificaciones.leer', $alerta) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 text-left" style="font-size:.86rem;">
                        <i class="fas fa-circle" style="font-size:.45rem;color:#10b981;vertical-align:middle;"></i>
                        {{ $alerta->titulo }}
                        @if($alerta->mensaje)
                            <span class="text-muted d-block pl-3" style="font-size:.78rem;">{{ Str::limit($alerta->mensaje, 90) }}</span>
                        @endif
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endif
