<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentSeeder extends Seeder
{
    /**
     * Importa os 38 alunos do export MySQL com ids 1–38 na mesma ordem.
     * Esvazia `attendance_list_students`, `student_graduations` e `students` e recria os alunos.
     * Rode depois de {@see BeltSeeder}.
     */
    private static function defaultAddress(): array
    {
        return [
            'cep' => '',
            'city' => '',
            'number' => '',
            'street' => '',
            'complement' => '',
            'neighborhood' => '',
        ];
    }

    private static function defaultEmergencyContacts(): array
    {
        return [
            ['name' => '', 'phone' => '', 'relationship' => ''],
            ['name' => '', 'phone' => '', 'relationship' => ''],
        ];
    }

    /**
     * Cada item inclui id sequencial 1..38 (igual ao dump MySQL).
     *
     * @return list<array<string, mixed>>
     */
    private static function rows(): array
    {
        return [
            ['id' => 1, 'belt_id' => 1, 'name' => 'Christian Luiz Pinto Barrada', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 18:37:07', 'updated_at' => '2026-05-09 18:09:43'],
            ['id' => 2, 'belt_id' => 8, 'name' => 'Davi', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 18:49:23', 'updated_at' => '2026-05-09 17:29:21'],
            ['id' => 3, 'belt_id' => 1, 'name' => 'Felipe filho', 'birth_date' => null, 'first_class_at' => '2026-05-09', 'created_at' => '2026-05-08 18:54:00', 'updated_at' => '2026-05-09 17:58:23'],
            ['id' => 4, 'belt_id' => 3, 'name' => 'Heitor Fagundes Menezes', 'birth_date' => '2015-12-28', 'first_class_at' => '2026-05-09', 'created_at' => '2026-05-08 18:47:20', 'updated_at' => '2026-05-09 17:58:23'],
            ['id' => 5, 'belt_id' => 1, 'name' => 'Itallo Miguel da Silva Mendes Bonfim', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 18:54:40', 'updated_at' => '2026-05-08 18:54:40'],
            ['id' => 6, 'belt_id' => 3, 'name' => 'Pedro Mendes Bonfim Monteiro', 'birth_date' => '2016-10-12', 'first_class_at' => null, 'created_at' => '2026-05-08 18:48:46', 'updated_at' => '2026-05-08 18:48:46'],
            ['id' => 7, 'belt_id' => 6, 'name' => 'Leandra Mendes Bonfim Brito', 'birth_date' => '2013-06-28', 'first_class_at' => null, 'created_at' => '2026-05-08 18:46:04', 'updated_at' => '2026-05-08 18:46:04'],
            ['id' => 8, 'belt_id' => 1, 'name' => 'Alexandre Afonso Pacheco Filho', 'birth_date' => '2013-07-19', 'first_class_at' => null, 'created_at' => '2026-05-08 19:03:17', 'updated_at' => '2026-05-08 19:03:17'],
            ['id' => 9, 'belt_id' => 1, 'name' => 'Heitor Custódio Pedro', 'birth_date' => '2017-06-01', 'first_class_at' => null, 'created_at' => '2026-05-08 19:06:10', 'updated_at' => '2026-05-08 19:06:10'],
            ['id' => 10, 'belt_id' => 1, 'name' => 'Rebecca Marques Nuza', 'birth_date' => '2010-12-05', 'first_class_at' => null, 'created_at' => '2026-05-08 19:10:49', 'updated_at' => '2026-05-08 19:10:49'],
            ['id' => 11, 'belt_id' => 1, 'name' => 'Everton Alves Rodrigues', 'birth_date' => '2011-07-20', 'first_class_at' => null, 'created_at' => '2026-05-08 19:12:48', 'updated_at' => '2026-05-08 19:12:48'],
            ['id' => 12, 'belt_id' => 1, 'name' => 'Benjamim Araujo Sinhorão', 'birth_date' => '2015-05-24', 'first_class_at' => null, 'created_at' => '2026-05-08 19:17:40', 'updated_at' => '2026-05-08 19:17:40'],
            ['id' => 13, 'belt_id' => 1, 'name' => 'Leonardo Alejandro de Andrade', 'birth_date' => '2011-10-01', 'first_class_at' => null, 'created_at' => '2026-05-08 19:21:03', 'updated_at' => '2026-05-08 19:21:03'],
            ['id' => 14, 'belt_id' => 1, 'name' => 'Arthur Bispo dos Santos', 'birth_date' => '2012-02-17', 'first_class_at' => null, 'created_at' => '2026-05-08 19:22:16', 'updated_at' => '2026-05-08 19:22:16'],
            ['id' => 15, 'belt_id' => 1, 'name' => 'Emily Azevedo de Jesus', 'birth_date' => '2011-04-04', 'first_class_at' => null, 'created_at' => '2026-05-08 19:23:01', 'updated_at' => '2026-05-08 19:23:01'],
            ['id' => 16, 'belt_id' => 1, 'name' => 'Leona dos Santos Batista', 'birth_date' => '2017-07-20', 'first_class_at' => null, 'created_at' => '2026-05-08 19:28:34', 'updated_at' => '2026-05-08 19:28:34'],
            ['id' => 17, 'belt_id' => 1, 'name' => 'Luiz Gabriel Marinho dos Santos', 'birth_date' => '2013-06-18', 'first_class_at' => null, 'created_at' => '2026-05-08 19:29:27', 'updated_at' => '2026-05-08 19:29:27'],
            ['id' => 18, 'belt_id' => 1, 'name' => 'Luiz Phelipe Carvalho de Santana', 'birth_date' => '2010-01-02', 'first_class_at' => null, 'created_at' => '2026-05-08 19:32:10', 'updated_at' => '2026-05-08 19:32:10'],
            ['id' => 19, 'belt_id' => 1, 'name' => 'Victor Jesus Alves', 'birth_date' => '2011-05-11', 'first_class_at' => null, 'created_at' => '2026-05-08 19:33:05', 'updated_at' => '2026-05-08 19:33:05'],
            ['id' => 20, 'belt_id' => 1, 'name' => 'Sophya Colares Pereira', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:37:18', 'updated_at' => '2026-05-08 19:37:18'],
            ['id' => 21, 'belt_id' => 1, 'name' => 'Ana Carolina Sales alves', 'birth_date' => '1995-06-05', 'first_class_at' => '2026-05-09', 'created_at' => '2026-05-08 19:45:38', 'updated_at' => '2026-05-09 17:32:43'],
            ['id' => 22, 'belt_id' => 1, 'name' => 'Thiago', 'birth_date' => null, 'first_class_at' => '2026-05-09', 'created_at' => '2026-05-08 19:45:52', 'updated_at' => '2026-05-09 17:32:43'],
            ['id' => 23, 'belt_id' => 1, 'name' => 'Juan', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:42:09', 'updated_at' => '2026-05-08 19:42:09'],
            ['id' => 24, 'belt_id' => 1, 'name' => 'Heitor', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:42:12', 'updated_at' => '2026-05-08 19:42:12'],
            ['id' => 25, 'belt_id' => 1, 'name' => 'Potira', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:48:34', 'updated_at' => '2026-05-08 19:48:34'],
            ['id' => 26, 'belt_id' => 1, 'name' => 'Henrique Nogueira de Santana', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:48:48', 'updated_at' => '2026-05-08 19:48:48'],
            ['id' => 27, 'belt_id' => 1, 'name' => 'David Vinicius da Costa Ferreira', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:21:43', 'updated_at' => '2026-05-09 02:21:43'],
            ['id' => 28, 'belt_id' => 1, 'name' => 'Lucas Luiz Gomes', 'birth_date' => '2011-06-25', 'first_class_at' => null, 'created_at' => '2026-05-09 02:12:48', 'updated_at' => '2026-05-09 02:12:48'],
            ['id' => 29, 'belt_id' => 1, 'name' => 'Victória Ferreira da Silva', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:23:40', 'updated_at' => '2026-05-09 02:23:40'],
            ['id' => 30, 'belt_id' => 1, 'name' => 'Micaela Santos do Nascimento', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:24:09', 'updated_at' => '2026-05-09 02:24:09'],
            ['id' => 31, 'belt_id' => 1, 'name' => 'Luan Abrantes Santana', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:26:07', 'updated_at' => '2026-05-09 02:26:07'],
            ['id' => 32, 'belt_id' => 1, 'name' => 'Julio 1', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:29:09', 'updated_at' => '2026-05-09 13:53:51'],
            ['id' => 33, 'belt_id' => 1, 'name' => 'Sara Livia Costa dos Santos', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:32:20', 'updated_at' => '2026-05-09 02:32:20'],
            ['id' => 34, 'belt_id' => 1, 'name' => 'Julio 2', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:29:31', 'updated_at' => '2026-05-09 13:53:55'],
            ['id' => 35, 'belt_id' => 1, 'name' => 'Heitor Caetano da Silva', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:30:57', 'updated_at' => '2026-05-09 02:30:57'],
            ['id' => 36, 'belt_id' => 1, 'name' => 'Pedro', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:32:08', 'updated_at' => '2026-05-09 02:32:08'],
            ['id' => 37, 'belt_id' => 1, 'name' => 'Caio de Oliveira Miranda Z', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:36:22', 'updated_at' => '2026-05-09 02:36:22'],
            ['id' => 38, 'belt_id' => 1, 'name' => 'Gabriel Borges de Oliveira', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:36:57', 'updated_at' => '2026-05-09 02:36:57'],
        ];
    }

    public function run(): void
    {
        $tenant = Tenant::where('slug', 'btriad')->firstOrFail();

        Schema::withoutForeignKeyConstraints(function () {
            DB::table('attendance_list_students')->truncate();
            DB::table('student_graduations')->truncate();
            DB::table('students')->truncate();
        });

        $address = json_encode(self::defaultAddress());
        $emergency = json_encode(self::defaultEmergencyContacts());

        $payload = [];
        $graduationPayload = [];
        foreach (self::rows() as $row) {
            $payload[] = [
                'id' => $row['id'],
                'tenant_id' => $tenant->id,
                'user_id' => null,
                'degree' => null,
                'name' => $row['name'],
                'cpf' => null,
                'class_type' => 'kids',
                'photo' => null,
                'birth_date' => $row['birth_date'],
                'sex' => null,
                'address' => $address,
                'emergency_contacts' => $emergency,
                'other_sports' => null,
                'health_issues' => null,
                'medical_certificate' => null,
                'registration_form_file' => null,
                'active' => true,
                'first_class_at' => $row['first_class_at'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];

            if ($row['belt_id']) {
                $graduationPayload[] = [
                    'tenant_id' => $tenant->id,
                    'student_id' => $row['id'],
                    'belt_id' => $row['belt_id'],
                    'degree' => 0,
                    'graduated_at' => $row['first_class_at'] ?? substr((string) $row['created_at'], 0, 10),
                    'photo' => null,
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ];
            }
        }

        foreach (array_chunk($payload, 50) as $chunk) {
            Student::query()->insert($chunk);
        }

        foreach (array_chunk($graduationPayload, 50) as $chunk) {
            DB::table('student_graduations')->insert($chunk);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE students AUTO_INCREMENT = 39');
        }
    }
}
