<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('site_reviews', 'student_id')) {
                $table->foreignId('student_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('students')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('site_reviews', 'author_photo_path')) {
                $table->string('author_photo_path')->nullable()->after('author_name');
            }

            if (! Schema::hasColumn('site_reviews', 'status')) {
                $table->string('status', 20)->default('approved')->after('comment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('site_reviews', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }

            if (Schema::hasColumn('site_reviews', 'author_photo_path')) {
                $table->dropColumn('author_photo_path');
            }

            if (Schema::hasColumn('site_reviews', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
