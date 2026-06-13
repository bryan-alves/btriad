<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_sites', function (Blueprint $table) {
            $table->string('nav_logo_path')->nullable()->after('logo_path');
            $table->string('footer_logo_path')->nullable()->after('nav_logo_path');
            $table->string('hero_logo_path')->nullable()->after('footer_logo_path');
        });

        DB::table('tenant_sites')
            ->whereNotNull('logo_path')
            ->update([
                'nav_logo_path' => DB::raw('logo_path'),
                'footer_logo_path' => DB::raw('logo_path'),
                'hero_logo_path' => DB::raw('logo_path'),
            ]);
    }

    public function down(): void
    {
        Schema::table('tenant_sites', function (Blueprint $table) {
            $table->dropColumn(['nav_logo_path', 'footer_logo_path', 'hero_logo_path']);
        });
    }
};
