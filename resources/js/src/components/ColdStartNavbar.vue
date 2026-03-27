<template>
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="w-full px-4 sm:px-0 max-w-7xl mx-auto">
            <div class="flex justify-between items-center h-14">
                <!-- Logo -->
                <router-link :to="{ name: 'page-welcome' }" class="flex items-center gap-2">
                    <img src="/images/acomody.png" alt="Acomody" class="h-12" />
                    <span class="hidden sm:inline-flex text-xs font-medium text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 rounded-full px-2 py-0.5">
                        {{ $t('early_access') }}
                    </span>
                </router-link>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <!-- Language selector -->
                    <BaseDropdown align="end" close-inside>
                        <template #trigger>
                            <BaseButton variant="ghost" size="sm" class="font-medium">
                                {{ currentLanguageCode.toUpperCase() }}
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
                    <template v-if="!isLoggedIn">
                        <BaseButton variant="ghost" size="sm" @click="openLogInModal">
                            {{ $t('login') }}
                        </BaseButton>
                        <BaseButton variant="primary" size="sm" @click="handleCta">
                            <span class="sm:hidden">{{ $t('cta_short') }}</span>
                            <span class="hidden sm:inline">{{ $t('cta_long') }}</span>
                        </BaseButton>
                    </template>
                    <template v-else>
                        <BaseButton variant="primary" size="sm" @click="handleCta">
                            {{ ctaLabel }}
                        </BaseButton>

                        <BaseDropdown align="end" close-inside>
                            <template #trigger="{ isOpen }">
                                <BaseButton variant="ghost" size="sm">
                                    {{ $t('account') }}
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
                                    {{ $t('my_account') }}
                                </router-link>
                                <router-link
                                    v-if="hostingCtaStatus !== 'not_host'"
                                    :to="{ name: 'page-hosting-home' }"
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    {{ $t('dashboard') }}
                                </router-link>
                                <hr class="my-1 border-gray-100 dark:border-gray-700" />
                                <span
                                    class="px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-primary-600 dark:text-primary-400"
                                    @click="logOut"
                                >
                                    {{ $t('logout') }}
                                </span>
                            </nav>
                        </BaseDropdown>
                    </template>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
import { mapActions, mapGetters, mapState } from 'vuex';

export default {
    name: 'ColdStartNavbar',

    computed: {
        ...mapGetters('auth', ['isLoggedIn']),
        ...mapGetters('user', ['hostingCtaStatus']),
        ...mapState('ui', ['languages', 'selectedLanguage']),

        currentLanguageCode() {
            return this.selectedLanguage || this.$i18n.locale || 'en';
        },

        ctaLabel() {
            if (this.hostingCtaStatus === 'not_host') {
                return this.$t('cta_long');
            }
            if (this.hostingCtaStatus === 'continue_listing') {
                return this.$t('cta_continue');
            }
            return this.$t('cta_my_listing');
        },
    },

    methods: {
        ...mapActions(['openModal']),
        ...mapActions('auth', ['logOut']),
        ...mapActions('ui', ['setLanguage']),

        changeLanguage(code) {
            this.setLanguage(code);
        },

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

<i18n lang="yaml">
en:
  early_access: Early Access
  login: Log in
  cta_short: List property
  cta_long: List your property
  cta_continue: Continue listing
  cta_my_listing: My listing
  account: Account
  my_account: My account
  dashboard: Dashboard
  logout: Log out

sr:
  early_access: Rani pristup
  login: Prijavi se
  cta_short: Oglasi nekretninu
  cta_long: Oglasite svoju nekretninu
  cta_continue: Nastavi oglas
  cta_my_listing: Moj oglas
  account: Nalog
  my_account: Moj nalog
  dashboard: Kontrolna tabla
  logout: Odjavi se

hr:
  early_access: Rani pristup
  login: Prijavi se
  cta_short: Oglasi nekretninu
  cta_long: Oglasi svoju nekretninu
  cta_continue: Nastavi oglas
  cta_my_listing: Moj oglas
  account: Račun
  my_account: Moj račun
  dashboard: Nadzorna ploča
  logout: Odjavi se

mk:
  early_access: Ран пристап
  login: Најави се
  cta_short: Огласи имот
  cta_long: Огласете го вашиот имот
  cta_continue: Продолжи оглас
  cta_my_listing: Мој оглас
  account: Сметка
  my_account: Мојата сметка
  dashboard: Контролна табла
  logout: Одјави се

sl:
  early_access: Zgodnji dostop
  login: Prijava
  cta_short: Oglasi nepremičnino
  cta_long: Oglasite svojo nepremičnino
  cta_continue: Nadaljuj oglas
  cta_my_listing: Moj oglas
  account: Račun
  my_account: Moj račun
  dashboard: Nadzorna plošča
  logout: Odjava
</i18n>
