<?php

namespace App\Support;

final class UsuarioInformacionAdicional
{
    private const ETIQUETAS = [
        'ci' => 'CI / NIT',
        'licencia' => 'Licencia',
        'estado_logistico' => 'Estado logístico',
    ];

    /** @return list<array{label: string, value: string}> */
    public static function lineasParaVista(?string $valor): array
    {
        if ($valor === null || trim($valor) === '') {
            return [];
        }

        $valor = trim($valor);

        if (str_starts_with($valor, '{')) {
            return self::lineasDesdeJson($valor);
        }

        $texto = trim($valor);
        if (preg_match('/^\[MOD-[^\]]+\]\s*(.*)$/us', $texto, $coincidencias)) {
            $texto = trim($coincidencias[1]);
        }

        if ($texto === '') {
            return [];
        }

        if (str_starts_with($texto, '{')) {
            return self::lineasDesdeJson($texto);
        }

        return [['label' => 'Notas', 'value' => $texto]];
    }

    /** @return list<array{label: string, value: string}> */
    private static function lineasDesdeJson(string $json): array
    {
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return [];
        }

        $lineas = [];

        foreach ($data as $bloque) {
            if (! is_array($bloque)) {
                continue;
            }
            foreach ($bloque as $clave => $valor) {
                if ($valor === null || $valor === '') {
                    continue;
                }
                if (in_array($clave, ['estado_logistico', 'licencia', 'ci'], true)) {
                    continue;
                }
                $lineas[] = [
                    'label' => self::ETIQUETAS[$clave] ?? ucfirst(str_replace('_', ' ', (string) $clave)),
                    'value' => (string) $valor,
                ];
            }
        }

        return $lineas;
    }
}
