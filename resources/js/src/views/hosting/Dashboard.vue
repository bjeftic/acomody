<template>
    <div class="max-w-4xl mx-auto pb-12">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1
                class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
            >
                Welcome back, {{ userName }}
            </h1>
            <p class="text-base text-gray-600 dark:text-gray-400">
                Manage your listings, view your bookings and account details.
            </p>
        </div>

        <!-- Start a new listing Section -->
        <div class="mb-10">
            <h2
                class="text-xl font-semibold text-gray-900 dark:text-white mb-4"
            >
                Start a new listing
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Create New Listing Card -->
                 <action-card
                    title="Create a new listing"
                    description="Set up a fresh property listing"
                    @click="$router.push({ name: 'page-listing-create' })"
                >
                    <template #icon>
                        <HouseIcon />
                    </template>
                </action-card>

                <!-- Create From Existing Card -->
                 <action-card
                    title="Create from an existing listing"
                    description="Duplicate and modify an existing property"
                >
                    <template #icon>
                        <CopyIcon />
                    </template>
                </action-card>
            </div>
        </div>

        <!-- Quick Links Section -->
        <div>
            <h2
                class="text-xl font-semibold text-gray-900 dark:text-white mb-4"
            >
                Quick links
            </h2>

            <div class="space-y-3">
                <!-- My Listings Link -->
                <router-link
                    class="group flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600"
                >
                    <div class="flex items-center space-x-3">
                        <svg
                            class="w-5 h-5 text-gray-600 dark:text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>
                        <span
                            class="text-base font-medium text-gray-900 dark:text-white"
                        >
                            My listings
                        </span>
                    </div>
                    <svg
                        class="w-5 h-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </router-link>

                <!-- My Bookings Link -->
                <router-link
                    class="group flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600"
                >
                    <div class="flex items-center space-x-3">
                        <svg
                            class="w-5 h-5 text-gray-600 dark:text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <span
                            class="text-base font-medium text-gray-900 dark:text-white"
                        >
                            My bookings
                        </span>
                    </div>
                    <svg
                        class="w-5 h-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </router-link>

                <!-- Account Settings Link -->
                <router-link
                    class="group flex items-center justify-between p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600"
                >
                    <div class="flex items-center space-x-3">
                        <svg
                            class="w-5 h-5 text-gray-600 dark:text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span
                            class="text-base font-medium text-gray-900 dark:text-white"
                        >
                            Account settings
                        </span>
                    </div>
                    <svg
                        class="w-5 h-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";

export default {
    name: "ListingCreate",
    computed: {
        ...mapState({
            currentUser: (state) => state.user.currentUser,
        }),
        userName() {
            return this.currentUser?.first_name || "Guest";
        },
    },
};
</script>
