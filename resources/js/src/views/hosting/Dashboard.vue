<template>
    <div class="max-w-4xl mx-auto pb-12">
        <template v-if="hostingLoading">
            <form-skeleton />
        </template>
        <template v-else>
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
                        title="My bookings"
                    >
                        <template #icon>
                            <CalendarIcon />
                        </template>
                    </action-card>

                    <action-card
                        title="Account settings"
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
import { mapState, mapActions } from "vuex";

export default {
    name: "Dashboard",
    computed: {
        ...mapState({
            currentUser: (state) => state.user.currentUser,
        }),
        ...mapState("hosting", ["hostingLoading", "accommodationDraftStats"]),
        userName() {
            return this.currentUser?.first_name || "Guest";
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
    },
    methods: {
        ...mapActions("hosting", ["loadInitialDashboardData"]),
    },
    mounted() {
        this.loadInitialDashboardData();
    },
};
</script>
