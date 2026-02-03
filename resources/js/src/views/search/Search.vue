<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Main Content -->
        <div class="flex">
            <!-- Results Section -->
            <main class="flex-1">
                <!-- Results Header -->
                <div
                    class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4"
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
import { mapActions, mapState, mapGetters } from "vuex";
import SearchBar from "@/src/components/common/searchBar/SearchBar.vue";
import SearchResults from "./components/SearchResults.vue";
import { searchConfig } from "./config/searchConfig";
import { filtersConfig } from "./config/filtersConfig";

export default {
    name: "SearchPage",
    components: {
        SearchBar,
        SearchResults,
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
            isMapSearch: (state) => state.isMapSearch,
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
            "setIsMapSearch",
            "parseURLParams",
            "updateFiltersInURL",
            "handleMapBoundsChanged",
            "updateFetchingPage",
            "updateSearchParamsInURL",
            "resetPaginationAndSearch",
            "updatePageInURL",
            "getFilters",
        ]),

        handleCardHover(cardId) {
            this.hoveredCardId = cardId;
        },

        handleCardClick(accommodation) {
            this.$router.push(`/accommodation/${accommodation.id}`);
        },

        handleResize() {
            this.isMobile = window.innerWidth < searchConfig.breakpoints.tablet;
        },
    },
};
</script>
