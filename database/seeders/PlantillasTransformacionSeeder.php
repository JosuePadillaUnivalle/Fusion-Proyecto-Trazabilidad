<?php

namespace Database\Seeders;

use App\Models\MaquinaPlanta;
use App\Models\PlantillaTransformacion;
use App\Models\ProcesoPlanta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Plantillas predefinidas de línea de transformación.
 * php artisan db:seed --class=PlantillasTransformacionSeeder
 */
class PlantillasTransformacionSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('plantilla_transformacion')) {
            return;
        }

        $definiciones = [
            [
                'nombre' => 'Puré de papa',
                'producto_ejemplo' => 'Puré de papa',
                'palabras_clave' => ['puré', 'pure', 'papa', 'patata'],
                'descripcion' => 'Línea para puré envasado (~300 g). Lavado, cocción, mezcla y empaque.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Lavado y selección'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Cocción / blanqueado'],
                    ['proceso' => 'Mezclado', 'maquina' => 'MX-200', 'notas' => 'Puré y homogeneización'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Llenado de envases'],
                    ['proceso' => 'Etiquetado', 'maquina' => 'ET-800', 'notas' => 'Etiqueta nutricional'],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => 'Cierre de caja'],
                ],
            ],
            [
                'nombre' => 'Papa frita congelada',
                'producto_ejemplo' => 'Papas fritas',
                'palabras_clave' => ['frita', 'fritas', 'snack', 'congelada', 'papa'],
                'descripcion' => 'Corte, prefrito y empaque para papas fritas o bastones.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'L-100', 'notas' => 'Lavado'],
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Corte en bastones'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Freído / prefrito'],
                    ['proceso' => 'Secado', 'maquina' => 'SC-500', 'notas' => 'Escurrido y secado superficial'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Bolsa o caja'],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => 'Empaque final'],
                ],
            ],
            [
                'nombre' => 'Papas chips',
                'producto_ejemplo' => 'Papas chips',
                'palabras_clave' => ['chips', 'snack', 'papa', 'frita'],
                'descripcion' => 'Snack de papa laminada, frita y empaquetada.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Laminado fino'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Freído'],
                    ['proceso' => 'Secado', 'maquina' => 'SC-500', 'notas' => 'Reducir humedad'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Bolsa snack'],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => 'Display o caja'],
                ],
            ],
            [
                'nombre' => 'Zanahoria en conserva',
                'producto_ejemplo' => 'Zanahoria en conserva',
                'palabras_clave' => ['zanahoria', 'conserva', 'rallad'],
                'descripcion' => 'Rallado, blanqueado y envasado de zanahoria.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'L-100', 'notas' => 'Lavado'],
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Rallado / cubos'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Blanqueado'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Frasco o bolsa'],
                    ['proceso' => 'Etiquetado', 'maquina' => 'ET-800', 'notas' => null],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
            [
                'nombre' => 'Puré de zanahoria',
                'producto_ejemplo' => 'Puré de zanahoria',
                'palabras_clave' => ['puré', 'pure', 'zanahoria'],
                'descripcion' => 'Puré de zanahoria para bebé o industrial.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'L-100', 'notas' => 'Lavado y pelado'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Cocción'],
                    ['proceso' => 'Mezclado', 'maquina' => 'MX-200', 'notas' => 'Homogeneizar'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => null],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
            [
                'nombre' => 'Salsa de tomate',
                'producto_ejemplo' => 'Salsa de tomate',
                'palabras_clave' => ['tomate', 'salsa', 'ketchup', 'puré'],
                'descripcion' => 'Triturado, cocción y envasado de tomate.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Selección'],
                    ['proceso' => 'Mezclado', 'maquina' => 'MX-200', 'notas' => 'Triturado / molienda'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Cocción y concentración'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Frasco o sachet'],
                    ['proceso' => 'Etiquetado', 'maquina' => 'ET-800', 'notas' => null],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
            [
                'nombre' => 'Cebolla en cubos IQF',
                'producto_ejemplo' => 'Cebolla en cubos',
                'palabras_clave' => ['cebolla', 'cubo', 'iqf'],
                'descripcion' => 'Corte de cebolla y envasado para congelado.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'L-100', 'notas' => 'Pelado y lavado'],
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Cubos uniformes'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Bolsa IQF'],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
            [
                'nombre' => 'Cebolla deshidratada en polvo',
                'producto_ejemplo' => 'Cebolla deshidratada en polvo',
                'palabras_clave' => ['cebolla', 'polvo', 'deshidratada', 'deshidratado'],
                'descripcion' => 'Transformación de cebolla fresca a polvo deshidratado comercial.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'L-100', 'notas' => 'Recepción, pesaje y lavado de cebolla fresca de campo'],
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Pelado, corte en láminas y selección de calibre'],
                    ['proceso' => 'Secado', 'maquina' => 'SC-500', 'notas' => 'Deshidratación térmica hasta humedad objetivo'],
                    ['proceso' => 'Mezclado', 'maquina' => 'MX-200', 'notas' => 'Molienda fina a polvo homogéneo'],
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Tamizado y control de granulometría'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Envasado en bolsa aluminizada anti-humedad'],
                    ['proceso' => 'Etiquetado', 'maquina' => 'ET-800', 'notas' => 'Lote, fecha y datos nutricionales'],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => 'Embalaje para despacho a centro mayorista'],
                ],
            ],
            [
                'nombre' => 'Mix vegetal ensalada',
                'producto_ejemplo' => 'Mix vegetal',
                'palabras_clave' => ['mix', 'ensalada', 'vegetal', 'lechuga'],
                'descripcion' => 'Mezcla de hortalizas listas para consumo.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'BC-20', 'notas' => 'Lavado y corte'],
                    ['proceso' => 'Mezclado', 'maquina' => 'MX-200', 'notas' => 'Mezcla de componentes'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Bolsa MAP'],
                    ['proceso' => 'Etiquetado', 'maquina' => 'ET-800', 'notas' => 'Fecha de vencimiento'],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
            [
                'nombre' => 'Harina precocida',
                'producto_ejemplo' => 'Harina precocida',
                'palabras_clave' => ['harina', 'precocida', 'mandioca', 'yuca'],
                'descripcion' => 'Secado y molienda de tubérculos.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'L-100', 'notas' => 'Lavado y rallado'],
                    ['proceso' => 'Secado', 'maquina' => 'SC-500', 'notas' => 'Secado de pulpa'],
                    ['proceso' => 'Mezclado', 'maquina' => 'MX-200', 'notas' => 'Molienda fina'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Saco o bolsa'],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
            [
                'nombre' => 'Snack extruido',
                'producto_ejemplo' => 'Snack extruido',
                'palabras_clave' => ['extruido', 'snack', 'maíz', 'expandido'],
                'descripcion' => 'Extrusión, cocción y empaque de snacks.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'MX-200', 'notas' => 'Premezcla'],
                    ['proceso' => 'Extrusión', 'maquina' => 'EX-300', 'notas' => 'Formado'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Expansión / horneado'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => null],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
            [
                'nombre' => 'Galleta o masa moldeada',
                'producto_ejemplo' => 'Galleta artesanal',
                'palabras_clave' => ['galleta', 'molde', 'masa', 'horneado'],
                'descripcion' => 'Mezcla, moldeo, horneado y empaque.',
                'pasos' => [
                    ['proceso' => 'Mezclado', 'maquina' => 'MX-200', 'notas' => 'Amasado'],
                    ['proceso' => 'Moldeo', 'maquina' => 'MD-400', 'notas' => 'Corte o molde'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Horneado'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Flow pack'],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
            [
                'nombre' => 'Jugo pasteurizado',
                'producto_ejemplo' => 'Jugo de fruta',
                'palabras_clave' => ['jugo', 'zumo', 'bebida', 'pasteurizado'],
                'descripcion' => 'Extracción, pasteurización y envasado de jugo.',
                'pasos' => [
                    ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'L-100', 'notas' => 'Lavado fruta'],
                    ['proceso' => 'Mezclado', 'maquina' => 'MX-200', 'notas' => 'Extracción / pulpa'],
                    ['proceso' => 'Tratamiento Térmico', 'maquina' => 'TR-600', 'notas' => 'Pasteurización'],
                    ['proceso' => 'Envasado', 'maquina' => 'EV-700', 'notas' => 'Botella o tetra'],
                    ['proceso' => 'Etiquetado', 'maquina' => 'ET-800', 'notas' => null],
                    ['proceso' => 'Empaquetado', 'maquina' => 'SE-10', 'notas' => null],
                ],
            ],
        ];

        $creadas = 0;

        foreach ($definiciones as $def) {
            $plantilla = PlantillaTransformacion::updateOrCreate(
                ['nombre' => $def['nombre']],
                [
                    'descripcion' => $def['descripcion'],
                    'producto_ejemplo' => $def['producto_ejemplo'],
                    'palabras_clave' => json_encode($def['palabras_clave'], JSON_UNESCAPED_UNICODE),
                    'activo' => true,
                ]
            );

            $plantilla->pasos()->delete();
            $orden = 1;

            foreach ($def['pasos'] as $pasoDef) {
                $proceso = ProcesoPlanta::query()->where('nombre', $pasoDef['proceso'])->first();
                if (! $proceso) {
                    continue;
                }

                $maquinaId = null;
                if (! empty($pasoDef['maquina'])) {
                    $maquinaId = MaquinaPlanta::query()->where('codigo', $pasoDef['maquina'])->value('maquinaplantaid');
                }

                $plantilla->pasos()->create([
                    'orden' => $orden++,
                    'procesoplantaid' => $proceso->procesoplantaid,
                    'maquinaplantaid' => $maquinaId,
                    'notas' => $pasoDef['notas'] ?? null,
                ]);
            }

            $creadas++;
        }

        $this->command?->info("Plantillas de transformación: {$creadas} rutas predefinidas.");
    }
}
