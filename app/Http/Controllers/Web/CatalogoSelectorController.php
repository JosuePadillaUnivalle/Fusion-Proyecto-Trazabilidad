<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActorAbastecimiento;
use App\Models\Almacen;
use App\Models\Cultivo;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\MaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Models\Pedido;
use App\Models\Produccion;
use App\Models\PuntoVenta;
use App\Models\PerfilTransportista;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Support\AlmacenAmbito;
use App\Support\CultivoCatalogo;
use App\Support\PedidoCatalogo;
use App\Support\PuntoVentaAccess;
use App\Support\UbicacionGpsParser;
use App\Support\UsuarioRol;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoSelectorController extends Controller
{
    public function usuarios(Request $request): JsonResponse
    {
        $query = Usuario::query()->where('activo', true);

        if ($request->filled('roles')) {
            $roles = array_filter(array_map('trim', explode(',', (string) $request->roles)));
            if ($roles !== []) {
                $query->whereIn('role', $roles);
            }
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // El administrador supervisa el sistema; no es responsable operativo de parcelas.
        if (! $request->boolean('incluir_admin')) {
            $query->whereNotIn('role', ['admin', 'Admin']);
        }

        if ($request->filled('supervisor_usuarioid')) {
            $query->where('supervisor_usuarioid', (int) $request->supervisor_usuarioid);
        } elseif (
            UsuarioRol::esJefeAgricultor($request->user())
            && ! UsuarioRol::esAdminGlobal($request->user())
            && $request->filled('roles')
            && str_contains((string) $request->roles, 'agricultor')
        ) {
            $query->where('supervisor_usuarioid', $request->user()->usuarioid);
        }

        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'apellido', 'email', 'nombreusuario']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre')->orderBy('apellido'), function (Usuario $u) {
            return [
                'id' => $u->usuarioid,
                'label' => trim($u->nombre.' '.($u->apellido ?? '')),
                'meta' => ucfirst((string) ($u->role ?? '')).($u->email ? ' · '.$u->email : ''),
            ];
        });
    }

    public function vehiculos(Request $request): JsonResponse
    {
        $query = Vehiculo::query()
            ->with(['tipoVehiculo'])
            ->where('activo', true);

        if ($request->filled('transportista_usuarioid') && $request->boolean('solo_transportista')) {
            $vehiculoIds = PerfilTransportista::query()
                ->where('usuarioid', (int) $request->transportista_usuarioid)
                ->whereNotNull('vehiculoid')
                ->pluck('vehiculoid');

            if ($vehiculoIds->isNotEmpty()) {
                $query->whereIn('vehiculoid', $vehiculoIds);
            }
        }

        $q = trim((string) $request->q);
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function (Builder $w) use ($like) {
                $w->where('placa', 'like', $like)
                    ->orWhere('marca', 'like', $like)
                    ->orWhere('modelo', 'like', $like)
                    ->orWhere('color', 'like', $like);
            });
        }

        return $this->respuestaPaginada($request, $query->orderBy('placa'), function (Vehiculo $v) {
            $nombre = trim(collect([$v->marca, $v->modelo])->filter()->implode(' '));

            return [
                'id' => $v->vehiculoid,
                'label' => $v->placa,
                'meta' => trim(($nombre !== '' ? $nombre.' · ' : '').($v->tipoVehiculo?->nombre ?? 'Vehículo')),
            ];
        });
    }

    public function cultivos(Request $request): JsonResponse
    {
        $query = Cultivo::query();
        $this->aplicarBusqueda($query, (string) $request->q, ['nombre']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (Cultivo $c) {
            return [
                'id' => $c->cultivoid,
                'label' => $c->nombre,
                'meta' => CultivoCatalogo::detallePorNombre($c->nombre),
            ];
        });
    }

    public function lotes(Request $request): JsonResponse
    {
        $query = Lote::query()->with(['cultivo', 'usuario']);

        if ($request->user()?->hasRole('agricultor')) {
            $query->where('usuarioid', $request->user()->usuarioid);
        }

        if ($request->filled('usuarioid')) {
            $query->where('usuarioid', (int) $request->usuarioid);
        }

        if ($request->boolean('solo_cosecha')) {
            $query->with(['estadoTipo', 'actividades.tipoActividad']);
            $ids = \App\Support\EstadoLoteCatalogo::idsPorSlugs(['listo_para_cosecha', 'en_crecimiento']);
            if ($ids !== []) {
                $query->whereIn('estadolotetipoid', $ids);
            } else {
                $query->whereHas('estadoTipo', function ($q) {
                    $q->whereRaw('LOWER(TRIM(nombre)) IN (?, ?)', ['listo para cosecha', 'en crecimiento']);
                });
            }
        } elseif ($request->boolean('solo_produccion')) {
            $ids = \App\Support\EstadoLoteCatalogo::idsPorSlugs(['listo_para_cosecha']);
            if ($ids !== []) {
                $query->whereIn('estadolotetipoid', $ids);
            } else {
                $query->whereHas('estadoTipo', function ($q) {
                    $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['listo para cosecha']);
                });
            }
        }

        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'codigo_trazabilidad', 'ubicacion']);

        if ($request->boolean('solo_cosecha')) {
            $trazabilidad = app(\App\Support\LoteTrazabilidadService::class);
            $perPage = min(50, max(5, (int) $request->input('per_page', 20)));
            $page = max(1, (int) $request->input('page', 1));
            $filtrados = $query->orderBy('nombre')->get()
                ->filter(fn (Lote $l) => $trazabilidad->puedeRegistrarCosecha($l))
                ->values();
            $total = $filtrados->count();
            $items = $filtrados->slice(($page - 1) * $perPage, $perPage)->values();

            return response()->json([
                'data' => $items->map(function (Lote $l) {
                    $meta = [];
                    if ($l->cultivo?->nombre) {
                        $meta[] = $l->cultivo->nombre;
                    }
                    if ($l->codigo_trazabilidad) {
                        $meta[] = $l->codigo_trazabilidad;
                    }

                    $responsable = $l->usuario
                        ? trim($l->usuario->nombre.' '.($l->usuario->apellido ?? ''))
                        : '';

                    return [
                        'id' => $l->loteid,
                        'label' => $l->nombre,
                        'meta' => $meta !== [] ? implode(' · ', $meta) : ($l->ubicacion ?: null),
                        'extra' => [
                            'responsable' => $responsable,
                            'cultivo' => $l->cultivo?->nombre ?? 'Sin cultivo',
                            'superficie' => $l->superficie,
                        ],
                    ];
                })->values(),
                'meta' => [
                    'current_page' => $page,
                    'last_page' => max(1, (int) ceil($total / $perPage)),
                    'per_page' => $perPage,
                    'total' => $total,
                ],
            ]);
        }

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (Lote $l) {
            $meta = [];
            if ($l->cultivo?->nombre) {
                $meta[] = $l->cultivo->nombre;
            }
            if ($l->codigo_trazabilidad) {
                $meta[] = $l->codigo_trazabilidad;
            }

            $responsable = $l->usuario
                ? trim($l->usuario->nombre.' '.($l->usuario->apellido ?? ''))
                : '';

            return [
                'id' => $l->loteid,
                'label' => $l->nombre,
                'meta' => $meta !== [] ? implode(' · ', $meta) : ($l->ubicacion ?: null),
                'extra' => [
                    'responsable' => $responsable,
                    'cultivo' => $l->cultivo?->nombre ?? 'Sin cultivo',
                    'superficie' => $l->superficie,
                ],
            ];
        });
    }

    public function insumos(Request $request): JsonResponse
    {
        AlmacenAmbito::asegurarAmbitosEnRegistros();

        $query = Insumo::query()->with(['unidadMedida', 'almacen']);

        if ($request->boolean('solo_con_stock')) {
            $query->where('stock', '>', 0);
        }

        if ($request->boolean('ambito_planta')) {
            $almacenIds = AlmacenAmbito::scope(Almacen::query()->where('activo', true), AlmacenAmbito::PLANTA)
                ->pluck('almacenid');

            if ($almacenIds->isNotEmpty()) {
                $query->whereIn('almacenid', $almacenIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('almacenid')) {
            $query->where('almacenid', (int) $request->almacenid);
        }

        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'descripcion']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (Insumo $i) {
            $unidad = $i->unidadMedida?->abreviatura ?? $i->unidadMedida?->nombre ?? 'ud';
            $alm = $i->almacen?->nombre;
            $stock = (float) ($i->stock ?? 0);

            return [
                'id' => $i->insumoid,
                'label' => $i->nombre,
                'meta' => trim(
                    ($alm ? $alm.' · ' : '')
                    .'Stock: '.number_format($stock, 2).' '.$unidad
                ),
                'extra' => [
                    'stock' => $stock,
                    'unidad' => $unidad,
                    'almacen' => $alm,
                    'precio' => $i->preciounitario ?? 0,
                    'sin_stock' => $stock <= 0,
                ],
            ];
        });
    }

    public function pedidos(Request $request): JsonResponse
    {
        $query = Pedido::query();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $this->aplicarBusqueda($query, (string) $request->q, [
            'numero_solicitud',
            'nombre_planta',
            'direccion_texto',
            'observaciones',
        ]);

        return $this->respuestaPaginada(
            $request,
            $query->orderByDesc('fechapedido'),
            function (Pedido $p) {
                $fecha = $p->fechapedido
                    ? (\Carbon\Carbon::parse($p->fechapedido)->format('d/m/Y'))
                    : null;

                return [
                    'id' => $p->pedidoid,
                    'label' => $p->numero_solicitud,
                    'meta' => trim(
                        ($p->nombre_planta ?? '')
                        .($fecha ? ' · '.$fecha : '')
                        .($p->estado ? ' · '.ucfirst(str_replace('_', ' ', (string) $p->estado)) : '')
                    ),
                    'extra' => [
                        'estado' => $p->estado,
                        'planta' => $p->nombre_planta,
                    ],
                ];
            }
        );
    }

    public function actores(Request $request): JsonResponse
    {
        $query = ActorAbastecimiento::query()->where('activo', true);
        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'contacto', 'email']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (ActorAbastecimiento $a) {
            $meta = $a->tipo_actor ? ucfirst((string) $a->tipo_actor) : null;
            if ($a->contacto ?? $a->email) {
                $meta = trim(($meta ? $meta.' · ' : '').($a->contacto ?? $a->email));
            }

            return [
                'id' => $a->actorid,
                'label' => $a->nombre,
                'meta' => $meta,
            ];
        });
    }

    public function almacenes(Request $request): JsonResponse
    {
        AlmacenAmbito::asegurarAmbitosEnRegistros();

        $query = Almacen::query()->where('activo', true);

        if ($request->filled('ambito') && AlmacenAmbito::esValido($request->string('ambito')->toString())) {
            $query = AlmacenAmbito::scope($query, $request->string('ambito')->toString());
        }

        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'ubicacion']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (Almacen $a) {
            $resuelto = UbicacionGpsParser::resolverAlmacen(
                (int) $a->almacenid,
                $a->nombre,
                $a->ubicacion
            );

            return [
                'id' => $a->almacenid,
                'label' => $a->nombre,
                'meta' => $resuelto['estimada']
                    ? $resuelto['direccion'].' (ubicación referencial)'
                    : \Illuminate\Support\Str::limit($resuelto['direccion'], 80),
                'extra' => [
                    'lat' => $resuelto['lat'],
                    'lng' => $resuelto['lng'],
                    'direccion' => $resuelto['direccion'],
                    'ambito' => $a->ambito ?? AlmacenAmbito::AGRICOLA,
                    'ubicacion_estimada' => $resuelto['estimada'],
                ],
            ];
        });
    }

    public function puntosVenta(Request $request): JsonResponse
    {
        $query = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->with('minorista'),
            $request->user()
        );

        if (! $request->boolean('incluir_inactivos')) {
            $query->where('activo', true);
        }

        if ($request->filled('minorista_usuarioid')) {
            $query->where('usuarioid', (int) $request->minorista_usuarioid);
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $query->where(function (Builder $w) use ($term) {
                $w->where('nombre', 'like', "%{$term}%")
                    ->orWhere('direccion', 'like', "%{$term}%")
                    ->orWhereHas('minorista', function (Builder $m) use ($term) {
                        $m->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (PuntoVenta $pv) {
            $minorista = trim(($pv->minorista?->nombre ?? '').' '.($pv->minorista?->apellido ?? ''));

            return [
                'id' => $pv->puntoventaid,
                'label' => $pv->nombre,
                'meta' => trim(collect([
                    $minorista !== '' ? $minorista : null,
                    $pv->direccion ? \Illuminate\Support\Str::limit($pv->direccion, 60) : null,
                    $pv->activo ? null : 'Inactivo',
                ])->filter()->implode(' · ')),
                'extra' => [
                    'lat' => $pv->latitud,
                    'lng' => $pv->longitud,
                    'direccion' => $pv->direccion,
                    'minorista' => $minorista,
                    'activo' => (bool) $pv->activo,
                ],
            ];
        });
    }

    public function productosPedido(Request $request): JsonResponse
    {
        AlmacenAmbito::asegurarAmbitosEnRegistros();

        $q = mb_strtolower(trim((string) $request->q));
        $almacenId = $request->filled('almacenid') ? (int) $request->almacenid : null;
        $items = collect();

        foreach (PedidoCatalogo::insumosMaterialSiembraGlobales() as $insumo) {
            if ($almacenId && (int) $insumo->almacenid !== $almacenId) {
                continue;
            }

            $almacen = $insumo->almacen?->nombre;
            $stock = number_format((float) $insumo->stock, 2);
            $unidad = $insumo->unidadMedida?->abreviatura ?? 'kg';
            $label = $insumo->nombre;
            $meta = trim(collect([$almacen, "Stock: {$stock} {$unidad}"])->filter()->implode(' · '));

            if ($q !== '' && ! str_contains(mb_strtolower($label.' '.$meta), $q)) {
                continue;
            }

            $items->push([
                'id' => 'insumo:'.$insumo->insumoid,
                'label' => $label,
                'meta' => $meta !== '' ? $meta : 'Insumo · Material de siembra',
                'extra' => [
                    'tipo' => 'insumo',
                    'almacen' => $almacen,
                    'almacenid' => $insumo->almacenid,
                ],
            ]);
        }

        foreach (PedidoCatalogo::cosechasAgricolasDisponibles() as $cosecha) {
            if ($almacenId && (int) $cosecha->almacenid !== $almacenId) {
                continue;
            }

            $cultivo = $cosecha->produccion?->lote?->cultivo?->nombre ?? 'Cultivo';
            $lote = $cosecha->produccion?->lote?->nombre ?? 'Lote';
            $almacen = $cosecha->almacen?->nombre ?? 'Almacén agrícola';
            $cantidad = number_format((float) $cosecha->cantidad, 2);
            $unidad = $cosecha->unidadMedida?->abreviatura ?? 'kg';
            $label = "{$cultivo} — {$lote}";
            $meta = "{$almacen} · {$cantidad} {$unidad} disponibles";

            if ($q !== '' && ! str_contains(mb_strtolower($label.' '.$meta), $q)) {
                continue;
            }

            $items->push([
                'id' => 'cosecha:'.$cosecha->produccionalmacenamientoid,
                'label' => $label,
                'meta' => $meta,
                'extra' => [
                    'tipo' => 'cosecha',
                    'almacen' => $almacen,
                    'almacenid' => $cosecha->almacenid,
                ],
            ]);
        }

        if ($items->isEmpty()) {
            foreach (\App\Models\Cultivo::query()->orderBy('nombre')->get() as $cultivo) {
                $label = $cultivo->nombre;
                $meta = 'Cultivo de producción agrícola';

                if ($q !== '' && ! str_contains(mb_strtolower($label.' '.$meta), $q)) {
                    continue;
                }

                $items->push([
                    'id' => 'cultivo:'.$cultivo->cultivoid,
                    'label' => $label,
                    'meta' => $meta,
                    'extra' => ['tipo' => 'cultivo'],
                ]);
            }
        }

        $perPage = min(50, max(5, (int) $request->input('per_page', 20)));
        $page = max(1, (int) $request->input('page', 1));
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $slice,
            'meta' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    public function producciones(Request $request): JsonResponse
    {
        $query = Produccion::query()->with(['lote', 'destino']);

        if ($request->filled('loteid')) {
            $query->where('loteid', (int) $request->loteid);
        }

        $q = trim((string) $request->q);
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function (Builder $w) use ($like) {
                $w->whereHas('lote', fn ($l) => $l->where('nombre', 'like', $like))
                    ->orWhere('observaciones', 'like', $like);
            });
        }

        return $this->respuestaPaginada($request, $query->orderByDesc('produccionid'), function (Produccion $p) {
            $fecha = $p->fechacosecha ? $p->fechacosecha->format('d/m/Y') : null;

            return [
                'id' => $p->produccionid,
                'label' => ($p->lote->nombre ?? 'Cosecha').' #'.$p->produccionid,
                'meta' => trim(($fecha ? $fecha.' · ' : '').number_format((float) ($p->cantidad ?? 0), 2).' · '.($p->destino->nombre ?? '')),
            ];
        });
    }

    public function procesosPlanta(Request $request): JsonResponse
    {
        $query = $request->boolean('activo', true)
            ? \App\Support\ProcesoPlantaCatalogo::queryActivos()
            : ProcesoPlanta::query();

        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'descripcion']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (ProcesoPlanta $p) {
            return [
                'id' => $p->procesoplantaid,
                'label' => $p->nombre,
                'meta' => $p->descripcion ? \Illuminate\Support\Str::limit($p->descripcion, 60) : null,
            ];
        });
    }

    public function maquinasPlanta(Request $request): JsonResponse
    {
        $query = MaquinaPlanta::query();
        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'codigo', 'descripcion']);

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (MaquinaPlanta $m) {
            return [
                'id' => $m->maquinaplantaid,
                'label' => $m->nombre,
                'meta' => $m->codigo ?: ($m->activo ? 'Activa' : 'Mantenimiento'),
            ];
        });
    }

    private function aplicarBusqueda(Builder $query, string $q, array $columnas): void
    {
        if ($q === '') {
            return;
        }

        $like = '%'.$q.'%';
        $query->where(function (Builder $w) use ($columnas, $like) {
            foreach ($columnas as $col) {
                $w->orWhere($col, 'like', $like);
            }
        });
    }

    private function respuestaPaginada(Request $request, Builder $query, callable $mapper): JsonResponse
    {
        $paginator = $query->paginate(
            min(50, max(5, (int) $request->input('per_page', 20))),
            ['*'],
            'page',
            max(1, (int) $request->input('page', 1))
        );

        return response()->json([
            'data' => collect($paginator->items())->map($mapper)->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
