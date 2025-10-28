import auth from '@/middleware/auth';

const routes = [
    {
        path: 'dashboard',
        name: 'page-dashboard',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/Dashboard.vue'),
    },
    {
        path: 'listing-create',
        name: 'page-listing-create',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/createAccommodation/CreateAccommodation.vue'),
    },
];

export default routes;
