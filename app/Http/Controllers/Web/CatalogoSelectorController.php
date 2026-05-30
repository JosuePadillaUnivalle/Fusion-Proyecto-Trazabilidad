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
use App\Models\Produccion;
use App\Models\Usuario;
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

        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'apellido', 'email', 'nombreusuario']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre')->orderBy('apellido'), function (Usuario $u) {
            return [
                'id' => $u->usuarioid,
                'label' => trim($u->nombre.' '.($u->apellido ?? '')),
                'meta' => ucfirst((string) ($u->role ?? '')).($u->email ? ' · '.$u->email : ''),
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
                'meta' => null,
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

        if ($request->boolean('solo_produccion') || $request->boolean('solo_cosecha')) {
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
        $query = Insumo::query()->with('unidadMedida');

        if ($request->boolean('solo_con_stock')) {
            $query->where('stock', '>', 0);
        }

        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'descripcion']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (Insumo $i) {
            $unidad = $i->unidadMedida?->abreviatura ?? 'ud';

            return [
                'id' => $i->insumoid,
                'label' => $i->nombre,
                'meta' => 'Stock: '.($i->stock ?? 0).' '.$unidad,
                'extra' => [
                    'stock' => $i->stock,
                    'unidad' => $unidad,
                    'precio' => $i->preciounitario ?? 0,
                ],
            ];
        });
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
        $query = Almacen::query();
        $this->aplicarBusqueda($query, (string) $request->q, ['nombre', 'ubicacion']);

        return $this->respuestaPaginada($request, $query->orderBy('nombre'), function (Almacen $a) {
            return [
                'id' => $a->almacenid,
                'label' => $a->nombre,
                'meta' => $a->ubicacion,
            ];
        });
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
        $query = ProcesoPlanta::query();

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

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
