<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Main Content -->
        <div class="flex">
            <!-- Filters Sidebar (Desktop) -->
            <aside
                v-if="!isMobile"
                class="w-80 flex-shrink-0 h-[calc(100vh-80px)] sticky top-20 overflow-y-auto border-r border-gray-200 dark:border-gray-800"
            >
                <search-filters
                    :filters="filters"
                    :active-filters="activeFilters"
                    :active-filters-count="activeFiltersCount"
                    @update:filters="handleFiltersUpdate"
                    @clear-all="clearAllFilters"
                />
            </aside>

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
                            @search="handleSearch"
                            class="mx-auto"
                        ></search-bar>

                        <!-- View Options -->
                        <div class="flex items-center space-x-4">
                            <!-- Sort Dropdown -->
                            <div class="relative">
                                <button
                                    @click="showSortMenu = !showSortMenu"
                                    class="flex items-center space-x-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                                >
                                    <span>{{ currentSortOption.name }}</span>
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </button>

                                <!-- Sort Menu -->
                                <div
                                    v-if="showSortMenu"
                                    v-click-outside="
                                        () => (showSortMenu = false)
                                    "
                                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-20"
                                >
                                    <button
                                        v-for="option in config.sortOptions"
                                        :key="option.id"
                                        @click="handleSortChange(option.id)"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="
                                            sortBy === option.id
                                                ? 'bg-gray-50 dark:bg-gray-700'
                                                : ''
                                        "
                                    >
                                        <span class="mr-2">{{
                                            option.icon
                                        }}</span>
                                        {{ option.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- View Mode Toggle -->
                            <div
                                class="flex items-center space-x-2 border border-gray-300 dark:border-gray-700 rounded-lg p-1"
                            >
                                <button
                                    @click="viewMode = 'grid'"
                                    :class="[
                                        'p-2 rounded transition-colors',
                                        viewMode === 'grid'
                                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800',
                                    ]"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="viewMode = 'map'"
                                    :class="[
                                        'p-2 rounded transition-colors',
                                        viewMode === 'map'
                                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800',
                                    ]"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Grid/Map -->
                <div class="relative">
                    <div
                        v-if="totalAccommodationsFound > 0"
                        class="ml-6 mt-6 text-base font-semibold text-gray-900 dark:text-white"
                    >
                        {{ totalAccommodationsFound }} results
                    </div>
                    <search-results
                        :results="accommodations"
                        :loading="loading"
                        :view-mode="viewMode"
                        :hovered-card-id="hoveredCardId"
                        :infinite-id="infiniteId"
                        @map-bounds-changed="handleMapBoundsChanged"
                        @card-hover="handleCardHover"
                        @card-click="handleCardClick"
                        @load-more="loadMore"
                        @clear-filters="clearAllFilters"
                        @retry="retryLoadMore"
                    />

                    <!-- Map Toggle Button (Mobile) -->
                    <button
                        v-if="isMobile && viewMode !== 'map'"
                        @click="viewMode = 'map'"
                        class="fixed bottom-6 left-1/2 -translate-x-1/2 px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full shadow-lg font-medium z-30 flex items-center space-x-2"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                            />
                        </svg>
                        <span>Show map</span>
                    </button>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import { mapActions, mapState, mapGetters } from "vuex";
import moment from "moment";
import SearchBar from "@/src/components/common/searchBar/SearchBar.vue";
import SearchFilters from "./components/SearchFilters.vue";
import SearchResults from "./components/SearchResults.vue";
import FilterModal from "./components/FilterModal.vue";
import { searchConfig } from "./config/searchConfig";
import { filtersConfig } from "./config/filtersConfig";

export default {
    name: "SearchPage",
    components: {
        SearchBar,
        SearchFilters,
        SearchResults,
        FilterModal,
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
            geoLocation: null,
            activeFilters: {
                priceRange: { min: null, max: null },
                accommodation_type: null,
                accommodation_occupation: null,
                roomTypes: [],
                amenities: [],
                bedrooms: { min: 0, max: null },
                beds: { min: 0, max: null },
                bathrooms: { min: 0, max: null },
                bookingOptions: [],
                instantBook: false,
                selfCheckIn: false,
                superhost: false,
                hostLanguages: [],
                accessibility: [],
                houseRules: [],
                cancellationPolicy: null,
            },
            loading: false,
            viewMode: searchConfig.defaultViewMode,
            sortBy: "recommended",
            page: 1,
            infiniteId: +new Date(),
            hoveredCardId: null,
            showFilters: false,
            showSortMenu: false,
            isMobile: window.innerWidth < searchConfig.breakpoints.tablet,
            lastInfiniteState: null,
        };
    },
    computed: {
        ...mapState("search", {
            accommodations: (state) => state.accommodations,
            totalAccommodationsFound: (state) => state.totalAccommodationsFound,
        }),
        ...mapState("ui", ["selectedCurrency"]),
        ...mapGetters("search", {
            accommodationPricesFilters: "accommodationPricesFilters",
            filters: "accommodationFilters",
        }),
        dateRangeText() {
            if (!this.searchParams.checkIn || !this.searchParams.checkOut) {
                return "Add dates";
            }

            const checkIn = moment(this.searchParams.checkIn);
            const checkOut = moment(this.searchParams.checkOut);
            const nights = checkOut.diff(checkIn, "days");

            return `${checkIn.format("MMM D")} - ${checkOut.format(
                "MMM D",
            )} (${nights} nights)`;
        },
        activeFiltersCount() {
            let count = 0;
            if (this.activeFilters.accommodation_type) count++;
            if (
                this.activeFilters.priceRange.min > 0 ||
                this.activeFilters.priceRange.max < 1000
            )
                count++;
            if (this.activeFilters.amenities.length) count++;
            if (this.activeFilters.bedrooms.min > 0) count++;
            if (this.activeFilters.instantBook) count++;
            return count;
        },
        currentSortOption() {
            return (
                this.config.sortOptions.find((opt) => opt.id === this.sortBy) ||
                this.config.sortOptions[0]
            );
        },
        guestCountText() {
            const { adults, children, infants, pets } =
                this.searchParams.guests;
            const parts = [];
            if (adults) parts.push(`${adults} adult${adults > 1 ? "s" : ""}`);
            if (children)
                parts.push(`${children} child${children > 1 ? "ren" : ""}`);
            if (infants)
                parts.push(`${infants} infant${infants > 1 ? "s" : ""}`);
            if (pets) parts.push(`${pets} pet${pets > 1 ? "s" : ""}`);
            return parts.join(", ") || "1 guest";
        },
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
        pricesFromRoute() {
            return {
                min:
                    parseInt(
                        this.$route.query[
                            "price_min_" + this.selectedCurrency.code
                        ],
                    ) || null,
                max:
                    parseInt(
                        this.$route.query[
                            "price_max_" + this.selectedCurrency.code
                        ],
                    ) || null,
            };
        },
    },
    async mounted() {
        await this.parseURLParams();
        this.performSearch();

        window.addEventListener("resize", this.handleResize);
    },
    beforeDestroy() {
        window.removeEventListener("resize", this.handleResize);
    },
    methods: {
        ...mapActions("search", ["searchAccommodations"]),

        async performSearch() {
            this.loading = true;

            try {
                const filtersToSend = { ...this.activeFilters };
                delete filtersToSend.priceRange;
                const currencyCode = this.selectedCurrency.code;
                filtersToSend[`priceRange_${currencyCode}`] = {
                    min: this.activeFilters.priceRange?.min ?? null,
                    max: this.activeFilters.priceRange?.max ?? null,
                };
                const response = await this.searchAccommodations({
                    ...this.searchParams,
                    ...this.geoLocation,
                    ...filtersToSend,
                    sortBy: this.sortBy,
                    page: this.page,
                    perPage: 12,
                });

                const data = response.hits || response.data || response;
                const found = response.found || response.total || 0;

                return {
                    data: data || [],
                    found,
                };
            } catch (error) {
                console.error("Search error:", error);
                return {
                    data: [],
                    found: 0,
                };
            } finally {
                this.loading = false;
            }
        },

        handleSearchUpdate(newParams) {
            this.searchParams = { ...this.searchParams, ...newParams };
            this.updateSearchParamsInURL();
            this.resetPaginationAndSearch();
        },

        handleFiltersUpdate(newFilters) {
            this.activeFilters = { ...this.activeFilters, ...newFilters };
            this.updateFiltersInURL();
            this.resetPaginationAndSearch();
        },

        handleSortChange(newSortBy) {
            this.sortBy = newSortBy;
            this.showSortMenu = false;
            this.resetPaginationAndSearch();
        },

        clearAllFilters() {
            this.activeFilters = {
                ...filtersConfig.defaults,
                priceRange: {
                    min: this.accommodationPricesFilters.min,
                    max: this.accommodationPricesFilters.max,
                },
            };

            const query = { ...this.$route.query };

            delete query.accommodation_type;
            delete query.accommodation_occupation;
            delete query.amenities;
            delete query.room_types;
            delete query.bedrooms;
            delete query.beds;
            delete query.bathrooms;
            delete query.instant_book;
            delete query.self_check_in;
            delete query.superhost;

            Object.keys(query).forEach((key) => {
                if (
                    key.startsWith("price_min_") ||
                    key.startsWith("price_max_")
                ) {
                    delete query[key];
                }
            });

            this.$router.replace({ query });
            this.resetPaginationAndSearch();
        },

        resetPaginationAndSearch() {
            this.page = 1;
            this.infiniteId = +new Date();
            this.performSearch(false);
        },

        async loadMore($state) {
            this.lastInfiniteState = $state;
            this.page++;

            try {
                const { data, found } = await this.performSearch();

                const perPage = 12;

                if (data && data.length > 0) {
                    if (data.length >= perPage) {
                        $state.loaded();
                    } else {
                        $state.complete();
                    }
                } else {
                    $state.complete();
                }
            } catch (error) {
                console.error("Load more error:", error);

                if (
                    error.response?.status === 404 ||
                    error.message?.includes("No results")
                ) {
                    $state.complete();
                } else {
                    $state.error();
                }
            }
        },

        retryLoadMore() {
            if (this.lastInfiniteState) {
                this.loadMore(this.lastInfiniteState);
            }
        },

        handleCardHover(cardId) {
            this.hoveredCardId = cardId;
        },

        handleCardClick(accommodation) {
            this.$router.push(`/accommodation/${accommodation.id}`);
        },

        handleResize() {
            this.isMobile = window.innerWidth < searchConfig.breakpoints.tablet;
        },

        updateSearchParamsInURL() {
            const query = { ...this.$route.query };

            if (this.searchParams.location.id) {
                query.locationId = this.searchParams.location.id;
            }
            if (this.searchParams.location.name) {
                query.locationName = this.searchParams.location.name;
            }
            if (this.searchParams.checkIn) {
                query.checkIn = this.searchParams.checkIn;
            }
            if (this.searchParams.checkOut) {
                query.checkOut = this.searchParams.checkOut;
            }

            query.adults = this.searchParams.guests.adults || 2;
            query.children = this.searchParams.guests.children || 0;
            query.infants = this.searchParams.guests.infants || 0;

            this.$router.replace({ query });
        },

        updateFiltersInURL() {
    const query = { ...this.$route.query };

    // Remove all existing price filters first
    Object.keys(query).forEach((key) => {
        if (
            key.startsWith("price_min_") ||
            key.startsWith("price_max_")
        ) {
            delete query[key];
        }
    });

    const priceMin = this.activeFilters.priceRange?.min;
    const priceMax = this.activeFilters.priceRange?.max;
    const facetMin = this.accommodationPricesFilters?.stats?.min;
    const facetMax = this.accommodationPricesFilters?.stats?.max;

    // Check if both values are valid and not equal to facet range
    const hasValidMin = priceMin !== null && priceMin !== undefined && facetMin !== undefined;
    const hasValidMax = priceMax !== null && priceMax !== undefined && facetMax !== undefined;
    const minDifferent = hasValidMin && priceMin !== facetMin;
    const maxDifferent = hasValidMax && priceMax !== facetMax;

    // Add prices in  URL ONLY if BOTH are present or one is non equal to facet range
    if (hasValidMin && hasValidMax && (minDifferent || maxDifferent)) {
        query["price_min_" + this.selectedCurrency.code] = priceMin;
        query["price_max_" + this.selectedCurrency.code] = priceMax;
    }

    // Accommodation type
    if (this.activeFilters.accommodation_type !== null) {
        query.accommodation_type = this.activeFilters.accommodation_type;
    } else {
        delete query.accommodation_type;
    }

    // Accommodation occupation
    if (this.activeFilters.accommodation_occupation !== null) {
        query.accommodation_occupation = this.activeFilters.accommodation_occupation;
    } else {
        delete query.accommodation_occupation;
    }

    // Amenities
    if (this.activeFilters.amenities?.length) {
        query.amenities = this.activeFilters.amenities.join(",");
    } else {
        delete query.amenities;
    }

    // Clean up null/undefined/empty values
    Object.keys(query).forEach((key) => {
        if (
            query[key] === null ||
            query[key] === undefined ||
            query[key] === ""
        ) {
            delete query[key];
        }
    });

    this.$router.replace({ query });
},

        parseURLParams() {
    const query = this.$route.query;

    // Parse search params
    if (query.locationId || query.locationName) {
        this.searchParams.location = {
            id: query.locationId || null,
            name: query.locationName || "",
        };
        this.searchParams.locationId = query.locationId || null;
    }

    if (query.checkIn) {
        this.searchParams.checkIn = query.checkIn;
    }
    if (query.checkOut) {
        this.searchParams.checkOut = query.checkOut;
    }
    if (query.adults) {
        this.searchParams.guests.adults = parseInt(query.adults);
    }
    if (query.children) {
        this.searchParams.guests.children = parseInt(query.children);
    }
    if (query.infants) {
        this.searchParams.guests.infants = parseInt(query.infants);
    }
    if (query.pets) {
        this.searchParams.guests.pets = parseInt(query.pets);
    }

    // Parse filters
    if (query.accommodation_type) {
        this.activeFilters.accommodation_type = query.accommodation_type;
    }

    if (query.accommodation_occupation) {
        this.activeFilters.accommodation_occupation = query.accommodation_occupation;
    }

    if (query.amenities) {
        this.activeFilters.amenities = query.amenities.split(",");
    }

    // Parse price filters - check for current currency
    const currentCurrencyMinKey = "price_min_" + this.selectedCurrency.code;
    const currentCurrencyMaxKey = "price_max_" + this.selectedCurrency.code;

    // Check if there are price filters for different currencies
    const allPriceMinKeys = Object.keys(query).filter((k) =>
        k.startsWith("price_min_")
    );
    const allPriceMaxKeys = Object.keys(query).filter((k) =>
        k.startsWith("price_max_")
    );

    const hasDifferentCurrencyPrices =
        allPriceMinKeys.some((k) => k !== currentCurrencyMinKey) ||
        allPriceMaxKeys.some((k) => k !== currentCurrencyMaxKey);

    // If there are price filters for different currencies, remove them from URL
    if (hasDifferentCurrencyPrices) {
        const newQuery = { ...query };

        // Remove all price filters that don't match current currency
        allPriceMinKeys.forEach((key) => {
            if (key !== currentCurrencyMinKey) {
                delete newQuery[key];
            }
        });

        allPriceMaxKeys.forEach((key) => {
            if (key !== currentCurrencyMaxKey) {
                delete newQuery[key];
            }
        });

        // Update URL without wrong currency filters
        this.$router.replace({ query: newQuery });
    }

    // BOTH parameters must be present
    const hasMinInUrl = query[currentCurrencyMinKey] !== undefined;
    const hasMaxInUrl = query[currentCurrencyMaxKey] !== undefined;

    // If there is only one delete it
    if (hasMinInUrl !== hasMaxInUrl) {
        const newQuery = { ...query };
        delete newQuery[currentCurrencyMinKey];
        delete newQuery[currentCurrencyMaxKey];
        this.$router.replace({ query: newQuery });

        // Set to null (full range)
        this.activeFilters.priceRange = {
            min: null,
            max: null
        };
        return;
    }

    // If there are both (parse them)
    if (hasMinInUrl && hasMaxInUrl) {
        const minValue = parseInt(query[currentCurrencyMinKey]);
        const maxValue = parseInt(query[currentCurrencyMaxKey]);

        if (this.accommodationPricesFilters?.stats) {
            this.activeFilters.priceRange = {
                min: Math.max(minValue, this.accommodationPricesFilters.stats.min),
                max: Math.min(maxValue, this.accommodationPricesFilters.stats.max)
            };
        } else {
            this.activeFilters.priceRange = {
                min: minValue,
                max: maxValue
            };
        }
    } else {
        // Set them on null (full range)
        this.activeFilters.priceRange = {
            min: null,
            max: null
        };
    }
},

        handleSearch(searchData) {
            this.searchParams = {
                location: searchData.location,
                checkIn: searchData.checkIn,
                checkOut: searchData.checkOut,
                guests: searchData.guests,
            };

            this.updateSearchParamsInURL();
            this.resetPaginationAndSearch();
        },
    },
};
</script>
