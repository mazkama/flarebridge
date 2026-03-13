<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    protected $apiToken;
    protected $email;
    protected $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = \App\Models\Setting::get('cloudflare_api_token', env('CLOUDFLARE_API_TOKEN'));
        $this->email = \App\Models\Setting::get('cloudflare_email', env('CLOUDFLARE_EMAIL'));
    }

    /**
     * Get the appropriate request headers based on auth type.
     */
    protected function getRequest()
    {
        if ($this->email) {
            return Http::withHeaders([
                'X-Auth-Email' => $this->email,
                'X-Auth-Key' => $this->apiToken,
            ]);
        }
        return Http::withToken($this->apiToken);
    }

    /**
     * Create or update a CNAME record for a subdomain.
     */
    public function upsertDnsRecord($subdomain, $domainName, $zoneId, $tunnelId)
    {
        $fullDomain = "{$subdomain}.{$domainName}";
        $tunnelCname = "{$tunnelId}.cfargotunnel.com";

        $response = $this->getRequest()
            ->get("{$this->baseUrl}/zones/{$zoneId}/dns_records", [
                'name' => $fullDomain,
                'type' => 'CNAME'
            ]);

        if (!$response->successful()) {
            throw new \Exception("Cloudflare API Error (Get DNS): " . $response->body());
        }

        $records = $response->json()['result'];

        if (count($records) > 0) {
            $recordId = $records[0]['id'];
            $updateResponse = $this->getRequest()
                ->put("{$this->baseUrl}/zones/{$zoneId}/dns_records/{$recordId}", [
                    'type' => 'CNAME',
                    'name' => $fullDomain,
                    'content' => $tunnelCname,
                    'ttl' => 1,
                    'proxied' => true
                ]);
            
            if (!$updateResponse->successful()) {
                throw new \Exception("Cloudflare API Error (Update DNS): " . $updateResponse->body());
            }
            return true;
        } else {
            $createResponse = $this->getRequest()
                ->post("{$this->baseUrl}/zones/{$zoneId}/dns_records", [
                    'type' => 'CNAME',
                    'name' => $fullDomain,
                    'content' => $tunnelCname,
                    'ttl' => 1,
                    'proxied' => true
                ]);
            
            if (!$createResponse->successful()) {
                throw new \Exception("Cloudflare API Error (Create DNS): " . $createResponse->body());
            }
            return true;
        }
    }

    /**
     * Delete a DNS record for a subdomain.
     */
    public function deleteDnsRecord($subdomain, $domainName, $zoneId)
    {
        $fullDomain = "{$subdomain}.{$domainName}";

        $response = $this->getRequest()
            ->get("{$this->baseUrl}/zones/{$zoneId}/dns_records", [
                'name' => $fullDomain,
                'type' => 'CNAME'
            ]);

        if ($response->successful() && count($response->json()['result']) > 0) {
            $recordId = $response->json()['result'][0]['id'];
            $deleteResponse = $this->getRequest()
                ->delete("{$this->baseUrl}/zones/{$zoneId}/dns_records/{$recordId}");
            
            if (!$deleteResponse->successful()) {
                throw new \Exception("Cloudflare API Error (Delete DNS): " . $deleteResponse->body());
            }
        }

        return true;
    }

    /**
     * Update Tunnel Ingress Configuration.
     */
    public function updateTunnelIngress($services, $accountId, $tunnelId)
    {
        $ingress = [];

        foreach ($services as $service) {
            $ingress[] = [
                'hostname' => $service->full_domain,
                'service' => "http://localhost:{$service->port}"
            ];
        }

        $ingress[] = [
            'service' => 'http_status:404'
        ];

        $response = $this->getRequest()
            ->put("{$this->baseUrl}/accounts/{$accountId}/cfd_tunnel/{$tunnelId}/configurations", [
                'config' => [
                    'ingress' => $ingress
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception("Cloudflare API Error (Tunnel Config): " . $response->body());
        }

        return $response->successful();
    }

    /**
     * Verify Cloudflare Credentials.
     */
    public function verifyCredentials($email, $token, $zoneId = null, $accountId = null)
    {
        // 1. Verify Token
        $tokenResponse = Http::withToken($token)->get("{$this->baseUrl}/user/tokens/verify");
        
        if (!$tokenResponse->successful() || $tokenResponse->json()['result']['status'] !== 'active') {
            throw new \Exception("Invalid Cloudflare API Token or Token is not active.");
        }

        // 2. Verify Zone (optional)
        if ($zoneId) {
            $zoneResponse = Http::withHeaders([
                'X-Auth-Email' => $email,
                'X-Auth-Key' => $token,
            ])->get("{$this->baseUrl}/zones/{$zoneId}");

            if (!$zoneResponse->successful()) {
                throw new \Exception("Invalid Zone ID or insufficient permissions.");
            }
        }

        // 3. Verify Account (optional)
        if ($accountId) {
            $accountResponse = Http::withHeaders([
                'X-Auth-Email' => $email,
                'X-Auth-Key' => $token,
            ])->get("{$this->baseUrl}/accounts/{$accountId}");

            if (!$accountResponse->successful()) {
                throw new \Exception("Invalid Account ID or insufficient permissions.");
            }
        }

        return true;
    }
}
