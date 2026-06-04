<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Equipe B-Triad Jiu-Jitsu',
                'slug' => 'btriad',
                'domains' => ['localhost', '127.0.0.1', 'btriadjiujitsu.com.br'],
            ],
            [
                'name' => 'AP Jiu-Jitsu',
                'slug' => 'apjiujitsu',
                'domains' => ['apjiujitsu.com.br'],
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
        }
    }
}
