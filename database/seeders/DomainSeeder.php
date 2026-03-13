<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Domain::updateOrCreate(
            ['domain' => 'mazkama.web.id'],
            [
                'zone_id' => env('CLOUDFLARE_ZONE_ID'),
                'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
                'tunnel_id' => env('CLOUDFLARE_TUNNEL_ID'),
            ]
        );
    }
}
