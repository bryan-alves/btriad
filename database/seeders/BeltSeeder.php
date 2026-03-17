<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;

class BeltSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('belts')->insert([

            // Branca
            [
                'id' => 1,
                'name' => 'Branca',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'name' => 'Branca',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // Cinza
            [
                'id' => 3,
                'name' => 'Cinza e Branco',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 4,
                'name' => 'Cinza',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 5,
                'name' => 'Cinza e Preto',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // Amarela
            [
                'id' => 6,
                'name' => 'Amarela e Branco',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 7,
                'name' => 'Amarela',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 8,
                'name' => 'Amarela e Preto',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // Laranja
            [
                'id' => 9,
                'name' => 'Laranja e Branco',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 10,
                'name' => 'Laranja',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 11,
                'name' => 'Laranja e Preto',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // Verde
            [
                'id' => 12,
                'name' => 'Verde e Branco',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 13,
                'name' => 'Verde',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 14,
                'name' => 'Verde e Preto',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // Adulto
            [
                'id' => 15,
                'name' => 'Azul',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 16,
                'name' => 'Roxa',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 17,
                'name' => 'Marrom',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 18,
                'name' => 'Preta',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ]

        ]);
    }
}
