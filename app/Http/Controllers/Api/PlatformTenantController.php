<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\TenantSite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlatformTenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::query()
            ->where('is_platform', false)
            ->with(['domains', 'site'])
            ->orderBy('name')
            ->get();

        return response()->json($tenants, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,slug'],
            'plan' => ['required', Rule::in([
                Tenant::PLAN_APP,
                Tenant::PLAN_DIGITAL,
            ])],
            'domains' => ['required', 'array', 'min:1'],
            'domains.*' => ['required', 'string', 'max:255', 'distinct'],
            'primary_domain' => ['nullable', 'string', 'max:255'],
        ]);

        $normalizedDomains = $this->normalizeDomains($data['domains']);
        $primaryDomain = $this->resolvePrimaryDomain(
            $normalizedDomains,
            $data['primary_domain'] ?? null,
        );

        $tenant = DB::transaction(function () use ($data, $normalizedDomains, $primaryDomain) {
            $tenant = Tenant::query()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'plan' => $data['plan'],
                'is_platform' => false,
                'primary_domain' => $primaryDomain,
            ]);

            foreach ($normalizedDomains as $domain) {
                Domain::query()->create([
                    'tenant_id' => $tenant->id,
                    'domain' => $domain,
                ]);
            }

            TenantSite::query()->create([
                'tenant_id' => $tenant->id,
                'academy_name' => $data['name'],
                'active' => $tenant->hasPublicSite(),
            ]);

            return $tenant;
        });

        return response()->json($tenant->fresh()->load(['domains', 'site']), 201);
    }

    public function show(Tenant $tenant)
    {
        $this->assertClientTenant($tenant);

        return response()->json($tenant->load(['domains', 'site']), 200);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $this->assertClientTenant($tenant);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('tenants', 'slug')->ignore($tenant->id)],
            'plan' => ['required', Rule::in([
                Tenant::PLAN_APP,
                Tenant::PLAN_DIGITAL,
            ])],
            'domains' => ['required', 'array', 'min:1'],
            'domains.*' => ['required', 'string', 'max:255', 'distinct'],
            'primary_domain' => ['nullable', 'string', 'max:255'],
        ]);

        $normalizedDomains = $this->normalizeDomains($data['domains']);
        $primaryDomain = $this->resolvePrimaryDomain(
            $normalizedDomains,
            $data['primary_domain'] ?? null,
            $tenant->primary_domain,
        );

        DB::transaction(function () use ($tenant, $data, $normalizedDomains, $primaryDomain) {
            $tenant->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'plan' => $data['plan'],
                'primary_domain' => $primaryDomain,
            ]);

            Domain::query()
                ->where('tenant_id', $tenant->id)
                ->whereNotIn('domain', $normalizedDomains)
                ->delete();

            foreach ($normalizedDomains as $domain) {
                Domain::query()->updateOrCreate(
                    ['domain' => $domain],
                    ['tenant_id' => $tenant->id],
                );
            }

            $tenant->site?->update([
                'academy_name' => $data['name'],
                'active' => $tenant->fresh()->hasPublicSite(),
            ]);
        });

        return response()->json($tenant->fresh()->load(['domains', 'site']), 200);
    }

    public function storeAdmin(Request $request, Tenant $tenant)
    {
        $this->assertClientTenant($tenant);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::withoutGlobalScope('tenant')->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'active' => true,
        ]);

        return response()->json($user, 201);
    }

    private function assertClientTenant(Tenant $tenant): void
    {
        if ($tenant->is_platform) {
            abort(404, 'Tenant não encontrado.');
        }
    }

    /**
     * @param  list<string>  $domains
     * @return list<string>
     */
    private function normalizeDomains(array $domains): array
    {
        return collect($domains)
            ->map(fn (string $domain) => strtolower(trim($domain)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $domains
     */
    private function resolvePrimaryDomain(array $domains, ?string $requested, ?string $current = null): string
    {
        if ($domains === []) {
            throw ValidationException::withMessages([
                'domains' => ['Informe ao menos um domínio.'],
            ]);
        }

        $requested = $requested !== null ? strtolower(trim($requested)) : null;

        if ($requested !== null && $requested !== '') {
            if (! in_array($requested, $domains, true)) {
                throw ValidationException::withMessages([
                    'primary_domain' => ['O domínio principal deve estar na lista de domínios.'],
                ]);
            }

            return $requested;
        }

        if ($current !== null && in_array($current, $domains, true)) {
            return $current;
        }

        if (count($domains) === 1) {
            return $domains[0];
        }

        throw ValidationException::withMessages([
            'primary_domain' => ['Selecione o domínio principal.'],
        ]);
    }
}
