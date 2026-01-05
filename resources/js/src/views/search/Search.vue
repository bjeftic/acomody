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
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Sort Menu -->
                                <div
                                    v-if="showSortMenu"
                                    v-click-outside="() => showSortMenu = false"
                                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-20"
                                >
                                    <button
                                        v-for="option in config.sortOptions"
                                        :key="option.id"
                                        @click="handleSortChange(option.id)"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        :class="sortBy === option.id ? 'bg-gray-50 dark:bg-gray-700' : ''"
                                    >
                                        <span class="mr-2">{{ option.icon }}</span>
                                        {{ option.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- View Mode Toggle -->
                            <div class="flex items-center space-x-2 border border-gray-300 dark:border-gray-700 rounded-lg p-1">
                                <button
                                    @click="viewMode = 'grid'"
                                    :class="[
                                        'p-2 rounded transition-colors',
                                        viewMode === 'grid'
                                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                                    ]"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </button>
                                <button
                                    @click="viewMode = 'map'"
                                    :class="[
                                        'p-2 rounded transition-colors',
                                        viewMode === 'map'
                                            ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900'
                                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                                    ]"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Grid/Map -->
                <div class="relative">
                    <search-results
                        :results="results"
                        :loading="loading"
                        :view-mode="viewMode"
                        :hovered-card-id="hoveredCardId"
                        @map-bounds-changed="handleMapBoundsChanged"
                        @card-hover="handleCardHover"
                        @card-click="handleCardClick"
                        @load-more="loadMore"
                    />

                    <!-- Map Toggle Button (Mobile) -->
                    <button
                        v-if="isMobile && viewMode !== 'map'"
                        @click="viewMode = 'map'"
                        class="fixed bottom-6 left-1/2 -translate-x-1/2 px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full shadow-lg font-medium z-30 flex items-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span>Show map</span>
                    </button>
                </div>
            </main>
        </div>

        <!-- Mobile Filters Modal -->
        <filter-modal
            v-if="isMobile && showFilters"
            :filters="filters"
            :active-filters-count="activeFiltersCount"
            @update:filters="handleFiltersUpdate"
            @clear-all="clearAllFilters"
            @close="showFilters = false"
        />
    </div>
</template>

<script>
import { mapActions } from "vuex";
import moment from 'moment';
import SearchBar from "@/src/components/common/searchBar/SearchBar.vue";
import SearchFilters from './components/SearchFilters.vue';
import SearchResults from './components/SearchResults.vue';
import FilterModal from './components/FilterModal.vue';
import { searchConfig } from './config/searchConfig';
import { filtersConfig } from './config/filtersConfig';

export default {
    name: 'SearchPage',
    components: {
        SearchBar,
        SearchFilters,
        SearchResults,
        FilterModal,
    },
    data() {
        return {
            config: searchConfig,
            searchParams: { ...searchConfig.defaults },
            geoLocation: null,
            filters: { ...filtersConfig.defaults },
            results: [],
            totalResults: 0,
            loading: false,
            viewMode: searchConfig.defaultViewMode,
            sortBy: 'recommended',
            page: 1,
            hasMore: true,
            hoveredCardId: null,
            showFilters: false,
            showSortMenu: false,
            isMobile: window.innerWidth < searchConfig.breakpoints.tablet,
        };
    },
    computed: {
        dateRangeText() {
            if (!this.searchParams.checkIn || !this.searchParams.checkOut) {
                return 'Add dates';
            }

            const checkIn = moment(this.searchParams.checkIn);
            const checkOut = moment(this.searchParams.checkOut);
            const nights = checkOut.diff(checkIn, 'days');

            return `${checkIn.format('MMM D')} - ${checkOut.format('MMM D')} (${nights} nights)`;
        },
        activeFiltersCount() {
            let count = 0;
            if (this.filters.propertyTypes.length) count++;
            if (this.filters.priceRange.min > 0 || this.filters.priceRange.max < 1000) count++;
            if (this.filters.amenities.length) count++;
            if (this.filters.bedrooms.min > 0) count++;
            if (this.filters.instantBook) count++;
            return count;
        },
        currentSortOption() {
            return this.config.sortOptions.find(opt => opt.id === this.sortBy) || this.config.sortOptions[0];
        },
        dateRangeText() {
            if (!this.searchParams.checkIn || !this.searchParams.checkOut) {
                return 'Add dates';
            }
            const checkIn = new Date(this.searchParams.checkIn);
            const checkOut = new Date(this.searchParams.checkOut);
            const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            return `${checkIn.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${checkOut.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} (${nights} nights)`;
        },
        guestCountText() {
            const { adults, children, infants, pets } = this.searchParams.guests;
            const parts = [];
            if (adults) parts.push(`${adults} adult${adults > 1 ? 's' : ''}`);
            if (children) parts.push(`${children} child${children > 1 ? 'ren' : ''}`);
            if (infants) parts.push(`${infants} infant${infants > 1 ? 's' : ''}`);
            if (pets) parts.push(`${pets} pet${pets > 1 ? 's' : ''}`);
            return parts.join(', ') || '1 guest';
        },
        locationFromRoute() {
            const locationId = this.$route.query.locationId;
            const locationName = this.$route.query.locationName;

            if (!locationId && !locationName) {
                return null;
            }

            return {
                id: locationId || null,
                name: locationName || '',
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
    },
    mounted() {
        this.parseURLParams();
        this.performSearch();

        // Handle resize
        window.addEventListener('resize', this.handleResize);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.handleResize);
    },
    methods: {
        ...mapActions("search", ["searchAccommodations"]),
        async performSearch() {
            this.loading = true;
            try {
                // TODO: Replace with actual API call
                const response = await this.searchAccommodations({
                    ...this.searchParams,
                    ...this.geoLocation,
                    ...this.filters,
                    sortBy: this.sortBy,
                    page: this.page,
                    perPage: this.config.resultsPerPage,
                });
                const data = response;

                if (this.page === 1) {
                    this.results = data;
                } else {
                    this.results.push(...data);
                }

                this.totalResults = data.length;
                this.hasMore = data.length === 10;
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.loading = false;
            }
        },
        handleSearchUpdate(newParams) {
            this.searchParams = { ...this.searchParams, ...newParams };
            this.updateSearchParamsInURL();
            this.page = 1;
            this.performSearch();
        },
        handleFiltersUpdate(newFilters) {
            this.filters = { ...this.filters, ...newFilters };
            this.updateFiltersInURL();
            this.page = 1;
            this.performSearch();
        },
        handleSortChange(newSortBy) {
            this.sortBy = newSortBy;
            this.showSortMenu = false;
            this.page = 1;
            this.performSearch();
        },
        clearAllFilters() {
            this.filters = { ...filtersConfig.defaults };
            this.updateFiltersInURL();
            this.page = 1;
            this.performSearch();
        },
        loadMore() {
            if (!this.loading && this.hasMore) {
                this.page++;
                this.performSearch();
            }
        },
        handleMapBoundsChanged(bounds) {
            console.log('Map bounds changed:', bounds);
            this.performSearch();
            // Handle map bounds change if needed
            // For example, you could trigger a new search based on bounds
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

            // Update samo filters
            if (this.filters.propertyTypes?.length) {
                query.property_types = this.filters.propertyTypes.join(',');
            } else {
                delete query.property_types;
            }

            if (this.filters.priceRange?.min > 0) {
                query.price_min = this.filters.priceRange.min;
            } else {
                delete query.price_min;
            }

            if (this.filters.priceRange?.max < 1000) {
                query.price_max = this.filters.priceRange.max;
            } else {
                delete query.price_max;
            }

            if (this.filters.amenities?.length) {
                query.amenities = this.filters.amenities.join(',');
            } else {
                delete query.amenities;
            }

            Object.keys(query).forEach(key => {
                if (query[key] === null || query[key] === undefined || query[key] === '') {
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
                    name: query.locationName || '',
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
            if (query.property_types) {
                this.filters.propertyTypes = query.property_types.split(',');
            }
            if (query.price_min) {
                this.filters.priceRange.min = parseInt(query.price_min);
            }
            if (query.price_max) {
                this.filters.priceRange.max = parseInt(query.price_max);
            }
            if (query.amenities) {
                this.filters.amenities = query.amenities.split(',');
            }
        },
        handleSearch(searchData) {
            // Update search params
            this.searchParams = {
                location: searchData.location,
                checkIn: searchData.checkIn,
                checkOut: searchData.checkOut,
                guests: searchData.guests,
            };

            // Update URL
            this.updateSearchParamsInURL();

            // Reset pagination and search
            this.page = 1;
            this.performSearch();
        },
    },
};
</script>
