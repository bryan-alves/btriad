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
        return self::appleTouchIconHref();
    }

    public static function appleTouchIconHref(): string
    {
        return url('/admin/pwa/icon-180.png');
    }

    /** @return list<array{src: string, sizes: string, type: string, purpose: string}> */
    public static function manifestIcons(): array
    {
        return [
            [
                'src' => self::iconUrl(180),
                'sizes' => '180x180',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
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
            return self::fallbackResponse($size);
        }

        $sourcePath = self::resolveSourcePath();
        $png = self::renderPng($sourcePath, $size);

        if ($png === '') {
            return self::fallbackResponse($size);
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private static function fallbackResponse(int $size): Response
    {
        if (extension_loaded('gd')) {
            $sourcePath = self::resolveSourcePath();
            $png = self::renderPng($sourcePath, $size);

            if ($png !== '') {
                return response($png, 200, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

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
        if (TatameiroBranding::is(CurrentTenant::get())) {
            return public_path(TatameiroBranding::FAVICON);
        }

        $site = CurrentTenant::get()?->site;

        foreach ([$site?->nav_logo_path, $site?->logo_path] as $path) {
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
        [$red, $green, $blue] = self::resolveBackgroundColor();
        $background = imagecolorallocate($canvas, $red, $green, $blue);
        imagefill($canvas, 0, 0, $background);
        imagealphablending($canvas, true);

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $padding = (int) round($size * 0.1);
        $inner = $size - ($padding * 2);
        $scale = min($inner / $srcW, $inner / $srcH);
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

    /** @return array{0: int, 1: int, 2: int} */
    private static function resolveBackgroundColor(): array
    {
        $site = CurrentTenant::get()?->site;
        $hex = ltrim((string) ($site?->app_header_color ?? '#1b1b18'), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            $hex = '1b1b18';
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
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
