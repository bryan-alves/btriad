<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('belt_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('photo')->nullable();
            $table->string('name');
            $table->string('cpf', 14)->unique()->nullable();
            $table->date('birth_date')->nullable();
            $table->string('sex')->nullable();

            $table->json('address')->nullable();

            $table->json('emergency_contacts')->nullable();

            $table->boolean('practices_other_sports')->default(false);
            $table->string('other_sports')->nullable();

            $table->text('health_issues')->nullable();
            $table->string('medical_certificate')->nullable();

            $table->string('registration_form_file')->nullable();

            $table->boolean('image_authorization')->default(false);
            $table->string('image_authorization_file')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
