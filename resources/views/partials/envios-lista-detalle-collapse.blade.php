{{-- Lista de envíos dentro de fila expandible --}}
<ul class="list-group list-group-flush mb-0">
    @foreach($lista as $envio)
    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
        <span>
            <strong>{{ $envio['externo_envio_id'] ?? '#'.$envio['id'] }}</strong>
            <span class="text-muted small ml-2">{{ $envio['nombre_remitente'] ?? '' }}</span>
            @if(!empty($envio['estado']))
                <span class="badge badge-light border ml-1">{{ $envio['estado'] }}</span>
            @endif
        </span>
        <a href="{{ url('/envios/'.$envio['id']) }}" class="btn btn-outline-success btn-sm" title="Ver detalle">
            <i class="fas fa-eye mr-1"></i> Detalle
        </a>
    </li>
    @endforeach
</ul>
