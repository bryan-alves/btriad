<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addTenantColumn('users');
        $this->addTenantColumn('students');
        $this->addTenantColumn('classes');
        $this->addTenantColumn('attendance_lists');
        $this->addTenantColumn('attendance_list_students');
        $this->addTenantColumn('student_graduations');

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_username_unique');
            });
        } catch (\Throwable) {
            //
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['tenant_id', 'username']);
        });

        Schema::create('tenant_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('academy_name');
            $table->string('primary_color', 20)->default('#c41e3a');
            $table->string('logo_path')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('instagram')->nullable();
            $table->text('address')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $btriadTenantId = DB::table('tenants')->where('slug', 'btriad')->value('id');

        if ($btriadTenantId === null) {
            $btriadTenantId = DB::table('tenants')->insertGetId([
                'name' => 'Equipe B-Triad Jiu-Jitsu',
                'slug' => 'btriad',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['users', 'students', 'classes', 'attendance_lists', 'attendance_list_students', 'student_graduations'] as $table) {
            DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $btriadTenantId]);
        }

        DB::table('tenant_sites')->insertOrIgnore([
            [
                'tenant_id' => $btriadTenantId,
                'academy_name' => 'Equipe B-Triad Jiu-Jitsu',
                'primary_color' => '#c41e3a',
                'logo_path' => 'logo.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_sites');

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['tenant_id', 'username']);
            });
        } catch (\Throwable) {
            //
        }

        foreach (['student_graduations', 'attendance_list_students', 'attendance_lists', 'classes', 'students', 'users'] as $table) {
            if (Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('tenant_id');
                });
            }
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        } catch (\Throwable) {
            //
        }
    }

    private function addTenantColumn(string $table): void
    {
        if (Schema::hasColumn($table, 'tenant_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }
};
