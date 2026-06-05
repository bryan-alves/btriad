<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Conta administrativa pessoal (ambiente local / staging).
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'btriad')->firstOrFail();

        User::withoutGlobalScope('tenant')->updateOrCreate(
            ['username' => 'bryanalves'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Bryan Alves',
                'role' => 'admin',
                'password' => '12122017@Br',
                'active' => true,
            ]
        );
    }
}
