<?php

namespace App\Services;

use App\Models\AlmacenMovimiento;
use App\Models\DistribucionIngreso;
use App\Models\DistribucionPedidoAlmacen;
use App\Models\DistribucionSalida;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\Pedido;
use App\Models\SeguimientoEnvioPedido;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReferenciasAlmacenDisponiblesService
{
    /**
     * @return list<array{grupo: string, items: list<array{valor: string, detalle: string, destino: ?string}>}>
     */
    public function listar(string $naturaleza, ?int $almacenId = null, ?int $insumoId = null): array
    {
        abort_unless(in_array($naturaleza, ['ingreso', 'salida'], true), 404);

        $insumoNombre = $insumoId
            ? Insumo::query()->whereKey($insumoId)->value('nombre')
            : null;

        $usadas = $this->referenciasYaUsadas($almacenId);

        $grupos = $naturaleza === 'ingreso'
            ? $this->gruposIngreso($almacenId, $insumoNombre, $usadas)
            : $this->gruposSalida($almacenId, $insumoNombre, $usadas);

        $historial = $this->grupoReferenciasEnMovimientos($naturaleza, $almacenId, $usadas);
        if ($historial !== null) {
            $grupos[] = $historial;
        }

        return array_values(array_filter($grupos, fn (array $g) => count($g['items']) > 0));
    }

    /**
     * Referencias que ya figuran en movimientos de este almacén (datos reales del sistema).
     *
     * @param  Collection<int, string>  $usadas
     * @return ?array{grupo: string, items: list<array{valor: string, detalle: string, destino: ?string, ya_registrada: bool}>}
     */
    private function grupoReferenciasEnMovimientos(string $naturaleza, ?int $almacenId, Collection $usadas): ?array
    {
        $q = AlmacenMovimiento::query()
            ->whereNotNull('referencia')
            ->where('referencia', '!=', '')
            ->whereHas('tipo', fn ($t) => $t->where('naturaleza', $naturaleza))
            ->orderByDesc('fecha')
            ->limit(50);

        if ($almacenId) {
            $q->where('almacenid', $almacenId);
        }

        $items = $q->get()
            ->pluck('referencia')
            ->map(fn ($r) => trim((string) $r))
            ->filter()
            ->unique()
            ->map(fn (string $ref) => $this->item(
                $ref,
                'Registrado previamente en movimientos de almacén',
                null,
                $usadas,
                true
            ))
            ->filter()
            ->values()
            ->all();

        if ($items === []) {
            return null;
        }

        return ['grupo' => 'Referencias en movimientos de almacén', 'items' => $items];
    }

    public function resolverDestinoPorReferencia(string $naturaleza, ?int $almacenId, string $referencia): ?string
    {
        $referencia = trim($referencia);
        if ($referencia === '') {
            return null;
        }

        if (Schema::hasTable('envio_asignacion_multiple')) {
            $envio = EnvioAsignacionMultiple::query()
                ->where('externo_envio_id', $referencia)
                ->when($almacenId, fn ($q) => $q->where('almacenid', $almacenId))
                ->first();
            if ($envio) {
                $dest = $this->destinoDesdeEnvio($envio);

                return $dest ?: ($naturaleza === 'ingreso' ? 'Recepción de envío '.$referencia : 'Despacho envío '.$referencia);
            }
        }

        if (Schema::hasTable('pedido')) {
            $pedido = Pedido::query()->where('numero_solicitud', $referencia)->first();
            if ($pedido?->direccion_texto) {
                return $pedido->direccion_texto;
            }
        }

        if (Schema::hasTable('distribucion_ingreso') && $naturaleza === 'ingreso') {
            if (DistribucionIngreso::query()->where('codigo_comprobante', $referencia)->exists()) {
                return 'Recepción por comprobante '.$referencia;
            }
        }

        if (Schema::hasTable('distribucion_salida') && $naturaleza === 'salida') {
            if (DistribucionSalida::query()->where('codigo_comprobante', $referencia)->exists()) {
                return 'Salida por comprobante '.$referencia;
            }
        }

        if (Schema::hasTable('lote')) {
            $lote = Lote::query()->where('codigo_trazabilidad', $referencia)->first();
            if ($lote) {
                return 'Lote '.$lote->nombre.($lote->ubicacion ? ' — '.$lote->ubicacion : '');
            }
        }

        return null;
    }

    /**
     * @return Collection<int, string>
     */
    private function referenciasYaUsadas(?int $almacenId): Collection
    {
        $q = AlmacenMovimiento::query()
            ->whereNotNull('referencia')
            ->where('referencia', '!=', '');

        if ($almacenId) {
            $q->where('almacenid', $almacenId);
        }

        return $q->pluck('referencia')->map(fn ($r) => trim((string) $r))->unique();
    }

    /**
     * @param  Collection<int, string>  $usadas
     * @return list<array{grupo: string, items: list<array{valor: string, detalle: string, destino: ?string}>}>
     */
    private function gruposIngreso(?int $almacenId, ?string $insumoNombre, Collection $usadas): array
    {
        $comprobantes = collect();
        $envios = collect();
        $lotes = collect();
        $pedidos = collect();

        if (Schema::hasTable('distribucion_ingreso')) {
            $q = DistribucionIngreso::query()
                ->whereNotNull('codigo_comprobante')
                ->where('codigo_comprobante', '!=', '')
                ->orderByDesc('fecha')
                ->limit(40);

            if ($almacenId) {
                $q->where('almacenid', $almacenId);
            }

            foreach ($q->get() as $row) {
                $comprobantes->push($this->item(
                    $row->codigo_comprobante,
                    'Comprobante · '.optional($row->fecha)->format('d/m/Y'),
                    'Recepción en almacén',
                    $usadas
                ));
            }
        }

        if (Schema::hasTable('envio_asignacion_multiple')) {
            $q = EnvioAsignacionMultiple::query()
                ->whereNotNull('externo_envio_id')
                ->where('externo_envio_id', '!=', '')
                ->whereNotIn('estado', ['cancelado', 'anulado'])
                ->orderByDesc('fecha_asignacion')
                ->limit(40);

            if ($almacenId) {
                $q->where('almacenid', $almacenId);
            }

            foreach ($q->get() as $envio) {
                if ($insumoNombre && ! $this->envioCoincideInsumo($envio, $insumoNombre)) {
                    continue;
                }
                $destino = $this->destinoDesdeEnvio($envio);
                $envios->push($this->item(
                    $envio->externo_envio_id,
                    'Envío · '.($envio->estado ?? '—'),
                    $destino ?: 'Recepción de envío',
                    $usadas
                ));
            }
        }

        if (Schema::hasTable('lote')) {
            Lote::query()
                ->whereNotNull('codigo_trazabilidad')
                ->where('codigo_trazabilidad', '!=', '')
                ->orderByDesc('loteid')
                ->limit(25)
                ->get()
                ->each(function (Lote $lote) use ($lotes, $usadas) {
                    $lotes->push($this->item(
                        $lote->codigo_trazabilidad,
                        'Lote · '.$lote->nombre,
                        'Producción / cosecha del lote',
                        $usadas
                    ));
                });
        }

        if (Schema::hasTable('pedido')) {
            Pedido::query()
                ->whereNotNull('numero_solicitud')
                ->where('numero_solicitud', '!=', '')
                ->orderByDesc('pedidoid')
                ->limit(20)
                ->get()
                ->each(function (Pedido $pedido) use ($pedidos, $usadas) {
                    $pedidos->push($this->item(
                        $pedido->numero_solicitud,
                        'Pedido · '.($pedido->nombre_planta ?: 'sin planta'),
                        $pedido->direccion_texto,
                        $usadas
                    ));
                });
        }

        return $this->construirGrupos([
            ['grupo' => 'Comprobantes de ingreso', 'items' => $comprobantes],
            ['grupo' => 'Envíos recibidos', 'items' => $envios],
            ['grupo' => 'Lotes (trazabilidad)', 'items' => $lotes],
            ['grupo' => 'Pedidos', 'items' => $pedidos],
        ]);
    }

    /**
     * @param  Collection<int, string>  $usadas
     * @return list<array{grupo: string, items: list<array{valor: string, detalle: string, destino: ?string}>}>
     */
    private function gruposSalida(?int $almacenId, ?string $insumoNombre, Collection $usadas): array
    {
        $comprobantes = collect();
        $pedidosAlmacen = collect();
        $envios = collect();
        $seguimientos = collect();
        $pedidos = collect();

        if (Schema::hasTable('distribucion_salida')) {
            $q = DistribucionSalida::query()
                ->whereNotNull('codigo_comprobante')
                ->where('codigo_comprobante', '!=', '')
                ->orderByDesc('fecha')
                ->limit(40);

            if ($almacenId) {
                $q->where('almacenid', $almacenId);
            }

            foreach ($q->get() as $row) {
                $comprobantes->push($this->item(
                    $row->codigo_comprobante,
                    'Salida · '.optional($row->fecha)->format('d/m/Y'),
                    'Despacho desde almacén',
                    $usadas
                ));
            }
        }

        if (Schema::hasTable('distribucion_pedido_almacen')) {
            $q = DistribucionPedidoAlmacen::query()
                ->whereNotNull('codigo_comprobante')
                ->where('codigo_comprobante', '!=', '')
                ->orderByDesc('fecha')
                ->limit(30);

            if ($almacenId) {
                $q->where('almacenid', $almacenId);
            }

            foreach ($q->get() as $row) {
                $pedidosAlmacen->push($this->item(
                    $row->codigo_comprobante,
                    'Pedido almacén · '.optional($row->fecha)->format('d/m/Y'),
                    'Salida por pedido de distribución',
                    $usadas
                ));
            }
        }

        if (Schema::hasTable('envio_asignacion_multiple')) {
            $q = EnvioAsignacionMultiple::query()
                ->whereNotNull('externo_envio_id')
                ->where('externo_envio_id', '!=', '')
                ->whereNotIn('estado', ['cancelado', 'anulado'])
                ->orderByDesc('fecha_asignacion')
                ->limit(40);

            if ($almacenId) {
                $q->where('almacenid', $almacenId);
            }

            foreach ($q->get() as $envio) {
                if ($insumoNombre && ! $this->envioCoincideInsumo($envio, $insumoNombre)) {
                    continue;
                }
                $destino = $this->destinoDesdeEnvio($envio);
                $envios->push($this->item(
                    $envio->externo_envio_id,
                    'Envío · '.($envio->estado ?? '—'),
                    $destino,
                    $usadas
                ));
            }
        }

        if (Schema::hasTable('seguimiento_envio_pedido')) {
            SeguimientoEnvioPedido::query()
                ->whereNotNull('codigo_envio')
                ->where('codigo_envio', '!=', '')
                ->orderByDesc('seguimientoenviopedidoid')
                ->limit(20)
                ->get()
                ->each(function (SeguimientoEnvioPedido $seg) use ($seguimientos, $usadas) {
                    $seguimientos->push($this->item(
                        $seg->codigo_envio,
                        'Seguimiento · '.($seg->estado ?? 'activo'),
                        null,
                        $usadas
                    ));
                });
        }

        if (Schema::hasTable('pedido')) {
            Pedido::query()
                ->whereNotNull('numero_solicitud')
                ->where('numero_solicitud', '!=', '')
                ->orderByDesc('pedidoid')
                ->limit(15)
                ->get()
                ->each(function (Pedido $pedido) use ($pedidos, $usadas) {
                    $pedidos->push($this->item(
                        $pedido->numero_solicitud,
                        'Pedido cliente',
                        $pedido->direccion_texto,
                        $usadas
                    ));
                });
        }

        return $this->construirGrupos([
            ['grupo' => 'Comprobantes de salida', 'items' => $comprobantes],
            ['grupo' => 'Pedidos de almacén', 'items' => $pedidosAlmacen],
            ['grupo' => 'Envíos y despachos', 'items' => $envios],
            ['grupo' => 'Seguimiento logístico', 'items' => $seguimientos],
            ['grupo' => 'Pedidos comerciales', 'items' => $pedidos],
        ]);
    }

    /**
     * @param  Collection<int, string>  $usadas
     * @return ?array{valor: string, detalle: string, destino: ?string, ya_registrada: bool}
     */
    private function item(string $valor, string $detalle, ?string $destino, Collection $usadas, bool $forzarIncluir = false): ?array
    {
        $valor = trim($valor);
        if ($valor === '' || $this->esReferenciaDemo($valor)) {
            return null;
        }

        $yaRegistrada = $usadas->contains($valor);
        if ($yaRegistrada && ! $forzarIncluir) {
            $detalle .= ' · ya vinculado a un movimiento';
        }

        return [
            'valor' => $valor,
            'detalle' => $detalle,
            'destino' => $destino ? trim($destino) : null,
            'ya_registrada' => $yaRegistrada,
        ];
    }

    private function esReferenciaDemo(string $valor): bool
    {
        return str_contains(strtoupper($valor), 'FORM-DEMO');
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
        if (is_array($detalles) && isset($detalles[0]['destino'])) {
            return (string) $detalles[0]['destino'];
        }

        return null;
    }

    /**
     * @param  list<array{grupo: string, items: Collection}>  $definiciones
     * @return list<array{grupo: string, items: list<array{valor: string, detalle: string, destino: ?string}>}>
     */
    private function construirGrupos(array $definiciones): array
    {
        $out = [];
        foreach ($definiciones as $def) {
            $items = $def['items']->filter()->unique('valor')->values()->all();
            if ($items !== []) {
                $out[] = ['grupo' => $def['grupo'], 'items' => $items];
            }
        }

        return $out;
    }
}
