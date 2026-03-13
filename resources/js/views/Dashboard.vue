<template>
    <div class="min-h-screen flex flex-col bg-slate-950 font-sans text-white">
        <!-- Modern Header -->
        <nav class="bg-slate-900/50 backdrop-blur-md border-b border-white/5 px-8 py-4 flex items-center justify-between sticky top-0 z-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight">FlareBridge <span class="text-indigo-400 text-sm ml-1">v1.0</span></span>
            </div>

            <div class="flex items-center bg-slate-800/50 p-1 rounded-xl border border-white/5">
                <button @click="setMode('docs')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                    :class="appMode === 'docs' ? 'bg-indigo-500 text-white shadow-lg' : 'text-slate-400 hover:text-white'">
                    Documentation
                </button>
                <button @click="setMode('dashboard')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                    :class="appMode === 'dashboard' ? 'bg-indigo-500 text-white shadow-lg' : 'text-slate-400 hover:text-white'">
                    Management
                </button>
            </div>

            <div class="flex items-center space-x-4">
                <div v-if="user" class="hidden md:flex flex-col items-end mr-2">
                    <span class="text-sm font-bold">{{ user.name }}</span>
                    <span class="text-xs text-slate-500">{{ user.email }}</span>
                </div>
                <button @click="logout" class="p-2 text-slate-400 hover:text-red-400 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Dynamic Content -->
        <main class="flex-grow">
            <transition name="page" mode="out-in">
                <div v-if="appMode === 'docs'" key="docs" class="max-w-7xl mx-auto py-12 px-6">
                    <Docs />
                </div>
                <div v-else key="dashboard" class="max-w-7xl mx-auto py-12 px-6">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                        <!-- Sidebar: Domains -->
                        <div class="lg:col-span-1 space-y-6">
                            <h2 class="text-xl font-bold">Domains</h2>
                            <div class="space-y-3">
                                <div v-for="domain in domains" :key="domain.id" 
                                    class="flex items-center justify-between p-4 bg-slate-800/30 rounded-xl border border-white/5 hover:border-indigo-500/30 transition-all cursor-pointer group"
                                    :class="{'border-indigo-500 bg-indigo-500/10': selectedDomain?.id === domain.id}"
                                    @click="selectDomain(domain)">
                                    <div class="flex items-center space-x-3 overflow-hidden">
                                        <div class="w-2 h-2 rounded-full flex-shrink-0" :class="selectedDomain?.id === domain.id ? 'bg-indigo-400' : 'bg-slate-600'"></div>
                                        <span class="font-medium truncate">{{ domain.domain }}</span>
                                    </div>
                                    <svg v-if="selectedDomain?.id === domain.id" class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                            <button @click="showAddDomain = true" class="w-full py-3 border border-dashed border-white/10 rounded-xl text-slate-500 hover:text-indigo-400 hover:border-indigo-500/40 transition-all text-sm font-medium">
                                + Add New Domain
                            </button>

                            <!-- Danger Zone -->
                            <div class="pt-10 mt-10 border-t border-white/5">
                                <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest mb-4">Danger Zone</h3>
                                <button @click="showResetModal = true" class="w-full py-3 px-4 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl text-xs font-bold transition-all flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Reset FlareBridge</span>
                                </button>
                            </div>
                        </div>

                        <!-- Main: Subdomains -->
                        <div class="lg:col-span-3 space-y-8">
                            <div v-if="selectedDomain" class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-2xl font-bold">{{ selectedDomain.domain }}</h2>
                                        <p class="text-slate-400 text-sm">Managing subdomains for this zone.</p>
                                    </div>
                                    <button @click="showAddSubdomain = true" 
                                        class="px-6 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-lg shadow-lg shadow-indigo-500/20 transition-all active:scale-95 text-sm">
                                        New Subdomain
                                    </button>
                                </div>

                                <div class="bg-slate-800/20 border border-white/5 rounded-2xl overflow-hidden shadow-2xl">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="bg-slate-800/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
                                                <th class="px-6 py-4">Subdomain</th>
                                                <th class="px-6 py-4">Port</th>
                                                <th class="px-6 py-4">Full URL</th>
                                                <th class="px-6 py-4 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            <tr v-for="sub in subdomains" :key="sub.id" class="hover:bg-white/5 transition-colors group">
                                                <td class="px-6 py-4 font-medium">{{ sub.subdomain }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2 py-1 bg-slate-800 rounded text-xs font-mono">{{ sub.port }}</span>
                                                </td>
                                                <td class="px-6 py-4 text-slate-400 text-sm truncate max-w-xs">
                                                    https://{{ sub.subdomain }}.{{ selectedDomain.domain }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button @click="deleteSubdomain(sub.id)" class="p-2 text-slate-600 hover:text-red-400 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="subdomains.length === 0">
                                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 italic">No subdomains found. Create your first one!</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Empty State -->
                            <div v-else class="flex flex-col items-center justify-center py-20 bg-slate-800/10 rounded-3xl border border-dashed border-white/5">
                                <svg class="w-16 h-16 text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="text-slate-500 font-medium">Select a domain from the sidebar to manage subdomains.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </main>

        <!-- Footer -->
        <footer class="py-10 text-center border-t border-white/5 text-slate-600 text-sm">
            FlareBridge &copy; 2026 - Powered by Laravel & Vue
        </footer>

        <!-- Modal: Add Subdomain -->
        <div v-if="showAddSubdomain" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-slate-900 border border-white/10 rounded-2xl p-8 shadow-2xl">
                <h3 class="text-xl font-bold mb-6">Create New Subdomain</h3>
                <div class="space-y-4 mb-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Subdomain Prefix</label>
                        <div class="flex items-center">
                            <input v-model="newSubdomain" type="text" placeholder="myapp"
                                class="flex-grow bg-slate-800 border-y border-l border-white/10 rounded-l-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all">
                            <span class="bg-slate-800 border border-white/10 rounded-r-xl px-4 py-3 text-slate-500 font-medium">.{{ selectedDomain?.domain }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button @click="showAddSubdomain = false" class="flex-grow py-3 text-slate-400 hover:text-white transition-colors">Cancel</button>
                    <button @click="createSubdomain" :disabled="!newSubdomain || creating"
                        class="flex-grow py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl transition-all active:scale-95 disabled:opacity-50">
                        {{ creating ? 'Creating...' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Add Domain -->
        <div v-if="showAddDomain" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-lg bg-slate-900 border border-white/10 rounded-2xl p-8 shadow-2xl">
                <h3 class="text-xl font-bold mb-6">Register New Domain</h3>
                <div class="space-y-4 mb-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Domain Name</label>
                        <input v-model="newDomain.domain" type="text" placeholder="example.id"
                            class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Zone ID</label>
                            <input v-model="newDomain.zone_id" type="text" placeholder="Cloudflare Zone ID"
                                class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Account ID</label>
                            <input v-model="newDomain.account_id" type="text" placeholder="Cloudflare Account ID"
                                class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all text-sm">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tunnel ID</label>
                        <input v-model="newDomain.tunnel_id" type="text" placeholder="Cloudflare Tunnel ID"
                            class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all">
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button @click="showAddDomain = false" class="flex-grow py-3 text-slate-400 hover:text-white transition-colors">Cancel</button>
                    <button @click="createDomain" :disabled="creatingDomain || !newDomain.domain"
                        class="flex-grow py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl transition-all active:scale-95 disabled:opacity-50">
                        {{ creatingDomain ? 'Saving...' : 'Register' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Reset System (Danger Zone) -->
        <div v-if="showResetModal" class="fixed inset-0 z-[70] flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-md">
            <div class="w-full max-w-md bg-slate-900 border border-red-500/30 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                <!-- Background Warning Pulse -->
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-500/10 blur-[60px] rounded-full animate-pulse"></div>
                
                <div class="relative z-10 text-center">
                    <div class="w-16 h-16 bg-red-500/20 rounded-2xl flex items-center justify-center mx-auto mb-6 text-red-500">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-2xl font-black mb-2 text-white">Wipe Everything?</h3>
                    <p class="text-slate-400 mb-8 text-sm leading-relaxed">
                        This will permanently delete all <span class="text-white font-bold">domains, subdomains, users, and settings</span>. 
                        FlareBridge will return to the onboarding screen. This action cannot be undone.
                    </p>

                    <div class="space-y-3">
                        <button @click="performReset" :disabled="resetting"
                            class="w-full py-4 bg-red-500 hover:bg-red-600 text-white font-black rounded-2xl shadow-xl shadow-red-500/20 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center">
                            <span v-if="resetting" class="mr-2">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            {{ resetting ? 'Wiping System...' : 'YES, RESET SYSTEM' }}
                        </button>
                        <button @click="showResetModal = false" :disabled="resetting" class="w-full py-4 text-slate-500 hover:text-white font-bold transition-colors">
                            Wait, Take Me Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Docs from './Docs.vue';

const router = useRouter();
const appMode = ref('docs');
const user = ref(null);
const domains = ref([]);
const selectedDomain = ref(null);
const subdomains = ref([]);

const showAddSubdomain = ref(false);
const showAddDomain = ref(false);
const showResetModal = ref(false);
const newSubdomain = ref('');
const creating = ref(false);
const creatingDomain = ref(false);
const resetting = ref(false);

const newDomain = reactive({
    domain: '',
    zone_id: '',
    account_id: '',
    tunnel_id: '',
});

onMounted(async () => {
    // Check Auth
    const token = localStorage.getItem('flare_token');
    if (!token) {
        return router.push('/onboarding');
    }

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    try {
        const { data: userData } = await axios.get('/api/user');
        user.value = userData;

        // Fetch settings for mode
        const { data: settingsData } = await axios.get('/api/v1/settings?keys=app_mode');
        appMode.value = settingsData.data.app_mode || 'docs';

        // Fetch domains
        await fetchDomains();
    } catch (error) {
        if (error.response?.status === 401) {
            localStorage.removeItem('flare_token');
            router.push('/onboarding');
        }
    }
});

const fetchDomains = async () => {
    const { data } = await axios.get('/api/v1/domains');
    domains.value = data.data;
    if (domains.value.length > 0 && !selectedDomain.value) {
        selectDomain(domains.value[0]);
    }
};

const selectDomain = async (domain) => {
    selectedDomain.value = domain;
    await fetchSubdomains();
};

const fetchSubdomains = async () => {
    if (!selectedDomain.value) return;
    const { data } = await axios.get(`/api/v1/subdomains?domain_id=${selectedDomain.value.id}`);
    subdomains.value = data.data;
};

const createDomain = async () => {
    if (!newDomain.domain) return;
    creatingDomain.value = true;
    try {
        await axios.post('/api/v1/domains', newDomain);
        showAddDomain.value = false;
        // Reset form
        newDomain.domain = '';
        newDomain.zone_id = '';
        newDomain.account_id = '';
        newDomain.tunnel_id = '';
        await fetchDomains();
    } catch (error) {
        alert('Failed to register domain');
    } finally {
        creatingDomain.value = false;
    }
};

const createSubdomain = async () => {
    if (!newSubdomain.value || !selectedDomain.value) return;
    
    creating.value = true;
    try {
        await axios.post('/api/v1/subdomains', {
            domain_id: selectedDomain.value.id,
            subdomain: newSubdomain.value
        });
        newSubdomain.value = '';
        showAddSubdomain.value = false;
        await fetchSubdomains();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to create subdomain');
    } finally {
        creating.value = false;
    }
};

const deleteSubdomain = async (id) => {
    if (!confirm('Are you sure you want to delete this subdomain and sync with Cloudflare?')) return;
    
    try {
        await axios.delete(`/api/v1/subdomains/${id}`);
        await fetchSubdomains();
    } catch (error) {
        alert('Failed to delete subdomain');
    }
};

const setMode = async (mode) => {
    appMode.value = mode;
    try {
        await axios.post('/api/v1/settings', {
            settings: { app_mode: mode }
        });
    } catch (error) {
        console.error('Failed to save mode preference');
    }
};

const logout = () => {
    localStorage.removeItem('flare_token');
    router.push('/onboarding');
};

const performReset = async () => {
    resetting.value = true;
    try {
        await axios.post('/api/v1/system/reset');
        localStorage.removeItem('flare_token');
        window.location.href = '/onboarding'; // Force full reload to reset state
    } catch (error) {
        alert('Failed to reset system: ' + (error.response?.data?.message || error.message));
        resetting.value = false;
    }
};
</script>

<style scoped>
.page-enter-active,
.page-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-enter-from {
    opacity: 0;
    transform: translateY(10px);
}
.page-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
