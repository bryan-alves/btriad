<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_sites', function (Blueprint $table) {
            $table->string('app_primary_color', 20)->default('#111827')->after('portal_button_color');
            $table->string('app_header_color', 20)->default('#1b1b18')->after('app_primary_color');
            $table->string('app_background_color', 20)->default('#f8fafc')->after('app_header_color');
            $table->string('app_login_background_color', 20)->default('#333333')->after('app_background_color');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_sites', function (Blueprint $table) {
            $table->dropColumn([
                'app_primary_color',
                'app_header_color',
                'app_background_color',
                'app_login_background_color',
            ]);
        });
    }
};
