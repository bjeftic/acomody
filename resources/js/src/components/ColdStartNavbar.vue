<template>
    <fwb-navbar class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="w-full px-4 sm:px-0 max-w-7xl mx-auto">
            <div class="flex justify-between items-center h-14">
                <!-- Logo -->
                <router-link :to="{ name: 'page-welcome' }" class="flex items-center gap-2">
                    <img src="/images/acomody.png" alt="Acomody" class="h-12" />
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 rounded-full px-2 py-0.5">
                        Early Access
                    </span>
                </router-link>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <template v-if="!isLoggedIn">
                        <BaseButton variant="ghost" size="sm" @click="openLogInModal">
                            Log in
                        </BaseButton>
                        <BaseButton variant="primary" size="sm" @click="handleCta">
                            List your property
                        </BaseButton>
                    </template>
                    <template v-else>
                        <BaseButton variant="primary" size="sm" @click="handleCta">
                            {{ ctaLabel }}
                        </BaseButton>

                        <BaseDropdown align="end" close-inside>
                            <template #trigger="{ isOpen }">
                                <BaseButton variant="ghost" size="sm">
                                    Account
                                    <svg
                                        class="w-3.5 h-3.5 transition-transform"
                                        :class="{ 'rotate-180': isOpen }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
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
                                <router-link
                                    v-if="hostingCtaStatus !== 'not_host'"
                                    :to="{ name: 'page-hosting-home' }"
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Dashboard
                                </router-link>
                                <hr class="my-1 border-gray-100 dark:border-gray-700" />
                                <span
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-primary-600 dark:text-primary-400"
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
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'ColdStartNavbar',

    computed: {
        ...mapGetters('auth', ['isLoggedIn']),
        ...mapGetters('user', ['hostingCtaStatus']),

        ctaLabel() {
            if (this.hostingCtaStatus === 'not_host') {
                return 'List your property';
            }
            if (this.hostingCtaStatus === 'continue_listing') {
                return 'Continue listing';
            }
            return 'My listing';
        },
    },

    methods: {
        ...mapActions(['openModal']),
        ...mapActions('auth', ['logOut']),

        openLogInModal() {
            this.openModal({ modalName: 'logInModal' });
        },

        handleCta() {
            if (!this.isLoggedIn) {
                this.openModal({
                    modalName: 'signUpModal',
                    options: { redirectTo: '/hosting/profile?next=listing-create' },
                });
                return;
            }

            if (this.hostingCtaStatus === 'not_host') {
                this.$router.push({ name: 'page-host-profile', query: { next: 'listing-create' } });
            } else if (this.hostingCtaStatus === 'continue_listing') {
                this.$router.push({ name: 'page-listing-create' });
            } else {
                this.$router.push({ name: 'page-hosting-home' });
            }
        },
    },
};
</script>
