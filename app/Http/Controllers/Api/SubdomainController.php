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
        $isBatch = $request->has('subdomains') && is_array($request->subdomains);
        $items = $isBatch ? $request->subdomains : [$request->all()];
        $results = [];
        $errors = [];
        $domainsToSync = [];

        foreach ($items as $index => $item) {
            $validator = Validator::make($item, [
                'domain_id' => 'required|exists:domains,id',
                'subdomain' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($item) {
                        $exists = Service::where('domain_id', $item['domain_id'] ?? null)
                            ->where('subdomain', $value)
                            ->exists();
                        if ($exists) {
                            $fail('The subdomain has already been taken for this domain.');
                        }
                    },
                ],
                'port' => [
                    'nullable',
                    'numeric',
                    'min:1',
                    'max:65535',
                    function ($attribute, $value, $fail) use ($item) {
                        if ($value && Service::where('port', $value)->exists()) {
                            $fail('The port ' . $value . ' is already in use by another service.');
                        }
                    }
                ]
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'index' => $index,
                    'subdomain' => $item['subdomain'] ?? 'N/A',
                    'errors' => $validator->errors()
                ];
                continue;
            }

            try {
                $domain = Domain::findOrFail($item['domain_id']);
                $port = ($item['port'] ?? null) ?: $this->generatePort();

                $service = Service::create([
                    'domain_id' => $domain->id,
                    'subdomain' => $item['subdomain'],
                    'full_domain' => $item['subdomain'] . '.' . $domain->domain,
                    'port' => $port,
                    'status' => 'active',
                ]);

                // 1. Sync DNS Record Immediately
                $this->cloudflare->upsertDnsRecord(
                    $service->subdomain, 
                    $domain->domain, 
                    $domain->zone_id, 
                    $domain->tunnel_id
                );

                $results[] = [
                    'id' => $service->id,
                    'url' => $service->full_domain,
                    'port' => $service->port,
                ];

                // Track which domains need Ingress update
                if (!isset($domainsToSync[$domain->id])) {
                    $domainsToSync[$domain->id] = $domain;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'subdomain' => $item['subdomain'] ?? 'N/A',
                    'error' => $e->getMessage()
                ];
            }
        }

        // 2. Optimized Ingress Sync (Once per domain)
        foreach ($domainsToSync as $domain) {
            try {
                $this->cloudflare->updateTunnelIngress(
                    Service::where('domain_id', $domain->id)->get(), 
                    $domain->account_id, 
                    $domain->tunnel_id
                );
            } catch (\Exception $e) {
                Log::error("Bulk Ingress Sync Error for Domain {$domain->id}: " . $e->getMessage());
            }
        }

        if (count($errors) > 0 && count($results) === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create subdomains',
                'errors' => $errors
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => count($errors) > 0 ? 'Subdomains created with some errors' : 'Subdomains created successfully',
            'data' => $isBatch ? $results : $results[0],
            'errors' => count($errors) > 0 ? $errors : null
        ], 201);
    }

    /**
     * Update subdomain mapping.
     */
    public function update(Request $request, $id)
    {
        $service = Service::with('domain')->find($id);

        if (!$service) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'subdomain' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request, $service) {
                    $exists = Service::where('domain_id', $service->domain_id)
                        ->where('subdomain', $value)
                        ->where('id', '!=', $service->id)
                        ->exists();
                    if ($exists) {
                        $fail('The subdomain has already been taken for this domain.');
                    }
                },
            ],
            'port' => [
                'nullable',
                'numeric',
                'min:1',
                'max:65535',
                function ($attribute, $value, $fail) use ($service) {
                    if ($value && Service::where('port', $value)->where('id', '!=', $service->id)->exists()) {
                        $fail('The port ' . $value . ' is already in use by another service.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $oldSubdomain = $service->subdomain;
            $domain = $service->domain;

            // Update DB
            $service->update([
                'subdomain' => $request->subdomain,
                'full_domain' => $request->subdomain . '.' . $domain->domain,
                'port' => $request->input('port') ?: $service->port,
            ]);

            // === Cloudflare Sync ===
            // 1. Delete Old DNS
            $this->cloudflare->deleteDnsRecord($oldSubdomain, $domain->domain, $domain->zone_id);

            // 2. Upsert New DNS
            $this->cloudflare->upsertDnsRecord(
                $service->subdomain, 
                $domain->domain, 
                $domain->zone_id, 
                $domain->tunnel_id
            );
            
            // 3. Update Tunnel Ingress
            $this->cloudflare->updateTunnelIngress(
                Service::where('domain_id', $domain->id)->get(), 
                $domain->account_id, 
                $domain->tunnel_id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Subdomain updated and synced with Cloudflare successfully',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            Log::error("Update Subdomain Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update subdomain or sync with Cloudflare',
                'error' => $e->getMessage()
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
