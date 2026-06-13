<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('site_reviews', 'student_id')) {
            Schema::table('site_reviews', function (Blueprint $table) {
                $table->foreignId('student_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('site_reviews', 'author_photo_path')) {
            Schema::table('site_reviews', function (Blueprint $table) {
                $table->string('author_photo_path')->nullable()->after('author_name');
            });
        }

        if (! Schema::hasColumn('site_reviews', 'status')) {
            Schema::table('site_reviews', function (Blueprint $table) {
                $table->string('status', 20)->default('approved')->after('comment');
            });
        }

        if (Schema::hasColumn('site_reviews', 'status')) {
            DB::table('site_reviews')
                ->whereNull('status')
                ->update(['status' => 'approved']);
        }
    }

    public function down(): void
    {
        Schema::table('site_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('site_reviews', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }

            $columns = array_filter([
                Schema::hasColumn('site_reviews', 'author_photo_path') ? 'author_photo_path' : null,
                Schema::hasColumn('site_reviews', 'status') ? 'status' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
