<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-950 font-sans text-white p-6 relative">
        <!-- Language Switcher Floating -->
        <div class="absolute top-8 right-8 z-50 flex items-center bg-slate-900/50 backdrop-blur-md rounded-xl p-1 border border-white/5">
            <button v-for="l in allLocales" :key="l" @click="setLocale(l)"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all uppercase"
                :class="locale === l ? 'bg-indigo-500 text-white' : 'text-slate-500 hover:text-slate-300'">
                {{ l }}
            </button>
        </div>

        <!-- Floating Glow -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-600/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-600/10 blur-[120px] rounded-full"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Brand -->
            <div class="flex flex-col items-center mb-8 md:mb-10">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-500/40 mb-4 animate-bounce-slow">
                    <svg class="w-8 h-8 md:w-10 md:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight">FlareBridge <span class="text-indigo-500">Login</span></h1>
                <p class="text-slate-500 mt-2 text-sm md:text-base">{{ t('login.subtitle') }}</p>
            </div>

            <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-8 rounded-3xl shadow-2xl">
                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center">
                            {{ t('login.email_label') }}
                            <div class="group relative ml-2">
                                <span class="cursor-help text-slate-600 hover:text-indigo-400">?</span>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 bg-slate-800 text-[10px] rounded shadowing-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none normal-case font-medium">
                                    {{ t('login.email_hint') }}
                                </div>
                            </div>
                        </label>
                        <input v-model="form.email" type="email" required placeholder="admin@example.com"
                            class="w-full bg-slate-900 border border-white/5 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center">
                            {{ t('login.password_label') }}
                            <div class="group relative ml-2">
                                <span class="cursor-help text-slate-600 hover:text-indigo-400">?</span>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 bg-slate-800 text-[10px] rounded shadowing-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none normal-case font-medium">
                                    {{ t('login.password_hint') }}
                                </div>
                            </div>
                        </label>
                        <input v-model="form.password" type="password" required placeholder="••••••••"
                            class="w-full bg-slate-900 border border-white/5 rounded-2xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium">
                    </div>

                    <div v-if="error" class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl text-xs font-medium animate-shake">
                        {{ error }}
                    </div>

                    <button type="submit" :disabled="loading"
                        class="w-full py-5 bg-indigo-500 hover:bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center">
                        <span v-if="loading" class="mr-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        {{ loading ? t('login.authenticating') : t('login.button') }}
                    </button>
                </form>
            </div>

            <p class="text-center mt-8 text-slate-600 text-sm">
                {{ t('login.forgot_link') }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useI18n } from '../composables/useI18n';

const router = useRouter();
const loading = ref(false);
const error = ref('');
const { t, locale, allLocales, setLocale } = useI18n();

const form = reactive({
    email: '',
    password: '',
});

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        const { data } = await axios.post('/api/login', form);
        localStorage.setItem('flare_token', data.data.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${data.data.token}`;
        router.push('/');
    } catch (err) {
        error.value = err.response?.data?.message || t('login.error_default');
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.animate-bounce-slow {
    animation: bounce 3s infinite;
}
.animate-shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>
