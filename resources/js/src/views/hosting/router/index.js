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
        path: 'listings',
        name: 'page-listings',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/listings/MyListings.vue'),
    },
    {
        path: 'listings/:accommodationId',
        name: 'page-listings-show',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/listings/ShowListing.vue'),
    },
];

export default routes;
