<?php

namespace App\Support;

use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class UsuarioAvatar
{
    /** URLs externas legacy que ya no responden (Supabase demo). */
    private const LEGACY_DEAD_HOSTS = [
        'bsmobatqfjmrfiipkimu.supabase.co',
        'agronexus-bucket',
    ];

    public static function resolve(?Usuario $usuario): string
    {
        $raw = trim((string) ($usuario?->imagenurl ?? ''));

        if ($raw === '' || self::isLegacyDeadUrl($raw)) {
            return self::placeholder();
        }

        if (str_starts_with($raw, 'data:image/')) {
            return $raw;
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        if (str_starts_with($raw, '/storage/')) {
            $relative = ltrim(Str::after($raw, '/storage/'), '/');
            if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                return asset('storage/'.$relative);
            }

            return self::placeholder();
        }

        if (str_starts_with($raw, 'storage/')) {
            $relative = Str::after($raw, 'storage/');
            if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                return asset('storage/'.$relative);
            }

            return self::placeholder();
        }

        if (is_file(public_path($raw))) {
            return asset($raw);
        }

        return self::placeholder();
    }

    public static function placeholder(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">'
            .'<rect fill="#2c5530" width="128" height="128" rx="64"/>'
            .'<circle cx="64" cy="48" r="22" fill="#e8f5e9"/>'
            .'<ellipse cx="64" cy="98" rx="36" ry="28" fill="#e8f5e9"/>'
            .'</svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public static function storeUpload(Usuario $usuario, \Illuminate\Http\UploadedFile $file): string
    {
        self::deleteStoredFile($usuario->imagenurl);

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $ext = 'jpg';
        }

        $path = $file->storeAs(
            'avatars',
            'usuario-'.$usuario->usuarioid.'-'.time().'.'.$ext,
            'public'
        );

        return '/storage/'.$path;
    }

    public static function deleteStoredFile(?string $imagenurl): void
    {
        $relative = self::storageRelativePath($imagenurl);
        if ($relative !== null) {
            Storage::disk('public')->delete($relative);
        }
    }

    public static function storageRelativePath(?string $imagenurl): ?string
    {
        $raw = trim((string) $imagenurl);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, '/storage/')) {
            $relative = ltrim(Str::after($raw, '/storage/'), '/');

            return $relative !== '' ? $relative : null;
        }

        if (str_starts_with($raw, 'storage/')) {
            $relative = ltrim(Str::after($raw, 'storage/'), '/');

            return $relative !== '' ? $relative : null;
        }

        return null;
    }

    private static function isLegacyDeadUrl(string $url): bool
    {
        foreach (self::LEGACY_DEAD_HOSTS as $fragment) {
            if (str_contains($url, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
