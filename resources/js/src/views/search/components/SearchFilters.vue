<template>
    <div class="p-6 space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800">
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
            :price-range="filters.priceRange"
            @update:price-range="handleFilterUpdate('priceRange', $event)"
        />

        <!-- Property Type -->
        <property-type-filter
            :selected-types="filters.propertyTypes"
            @update:types="handleFilterUpdate('propertyTypes', $event)"
        />

        <!-- Rooms & Beds -->
        <rooms-beds-filter
            :bedrooms="filters.bedrooms"
            :beds="filters.beds"
            :bathrooms="filters.bathrooms"
            @update:bedrooms="handleFilterUpdate('bedrooms', $event)"
            @update:beds="handleFilterUpdate('beds', $event)"
            @update:bathrooms="handleFilterUpdate('bathrooms', $event)"
        />

        <!-- Amenities -->
        <amenities-filter
            :selected-amenities="filters.amenities"
            @update:amenities="handleFilterUpdate('amenities', $event)"
        />

        <!-- Booking Options -->
        <booking-options-filter
            :instant-book="filters.instantBook"
            :self-checkin="filters.selfCheckIn"
            @update:instant-book="handleFilterUpdate('instantBook', $event)"
            @update:self-checkin="handleFilterUpdate('selfCheckIn', $event)"
        />

        <!-- Top Tier Stays -->
        <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                Top tier stays
            </h3>
            <div class="space-y-3">
                <label class="flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        :checked="filters.superhost"
                        @change="handleFilterUpdate('superhost', $event.target.checked)"
                        class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            ⭐ Superhost
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            Experienced hosts with great reviews
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- More Filters Button -->
        <button
            @click="showMoreFilters = true"
            class="w-full py-3 px-4 border border-gray-300 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors flex items-center justify-center space-x-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <span>More filters</span>
        </button>

        <!-- More Filters Modal -->
        <more-filters-modal
            v-if="showMoreFilters"
            :filters="filters"
            @update:filters="handleFiltersUpdate"
            @close="showMoreFilters = false"
        />
    </div>
</template>

<script>
import PriceFilter from './filters/PriceFilter.vue';
import PropertyTypeFilter from './filters/PropertyTypeFilter.vue';
import RoomsBedsFilter from './filters/RoomsBedsFilter.vue';
import AmenitiesFilter from './filters/AmenitiesFilter.vue';
import BookingOptionsFilter from './filters/BookingOptionsFilter.vue';
import MoreFiltersModal from './filters/MoreFiltersModal.vue';

export default {
    name: 'SearchFilters',
    components: {
        PriceFilter,
        PropertyTypeFilter,
        RoomsBedsFilter,
        AmenitiesFilter,
        BookingOptionsFilter,
        MoreFiltersModal,
    },
    props: {
        filters: {
            type: Object,
            required: true,
        },
        activeFiltersCount: {
            type: Number,
            default: 0,
        },
    },
    data() {
        return {
            showMoreFilters: false,
        };
    },
    methods: {
        handleFilterUpdate(key, value) {
            this.$emit('update:filters', {
                [key]: value,
            });
        },
        handleFiltersUpdate(updates) {
            this.$emit('update:filters', updates);
        },
    },
};
</script>
