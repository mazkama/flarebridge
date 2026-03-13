<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flare:list-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered users who have API access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::all(['id', 'name', 'email', 'created_at']);

        if ($users->isEmpty()) {
            $this->warn("No users found in the database.");
            return;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Registered At'],
            $users->toArray()
        );
    }
}
