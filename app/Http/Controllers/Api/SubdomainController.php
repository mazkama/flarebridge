<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Domain;
use App\Models\Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubdomainController extends Controller
{
    protected $cloudflare;

    public function __construct(\App\Services\CloudflareService $cloudflare)
    {
        $this->cloudflare = $cloudflare;
    }

    /**
     * Return list of all subdomains with port and domain.
     */
    public function index()
    {
        $services = Service::with('domain')->get();

        $data = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'subdomain' => $service->subdomain,
                'full_domain' => $service->full_domain,
                'port' => $service->port,
                'status' => $service->status,
                'domain' => $service->domain->domain,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Subdomains retrieved successfully',
            'data' => $data
        ]);
    }

    /**
     * Create new subdomain service with random port.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'domain_id' => 'required|exists:domains,id',
            'subdomain' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Service::where('domain_id', $request->domain_id)
                        ->where('subdomain', $value)
                        ->exists();
                    if ($exists) {
                        $fail('The subdomain has already been taken for this domain.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $domain = Domain::findOrFail($request->domain_id);
            $port = $this->generatePort();

            $service = Service::create([
                'domain_id' => $domain->id,
                'subdomain' => $request->subdomain,
                'full_domain' => $request->subdomain . '.' . $domain->domain,
                'port' => $port,
                'status' => 'active',
            ]);

            // === Cloudflare Sync ===
            // 1. Upsert DNS
            $this->cloudflare->upsertDnsRecord(
                $service->subdomain, 
                $domain->domain, 
                $domain->zone_id, 
                $domain->tunnel_id
            );
            
            // 2. Update Tunnel Ingress
            $this->cloudflare->updateTunnelIngress(
                Service::where('domain_id', $domain->id)->get(), 
                $domain->account_id, 
                $domain->tunnel_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Subdomain created and synced with Cloudflare successfully',
                'data' => [
                    'url' => $service->full_domain,
                    'port' => $service->port,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error("Store Subdomain Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create subdomain or sync with Cloudflare',
                'error' => $e->getMessage(),
                'hint' => 'Check your Cloudflare Token and Permissions in .env'
            ], 500);
        }
    }

    /**
     * Delete subdomain mapping.
     */
    public function destroy($id)
    {
        $service = Service::with('domain')->find($id);

        if (!$service) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found'
            ], 404);
        }

        try {
            $domain = $service->domain;
            $domainName = $domain->domain;
            $subdomain = $service->subdomain;
            $zoneId = $domain->zone_id;
            $accountId = $domain->account_id;
            $tunnelId = $domain->tunnel_id;

            // Delete From DB
            $service->delete();

            // === Cloudflare Sync ===
            // 1. Delete DNS
            $this->cloudflare->deleteDnsRecord($subdomain, $domainName, $zoneId);

            // 2. Update Tunnel Ingress
            $this->cloudflare->updateTunnelIngress(
                Service::where('domain_id', $domain->id)->get(), 
                $accountId, 
                $tunnelId
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Service deleted and Cloudflare synced successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("Delete Subdomain Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete service or sync with Cloudflare',
                'error' => $e->getMessage(),
                'hint' => 'Check your Cloudflare Token and Permissions in .env'
            ], 500);
        }
    }

    /**
     * Generate random port between 3000 and 9000 that is unique.
     */
    private function generatePort()
    {
        do {
            $port = rand(3000, 9000);
        } while (Service::where('port', $port)->exists());

        return $port;
    }
}
