<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flare:generate-token {email} {--name=Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API token for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name');

        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32))
            ]
        );

        $token = $user->createToken('flare-api-token')->plainTextToken;

        $this->info("Token generated successfully for {$email}");
        $this->line("Your API Token is: <fg=yellow>{$token}</>");
        $this->warn("Please copy this token and keep it safe. It will not be shown again.");
    }
}
