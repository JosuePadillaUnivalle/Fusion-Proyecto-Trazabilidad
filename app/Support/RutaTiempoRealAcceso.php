<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\RutaDistribucion;
use App\Models\Usuario;
use Illuminate\Support\Collection;

final class RutaTiempoRealAcceso
{
    public static function esVistaGlobal(?Usuario $user): bool
    {
        return $user !== null && UsuarioRol::esAdminGlobal($user);
    }

    public static function puedeAccederModulo(?Usuario $user): bool
    {
        if ($user === null || UsuarioRol::esTransportista($user)) {
            return false;
        }

        return self::esVistaGlobal($user)
            || UsuarioRol::esJefePlanta($user)
            || UsuarioRol::esJefeAgricultor($user)
            || UsuarioRol::esJefeMayorista($user)
            || UsuarioRol::esMayorista($user);
    }

    /** @return array<string, array{etiqueta: string, variante: string, color: string, icono: string}> */
    public static function catalogoVariantes(?Usuario $user): array
    {
        $todas = SimulacionRutaCatalogo::catalogoVariantes();

        if ($user === null || self::esVistaGlobal($user)) {
            return $todas;
        }

        if (UsuarioRol::esJefePlanta($user)) {
            return array_intersect_key($todas, [
                SimulacionRutaCatalogo::TIPO_PLANTA_MAYORISTA => true,
            ]);
        }

        if (UsuarioRol::esJefeAgricultor($user)) {
            return array_intersect_key($todas, [
                SimulacionRutaCatalogo::TIPO_AGRICOLA => true,
            ]);
        }

        if (UsuarioRol::esMayorista($user) || UsuarioRol::esJefeMayorista($user)) {
            return array_intersect_key($todas, [
                SimulacionRutaCatalogo::TIPO_PLANTA_MAYORISTA => true,
                SimulacionRutaCatalogo::TIPO_DISTRIBUCION => true,
            ]);
        }

        return $todas;
    }

    /** @return list<string> */
    public static function variantesFiltroPermitidas(?Usuario $user): array
    {
        return array_values(array_map(
            fn (array $meta) => (string) $meta['variante'],
            self::catalogoVariantes($user)
        ));
    }

    public static function normalizarVarianteFiltro(?Usuario $user, ?string $variante): ?string
    {
        $permitidas = self::variantesFiltroPermitidas($user);

        if ($permitidas === []) {
            return null;
        }

        if (count($permitidas) === 1) {
            return $permitidas[0];
        }

        if ($variante !== null && $variante !== '' && in_array($variante, $permitidas, true)) {
            return $variante;
        }

        return null;
    }

    public static function subtituloModulo(?Usuario $user): string
    {
        if (self::esVistaGlobal($user)) {
            return 'Supervise envíos activos. Filtre por tipo o abra el mapa global.';
        }

        if (UsuarioRol::esJefePlanta($user)) {
            return 'Solo traslados en curso desde planta hacia almacén mayorista.';
        }

        if (UsuarioRol::esJefeAgricultor($user)) {
            return 'Solo envíos agrícola → planta de sus transportistas.';
        }

        if (UsuarioRol::esMayorista($user) || UsuarioRol::esJefeMayorista($user)) {
            return 'Solo envíos vinculados a sus almacenes mayoristas.';
        }

        return 'Solo se muestran los envíos activos relacionados con su rol.';
    }

    /** @param  Collection<int, array<string, mixed>>  $items */
    public static function filtrarActivas(Collection $items, ?Usuario $user): Collection
    {
        if ($user === null || self::esVistaGlobal($user)) {
            return $items->values();
        }

        return $items
            ->filter(fn (array $item) => self::puedeVerItem($user, $item))
            ->values();
    }

    /** @param  array<string, mixed>  $item */
    public static function puedeVerItem(?Usuario $user, array $item): bool
    {
        if ($user === null) {
            return false;
        }

        if (UsuarioRol::esTransportista($user)) {
            return false;
        }

        if (self::esVistaGlobal($user)) {
            return true;
        }

        $tipo = (string) ($item['tipo'] ?? '');
        $id = (int) ($item['id'] ?? 0);

        return match ($tipo) {
            SimulacionRutaCatalogo::TIPO_AGRICOLA => self::puedeVerEnvioAgricola($user, $id),
            SimulacionRutaCatalogo::TIPO_DISTRIBUCION => self::puedeVerRutaDistribucion($user, $id),
            SimulacionRutaCatalogo::TIPO_PLANTA_MAYORISTA => self::puedeVerTrasladoPlantaMayorista($user, $id),
            default => false,
        };
    }

    public static function puedeVerEnvioAgricola(?Usuario $user, int $id): bool
    {
        if ($user === null || UsuarioRol::esTransportista($user)) {
            return false;
        }

        if (self::esVistaGlobal($user)) {
            return true;
        }

        $envio = EnvioAsignacionMultiple::query()
            ->with(['pedido', 'almacen'])
            ->find($id);

        if ($envio === null || ! SimulacionRutaCatalogo::simulacionActivaAgricola($envio)) {
            return false;
        }

        if (UsuarioRol::esJefeAgricultor($user)) {
            return CampoJefeScope::envioPerteneceAJefe($envio, $user)
                && self::envioEsAgricola($envio);
        }

        return false;
    }

    public static function puedeVerRutaDistribucion(?Usuario $user, int $id): bool
    {
        if ($user === null || UsuarioRol::esTransportista($user)) {
            return false;
        }

        if (self::esVistaGlobal($user)) {
            return true;
        }

        $ruta = RutaDistribucion::query()
            ->with(['pedidos.puntoVenta', 'almacenOrigen'])
            ->find($id);

        if ($ruta === null
            || RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta)
            || ! SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            return false;
        }

        if (UsuarioRol::esMayorista($user) || UsuarioRol::esJefeMayorista($user)) {
            $ids = MayoristaAccess::idsAlmacenesMayorista($user);

            return $ids !== []
                && in_array((int) $ruta->almacen_mayorista_origenid, $ids, true);
        }

        return false;
    }

    public static function puedeVerTrasladoPlantaMayorista(?Usuario $user, int $id): bool
    {
        if ($user === null || UsuarioRol::esTransportista($user)) {
            return false;
        }

        if (self::esVistaGlobal($user)) {
            return true;
        }

        $ruta = RutaDistribucion::query()->find($id);

        if ($ruta === null
            || ! RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta)
            || ! SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            return false;
        }

        if (UsuarioRol::esJefePlanta($user)) {
            return self::almacenEsPlanta((int) $ruta->almacen_planta_origenid);
        }

        if (UsuarioRol::esMayorista($user) || UsuarioRol::esJefeMayorista($user)) {
            $ids = MayoristaAccess::idsAlmacenesMayorista($user);

            return $ids !== []
                && in_array((int) $ruta->almacen_mayorista_destinoid, $ids, true);
        }

        return false;
    }

    private static function envioEsAgricola(EnvioAsignacionMultiple $envio): bool
    {
        $envio->loadMissing(['almacen', 'pedido']);

        if ($envio->almacen !== null) {
            return AlmacenAmbito::resolverAmbito($envio->almacen) === AlmacenAmbito::AGRICOLA;
        }

        return $envio->pedido !== null && self::pedidoDestinoEsPlanta($envio->pedido);
    }

    private static function pedidoDestinoEsPlanta(?Pedido $pedido): bool
    {
        if ($pedido === null) {
            return false;
        }

        $plantas = AlmacenAmbito::scope(
            Almacen::query()->where('activo', true),
            AlmacenAmbito::PLANTA
        )->get();

        foreach ($plantas as $planta) {
            $coords = UbicacionGpsParser::resolverAlmacen(
                (int) $planta->almacenid,
                $planta->nombre,
                $planta->ubicacion
            );

            if ($pedido->latitud !== null && $pedido->longitud !== null) {
                $latDiff = abs((float) $pedido->latitud - (float) $coords['lat']);
                $lngDiff = abs((float) $pedido->longitud - (float) $coords['lng']);
                if ($latDiff < 0.02 && $lngDiff < 0.02) {
                    return true;
                }
            }

            $nombrePlanta = trim((string) ($pedido->nombre_planta ?? ''));
            if ($nombrePlanta !== ''
                && stripos($nombrePlanta, (string) $planta->nombre) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function almacenEsPlanta(int $almacenId): bool
    {
        if ($almacenId <= 0) {
            return false;
        }

        $almacen = Almacen::query()->find($almacenId);

        return $almacen !== null
            && AlmacenAmbito::resolverAmbito($almacen) === AlmacenAmbito::PLANTA;
    }
}
