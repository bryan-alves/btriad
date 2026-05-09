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
            [
                'id' => 1,
                'name' => 'Branca',
                'slug' => 'white',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'name' => 'Cinza e Branco',
                'slug' => 'gray-white',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 3,
                'name' => 'Cinza',
                'slug' => 'gray',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 4,
                'name' => 'Cinza e Preto',
                'slug' => 'gray-black',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 5,
                'name' => 'Amarela e Branco',
                'slug' => 'yellow-white',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 6,
                'name' => 'Amarela',
                'slug' => 'yellow',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 7,
                'name' => 'Amarela e Preto',
                'slug' => 'yellow-black',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 8,
                'name' => 'Laranja',
                'slug' => 'orange',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 9,
                'name' => 'Laranja e Preto',
                'slug' => 'orange-black',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 10,
                'name' => 'Verde',
                'slug' => 'green',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 11,
                'name' => 'Verde e Preto',
                'slug' => 'green-black',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 12,
                'name' => 'Azul',
                'slug' => 'blue',

                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 13,
                'name' => 'Roxa',
                'slug' => 'purple',

                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 14,
                'name' => 'Marrom',
                'slug' => 'brown',

                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 15,
                'name' => 'Preta',
                'slug' => 'black',

                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);
    }
}
