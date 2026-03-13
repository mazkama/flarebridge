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

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

:root {
    --primary: #6366f1;
    --primary-hover: #4f46e5;
    --bg-dark: #0f172a;
    --card-bg: rgba(30, 41, 59, 0.7);
    --border-color: rgba(255, 255, 255, 0.1);
}

body {
    margin: 0;
    padding: 0;
    font-family: 'Outfit', sans-serif;
    overflow-x: hidden;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: var(--bg-dark);
}
::-webkit-scrollbar-thumb {
    background: #1e293b;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #334155;
}
</style>
