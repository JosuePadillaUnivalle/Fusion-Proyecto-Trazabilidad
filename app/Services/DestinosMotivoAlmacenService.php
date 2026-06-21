<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\Pedido;
use App\Models\PedidoDestino;
use App\Models\TipoMovimientoAlmacen;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DestinosMotivoAlmacenService
{
    /**
     * @return list<array{grupo: string, items: list<array{valor: string, detalle: string}>}>
     */
    public function listar(
        string $naturaleza,
        ?int $almacenId = null,
        ?int $insumoId = null,
        ?int $tipoMovimientoId = null,
        ?string $referencia = null,
    ): array {
        abort_unless(in_array($naturaleza, ['ingreso', 'salida'], true), 404);

        $tipoNombre = $tipoMovimientoId
            ? TipoMovimientoAlmacen::query()->whereKey($tipoMovimientoId)->value('nombre')
            : null;

        $insumo = $insumoId ? Insumo::query()->with('actor')->find($insumoId) : null;
        $almacen = $almacenId ? Almacen::query()->find($almacenId) : null;

        $porTipo = collect();
        $porReferencia = collect();
        $logistica = collect();
        $internos = collect();
        $recientes = collect();

        foreach ($this->sugerenciasPorTipo($naturaleza, $tipoNombre, $almacen, $insumo) as $s) {
            $porTipo->push($this->item($s['valor'], $s['detalle']));
        }

        if ($referencia = trim((string) $referencia)) {
            $dest = app(ReferenciasAlmacenDisponiblesService::class)
                ->resolverDestinoPorReferencia($naturaleza, $almacenId, $referencia);
            if ($dest) {
                $porReferencia->push($this->item($dest, 'Vinculado a la referencia seleccionada'));
            }
        }

        foreach ($this->destinosLogistica($naturaleza, $almacenId, $insumo?->nombre) as $s) {
            $logistica->push($this->item($s['valor'], $s['detalle']));
        }

        foreach (config('almacen_movimientos.destinos_'.$naturaleza, []) as $valor) {
            $internos->push($this->item($valor, 'Área / uso frecuente'));
        }

        if ($almacen) {
            $internos->prepend($this->item(
                $naturaleza === 'ingreso'
                    ? 'Recepción en '.$almacen->nombre
                    : 'Despacho desde '.$almacen->nombre,
                'Ubicación del almacén actual'
            ));
        }

        if ($insumo?->proveedor) {
            $porTipo->push($this->item(
                'Proveedor: '.$insumo->proveedor,
                'Proveedor registrado en el insumo'
            ));
        }

        $this->destinosRecientes($naturaleza, $almacenId, 15)->each(function (string $d) use ($recientes) {
            $recientes->push($this->item($d, 'Registrado en movimientos de este almacén'));
        });

        $grupos = $this->construirGrupos([
            ['grupo' => 'Sugerido según tipo de movimiento', 'items' => $porTipo],
            ['grupo' => 'Según referencia', 'items' => $porReferencia],
            ['grupo' => $naturaleza === 'ingreso' ? 'Orígenes (pedidos y envíos)' : 'Destinos (clientes y envíos)', 'items' => $logistica],
            ['grupo' => 'Áreas y ubicaciones', 'items' => $internos],
            ['grupo' => 'Usados recientemente', 'items' => $recientes],
        ]);

        return $grupos;
    }

    /**
     * @return list<array{valor: string, detalle: string}>
     */
    private function sugerenciasPorTipo(
        string $naturaleza,
        ?string $tipoNombre,
        ?Almacen $almacen,
        ?Insumo $insumo,
    ): array {
        if (! $tipoNombre) {
            return [];
        }

        $mapa = config('almacen_movimientos.destinos_por_tipo.'.$naturaleza, []);
        $plantillas = $mapa[$tipoNombre] ?? $mapa[TipoMovimientoAlmacen::normalizeNombre($tipoNombre)] ?? [];

        $out = [];
        foreach ((array) $plantillas as $plantilla) {
            $valor = str_replace(
                ['{almacen}', '{insumo}', '{proveedor}'],
                [
                    $almacen?->nombre ?? 'almacén',
                    $insumo?->nombre ?? 'insumo',
                    $insumo?->proveedor ?? 'proveedor',
                ],
                $plantilla
            );
            $out[] = ['valor' => $valor, 'detalle' => 'Sugerencia para tipo «'.$tipoNombre.'»'];
        }

        return $out;
    }

    /**
     * @return list<array{valor: string, detalle: string}>
     */
    private function destinosLogistica(string $naturaleza, ?int $almacenId, ?string $insumoNombre): array
    {
        $out = [];

        if (Schema::hasTable('pedido_destino')) {
            PedidoDestino::query()
                ->whereNotNull('direccion')
                ->where('direccion', '!=', '')
                ->when($almacenId && Schema::hasColumn('pedido_destino', 'almacen_origenid'), function ($q) use ($almacenId, $naturaleza) {
                    if ($naturaleza === 'salida') {
                        $q->where(function ($q2) use ($almacenId) {
                            $q2->where('almacen_origenid', $almacenId)->orWhereNull('almacen_origenid');
                        });
                    } else {
                        $q->where(function ($q2) use ($almacenId) {
                            $q2->where('almacen_destinoid', $almacenId)->orWhereNull('almacen_destinoid');
                        });
                    }
                })
                ->orderByDesc('pedidodestinoid')
                ->limit(15)
                ->get()
                ->each(function (PedidoDestino $pd) use (&$out) {
                    $txt = trim($pd->direccion);
                    if ($pd->nombre_contacto) {
                        $txt .= ' ('.$pd->nombre_contacto.')';
                    }
                    $out[] = ['valor' => Str::limit($txt, 150, ''), 'detalle' => 'Destino de pedido'];
                });
        }

        if (Schema::hasTable('pedido')) {
            Pedido::query()
                ->whereNotNull('direccion_texto')
                ->where('direccion_texto', '!=', '')
                ->orderByDesc('pedidoid')
                ->limit(10)
                ->get()
                ->each(function (Pedido $p) use (&$out) {
                    $out[] = [
                        'valor' => Str::limit($p->direccion_texto, 150, ''),
                        'detalle' => 'Pedido '.($p->numero_solicitud ?? '#'.$p->pedidoid),
                    ];
                });
        }

        if (Schema::hasTable('envio_asignacion_multiple')) {
            $q = EnvioAsignacionMultiple::query()
                ->with('pedido')
                ->whereNotNull('externo_envio_id')
                ->orderByDesc('fecha_asignacion')
                ->limit(20);

            if ($almacenId) {
                $q->where('almacenid', $almacenId);
            }

            foreach ($q->get() as $envio) {
                if ($insumoNombre && ! $this->envioCoincideInsumo($envio, $insumoNombre)) {
                    continue;
                }
                $dest = $this->destinoDesdeEnvio($envio);
                if ($dest) {
                    $out[] = [
                        'valor' => Str::limit($dest, 150, ''),
                        'detalle' => 'Envío '.$envio->externo_envio_id,
                    ];
                }
            }
        }

        if (Schema::hasTable('lote') && $naturaleza === 'ingreso') {
            Lote::query()
                ->whereNotNull('ubicacion')
                ->where('ubicacion', '!=', '')
                ->orderByDesc('loteid')
                ->limit(8)
                ->get()
                ->each(function (Lote $lote) use (&$out) {
                    $out[] = [
                        'valor' => 'Lote '.$lote->nombre.' — '.$lote->ubicacion,
                        'detalle' => 'Origen en campo ('.($lote->codigo_trazabilidad ?? 'sin código').')',
                    ];
                });
        }

        if (Schema::hasTable('almacen')) {
            Almacen::query()
                ->when($almacenId, fn ($q) => $q->where('almacenid', '!=', $almacenId))
                ->orderBy('nombre')
                ->limit(6)
                ->get()
                ->each(function (Almacen $a) use (&$out, $naturaleza) {
                    $out[] = [
                        'valor' => $naturaleza === 'ingreso'
                            ? 'Transferencia desde '.$a->nombre
                            : 'Transferencia hacia '.$a->nombre,
                        'detalle' => 'Otro almacén del sistema',
                    ];
                });
        }

        return $out;
    }

    /**
     * @return Collection<int, string>
     */
    private function destinosRecientes(string $naturaleza, ?int $almacenId, int $limite = 40): Collection
    {
        $q = AlmacenMovimiento::query()
            ->whereNotNull('destino_motivo')
            ->where('destino_motivo', '!=', '')
            ->whereHas('tipo', fn ($t) => $t->where('naturaleza', $naturaleza))
            ->orderByDesc('fecha')
            ->limit($limite * 3);

        if ($almacenId) {
            $q->where('almacenid', $almacenId);
        }

        return $q->pluck('destino_motivo')
            ->map(fn ($d) => trim((string) $d))
            ->filter()
            ->unique()
            ->take($limite)
            ->values();
    }

    private function envioCoincideInsumo(EnvioAsignacionMultiple $envio, string $insumoNombre): bool
    {
        $detalles = $envio->detalles_productos;
        if (! is_array($detalles) || $detalles === []) {
            return true;
        }
        $needle = Str::lower($insumoNombre);
        foreach ($detalles as $det) {
            $nombre = Str::lower((string) ($det['producto'] ?? $det['nombre'] ?? $det['cultivo'] ?? ''));
            if ($nombre !== '' && (Str::contains($nombre, $needle) || Str::contains($needle, $nombre))) {
                return true;
            }
        }

        return false;
    }

    private function destinoDesdeEnvio(EnvioAsignacionMultiple $envio): ?string
    {
        $envio->loadMissing('pedido');
        if ($envio->pedido?->direccion_texto) {
            return $envio->pedido->direccion_texto;
        }
        $detalles = $envio->detalles_productos;
        if (is_array($detalles)) {
            foreach ($detalles as $det) {
                if (! empty($det['destino'])) {
                    return (string) $det['destino'];
                }
            }
        }

        return null;
    }

    /**
     * @return ?array{valor: string, detalle: string}
     */
    private function item(string $valor, string $detalle): ?array
    {
        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        return ['valor' => $valor, 'detalle' => $detalle];
    }

    /**
     * @param  list<array{grupo: string, items: Collection}>  $definiciones
     * @return list<array{grupo: string, items: list<array{valor: string, detalle: string}>}>
     */
    private function construirGrupos(array $definiciones): array
    {
        $out = [];
        $vistos = collect();

        foreach ($definiciones as $def) {
            $items = $def['items']
                ->filter()
                ->filter(fn ($i) => ! $vistos->contains($i['valor']))
                ->values()
                ->all();

            foreach ($items as $i) {
                $vistos->push($i['valor']);
            }

            if ($items !== []) {
                $out[] = ['grupo' => $def['grupo'], 'items' => $items];
            }
        }

        return $out;
    }
}
