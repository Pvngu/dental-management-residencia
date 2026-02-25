<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalApiKey;
use App\Traits\CompanyTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class ExternalApiKeyController extends Controller
{
    use CompanyTraits;

    /**
     * Get ID from hash
     */
    protected function getIdFromHash($hash)
    {
        $id = Hashids::decode($hash);
        return $id ? $id[0] : null;
    }

    /**
     * Display a listing of API keys
     */
    public function index()
    {
        $keys = ExternalApiKey::with('company')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $keys->map(function ($key) {
                return [
                    'id' => $key->id,
                    'xid' => $key->xid,
                    'x_company_id' => $key->x_company_id,
                    'company' => $key->company ? [
                        'xid' => $key->company->xid,
                        'name' => $key->company->name,
                    ] : null,
                    'name' => $key->name,
                    'api_key' => substr($key->api_key, 0, 12) . '...' . substr($key->api_key, -4), // Mostrar parcialmente
                    'description' => $key->description,
                    'contact_email' => $key->contact_email,
                    'is_active' => $key->is_active,
                    'last_used_at' => $key->last_used_at,
                    'expires_at' => $key->expires_at,
                    'allowed_ips' => $key->allowed_ips,
                    'allowed_domains' => $key->allowed_domains,
                    'request_count' => $key->request_count,
                    'created_at' => $key->created_at,
                ];
            }),
        ]);
    }

    /**
     * Store a new API key
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|string', // xid hash
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'expires_at' => 'nullable|date|after:now',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $apiKey = ExternalApiKey::create([
            'company_id' => $this->getIdFromHash($request->company_id),
            'name' => $request->name,
            'api_key' => ExternalApiKey::generateKey(),
            'description' => $request->description,
            'contact_email' => $request->contact_email,
            'expires_at' => $request->expires_at,
            'allowed_ips' => $request->allowed_ips,
            'allowed_domains' => $request->allowed_domains,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'API key created successfully',
            'data' => [
                'id' => $apiKey->id,
                'xid' => $apiKey->xid,
                'x_company_id' => $apiKey->x_company_id,
                'name' => $apiKey->name,
                'api_key' => $apiKey->api_key, // Mostrar completo solo al crear
                'description' => $apiKey->description,
                'contact_email' => $apiKey->contact_email,
                'expires_at' => $apiKey->expires_at,
                'allowed_ips' => $apiKey->allowed_ips,
                'allowed_domains' => $apiKey->allowed_domains,
                'allowed_ips' => $apiKey->allowed_ips,
            ],
            'warning' => 'IMPORTANTE: Guarda este token ahora. No podrás verlo completo nuevamente.',
        ], 201);
    }

    /**
     * Update an API key
     */
    public function update(Request $request, $id)
    {
        $apiKey = ExternalApiKey::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_id' => 'sometimes|string',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'is_active' => 'sometimes|boolean',
            'expires_at' => 'nullable|date',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = $request->only([
            'name',
            'description',
            'contact_email',
            'is_active',
            'expires_at',
            'allowed_ips',
            'allowed_domains',
        ]);

        if ($request->has('company_id')) {
            $updateData['company_id'] = $this->getIdFromHash($request->company_id);
        }

        $apiKey->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'API key updated successfully',
            'data' => $apiKey,
        ]);
    }

    /**
     * Delete (soft delete) an API key
     */
    public function destroy($id)
    {
        $apiKey = ExternalApiKey::findOrFail($id);
        $apiKey->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'API key deleted successfully',
        ]);
    }

    /**
     * Regenerate an API key
     */
    public function regenerate($id)
    {
        $apiKey = ExternalApiKey::findOrFail($id);
        
        $newKey = ExternalApiKey::generateKey();
        $apiKey->update([
            'api_key' => $newKey,
            'request_count' => 0,
            'last_used_at' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'API key regenerated successfully',
            'data' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'api_key' => $newKey,
            ],
            'warning' => 'IMPORTANTE: Guarda este nuevo token ahora. El anterior dejará de funcionar.',
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        $apiKey = ExternalApiKey::findOrFail($id);
        $apiKey->update(['is_active' => !$apiKey->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'API key status updated',
            'data' => [
                'is_active' => $apiKey->is_active,
            ],
        ]);
    }

    /**
     * Get usage statistics
     */
    public function statistics()
    {
        $stats = [
            'total_keys' => ExternalApiKey::count(),
            'active_keys' => ExternalApiKey::where('is_active', true)->count(),
            'inactive_keys' => ExternalApiKey::where('is_active', false)->count(),
            'expired_keys' => ExternalApiKey::where('expires_at', '<', now())->count(),
            'total_requests' => ExternalApiKey::sum('request_count'),
            'most_used' => ExternalApiKey::orderBy('request_count', 'desc')
                ->take(5)
                ->get(['name', 'request_count', 'last_used_at']),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }
}
