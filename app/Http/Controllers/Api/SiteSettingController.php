<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSite;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SiteSettingController extends Controller
{
    public function index()
    {
        $site = CurrentTenant::get()
            ->load(['domains', 'site']);

        return response()->json($site, 200);
    }

    public function update(Request $request, Tenant $tenant)
    {
        if ((int) $tenant->id !== (int) CurrentTenant::id()) {
            abort(403, 'Você só pode alterar o site do domínio atual.');
        }

        if (is_string($request->input('carousel_images'))) {
            $request->merge([
                'carousel_images' => json_decode($request->input('carousel_images'), true) ?: [],
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academy_name' => ['required', 'string', 'max:255'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'header_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'background_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'trial_button_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'portal_button_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'app_primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'app_header_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'app_background_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'app_login_background_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'nav_logo_path' => ['nullable', 'string', 'max:255'],
            'nav_logo' => ['nullable', 'image', 'max:5120'],
            'footer_logo_path' => ['nullable', 'string', 'max:255'],
            'footer_logo' => ['nullable', 'image', 'max:5120'],
            'hero_logo_path' => ['nullable', 'string', 'max:255'],
            'hero_logo' => ['nullable', 'image', 'max:5120'],
            'carousel_images' => ['nullable', 'array', 'max:5'],
            'carousel_images.*' => ['string', 'max:255'],
            'carousel_photos' => ['nullable', 'array', 'max:5'],
            'carousel_photos.*' => ['image', 'max:5120'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],
        ]);

        $tenant->update([
            'name' => $data['name'],
        ]);

        $existingSite = $tenant->site;
        $logoPath = $this->resolveLogoPath(
            $request,
            'logo',
            $data['logo_path'] ?? null,
            $existingSite?->logo_path,
        );
        $navLogoPath = $this->resolveLogoPath(
            $request,
            'nav_logo',
            $data['nav_logo_path'] ?? null,
            $existingSite?->nav_logo_path,
        );
        $footerLogoPath = $this->resolveLogoPath(
            $request,
            'footer_logo',
            $data['footer_logo_path'] ?? null,
            $existingSite?->footer_logo_path,
        );
        $heroLogoPath = $this->resolveLogoPath(
            $request,
            'hero_logo',
            $data['hero_logo_path'] ?? null,
            $existingSite?->hero_logo_path,
        );

        $keptCarouselImages = collect($data['carousel_images'] ?? [])
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->values()
            ->all();
        $newCarouselFiles = $request->file('carousel_photos', []);

        if (count($keptCarouselImages) + count($newCarouselFiles) > 5) {
            throw ValidationException::withMessages([
                'carousel_photos' => ['O carrossel pode ter no máximo 5 fotos.'],
            ]);
        }

        foreach (($existingSite?->carousel_images ?? []) as $existingCarouselImage) {
            if (
                str_starts_with($existingCarouselImage, 'site-carousel/')
                && ! in_array($existingCarouselImage, $keptCarouselImages, true)
            ) {
                Storage::disk('public')->delete($existingCarouselImage);
            }
        }

        foreach ($newCarouselFiles as $file) {
            $keptCarouselImages[] = $file->store('site-carousel', 'public');
        }

        $site = TenantSite::query()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'academy_name' => $data['academy_name'],
                'page_title' => $data['page_title'] ?? null,
                'hero_title' => $data['hero_title'] ?? null,
                'hero_subtitle' => $data['hero_subtitle'] ?? null,
                'primary_color' => $data['primary_color'],
                'header_color' => $data['header_color'],
                'background_color' => $data['background_color'],
                'trial_button_color' => $data['trial_button_color'],
                'portal_button_color' => $data['portal_button_color'],
                'app_primary_color' => $data['app_primary_color'],
                'app_header_color' => $data['app_header_color'],
                'app_background_color' => $data['app_background_color'],
                'app_login_background_color' => $data['app_login_background_color'],
                'logo_path' => $logoPath ?: null,
                'nav_logo_path' => $navLogoPath ?: null,
                'footer_logo_path' => $footerLogoPath ?: null,
                'hero_logo_path' => $heroLogoPath ?: null,
                'carousel_images' => $keptCarouselImages,
                'whatsapp' => $data['whatsapp'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'youtube' => $data['youtube'] ?? null,
                'address' => $data['address'] ?? null,
                'active' => $data['active'],
            ],
        );

        return response()->json($tenant->fresh()->load(['domains', 'site']), 200);
    }

    private function resolveLogoPath(
        Request $request,
        string $fileKey,
        ?string $submittedPath,
        ?string $existingPath,
    ): ?string {
        if ($request->hasFile($fileKey)) {
            $this->deleteStoredLogo($existingPath);

            /** @var UploadedFile $file */
            $file = $request->file($fileKey);

            return $file->store('site-logos', 'public');
        }

        return $submittedPath ?? $existingPath;
    }

    private function deleteStoredLogo(?string $path): void
    {
        if ($path && str_starts_with($path, 'site-logos/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
