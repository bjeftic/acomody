<template>
    <fwb-navbar class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="w-full px-4 sm:px-0 max-w-7xl mx-auto">
            <div class="flex justify-between items-center h-14">
                <!-- Logo -->
                <router-link :to="{ name: 'page-welcome' }" class="flex items-center">
                    <span class="text-xl font-bold text-primary-600 dark:text-primary-400 tracking-tight">
                        Acomody
                    </span>
                </router-link>

                <!-- Navigation Items -->
                <div class="flex items-center gap-2">
                    <!-- Currency selector -->
                    <BaseDropdown align="end" close-inside>
                        <template #trigger="{ isOpen }">
                            <BaseButton variant="ghost" size="sm">
                                {{ currentCurrency || selectedCurrency.code }}
                                <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </BaseButton>
                        </template>
                        <nav class="bg-white dark:bg-gray-800 py-1 text-sm rounded-xl text-gray-700 dark:text-gray-200 flex flex-col shadow-dropdown border border-gray-100 dark:border-gray-700">
                            <span
                                v-for="currency in currencies"
                                :key="currency.code"
                                class="cursor-pointer px-4 py-2 min-w-24 hover:bg-gray-50 dark:hover:bg-gray-700"
                                @click="setCurrency(currency.code); currentCurrency = currency.code"
                            >
                                {{ currency.code }}
                            </span>
                        </nav>
                    </BaseDropdown>

                    <!-- Host CTA -->
                    <template v-if="isLoggedIn">
                        <BaseButton
                            v-if="hostingCtaStatus === 'not_host' && $route.name !== 'page-become-a-host'"
                            variant="ghost"
                            size="sm"
                            @click="$router.push({ name: 'page-become-a-host' })"
                        >
                            Become a host
                        </BaseButton>
                        <BaseButton
                            v-else-if="hostingCtaStatus !== 'not_host' && $route.name !== 'page-hosting-home'"
                            variant="ghost"
                            size="sm"
                            @click="$router.push({ name: 'page-hosting-home' })"
                        >
                            Hosting
                        </BaseButton>
                    </template>
                    <BaseButton
                        v-else-if="$route.name !== 'page-become-a-host'"
                        variant="ghost"
                        size="sm"
                        @click="$router.push({ name: 'page-become-a-host' })"
                    >
                        Become a host
                    </BaseButton>

                    <!-- Notifications -->
                    <notification-dropdown v-if="isLoggedIn" />

                    <!-- Auth buttons or Account dropdown -->
                    <template v-if="!isLoggedIn">
                        <BaseButton variant="secondary" size="sm" @click="openLogInModal">
                            Log in
                        </BaseButton>
                        <BaseButton variant="primary" size="sm" @click="openSignUpModal">
                            Sign up
                        </BaseButton>
                    </template>
                    <template v-else>
                        <BaseDropdown align="end" close-inside>
                            <template #trigger="{ isOpen }">
                                <BaseButton variant="ghost" size="sm">
                                    Account
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </BaseButton>
                            </template>
                            <nav class="bg-white dark:bg-gray-800 py-1 text-sm rounded-xl text-gray-700 dark:text-gray-200 flex flex-col shadow-dropdown border border-gray-100 dark:border-gray-700">
                                <router-link
                                    :to="{ name: 'page-account' }"
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    My account
                                </router-link>
                                <span class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    Dashboard
                                </span>
                                <router-link
                                    :to="{ name: 'bookings-list' }"
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Bookings
                                </router-link>
                                <span class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    Wishlists
                                </span>
                                <span class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    Reviews
                                </span>
                                <hr class="my-1 border-gray-100 dark:border-gray-700" />
                                <span
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-rose-600 dark:text-rose-400"
                                    @click="logOut"
                                >
                                    Log out
                                </span>
                            </nav>
                        </BaseDropdown>
                    </template>
                </div>
            </div>
        </div>
    </fwb-navbar>
</template>

<script>
import { mapActions, mapGetters, mapState } from "vuex";
import NotificationDropdown from "@/src/components/NotificationDropdown.vue";

const POLL_INTERVAL_MS = 60_000;

export default {
    name: "Navbar",

    components: { NotificationDropdown },

    data() {
        return {
            currentCurrency: null,
            pollTimer: null,
        };
    },

    computed: {
        ...mapGetters("auth", ["isLoggedIn"]),
        ...mapGetters("user", ["hostingCtaStatus"]),
        ...mapState("ui", ["currencies", "selectedCurrency"]),
    },

    watch: {
        isLoggedIn(loggedIn) {
            if (loggedIn) {
                this.startPolling();
            } else {
                this.stopPolling();
            }
        },
    },

    mounted() {
        if (this.isLoggedIn) {
            this.startPolling();
        }
    },

    beforeUnmount() {
        this.stopPolling();
    },

    methods: {
        ...mapActions(["openModal"]),
        ...mapActions("auth", ["logOut"]),
        ...mapActions("ui", ["setCurrency"]),
        ...mapActions("notifications", ["fetchNotifications"]),

        openLogInModal() {
            this.openModal({ modalName: "logInModal" });
        },

        openSignUpModal() {
            this.openModal({ modalName: "signUpModal" });
        },

        startPolling() {
            this.fetchNotifications();
            this.pollTimer = setInterval(this.fetchNotifications, POLL_INTERVAL_MS);
        },

        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },
    },
};
</script>
