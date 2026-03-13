<template>
    <div class="space-y-12">
        <header>
            <h1 class="text-4xl font-extrabold tracking-tight mb-4">{{ t('docs.title') }}</h1>
            <p class="text-xl text-slate-400">{{ t('docs.subtitle') }}</p>
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
                            {{ t('docs.token_mgmt_title') }}
                            <div class="group relative ml-2">
                                <span class="cursor-help text-slate-600 hover:text-indigo-400 text-sm">?</span>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 p-3 bg-slate-800 text-[10px] rounded shadowing-2xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none font-medium leading-relaxed">
                                    {{ t('docs.token_mgmt_hint') }}
                                </div>
                            </div>
                        </h2>
                        <p class="text-slate-400 text-sm">{{ t('docs.token_mgmt_desc') }}</p>
                    </div>
                    <button @click="renewToken" :disabled="renewing"
                        class="px-6 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50">
                        {{ renewing ? t('docs.renewing') : t('docs.renew_token') }}
                    </button>
                </div>

                <div class="mt-8 bg-slate-950 p-6 rounded-2xl border border-white/5 group relative">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ t('docs.current_token') }}</span>
                        <div class="flex items-center space-x-4">
                            <button @click="showToken = !showToken" class="text-slate-500 hover:text-indigo-400 transition-colors">
                                <svg v-if="!showToken" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                            <button @click="copyToken" class="text-xs text-indigo-400 hover:text-indigo-300 font-bold transition-colors">
                                {{ copied ? t('docs.copied') : t('docs.copy_token') }}
                            </button>
                        </div>
                    </div>
                    <div class="font-mono text-indigo-300 break-all text-sm selection:bg-indigo-500/30">
                        {{ currentToken ? (showToken ? currentToken : '••••••••••••••••••••••••••••••••••••••••') : t('docs.loading_token') }}
                    </div>
                </div>
            </div>
        </section>

        <!-- Get Started Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold border-b border-white/5 pb-2">{{ t('docs.quick_start') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-800/40 p-6 rounded-2xl border border-white/5">
                    <h3 class="font-bold text-indigo-400 mb-2">{{ t('docs.auth_title') }}</h3>
                    <p class="text-sm text-slate-400 mb-4 font-medium">{{ t('docs.auth_desc') }}</p>
                    <code class="block bg-slate-950 p-3 rounded-lg text-xs text-indigo-300 border border-white/5">
                        Authorization: Bearer {{ currentToken?.substring(0, 10) }}...
                    </code>
                </div>
                <div class="bg-slate-800/40 p-6 rounded-2xl border border-white/5">
                    <h3 class="font-bold text-indigo-400 mb-2">{{ t('docs.base_url_title') }}</h3>
                    <p class="text-sm text-slate-400 mb-4 font-medium">{{ t('docs.base_url_desc') }}</p>
                    <code class="block bg-slate-950 p-3 rounded-lg text-xs text-indigo-300 border border-white/5 font-mono">
                        {{ apiUrl }}/api/v1/...
                    </code>
                </div>
            </div>
        </section>

        <!-- Endpoints Section -->
        <section class="space-y-8">
            <h2 class="text-2xl font-bold border-b border-white/5 pb-2">{{ t('docs.api_ref') }}</h2>
            
            <div class="space-y-10">
                <!-- Domains -->
                <div>
                    <h3 class="text-sm font-bold text-slate-500 mb-4 uppercase tracking-[0.2em]">{{ t('docs.domain_mgmt') }}</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <EndpointRow method="GET" path="/domains" :desc="t('docs.list_domains')" />
                        <EndpointRow method="POST" path="/domains" :desc="t('docs.register_domain_desc')" json='{ "domain": "example.com", "zone_id": "...", "account_id": "...", "tunnel_id": "..." }' />
                        <EndpointRow method="PUT" path="/domains/{id}" :desc="t('docs.update_domain_desc')" json='{ "domain": "new.com", "zone_id": "..." }' />
                        <EndpointRow method="DELETE" path="/domains/{id}" :desc="t('docs.delete_domain_desc')" />
                    </div>
                </div>

                <!-- Subdomains -->
                <div>
                    <h3 class="text-sm font-bold text-slate-500 mb-4 uppercase tracking-[0.2em]">{{ t('docs.subdomain_mgmt') }}</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <EndpointRow method="GET" path="/subdomains" :desc="t('docs.list_subdomains')" />
                        <EndpointRow method="POST" path="/subdomains" :desc="t('docs.create_mapping_desc')" json='{ "domain_id": 1, "subdomain": "shop", "port": 8080 }' />
                        <EndpointRow method="PUT" path="/subdomains/{id}" :desc="t('docs.update_mapping_desc')" json='{ "subdomain": "store", "port": 9000 }' />
                        <EndpointRow method="DELETE" path="/subdomains/{id}" :desc="t('docs.delete_mapping_desc')" />
                    </div>
                </div>

                <!-- System -->
                <div>
                    <h3 class="text-sm font-bold text-slate-500 mb-4 uppercase tracking-[0.2em]">{{ t('docs.system_ops') }}</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <EndpointRow method="POST" path="/system/token/renew" :desc="t('docs.renew_api_token_desc')" />
                        <EndpointRow method="POST" path="/system/reset" :desc="t('docs.reset_system_desc')" />
                        <EndpointRow method="POST" path="/settings" :desc="t('docs.save_settings_desc')" json='{ "settings": { "app_mode": "dashboard" } }' />
                    </div>
                </div>
            </div>
        </section>

        <!-- Examples -->
        <section class="bg-indigo-600/5 border border-indigo-500/10 p-8 rounded-3xl">
            <h2 class="text-xl font-bold mb-4">{{ t('docs.pro_tip_title') }}</h2>
            <p class="text-slate-400 text-sm leading-relaxed font-medium">
                {{ t('docs.pro_tip_desc') }}
            </p>
        </section>

        <!-- Custom Confirmation Modal -->
        <transition name="modal">
            <div v-if="confirmModal.show" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
                <div class="bg-slate-900 border border-white/10 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-zoom-in">
                    <div class="p-8">
                        <div class="w-16 h-16 bg-yellow-500/10 rounded-2xl flex items-center justify-center mb-6 text-yellow-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-2">{{ confirmModal.title }}</h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-8">{{ confirmModal.message }}</p>
                        
                        <div class="flex space-x-3">
                            <button @click="confirmModal.show = false" 
                                class="flex-1 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl transition-all">
                                {{ t('common.cancel') }}
                            </button>
                            <button @click="confirmModal.action" 
                                class="flex-1 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95">
                                {{ t('common.update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Toast System (Local for Docs) -->
        <transition-group name="toast" tag="div" class="fixed bottom-8 right-8 z-[110] space-y-3">
            <div v-for="toast in toasts" :key="toast.id" 
                class="flex items-center p-4 min-w-[320px] max-w-md rounded-2xl shadow-2xl border backdrop-blur-md transition-all duration-500"
                :class="{
                    'bg-slate-900/95 border-red-500/40 text-red-100 shadow-red-500/10': toast.type === 'error',
                    'bg-slate-900/95 border-green-500/40 text-green-100 shadow-green-500/10': toast.type === 'success',
                }">
                <div class="flex-shrink-0 mr-3">
                    <div v-if="toast.type === 'error'" class="w-8 h-8 bg-red-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div v-else class="w-8 h-8 bg-green-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-grow">
                    <div class="text-xs font-black uppercase tracking-widest mb-0.5 opacity-50">{{ toast.title }}</div>
                    <div class="text-sm font-medium leading-normal">{{ toast.message }}</div>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { ref, onMounted, defineProps } from 'vue';
import axios from 'axios';
import { useI18n } from '../composables/useI18n';

const { t } = useI18n();
const apiUrl = window.location.origin;
const currentToken = ref('');
const renewing = ref(false);
const copied = ref(false);
const showToken = ref(false);
const toasts = ref([]);

const confirmModal = reactive({
    show: false,
    title: '',
    message: '',
    action: () => {}
});

const showToast = (title, message, type = 'success') => {
    const id = Date.now();
    toasts.value.push({ id, title, message, type });
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, 4000);
};

const triggerConfirm = (title, message, action) => {
    confirmModal.title = title;
    confirmModal.message = message;
    confirmModal.action = () => {
        action();
        confirmModal.show = false;
    };
    confirmModal.show = true;
};

const fetchToken = async () => {
    try {
        currentToken.value = localStorage.getItem('flare_token');
    } catch (e) {
        console.error(t('docs.fetch_token_error'));
    }
};

const renewToken = () => {
    triggerConfirm(
        t('docs.renew_token'),
        t('docs.renew_confirm'),
        async () => {
            renewing.value = true;
            try {
                const { data } = await axios.post('/api/v1/system/token/renew');
                const token = data.data.token;
                localStorage.setItem('flare_token', token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                currentToken.value = token;
                showToast(t('common.success'), t('docs.renew_success'));
            } catch (e) {
                showToast(t('common.error'), t('docs.renew_error'), 'error');
            } finally {
                renewing.value = false;
            }
        }
    );
};

const copyToken = () => {
    if (!currentToken.value) return;
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
