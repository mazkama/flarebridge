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
            ['zone_id' => null]
        );
    }
}
