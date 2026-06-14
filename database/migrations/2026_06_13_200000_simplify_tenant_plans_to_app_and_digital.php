<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY plan VARCHAR(20) NOT NULL DEFAULT 'digital'");

        DB::table('tenants')->where('plan', 'essential')->update(['plan' => 'app']);
        DB::table('tenants')->whereIn('plan', ['professional', 'premium'])->update(['plan' => 'digital']);

        DB::statement("ALTER TABLE tenants MODIFY plan ENUM('app', 'digital') NOT NULL DEFAULT 'digital'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY plan VARCHAR(20) NOT NULL DEFAULT 'professional'");

        DB::table('tenants')->where('plan', 'app')->update(['plan' => 'essential']);
        DB::table('tenants')->where('plan', 'digital')->update(['plan' => 'professional']);

        DB::statement("ALTER TABLE tenants MODIFY plan ENUM('essential', 'professional', 'premium') NOT NULL DEFAULT 'professional'");
    }
};
