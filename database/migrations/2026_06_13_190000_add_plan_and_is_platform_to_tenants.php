<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('plan', ['essential', 'professional', 'premium'])
                ->default('professional')
                ->after('slug');
            $table->boolean('is_platform')->default(false)->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['plan', 'is_platform']);
        });
    }
};
