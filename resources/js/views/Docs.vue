<template>
    <div class="space-y-12">
        <header>
            <h1 class="text-4xl font-extrabold tracking-tight mb-4">API Documentation</h1>
            <p class="text-xl text-slate-400">Simplified Subdomain & Cloudflare Tunnel Management.</p>
        </header>

        <!-- Get Started Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold border-b border-white/5 pb-2">Quick Start Guide</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-800/40 p-6 rounded-2xl border border-white/5">
                    <h3 class="font-bold text-indigo-400 mb-2">1. Authentication</h3>
                    <p class="text-sm text-slate-400 mb-4">All requests require a Bearer Token in the header.</p>
                    <code class="block bg-slate-950 p-3 rounded-lg text-xs text-indigo-300">
                        Authorization: Bearer YOUR_TOKEN
                    </code>
                </div>
                <div class="bg-slate-800/40 p-6 rounded-2xl border border-white/5">
                    <h3 class="font-bold text-indigo-400 mb-2">2. Usage Path</h3>
                    <p class="text-sm text-slate-400 mb-4">The current API version is v1.</p>
                    <code class="block bg-slate-950 p-3 rounded-lg text-xs text-indigo-300">
                        {{ apiUrl }}/api/v1/...
                    </code>
                </div>
            </div>
        </section>

        <!-- Endpoints Section -->
        <section class="space-y-8">
            <h2 class="text-2xl font-bold border-b border-white/5 pb-2">Primary Endpoints</h2>
            
            <div class="space-y-4">
                <div class="bg-slate-900/60 rounded-2xl border border-white/5 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs font-bold rounded">GET</span>
                            <span class="font-mono text-sm">/subdomains</span>
                        </div>
                        <p class="text-sm text-slate-400">Retrieve all active subdomain mappings.</p>
                    </div>
                </div>

                <div class="bg-slate-900/60 rounded-2xl border border-white/5 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <span class="px-2 py-1 bg-indigo-500/20 text-indigo-400 text-xs font-bold rounded">POST</span>
                            <span class="font-mono text-sm">/subdomains</span>
                        </div>
                        <p class="text-sm text-slate-400 mb-4">Create a new subdomain mapping and sync with Cloudflare.</p>
                        <div class="bg-slate-950 p-4 rounded-xl text-xs">
                            <pre class="text-indigo-300">
{
  "domain_id": 1,
  "subdomain": "shop"
}</pre>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900/60 rounded-2xl border border-white/5 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <span class="px-2 py-1 bg-red-500/20 text-red-400 text-xs font-bold rounded">DELETE</span>
                            <span class="font-mono text-sm">/subdomains/{id}</span>
                        </div>
                        <p class="text-sm text-slate-400">Remove a subdomain mapping and delete its DNS record.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Examples -->
        <section class="bg-indigo-600/5 border border-indigo-500/10 p-8 rounded-3xl">
            <h2 class="text-xl font-bold mb-4">Pro Tip: Port Generation</h2>
            <p class="text-slate-400 text-sm leading-relaxed">
                FlareBridge automatically generates a unique random port (3000-9000) for every new subdomain created. 
                You don't need to specify the port manually unless you're updating an existing service registry.
            </p>
        </section>
    </div>
</template>

<script setup>
const apiUrl = window.location.origin;
</script>
