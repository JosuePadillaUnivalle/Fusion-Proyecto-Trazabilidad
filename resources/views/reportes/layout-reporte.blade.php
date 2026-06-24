@extends('layouts.app')

@section('title', ($item['title'] ?? 'Reporte').' | AgroFusion')
@section('page_title', $item['title'] ?? 'Reporte')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">{{ $item['title'] ?? 'Reporte' }}</li>
@endsection

@push('styles')
    @include('reportes.partials.estilos')
@endpush

@section('content')
    @include('reportes.partials.shell-open')
    @include('reportes.partials.sin-resultados')
    @yield('rpt_kpis')
    @yield('rpt_body')
    @include('reportes.partials.shell-close')
    @include('reportes.partials.preview-pdf')
@endsection

@push('scripts')
    @include('reportes.partials.export-scripts')
@endpush
