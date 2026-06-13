<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_reviews', function (Blueprint $table) {
            $table->string('author_photo_path')->nullable()->after('author_name');
        });
    }

    public function down(): void
    {
        Schema::table('site_reviews', function (Blueprint $table) {
            $table->dropColumn('author_photo_path');
        });
    }
};
