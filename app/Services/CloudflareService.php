<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    protected $apiToken;
    protected $accountId;
    protected $zoneId;
    protected $tunnelId;
    protected $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = env('CLOUDFLARE_API_TOKEN');
        $this->accountId = env('CLOUDFLARE_ACCOUNT_ID');
        $this->zoneId = env('CLOUDFLARE_ZONE_ID');
        $this->tunnelId = env('CLOUDFLARE_TUNNEL_ID');
    }

    /**
     * Create or update a CNAME record for a subdomain.
     */
    public function upsertDnsRecord($subdomain, $domainName)
    {
        $fullDomain = "{$subdomain}.{$domainName}";
        $tunnelCname = "{$this->tunnelId}.cfargotunnel.com";

        // 1. Check if record exists
        $response = Http::withToken($this->apiToken)
            ->get("{$this->baseUrl}/zones/{$this->zoneId}/dns_records", [
                'name' => $fullDomain,
                'type' => 'CNAME'
            ]);

        if (!$response->successful()) {
            Log::error("Cloudflare API Error (Get DNS): " . $response->body());
            return false;
        }

        $records = $response->json()['result'];

        if (count($records) > 0) {
            // Update existing
            $recordId = $records[0]['id'];
            $updateResponse = Http::withToken($this->apiToken)
                ->put("{$this->baseUrl}/zones/{$this->zoneId}/dns_records/{$recordId}", [
                    'type' => 'CNAME',
                    'name' => $fullDomain,
                    'content' => $tunnelCname,
                    'ttl' => 1, // Auto
                    'proxied' => true
                ]);
            return $updateResponse->successful();
        } else {
            // Create new
            $createResponse = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/zones/{$this->zoneId}/dns_records", [
                    'type' => 'CNAME',
                    'name' => $fullDomain,
                    'content' => $tunnelCname,
                    'ttl' => 1,
                    'proxied' => true
                ]);
            return $createResponse->successful();
        }
    }

    /**
     * Delete a DNS record for a subdomain.
     */
    public function deleteDnsRecord($subdomain, $domainName)
    {
        $fullDomain = "{$subdomain}.{$domainName}";

        $response = Http::withToken($this->apiToken)
            ->get("{$this->baseUrl}/zones/{$this->zoneId}/dns_records", [
                'name' => $fullDomain,
                'type' => 'CNAME'
            ]);

        if ($response->successful() && count($response->json()['result']) > 0) {
            $recordId = $response->json()['result'][0]['id'];
            $deleteResponse = Http::withToken($this->apiToken)
                ->delete("{$this->baseUrl}/zones/{$this->zoneId}/dns_records/{$recordId}");
            return $deleteResponse->successful();
        }

        return true; // Already gone or not found
    }

    /**
     * Update Tunnel Ingress Configuration.
     * This replaces the entire ingress rule set.
     */
    public function updateTunnelIngress($services)
    {
        $ingress = [];

        foreach ($services as $service) {
            $ingress[] = [
                'hostname' => $service->full_domain,
                'service' => "http://localhost:{$service->port}"
            ];
        }

        // Final catch-all (404)
        $ingress[] = [
            'service' => 'http_status:404'
        ];

        $response = Http::withToken($this->apiToken)
            ->put("{$this->baseUrl}/accounts/{$this->accountId}/cfd_tunnel/{$this->tunnelId}/configurations", [
                'config' => [
                    'ingress' => $ingress
                ]
            ]);

        if (!$response->successful()) {
            Log::error("Cloudflare API Error (Tunnel Config): " . $response->body());
        }

        return $response->successful();
    }
}
