@php
    $actividades = session('agricultor_actividades_pendientes', []);
@endphp

@if(! empty($actividades))
@include('partials.agricultor-tareas-pendientes-modal', [
    'modalId' => 'modalAgricultorActividad',
    'tareas' => $actividades,
    'autoShow' => true,
    'marcarVistas' => true,
])

@php session()->forget('agricultor_actividades_pendientes'); @endphp
@endif
