@extends('layouts.app')

@section('title', 'Editar envío '.$asignacion->externo_envio_id.' | AgroFusion')
@section('page_title', 'Editar asignación')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <p class="text-muted mb-0">Modifique chofer, vehículo o ruta antes de que el envío llegue a destino.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-edit mr-2"></i>{{ $asignacion->externo_envio_id }}
                        </h3>
                    </div>
                    <form method="POST" action="{{ route('logistica.asignaciones.update', $asignacion) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @include('partials.selector-catalogo', [
                                'id' => 'edit_asignacion_transportista',
                                'name' => 'transportista_usuarioid',
                                'label' => 'Chofer',
                                'icon' => 'fa-id-card',
                                'allowEmpty' => true,
                                'emptyLabel' => '— Sin chofer —',
                                'placeholderEmpty' => 'Sin chofer asignado',
                                'value' => $asignacion->transportista_usuarioid ?? '',
                                'labelSelected' => $asignacion->transportista
                                    ? trim($asignacion->transportista->nombre.' '.($asignacion->transportista->apellido ?? ''))
                                    : '',
                                'endpoint' => route('catalogo-selector.usuarios'),
                                'title' => 'Elegir chofer',
                                'searchPlaceholder' => 'Nombre, correo, teléfono o placa…',
                                'searchLabel' => 'Buscar transportista',
                                'modalIcon' => 'fa-truck',
                                'rowIcon' => 'fa-user-tie',
                                'params' => ['roles' => 'transportista'],
                                'filter' => [
                                    'param' => 'con_vehiculo',
                                    'options' => [
                                        ['value' => '', 'label' => 'Todos'],
                                        ['value' => '1', 'label' => 'Con vehículo'],
                                        ['value' => '0', 'label' => 'Sin vehículo'],
                                    ],
                                ],
                                'help' => 'Ventana flotante con filtros; no abandona esta pantalla.',
                            ])

                            <div class="form-group">
                                <label>Vehículo (placa o referencia)</label>
                                <input type="text" name="vehiculo_ref" class="form-control" maxlength="80"
                                       value="{{ old('vehiculo_ref', $asignacion->vehiculo_ref) }}" placeholder="Ej: SCZ-MOD-01">
                            </div>

                            @include('partials.selector-catalogo', [
                                'id' => 'edit_asignacion_ruta',
                                'name' => 'rutamultientregaid',
                                'label' => 'Ruta de entrega (opcional)',
                                'icon' => 'fa-route',
                                'allowEmpty' => true,
                                'emptyLabel' => '— Sin ruta —',
                                'placeholderEmpty' => 'Sin ruta asignada',
                                'value' => $asignacion->rutamultientregaid ?? '',
                                'labelSelected' => $asignacion->ruta?->nombre ?? '',
                                'endpoint' => route('catalogo-selector.rutas-multi'),
                                'title' => 'Elegir ruta de entrega',
                                'searchPlaceholder' => 'Nombre de ruta o chofer…',
                                'searchLabel' => 'Buscar ruta',
                                'modalIcon' => 'fa-map-signs',
                                'rowIcon' => 'fa-route',
                                'params' => [],
                                'filter' => [
                                    'param' => 'estado',
                                    'options' => [
                                        ['value' => '', 'label' => 'Activas (planificada / en ruta)'],
                                        ['value' => 'planificada', 'label' => 'Planificada'],
                                        ['value' => 'en_ruta', 'label' => 'En ruta'],
                                        ['value' => 'completada', 'label' => 'Completada'],
                                    ],
                                ],
                                'help' => 'Busque y filtre rutas existentes en el catálogo.',
                            ])
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('logistica.asignaciones.show', $asignacion) }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i>Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
