<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_sites', function (Blueprint $table) {
            $table->string('page_title')->nullable()->after('academy_name');
            $table->string('hero_title')->nullable()->after('page_title');
            $table->text('hero_subtitle')->nullable()->after('hero_title');
            $table->string('header_color', 20)->default('#1b1b18')->after('primary_color');
            $table->string('background_color', 20)->default('#3d3d3d')->after('header_color');
            $table->string('trial_button_color', 20)->default('#c41e3a')->after('background_color');
            $table->string('portal_button_color', 20)->default('#2563eb')->after('trial_button_color');
            $table->json('schedule')->nullable()->after('address');
        });

        Schema::create('site_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('author_name');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('comment');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_reviews');

        Schema::table('tenant_sites', function (Blueprint $table) {
            $table->dropColumn([
                'page_title',
                'hero_title',
                'hero_subtitle',
                'header_color',
                'background_color',
                'trial_button_color',
                'portal_button_color',
                'schedule',
            ]);
        });
    }
};
