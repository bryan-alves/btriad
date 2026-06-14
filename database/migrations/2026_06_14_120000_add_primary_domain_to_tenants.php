<?php

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('primary_domain')->nullable()->after('is_platform');
        });

        Tenant::query()->with('domains')->each(function (Tenant $tenant) {
            $domains = $tenant->domains->pluck('domain');

            if ($domains->isEmpty()) {
                return;
            }

            $preferred = $domains->first(
                fn (string $domain) => ! in_array($domain, ['localhost', '127.0.0.1'], true)
                    && ! str_ends_with($domain, '.test')
            );

            $tenant->update([
                'primary_domain' => $preferred ?? $domains->first(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('primary_domain');
        });
    }
};
