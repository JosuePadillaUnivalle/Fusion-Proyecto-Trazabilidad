<?php

namespace App\Support;

use App\Models\CatalogoTamanoConteo;
use App\Models\Cultivo;
use App\Models\DetallePedido;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\ProduccionAlmacenamiento;
use App\Models\TipoInsumo;
use App\Services\CosechaPresentacionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class PedidoCatalogo
{
    public const EDICION_ASIGNACION_COMPLETA = 'completa';

    public const EDICION_ASIGNACION_SOLO_TRANSPORTISTA = 'solo_transportista';

    public const EDICION_ASIGNACION_NINGUNA = 'ninguna';

    public const ESTADO_INICIAL = 'sin asignacion';

    public const ESTADO_CONFIRMADO = 'confirmado';

    public const ESTADO_RECHAZADO = 'rechazado';

    /** Estados en los que logística puede asignar transportista o avanzar el envío. */
    public static function estadosListosParaLogistica(): array
    {
        return ['confirmado'];
    }

    public static function pendienteAprobacionAgricola(Pedido $pedido): bool
    {
        return in_array($pedido->estado, ['sin asignacion', 'pendiente'], true);
    }

    public static function queryOperativosLogistica(): Builder
    {
        return self::aplicarFiltroLogistica(Pedido::query());
    }

    public static function contarPendientesAgricola(): int
    {
        return self::queryOperativosLogistica()
            ->whereIn('estado', ['sin asignacion', 'pendiente'])
            ->count();
    }

    public static function listoParaLogistica(?Pedido $pedido): bool
    {
        return $pedido !== null
            && in_array($pedido->estado, self::estadosListosParaLogistica(), true);
    }

    public static function puedeAsignarTransportista(Pedido $pedido): bool
    {
        return self::listoParaLogistica($pedido);
    }

    /** El transportista solo opera envíos cuyo pedido ya fue aceptado por producción agrícola. */
    public static function envioOperativoParaTransportista(?EnvioAsignacionMultiple $asignacion): bool
    {
        if ($asignacion === null) {
            return false;
        }

        $asignacion->loadMissing('pedido');

        if ($asignacion->pedido === null) {
            return true;
        }

        return self::listoParaLogistica($asignacion->pedido);
    }

    /**
     * Nivel de edición permitido en logística/asignaciones/edit según pedido y envío.
     */
    public static function nivelEdicionAsignacionEnvio(EnvioAsignacionMultiple $asignacion): string
    {
        if (EnvioAsignacionEstadoCatalogo::llegoADestino($asignacion)) {
            return self::EDICION_ASIGNACION_NINGUNA;
        }

        $estadoEnvio = strtolower(trim((string) ($asignacion->estado ?? '')));
        if (in_array($estadoEnvio, ['en_transporte_planta', 'en_ruta', 'en_transito'], true)) {
            return self::EDICION_ASIGNACION_NINGUNA;
        }

        $pedido = $asignacion->pedido;
        if ($pedido !== null) {
            if (self::pendienteAprobacionAgricola($pedido)) {
                return self::EDICION_ASIGNACION_COMPLETA;
            }

            if (self::listoParaLogistica($pedido)) {
                return self::EDICION_ASIGNACION_SOLO_TRANSPORTISTA;
            }

            return self::EDICION_ASIGNACION_NINGUNA;
        }

        if (in_array($estadoEnvio, ['asignado', 'asignada'], true)) {
            return self::EDICION_ASIGNACION_SOLO_TRANSPORTISTA;
        }

        return self::EDICION_ASIGNACION_COMPLETA;
    }

    public static function puedeEditarAsignacionEnvio(EnvioAsignacionMultiple $asignacion): bool
    {
        return self::nivelEdicionAsignacionEnvio($asignacion) !== self::EDICION_ASIGNACION_NINGUNA;
    }

    public static function etiquetaNivelEdicionAsignacion(string $nivel): string
    {
        return match ($nivel) {
            self::EDICION_ASIGNACION_COMPLETA => 'Puede modificar transportista, vehículo, fecha y puntos de recogida.',
            self::EDICION_ASIGNACION_SOLO_TRANSPORTISTA => 'Solo puede cambiar el transportista (pedido listo para envío).',
            default => '',
        };
    }

    /**
     * Fase logística derivada del envío (prioriza sobre el estado del pedido en UI).
     *
     * @param  array<string, mixed>|null  $logistica
     */
    public static function faseLogistica(?array $logistica): ?string
    {
        if ($logistica === null) {
            return null;
        }
        if (! empty($logistica['recibido_planta'])) {
            return 'recibido_planta';
        }
        if (! empty($logistica['cargado_en_ruta'])) {
            return 'en_camino_planta';
        }

        return null;
    }

    public static function etiquetaFaseLogistica(?string $fase): ?string
    {
        return match ($fase) {
            'en_camino_planta' => 'En camino a planta',
            'recibido_planta' => 'Recibido en planta',
            default => null,
        };
    }

    /**
     * Badge de estado para listados (color único por fase/estado).
     *
     * @param  array<string, mixed>|null  $logistica
     * @return array{clase: string, etiqueta: string}
     */
    public static function badgeEstadoLista(?array $logistica, Pedido $pedido): array
    {
        $fase = self::faseLogistica($logistica);
        if ($fase === 'en_camino_planta') {
            return [
                'clase' => 'pedido-estado-camino',
                'etiqueta' => self::etiquetaFaseLogistica($fase),
                'titulo' => self::etiquetaFaseLogistica($fase),
            ];
        }
        if ($fase === 'recibido_planta') {
            return [
                'clase' => 'pedido-estado-recibido',
                'etiqueta' => self::etiquetaFaseLogistica($fase),
                'titulo' => self::etiquetaFaseLogistica($fase),
            ];
        }

        return match ($pedido->estado) {
            'sin asignacion' => [
                'clase' => 'pedido-estado-agricola',
                'etiqueta' => 'Pendiente agrícola',
                'titulo' => self::etiquetaEstado('sin asignacion'),
            ],
            'pendiente' => [
                'clase' => 'pedido-estado-logistica',
                'etiqueta' => 'Pendiente logística',
                'titulo' => self::etiquetaEstado('pendiente'),
            ],
            'confirmado' => [
                'clase' => 'pedido-estado-confirmado',
                'etiqueta' => 'Listo para envío',
                'titulo' => self::etiquetaEstado('confirmado'),
            ],
            'en produccion' => [
                'clase' => 'pedido-estado-produccion',
                'etiqueta' => 'En producción',
                'titulo' => self::etiquetaEstado('en produccion'),
            ],
            'rechazado' => [
                'clase' => 'pedido-estado-rechazado',
                'etiqueta' => 'Rechazado',
                'titulo' => self::etiquetaEstado('rechazado'),
            ],
            default => [
                'clase' => 'pedido-estado-agricola',
                'etiqueta' => self::etiquetaEstado($pedido->estado),
                'titulo' => self::etiquetaEstado($pedido->estado),
            ],
        };
    }

    /**
     * Pedidos operativos de logística: con al menos un ítem y no internos de planta (INT-*).
     */
    public static function aplicarFiltroLogistica(Builder $query): Builder
    {
        return $query
            ->where('numero_solicitud', 'not like', 'INT-%')
            ->whereHas('detalles', fn (Builder $d) => $d->where('cantidad', '>', 0));
    }

    public static function esPedidoInternoPlanta(Pedido $pedido): bool
    {
        return str_starts_with((string) $pedido->numero_solicitud, 'INT-');
    }

    public static function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            'sin asignacion' => 'Pendiente agrícola',
            'pendiente' => 'Pendiente logística',
            'confirmado' => 'Aceptado, listo para envío',
            'en produccion' => 'En producción',
            'rechazado' => 'Rechazado',
            default => ucfirst($estado),
        };
    }

    /**
     * Opciones para selects de estado sin etiquetas duplicadas.
     *
     * @return array<string, string>
     */
    public static function opcionesEstadoEnSelector(?Pedido $pedido = null): array
    {
        $opciones = [];
        foreach (['sin asignacion', 'pendiente', 'confirmado', 'en produccion', 'rechazado'] as $estado) {
            if ($pedido !== null
                && in_array($estado, ['confirmado', 'en produccion'], true)
                && self::pendienteAprobacionAgricola($pedido)) {
                continue;
            }
            $opciones[$estado] = self::etiquetaEstado($estado);
        }

        return $opciones;
    }

    /**
     * Opciones para el formulario de pedido: insumos agrícolas (material de siembra),
     * cosechas en almacén y cultivos de producción. Sin filtro por rol ni almacén del usuario.
     *
     * @return Collection<int, array{value: string, label: string, cultivo: string, origen: string}>
     */
    public static function opcionesProductoPedido(): Collection
    {
        $opciones = collect();

        foreach (self::insumosMaterialSiembraGlobales() as $insumo) {
            $almacen = $insumo->almacen?->nombre;
            $stock = number_format((float) $insumo->stock, 2);
            $unidad = $insumo->unidadMedida?->abreviatura ?? 'kg';
            $meta = trim(collect([$almacen, "Stock: {$stock} {$unidad}"])->filter()->implode(' · '));

            $opciones->push([
                'value' => 'insumo:'.$insumo->insumoid,
                'label' => $insumo->nombre.($meta ? " ({$meta})" : ''),
                'cultivo' => self::cultivoDesdeInsumo($insumo),
                'origen' => 'insumo',
            ]);
        }

        foreach (self::cosechasAgricolasDisponibles() as $cosecha) {
            $cultivo = $cosecha->produccion?->lote?->cultivo?->nombre ?? 'Cultivo';
            $lote = $cosecha->produccion?->lote?->nombre ?? 'Lote';
            $almacen = $cosecha->almacen?->nombre ?? 'Almacén agrícola';
            $cantidad = number_format((float) $cosecha->cantidad, 2);
            $unidad = $cosecha->unidadMedida?->abreviatura ?? 'kg';

            $opciones->push([
                'value' => 'cosecha:'.$cosecha->produccionalmacenamientoid,
                'label' => "{$cultivo} — {$lote} ({$almacen} · {$cantidad} {$unidad} disponibles)",
                'cultivo' => $cultivo,
                'origen' => 'cosecha',
            ]);
        }

        if ($opciones->isEmpty()) {
            foreach (Cultivo::query()->orderBy('nombre')->get() as $cultivo) {
                $opciones->push([
                    'value' => 'cultivo:'.$cultivo->cultivoid,
                    'label' => $cultivo->nombre.' (cultivo de producción agrícola)',
                    'cultivo' => $cultivo->nombre,
                    'origen' => 'cultivo',
                ]);
            }
        }

        return $opciones->unique('value')->values();
    }

    /** @return Collection<int, Insumo> */
    public static function insumosMaterialSiembraGlobales(): Collection
    {
        InsumoCatalogo::asegurarCatalogosBase();

        $tipoIds = self::tiposMaterialSiembraIds();
        if ($tipoIds->isEmpty()) {
            return collect();
        }

        return Insumo::query()
            ->with(['tipo', 'unidadMedida', 'almacen', 'actorAbastecimiento'])
            ->whereIn('tipoinsumoid', $tipoIds)
            ->orderBy('nombre')
            ->get();
    }

    /** @return Collection<int, ProduccionAlmacenamiento> */
    public static function cosechasAgricolasDisponibles(): Collection
    {
        return ProduccionAlmacenamiento::query()
            ->with([
                'produccion.lote.cultivo',
                'produccion.lote.catalogoTamanoConteo',
                'catalogoTamanoConteo',
                'unidadMedida',
                'almacen',
            ])
            ->whereNull('fechasalida')
            ->where('cantidad', '>', 0)
            ->whereHas('almacen', fn ($q) => AlmacenAmbito::scope($q, AlmacenAmbito::AGRICOLA))
            ->orderByDesc('fechaentrada')
            ->get();
    }

    /** @return Collection<int, int> */
    private static function tiposMaterialSiembraIds(): Collection
    {
        return TipoInsumo::query()
            ->get()
            ->filter(function (TipoInsumo $tipo) {
                $slug = InsumoCatalogo::slugFromNombreTipo($tipo->nombre);
                if ($slug === 'material_siembra') {
                    return true;
                }

                $nombre = mb_strtolower(trim($tipo->nombre));

                return str_contains($nombre, 'siembra') || str_contains($nombre, 'semilla');
            })
            ->pluck('tipoinsumoid');
    }

    public static function generarNumeroSolicitud(): string
    {
        $fecha = now()->format('Ymd');
        $prefijo = "PED-{$fecha}-";
        $secuencia = Pedido::query()
            ->where('numero_solicitud', 'like', $prefijo.'%')
            ->count() + 1;

        return $prefijo.str_pad((string) $secuencia, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Resuelve la referencia del producto seleccionado en el formulario.
     *
     * @return array{insumoid: ?int, cultivo: string}
     */
    public static function resolverProductoPedido(string $productoRef): array
    {
        if (str_starts_with($productoRef, 'insumo:')) {
            $insumo = Insumo::query()->with('tipo')->findOrFail((int) substr($productoRef, 7));
            $slug = InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre);
            $nombre = mb_strtolower(trim($insumo->tipo?->nombre ?? ''));
            if ($slug !== 'material_siembra' && ! str_contains($nombre, 'siembra') && ! str_contains($nombre, 'semilla')) {
                throw new \InvalidArgumentException('El insumo seleccionado no es Material de Siembra.');
            }

            return [
                'insumoid' => $insumo->insumoid,
                'cultivo' => self::cultivoDesdeInsumo($insumo),
            ];
        }

        if (str_starts_with($productoRef, 'cosecha:')) {
            $cosecha = ProduccionAlmacenamiento::query()
                ->with(['produccion.lote.cultivo'])
                ->findOrFail((int) substr($productoRef, 8));

            return [
                'insumoid' => null,
                'cultivo' => $cosecha->produccion?->lote?->cultivo?->nombre ?? 'Cultivo',
            ];
        }

        if (str_starts_with($productoRef, 'cultivo:')) {
            $cultivo = Cultivo::query()->findOrFail((int) substr($productoRef, 8));

            return [
                'insumoid' => null,
                'cultivo' => $cultivo->nombre,
            ];
        }

        throw new \InvalidArgumentException('Producto de pedido no válido.');
    }

    /** Insumo con calibres logísticos asociado a la referencia del formulario (insumo, cosecha o cultivo). */
    public static function insumoIdParaCalibres(string $productoRef): ?int
    {
        if (str_starts_with($productoRef, 'insumo:')) {
            $id = (int) substr($productoRef, 7);

            return self::resolverInsumoIdConCalibres($id) ?? $id;
        }

        $cultivoNombre = null;
        if (str_starts_with($productoRef, 'cosecha:')) {
            $cosecha = ProduccionAlmacenamiento::query()
                ->with(['produccion.lote.cultivo', 'catalogoTamanoConteo'])
                ->find((int) substr($productoRef, 8));
            if ($cosecha?->catalogotamanoconteoid && $cosecha->catalogoTamanoConteo) {
                return (int) $cosecha->catalogoTamanoConteo->insumoid;
            }
            $cultivoNombre = $cosecha?->produccion?->lote?->cultivo?->nombre;
        } elseif (str_starts_with($productoRef, 'cultivo:')) {
            $cultivoNombre = Cultivo::query()->find((int) substr($productoRef, 8))?->nombre;
        }

        if ($cultivoNombre === null || trim($cultivoNombre) === '') {
            return null;
        }

        CalibresVerdurasCatalogo::sincronizarParaNombreCultivo($cultivoNombre);

        $insumoId = self::insumoVerduraPorNombreCultivo($cultivoNombre)?->insumoid
            ?? self::insumoPorNombreCultivo($cultivoNombre)?->insumoid;

        return $insumoId !== null ? (int) $insumoId : null;
    }

    /** Insumo verdura por raíz del cultivo (con o sin calibres). */
    public static function insumoPorNombreCultivo(string $cultivoNombre): ?Insumo
    {
        $raiz = mb_strtolower(trim(explode(' ', trim($cultivoNombre))[0] ?? ''));
        if ($raiz === '') {
            return null;
        }

        return Insumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%'.$raiz.'%'])
            ->orderBy('insumoid')
            ->first();
    }

    /**
     * Insumos relacionados (misma especie) para buscar calibres compartidos — p. ej. «Papa» y «Papa Rubiola».
     *
     * @return list<int>
     */
    public static function insumoIdsRelacionadosParaCalibres(int $insumoId): array
    {
        $insumo = Insumo::query()->find($insumoId);
        if ($insumo === null) {
            return [];
        }

        $ids = [$insumoId];
        $raiz = mb_strtolower(trim(explode(' ', self::cultivoDesdeInsumo($insumo))[0] ?? ''));
        if ($raiz === '') {
            $raiz = mb_strtolower(trim(explode(' ', (string) $insumo->nombre)[0] ?? ''));
        }

        if ($raiz !== '') {
            $relacionados = Insumo::query()
                ->whereRaw('LOWER(nombre) LIKE ?', ['%'.$raiz.'%'])
                ->pluck('insumoid')
                ->map(fn ($id) => (int) $id)
                ->all();
            $ids = array_values(array_unique(array_merge($ids, $relacionados)));
        }

        return $ids;
    }

    /**
     * Calibres activos para un producto del wizard (insumo, cosecha o cultivo).
     *
     * @return list<array<string, mixed>>
     */
    public static function listarCalibresParaProducto(string $productoRef, ?int $tipoEmpaqueId = null): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('catalogo_tamano_conteo')) {
            return [];
        }

        $insumoId = self::insumoIdParaCalibres($productoRef);
        if ($insumoId === null) {
            return [];
        }

        CalibresVerdurasCatalogo::sincronizarParaInsumo($insumoId);

        $insumoIds = self::insumoIdsRelacionadosParaCalibres($insumoId);

        $base = \App\Models\CatalogoTamanoConteo::query()
            ->with(['insumo:insumoid,nombre', 'tipoEmpaque'])
            ->whereIn('insumoid', $insumoIds)
            ->where('activo', true)
            ->orderBy('nombre');

        $coleccion = $base->get();

        $deduplicada = self::deduplicarCalibresPorNombre($coleccion, $insumoId);

        return $deduplicada->map(fn (\App\Models\CatalogoTamanoConteo $c) => [
            'id' => (int) $c->catalogotamanoconteoid,
            'id_producto' => (int) $c->insumoid,
            'producto' => $c->insumo?->nombre,
            'nombre' => $c->nombre,
            'conteo_por_empaque' => (int) $c->conteo_por_empaque,
            'peso_promedio_kg' => (float) $c->peso_promedio_kg,
            'peso_promedio_unidad' => (float) $c->peso_promedio_kg,
            'id_tipo_empaque' => $c->tipoempaqueid,
            'tipo_empaque' => $c->tipoEmpaque?->nombre,
        ])->values()->all();
    }

    /**
     * Un calibre por nombre (p. ej. una Mediana, una Pequeña) — prioriza el insumo principal.
     *
     * @param  \Illuminate\Support\Collection<int, CatalogoTamanoConteo>  $coleccion
     * @return \Illuminate\Support\Collection<int, CatalogoTamanoConteo>
     */
    private static function deduplicarCalibresPorNombre(\Illuminate\Support\Collection $coleccion, int $insumoPreferido): \Illuminate\Support\Collection
    {
        $ordenados = $coleccion->sortBy([
            fn (CatalogoTamanoConteo $c) => (int) $c->insumoid === $insumoPreferido ? 0 : 1,
            fn (CatalogoTamanoConteo $c) => self::ordenCalibrePorNombre($c->nombre),
            fn (CatalogoTamanoConteo $c) => mb_strtolower(trim($c->nombre)),
        ])->values();

        $vistos = [];
        $resultado = collect();

        foreach ($ordenados as $calibre) {
            $clave = mb_strtolower(trim($calibre->nombre));
            if ($clave === '' || isset($vistos[$clave])) {
                continue;
            }
            $vistos[$clave] = true;
            $resultado->push($calibre);
        }

        return $resultado->sortBy(fn (CatalogoTamanoConteo $c) => self::ordenCalibrePorNombre($c->nombre))->values();
    }

    private static function ordenCalibrePorNombre(string $nombre): int
    {
        $n = mb_strtolower($nombre);

        return match (true) {
            str_contains($n, 'peque') => 1,
            str_contains($n, 'median') => 2,
            str_contains($n, 'grand') => 3,
            str_contains($n, 'estándar') || str_contains($n, 'estandar') => 4,
            default => 5,
        };
    }

    private static function resolverInsumoIdConCalibres(int $insumoId): ?int
    {
        foreach (self::insumoIdsRelacionadosParaCalibres($insumoId) as $candidato) {
            $tiene = \App\Models\CatalogoTamanoConteo::query()
                ->where('insumoid', $candidato)
                ->where('activo', true)
                ->exists();
            if ($tiene) {
                return $candidato;
            }
        }

        return null;
    }

    public static function insumoVerduraPorNombreCultivo(string $cultivoNombre): ?Insumo
    {
        $raiz = mb_strtolower(trim(explode(' ', trim($cultivoNombre))[0] ?? ''));
        if ($raiz === '') {
            return null;
        }

        if (! \Illuminate\Support\Facades\Schema::hasTable('catalogo_tamano_conteo')) {
            return null;
        }

        return Insumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%'.$raiz.'%'])
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('catalogo_tamano_conteo')
                    ->whereColumn('catalogo_tamano_conteo.insumoid', 'insumo.insumoid')
                    ->where('catalogo_tamano_conteo.activo', true);
            })
            ->orderBy('insumoid')
            ->first();
    }

    /**
     * Stock disponible en kilogramos (base logística del wizard).
     */
    public static function stockDisponibleProductoPedido(string $productoRef, ?int $almacenId = null): ?float
    {
        if (str_starts_with($productoRef, 'insumo:')) {
            $insumo = Insumo::query()->with('unidadMedida')->find((int) substr($productoRef, 7));
            if ($insumo === null) {
                return null;
            }
            if ($almacenId !== null && (int) $insumo->almacenid !== $almacenId) {
                return 0.0;
            }

            return round(app(\App\Services\AlmacenCapacidadService::class)->convertirAKg(
                (float) $insumo->stock,
                $insumo->unidadMedida
            ), 4);
        }

        if (str_starts_with($productoRef, 'cosecha:')) {
            $cosecha = ProduccionAlmacenamiento::query()
                ->with([
                    'unidadMedida',
                    'catalogoTamanoConteo',
                    'produccion.lote.catalogoTamanoConteo',
                    'produccion.unidadMedida',
                ])
                ->find((int) substr($productoRef, 8));
            if ($cosecha === null) {
                return null;
            }
            if ($almacenId !== null && (int) $cosecha->almacenid !== $almacenId) {
                return 0.0;
            }

            return self::stockKgCosecha($cosecha);
        }

        return null;
    }

    public static function stockKgCosecha(ProduccionAlmacenamiento $cosecha): float
    {
        $cosecha->loadMissing([
            'unidadMedida',
            'catalogoTamanoConteo',
            'produccion.lote.catalogoTamanoConteo',
            'produccion.unidadMedida',
        ]);

        $capacidad = app(\App\Services\AlmacenCapacidadService::class);
        $abbr = mb_strtolower(trim($cosecha->unidadMedida?->abreviatura ?? 'kg'));
        $esUnidad = in_array($abbr, ['und', 'un', 'u'], true)
            || str_contains($abbr, 'und')
            || str_contains($abbr, 'unidad');

        if ($esUnidad) {
            $calibre = $cosecha->catalogoTamanoConteo ?? $cosecha->produccion?->lote?->catalogoTamanoConteo;
            if ($calibre === null) {
                $calibreId = self::calibreSugeridoIdParaProducto('cosecha:'.$cosecha->produccionalmacenamientoid);
                if ($calibreId) {
                    $calibre = CatalogoTamanoConteo::query()->find($calibreId);
                }
            }
            $unidades = (int) ($cosecha->cantidad_unidades ?? round((float) $cosecha->cantidad));
            if ($calibre && (float) $calibre->peso_promedio_kg > 0 && $unidades > 0) {
                return round($unidades * (float) $calibre->peso_promedio_kg, 4);
            }
        }

        $presentacion = app(\App\Services\CosechaPresentacionService::class)->paraAlmacenamiento($cosecha);
        $kg = (float) ($presentacion['kg'] ?? 0);
        if ($kg > 0) {
            return round($kg, 4);
        }

        return round($capacidad->convertirAKg((float) $cosecha->cantidad, $cosecha->unidadMedida), 4);
    }

    /** Reutiliza calibre ya registrado en cosecha, lote o siembra previa del mismo producto. */
    public static function calibreSugeridoIdParaProducto(string $productoRef): ?int
    {
        if (! Schema::hasTable('catalogo_tamano_conteo')) {
            return null;
        }

        if (str_starts_with($productoRef, 'cosecha:')) {
            $cosecha = ProduccionAlmacenamiento::query()
                ->with(['produccion.lote'])
                ->find((int) substr($productoRef, 8));
            if ($cosecha?->catalogotamanoconteoid) {
                return (int) $cosecha->catalogotamanoconteoid;
            }
            if ($cosecha?->produccion?->lote?->catalogotamanoconteoid) {
                return (int) $cosecha->produccion->lote->catalogotamanoconteoid;
            }
        }

        if (str_starts_with($productoRef, 'insumo:')) {
            $insumoId = (int) substr($productoRef, 7);
            CalibresVerdurasCatalogo::sincronizarParaInsumo($insumoId);

            $lote = \App\Models\Lote::query()
                ->where('insumosemillaid', $insumoId)
                ->whereNotNull('catalogotamanoconteoid')
                ->orderByDesc('loteid')
                ->first();
            if ($lote?->catalogotamanoconteoid) {
                return (int) $lote->catalogotamanoconteoid;
            }

            $ctx = app(\App\Services\PlanificacionCosechaService::class)->contexto($insumoId);

            return isset($ctx['calibre_default_id']) ? (int) $ctx['calibre_default_id'] : null;
        }

        if (str_starts_with($productoRef, 'cultivo:')) {
            $cultivoId = (int) substr($productoRef, 8);

            $lote = \App\Models\Lote::query()
                ->where('cultivoid', $cultivoId)
                ->whereNotNull('catalogotamanoconteoid')
                ->orderByDesc('loteid')
                ->first();
            if ($lote?->catalogotamanoconteoid) {
                return (int) $lote->catalogotamanoconteoid;
            }

            $cosecha = ProduccionAlmacenamiento::query()
                ->whereHas('produccion.lote', fn ($q) => $q->where('cultivoid', $cultivoId))
                ->whereNotNull('catalogotamanoconteoid')
                ->orderByDesc('fechaentrada')
                ->first();
            if ($cosecha?->catalogotamanoconteoid) {
                return (int) $cosecha->catalogotamanoconteoid;
            }

            $nombre = Cultivo::query()->find($cultivoId)?->nombre;
            if ($nombre) {
                $insumoId = CalibresVerdurasCatalogo::sincronizarParaNombreCultivo($nombre);
                if ($insumoId) {
                    $ctx = app(\App\Services\PlanificacionCosechaService::class)->contexto($insumoId);

                    return isset($ctx['calibre_default_id']) ? (int) $ctx['calibre_default_id'] : null;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $detalles
     * @param  array<int, array<string, mixed>>  $recogidasExtra
     * @return array<string, string>|null
     */
    public static function validarStockDetallesPedido(
        array $detalles,
        ?int $origenAlmacenId,
        array $recogidasExtra = []
    ): ?array {
        foreach ($detalles as $idx => $detalle) {
            $productoRef = (string) ($detalle['producto_ref'] ?? '');
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $almacenId = $idx === 0
                ? ($origenAlmacenId !== null && $origenAlmacenId > 0 ? $origenAlmacenId : null)
                : (isset($recogidasExtra[$idx - 1]['almacenid'])
                    ? (int) $recogidasExtra[$idx - 1]['almacenid']
                    : null);

            $stock = self::stockDisponibleProductoPedido($productoRef, $almacenId);

            if ($stock === null) {
                if ($almacenId !== null) {
                    return [
                        'detalles.'.$idx.'.producto_ref' => 'Seleccione un producto con stock registrado en el almacén de recogida.',
                    ];
                }

                continue;
            }

            if ($cantidad > $stock + 0.0001) {
                $etiqueta = $idx === 0 ? 'recogida 1' : 'recogida '.($idx + 1);

                return [
                    'detalles.'.$idx.'.cantidad' => 'La cantidad supera el stock disponible ('.number_format($stock, 2, '.', '').' kg) en la '.$etiqueta.'.',
                ];
            }
        }

        return null;
    }

    /**
     * Exige empaque/presentación (@carga:…) en cada ítem del envío hacia planta.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     * @return array<string, string>|null
     */
    public static function validarPresentacionDetallesPedido(array $detalles): ?array
    {
        $errores = [];

        foreach ($detalles as $idx => $detalle) {
            $observaciones = trim((string) ($detalle['observaciones'] ?? ''));
            $presentacion = self::descripcionEmpaqueDetalle($observaciones);

            if ($presentacion === null || $presentacion === '') {
                $errores['detalles.'.$idx.'.observaciones'] =
                    'Indique el tipo de empaque y la presentación del producto antes de continuar.';
            }
        }

        return $errores === [] ? null : $errores;
    }

    /** Cultivo de producción agrícola vinculado al insumo de material de siembra. */
    public static function cultivoDesdeInsumo(Insumo $insumo): string
    {
        $nombreInsumo = mb_strtolower(trim($insumo->nombre));

        $cultivo = Cultivo::query()
            ->get()
            ->first(function (Cultivo $c) use ($nombreInsumo) {
                $nombreCultivo = mb_strtolower(trim($c->nombre));

                return $nombreCultivo !== '' && str_contains($nombreInsumo, $nombreCultivo);
            });

        if ($cultivo) {
            return $cultivo->nombre;
        }

        $limpio = preg_replace('/^(semilla\s+certificada|semilla|material de siembra)\s+/iu', '', $insumo->nombre);

        return trim((string) $limpio) ?: $insumo->nombre;
    }

    /** Prefijo interno para empaque/carga en observaciones del detalle. */
    public const PREFIJO_META_CARGA = '@carga:';

    public const SUFIJO_META_CARGA = '@';

    /**
     * Texto de empaque/unidades para guardar en observaciones del ítem.
     */
    public static function construirMetaCargaObservaciones(
        string $forma,
        ?string $cantidadPedido,
        ?string $nombreEmpaque,
        ?string $nombreCalibre,
        ?string $observacionesUsuario = null
    ): ?string {
        $cantidadPedido = trim((string) $cantidadPedido);
        $meta = null;

        if ($forma === 'empaques' && $cantidadPedido !== '' && $nombreEmpaque) {
            $meta = $cantidadPedido.' empaques · '.$nombreEmpaque;
            if ($nombreCalibre) {
                $meta .= ' · '.$nombreCalibre;
            }
        } elseif ($forma === 'unidades' && $cantidadPedido !== '') {
            $meta = $cantidadPedido.' unidades';
            if ($nombreCalibre) {
                $meta .= ' · '.$nombreCalibre;
            }
        } elseif ($forma === 'kg' && $cantidadPedido !== '' && $nombreEmpaque) {
            $meta = $cantidadPedido.' kg · '.$nombreEmpaque;
            if ($nombreCalibre) {
                $meta .= ' · '.$nombreCalibre;
            }
        }

        if ($meta === null) {
            return $observacionesUsuario !== null && trim($observacionesUsuario) !== ''
                ? trim($observacionesUsuario)
                : null;
        }

        $usuario = trim((string) $observacionesUsuario);
        $bloque = self::PREFIJO_META_CARGA.$meta.self::SUFIJO_META_CARGA;

        return $usuario !== '' ? $bloque.' '.$usuario : $bloque;
    }

    /**
     * Empaque o presentación legible a partir de observaciones del detalle.
     */
    public static function descripcionEmpaqueDetalle(?string $observaciones): ?string
    {
        if ($observaciones === null || trim($observaciones) === '') {
            return null;
        }

        if (preg_match('/'.preg_quote(self::PREFIJO_META_CARGA, '/').'(.+?)'.preg_quote(self::SUFIJO_META_CARGA, '/').'/s', $observaciones, $coincidencias)) {
            return trim($coincidencias[1]);
        }

        $primeraLinea = trim(strtok($observaciones, "\n"));
        if ($primeraLinea === '') {
            return null;
        }

        if (preg_match('/^\d+[\d.,]*\s+(empaques?|unidades)\b/iu', $primeraLinea)) {
            return $primeraLinea;
        }

        return null;
    }

    public static function observacionesUsuarioDetalle(?string $observaciones): ?string
    {
        if ($observaciones === null || trim($observaciones) === '') {
            return null;
        }

        if (preg_match('/'.preg_quote(self::PREFIJO_META_CARGA, '/').'.+?'.preg_quote(self::SUFIJO_META_CARGA, '/').'\s*(.*)/s', $observaciones, $coincidencias)) {
            $resto = trim($coincidencias[1]);

            return $resto !== '' ? $resto : null;
        }

        if (self::descripcionEmpaqueDetalle($observaciones) !== null) {
            $lineas = preg_split("/\r\n|\n|\r/", $observaciones);
            $resto = trim(implode("\n", array_slice($lineas, 1)));

            return $resto !== '' ? $resto : null;
        }

        return trim($observaciones);
    }

    /**
     * Presentación completa de un ítem de pedido: empaque, unidades estimadas y texto para UI.
     *
     * @return array{
     *     kg: float,
     *     kg_fmt: string,
     *     empaque: ?string,
     *     empaques: ?int,
     *     unidades: ?int,
     *     unidades_fmt: ?string,
     *     linea: string,
     *     linea_corta: string
     * }
     */
    public static function presentacionDetalle(DetallePedido $detalle): array
    {
        $detalle->loadMissing([
            'cosechaAlmacen.catalogoTamanoConteo.tipoEmpaque',
            'insumo',
        ]);

        $kg = (float) $detalle->cantidad;
        $kgFmt = number_format($kg, 2, ',', '.');
        $meta = self::descripcionEmpaqueDetalle($detalle->observaciones);

        $calibre = $detalle->cosechaAlmacen?->catalogoTamanoConteo
            ?? self::resolverCalibrePorCultivo($detalle->cultivo_personalizado);

        $estimacion = app(CosechaPresentacionService::class)->desdeKg($kg, $calibre);

        $empaqueTexto = self::formatearTextoEmpaqueDetalle($meta, $estimacion);
        $empaquesCount = null;
        $unidadesCount = null;

        if ($meta !== null) {
            if (preg_match('/^([\d][\d.,]*)\s+empaques?\b/iu', $meta, $coincidencias)) {
                $empaquesCount = (int) preg_replace('/[^\d]/', '', $coincidencias[1]);
            }
            if (preg_match('/^([\d][\d.,]*)\s+unidades?\b/iu', $meta, $coincidencias)) {
                $unidadesCount = (int) preg_replace('/[^\d]/', '', $coincidencias[1]);
            }
        }

        if ($unidadesCount === null && ($estimacion['ok'] ?? false)) {
            $unidadesCount = (int) ($estimacion['unidades'] ?? 0);
        }
        if ($empaquesCount === null && ($estimacion['ok'] ?? false) && $empaqueTexto === null) {
            $empaquesCount = (int) ($estimacion['empaques'] ?? 0);
            $empaqueTexto = $estimacion['resumen'] ?? null;
        }

        if ($empaqueTexto === null && ($estimacion['ok'] ?? false)) {
            $empaqueTexto = $estimacion['resumen'] ?? null;
        }

        $unidadesFmt = $unidadesCount !== null && $unidadesCount > 0
            ? number_format($unidadesCount, 0, ',', '.')
            : null;

        $partes = [];
        if ($empaqueTexto) {
            $partes[] = $empaqueTexto;
        }
        $partes[] = $kgFmt.' kg';
        if ($unidadesFmt !== null && $meta === null && ! str_contains(mb_strtolower($empaqueTexto ?? ''), 'unidad')) {
            $partes[] = '~'.$unidadesFmt.' unidades est.';
        } elseif ($unidadesFmt !== null && $meta !== null && ! str_contains(mb_strtolower($empaqueTexto ?? ''), 'unidad')) {
            $partes[] = '~'.$unidadesFmt.' unidades';
        }

        $linea = implode(' · ', $partes);
        $lineaCorta = $empaqueTexto
            ? $empaqueTexto.' ('.$kgFmt.' kg)'
            : ($unidadesFmt
                ? $kgFmt.' kg · ~'.$unidadesFmt.' u.'
                : $kgFmt.' kg');

        return [
            'kg' => $kg,
            'kg_fmt' => $kgFmt,
            'empaque' => $empaqueTexto,
            'empaques' => $empaquesCount,
            'unidades' => $unidadesCount,
            'unidades_fmt' => $unidadesFmt,
            'linea' => $linea,
            'linea_corta' => $lineaCorta,
        ];
    }

    public static function etiquetaCantidadDetalle(DetallePedido $detalle): string
    {
        return self::presentacionDetalle($detalle)['linea'];
    }

    private static function resolverCalibrePorCultivo(?string $cultivo): ?\App\Models\CatalogoTamanoConteo
    {
        $nombre = trim((string) $cultivo);
        if ($nombre === '') {
            return null;
        }

        $insumo = self::insumoVerduraPorNombreCultivo($nombre);
        if ($insumo === null) {
            return null;
        }

        $ctx = app(\App\Services\PlanificacionCosechaService::class)->contexto((int) $insumo->insumoid);
        $calibreId = $ctx['calibre_default_id'] ?? null;
        if (! $calibreId) {
            return null;
        }

        return \App\Models\CatalogoTamanoConteo::query()
            ->with('tipoEmpaque')
            ->find((int) $calibreId);
    }

    /**
     * Convierte metadatos @carga:…@ en texto legible (p. ej. "80 Sacos · 5.600 unidades").
     *
     * @param  array<string, mixed>  $estimacion
     */
    private static function formatearTextoEmpaqueDetalle(?string $meta, array $estimacion): ?string
    {
        if ($meta === null || trim($meta) === '') {
            return null;
        }

        if (preg_match('/^([\d][\d.,]*)\s+unidades?\b/iu', $meta, $coincidencias)) {
            $unidades = (int) preg_replace('/[^\d]/', '', $coincidencias[1]);

            return number_format($unidades, 0, ',', '.').' unidades';
        }

        if (preg_match('/^([\d][\d.,]*)\s+empaques?\b/iu', $meta, $coincidencias)) {
            $empaques = (int) preg_replace('/[^\d]/', '', $coincidencias[1]);
            $partes = array_values(array_filter(array_map('trim', preg_split('/\s*·\s*/u', $meta) ?: [])));
            $tipoNombre = $partes[1] ?? null;
            $label = CosechaPresentacionService::etiquetaEmpaquePlural($tipoNombre);
            $unidades = ($estimacion['ok'] ?? false) ? (int) ($estimacion['unidades'] ?? 0) : null;

            $texto = number_format($empaques, 0, ',', '.').' '.$label;
            if ($unidades !== null && $unidades > 0) {
                $texto .= ' · '.number_format($unidades, 0, ',', '.').' unidades';
            }

            return $texto;
        }

        return $meta;
    }
}
