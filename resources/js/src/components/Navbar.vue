<template>
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="w-full px-4 max-w-7xl mx-auto">
            <div class="flex justify-between items-center h-14">
                <!-- Logo -->
                <router-link :to="{ name: 'page-welcome' }" class="flex items-center">
                    <img src="/images/acomody.png" alt="Acomody" class="h-10" />
                </router-link>

                <!-- Desktop Navigation Items -->
                <div class="hidden md:flex items-center gap-2">
                    <!-- Language selector -->
                    <BaseDropdown align="end" close-inside>
                        <template #trigger="{ isOpen }">
                            <BaseButton variant="ghost" size="sm">
                                {{ currentLanguageCode.toUpperCase() }}
                                <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </BaseButton>
                        </template>
                        <nav class="bg-white dark:bg-gray-800 py-1 text-sm rounded-xl text-gray-700 dark:text-gray-200 flex flex-col shadow-dropdown border border-gray-100 dark:border-gray-700">
                            <span
                                v-for="lang in languages"
                                :key="lang.code"
                                class="cursor-pointer px-4 py-2 min-w-32 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between gap-3"
                                @click="changeLanguage(lang.code)"
                            >
                                <span>{{ lang.native }}</span>
                                <span v-if="currentLanguageCode === lang.code" class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                            </span>
                        </nav>
                    </BaseDropdown>

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
                                class="cursor-pointer px-4 py-2 min-w-32 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between gap-3"
                                @click="setCurrency(currency.code); currentCurrency = currency.code"
                            >
                                <span>{{ currency.code }}</span>
                                <span v-if="(currentCurrency || selectedCurrency.code) === currency.code" class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
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
                            {{ $t('nav.become_host') }}
                        </BaseButton>
                        <BaseButton
                            v-else-if="hostingCtaStatus !== 'not_host' && $route.name !== 'page-hosting-home'"
                            variant="ghost"
                            size="sm"
                            @click="$router.push({ name: 'page-hosting-home' })"
                        >
                            {{ $t('nav.hosting') }}
                        </BaseButton>
                    </template>
                    <BaseButton
                        v-else-if="$route.name !== 'page-become-a-host'"
                        variant="ghost"
                        size="sm"
                        @click="$router.push({ name: 'page-become-a-host' })"
                    >
                        {{ $t('nav.become_host') }}
                    </BaseButton>

                    <!-- Notifications -->
                    <notification-dropdown v-if="isLoggedIn" />

                    <!-- Auth buttons or Account dropdown -->
                    <template v-if="!isLoggedIn">
                        <BaseButton variant="secondary" size="sm" @click="openLogInModal">
                            {{ $t('nav.login') }}
                        </BaseButton>
                        <BaseButton variant="primary" size="sm" @click="openSignUpModal">
                            {{ $t('nav.signup') }}
                        </BaseButton>
                    </template>
                    <template v-else>
                        <BaseDropdown align="end" close-inside>
                            <template #trigger="{ isOpen }">
                                <BaseButton variant="ghost" size="sm">
                                    {{ $t('nav.account') }}
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
                                    {{ $t('nav.my_account') }}
                                </router-link>
                                <span class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    {{ $t('nav.dashboard') }}
                                </span>
                                <router-link
                                    :to="{ name: 'bookings-list' }"
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    {{ $t('nav.bookings') }}
                                </router-link>
                                <span class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    {{ $t('nav.wishlists') }}
                                </span>
                                <span class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    {{ $t('nav.reviews') }}
                                </span>
                                <hr class="my-1 border-gray-100 dark:border-gray-700" />
                                <span
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-primary-600 dark:text-primary-400"
                                    @click="logOut"
                                >
                                    {{ $t('nav.logout') }}
                                </span>
                            </nav>
                        </BaseDropdown>
                    </template>
                </div>

                <!-- Mobile: Notifications + Hamburger -->
                <div class="flex md:hidden items-center gap-2">
                    <notification-dropdown v-if="isLoggedIn" />
                    <button
                        class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu Drawer -->
            <div
                v-if="mobileMenuOpen"
                class="md:hidden border-t border-gray-200 dark:border-gray-700 py-3 space-y-1"
                @click="mobileMenuOpen = false"
            >
                <!-- Language selector -->
                <div class="px-2 py-1">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 px-2">{{ $t('nav.language') }}</p>
                    <div class="flex flex-wrap gap-1">
                        <button
                            v-for="lang in languages"
                            :key="lang.code"
                            class="px-3 py-1.5 text-sm rounded-lg border transition"
                            :class="currentLanguageCode === lang.code
                                ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-700 text-primary-700 dark:text-primary-300 font-medium'
                                : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                            @click.stop="changeLanguage(lang.code)"
                        >
                            {{ lang.native }}
                        </button>
                    </div>
                </div>

                <!-- Currency selector -->
                <div class="px-2 py-1">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 px-2">{{ $t('nav.currency') }}</p>
                    <div class="flex flex-wrap gap-1">
                        <button
                            v-for="currency in currencies"
                            :key="currency.code"
                            class="px-3 py-1.5 text-sm rounded-lg border transition"
                            :class="(currentCurrency || selectedCurrency.code) === currency.code
                                ? 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-700 text-primary-700 dark:text-primary-300 font-medium'
                                : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                            @click.stop="setCurrency(currency.code); currentCurrency = currency.code"
                        >
                            {{ currency.code }}
                        </button>
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700 mx-2" />

                <!-- Host CTA -->
                <template v-if="isLoggedIn">
                    <button
                        v-if="hostingCtaStatus === 'not_host' && $route.name !== 'page-become-a-host'"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition"
                        @click="$router.push({ name: 'page-become-a-host' })"
                    >
                        {{ $t('nav.become_host') }}
                    </button>
                    <button
                        v-else-if="hostingCtaStatus !== 'not_host' && $route.name !== 'page-hosting-home'"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition"
                        @click="$router.push({ name: 'page-hosting-home' })"
                    >
                        {{ $t('nav.hosting_dashboard') }}
                    </button>
                </template>
                <button
                    v-else-if="$route.name !== 'page-become-a-host'"
                    class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition"
                    @click="$router.push({ name: 'page-become-a-host' })"
                >
                    {{ $t('nav.become_host') }}
                </button>

                <!-- Auth / Account links -->
                <template v-if="!isLoggedIn">
                    <button
                        class="w-full text-left px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition"
                        @click="openLogInModal"
                    >
                        {{ $t('nav.login') }}
                    </button>
                    <button
                        class="w-full text-left px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition"
                        @click="openSignUpModal"
                    >
                        {{ $t('nav.signup') }}
                    </button>
                </template>
                <template v-else>
                    <router-link
                        :to="{ name: 'page-account' }"
                        class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition"
                    >
                        {{ $t('nav.my_account') }}
                    </router-link>
                    <router-link
                        :to="{ name: 'bookings-list' }"
                        class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition"
                    >
                        {{ $t('nav.bookings') }}
                    </router-link>
                    <button
                        class="w-full text-left px-4 py-3 text-sm text-primary-600 dark:text-primary-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition"
                        @click="logOut"
                    >
                        {{ $t('nav.logout') }}
                    </button>
                </template>
            </div>
        </div>
    </header>
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
            mobileMenuOpen: false,
        };
    },

    computed: {
        ...mapGetters("auth", ["isLoggedIn"]),
        ...mapGetters("user", ["hostingCtaStatus"]),
        ...mapState("ui", ["currencies", "selectedCurrency", "languages", "selectedLanguage"]),

        currentLanguageCode() {
            return this.selectedLanguage || this.$i18n.locale || "en";
        },
    },

    watch: {
        isLoggedIn(loggedIn) {
            if (loggedIn) {
                this.startPolling();
            } else {
                this.stopPolling();
            }
        },
        $route() {
            this.mobileMenuOpen = false;
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
        ...mapActions("ui", ["setCurrency", "setLanguage"]),
        ...mapActions("notifications", ["fetchNotifications"]),

        openLogInModal() {
            this.openModal({ modalName: "logInModal" });
        },

        openSignUpModal() {
            this.openModal({ modalName: "signUpModal" });
        },

        changeLanguage(code) {
            this.setLanguage(code);
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
