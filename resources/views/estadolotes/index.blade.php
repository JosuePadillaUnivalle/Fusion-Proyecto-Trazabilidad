@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Estados de Lote</h3>
        <a href="{{ route('estadolotes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Estado
        </a>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lote</th>
                    <th>Tipo de Estado</th>
                    <th>Fecha Registro</th>
                    <th>Imagen</th>
                    <th style="width:130px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estados as $e)
                    <tr>
                        <td>{{ $e->estadoid }}</td>
                        <td>{{ $e->lote->nombre ?? '-' }}</td>
                        <td>{{ $e->estadoTipo->nombre ?? '-' }}</td>
                        <td>{{ $e->fecharegistro }}</td>
                        <td>
                            @if($e->imagenurl)
                                <img src="{{ $e->imagenurl }}" width="60" class="img-thumbnail">
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('estadolotes.show', $e) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>

                            <a href="{{ route('estadolotes.edit', $e) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('estadolotes.destroy', $e) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar estado de lote?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-3">No hay estados registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $estados->links() }}
    </div>

</div>
@endsection