<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Conta administrativa pessoal (ambiente local / staging).
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'bryanalves'],
            [
                'name' => 'Bryan Alves',
                'role' => 'admin',
                'password' => '12122017@Br',
            ]
        );
    }
}
