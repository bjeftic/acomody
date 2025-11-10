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
    {
        path: 'listing-all',
        name: 'page-listing-all',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/listings/MyListings.vue'),
    }
];

export default routes;
