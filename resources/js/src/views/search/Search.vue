<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Main Content -->
        <div class="flex">
            <!-- Results Section -->
            <main class="flex-1">
                <!-- Results Header -->
                <div
                    class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 py-2 md:py-4"
                >
                    <div class="flex items-center justify-between">
                        <search-bar
                            :initial-location-id="locationFromRoute?.id"
                            :initial-location-name="locationFromRoute?.name"
                            :initial-check-in="checkInFromRoute"
                            :initial-check-out="checkOutFromRoute"
                            :initial-adults="adultsFromRoute"
                            :initial-children="childrenFromRoute"
                            :initial-infants="infantsFromRoute"
                            :initial-map-bounds="boundsFromRoute"
                            @search="
                                handleSearch({
                                    route: this.$route,
                                    router: this.$router,
                                    searchData: $event,
                                })
                            "
                            class="mx-auto"
                        ></search-bar>
                    </div>
                </div>

                <!-- Results Grid/Map -->
                <div class="relative">
                    <search-results
                        :loading="loading"
                        :hovered-card-id="hoveredCardId"
                        :current-map-bounds="bounds"
                        @map-bounds-changed="
                            handleMapBoundsChanged({
                                route: this.$route,
                                router: this.$router,
                                mapBounds: $event,
                            })
                        "
                        @card-hover="handleCardHover"
                        @card-click="handleCardClick"
                        @clear-filters="clearAllFilters"
                        @page-changed="
                            updateFetchingPage({
                                route: this.$route,
                                router: this.$router,
                                newPage: $event,
                            })
                        "
                    />
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { mapActions, mapState, mapGetters } from "vuex";
import { useRoute } from "vue-router";
import SearchBar from "@/src/components/common/searchBar/SearchBar.vue";
import SearchResults from "@/src/views/search/components/SearchResults.vue";
import { searchConfig } from "./config/searchConfig";
import { useSeoHead } from "@/src/composables/useSeoHead";

export default {
    name: "SearchPage",
    components: {
        SearchBar,
        SearchResults,
    },
    setup() {
        const route = useRoute();
        const { t } = useI18n();

        const title = computed(() => {
            const location = route.query.locationName;
            return location
                ? t('seo.title_location', { location })
                : t('seo.title_default');
        });

        const description = computed(() => {
            const location = route.query.locationName;
            return location
                ? t('seo.description_location', { location })
                : t('seo.description_default');
        });

        useSeoHead({ title, description });
    },
    directives: {
        clickOutside: {
            mounted(el, binding) {
                el.clickOutsideEvent = function (event) {
                    if (!(el === event.target || el.contains(event.target))) {
                        binding.value(event);
                    }
                };
                document.addEventListener("click", el.clickOutsideEvent);
            },
            unmounted(el) {
                document.removeEventListener("click", el.clickOutsideEvent);
            },
        },
    },
    data() {
        return {
            config: searchConfig,
            searchParams: { ...searchConfig.defaults },
            hoveredCardId: null,
            isMobile: window.innerWidth < searchConfig.breakpoints.tablet,
            bounds: null,
        };
    },
    computed: {
        ...mapState("search", {
            accommodations: (state) => state.accommodations,
            totalAccommodationsFound: (state) => state.totalAccommodationsFound,
            loading: (state) => state.loading,
        }),
        ...mapState("ui", ["selectedCurrency"]),
        ...mapGetters("search", {
            accommodationPricesFilters: "accommodationPricesFilters",
            filters: "accommodationFilters",
        }),
        locationFromRoute() {
            const locationId = this.$route.query.locationId;
            const locationName = this.$route.query.locationName;

            if (!locationId && !locationName) {
                return null;
            }

            return {
                id: locationId || null,
                name: locationName || "",
            };
        },
        checkInFromRoute() {
            return this.$route.query.checkIn || null;
        },
        checkOutFromRoute() {
            return this.$route.query.checkOut || null;
        },
        adultsFromRoute() {
            return parseInt(this.$route.query.adults) || 2;
        },
        childrenFromRoute() {
            return parseInt(this.$route.query.children) || 0;
        },
        infantsFromRoute() {
            return parseInt(this.$route.query.infants) || 0;
        },
        boundsFromRoute() {
            return (
                {
                    northEast: {
                        lat: this.$route.query.ne_lat,
                        lng: this.$route.query.ne_lng,
                    },
                    southWest: {
                        lat: this.$route.query.sw_lat,
                        lng: this.$route.query.sw_lng,
                    },
                } || null
            );
        },
    },
    async mounted() {
        await this.parseURLParams(this.$route.query);

        // Ensure page is always in URL
        if (!this.$route.query.page) {
            this.updatePageInURL({ route: this.$route, router: this.$router });
        }

        await this.getFilters();
        await this.performSearch();

        window.addEventListener("resize", this.handleResize);
    },
    beforeDestroy() {
        window.removeEventListener("resize", this.handleResize);
    },
    methods: {
        ...mapActions("search", [
            "performSearch",
            "handleSearch",
            "parseURLParams",
            "updateFiltersInURL",
            "handleMapBoundsChanged",
            "updateFetchingPage",
            "updateSearchParamsInURL",
            "resetPaginationAndSearch",
            "updatePageInURL",
            "getFilters",
        ]),
        ...mapActions("accommodation", ["clearAccommodation"]),

        handleCardHover(cardId) {
            this.hoveredCardId = cardId;
        },

        handleCardClick(accommodation) {
            this.clearAccommodation();
            const query = {
                checkIn: this.checkInFromRoute || undefined,
                checkOut: this.checkOutFromRoute || undefined,
                adults: this.adultsFromRoute || undefined,
                children: this.childrenFromRoute || undefined,
                infants: this.infantsFromRoute || undefined,
            };

            this.$router.push({
                path: `/accommodations/${accommodation.id}`,
                query,
            });
        },

        handleResize() {
            this.isMobile = window.innerWidth < searchConfig.breakpoints.tablet;
        },
    },
};
</script>

<i18n lang="yaml">
en:
  seo:
    title_location: Accommodation in {location}
    title_default: Search accommodation
    description_location: Find apartments, rooms and accommodation in {location}. Check availability and book online.
    description_default: Search apartments and rooms on the Acomody platform. Short-term rentals in Serbia at the best prices.
sr:
  seo:
    title_location: Smeštaj u {location}
    title_default: Pretraga smeštaja
    description_location: Pronađite apartmane, sobe i smeštaj u {location}. Proverite dostupnost i rezervišite online.
    description_default: Pretražite apartmane i sobe na Acomody platformi. Kratkoročni najam u Srbiji po najboljim cenama.
hr:
  seo:
    title_location: Smještaj u {location}
    title_default: Pretraga smještaja
    description_location: Pronađite apartmane, sobe i smještaj u {location}. Provjerite dostupnost i rezervirajte online.
    description_default: Pretražite apartmane i sobe na Acomody platformi. Kratkoročni najam u Srbiji po najboljim cijenama.
mk:
  seo:
    title_location: Сместување во {location}
    title_default: Пребарување сместување
    description_location: Најдете станови, соби и сместување во {location}. Проверете достапност и резервирајте онлајн.
    description_default: Пребарувајте станови и соби на Acomody платформата. Краткорочен закуп во Србија по најдобри цени.
sl:
  seo:
    title_location: Nastanitev v {location}
    title_default: Iskanje nastanitve
    description_location: Poiščite apartmaje, sobe in nastanitev v {location}. Preverite razpoložljivost in rezervirajte online.
    description_default: Iščite apartmaje in sobe na platformi Acomody. Kratkoročni najem v Srbiji po najboljših cenah.
</i18n>
