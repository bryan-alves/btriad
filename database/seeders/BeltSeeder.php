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
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'name' => 'Branca',
                'slug' => 'white',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 3,
                'name' => 'Cinza e Branco',
                'slug' => 'gray-white',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 4,
                'name' => 'Cinza',
                'slug' => 'gray',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 5,
                'name' => 'Cinza e Preto',
                'slug' => 'gray-black',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 6,
                'name' => 'Amarela e Branco',
                'slug' => 'yellow-white',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 7,
                'name' => 'Amarela',
                'slug' => 'yellow',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 8,
                'name' => 'Amarela e Preto',
                'slug' => 'yellow-black',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 9,
                'name' => 'Laranja e Branco',
                'slug' => 'orange-white',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 10,
                'name' => 'Laranja',
                'slug' => 'orange',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 11,
                'name' => 'Laranja e Preto',
                'slug' => 'orange-black',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 12,
                'name' => 'Verde e Branco',
                'slug' => 'green-white',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 13,
                'name' => 'Verde',
                'slug' => 'green',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 14,
                'name' => 'Verde e Preto',
                'slug' => 'green-black',
                'group' => 'kids',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 15,
                'name' => 'Azul',
                'slug' => 'blue',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 16,
                'name' => 'Roxa',
                'slug' => 'purple',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 17,
                'name' => 'Marrom',
                'slug' => 'brown',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 18,
                'name' => 'Preta',
                'slug' => 'black',
                'group' => 'adult',
                'classes_per_stripe' => null,
                'created_at' => $now,
                'updated_at' => $now
            ]

        ]);
    }
}
