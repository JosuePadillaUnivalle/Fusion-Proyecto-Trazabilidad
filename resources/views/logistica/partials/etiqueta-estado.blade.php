@php
    $texto = \App\Support\EnvioAsignacionEstadoCatalogo::etiqueta($estado ?? '');
    if ($texto === '' || $texto === ucfirst(str_replace('_', ' ', (string) ($estado ?? '')))) {
        $etiquetas = [
            'planificada' => 'Planificada',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
        ];
        $texto = $etiquetas[$estado ?? ''] ?? $texto;
    }
@endphp
<span class="badge badge-pill {{ $clase ?? 'badge-secondary' }}">{{ $texto }}</span>
