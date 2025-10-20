const routes = [
    {
        path: 'dashboard',
        name: 'page-dashboard',
        component: () => import('@/src/views/hosting/Dashboard.vue'),
    },
    {
        path: 'listing-create',
        name: 'page-listing-create',
        component: () => import('@/src/views/hosting/listingCreate/ListingCreate.vue'),
    },
];

export default routes;
