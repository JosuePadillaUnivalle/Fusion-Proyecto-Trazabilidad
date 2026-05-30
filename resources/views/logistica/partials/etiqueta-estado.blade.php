@php
    $etiquetas = [
        'planificada' => 'Planificada',
        'en_ruta' => 'En camino',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
        'asignado' => 'Asignado',
        'entregado' => 'Entregado',
        'pendiente' => 'Pendiente',
    ];
    $texto = $etiquetas[$estado ?? ''] ?? ucfirst(str_replace('_', ' ', (string) ($estado ?? '')));
@endphp
<span class="badge badge-pill {{ $clase ?? 'badge-secondary' }}">{{ $texto }}</span>
