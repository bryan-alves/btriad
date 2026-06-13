<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_sites', function (Blueprint $table) {
            $table->json('carousel_images')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_sites', function (Blueprint $table) {
            $table->dropColumn('carousel_images');
        });
    }
};
