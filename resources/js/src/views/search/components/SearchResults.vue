<template>
    <div>
        <!-- Map View -->
        <div class="relative">
            <div class="flex h-[calc(100vh-180px)]">
                <!-- Results List (Left Side) -->
                <div
                    class="w-3/5 overflow-y-auto border-r border-gray-200 dark:border-gray-800 p-4"
                >
                    <div class="flex items-center justify-between mb-2">
                        <div
                            v-if="totalAccommodationsFound > 0"
                            class="text-base font-semibold text-gray-900 dark:text-white"
                        >
                            {{ totalAccommodationsFound }} results
                        </div>

                        <div class="flex items-center space-x-4 ml-auto">
                            <!-- Sort Dropdown -->
                            <div class="relative flex gap-2">
                                <BaseButton variant="secondary" @click="openFiltersModal">
                                    <IconLoader name="SlidersHorizontal" :size="18" />
                                    Filters
                                </BaseButton>
                                <BaseDropdown align="end" close-inside>
                                    <template #trigger="{ isOpen }">
                                        <BaseButton variant="secondary">
                                            {{ currentSortOption.name }}
                                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </BaseButton>
                                    </template>
                                    <nav class="bg-white dark:bg-gray-800 py-1 text-sm rounded-xl text-gray-700 dark:text-gray-200 flex flex-col shadow-dropdown border border-gray-100 dark:border-gray-700">
                                        <span
                                            v-for="sortOption in config.ui.sortOptions"
                                            :key="sortOption.id"
                                            class="cursor-pointer px-4 py-2 min-w-36 hover:bg-gray-50 dark:hover:bg-gray-700"
                                            @click="handleSortChange({ route: $route, router: $router, newSortBy: sortOption.id })"
                                        >
                                            {{ sortOption.name }}
                                        </span>
                                    </nav>
                                </BaseDropdown>
                            </div>
                        </div>
                    </div>
                    <!-- View Options -->
                    <div class="grid grid-cols-2 gap-6">
                        <accommodation-card
                            v-for="accommodation in accommodations"
                            :key="accommodation.id"
                            :accommodation="accommodation"
                            :hovered="hoveredCardId === accommodation.id"
                            @click="$emit('card-click', accommodation)"
                            @hover="$emit('card-hover', $event)"
                        />
                    </div>
                    <fwb-pagination
                        v-if="accommodations.length > 0"
                        :model-value="page"
                        :total-items="totalAccommodationsFound"
                        :per-page="20"
                        @update:model-value="handlePageChange"
                        large
                    />
                </div>

                <!-- Map (Right Side) -->
                <div class="flex-1">
                    <search-map
                        :results="accommodations"
                        :hovered-card-id="hoveredCardId"
                        :current-map-bounds="bounds"
                        @map-bounds-changed="
                            $emit('map-bounds-changed', $event)
                        "
                        @marker-hover="$emit('card-hover', $event)"
                        @marker-click="$emit('card-click', $event)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import config from "@/config";
import AccommodationCard from "@/src/views/search/components/AccommodationCard.vue";
import SearchMap from "./SearchMap.vue";

export default {
    name: "SearchResults",
    components: {
        AccommodationCard,
        SearchMap,
    },
    props: {
        loading: {
            type: Boolean,
            default: false,
        },
        hoveredCardId: {
            type: [Number, String],
            default: null,
        },
        bounds: {
            type: Object,
            default: null,
        },
    },
    computed: {
        ...mapState("search", {
            accommodations: (state) => state.accommodations,
            totalAccommodationsFound: (state) => state.totalAccommodationsFound,
            searchParams: (state) => state.searchParams,
            page: (state) => state.page,
        }),
        currentSortOption() {
            return (
                this.config.ui.sortOptions.find((opt) => opt.id === this.searchParams.sortBy) ||
                this.config.ui.sortOptions[0]
            );
        },
    },
    data() {
        return {
            config,
        };
    },
    methods: {
        ...mapActions(["openModal"]),
        ...mapActions("search", ["handleSortChange"]),
        handlePageChange(newPage) {
            this.$emit("page-changed", newPage);
        },
        openFiltersModal() {
            this.openModal({
                modalName: "filtersModal",
            });
        },
    },
};
</script>
