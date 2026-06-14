<?php

namespace Database\Seeders;

use App\Models\PlatformClient;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PlatformClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['slug' => 'btriad', 'sort_order' => 0],
            ['slug' => 'apjiujitsu', 'sort_order' => 1],
        ];

        foreach ($clients as $item) {
            $tenant = Tenant::query()->where('slug', $item['slug'])->first();

            if ($tenant === null) {
                continue;
            }

            PlatformClient::query()->updateOrCreate(
                ['client_tenant_id' => $tenant->id],
                [
                    'name' => $tenant->name,
                    'sort_order' => $item['sort_order'],
                    'active' => true,
                ],
            );
        }
    }
}
