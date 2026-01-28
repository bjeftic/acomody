<template>
    <div>
        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="p-6">
            <!-- Loading Skeleton -->
            <div v-if="loading && results.length === 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="i in 9" :key="i" class="animate-pulse">
                    <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-xl mb-3"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
            </div>

            <!-- Results Grid -->
            <div v-else-if="results.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <accommodation-card
                        v-for="accommodation in results"
                        :key="accommodation.id"
                        :accommodation="accommodation"
                        :hovered="hoveredCardId === accommodation.id"
                        @click="$emit('card-click', accommodation)"
                        @hover="$emit('card-hover', $event)"
                    />
                </div>

                <!-- Infinite Loading -->
                <v3-infinite-loading
                    @infinite="handleInfiniteScroll"
                    :key="infiniteId"
                >
                    <template #spinner>
                        <div class="py-8 text-center">
                            <div class="inline-flex items-center space-x-2">
                                <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">Loading more...</span>
                            </div>
                        </div>
                    </template>
                    <template #complete>
                        <div class="py-8 text-center">
                            <p class="text-gray-500 dark:text-gray-400">
                                You've reached the end of the results
                            </p>
                        </div>
                    </template>
                </v3-infinite-loading>
            </div>

            <!-- No Results -->
            <div v-else class="text-center py-16">
                <svg class="w-24 h-24 mx-auto text-gray-300 dark:text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    No exact matches
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Try changing or removing some of your filters
                </p>
                <button
                    @click="$emit('clear-filters')"
                    class="px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors"
                >
                    Clear all filters
                </button>
            </div>
        </div>

        <!-- Map View -->
        <div v-else-if="viewMode === 'map'" class="relative">
            <div class="flex h-[calc(100vh-180px)]">
                <!-- Results List (Left Side) -->
                <div class="w-2/5 overflow-y-auto border-r border-gray-200 dark:border-gray-800 p-4">
                    <div class="space-y-4">
                        <accommodation-card
                            v-for="accommodation in results"
                            :key="accommodation.id"
                            :accommodation="accommodation"
                            :hovered="hoveredCardId === accommodation.id"
                            @click="$emit('card-click', accommodation)"
                            @hover="$emit('card-hover', $event)"
                        />

                        <!-- Infinite Loading for Map View -->
                        <v3-infinite-loading
                            v-if="results.length > 0"
                            @infinite="handleInfiniteScroll"
                            :key="infiniteId"
                        >
                            <template #spinner>
                                <div class="py-4 text-center">
                                    <div class="inline-flex items-center space-x-2">
                                        <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-gray-600 dark:text-gray-400 text-sm">Loading more...</span>
                                    </div>
                                </div>
                            </template>
                            <template #complete>
                                <div class="py-4 text-center">
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                                        No more results
                                    </p>
                                </div>
                            </template>
                        </v3-infinite-loading>
                    </div>
                </div>

                <!-- Map (Right Side) -->
                <div class="flex-1">
                    <search-map
                        :results="results"
                        :hovered-card-id="hoveredCardId"
                        @map-bounds-changed="$emit('map-bounds-changed', $event)"
                        @marker-hover="$emit('card-hover', $event)"
                        @marker-click="$emit('card-click', $event)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import V3InfiniteLoading from 'v3-infinite-loading';
import 'v3-infinite-loading/lib/style.css';
import AccommodationCard from './AccommodationCard.vue';
import SearchMap from './SearchMap.vue';

export default {
    name: 'SearchResults',
    components: {
        AccommodationCard,
        SearchMap,
        V3InfiniteLoading,
    },
    props: {
        results: {
            type: Array,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: false,
        },
        viewMode: {
            type: String,
            default: 'grid',
        },
        hoveredCardId: {
            type: [Number, String],
            default: null,
        },
        infiniteId: {
            type: [String, Number],
            default: 0,
        },
    },
    methods: {
        handleInfiniteScroll($state) {
            this.$emit('load-more', $state);
        },
        handleRetry() {
            this.$emit('retry');
        },
    },
};
</script>
