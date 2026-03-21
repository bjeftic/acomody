
import { createRouter, createWebHistory } from 'vue-router';
import hostingRoutes from '@/src/views/hosting/router';
import store from '@/store';
import config from '@/config.js';

const isColdStart = config.features.cold_start === true;

// Routes blocked during cold start — guests can only create listings
const COLD_START_BLOCKED_ROUTES = new Set([
    'page-search',
    'accommodation-detail',
    'accommodation-reserve',
    'bookings-list',
    'booking-detail',
    'page-become-a-host',
    'page-calendar',
]);

const routes = [
    {
        path: '/',
        name: 'page-welcome',
        component: isColdStart
            ? () => import('@/src/views/ColdStartLanding.vue')
            : () => import('@/src/views/welcome/Welcome.vue'),
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
        path: '/accommodations/:id',
        name: 'accommodation-detail',
        component: () => import('@/src/views/accommodation/AccommodationDetailPage.vue')
    },
    {
        path: '/accommodations/:id/reserve',
        name: 'accommodation-reserve',
        component: () => import('@/src/views/accommodation/ReservationConfirmPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/bookings',
        name: 'bookings-list',
        component: () => import('@/src/views/bookings/BookingsPage.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/bookings/:id',
        name: 'booking-detail',
        component: () => import('@/src/views/bookings/BookingDetailPage.vue'),
        meta: { requiresAuth: true },
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
        path: '/account',
        name: 'page-account',
        component: () => import('@/src/views/account/AccountView.vue'),
        meta: { requiresAuth: true },
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

    // COLD START: block guest-facing routes, redirect to landing page
    if (isColdStart && COLD_START_BLOCKED_ROUTES.has(to.name)) {
        return next({ name: 'page-welcome', replace: true });
    }

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
                router.push(to.fullPath).finally(() => {
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
