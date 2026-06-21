<?php

namespace App\Support;

final class AlmacenajeLoteCondiciones
{
    /** @return list<string> */
    public static function opciones(): array
    {
        return [
            'A temperatura ambiente',
            'A temperatura controlada (2–8 °C)',
            'Refrigerado (0–4 °C)',
            'Congelado (-18 °C o menos)',
            'Ambiente seco',
            'Ambiente ventilado',
            'Protegido de luz solar',
        ];
    }
}
