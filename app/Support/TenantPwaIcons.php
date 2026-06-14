<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

class TenantPwaIcons
{
    private const ALLOWED_SIZES = [180, 192, 512];

    public static function appleTouchIconHref(): string
    {
        return self::staticIconUrl(180);
    }

    public static function appleTouchIconUrl(): string
    {
        return self::appleTouchIconHref();
    }

    /** @return list<array{src: string, sizes: string, type: string, purpose: string}> */
    public static function manifestIcons(): array
    {
        return [
            self::manifestEntry(180, 'any'),
            self::manifestEntry(192, 'any'),
            self::manifestEntry(512, 'any'),
            self::manifestEntry(512, 'maskable'),
        ];
    }

    public static function staticIconUrl(int $size): string
    {
        $path = self::staticIconPath($size);

        return $path ? asset($path) : asset('pwa/apple-touch-icon.png');
    }

    public static function response(int $size): Response
    {
        if (! in_array($size, self::ALLOWED_SIZES, true)) {
            abort(404);
        }

        if (extension_loaded('gd')) {
            $sourcePath = self::resolveExistingSourcePath();
            if ($sourcePath !== null) {
                $png = self::renderPng($sourcePath, $size);
                if ($png !== '') {
                    return self::pngResponse($png);
                }
            }
        }

        $staticPath = self::staticIconAbsolutePath($size);
        if ($staticPath !== null) {
            return response()->file($staticPath, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }

    /** @return array{src: string, sizes: string, type: string, purpose: string} */
    private static function manifestEntry(int $size, string $purpose): array
    {
        return [
            'src' => self::staticIconUrl($size),
            'sizes' => "{$size}x{$size}",
            'type' => 'image/png',
            'purpose' => $purpose,
        ];
    }

    private static function staticIconPath(int $size): ?string
    {
        if (TatameiroBranding::is(CurrentTenant::get())) {
            return match ($size) {
                180, 192 => TatameiroBranding::FAVICON,
                512 => TatameiroBranding::FAVICON,
                default => TatameiroBranding::FAVICON,
            };
        }

        return match ($size) {
            180 => 'pwa/apple-touch-icon.png',
            192 => 'pwa/icon-192.png',
            512 => 'pwa/icon-512.png',
            default => 'pwa/apple-touch-icon.png',
        };
    }

    private static function staticIconAbsolutePath(int $size): ?string
    {
        $relative = self::staticIconPath($size);
        if ($relative === null) {
            return null;
        }

        $absolute = public_path($relative);

        return is_file($absolute) ? $absolute : null;
    }

    private static function pngResponse(string $png): Response
    {
        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private static function resolveExistingSourcePath(): ?string
    {
        $candidates = [];

        if (TatameiroBranding::is(CurrentTenant::get())) {
            $candidates[] = public_path(TatameiroBranding::FAVICON);
        }

        $site = CurrentTenant::get()?->site;

        foreach ([$site?->nav_logo_path, $site?->logo_path] as $path) {
            $local = self::pathFromStoredLogo($path);
            if ($local !== null) {
                $candidates[] = $local;
            }
        }

        $candidates[] = public_path('logo.png');
        $candidates[] = public_path('pwa/apple-touch-icon.png');
        $candidates[] = public_path('img/logo/triangulo.png');

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
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
            $fallback = public_path('pwa/apple-touch-icon.png');
            $source = is_file($fallback) ? self::loadImage($fallback) : false;
        }

        if ($source === false) {
            return '';
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
