<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\TenantSite;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Equipe B-Triad Jiu-Jitsu',
                'slug' => 'btriad',
                'domains' => ['localhost', '127.0.0.1', 'btriadjiujitsu.com.br'],
                'site' => [
                    'academy_name' => 'Equipe B-Triad Jiu-Jitsu',
                    'primary_color' => '#c41e3a',
                    'logo_path' => 'logo.png',
                ],
            ],
            [
                'name' => 'AP Jiu-Jitsu',
                'slug' => 'apjiujitsu',
                'domains' => ['apjiujitsu.com.br'],
                'site' => [
                    'academy_name' => 'AP Jiu-Jitsu',
                    'primary_color' => '#c97716',
                    'logo_path' => 'apjiujitsu-logo.svg',
                ],
            ],
        ];

        $domains = collect($tenants)->flatMap(fn (array $tenant) => $tenant['domains']);

        Domain::whereNotIn('domain', $domains)->delete();

        foreach ($tenants as $data) {
            $tenant = Tenant::updateOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name']],
            );

            foreach ($data['domains'] as $domain) {
                Domain::updateOrCreate(
                    ['domain' => $domain],
                    ['tenant_id' => $tenant->id],
                );
            }

            TenantSite::updateOrCreate(
                ['tenant_id' => $tenant->id],
                $data['site'],
            );
        }

        $apTenant = Tenant::where('slug', 'apjiujitsu')->firstOrFail();

        User::withoutGlobalScope('tenant')->updateOrCreate(
            ['username' => 'petterin'],
            [
                'tenant_id' => $apTenant->id,
                'name' => 'Andre Petterin',
                'role' => 'admin',
                'password' => Hash::make('petterin@32145'),
                'active' => true,
            ],
        );
    }
}
