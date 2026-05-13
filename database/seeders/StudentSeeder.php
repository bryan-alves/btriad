<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Importa os alunos do export MySQL (38 registros).
     * Apaga todos os registros de `students` antes de inserir (CASCADE remove vínculos em listas/frequências).
     * Rode depois de {@see BeltSeeder}.
     */
    /**
     * Endereço e contatos vazios no mesmo formato do export MySQL.
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
     * @return list<array<string, mixed>>
     */
    private static function rows(): array
    {
        return [
            ['belt_id' => 1, 'name' => 'Christian Luiz Pinto Barrada', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 18:37:07', 'updated_at' => '2026-05-09 18:09:43'],
            ['belt_id' => 8, 'name' => 'Davi', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 18:49:23', 'updated_at' => '2026-05-09 17:29:21'],
            ['belt_id' => 1, 'name' => 'Felipe filho', 'birth_date' => null, 'first_class_at' => '2026-05-09', 'created_at' => '2026-05-08 18:54:00', 'updated_at' => '2026-05-09 17:58:23'],
            ['belt_id' => 3, 'name' => 'Heitor Fagundes Menezes', 'birth_date' => '2015-12-28', 'first_class_at' => '2026-05-09', 'created_at' => '2026-05-08 18:47:20', 'updated_at' => '2026-05-09 17:58:23'],
            ['belt_id' => 1, 'name' => 'Itallo Miguel da Silva Mendes Bonfim', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 18:54:40', 'updated_at' => '2026-05-08 18:54:40'],
            ['belt_id' => 3, 'name' => 'Pedro Mendes Bonfim Monteiro', 'birth_date' => '2016-10-12', 'first_class_at' => null, 'created_at' => '2026-05-08 18:48:46', 'updated_at' => '2026-05-08 18:48:46'],
            ['belt_id' => 6, 'name' => 'Leandra Mendes Bonfim Brito', 'birth_date' => '2013-06-28', 'first_class_at' => null, 'created_at' => '2026-05-08 18:46:04', 'updated_at' => '2026-05-08 18:46:04'],
            ['belt_id' => 1, 'name' => 'Alexandre Afonso Pacheco Filho', 'birth_date' => '2013-07-19', 'first_class_at' => null, 'created_at' => '2026-05-08 19:03:17', 'updated_at' => '2026-05-08 19:03:17'],
            ['belt_id' => 1, 'name' => 'Heitor Custódio Pedro', 'birth_date' => '2017-06-01', 'first_class_at' => null, 'created_at' => '2026-05-08 19:06:10', 'updated_at' => '2026-05-08 19:06:10'],
            ['belt_id' => 1, 'name' => 'Rebecca Marques Nuza', 'birth_date' => '2010-12-05', 'first_class_at' => null, 'created_at' => '2026-05-08 19:10:49', 'updated_at' => '2026-05-08 19:10:49'],
            ['belt_id' => 1, 'name' => 'Everton Alves Rodrigues', 'birth_date' => '2011-07-20', 'first_class_at' => null, 'created_at' => '2026-05-08 19:12:48', 'updated_at' => '2026-05-08 19:12:48'],
            ['belt_id' => 1, 'name' => 'Benjamim Araujo Sinhorão', 'birth_date' => '2015-05-24', 'first_class_at' => null, 'created_at' => '2026-05-08 19:17:40', 'updated_at' => '2026-05-08 19:17:40'],
            ['belt_id' => 1, 'name' => 'Leonardo Alejandro de Andrade', 'birth_date' => '2011-10-01', 'first_class_at' => null, 'created_at' => '2026-05-08 19:21:03', 'updated_at' => '2026-05-08 19:21:03'],
            ['belt_id' => 1, 'name' => 'Arthur Bispo dos Santos', 'birth_date' => '2012-02-17', 'first_class_at' => null, 'created_at' => '2026-05-08 19:22:16', 'updated_at' => '2026-05-08 19:22:16'],
            ['belt_id' => 1, 'name' => 'Emily Azevedo de Jesus', 'birth_date' => '2011-04-04', 'first_class_at' => null, 'created_at' => '2026-05-08 19:23:01', 'updated_at' => '2026-05-08 19:23:01'],
            ['belt_id' => 1, 'name' => 'Leona dos Santos Batista', 'birth_date' => '2017-07-20', 'first_class_at' => null, 'created_at' => '2026-05-08 19:28:34', 'updated_at' => '2026-05-08 19:28:34'],
            ['belt_id' => 1, 'name' => 'Luiz Gabriel Marinho dos Santos', 'birth_date' => '2013-06-18', 'first_class_at' => null, 'created_at' => '2026-05-08 19:29:27', 'updated_at' => '2026-05-08 19:29:27'],
            ['belt_id' => 1, 'name' => 'Luiz Phelipe Carvalho de Santana', 'birth_date' => '2010-01-02', 'first_class_at' => null, 'created_at' => '2026-05-08 19:32:10', 'updated_at' => '2026-05-08 19:32:10'],
            ['belt_id' => 1, 'name' => 'Victor Jesus Alves', 'birth_date' => '2011-05-11', 'first_class_at' => null, 'created_at' => '2026-05-08 19:33:05', 'updated_at' => '2026-05-08 19:33:05'],
            ['belt_id' => 1, 'name' => 'Sophya Colares Pereira', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:37:18', 'updated_at' => '2026-05-08 19:37:18'],
            ['belt_id' => 1, 'name' => 'Ana Carolina Sales alves', 'birth_date' => '1995-06-05', 'first_class_at' => '2026-05-09', 'created_at' => '2026-05-08 19:45:38', 'updated_at' => '2026-05-09 17:32:43'],
            ['belt_id' => 1, 'name' => 'Thiago', 'birth_date' => null, 'first_class_at' => '2026-05-09', 'created_at' => '2026-05-08 19:45:52', 'updated_at' => '2026-05-09 17:32:43'],
            ['belt_id' => 1, 'name' => 'Juan', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:42:09', 'updated_at' => '2026-05-08 19:42:09'],
            ['belt_id' => 1, 'name' => 'Heitor', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:42:12', 'updated_at' => '2026-05-08 19:42:12'],
            ['belt_id' => 1, 'name' => 'Potira', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:48:34', 'updated_at' => '2026-05-08 19:48:34'],
            ['belt_id' => 1, 'name' => 'Henrique Nogueira de Santana', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-08 19:48:48', 'updated_at' => '2026-05-08 19:48:48'],
            ['belt_id' => 1, 'name' => 'David Vinicius da Costa Ferreira', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:21:43', 'updated_at' => '2026-05-09 02:21:43'],
            ['belt_id' => 1, 'name' => 'Lucas Luiz Gomes', 'birth_date' => '2011-06-25', 'first_class_at' => null, 'created_at' => '2026-05-09 02:12:48', 'updated_at' => '2026-05-09 02:12:48'],
            ['belt_id' => 1, 'name' => 'Victória Ferreira da Silva', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:23:40', 'updated_at' => '2026-05-09 02:23:40'],
            ['belt_id' => 1, 'name' => 'Micaela Santos do Nascimento', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:24:09', 'updated_at' => '2026-05-09 02:24:09'],
            ['belt_id' => 1, 'name' => 'Luan Abrantes Santana', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:26:07', 'updated_at' => '2026-05-09 02:26:07'],
            ['belt_id' => 1, 'name' => 'Julio 1', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:29:09', 'updated_at' => '2026-05-09 13:53:51'],
            ['belt_id' => 1, 'name' => 'Sara Livia Costa dos Santos', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:32:20', 'updated_at' => '2026-05-09 02:32:20'],
            ['belt_id' => 1, 'name' => 'Julio 2', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:29:31', 'updated_at' => '2026-05-09 13:53:55'],
            ['belt_id' => 1, 'name' => 'Heitor Caetano da Silva', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:30:57', 'updated_at' => '2026-05-09 02:30:57'],
            ['belt_id' => 1, 'name' => 'Pedro', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:32:08', 'updated_at' => '2026-05-09 02:32:08'],
            ['belt_id' => 1, 'name' => 'Caio de Oliveira Miranda Z', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:36:22', 'updated_at' => '2026-05-09 02:36:22'],
            ['belt_id' => 1, 'name' => 'Gabriel Borges de Oliveira', 'birth_date' => null, 'first_class_at' => null, 'created_at' => '2026-05-09 02:36:57', 'updated_at' => '2026-05-09 02:36:57'],
        ];
    }

    public function run(): void
    {
        Student::query()->delete();

        $address = self::defaultAddress();
        $emergency = self::defaultEmergencyContacts();

        $payload = [];
        foreach (self::rows() as $row) {
            $payload[] = [
                'user_id' => null,
                'belt_id' => $row['belt_id'],
                'degree' => null,
                'name' => $row['name'],
                'cpf' => null,
                'class_type' => 'kids',
                'photo' => null,
                'birth_date' => $row['birth_date'],
                'sex' => null,
                'address' => json_encode($address),
                'emergency_contacts' => json_encode($emergency),
                'other_sports' => null,
                'health_issues' => null,
                'medical_certificate' => null,
                'registration_form_file' => null,
                'active' => true,
                'first_class_at' => $row['first_class_at'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];
        }

        foreach (array_chunk($payload, 50) as $chunk) {
            Student::query()->insert($chunk);
        }
    }
}
