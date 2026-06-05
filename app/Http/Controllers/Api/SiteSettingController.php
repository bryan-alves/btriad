<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSite;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function index()
    {
        $sites = Tenant::query()
            ->with(['domains', 'site'])
            ->whereHas('domains')
            ->orderBy('name')
            ->get();

        return response()->json($sites, 200);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academy_name' => ['required', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],
        ]);

        $tenant->update([
            'name' => $data['name'],
        ]);

        $site = TenantSite::query()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'academy_name' => $data['academy_name'],
                'primary_color' => $data['primary_color'],
                'logo_path' => $data['logo_path'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'address' => $data['address'] ?? null,
                'active' => $data['active'],
            ],
        );

        return response()->json($tenant->fresh()->load(['domains', 'site']), 200);
    }
}
