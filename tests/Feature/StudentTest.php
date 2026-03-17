<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    const API_URL = '/api/students';

    public function test_can_create_student()
    {
        $response = $this->postJson(self::API_URL, [
            'name' => 'Bryan',
            'cpf' => '123.458.984-00',
            'birth_date' => '1995-08-06',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('students', [
            'name' => 'Bryan',
            'cpf' => '123.458.984-00',
        ]);
    }

    public function test_cannot_create_student_with_duplicate_cpf()
    {
        $this->postJson(self::API_URL, [
            'name' => 'Primeiro',
            'cpf' => '123.456.789-00',
            'birth_date' => '2000-01-01',
        ]);

        $response = $this->postJson(self::API_URL, [
            'name' => 'Outro',
            'cpf' => '123.456.789-00',
            'birth_date' => '2000-01-01',
        ]);

        $response->assertStatus(422);
    }
}
