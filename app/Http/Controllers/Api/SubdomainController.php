<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Domain;
use App\Models\Service;
use Illuminate\Support\Facades\Validator;

class SubdomainController extends Controller
{
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

            return response()->json([
                'status' => 'success',
                'message' => 'Subdomain created successfully',
                'data' => [
                    'url' => $service->full_domain,
                    'port' => $service->port,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create subdomain',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete subdomain mapping.
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found'
            ], 404);
        }

        try {
            $service->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Service deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete service',
                'error' => $e->getMessage()
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
