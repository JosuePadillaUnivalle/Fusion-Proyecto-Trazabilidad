<?php

namespace App\Support;

use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

final class DocumentoEntregaAcceso
{
    public static function esVistaGlobal(?Usuario $user): bool
    {
        return $user !== null && UsuarioRol::esAdminGlobal($user);
    }

    public static function puedeAccederModulo(?Usuario $user): bool
    {
        if ($user === null) {
            return false;
        }

        if (UsuarioRol::esTransportista($user)) {
            return $user->can('documentos.view');
        }

        return self::esVistaGlobal($user)
            || UsuarioRol::esJefePlanta($user)
            || UsuarioRol::esJefeAgricultor($user)
            || UsuarioRol::esJefeMayorista($user)
            || UsuarioRol::esMayorista($user)
            || ($user->can('documentos.view') && ! UsuarioRol::esMinorista($user));
    }

    /** @param  Builder<DocumentoEntrega>  $query */
    public static function aplicarFiltroRol(Builder $query, ?Usuario $user): Builder
    {
        if ($user === null || self::esVistaGlobal($user)) {
            return $query;
        }

        if (UsuarioRol::esTransportista($user)) {
            return DocumentoEntregaTransportista::restringirConsultaTransportista($query, (int) $user->usuarioid);
        }

        if (UsuarioRol::esJefeAgricultor($user)) {
            $envioIds = CampoJefeScope::idsEnviosAgricolasVisibles($user);
            if ($envioIds === []) {
                return $query->where('metadata->envio_cierre_agricola', true)->whereRaw('0 = 1');
            }

            return $query->where('metadata->envio_cierre_agricola', true)
                ->whereIn('metadata->envioasignacionmultipleid', $envioIds);
        }

        if (UsuarioRol::esJefePlanta($user)) {
            return $query->where('metadata->envio_cierre_planta_mayorista', true);
        }

        if (UsuarioRol::esMayorista($user) || UsuarioRol::esJefeMayorista($user)) {
            return self::aplicarFiltroMayorista($query, $user);
        }

        return $query;
    }

    public static function puedeVerDocumento(DocumentoEntrega $documento, ?Usuario $user): bool
    {
        if ($user === null) {
            return false;
        }

        if (UsuarioRol::esTransportista($user)) {
            return DocumentoEntregaTransportista::puedeVerDocumento($documento, (int) $user->usuarioid);
        }

        if (self::esVistaGlobal($user)) {
            return true;
        }

        $metadata = is_array($documento->metadata) ? $documento->metadata : [];

        if (UsuarioRol::esJefeAgricultor($user)) {
            if (empty($metadata['envio_cierre_agricola'])) {
                return false;
            }
            $envioId = (int) ($metadata['envioasignacionmultipleid'] ?? 0);
            if ($envioId <= 0) {
                return false;
            }
            $envio = EnvioAsignacionMultiple::query()->find($envioId);

            return $envio !== null && CampoJefeScope::envioPerteneceAJefe($envio, $user);
        }

        if (UsuarioRol::esJefePlanta($user)) {
            return ! empty($metadata['envio_cierre_planta_mayorista'])
                && self::trasladoPlantaVisibleParaPlanta($documento, $metadata);
        }

        if (UsuarioRol::esMayorista($user) || UsuarioRol::esJefeMayorista($user)) {
            return self::documentoVisibleParaMayorista($documento, $metadata, $user);
        }

        return $user->can('documentos.view');
    }

    /** @param  Builder<DocumentoEntrega>  $query */
    private static function aplicarFiltroMayorista(Builder $query, Usuario $user): Builder
    {
        $ids = MayoristaAccess::idsAlmacenesMayorista($user);
        if ($ids === []) {
            return $query->whereRaw('0 = 1');
        }

        $rutasDestino = RutaDistribucion::query()
            ->whereIn('almacen_mayorista_destinoid', $ids)
            ->pluck('rutadistribucionid')
            ->map(fn ($id) => (int) $id)
            ->all();

        $rutasOrigen = RutaDistribucion::query()
            ->whereIn('almacen_mayorista_origenid', $ids)
            ->where(function (Builder $q) {
                $q->whereNull('tipo_ruta')
                    ->orWhere('tipo_ruta', '!=', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA);
            })
            ->pluck('rutadistribucionid')
            ->map(fn ($id) => (int) $id)
            ->all();

        $rutaIds = array_values(array_unique(array_merge($rutasDestino, $rutasOrigen)));

        return $query->where(function (Builder $q) use ($rutaIds) {
            if ($rutaIds === []) {
                $q->whereRaw('0 = 1');

                return;
            }

            foreach ($rutaIds as $rutaId) {
                $q->orWhere('metadata->rutadistribucionid', $rutaId);
            }
        });
    }

    /** @param  array<string, mixed>  $metadata */
    private static function trasladoPlantaVisibleParaPlanta(DocumentoEntrega $documento, array $metadata): bool
    {
        $rutaId = (int) ($metadata['rutadistribucionid'] ?? 0);
        if ($rutaId <= 0) {
            return AlmacenAmbito::resolverAmbito($documento->almacen) === AlmacenAmbito::PLANTA;
        }

        $ruta = RutaDistribucion::query()->with('almacenPlantaOrigen')->find($rutaId);

        return $ruta !== null
            && RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta)
            && AlmacenAmbito::resolverAmbito($ruta->almacenPlantaOrigen) === AlmacenAmbito::PLANTA;
    }

    /** @param  array<string, mixed>  $metadata */
    private static function documentoVisibleParaMayorista(DocumentoEntrega $documento, array $metadata, Usuario $user): bool
    {
        $ids = MayoristaAccess::idsAlmacenesMayorista($user);
        if ($ids === []) {
            return false;
        }

        $rutaId = (int) ($metadata['rutadistribucionid'] ?? 0);
        if ($rutaId > 0) {
            $ruta = RutaDistribucion::query()->find($rutaId);
            if ($ruta === null) {
                return false;
            }

            if (RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta)) {
                return in_array((int) $ruta->almacen_mayorista_destinoid, $ids, true);
            }

            return in_array((int) $ruta->almacen_mayorista_origenid, $ids, true);
        }

        if (! empty($metadata['envio_cierre_planta_mayorista'])) {
            return in_array((int) $documento->almacenid, $ids, true);
        }

        return false;
    }
}
