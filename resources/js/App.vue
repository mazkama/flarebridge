<template>
    <div class="min-h-screen bg-[#0f172a] text-white font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Main Layout -->
        <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
                <component :is="Component" />
            </transition>
        </router-view>
    </div>
</template>

<style scoped>
/* Scoped styles only if needed */
</style>
