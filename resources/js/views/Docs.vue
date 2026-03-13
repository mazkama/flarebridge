<template>
    <div class="space-y-12">
        <header>
            <h1 class="text-4xl font-extrabold tracking-tight mb-4">API Documentation</h1>
            <p class="text-xl text-slate-400">Simplified Subdomain & Cloudflare Tunnel Management.</p>
        </header>

        <!-- Token Management Section -->
        <section class="bg-slate-900/60 p-8 rounded-3xl border border-white/10 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/>
                </svg>
            </div>
            
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h2 class="text-2xl font-black mb-2 flex items-center">
                            Access Token Management
                            <div class="group relative ml-2">
                                <span class="cursor-help text-slate-600 hover:text-indigo-400 text-sm">?</span>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 p-3 bg-slate-800 text-[10px] rounded shadowing-2xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none font-medium leading-relaxed">
                                    Use this token in your API clients (Postman, cURL, etc.) as a Bearer Token. Renewing will invalidate your current token.
                                </div>
                            </div>
                        </h2>
                        <p class="text-slate-400 text-sm">Your personal access key for automating tunnel operations.</p>
                    </div>
                    <button @click="renewToken" :disabled="renewing"
                        class="px-6 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50">
                        {{ renewing ? 'Renewing...' : 'Renew Token' }}
                    </button>
                </div>

                <div class="mt-8 bg-slate-950 p-6 rounded-2xl border border-white/5 group relative">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Current Access Token</span>
                        <button @click="copyToken" class="text-xs text-indigo-400 hover:text-indigo-300 font-bold transition-colors">
                            {{ copied ? 'COPIED!' : 'COPY TOKEN' }}
                        </button>
                    </div>
                    <div class="font-mono text-indigo-300 break-all text-sm selection:bg-indigo-500/30">
                        {{ currentToken || 'Loading token...' }}
                    </div>
                </div>
            </div>
        </section>

        <!-- Get Started Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold border-b border-white/5 pb-2">Quick Start Guide</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-800/40 p-6 rounded-2xl border border-white/5">
                    <h3 class="font-bold text-indigo-400 mb-2">1. Authentication</h3>
                    <p class="text-sm text-slate-400 mb-4 font-medium">Add the token above as a Bearer Token in your HTTP headers.</p>
                    <code class="block bg-slate-950 p-3 rounded-lg text-xs text-indigo-300 border border-white/5">
                        Authorization: Bearer {{ currentToken?.substring(0, 10) }}...
                    </code>
                </div>
                <div class="bg-slate-800/40 p-6 rounded-2xl border border-white/5">
                    <h3 class="font-bold text-indigo-400 mb-2">2. API Base URL</h3>
                    <p class="text-sm text-slate-400 mb-4 font-medium">Prefix all requests with the API path.</p>
                    <code class="block bg-slate-950 p-3 rounded-lg text-xs text-indigo-300 border border-white/5 font-mono">
                        {{ apiUrl }}/api/v1/...
                    </code>
                </div>
            </div>
        </section>

        <!-- Endpoints Section -->
        <section class="space-y-8">
            <h2 class="text-2xl font-bold border-b border-white/5 pb-2">API Reference</h2>
            
            <div class="space-y-10">
                <!-- Domains -->
                <div>
                    <h3 class="text-sm font-bold text-slate-500 mb-4 uppercase tracking-[0.2em]">Domain Management</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <EndpointRow method="GET" path="/domains" desc="List all managed root domains." />
                        <EndpointRow method="POST" path="/domains" desc="Register a new Cloudflare Domain (Zone)." json='{ "domain": "example.com", "zone_id": "...", "account_id": "...", "tunnel_id": "..." }' />
                        <EndpointRow method="PUT" path="/domains/{id}" desc="Update domain configuration." json='{ "domain": "new.com", "zone_id": "..." }' />
                        <EndpointRow method="DELETE" path="/domains/{id}" desc="Remove domain and its settings." />
                    </div>
                </div>

                <!-- Subdomains -->
                <div>
                    <h3 class="text-sm font-bold text-slate-500 mb-4 uppercase tracking-[0.2em]">Subdomain Mappings</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <EndpointRow method="GET" path="/subdomains" desc="Retrieve all active subdomain mappings." />
                        <EndpointRow method="POST" path="/subdomains" desc="Create a new mapping (Tunnel Ingress) and DNS record." json='{ "domain_id": 1, "subdomain": "shop", "port": 8080 }' />
                        <EndpointRow method="PUT" path="/subdomains/{id}" desc="Update mapping subdomain or port." json='{ "subdomain": "store", "port": 9000 }' />
                        <EndpointRow method="DELETE" path="/subdomains/{id}" desc="Remove mapping and sync with Cloudflare." />
                    </div>
                </div>

                <!-- System -->
                <div>
                    <h3 class="text-sm font-bold text-slate-500 mb-4 uppercase tracking-[0.2em]">System Operations</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <EndpointRow method="POST" path="/system/token/renew" desc="Generate a new API token for the current user." />
                        <EndpointRow method="POST" path="/system/reset" desc="WIPE all data and start onboarding (DANGER)." />
                        <EndpointRow method="POST" path="/settings" desc="Save app-wide settings (e.g. app_mode)." json='{ "settings": { "app_mode": "dashboard" } }' />
                    </div>
                </div>
            </div>
        </section>

        <!-- Examples -->
        <section class="bg-indigo-600/5 border border-indigo-500/10 p-8 rounded-3xl">
            <h2 class="text-xl font-bold mb-4">Pro Tip: Custom Port Control</h2>
            <p class="text-slate-400 text-sm leading-relaxed font-medium">
                By default, FlareBridge generates a unique random port (3000-9000). 
                However, you can specify your own <code class="text-indigo-400">port</code> in the payload. 
                FlareBridge will validate that the port is not already in use by another tunnel mapping before saving.
            </p>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted, defineProps } from 'vue';
import axios from 'axios';

const apiUrl = window.location.origin;
const currentToken = ref('');
const renewing = ref(false);
const copied = ref(false);

const fetchToken = async () => {
    try {
        // We get it from localStorage first, or the API if we want the actual hashed version (not ideal for display but good for verification)
        // Actually, we should display the plain text token only once or keep it in storage.
        // For FlareBridge, we'll show what's in localStorage or the partial one.
        currentToken.value = localStorage.getItem('flare_token');
    } catch (e) {
        console.error('Failed to get token');
    }
};

const renewToken = async () => {
    if (!confirm('Are you sure? All existing scripts using this token will break.')) return;
    renewing.value = true;
    try {
        const { data } = await axios.post('/api/v1/system/token/renew');
        const token = data.data.token;
        localStorage.setItem('flare_token', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        currentToken.value = token;
        alert('Token renewed successfully and updated in your session.');
    } catch (e) {
        alert('Failed to renew token');
    } finally {
        renewing.value = false;
    }
};

const copyToken = () => {
    navigator.clipboard.writeText(currentToken.value);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};

onMounted(fetchToken);

// Inner Component for clean list
const EndpointRow = {
    props: ['method', 'path', 'desc', 'json'],
    template: `
        <div class="bg-slate-900/60 rounded-2xl border border-white/5 overflow-hidden group hover:border-white/10 transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <span :class="{
                            'bg-green-500/20 text-green-400': method === 'GET',
                            'bg-indigo-500/20 text-indigo-400': method === 'POST',
                            'bg-yellow-500/20 text-yellow-400': method === 'PUT',
                            'bg-red-500/20 text-red-400': method === 'DELETE'
                        }" class="px-2.5 py-1 text-[10px] font-black rounded-lg uppercase tracking-widest">{{ method }}</span>
                        <span class="font-mono text-sm text-slate-200">{{ path }}</span>
                    </div>
                </div>
                <p class="text-sm text-slate-400 font-medium mb-4">{{ desc }}</p>
                <div v-if="json" class="bg-slate-950 p-4 rounded-xl text-xs border border-white/5">
                    <pre class="text-indigo-300">{{ json }}</pre>
                </div>
            </div>
        </div>
    `
};
</script>
