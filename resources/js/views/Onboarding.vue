<template>
    <div class="flex items-center justify-center min-h-screen p-6 relative overflow-hidden bg-slate-950">
        <!-- Background Decorations -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-600/20 blur-[120px] rounded-full"></div>

        <div class="w-full max-w-2xl bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl overflow-hidden relative z-10 transition-all">
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
                        <div v-for="i in 4" :key="i" 
                            class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                            :class="step >= i ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-800 text-slate-500 border border-white/5'">
                            {{ i }}
                        </div>
                    </div>
                </div>

                <!-- Step 1: Cloudflare Credentials -->
                <div v-if="step === 1" class="space-y-6 animate-fade-in">
                    <div>
                        <h2 class="text-xl font-semibold mb-6 flex items-center">
                            Step 1: Cloudflare Credentials
                            <span class="ml-2 text-xs bg-indigo-500/20 text-indigo-400 px-2 py-1 rounded">Security First</span>
                        </h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Cloudflare Email</label>
                                <input v-model="form.cloudflare_email" type="email" placeholder="admin@example.com"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder:text-slate-600">
                                <p class="text-[10px] text-slate-500 font-medium">ONLY REQUIRED IF USING GLOBAL API KEY. LEAVE BLANK FOR SCOPED TOKENS.</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Cloudflare API Token / Global Key</label>
                                <input v-model="form.cloudflare_api_token" type="password" placeholder="••••••••••••••••"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Primary Domain -->
                <div v-if="step === 2" class="space-y-6 animate-fade-in">
                    <div>
                        <h2 class="text-xl font-semibold mb-6">Step 2: Primary Domain & Zone</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Domain Name</label>
                                <input v-model="domain.domain" type="text" placeholder="e.g. mazkama.web.id"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-300">Zone ID</label>
                                    <input v-model="domain.zone_id" type="text" placeholder="Cloudflare Zone ID"
                                        class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all text-xs">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-300">Account ID</label>
                                    <input v-model="domain.account_id" type="text" placeholder="Cloudflare Account ID"
                                        class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all text-xs">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Tunnel ID</label>
                                <input v-model="domain.tunnel_id" type="text" placeholder="Cloudflare Tunnel ID"
                                    class="w-full bg-slate-800/50 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all text-xs">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Admin Account -->
                <div v-if="step === 3" class="space-y-6 animate-fade-in">
                    <div>
                        <h2 class="text-xl font-semibold mb-6">Step 3: Administrative Account</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-300">Admin Full Name</label>
                                <input v-model="form.admin_name" type="text" placeholder="e.g. John Doe"
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
                <div v-if="step === 4" class="space-y-6 text-center animate-fade-in">
                    <div>
                        <h2 class="text-xl font-semibold mb-6">Registration Summary</h2>
                        <div class="p-6 bg-indigo-500/5 border border-indigo-500/20 rounded-2xl mb-8 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-5">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Domain</span>
                                    <span class="text-white font-bold">{{ domain.domain }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Admin</span>
                                    <span class="text-white font-bold">{{ form.admin_name }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Email</span>
                                    <span class="text-indigo-400 font-bold font-mono">{{ form.admin_email }}</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-slate-400 text-sm italic">Click "Complete Setup" to finish the onboarding process.</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-12 flex items-center justify-between">
                    <button v-if="step > 1" @click="step--" 
                        class="px-6 py-3 font-medium text-slate-400 hover:text-white transition-colors">
                        Back
                    </button>
                    <div v-else></div>

                    <button @click="handleNext" :disabled="loading"
                        class="px-8 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50 flex items-center">
                        <span v-if="loading" class="mr-3">
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

        <!-- Professional Toast System -->
        <transition-group name="toast" tag="div" class="fixed bottom-8 right-8 z-[100] space-y-3">
            <div v-for="toast in toasts" :key="toast.id" 
                class="flex items-center p-4 min-w-[320px] max-w-md rounded-2xl shadow-2xl border backdrop-blur-md transition-all duration-500"
                :class="{
                    'bg-slate-900/90 border-red-500/50 text-red-100': toast.type === 'error',
                    'bg-slate-900/90 border-green-500/50 text-green-100': toast.type === 'success',
                }">
                <div class="flex-shrink-0 mr-3">
                    <svg v-if="toast.type === 'error'" class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <svg v-else class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-grow text-sm font-medium pr-2">{{ toast.message }}</div>
                <button @click="removeToast(toast.id)" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const step = ref(1);
const loading = ref(false);
const router = useRouter();
const toasts = ref([]);

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

const showToast = (message, type = 'error') => {
    const id = Date.now();
    toasts.value.push({ id, message, type });
    setTimeout(() => removeToast(id), 5000);
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

const handleNext = async () => {
    if (step.value === 1) {
        if (!form.cloudflare_api_token) return showToast('Please enter your Cloudflare API Token or Global Key.');
        await validateStep(1);
    } else if (step.value === 2) {
        if (!domain.domain || !domain.zone_id || !domain.account_id || !domain.tunnel_id) 
            return showToast('Complete all domain and tunnel details.');
        await validateStep(2);
    } else if (step.value === 3) {
        if (!form.admin_name || !form.admin_email) return showToast('Please enter admin name and email.');
        step.value++;
    } else {
        finishOnboarding();
    }
};

const validateStep = async (stepNumber) => {
    loading.value = true;
    try {
        await axios.post('/api/v1/system/validate-step', {
            step: stepNumber,
            data: {
                cloudflare_email: form.cloudflare_email,
                cloudflare_api_token: form.cloudflare_api_token,
                domain: domain
            }
        });
        step.value++;
    } catch (error) {
        const msg = error.response?.data?.message || 'Verification failed. Please check your credentials.';
        showToast(msg, 'error');
    } finally {
        loading.value = false;
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
            showToast('Setup completed! Welcome aboard.', 'success');
            localStorage.setItem('flare_token', data.data.token);
            axios.defaults.headers.common['Authorization'] = `Bearer ${data.data.token}`;
            setTimeout(() => router.push('/'), 1000);
        }
    } catch (error) {
        showToast('Final setup error: ' + (error.response?.data?.message || error.message));
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.toast-enter-active, .toast-leave-active {
    transition: all 0.4s cubic-bezier(0.18, 0.89, 0.32, 1.28);
}
.toast-enter-from { opacity: 0; transform: translateX(50px) scale(0.9); }
.toast-enter-to { opacity: 1; transform: translateX(0) scale(1); }
.toast-leave-from { opacity: 1; transform: scale(1); }
.toast-leave-to { opacity: 0; transform: scale(0.8) translateY(20px); }
</style>
