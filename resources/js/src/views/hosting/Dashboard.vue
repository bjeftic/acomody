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
                        {{ $t('profile_incomplete_title') }}
                    </h3>
                    <p class="text-sm text-amber-700 mb-3">
                        {{ $t('profile_incomplete_desc') }}
                    </p>
                    <router-link
                        :to="{ name: 'page-host-profile' }"
                        class="inline-block px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-xl hover:bg-amber-700 transition"
                    >
                        {{ $t('profile_cta') }}
                    </router-link>
                </div>
            </div>

            <!-- Welcome Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
                    {{ $t('welcome_back', { name: userName }) }}
                </h1>
                <p class="text-base text-gray-600 dark:text-gray-400">
                    {{ $t('welcome_desc') }}
                </p>
            </div>

            <!-- Start a new listing Section -->
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('new_listing_section') }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Create New Listing Card -->
                    <action-card
                        :title="!accommodationDraftExists ? $t('create_listing_title') : $t('continue_draft_title')"
                        :description="!accommodationDraftExists ? $t('create_listing_desc') : $t('continue_draft_desc')"
                        :icon-background="true"
                        @click="$router.push({ name: 'page-listing-create' })"
                    >
                        <template #icon>
                            <IconLoader
                                v-if="!accommodationDraftExists"
                                name="HousePlus"
                                :size="20"
                                class="text-primary-600 dark:text-primary-400"
                            />
                            <IconLoader
                                v-else
                                name="PencilLine"
                                :size="20"
                                class="text-primary-600 dark:text-primary-400"
                            />
                        </template>
                    </action-card>

                    <!-- Create From Existing Card -->
                    <action-card
                        :title="$t('copy_listing_title')"
                        :description="$t('copy_listing_desc')"
                        :icon-background="true"
                    >
                        <template #icon>
                            <IconLoader
                                name="Copy"
                                :size="20"
                                class="text-primary-600 dark:text-primary-400"
                            />
                        </template>
                    </action-card>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('quick_links') }}
                </h2>

                <div class="space-y-3">
                    <!-- My Listings Link -->
                    <action-card
                        :title="$t('my_listings')"
                        @click="$router.push({ name: 'page-listings' })"
                    >
                        <template #icon>
                            <IconLoader
                                name="Archive"
                                :size="20"
                                class="text-primary-600 dark:text-primary-400"
                            />
                        </template>
                        <template #badges>
                            <fwb-badge v-if="accommodationDraftWaitingApproval" type="yellow">{{ accommodationDraftStats.waiting_for_approval }}</fwb-badge>
                            <fwb-badge v-if="accommodationDraftPublished">{{ accommodationDraftStats.published }}</fwb-badge>
                        </template>
                    </action-card>

                    <action-card
                        v-if="!isColdStart"
                        :title="$t('calendar')"
                        @click="$router.push({ name: 'page-calendar' })"
                    >
                        <template #icon>
                            <IconLoader
                                name="CalendarDays"
                                :size="20"
                                class="text-primary-600 dark:text-primary-400"
                            />
                        </template>
                    </action-card>

                    <action-card
                        :title="$t('account_settings')"
                        @click="$router.push({ name: 'page-host-profile' })"
                    >
                        <template #icon>
                            <IconLoader
                                name="Settings"
                                :size="20"
                                class="text-primary-600 dark:text-primary-400"
                            />
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
            return this.currentUser?.first_name || this.currentUser?.email || this.$t('guest_fallback');
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

<i18n lang="yaml">
en:
  profile_incomplete_title: Complete your host profile to go live
  profile_incomplete_desc: "Your listing won't be searchable until you add your display name, contact email, and phone number."
  profile_cta: "Complete host profile →"
  welcome_back: "Welcome back, {name}"
  welcome_desc: Manage your listings, view your bookings and account details.
  new_listing_section: Start a new listing
  create_listing_title: Create a new listing
  create_listing_desc: Set up a fresh property listing
  continue_draft_title: Continue your draft listing
  continue_draft_desc: Resume editing your saved property listing
  copy_listing_title: Create from an existing listing
  copy_listing_desc: Duplicate and modify an existing property
  quick_links: Quick links
  my_listings: My listings
  calendar: Calendar
  account_settings: Account settings
  guest_fallback: Guest

sr:
  profile_incomplete_title: Popunite profil domaćina da biste bili vidljivi
  profile_incomplete_desc: Vaš oglas neće biti pretraživ dok ne dodate ime za prikaz, email za kontakt i broj telefona.
  profile_cta: "Popunite profil domaćina →"
  welcome_back: "Dobro došli, {name}"
  welcome_desc: Upravljajte oglasima, pregledajte rezervacije i detalje naloga.
  new_listing_section: Kreirajte novi oglas
  create_listing_title: Kreirajte novi oglas
  create_listing_desc: Postavite novi oglas za nekretninu
  continue_draft_title: Nastavite sa nacrtom oglasa
  continue_draft_desc: Nastavite uređivanje sačuvanog oglasa
  copy_listing_title: Kreirajte iz postojećeg oglasa
  copy_listing_desc: Duplirajte i izmenite postojeću nekretninu
  quick_links: Brze veze
  my_listings: Moji oglasi
  calendar: Kalendar
  account_settings: Podešavanja naloga
  guest_fallback: Gost

hr:
  profile_incomplete_title: Popunite profil domaćina da biste bili vidljivi
  profile_incomplete_desc: Vaš oglas neće biti pretraživ dok ne dodate ime za prikaz, kontakt email i broj telefona.
  profile_cta: "Popunite profil domaćina →"
  welcome_back: "Dobrodošli, {name}"
  welcome_desc: Upravljajte oglasima, pregledajte rezervacije i detalje računa.
  new_listing_section: Stvorite novi oglas
  create_listing_title: Stvorite novi oglas
  create_listing_desc: Postavite novi oglas za nekretninu
  continue_draft_title: Nastavite s nacrtom oglasa
  continue_draft_desc: Nastavite uređivanje spremljenog oglasa
  copy_listing_title: Stvorite iz postojećeg oglasa
  copy_listing_desc: Duplicirajte i izmijenite postojeću nekretninu
  quick_links: Brze veze
  my_listings: Moji oglasi
  calendar: Kalendar
  account_settings: Postavke računa
  guest_fallback: Gost

mk:
  profile_incomplete_title: Пополнете го профилот на домаќин за да бидете видливи
  profile_incomplete_desc: Вашиот оглас нема да биде пребарлив додека не го додадете вашето прикажано име, контакт email и телефонски број.
  profile_cta: "Пополнете го профилот →"
  welcome_back: "Добредојдовте, {name}"
  welcome_desc: Управувајте со огласите, прегледајте ги резервациите и деталите на сметката.
  new_listing_section: Креирајте нов оглас
  create_listing_title: Креирајте нов оглас
  create_listing_desc: Поставете нов оглас за имот
  continue_draft_title: Продолжете со нацртот на огласот
  continue_draft_desc: Продолжете со уредувањето на зачуваниот оглас
  copy_listing_title: Креирајте од постоечки оглас
  copy_listing_desc: Дуплирајте и изменете постоечки имот
  quick_links: Брзи врски
  my_listings: Моите огласи
  calendar: Календар
  account_settings: Поставки на сметката
  guest_fallback: Гостин

sl:
  profile_incomplete_title: Izpolnite profil gostitelja za vidljivost
  profile_incomplete_desc: Vaš oglas ne bo iskljiv, dokler ne dodate prikazno ime, kontaktni e-naslov in telefonsko številko.
  profile_cta: "Izpolnite profil gostitelja →"
  welcome_back: "Dobrodošli, {name}"
  welcome_desc: Upravljajte z oglasi, pregledujte rezervacije in podrobnosti računa.
  new_listing_section: Ustvarite nov oglas
  create_listing_title: Ustvarite nov oglas
  create_listing_desc: Nastavite nov oglas za nepremičnino
  continue_draft_title: Nadaljujte z osnutkom oglasa
  continue_draft_desc: Nadaljujte z urejanjem shranjenega oglasa
  copy_listing_title: Ustvarite iz obstoječega oglasa
  copy_listing_desc: Podvojite in spremenite obstoječo nepremičnino
  quick_links: Hitre povezave
  my_listings: Moji oglasi
  calendar: Koledar
  account_settings: Nastavitve računa
  guest_fallback: Gost
</i18n>
