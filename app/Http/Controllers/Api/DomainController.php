<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    protected $cloudflare;

    public function __construct(\App\Services\CloudflareService $cloudflare)
    {
        $this->cloudflare = $cloudflare;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $domains = \App\Models\Domain::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Domains retrieved successfully',
            'data' => $domains
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'domain' => 'required|string|unique:domains,domain',
            'zone_id' => 'required|string',
            'account_id' => 'required|string',
            'tunnel_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate Cloudflare Credentials
        try {
            $this->cloudflare->verifyCredentials(
                \App\Models\Setting::get('cloudflare_email'),
                \App\Models\Setting::get('cloudflare_api_token'),
                $request->zone_id,
                $request->account_id
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cloudflare validation failed: ' . $e->getMessage()
            ], 422);
        }

        $domain = \App\Models\Domain::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Domain created successfully',
            'data' => $domain
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $domain = \App\Models\Domain::find($id);

        if (!$domain) {
            return response()->json([
                'status' => 'error',
                'message' => 'Domain not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Domain retrieved successfully',
            'data' => $domain
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $domain = \App\Models\Domain::find($id);

        if (!$domain) {
            return response()->json([
                'status' => 'error',
                'message' => 'Domain not found'
            ], 404);
        }

        if ($request->has('zone_id') || $request->has('account_id')) {
            try {
                $this->cloudflare->verifyCredentials(
                    \App\Models\Setting::get('cloudflare_email'),
                    \App\Models\Setting::get('cloudflare_api_token'),
                    $request->zone_id ?? $domain->zone_id,
                    $request->account_id ?? $domain->account_id
                );
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cloudflare validation failed: ' . $e->getMessage()
                ], 422);
            }
        }

        $domain->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Domain updated successfully',
            'data' => $domain
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $domain = \App\Models\Domain::find($id);

        if (!$domain) {
            return response()->json([
                'status' => 'error',
                'message' => 'Domain not found'
            ], 404);
        }

        if ($domain->services()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete domain with active subdomains'
            ], 422);
        }

        $domain->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Domain deleted successfully'
        ]);
    }
}
