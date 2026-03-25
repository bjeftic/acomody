<template>
    <div class="max-w-4xl mx-auto px-4 pt-6 md:py-12">
        <template v-if="myListingsLoading">
            <form-skeleton />
        </template>
        <template v-else>
            <div class="mb-8">
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
                    {{ $t('title') }}
                </h1>
                <p class="text-base text-gray-600 dark:text-gray-400">
                    {{ $t('subtitle') }}
                </p>
            </div>
            <div v-if="myRejectedDrafts.length > 0" class="mb-10">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('rejected_section') }}
                </h2>
                <div class="space-y-3">
                    <action-card
                        v-for="draft in myRejectedDrafts"
                        :key="draft.id"
                        :title="draft.data.title || $t('untitled_listing')"
                        :subtitle="$t('rejected_subtitle')"
                        @click="$router.push({ name: 'page-draft-edit', params: { draftId: draft.id } })"
                    >
                        <template #icon>
                            <HouseIcon />
                        </template>
                    </action-card>
                </div>
            </div>
            <div v-if="myAccommodationDrafts.length > 0" class="mb-10">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('waiting_section') }}
                </h2>
                <div class="space-y-3">
                    <action-card
                        v-for="accommodationDraft in myAccommodationDrafts"
                        :key="accommodationDraft.id"
                        :title="accommodationDraft.data.title || $t('untitled_listing')"
                        :disabled="true"
                    >
                        <template #icon>
                            <HouseIcon />
                        </template>
                    </action-card>
                </div>
            </div>
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $t('listings_section') }}
                </h2>
                <div class="space-y-3">
                    <!-- My Listings Link -->
                    <action-card
                        v-for="accommodation in accommodations"
                        :key="accommodation.id"
                        :title="accommodation.title || $t('untitled_listing')"
                        @click="$router.push({ name: 'page-listings-show', params: { accommodationId: accommodation.id } })"
                    >
                        <template #icon>
                            <HouseIcon />
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
    name: "MyListings",
    computed: {
        ...mapState("hosting/listings", ["myAccommodationDrafts", "myRejectedDrafts", "accommodations", "myListingsLoading"]),
    },
    methods: {
        ...mapActions("hosting/listings", ["loadInitialMyListingsData"]),
    },
    created() {
        this.loadInitialMyListingsData();
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

<i18n lang="yaml">
en:
  title: My Listings
  subtitle: Manage your property listings here.
  rejected_section: Rejected
  rejected_subtitle: "Rejected — click to review feedback and edit"
  waiting_section: Waiting for approval
  listings_section: Listings
  untitled_listing: Untitled Listing

sr:
  title: Moji oglasi
  subtitle: Upravljajte oglasima za vaše nekretnine ovde.
  rejected_section: Odbijeni
  rejected_subtitle: "Odbijen — kliknite da pregledate povratne informacije i uredite"
  waiting_section: Čeka na odobrenje
  listings_section: Oglasi
  untitled_listing: Oglas bez naslova

hr:
  title: Moji oglasi
  subtitle: Upravljajte oglasima za vaše nekretnine ovdje.
  rejected_section: Odbijeni
  rejected_subtitle: "Odbijen — kliknite za pregled povratnih informacija i uređivanje"
  waiting_section: Čeka na odobrenje
  listings_section: Oglasi
  untitled_listing: Oglas bez naslova

mk:
  title: Моите огласи
  subtitle: Управувајте со огласите за вашите имоти овде.
  rejected_section: Одбиени
  rejected_subtitle: "Одбиен — кликнете за преглед на повратни информации и уредување"
  waiting_section: Чека на одобрение
  listings_section: Огласи
  untitled_listing: Оглас без наслов

sl:
  title: Moji oglasi
  subtitle: Tukaj upravljajte z oglasi za svoje nepremičnine.
  rejected_section: Zavrnjeni
  rejected_subtitle: "Zavrnjeno — kliknite za pregled povratnih informacij in urejanje"
  waiting_section: Čaka na odobritev
  listings_section: Oglasi
  untitled_listing: Oglas brez naslova
</i18n>
