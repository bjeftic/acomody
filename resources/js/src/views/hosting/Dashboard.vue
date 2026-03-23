<template>
    <div class="max-w-4xl mx-auto px-4 pt-6 md:py-12">
        <template v-if="hostingLoading">
            <form-skeleton />
        </template>
        <template v-else>
            <!-- Host profile incomplete banner -->
            <div
                v-if="!hostProfileComplete"
                class="flex gap-4 p-5 mb-8 bg-amber-50 border border-amber-200 rounded-xl"
            >
                <div class="flex-shrink-0 text-amber-500 mt-0.5">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-amber-800 mb-1">
                        Complete your host profile to go live
                    </h3>
                    <p class="text-sm text-amber-700 mb-3">
                        Your listing won't be searchable until you add your display name, contact email, and phone number.
                    </p>
                    <router-link
                        :to="{ name: 'page-host-profile' }"
                        class="inline-block px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-xl hover:bg-amber-700 transition"
                    >
                        Complete host profile →
                    </router-link>
                </div>
            </div>

            <!-- Welcome Header -->
            <div class="mb-8">
                <h1
                    class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                >
                    Welcome back, {{ userName }}
                </h1>
                <p class="text-base text-gray-600 dark:text-gray-400">
                    Manage your listings, view your bookings and account
                    details.
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
                        :title="
                            !accommodationDraftExists
                                ? 'Create a new listing'
                                : 'Continue your draft listing'
                        "
                        :description="
                            !accommodationDraftExists
                                ? 'Set up a fresh property listing'
                                : 'Resume editing your saved property listing'
                        "
                        :icon-background="true"
                        @click="$router.push({ name: 'page-listing-create' })"
                    >
                        <template #icon>
                            <HouseIcon v-if="!accommodationDraftExists" />
                            <PenIcon v-else />
                        </template>
                    </action-card>

                    <!-- Create From Existing Card -->
                    <action-card
                        title="Create from an existing listing"
                        description="Duplicate and modify an existing property"
                        :icon-background="true"
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
                     <action-card
                        title="My listings"
                        @click="$router.push({ name: 'page-listings' })"
                    >
                        <template #icon>
                            <ArchiveIcon />
                        </template>
                        <template #badges>
                            <fwb-badge v-if="accommodationDraftWaitingApproval" type="yellow">{{ accommodationDraftStats.waiting_for_approval }}</fwb-badge>
                            <fwb-badge v-if="accommodationDraftPublished">{{ accommodationDraftStats.published }}</fwb-badge>
                        </template>
                    </action-card>

                    <action-card
                        v-if="!isColdStart"
                        title="Calendar"
                        @click="$router.push({ name: 'page-calendar' })"
                    >
                        <template #icon>
                            <CalendarIcon />
                        </template>
                    </action-card>

                    <action-card
                        title="Account settings"
                        @click="$router.push({ name: 'page-host-profile' })"
                    >
                        <template #icon>
                            <SettingsIcon />
                        </template>
                    </action-card>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from "vuex";
import config from "@/config.js";

export default {
    name: "Dashboard",
    computed: {
        ...mapState({
            currentUser: (state) => state.user.currentUser,
        }),
        ...mapState("hosting", ["hostingLoading", "accommodationDraftStats"]),
        ...mapGetters("user", ["hostProfileComplete"]),
        userName() {
            return this.currentUser?.first_name || this.currentUser?.email || "Guest";
        },
        accommodationDraftExists() {
            return this.accommodationDraftStats?.draft > 0 ?? false;
        },
        accommodationDraftWaitingApproval() {
            return this.accommodationDraftStats?.waiting_for_approval > 0 ?? false;
        },
        accommodationDraftPublished() {
            return this.accommodationDraftStats?.published > 0 ?? false;
        },
        isColdStart() {
            return config.features.cold_start === true;
        },
    },
    methods: {
        ...mapActions("hosting", ["loadInitialDashboardData"]),
    },
    mounted() {
        this.loadInitialDashboardData();
    },
};
</script>
