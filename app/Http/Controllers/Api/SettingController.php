<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $cloudflare;

    public function __construct(\App\Services\CloudflareService $cloudflare)
    {
        $this->cloudflare = $cloudflare;
    }

    public function index(Request $request)
    {
        $keys = $request->query('keys');
        if ($keys) {
            $keysArray = explode(',', $keys);
            $settings = \App\Models\Setting::whereIn('key', $keysArray)->get();
        } else {
            $settings = \App\Models\Setting::all();
        }

        return response()->json([
            'status' => 'success',
            'data' => $settings->pluck('value', 'key')
        ]);
    }

    public function store(Request $request)
    {
        $settings = $request->input('settings');
        if (!is_array($settings)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid settings format'], 422);
        }

        foreach ($settings as $key => $value) {
            \App\Models\Setting::set($key, $value);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Settings saved successfully'
        ]);
    }

    public function setup(Request $request)
    {
        // 1. Check if already completed
        if (\App\Models\Setting::get('onboarding_completed', false)) {
            return response()->json(['status' => 'error', 'message' => 'Onboarding already completed'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'cloudflare_email' => 'nullable|email',
            'cloudflare_api_token' => 'required|string',
            'admin_email' => 'required|email',
            'admin_name' => 'required|string',
            'domain' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        // 1.5 Validate Cloudflare Credentials
        try {
            $this->cloudflare->verifyCredentials(
                $request->cloudflare_email,
                $request->cloudflare_api_token,
                $request->domain['zone_id'],
                $request->domain['account_id']
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cloudflare validation failed: ' . $e->getMessage()
            ], 422);
        }

        // 2. Save Settings
        \App\Models\Setting::set('cloudflare_email', $request->cloudflare_email);
        \App\Models\Setting::set('cloudflare_api_token', $request->cloudflare_api_token);
        \App\Models\Setting::set('onboarding_completed', '1');

        // 3. Create First User
        $user = \App\Models\User::firstOrCreate(
            ['email' => $request->admin_email],
            [
                'name' => $request->admin_name,
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32))
            ]
        );

        // 4. Create First Domain
        \App\Models\Domain::create($request->domain);

        // 5. Generate Token
        $token = $user->createToken('flare-admin-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Setup completed successfully',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]);
    }

    public function checkOnboarding()
    {
        $onboardingCompleted = \App\Models\Setting::get('onboarding_completed', false);
        return response()->json([
            'status' => 'success',
            'data' => [
                'onboarding_completed' => (bool)$onboardingCompleted
            ]
        ]);
    }

    public function resetSystem(Request $request)
    {
        // 1. Clear all mappings and domains
        \App\Models\Service::query()->delete();
        \App\Models\Domain::query()->delete();
        
        // 2. Clear all settings
        \App\Models\Setting::query()->delete();
        
        // 3. Delete all Personal Access Tokens
        \Illuminate\Support\Facades\DB::table('personal_access_tokens')->delete();
        
        // 4. Delete all users
        \App\Models\User::query()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'System has been reset successfully. Redirecting to onboarding.'
        ]);
    }

    /**
     * Get current user's token (for documentation view).
     */
    public function viewToken(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $request->user()->currentAccessToken()->token ?? 'Check your headers'
            ]
        ]);
    }

    public function validateStep(Request $request)
    {
        // Guard: prevent use after onboarding unless specifically allowed (but for now it's for onboarding)
        if (\App\Models\Setting::get('onboarding_completed', false)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $step = $request->input('step');
        $data = $request->input('data');

        try {
            if ($step == 1) {
                // Validate Credentials only
                $this->cloudflare->verifyCredentials(
                    $data['cloudflare_email'] ?? null,
                    $data['cloudflare_api_token']
                );
            } elseif ($step == 2) {
                // Validate Domain, Zone, Account
                $this->cloudflare->verifyCredentials(
                    $data['cloudflare_email'] ?? null,
                    $data['cloudflare_api_token'],
                    $data['domain']['zone_id'] ?? null,
                    $data['domain']['account_id'] ?? null
                );
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Renew API token.
     */
    public function renewToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $token = $user->createToken('flare-access-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Token renewed successfully',
            'data' => [
                'token' => $token
            ]
        ]);
    }
}
