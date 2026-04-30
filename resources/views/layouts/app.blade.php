<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Fusion-Proyectos | Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- FONT AWESOME (CDN) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- ADMINLTE (CDN) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    {{-- PALETA EXACTA Y ESTILO DE HEADER / SIDEBAR / FOOTER DE LA MAQUETA --}}
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #4a7c59;
            --accent-color: #e8f5e8;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --text-dark: #1a252f;
            --text-light: #6c757d;
            --border-color: #dee2e6;
        }

        .main-header {
            background: var(--primary-color) !important;
            border-bottom: 3px solid var(--secondary-color);
        }

        .main-header .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .main-header .navbar-nav .nav-link:hover {
            color: #ffffff !important;
        }

        .main-sidebar {
            background: #2d3748 !important;
        }

        .brand-link {
            background: var(--primary-color) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff !important;
        }

        .brand-link .brand-image {
            color: #ffffff;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link {
            color: rgba(255, 255, 255, 0.8);
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
            background: var(--primary-color) !important;
            color: #ffffff;
        }

        .nav-sidebar .nav-item>.nav-link .right {
            margin-left: auto;
        }

        .content-wrapper {
            background: #f8f9fc;
        }

        body {
            background: #f8f9fc;
        }

        .user-panel img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .main-footer {
            background: #ffffff;
            border-top: 1px solid #dee2e6;
            color: var(--text-light);
        }

        .main-footer a {
            color: var(--primary-color);
        }

        .role-badge {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
        }

        .role-badge.admin {
            background: #dc3545;
            color: white;
        }

        .role-badge.agricultor {
            background: #28a745;
            color: white;
        }

        .role-badge.operador {
            background: #17a2b8;
            color: white;
        }

        .role-badge.planta {
            background: #6f42c1;
            color: white;
        }

        .role-badge.transportista {
            background: #fd7e14;
            color: white;
        }

        .role-badge.almacen {
            background: #20c997;
            color: white;
        }
    </style>

    @stack('styles')
</head>

@php
    $authUser = auth()->user();
    $userFullName = $authUser
        ? trim(($authUser->nombre ?? '') . ' ' . ($authUser->apellido ?? ''))
        : 'Usuario';

    if ($authUser && $userFullName === '') {
        $userFullName = $authUser->nombreusuario ?? 'Usuario';
    }

    $userImageUrl = $authUser && $authUser->imagenurl
        ? $authUser->imagenurl
        : asset('images/user.png');

    // Obtener rol del usuario
    $userRole = $authUser ? ($authUser->getRoleNames()->first() ?? 'sin rol') : 'invitado';
    // Fix: Check for 'Admin' (capitalized) as stored in DB.
    $isAdmin = $authUser && ($authUser->hasRole('Admin') || $authUser->hasRole('admin'));

    // Fix: Lowercase for CSS class matching
    $userRoleCss = strtolower($userRole);
@endphp

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        {{-- NAVBAR SUPERIOR --}}
        <nav class="main-header navbar navbar-expand navbar-dark">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 28px; margin-right: 8px;">
                        <span class="font-weight-bold">Fusion-Proyectos</span>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">


                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ $userImageUrl }}" class="user-image img-circle elevation-2" alt="User Image">
                        <span class="d-none d-md-inline">
                            @auth
                                {{ $userFullName }}
                            @else
                                Invitado
                            @endauth
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-primary">
                            <img src="{{ $userImageUrl }}" class="img-circle elevation-2" alt="User Image">
                            <p>
                                @auth
                                    {{ $userFullName }}
                                    <small class="role-badge {{ $userRoleCss }}">{{ ucfirst($userRole) }}</small>
                                @else
                                    Invitado
                                @endauth
                                <small>Fusion-Proyectos · Panel de trazabilidad</small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="{{ route('profile.show') }}" class="btn btn-default btn-flat">Perfil</a>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline float-right">
                                @csrf
                                <button type="submit" class="btn btn-default btn-flat">Salir</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        {{-- SIDEBAR --}}
        <aside class="main-sidebar sidebar-dark-primary elevation-4">

            <a href="{{ route('dashboard') }}" class="brand-link">
                <img src="{{ asset('images/logo.png') }}" alt="Fusion-Proyectos Logo"
                    class="brand-image img-circle elevation-3" style="opacity:.9">
                <span class="brand-text font-weight-light">Fusion-Proyectos</span>
            </a>

            <div class="sidebar">

                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ $userImageUrl }}" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">
                            @auth
                                {{ $userFullName }}
                            @else
                                Invitado
                            @endauth
                        </a>
                        <span class="role-badge {{ $userRoleCss }}">{{ ucfirst($userRole) }}</span>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        {{-- DASHBOARD (todos) --}}
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.panel-*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Inicio</p>
                            </a>
                        </li>

                        {{-- GESTIÓN DE LOTES (todos) --}}
                        @canany(['lotes.view', 'lotes.create', 'lotes.update', 'lotes.delete'])
                        <li class="nav-item {{ request()->routeIs('lotes.*', 'actividades.*') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('lotes.*', 'actividades.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-map-marked-alt"></i>
                                <p>
                                    Lotes y actividades
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @can('lotes.view')
                                <li class="nav-item">
                                    <a href="{{ route('lotes.index') }}"
                                        class="nav-link {{ request()->routeIs('lotes.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Lotes</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('lotes.mapa') }}"
                                        class="nav-link {{ request()->routeIs('lotes.mapa') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Mapa de Lotes</p>
                                    </a>
                                </li>
                                @endcan
                                <li class="nav-item">
                                    <a href="{{ route('actividades.index') }}"
                                        class="nav-link {{ request()->routeIs('actividades.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Actividades</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('actividades.calendario') }}"
                                        class="nav-link {{ request()->routeIs('actividades.calendario') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Calendario</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endcanany

                        {{-- CERTIFICACIONES (fusionado desde Planta) --}}
                        @can('certificaciones.view')
                            <li class="nav-item">
                                <a href="{{ route('certificaciones.index') }}"
                                    class="nav-link {{ request()->routeIs('certificaciones.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-certificate"></i>
                                    <p>Certificaciones</p>
                                </a>
                            </li>
                        @endcan

                        @can('catalogos.view')
                            <li class="nav-item">
                                <a href="{{ route('catalogos.index') }}"
                                    class="nav-link {{ request()->routeIs('catalogos.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-book"></i>
                                    <p>Catálogos</p>
                                </a>
                            </li>
                        @endcan

                        {{-- PRODUCCIÓN (todos) --}}
                        <li class="nav-item {{ request()->routeIs('producciones.*', 'climas.*', 'procesos-planta.*', 'maquinas-planta.*') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('producciones.*', 'climas.*', 'procesos-planta.*', 'maquinas-planta.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-seedling"></i>
                                <p>
                                    Producción
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @unless(auth()->user()?->hasRole('transportista') || auth()->user()?->hasRole('almacen'))
                                <li class="nav-item">
                                    <a href="{{ route('producciones.index') }}"
                                        class="nav-link {{ request()->routeIs('producciones.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Registro de producción</p>
                                    </a>
                                </li>
                                @endunless
                                <li class="nav-item">
                                    <a href="{{ route('climas.index') }}"
                                        class="nav-link {{ request()->routeIs('climas.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Clima</p>
                                    </a>
                                </li>
                                @unless(auth()->user()?->hasRole('agricultor') || auth()->user()?->hasRole('transportista') || auth()->user()?->hasRole('almacen'))
                                <li class="nav-item">
                                    <a href="{{ route('procesos-planta.index') }}"
                                        class="nav-link {{ request()->routeIs('procesos-planta.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Procesos de planta</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('maquinas-planta.index') }}"
                                        class="nav-link {{ request()->routeIs('maquinas-planta.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Máquinas de planta</p>
                                    </a>
                                </li>
                                @endunless
                            </ul>
                        </li>

                        {{-- INVENTARIO (todos pueden ver, solo admin edita) --}}
                        @canany(['inventario.view', 'inventario.create', 'inventario.update', 'inventario.delete'])
                        <li
                            class="nav-item {{ request()->routeIs('insumos.*', 'lote-insumos.*', 'almacenes.*', 'producciones_almacenamiento.*', 'actores-abastecimiento.*', 'recursos-productivos.*', 'almacen-movimientos.*') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('insumos.*', 'lote-insumos.*', 'almacenes.*', 'producciones_almacenamiento.*', 'actores-abastecimiento.*', 'recursos-productivos.*', 'almacen-movimientos.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-warehouse"></i>
                                <p>
                                    Inventario
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('insumos.index') }}"
                                        class="nav-link {{ request()->routeIs('insumos.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Insumos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('actores-abastecimiento.index') }}"
                                        class="nav-link {{ request()->routeIs('actores-abastecimiento.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Actores de abastecimiento</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('recursos-productivos.index') }}"
                                        class="nav-link {{ request()->routeIs('recursos-productivos.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Vista consolidada de recursos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('lote-insumos.index') }}"
                                        class="nav-link {{ request()->routeIs('lote-insumos.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Aplicación de Insumos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('almacenes.index') }}"
                                        class="nav-link {{ request()->routeIs('almacenes.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Almacenes</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('producciones_almacenamiento.index') }}"
                                        class="nav-link {{ request()->routeIs('producciones_almacenamiento.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Almacenamiento</p>
                                    </a>
                                </li>
                                @can('almacen.movimientos.view')
                                <li class="nav-item">
                                    <a href="{{ route('almacen-movimientos.index') }}"
                                        class="nav-link {{ request()->routeIs('almacen-movimientos.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Movimientos de almacén</p>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany



                        {{-- ENVÍOS (todos) --}}
                        @canany(['envios.view', 'envios.create', 'envios.admin.view', 'vehiculos.view', 'transportistas.view', 'direcciones.view', 'reportes.view'])
                        <li class="nav-item {{ request()->routeIs('envios.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('envios.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-truck"></i>
                                <p>
                                    Envíos
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @can('envios.create')
                                <li class="nav-item">
                                    <a href="{{ route('envios.mandar') }}"
                                        class="nav-link {{ request()->routeIs('envios.mandar') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Crear envío</p>
                                    </a>
                                </li>
                                @endcan
                                @can('envios.view')
                                <li class="nav-item">
                                    <a href="{{ route('envios.seguimiento') }}"
                                        class="nav-link {{ request()->routeIs('envios.seguimiento') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Seguimiento de envíos</p>
                                    </a>
                                </li>
                                @endcan
                                @can('envios.admin.view')
                                <li class="nav-item">
                                    <a href="{{ route('envios.admin') }}"
                                        class="nav-link {{ request()->routeIs('envios.admin') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Dashboard logístico</p>
                                    </a>
                                </li>
                                @endcan
                                @can('transportistas.view')
                                <li class="nav-item">
                                    <a href="{{ route('envios.transportistas') }}"
                                        class="nav-link {{ request()->routeIs('envios.transportistas') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Transportistas</p>
                                    </a>
                                </li>
                                @endcan
                                @can('vehiculos.view')
                                <li class="nav-item">
                                    <a href="{{ route('envios.vehiculos') }}"
                                        class="nav-link {{ request()->routeIs('envios.vehiculos') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Vehículos</p>
                                    </a>
                                </li>
                                @endcan
                                @can('direcciones.view')
                                <li class="nav-item">
                                    <a href="{{ route('envios.direcciones') }}"
                                        class="nav-link {{ request()->routeIs('envios.direcciones') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Direcciones</p>
                                    </a>
                                </li>
                                @endcan
                                @can('reportes.view')
                                <li class="nav-item">
                                    <a href="{{ route('envios.reportes-distribucion') }}"
                                        class="nav-link {{ request()->routeIs('envios.reportes-distribucion') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Reportes de distribución</p>
                                    </a>
                                </li>
                                @endcan
                                @can('pedidos.view')
                                    <li class="nav-item {{ request()->routeIs('pedidos.*') ? 'menu-open' : '' }}">
                                        <a href="{{ route('pedidos.index') }}"
                                            class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-clipboard-list"></i>
                                            <p>Pedidos</p>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany

                        {{-- OPERACIÓN LOGÍSTICA --}}
                        @canany(['panel_planta.view', 'panel_transportista.view', 'panel_almacen.view', 'asignaciones.view', 'rutas_multi.view', 'incidentes.view', 'documentos.view'])
                            <li class="nav-header">Operación logística</li>
                            @can('panel_planta.view')
                                <li class="nav-item">
                                    <a href="{{ route('dashboard.panel-planta') }}" class="nav-link {{ request()->routeIs('dashboard.panel-planta') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-industry"></i>
                                        <p>Panel Planta</p>
                                    </a>
                                </li>
                            @endcan
                            @can('panel_transportista.view')
                                <li class="nav-item">
                                    <a href="{{ route('dashboard.panel-transportista') }}" class="nav-link {{ request()->routeIs('dashboard.panel-transportista') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-truck-moving"></i>
                                        <p>Panel Transportista</p>
                                    </a>
                                </li>
                            @endcan
                            @can('panel_almacen.view')
                                <li class="nav-item">
                                    <a href="{{ route('dashboard.panel-almacen') }}" class="nav-link {{ request()->routeIs('dashboard.panel-almacen') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-warehouse"></i>
                                        <p>Panel Almacén</p>
                                    </a>
                                </li>
                            @endcan
                            @can('asignaciones.view')
                                <li class="nav-item">
                                    <a href="{{ route('logistica.asignaciones.index') }}" class="nav-link {{ request()->routeIs('logistica.asignaciones.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-tag"></i>
                                        <p>Asignación múltiple</p>
                                    </a>
                                </li>
                            @endcan
                            @can('rutas_multi.view')
                                <li class="nav-item">
                                    <a href="{{ route('logistica.rutas.index') }}" class="nav-link {{ request()->routeIs('logistica.rutas.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-route"></i>
                                        <p>Rutas multi-entrega</p>
                                    </a>
                                </li>
                            @endcan
                            @can('documentos.view')
                                <li class="nav-item">
                                    <a href="{{ route('logistica.documentos.index') }}" class="nav-link {{ request()->routeIs('logistica.documentos.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-file-signature"></i>
                                        <p>Documentos entrega</p>
                                    </a>
                                </li>
                            @endcan
                            @can('incidentes.view')
                                <li class="nav-item">
                                    <a href="{{ route('logistica.incidentes.index') }}" class="nav-link {{ request()->routeIs('logistica.incidentes.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-exclamation-triangle"></i>
                                        <p>Incidentes</p>
                                    </a>
                                </li>
                            @endcan
                        @endcanany

                        {{-- Ventas y centro de reportes: no-admin con permisos (el admin ve el menú expandido más abajo) --}}
                        @if(!$isAdmin)
                            @can('ventas.view')
                                <li class="nav-item">
                                    <a href="{{ route('ventas.index') }}"
                                        class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-dollar-sign"></i>
                                        <p>Ventas</p>
                                    </a>
                                </li>
                            @endcan
                            @can('reportes.view')
                                <li class="nav-item">
                                    <a href="{{ route('reportes.index') }}"
                                        class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-chart-pie"></i>
                                        <p>Reportes</p>
                                    </a>
                                </li>
                            @endcan
                        @endif

                        {{-- ============================================ --}}
                        {{-- SECCIONES SOLO PARA ADMIN --}}
                        {{-- ============================================ --}}
                        @if($isAdmin)

                            @can('ventas.view')
                                {{-- VENTAS --}}
                                <li class="nav-item {{ request()->routeIs('ventas.*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-dollar-sign"></i>
                                        <p>
                                            Ventas
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('ventas.index') }}"
                                                class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Listado de Ventas</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan

                            {{-- REPORTES (solo admin) --}}
                            <li class="nav-item {{ request()->routeIs('reportes.*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>
                                        Reportes
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.index') }}"
                                            class="nav-link {{ request()->routeIs('reportes.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Centro de Reportes</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.ventas') }}"
                                            class="nav-link {{ request()->routeIs('reportes.ventas') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Ventas</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.inventario') }}"
                                            class="nav-link {{ request()->routeIs('reportes.inventario') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Inventario</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.produccion') }}"
                                            class="nav-link {{ request()->routeIs('reportes.produccion') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Producción</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.climatico') }}"
                                            class="nav-link {{ request()->routeIs('reportes.climatico') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Climático</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('reportes.actividades') }}"
                                            class="nav-link {{ request()->routeIs('reportes.actividades') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Actividades</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- CATÁLOGOS (solo admin) --}}
                            <li
                                class="nav-item {{ request()->routeIs('cultivos.*', 'tipo-actividad.*', 'tipo-insumos.*', 'unidades-medida.*', 'estado-lote-tipos.*', 'estado-lote-insumos.*', 'historial-estados-lote.*', 'prioridades.*', 'tipoalmacenes.*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ request()->routeIs('cultivos.*', 'tipo-actividad.*', 'tipo-insumos.*', 'unidades-medida.*', 'estado-lote-tipos.*', 'estado-lote-insumos.*', 'historial-estados-lote.*', 'prioridades.*', 'tipoalmacenes.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-book-open"></i>
                                    <p>
                                        Catálogos
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('cultivos.index') }}"
                                            class="nav-link {{ request()->routeIs('cultivos.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Cultivos</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('tipo-actividad.index') }}"
                                            class="nav-link {{ request()->routeIs('tipo-actividad.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                        <p>Tipos de actividad</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('tipo-insumos.index') }}"
                                            class="nav-link {{ request()->routeIs('tipo-insumos.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                        <p>Tipos de insumo</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('unidades-medida.index') }}"
                                            class="nav-link {{ request()->routeIs('unidades-medida.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                        <p>Unidades de medida</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('tipoalmacenes.index') }}"
                                            class="nav-link {{ request()->routeIs('tipoalmacenes.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Tipo de Almacén</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('estado-lote-tipos.index') }}"
                                            class="nav-link {{ request()->routeIs('estado-lote-tipos.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                        <p>Estados de lote</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('estado-lote-insumos.index') }}"
                                            class="nav-link {{ request()->routeIs('estado-lote-insumos.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                        <p>Estados de aplicación de insumo</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('historial-estados-lote.index') }}"
                                            class="nav-link {{ request()->routeIs('historial-estados-lote.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                        <p>Historial de estados de lote</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('prioridades.index') }}"
                                            class="nav-link {{ request()->routeIs('prioridades.*') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Prioridades</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {{-- GESTIÓN DE USUARIOS (solo admin) --}}
                            @can('usuarios.view')
                            <li class="nav-item {{ request()->routeIs('gestion.*') ? 'menu-open' : '' }}">
                                <a href="{{ route('gestion.index') }}"
                                    class="nav-link {{ request()->routeIs('gestion.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Gestión de Usuarios</p>
                                </a>
                            </li>
                            @endcan

                        @endif
                        {{-- FIN SECCIONES SOLO ADMIN --}}

                    </ul>
                </nav>
            </div>
        </aside>

        {{-- CONTENT WRAPPER --}}
        <div class="content-wrapper">

            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page_title', 'Dashboard Principal')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumbs')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        {{-- FOOTER --}}
        <footer class="main-footer">
            <strong>
                &copy; {{ date('Y') }}
                <a href="#">Fusion-Proyectos</a>.
            </strong>
            Sistema de Gestión de Producción Agrícola.
            <div class="float-right d-none d-sm-inline-block">
                <b>Versión</b> 1.0.0
            </div>
        </footer>

    </div>

    {{-- JS CDN --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    @stack('scripts')
</body>

</html>