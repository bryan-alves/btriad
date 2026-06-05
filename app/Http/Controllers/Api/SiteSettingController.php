<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSite;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        if (is_string($request->input('schedule'))) {
            $request->merge([
                'schedule' => json_decode($request->input('schedule'), true) ?: [],
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
            'logo' => ['nullable', 'image', 'max:2048'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'schedule' => ['nullable', 'array'],
            'schedule.*.day' => ['required_with:schedule', 'string', 'max:80'],
            'schedule.*.kids_time' => ['nullable', 'string', 'max:80'],
            'schedule.*.adults_time' => ['nullable', 'string', 'max:80'],
            'active' => ['required', 'boolean'],
        ]);

        $tenant->update([
            'name' => $data['name'],
        ]);

        $existingSite = $tenant->site;
        $logoPath = $data['logo_path'] ?? $existingSite?->logo_path;

        if ($request->hasFile('logo')) {
            if ($existingSite?->logo_path && str_starts_with($existingSite->logo_path, 'site-logos/')) {
                Storage::disk('public')->delete($existingSite->logo_path);
            }

            $logoPath = $request->file('logo')->store('site-logos', 'public');
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
                'whatsapp' => $data['whatsapp'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'address' => $data['address'] ?? null,
                'schedule' => $data['schedule'] ?? [],
                'active' => $data['active'],
            ],
        );

        return response()->json($tenant->fresh()->load(['domains', 'site']), 200);
    }
}
