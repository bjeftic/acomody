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
    {
        path: 'drafts/:draftId/edit',
        name: 'page-draft-edit',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/listings/EditAccommodationDraft.vue'),
    },
    {
        path: 'calendar',
        name: 'page-calendar',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/Calendar.vue'),
    },
    {
        path: 'bookings',
        name: 'page-host-bookings',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/bookings/HostBookingsPage.vue'),
    },
    {
        path: 'bookings/:bookingId',
        name: 'page-host-booking-show',
        meta: {
            requiresAuth: true,
        },
        component: () => import('@/src/views/hosting/bookings/HostBookingDetailPage.vue'),
    },
];

export default routes;
