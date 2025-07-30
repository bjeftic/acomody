import { createRouter, createWebHistory } from 'vue-router';
// import settingsRoutes from '@/pages/settings/router/index.js';
// import guidebooksRoutes from '@/pages/guidebooks/router/index.js';
// import store from '@/store';

const routes = [
    {
        path: '/',
        name: 'page-welcome',
        component: () => import('@/views/Welcome.vue'),
        // children: [
        //     {
        //         path: 'home',
        //         name: 'page-dashboard-home',
        //         component: () => import("@/pages/home/Home.vue"),
        //     },
        //     ...settingsRoutes,
        //     ...guidebooksRoutes,
        // ],
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'page-not-found',
        component: () => import('@/views/Page404.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { x: 0, y: 0 };
        }
    },
});

router.beforeEach((to, from, next) => {
    next();
  });

export default router;
