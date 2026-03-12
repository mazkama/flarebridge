<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->string('subdomain');
            $table->string('full_domain');
            $table->integer('port')->unique();
            $table->string('status')->default('active'); // active/inactive
            $table->timestamps();

            $table->unique(['domain_id', 'subdomain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
