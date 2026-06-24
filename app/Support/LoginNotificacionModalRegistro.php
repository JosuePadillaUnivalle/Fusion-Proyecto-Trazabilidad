<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class LoginNotificacionModalRegistro
{
    public function __construct(
        private readonly string $cacheKeyPrefix,
    ) {}

    /** @return list<string> */
    public function clavesVistas(int $usuarioid): array
    {
        $claves = Cache::get($this->cacheKey($usuarioid), []);

        return is_array($claves) ? array_values(array_unique($claves)) : [];
    }

    public function yaVio(int $usuarioid, string $clave): bool
    {
        return in_array($clave, $this->clavesVistas($usuarioid), true);
    }

    /**
     * @param  list<array{clave: string}>  $items
     * @return list<array{clave: string}>
     */
    public function filtrar(int $usuarioid, array $items): array
    {
        return array_values(array_filter(
            $items,
            fn (array $row) => ! $this->yaVio($usuarioid, (string) ($row['clave'] ?? ''))
        ));
    }

    /** @param  list<string>  $claves */
    public function marcarClaves(int $usuarioid, array $claves): void
    {
        $claves = array_values(array_filter($claves, fn ($c) => is_string($c) && $c !== ''));
        if ($claves === []) {
            return;
        }

        $existentes = $this->clavesVistas($usuarioid);
        Cache::forever(
            $this->cacheKey($usuarioid),
            array_values(array_unique(array_merge($existentes, $claves)))
        );
    }

    /**
     * @param  list<array{clave: string}>  $items
     */
    public function marcarItems(int $usuarioid, array $items): void
    {
        $claves = array_map(fn (array $row) => (string) ($row['clave'] ?? ''), $items);
        $this->marcarClaves($usuarioid, $claves);
    }

    private function cacheKey(int $usuarioid): string
    {
        return $this->cacheKeyPrefix.':'.$usuarioid;
    }
}
