import { createRouter, createWebHistory } from 'vue-router';
import Onboarding from './views/Onboarding.vue';
import Dashboard from './views/Dashboard.vue';
import Docs from './views/Docs.vue';
import Login from './views/Login.vue';
import axios from 'axios';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
    },
    {
        path: '/onboarding',
        name: 'Onboarding',
        component: Onboarding,
    },
    {
        path: '/',
        name: 'Home',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/docs',
        name: 'Docs',
        component: Docs,
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    try {
        const { data } = await axios.get('/api/onboarding-check');
        const onboardingCompleted = data.data.onboarding_completed;
        const hasToken = !!localStorage.getItem('flare_token');

        if (!onboardingCompleted && to.path !== '/onboarding') {
            return next('/onboarding');
        }
        
        if (onboardingCompleted && to.path === '/onboarding') {
            return next('/');
        }

        if (onboardingCompleted && !hasToken && to.path !== '/login') {
            return next('/login');
        }

        if (hasToken && to.path === '/login') {
            return next('/');
        }

        next();
    } catch (error) {
        console.error('Router check failed:', error);
        next();
    }
});

export default router;
