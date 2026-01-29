
import { createRouter, createWebHistory } from 'vue-router';
import hostingRoutes from '@/src/views/hosting/router';
import store from '@/store';

const routes = [
    {
        path: '/',
        name: 'page-welcome',
        component: () => import('@/src/views/welcome/Welcome.vue'),
    },
    {
        path: '/hosting',
        name: 'page-hosting-home',
        component: () => import('@/src/views/hosting/Home.vue'),
        meta: {
            requiresAuth: true,
        },
        redirect: '/hosting/dashboard',
        children: hostingRoutes,
    },
    {
        path: '/s',
        name: 'page-search',
        component: () => import('@/src/views/search/Search.vue'),
    },
    {
        path: '/become-a-host',
        name: 'page-become-a-host',
        component: () => import('@/src/views/BecomeAHost.vue'),
    },
    {
        path: '/reset-password',
        name: 'page-reset-password',
        component: () => import('@/src/views/auth/ResetPassword.vue'),
    },
    {
        path: '/email-verify',
        name: 'page-verify-email',
        component: () => import('@/src/views/auth/VerifyEmail.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'page-not-found',
        component: () => import('@/src/views/Page404.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0, left: 0 };
        }
    },
});

// Track current actual route and modal state
let lastSuccessfulRoute = '/';
let authModalShowing = false;

// ============================================
// TRACK SUCCESSFUL NAVIGATIONS
// ============================================
router.afterEach((to) => {
    lastSuccessfulRoute = to.path;
});

// ============================================
// GLOBAL BEFORE EACH - CHECK AUTH
// ============================================
router.beforeEach((to, from, next) => {

    // CHECK AUTH
    if (to.meta.requiresAuth && !store.getters['auth/isLoggedIn']) {

        // Prevent duplicate modals
        if (authModalShowing) {
            return next(false);
        }

        // Determine where to redirect back to
        const stayOnPath = from.name ? from.path : lastSuccessfulRoute;
        authModalShowing = true;

        // CRITICAL: Redirect to current route instead of next(false)
        next({ path: stayOnPath, replace: true });

        // Open modal after redirect
        setTimeout(() => {
            store.dispatch('openModal', {
                modalName: 'logInModal',
                options: {
                    redirectTo: to.fullPath
                }
            }).then(() => {
                authModalShowing = false;
                router.push(to.fullPath).then(() => {
                    state.mainLoading = true;
                }).finally(() => {
                    window.location.reload();
                });
            }).catch(() => {
                authModalShowing = false;
            });
        }, 0);

        return;
    }
    next();
});

export default router;
