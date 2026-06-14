<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

class TenantPwaIcons
{
    private const ALLOWED_SIZES = [180, 192, 512];

    public static function iconUrl(int $size): string
    {
        return url("/admin/pwa/icon-{$size}.png?v=".self::versionHash());
    }

    public static function appleTouchIconUrl(): string
    {
        return self::iconUrl(180);
    }

    /** @return list<array{src: string, sizes: string, type: string, purpose: string}> */
    public static function manifestIcons(): array
    {
        return [
            [
                'src' => self::iconUrl(192),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => self::iconUrl(512),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => self::iconUrl(512),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ];
    }

    public static function response(int $size): Response
    {
        if (! in_array($size, self::ALLOWED_SIZES, true)) {
            abort(404);
        }

        if (! extension_loaded('gd')) {
            return self::fallbackResponse();
        }

        $sourcePath = self::resolveSourcePath();
        $png = self::renderPng($sourcePath, $size);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private static function fallbackResponse(): Response
    {
        $path = self::resolveSourcePath();

        if (! is_file($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private static function versionHash(): string
    {
        $tenant = CurrentTenant::get();
        $site = $tenant?->site;

        return substr(md5(
            ($tenant?->id ?? 0).'|'.
            ($site?->logo_path ?? '').'|'.
            ($site?->nav_logo_path ?? '')
        ), 0, 8);
    }

    private static function resolveSourcePath(): string
    {
        $site = CurrentTenant::get()?->site;

        foreach ([$site?->logo_path, $site?->nav_logo_path] as $path) {
            $local = self::pathFromStoredLogo($path);
            if ($local !== null) {
                return $local;
            }
        }

        return public_path('img/logo/triangulo.png');
    }

    private static function pathFromStoredLogo(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        if (str_starts_with($path, 'site-logos/')) {
            $full = storage_path('app/public/'.$path);

            return is_file($full) ? $full : null;
        }

        $public = public_path(ltrim($path, '/'));

        return is_file($public) ? $public : null;
    }

    private static function renderPng(string $sourcePath, int $size): string
    {
        $source = self::loadImage($sourcePath);
        if ($source === false) {
            $source = self::loadImage(public_path('img/logo/triangulo.png'));
        }

        $canvas = imagecreatetruecolor($size, $size);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $scale = min($size / $srcW, $size / $srcH);
        $newW = (int) round($srcW * $scale);
        $newH = (int) round($srcH * $scale);
        $dstX = (int) round(($size - $newW) / 2);
        $dstY = (int) round(($size - $newH) / 2);

        imagecopyresampled($canvas, $source, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);
        imagedestroy($source);

        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();
        imagedestroy($canvas);

        return $png ?: '';
    }

    /** @return \GdImage|false */
    private static function loadImage(string $path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'gif' => @imagecreatefromgif($path),
            default => false,
        };
    }
}
