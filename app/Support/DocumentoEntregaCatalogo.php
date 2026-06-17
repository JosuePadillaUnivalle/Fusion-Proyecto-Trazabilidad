<?php

namespace App\Support;

use App\Models\DocumentoEntrega;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

final class DocumentoEntregaCatalogo
{
    /** @return array<string, string> */
    public static function tiposDocumento(): array
    {
        return [
            'pod' => 'POD / comprobante entrega',
            'nota_entrega' => 'Nota entrega',
            'guia_transporte' => 'Guía transporte',
            'guia_entrega' => 'Guía entrega',
            'confirmacion_entrega' => 'Confirmación entrega',
            'evidencia' => 'Evidencia',
        ];
    }

    public static function etiquetaTipo(?string $tipo): string
    {
        if ($tipo === null || $tipo === '') {
            return 'Sin tipo';
        }

        return self::tiposDocumento()[$tipo] ?? str_replace('_', ' ', $tipo);
    }

    /** @param  Builder<DocumentoEntrega>  $query */
    public static function aplicarFiltroOperativo(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $q) {
                $q->whereNull('archivo_path')
                    ->orWhere('archivo_path', 'not like', 'demo/%');
            })
            ->where(function (Builder $q) {
                $q->whereNull('externo_envio_id')
                    ->orWhere(function (Builder $w) {
                        $w->where('externo_envio_id', 'not like', 'MOD-PANEL-%')
                            ->where('externo_envio_id', 'not like', 'ENV-MOD-%');
                    });
            })
            ->where('titulo', 'not like', '[DEMO%')
            ->where('titulo', 'not like', '[demo%')
            ->where('titulo', 'not like', '[MOD-PANEL%')
            ->where('titulo', 'not like', '%MOD-PANEL-%');
    }

    public static function esDemo(DocumentoEntrega $documento): bool
    {
        $path = (string) ($documento->archivo_path ?? '');
        if ($path !== '' && str_starts_with($path, 'demo/')) {
            return true;
        }

        $ext = (string) ($documento->externo_envio_id ?? '');
        if (preg_match('/^(MOD-PANEL-|ENV-MOD-)/i', $ext)) {
            return true;
        }

        if (self::textoEsDemo($documento->titulo)) {
            return true;
        }

        $metadata = is_array($documento->metadata) ? $documento->metadata : [];
        foreach (['mod_panel', 'mod_env', 'mod_log', 'mod_planta', 'demo_xtra2', 'demo_b6', 'demo_b7', 'sin_archivo_real'] as $clave) {
            if (! empty($metadata[$clave])) {
                return true;
            }
        }

        return false;
    }

    public static function etiquetaUsuario(?Usuario $usuario): string
    {
        if ($usuario === null) {
            return 'Sin registrar';
        }

        $nombre = trim(($usuario->nombre ?? '').' '.($usuario->apellido ?? ''));
        if ($nombre !== '') {
            return $nombre;
        }

        if (filled($usuario->nombreusuario)) {
            return (string) $usuario->nombreusuario;
        }

        if (filled($usuario->email)) {
            return (string) $usuario->email;
        }

        return 'Usuario del sistema';
    }

    public static function etiquetaVinculo(DocumentoEntrega $documento): string
    {
        if (filled($documento->externo_envio_id)) {
            return (string) $documento->externo_envio_id;
        }

        if ($documento->pedidoid) {
            return 'Pedido #'.$documento->pedidoid;
        }

        return 'Sin vínculo';
    }

    private static function textoEsDemo(?string $texto): bool
    {
        $t = trim((string) $texto);
        if ($t === '') {
            return false;
        }

        if (EtiquetaDemo::esDemo($t)) {
            return true;
        }

        return (bool) preg_match('/\[(MOD-PANEL|DEMO|demo)/i', $t)
            || (bool) preg_match('/MOD-PANEL-/i', $t);
    }
}
