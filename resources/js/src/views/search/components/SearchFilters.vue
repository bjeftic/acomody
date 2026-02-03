<template>
    <div class="p-6 space-y-8">
        <!-- Header -->
        <div
            class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800"
        >
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Filters
            </h2>
            <button
                v-if="activeFiltersCount > 0"
                @click="$emit('clear-all')"
                class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
            >
                Clear all
            </button>
        </div>

        <!-- Price Range -->
        <price-filter
            v-if="facetPriceRange"
            :facet-price-range="facetPriceRange"
            :price-range="activeFilters.priceRange"
            :average-price="averagePrice"
            @update:price-range="handleFilterUpdate('priceRange', $event)"
        />

        <!-- Checkbox filters -->
        <checkbox-filter
            v-for="filter in filters"
            :key="filter.field_name"
            :title="filter.title"
            :options="filter.counts"
            :selected-options="activeFilters[filter.field_name]"
            @update:options="handleFilterUpdate(filter.field_name, $event)"
        />

        <!-- Accommodation Type -->
        <accommodation-type-filter
            :selected-types="activeFilters.accommodationTypes"
            @update:types="handleFilterUpdate('accommodationTypes', $event)"
        />

        <!-- Rooms & Beds -->
        <rooms-beds-filter
            :bedrooms="activeFilters.bedrooms"
            :beds="activeFilters.beds"
            :bathrooms="activeFilters.bathrooms"
            @update:bedrooms="handleFilterUpdate('bedrooms', $event)"
            @update:beds="handleFilterUpdate('beds', $event)"
            @update:bathrooms="handleFilterUpdate('bathrooms', $event)"
        />

        <!-- Amenities -->
        <amenities-filter
            :selected-amenities="activeFilters.amenities"
            @update:amenities="handleFilterUpdate('amenities', $event)"
        />

        <!-- Booking Options -->
        <booking-options-filter
            :instant-book="activeFilters.instantBook"
            :self-checkin="activeFilters.selfCheckIn"
            @update:instant-book="handleFilterUpdate('instantBook', $event)"
            @update:self-checkin="handleFilterUpdate('selfCheckIn', $event)"
        />

        <!-- Top Tier Stays -->
        <div class="pb-6">
            <h3
                class="text-base font-semibold text-gray-900 dark:text-white mb-4"
            >
                Top tier stays
            </h3>
            <div class="space-y-3">
                <label class="flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        :checked="activeFilters.superhost"
                        @change="
                            handleFilterUpdate(
                                'superhost',
                                $event.target.checked,
                            )
                        "
                        class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <div class="ml-3">
                        <div
                            class="text-sm font-medium text-gray-900 dark:text-white"
                        >
                            ⭐ Superhost
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            Experienced hosts with great reviews
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";

import AccommodationTypeFilter from "./filters/AccommodationTypeFilter.vue";
import RoomsBedsFilter from "./filters/RoomsBedsFilter.vue";
import AmenitiesFilter from "./filters/AmenitiesFilter.vue";
import BookingOptionsFilter from "./filters/BookingOptionsFilter.vue";

export default {
    name: "SearchFilters",
    components: {
        PriceFilter,
        CheckboxFilter,
        AccommodationTypeFilter,
        RoomsBedsFilter,
        AmenitiesFilter,
        BookingOptionsFilter,
    },
    props: {
        filters: {
            type: Object,
            required: true,
        },
        activeFilters: {
            type: Object,
            required: true,
        },
        activeFiltersCount: {
            type: Number,
            default: 0,
        },
    },
    computed: {
        ...mapGetters("search", {
            accommodationPricesFilters: "accommodationPricesFilters",
        }),
        facetPriceRange() {
            if (
                this.accommodationPricesFilters &&
                this.accommodationPricesFilters.stats &&
                this.accommodationPricesFilters.stats.min !== undefined &&
                this.accommodationPricesFilters.stats.max !== undefined
            ) {
                return {
                    min: this.accommodationPricesFilters.stats.min,
                    max: this.accommodationPricesFilters.stats.max,
                };
            }
            return { min: null, max: null };
        },
        averagePrice() {
            if (
                this.accommodationPricesFilters &&
                this.accommodationPricesFilters.stats &&
                this.accommodationPricesFilters.stats.avg !== undefined
            ) {
                return Math.round(this.accommodationPricesFilters.stats.avg);
            }
            return "N/A";
        },
    },
    methods: {
        handleFilterUpdate(key, value) {
            this.$emit("update:filters", {
                [key]: value,
            });
        },
        handleFiltersUpdate(updates) {
            this.$emit("update:filters", updates);
        },
    },
};
</script>
