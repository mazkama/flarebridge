import { createRouter, createWebHistory } from 'vue-router';
import Onboarding from './views/Onboarding.vue';
import Dashboard from './views/Dashboard.vue';
import Docs from './views/Docs.vue';
import axios from 'axios';

const routes = [
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

        if (!onboardingCompleted && to.name !== 'Onboarding') {
            return next({ name: 'Onboarding' });
        }
        
        if (onboardingCompleted && to.name === 'Onboarding') {
            return next({ name: 'Home' });
        }

        next();
    } catch (error) {
        next();
    }
});

export default router;
