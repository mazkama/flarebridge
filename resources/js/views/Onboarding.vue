<template>
    <div class="flex items-center justify-center min-h-screen p-6 relative overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-600/20 blur-[120px] rounded-full"></div>

        <div class="w-full max-w-2xl bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl overflow-hidden relative z-10">
            <div class="p-8 md:p-12">
                <!-- Header -->
                <div class="text-center mb-10">
                    <div class="inline-block p-4 bg-indigo-500/10 rounded-2xl mb-4">
                        <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight mb-2">Welcome to FlareBridge</h1>
                    <p class="text-slate-400">Let's get your professional subdomain api ready in a few steps.</p>
                </div>

                <!-- Step Indicator -->
                <div class="flex justify-center mb-10">
                    <div class="flex items-center space-x-4">
                        <div v-for="i in 3" :key="i" 
                            class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                            :class="step >= i ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-500 border border-white/5'">
                            {{ i }}
                        </div>
                    </div>
                </div>

                <!-- Step 1: Cloudflare Credentials -->
                <div v-if="step === 1" class="space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold mb-6">Step 1: Cloudflare Credentials</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Cloudflare Email</label>
                                <input v-model="form.cloudflare_email" type="email" placeholder="admin@example.com"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                                <p class="text-xs text-slate-500">Only required if using Global API Key.</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Cloudflare API Token / Key</label>
                                <input v-model="form.cloudflare_api_token" type="password" placeholder="••••••••••••••••"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Primary Domain -->
                <div v-if="step === 2" class="space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold mb-6">Step 2: Primary Domain</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Domain Name</label>
                                <input v-model="domain.domain" type="text" placeholder="example.com"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-300">Zone ID</label>
                                    <input v-model="domain.zone_id" type="text" placeholder="CF Zone ID"
                                        class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-300">Account ID</label>
                                    <input v-model="domain.account_id" type="text" placeholder="CF Account ID"
                                        class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Tunnel ID</label>
                                <input v-model="domain.tunnel_id" type="text" placeholder="Cloudflare Tunnel ID"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Admin Account -->
                <div v-if="step === 3" class="space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold mb-6">Step 3: Administrative Account</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Admin Name</label>
                                <input v-model="form.admin_name" type="text" placeholder="Admin User"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Admin Email</label>
                                <input v-model="form.admin_email" type="email" placeholder="admin@example.com"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Ready to Launch -->
                <div v-if="step === 4" class="space-y-6 text-center">
                    <div>
                        <h2 class="text-xl font-semibold mb-6">Ready to Launch!</h2>
                        <div class="p-6 bg-indigo-500/5 border border-indigo-500/20 rounded-2xl mb-8">
                            <p class="text-slate-300 mb-4">We will save your configurations and prepare everything for you. After this, you will be redirected to the dashboard.</p>
                            <div class="flex flex-col items-center space-y-2 text-indigo-400">
                                <span class="text-sm text-slate-500">Domain: {{ domain.domain }}</span>
                                <span class="text-lg font-bold">{{ form.admin_name }} ({{ form.admin_email }})</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-12 flex items-center justify-between">
                    <button v-if="step > 1" @click="step--" 
                        class="px-6 py-3 font-medium text-slate-400 hover:text-white transition-colors">
                        Back
                    </button>
                    <div v-else></div>

                    <button @click="nextStep" :disabled="loading"
                        class="px-8 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50 flex items-center">
                        <span v-if="loading" class="mr-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        {{ step === 4 ? 'Complete Setup' : 'Next Step' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const step = ref(1);
const loading = ref(false);
const router = useRouter();

const form = reactive({
    cloudflare_email: '',
    cloudflare_api_token: '',
    admin_email: '',
    admin_name: '',
});

const domain = reactive({
    domain: '',
    zone_id: '',
    account_id: '',
    tunnel_id: '',
});

const nextStep = () => {
    if (step.value < 4) {
        step.value++;
    } else {
        finishOnboarding();
    }
};

const finishOnboarding = async () => {
    loading.value = true;
    try {
        const payload = {
            ...form,
            domain: domain
        };

        const { data } = await axios.post('/api/setup', payload);

        if (data.status === 'success') {
            // Save token and redirect
            localStorage.setItem('flare_token', data.data.token);
            // Re-configure axios for future requests
            axios.defaults.headers.common['Authorization'] = `Bearer ${data.data.token}`;
            router.push('/');
        }
    } catch (error) {
        alert('Error saving setup: ' + (error.response?.data?.message || error.message));
    } finally {
        loading.value = false;
    }
};
</script>
