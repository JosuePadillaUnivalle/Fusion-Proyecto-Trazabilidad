<?php

namespace App\Support;

final class CultivoCatalogo
{
    /**
     * @return list<array{nombre:string, detalle:string}>
     */
    public static function variantes(): array
    {
        return [
            ['nombre' => 'Papa Imilla', 'detalle' => 'Piel clara; ideal para sopas y coccion casera.'],
            ['nombre' => 'Papa Huaycha', 'detalle' => 'Muy difundida en Bolivia; uso versatil en cocina.'],
            ['nombre' => 'Papa Desiree', 'detalle' => 'Piel roja y buena textura para pure o horno.'],
            ['nombre' => 'Papa Yungay', 'detalle' => 'Pulpa amarilla; recomendada para fritura y horneado.'],

            ['nombre' => 'Pimenton Rojo', 'detalle' => 'Sabor dulce y color intenso para ensaladas y salsas.'],
            ['nombre' => 'Pimenton Verde', 'detalle' => 'Mas fresco y herbal; usado en sofritos y guisos.'],
            ['nombre' => 'Pimenton Amarillo', 'detalle' => 'Dulce y aromático; buen rendimiento en cocina fresca.'],
            ['nombre' => 'Pimenton Morrón', 'detalle' => 'Fruto grande de pared gruesa, comercialmente estable.'],

            ['nombre' => 'Tomate Cherry', 'detalle' => 'Fruto pequeño y dulce; ideal para ensaladas.'],
            ['nombre' => 'Tomate Roma', 'detalle' => 'Pulpa firme y poca semilla; excelente para salsa.'],
            ['nombre' => 'Tomate Perita', 'detalle' => 'Forma alargada y buena conservacion postcosecha.'],
            ['nombre' => 'Tomate Beefsteak', 'detalle' => 'Fruto grande para consumo fresco y rebanado.'],

            ['nombre' => 'Zanahoria Nantes', 'detalle' => 'Raiz cilindrica y dulce, de ciclo medio.'],
            ['nombre' => 'Zanahoria Chantenay', 'detalle' => 'Corta y robusta; se adapta a suelos pesados.'],
            ['nombre' => 'Zanahoria Imperator', 'detalle' => 'Larga y delgada; buena para mercado fresco.'],

            ['nombre' => 'Lechuga Crespa', 'detalle' => 'Hojas rizadas y textura crujiente.'],
            ['nombre' => 'Lechuga Romana', 'detalle' => 'Hojas alargadas y firmes; alta preferencia comercial.'],
            ['nombre' => 'Lechuga Mantecosa', 'detalle' => 'Hojas suaves y sabor delicado para consumo fresco.'],
            ['nombre' => 'Lechuga Iceberg', 'detalle' => 'Cogollo compacto y buena vida util en frio.'],

            ['nombre' => 'Cebolla Roja', 'detalle' => 'Sabor medio y color atractivo para ensaladas.'],
            ['nombre' => 'Cebolla Blanca', 'detalle' => 'Sabor intenso; muy usada en coccion.'],
            ['nombre' => 'Cebolla Amarilla', 'detalle' => 'Equilibrada para salteados y guisos.'],
            ['nombre' => 'Cebolla Morada', 'detalle' => 'Aromatica y de buena aceptacion en mercado fresco.'],

            ['nombre' => 'Maiz Amarillo Duro', 'detalle' => 'Uso principal en alimento balanceado e industria.'],
            ['nombre' => 'Maiz Blanco', 'detalle' => 'Preferido para consumo humano y preparaciones locales.'],
            ['nombre' => 'Maiz Dulce', 'detalle' => 'Cosecha temprana para consumo fresco.'],
        ];
    }

    public static function detallePorNombre(?string $nombre): ?string
    {
        if ($nombre === null || trim($nombre) === '') {
            return null;
        }

        $needle = mb_strtolower(trim($nombre));
        foreach (self::variantes() as $item) {
            if (mb_strtolower($item['nombre']) === $needle) {
                return $item['detalle'];
            }
        }

        // Compatibilidad con registros base antiguos (sin variante).
        return match ($needle) {
            'papa' => 'Cultivo andino de alto consumo; existen variedades para fritura, pure y coccion.',
            'pimenton', 'pimentón', 'pimienton', 'pimientón' => 'Hortaliza de fruto carnoso; se produce en variedades rojas, verdes y amarillas.',
            'tomate' => 'Fruto horticola versatil para consumo fresco y procesado en salsa.',
            'zanahoria' => 'Raiz comestible rica en caroteno; se adapta bien a manejo intensivo.',
            'lechuga' => 'Hortaliza de hoja para consumo fresco con ciclos cortos de produccion.',
            'cebolla' => 'Bulbo de alto uso culinario; disponible en tipos blanca, roja y amarilla.',
            'maiz', 'maíz' => 'Cereal base para alimento y consumo humano, con variedades blanco, dulce y amarillo.',
            default => null,
        };
    }
}
