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
        // 1. Get current ingress rules from Cloudflare
        $currentIngress = [];
        try {
            $currentIngress = $this->getTunnelIngress($accountId, $tunnelId);
        } catch (\Exception $e) {
            Log::warning("Could not fetch existing tunnel configuration during update: " . $e->getMessage());
        }

        // 2. Identify the root domains we are managing here to filter.
        // We can look up the domain associated with the first service, or fetch all domains managed by this app.
        // To be safe, we will determine which root domain(s) are related to the $services being updated.
        $managedDomains = [];
        if ($services instanceof \Illuminate\Support\Collection) {
            $domainIds = $services->pluck('domain_id')->unique()->toArray();
            $managedDomains = \App\Models\Domain::whereIn('id', $domainIds)->pluck('domain')->toArray();
        } elseif (!empty($services)) {
            // It could be an array of Service models
            $domainIds = [];
            foreach ($services as $srv) {
                $domainIds[] = $srv->domain_id;
            }
            $managedDomains = \App\Models\Domain::whereIn('id', array_unique($domainIds))->pluck('domain')->toArray();
        }

        $ingress = [];

        // 3. Keep external ingress rules that are NOT part of our managed domains
        // (i.e. rules for other domains/subdomains routed through the same tunnel)
        foreach ($currentIngress as $rule) {
            if (isset($rule['hostname'])) {
                $host = $rule['hostname'];
                $isManaged = false;
                foreach ($managedDomains as $managedDomain) {
                    // Check if host matches the managed domain (e.g., host is sub.domain.com and domain is domain.com)
                    if (str_ends_with($host, $managedDomain)) {
                        $isManaged = true;
                        break;
                    }
                }
                
                // If it's not managed by the domain(s) being updated, preserve it!
                if (!$isManaged) {
                    $ingress[] = $rule;
                }
            }
        }

        // 4. Add our updated services/subdomains
        foreach ($services as $service) {
            $ingress[] = [
                'hostname' => $service->full_domain,
                'service' => "http://localhost:{$service->port}"
            ];
        }

        // 5. Append the catch-all rule (http_status:404) at the end.
        // Note: Cloudflare requires the catch-all to be the last rule.
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
        Log::info("Cloudflare Verification Attempt", [
            'email' => $email,
            'is_token_long' => strlen($token) >= 40,
            'zone_id' => $zoneId,
            'account_id' => $accountId
        ]);

        // Detect if Global API Key (needs email) or API Token
        // If email is provided, we assume it's a Global Key. 
        // If it's a token, email should be null in the request.
        $isGlobalKey = !empty($email);

        // 1. Verify Connectivity/Token
        if ($isGlobalKey) {
            $tokenResponse = Http::withHeaders([
                'X-Auth-Email' => $email,
                'X-Auth-Key' => $token,
            ])->get("{$this->baseUrl}/user");
        } else {
            $tokenResponse = Http::withToken($token)->get("{$this->baseUrl}/user/tokens/verify");
        }
        
        Log::info("Cloudflare Auth Response", [
            'status' => $tokenResponse->status(),
            'body' => $tokenResponse->json()
        ]);

        if (!$tokenResponse->successful() || ($isGlobalKey && $tokenResponse->json()['success'] !== true) || (!$isGlobalKey && $tokenResponse->json()['result']['status'] !== 'active')) {
            $errorMsg = $tokenResponse->json()['errors'][0]['message'] ?? 'Invalid Cloudflare API Token or Key.';
            throw new \Exception("Cloudflare validation failed: " . $errorMsg);
        }

        // 2. Verify Zone (optional)
        if ($zoneId) {
            $zoneRequest = $isGlobalKey 
                ? Http::withHeaders(['X-Auth-Email' => $email, 'X-Auth-Key' => $token])
                : Http::withToken($token);

            $zoneResponse = $zoneRequest->get("{$this->baseUrl}/zones/{$zoneId}");

            if (!$zoneResponse->successful()) {
                Log::warning("Cloudflare Zone failure", ['body' => $zoneResponse->json()]);
                throw new \Exception("Invalid Zone ID or insufficient permissions for this domain.");
            }
        }

        // 3. Verify Account (optional)
        if ($accountId) {
            $accountRequest = $isGlobalKey 
                ? Http::withHeaders(['X-Auth-Email' => $email, 'X-Auth-Key' => $token])
                : Http::withToken($token);

            $accountResponse = $accountRequest->get("{$this->baseUrl}/accounts/{$accountId}");

            if (!$accountResponse->successful()) {
                Log::warning("Cloudflare Account failure", ['body' => $accountResponse->json()]);
                throw new \Exception("Invalid Account ID or insufficient permissions.");
            }
        }

        return true;
    }

    /**
     * Get Tunnel Details.
     */
    public function getTunnel($accountId, $tunnelId)
    {
        $response = $this->getRequest()
            ->get("{$this->baseUrl}/accounts/{$accountId}/cfd_tunnel/{$tunnelId}");

        if (!$response->successful()) {
            throw new \Exception("Cloudflare API Error (Get Tunnel): " . $response->body());
        }

        return $response->json()['result'];
    }

    /**
     * Get Tunnel Ingress Configuration.
     */
    public function getTunnelIngress($accountId, $tunnelId)
    {
        $response = $this->getRequest()
            ->get("{$this->baseUrl}/accounts/{$accountId}/cfd_tunnel/{$tunnelId}/configurations");

        if ($response->status() === 404) {
            // Tunnel has no active configuration yet
            return [];
        }

        if (!$response->successful()) {
            throw new \Exception("Cloudflare API Error (Get Tunnel Ingress): " . $response->body());
        }

        $result = $response->json()['result'];
        return $result['config']['ingress'] ?? [];
    }

    /**
     * Import existing ingress configurations from Cloudflare into local database.
     */
    public function importExistingIngress($domain)
    {
        try {
            $ingressRules = $this->getTunnelIngress($domain->account_id, $domain->tunnel_id);
            
            foreach ($ingressRules as $rule) {
                if (isset($rule['hostname'])) {
                    $hostname = $rule['hostname'];
                    
                    // Check if the hostname belongs to this domain
                    $domainName = $domain->domain;
                    if ($hostname === $domainName) {
                        // The domain itself is mapped directly
                        $subdomain = '@';
                    } elseif (str_ends_with($hostname, '.' . $domainName)) {
                        $subdomain = substr($hostname, 0, -strlen('.' . $domainName));
                    } else {
                        // Belongs to another domain
                        continue;
                    }

                    // Check if already exists in DB
                    $exists = \App\Models\Service::where('domain_id', $domain->id)
                        ->where('subdomain', $subdomain)
                        ->exists();

                    if (!$exists) {
                        // Parse port from service URL (e.g. "http://localhost:8080")
                        $serviceUrl = $rule['service'] ?? '';
                        $port = null;
                        
                        if (preg_match('/:(\d+)/', $serviceUrl, $matches)) {
                            $port = intval($matches[1]);
                        } else {
                            if (str_starts_with($serviceUrl, 'https://')) {
                                $port = 443;
                            } elseif (str_starts_with($serviceUrl, 'http://')) {
                                $port = 80;
                            }
                        }

                        // Ensure port is unique in local db before saving, otherwise generate one
                        if ($port) {
                            $portExists = \App\Models\Service::where('port', $port)->exists();
                            if ($portExists) {
                                Log::warning("Port {$port} from Cloudflare ingress already exists in local DB. Generating unique port instead.");
                                $port = $this->generateUniquePort();
                            }
                        } else {
                            $port = $this->generateUniquePort();
                        }

                        \App\Models\Service::create([
                            'domain_id' => $domain->id,
                            'subdomain' => $subdomain,
                            'full_domain' => $hostname,
                            'port' => $port,
                            'status' => 'active'
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to import existing ingress for domain {$domain->domain}: " . $e->getMessage());
        }
    }

    private function generateUniquePort()
    {
        do {
            $port = rand(3000, 9000);
        } while (\App\Models\Service::where('port', $port)->exists());

        return $port;
    }
}
