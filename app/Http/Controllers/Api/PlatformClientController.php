<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformClient;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlatformClientController extends Controller
{
    public function index()
    {
        $clients = PlatformClient::query()
            ->with(['clientTenant.site', 'clientTenant.domains'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($clients, 200);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $tenant = $this->resolveClientTenant($data['client_tenant_id']);

        $client = PlatformClient::query()->create([
            'name' => $tenant->name,
            'client_tenant_id' => $tenant->id,
            'sort_order' => $data['sort_order'],
            'active' => true,
        ]);

        return response()->json($client->fresh()->load(['clientTenant.site', 'clientTenant.domains']), 201);
    }

    public function update(Request $request, PlatformClient $client)
    {
        $data = $this->validated($request, $client);
        $tenant = $this->resolveClientTenant($data['client_tenant_id']);

        $client->update([
            'name' => $tenant->name,
            'client_tenant_id' => $tenant->id,
            'sort_order' => $data['sort_order'],
        ]);

        return response()->json($client->fresh()->load(['clientTenant.site', 'clientTenant.domains']), 200);
    }

    public function destroy(PlatformClient $client)
    {
        $client->delete();

        return response()->json(null, 204);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', Rule::exists('platform_clients', 'id')],
        ]);

        foreach ($data['order'] as $index => $id) {
            PlatformClient::query()->whereKey($id)->update(['sort_order' => $index]);
        }

        return response()->json(['message' => 'Ordem atualizada.'], 200);
    }

    private function validated(Request $request, ?PlatformClient $client = null): array
    {
        return $request->validate([
            'client_tenant_id' => [
                'required',
                'integer',
                Rule::exists('tenants', 'id')->where(fn ($query) => $query->where('is_platform', false)),
                Rule::unique('platform_clients', 'client_tenant_id')->ignore($client?->id),
            ],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function resolveClientTenant(int $tenantId): Tenant
    {
        return Tenant::query()
            ->whereKey($tenantId)
            ->where('is_platform', false)
            ->firstOrFail();
    }
}
