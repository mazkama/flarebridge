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
                <span class="text-xl font-bold tracking-tight">FlareBridge <span class="text-indigo-400 text-sm ml-1">v1.1</span></span>
            </div>

            <div class="flex items-center bg-slate-800/50 p-1 rounded-xl border border-white/5">
                <button @click="setMode('docs')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                    :class="appMode === 'docs' ? 'bg-indigo-500 text-white shadow-lg' : 'text-slate-400 hover:text-white'">
                    {{ t('dashboard.docs_nav') }}
                </button>
                <button @click="setMode('dashboard')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                    :class="appMode === 'dashboard' ? 'bg-indigo-500 text-white shadow-lg' : 'text-slate-400 hover:text-white'">
                    {{ t('dashboard.management') }}
                </button>
            </div>

            <div class="flex items-center space-x-2">
                <!-- Language Switcher -->
                <div class="flex bg-slate-800/50 p-1 rounded-xl border border-white/5 mr-2">
                    <button v-for="l in allLocales" :key="l" @click="setLocale(l)"
                        class="px-2 py-1 rounded-lg text-[10px] font-black transition-all uppercase"
                        :class="locale === l ? 'bg-indigo-500 text-white' : 'text-slate-500 hover:text-slate-300'">
                        {{ l }}
                    </button>
                </div>

                <button @click="showMobileMenu = !showMobileMenu" class="lg:hidden p-2 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div v-if="user" class="hidden sm:flex flex-col items-end mr-2">
                    <span class="text-sm font-bold">{{ user.name }}</span>
                    <span class="text-xs text-slate-500">{{ user.email }}</span>
                </div>
                <button @click="logout" class="p-2 text-slate-400 hover:text-red-400 transition-colors" :title="t('common.logout')">
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
                        <div class="lg:col-span-1 space-y-6 lg:block" :class="showMobileMenu ? 'block' : 'hidden'">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold">{{ t('dashboard.domains') }}</h2>
                                <button @click="showAddDomain = true" class="lg:hidden text-indigo-400 text-sm font-bold">+ {{ t('common.new') }}</button>
                            </div>
                            <div class="space-y-3">
                                <div v-for="domain in domains" :key="domain.id" 
                                    class="flex items-center justify-between p-4 bg-slate-800/30 rounded-xl border border-white/5 hover:border-indigo-500/30 transition-all cursor-pointer group"
                                    :class="{'border-indigo-500 bg-indigo-500/10': selectedDomain?.id === domain.id}"
                                    @click="selectDomain(domain); showMobileMenu = false">
                                    <div class="flex items-center space-x-3 overflow-hidden">
                                        <div class="w-2 h-2 rounded-full flex-shrink-0" :class="selectedDomain?.id === domain.id ? 'bg-indigo-400' : 'bg-slate-600'"></div>
                                        <span class="font-medium truncate">{{ domain.domain }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                        <button @click.stop="editDomain(domain)" class="p-1 text-slate-500 hover:text-indigo-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button @click.stop="deleteDomain(domain.id)" class="p-1 text-slate-500 hover:text-red-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button @click="showAddDomain = true" class="hidden lg:block w-full py-3 border border-dashed border-white/10 rounded-xl text-slate-500 hover:text-indigo-400 hover:border-indigo-500/40 transition-all text-sm font-medium">
                                + {{ t('dashboard.add_new_domain') }}
                            </button>

                            <!-- Danger Zone -->
                            <div class="pt-10 mt-10 border-t border-white/5">
                                <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest mb-4">{{ t('dashboard.danger_zone') }}</h3>
                                <button @click="showResetModal = true" class="w-full py-3 px-4 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl text-xs font-bold transition-all flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>{{ t('dashboard.reset_button') }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Main: Subdomains -->
                        <div class="lg:col-span-3 space-y-8">
                            <div v-if="selectedDomain" class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-2xl font-bold">{{ selectedDomain.domain }}</h2>
                                        <p class="text-slate-400 text-sm">{{ t('dashboard.managing_subdomains') }}</p>
                                    </div>
                                    <button @click="showAddSubdomain = true" 
                                        class="px-6 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-lg shadow-lg shadow-indigo-500/20 transition-all active:scale-95 text-sm">
                                        {{ t('dashboard.new_subdomain') }}
                                    </button>
                                </div>

                                <div class="bg-slate-800/20 border border-white/5 rounded-2xl overflow-x-auto shadow-2xl">
                                    <table class="w-full text-left min-w-[600px]">
                                        <thead>
                                            <tr class="bg-slate-800/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
                                                <th class="px-6 py-4">{{ t('dashboard.subdomain') }}</th>
                                                <th class="px-6 py-4">{{ t('dashboard.port') }}</th>
                                                <th class="px-6 py-4">{{ t('dashboard.full_url') }}</th>
                                                <th class="px-6 py-4 text-right">{{ t('dashboard.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            <tr v-for="sub in subdomains" :key="sub.id" class="hover:bg-white/5 transition-colors group">
                                                <td class="px-6 py-4 font-medium">{{ sub.subdomain }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2 py-1 bg-slate-800 rounded text-xs font-mono">{{ sub.port }}</span>
                                                </td>
                                                <td class="px-6 py-4 text-slate-400 text-sm truncate max-w-[150px] sm:max-w-xs">
                                                    https://{{ sub.subdomain }}.{{ selectedDomain.domain }}
                                                </td>
                                                <td class="px-6 py-4 text-right flex items-center justify-end space-x-2">
                                                    <button @click="editSubdomain(sub)" class="p-2 text-slate-600 hover:text-indigo-400 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                    <button @click="confirmDeleteSubdomain(sub.id)" class="p-2 text-slate-600 hover:text-red-400 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="subdomains.length === 0">
                                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 italic">{{ t('dashboard.no_subdomains') }}</td>
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
                                <p class="text-slate-500 font-medium">{{ t('dashboard.select_domain') }}</p>
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

        <!-- Modal: Add/Edit Subdomain -->
        <div v-if="showAddSubdomain || editingSub" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-slate-900 border border-white/10 rounded-2xl p-8 shadow-2xl">
                <h3 class="text-xl font-bold mb-6 flex items-center justify-between">
                    {{ editingSub ? t('dashboard.edit_subdomain_title') : t('dashboard.create_subdomain_title') }}
                    <span v-if="editingSub" class="text-[10px] bg-slate-800 text-slate-400 px-2 py-1 rounded">ID: {{ subForm.id }}</span>
                </h3>
                <div class="space-y-5 mb-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            {{ t('dashboard.subdomain_prefix') }}
                            <div class="group relative ml-2">
                                <span class="cursor-help text-slate-600 hover:text-indigo-400">?</span>
                                <div class="absolute bottom-full left-0 mb-2 w-48 p-2 bg-slate-800 text-[10px] rounded shadowing-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                                    {{ t('dashboard.subdomain_hint') }}
                                </div>
                            </div>
                        </label>
                        <div class="flex items-center">
                            <input v-model="subForm.subdomain" type="text" placeholder="e.g. myapp"
                                class="flex-grow bg-slate-800 border-y border-l border-white/10 rounded-l-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all font-medium text-white">
                            <span class="bg-slate-800 border border-white/10 rounded-r-xl px-4 py-3 text-slate-500 font-medium whitespace-nowrap text-sm">.{{ selectedDomain?.domain }}</span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                                {{ t('dashboard.app_port') }}
                                <div class="group relative ml-2">
                                    <span class="cursor-help text-slate-600 hover:text-indigo-400">?</span>
                                    <div class="absolute bottom-full left-0 mb-2 w-48 p-2 bg-slate-800 text-[10px] rounded shadowing-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                                        {{ t('dashboard.port_hint') }}
                                    </div>
                                </div>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <span class="mr-2 text-[10px] font-bold text-slate-600 uppercase">{{ subForm.customPort ? t('common.custom') : t('common.auto') }}</span>
                                <div class="relative">
                                    <input type="checkbox" v-model="subForm.customPort" class="sr-only">
                                    <div class="w-8 h-4 bg-slate-800 rounded-full border border-white/5"></div>
                                    <div class="absolute left-1 top-1 w-2 h-2 rounded-full transition-transform duration-200"
                                        :class="subForm.customPort ? 'translate-x-4 bg-indigo-500' : 'bg-slate-600'"></div>
                                </div>
                            </label>
                        </div>
                        <div v-if="subForm.customPort" class="animate-fade-in">
                            <input v-model="subForm.port" type="number" placeholder="e.g. 8080"
                                class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all font-mono text-white">
                            <p class="text-[10px] text-slate-500 mt-1">{{ t('dashboard.port_auto_hint') }}</p>
                        </div>
                        <div v-else class="px-4 py-3 bg-slate-800/30 border border-dashed border-white/5 rounded-xl text-slate-500 text-xs italic">
                            {{ t('dashboard.port_assigned_hint') }}
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button @click="closeSubModal" class="flex-grow py-3 text-slate-400 hover:text-white transition-colors text-sm font-bold">{{ t('common.cancel') }}</button>
                    <button @click="saveSubdomain" :disabled="!subForm.subdomain || creating"
                        class="flex-grow py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-black rounded-xl transition-all active:scale-95 disabled:opacity-50 shadow-lg shadow-indigo-500/20 text-sm">
                        <span v-if="creating" class="flex items-center justify-center">
                            <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ t('common.saving') }}
                        </span>
                        <span v-else>{{ editingSub ? t('dashboard.update_mapping') : t('dashboard.create_mapping') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Add/Edit Domain -->
        <div v-if="showAddDomain || editingDom" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-lg bg-slate-900 border border-white/10 rounded-2xl p-8 shadow-2xl">
                <h3 class="text-xl font-bold mb-6">{{ editingDom ? t('dashboard.edit_domain_title') : t('dashboard.register_domain_title') }}</h3>
                <div class="space-y-4 mb-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            {{ t('onboarding.domain_label') }}
                            <div class="group relative ml-2">
                                <span class="cursor-help text-slate-600 hover:text-indigo-400">?</span>
                                <div class="absolute bottom-full left-0 mb-2 w-48 p-2 bg-slate-800 text-[10px] rounded shadowing-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                                    {{ t('onboarding.domain_hint') }}
                                </div>
                            </div>
                        </label>
                        <input v-model="domainForm.domain" type="text" placeholder="e.g. example.com"
                            class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                                {{ t('onboarding.zone_label') }}
                                <div class="group relative ml-2">
                                    <span class="cursor-help text-slate-600 hover:text-indigo-400">?</span>
                                    <div class="absolute bottom-full left-0 mb-2 w-48 p-2 bg-slate-800 text-[10px] rounded shadowing-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                                        {{ t('onboarding.zone_hint') }}
                                    </div>
                                </div>
                            </label>
                            <input v-model="domainForm.zone_id" type="text" placeholder="Cloudflare Zone ID"
                                class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                                {{ t('onboarding.account_label') }}
                                <div class="group relative ml-2">
                                    <span class="cursor-help text-slate-600 hover:text-indigo-400">?</span>
                                    <div class="absolute bottom-full left-0 mb-2 w-48 p-2 bg-slate-800 text-[10px] rounded shadowing-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                                        {{ t('onboarding.account_hint') }}
                                    </div>
                                </div>
                            </label>
                            <input v-model="domainForm.account_id" type="text" placeholder="Cloudflare Account ID"
                                class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all text-sm">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            {{ t('onboarding.tunnel_label') }}
                            <div class="group relative ml-2">
                                <span class="cursor-help text-slate-600 hover:text-indigo-400">?</span>
                                <div class="absolute bottom-full left-0 mb-2 w-48 p-2 bg-slate-800 text-[10px] rounded shadowing-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                                    {{ t('onboarding.tunnel_hint') }}
                                </div>
                            </div>
                        </label>
                        <input v-model="domainForm.tunnel_id" type="text" placeholder="e.g. 1234abcd-..."
                            class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all">
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button @click="closeDomainModal" class="flex-grow py-3 text-slate-400 hover:text-white transition-colors">{{ t('common.cancel') }}</button>
                    <button @click="saveDomain" :disabled="creatingDomain || !domainForm.domain"
                        class="flex-grow py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl transition-all active:scale-95 disabled:opacity-50">
                        {{ creatingDomain ? t('common.saving') : (editingDom ? t('common.update') : t('common.register')) }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Reset System (Danger Zone) -->
        <div v-if="showResetModal" class="fixed inset-0 z-[70] flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-md">
            <div class="w-full max-w-md bg-slate-900 border border-red-500/30 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-500/10 blur-[60px] rounded-full animate-pulse"></div>
                <div class="relative z-10 text-center">
                    <div class="w-16 h-16 bg-red-500/20 rounded-2xl flex items-center justify-center mx-auto mb-6 text-red-500">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black mb-2 text-white">{{ t('dashboard.reset_confirm_title') }}</h3>
                    <p class="text-slate-400 mb-8 text-sm leading-relaxed">
                        {{ t('dashboard.reset_confirm_desc') }}
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
                            {{ resetting ? t('dashboard.resetting') : t('dashboard.reset_confirm_button') }}
                        </button>
                        <button @click="showResetModal = false" :disabled="resetting" class="w-full py-4 text-slate-500 hover:text-white font-bold transition-colors">
                            {{ t('dashboard.reset_cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Docs from './Docs.vue';
import { useI18n } from '../composables/useI18n';

const { t, locale, allLocales, setLocale } = useI18n();
const router = useRouter();
const appMode = ref('docs');
const user = ref(null);
const domains = ref([]);
const selectedDomain = ref(null);
const subdomains = ref([]);

const showAddSubdomain = ref(false);
const editingSub = ref(null);
const showAddDomain = ref(false);
const editingDom = ref(null);
const showResetModal = ref(false);
const showMobileMenu = ref(false);

const creating = ref(false);
const creatingDomain = ref(false);
const resetting = ref(false);

const domainForm = reactive({
    id: null,
    domain: '',
    zone_id: '',
    account_id: '',
    tunnel_id: '',
});

const subForm = reactive({
    id: null,
    subdomain: '',
    port: '',
    customPort: false,
});

onMounted(async () => {
    const token = localStorage.getItem('flare_token');
    if (!token) return router.push('/login');

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    try {
        const { data: userData } = await axios.get('/api/user');
        user.value = userData;

        const { data: settingsData } = await axios.get('/api/v1/settings?keys=app_mode');
        appMode.value = settingsData.data.app_mode || 'docs';

        await fetchDomains();
    } catch (error) {
        if (error.response?.status === 401) {
            localStorage.removeItem('flare_token');
            router.push('/login');
        }
    }
});

const fetchDomains = async () => {
    try {
        const { data } = await axios.get('/api/v1/domains');
        domains.value = data.data;
        if (domains.value.length > 0 && !selectedDomain.value) {
            selectDomain(domains.value[0]);
        }
    } catch (error) {
        console.error(t('dashboard.fetch_domains_error'));
    }
};

const selectDomain = async (domain) => {
    selectedDomain.value = domain;
    await fetchSubdomains();
};

const fetchSubdomains = async () => {
    if (!selectedDomain.value) return;
    try {
        const { data } = await axios.get(`/api/v1/subdomains?domain_id=${selectedDomain.value.id}`);
        subdomains.value = data.data;
    } catch (error) {
        console.error(t('dashboard.fetch_subdomains_error'));
    }
};

// Domain CRUD
const saveDomain = async () => {
    if (!domainForm.domain) return;
    creatingDomain.value = true;
    try {
        if (editingDom.value) {
            await axios.put(`/api/v1/domains/${domainForm.id}`, domainForm);
        } else {
            await axios.post('/api/v1/domains', domainForm);
        }
        closeDomainModal();
        await fetchDomains();
    } catch (error) {
        alert(error.response?.data?.message || t('dashboard.save_domain_error'));
    } finally {
        creatingDomain.value = false;
    }
};

const editDomain = (domain) => {
    editingDom.value = true;
    domainForm.id = domain.id;
    domainForm.domain = domain.domain;
    domainForm.zone_id = domain.zone_id;
    domainForm.account_id = domain.account_id;
    domainForm.tunnel_id = domain.tunnel_id;
    showAddDomain.value = true;
};

const deleteDomain = async (id) => {
    if (!confirm(t('dashboard.delete_domain_confirm'))) return;
    try {
        await axios.delete(`/api/v1/domains/${id}`);
        if (selectedDomain.value?.id === id) selectedDomain.value = null;
        await fetchDomains();
    } catch (error) {
        alert(error.response?.data?.message || t('dashboard.delete_domain_error'));
    }
};

const closeDomainModal = () => {
    showAddDomain.value = false;
    editingDom.value = null;
    domainForm.id = null;
    domainForm.domain = '';
    domainForm.zone_id = '';
    domainForm.account_id = '';
    domainForm.tunnel_id = '';
};

// Subdomain CRUD
const saveSubdomain = async () => {
    if (!subForm.subdomain || !selectedDomain.value) return;
    creating.value = true;
    try {
        const payload = { 
            subdomain: subForm.subdomain,
            port: subForm.customPort ? subForm.port : null
        };

        if (editingSub.value) {
            await axios.put(`/api/v1/subdomains/${subForm.id}`, payload);
        } else {
            await axios.post('/api/v1/subdomains', {
                ...payload,
                domain_id: selectedDomain.value.id
            });
        }
        closeSubModal();
        await fetchSubdomains();
    } catch (error) {
        alert(error.response?.data?.message || t('dashboard.save_subdomain_error'));
    } finally {
        creating.value = false;
    }
};

const editSubdomain = (sub) => {
    editingSub.value = true;
    subForm.id = sub.id;
    subForm.subdomain = sub.subdomain;
    subForm.port = sub.port;
    subForm.customPort = true; // Always show port when editing
    showAddSubdomain.value = true;
};

const confirmDeleteSubdomain = async (id) => {
    if (!confirm(t('dashboard.delete_subdomain_confirm'))) return;
    try {
        await axios.delete(`/api/v1/subdomains/${id}`);
        await fetchSubdomains();
    } catch (error) {
        alert(t('dashboard.delete_subdomain_error'));
    }
};

const closeSubModal = () => {
    showAddSubdomain.value = false;
    editingSub.value = null;
    subForm.id = null;
    subForm.subdomain = '';
    subForm.port = '';
    subForm.customPort = false;
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

const logout = async () => {
    try {
        await axios.post('/api/logout');
    } finally {
        localStorage.removeItem('flare_token');
        router.push('/login');
    }
};

const performReset = async () => {
    resetting.value = true;
    try {
        await axios.post('/api/v1/system/reset');
        localStorage.removeItem('flare_token');
        window.location.href = '/onboarding';
    } catch (error) {
        alert(t('dashboard.reset_error') + ': ' + (error.response?.data?.message || error.message));
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
