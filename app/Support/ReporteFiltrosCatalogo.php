<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Support\PuntoVentaAccess;
use Illuminate\Http\Request;

final class ReporteFiltrosCatalogo
{
    /** @return list<string> */
    public static function clavesParaItem(array $item): array
    {
        return $item['filtros'] ?? [];
    }

    /** @return array<string, array<string, mixed>> */
    public static function definiciones(): array
    {
        return [
            'periodo' => ['tipo' => 'periodo', 'label' => 'Período'],
            'ambito' => ['tipo' => 'select', 'name' => 'ambito', 'label' => 'Ámbito', 'opciones' => 'ambitos'],
            'ambito_pt' => ['tipo' => 'select', 'name' => 'ambito', 'label' => 'Ámbito', 'opciones' => 'ambitos_pt'],
            'estado_envio' => ['tipo' => 'select', 'name' => 'estado_envio', 'label' => 'Estado', 'opciones' => 'estados_envio'],
            'estado_ruta' => ['tipo' => 'select', 'name' => 'estado', 'label' => 'Estado', 'opciones' => 'estados_ruta'],
            'estado_pdv' => ['tipo' => 'select', 'name' => 'estado', 'label' => 'Estado pedido', 'opciones' => 'estados_pdv'],
            'transportista' => ['tipo' => 'select', 'name' => 'transportista', 'label' => 'Transportista', 'opciones' => 'transportistas'],
            'almacen_planta' => ['tipo' => 'select', 'name' => 'almacen_planta', 'label' => 'Origen (planta)', 'opciones' => 'almacenes_planta'],
            'almacen_destino' => ['tipo' => 'select', 'name' => 'almacen_destino', 'label' => 'Destino mayorista', 'opciones' => 'almacenes_mayorista'],
            'punto_venta' => ['tipo' => 'select', 'name' => 'puntoventaid', 'label' => 'Punto de venta', 'opciones' => 'puntos_venta'],
            'solo_criticos' => ['tipo' => 'checkbox', 'name' => 'solo_criticos', 'label' => 'Solo bajo mínimo'],
            'busqueda' => ['tipo' => 'text', 'name' => 'q', 'label' => 'Buscar producto', 'placeholder' => 'Nombre del producto…'],
        ];
    }

    /**
     * @param  list<string>  $claves
     * @return list<array<string, mixed>>
     */
    public static function camposParaItem(array $item): array
    {
        $defs = self::definiciones();
        $campos = [];
        foreach (self::clavesParaItem($item) as $clave) {
            if (isset($defs[$clave])) {
                $campos[] = $defs[$clave];
            }
        }

        return $campos;
    }

    /** @return array<string, array<string, string>> */
    public static function opciones(?Usuario $user): array
    {
        $transportistas = Usuario::query()
            ->where('activo', true)
            ->where('role', 'transportista')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get()
            ->mapWithKeys(fn (Usuario $u) => [
                (string) $u->usuarioid => trim($u->nombre.' '.$u->apellido),
            ]);

        $almacenesPlanta = Almacen::query()
            ->where('activo', true)
            ->where('ambito', AlmacenAmbito::PLANTA)
            ->orderBy('nombre')
            ->pluck('nombre', 'almacenid');

        $almacenesMayorista = Almacen::query()
            ->where('activo', true)
            ->where('ambito', AlmacenAmbito::MAYORISTA)
            ->orderBy('nombre')
            ->pluck('nombre', 'almacenid');

        $puntosVenta = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->where('activo', true)->orderBy('nombre'),
            $user
        )->pluck('nombre', 'puntoventaid');

        return [
            'ambitos' => [
                '' => 'Todos los ámbitos',
                AlmacenAmbito::AGRICOLA => 'Agrícola',
                AlmacenAmbito::PLANTA => 'Planta',
                AlmacenAmbito::MAYORISTA => 'Mayorista',
                AlmacenAmbito::PUNTO_VENTA => 'Punto de venta',
            ],
            'ambitos_pt' => [
                '' => 'Planta y mayorista',
                AlmacenAmbito::PLANTA => 'Planta',
                AlmacenAmbito::MAYORISTA => 'Mayorista',
            ],
            'estados_envio' => ['' => 'Todos los estados'] + self::opcionesEstadosEnvio(),
            'estados_ruta' => ['' => 'Todos los estados'] + RutaDistribucionCatalogo::opcionesFiltroEstado(),
            'estados_pdv' => ['' => 'Todos los estados'] + PedidoDistribucionCatalogo::opcionesFiltroEstado(),
            'transportistas' => ['' => 'Todos los transportistas'] + $transportistas->all(),
            'almacenes_planta' => ['' => 'Todas las plantas'] + $almacenesPlanta->all(),
            'almacenes_mayorista' => ['' => 'Todos los destinos'] + $almacenesMayorista->all(),
            'puntos_venta' => ['' => 'Todos los puntos'] + $puntosVenta->all(),
        ];
    }

    /** @return array<string, mixed> */
    public static function extraer(Request $request, array $item): array
    {
        $filtros = [];
        foreach (self::clavesParaItem($item) as $clave) {
            match ($clave) {
                'periodo' => null,
                'ambito', 'ambito_pt' => $filtros['ambito'] = self::valorTexto($request, 'ambito'),
                'estado_envio' => $filtros['estado_envio'] = self::valorTexto($request, 'estado_envio'),
                'estado_ruta', 'estado_pdv' => $filtros['estado'] = self::valorTexto($request, 'estado'),
                'transportista' => $filtros['transportista'] = self::valorEntero($request, 'transportista'),
                'almacen_planta' => $filtros['almacen_planta'] = self::valorEntero($request, 'almacen_planta'),
                'almacen_destino' => $filtros['almacen_destino'] = self::valorEntero($request, 'almacen_destino'),
                'punto_venta' => $filtros['puntoventaid'] = self::valorEntero($request, 'puntoventaid'),
                'solo_criticos' => $request->boolean('solo_criticos') ? $filtros['solo_criticos'] = true : null,
                'busqueda' => $filtros['q'] = self::valorTexto($request, 'q'),
                default => null,
            };
        }

        return $filtros;
    }

    public static function hayActivos(Request $request, array $item): bool
    {
        return self::extraer($request, $item) !== [];
    }

    /** @return array<string, string> */
    public static function etiquetasActivas(Request $request, array $item, array $opciones = []): array
    {
        $etiquetas = [];
        $defs = self::definiciones();

        foreach (self::clavesParaItem($item) as $clave) {
            if ($clave === 'periodo') {
                continue;
            }

            $def = $defs[$clave] ?? null;
            if ($def === null) {
                continue;
            }

            match ($clave) {
                'ambito', 'ambito_pt' => self::agregarEtiquetaSelect($etiquetas, $def, $request, 'ambito', $opciones),
                'estado_envio' => self::agregarEtiquetaSelect($etiquetas, $def, $request, 'estado_envio', $opciones),
                'estado_ruta', 'estado_pdv' => self::agregarEtiquetaSelect($etiquetas, $def, $request, 'estado', $opciones),
                'transportista' => self::agregarEtiquetaSelect($etiquetas, $def, $request, 'transportista', $opciones),
                'almacen_planta' => self::agregarEtiquetaSelect($etiquetas, $def, $request, 'almacen_planta', $opciones),
                'almacen_destino' => self::agregarEtiquetaSelect($etiquetas, $def, $request, 'almacen_destino', $opciones),
                'punto_venta' => self::agregarEtiquetaSelect($etiquetas, $def, $request, 'puntoventaid', $opciones),
                'solo_criticos' => $request->boolean('solo_criticos') ? $etiquetas[] = $def['label'] : null,
                'busqueda' => self::valorTexto($request, 'q') ? $etiquetas[] = $def['label'].': '.self::valorTexto($request, 'q') : null,
                default => null,
            };
        }

        return $etiquetas;
    }

    private static function valorTexto(Request $request, string $key): ?string
    {
        if (! $request->has($key)) {
            return null;
        }

        $valor = trim($request->string($key)->toString());

        return $valor !== '' ? $valor : null;
    }

    private static function valorEntero(Request $request, string $key): ?int
    {
        if (! $request->has($key)) {
            return null;
        }

        $valor = (int) $request->input($key);

        return $valor > 0 ? $valor : null;
    }

    /** @param  array<string, array<string, string>>  $opciones */
    private static function agregarEtiquetaSelect(array &$etiquetas, array $def, Request $request, string $name, array $opciones): void
    {
        $valor = self::valorTexto($request, $name);
        if ($valor === null) {
            return;
        }

        $lista = $opciones[$def['opciones'] ?? ''] ?? [];
        $etiquetas[] = ($def['label'] ?? $name).': '.($lista[$valor] ?? $valor);
    }

    /** @return array<string, string> */
    private static function opcionesEstadosEnvio(): array
    {
        return [
            'Recibido en planta' => 'Recibido en planta',
            'En transporte hacia planta' => 'En transporte hacia planta',
            'Asignado' => 'Asignado',
            'Pendiente' => 'Pendiente',
            'En ruta' => 'En ruta',
            'Completada' => 'Completada',
            'Planificada' => 'Planificada',
            'Cancelado' => 'Cancelado',
            'PDV' => 'Pedidos PDV (todos)',
        ];
    }
}
