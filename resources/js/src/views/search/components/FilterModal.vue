<template>
    <transition name="modal">
        <div class="fixed inset-0 z-50 overflow-hidden">
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-black bg-opacity-50 transition-opacity"
                @click="$emit('close')"
            ></div>

            <!-- Modal -->
            <div class="absolute inset-0 flex items-end sm:items-center justify-center">
                <div
                    class="relative w-full h-full sm:h-auto sm:max-h-[90vh] bg-white dark:bg-gray-900 sm:rounded-t-2xl sm:max-w-3xl overflow-hidden flex flex-col"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                        <button
                            @click="handleClearAll"
                            class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white underline"
                        >
                            Clear all
                        </button>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Filters
                        </h2>
                        <button
                            @click="$emit('close')"
                            class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Filters Content (Scrollable) -->
                    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-8">
                        <!-- Price Range -->
                        <price-filter
                            :price-range="localFilters.priceRange"
                            @update:price-range="handleFilterUpdate('priceRange', $event)"
                        />

                        <!-- Accommodation Type -->
                        <accommodation-type-filter
                            :selected-types="localFilters.accommodationTypes"
                            @update:types="handleFilterUpdate('accommodationTypes', $event)"
                        />

                        <!-- Rooms & Beds -->
                        <rooms-beds-filter
                            :bedrooms="localFilters.bedrooms"
                            :beds="localFilters.beds"
                            :bathrooms="localFilters.bathrooms"
                            @update:bedrooms="handleFilterUpdate('bedrooms', $event)"
                            @update:beds="handleFilterUpdate('beds', $event)"
                            @update:bathrooms="handleFilterUpdate('bathrooms', $event)"
                        />

                        <!-- Amenities -->
                        <amenities-filter
                            :selected-amenities="localFilters.amenities"
                            @update:amenities="handleFilterUpdate('amenities', $event)"
                        />

                        <!-- Booking Options -->
                        <booking-options-filter
                            :instant-book="localFilters.instantBook"
                            :self-checkin="localFilters.selfCheckIn"
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
                                        :checked="localFilters.superhost"
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
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ activeFiltersCount }} filter{{ activeFiltersCount !== 1 ? 's' : '' }} applied
                        </div>
                        <button
                            @click="handleApplyFilters"
                            class="px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors"
                        >
                            Show results
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import PriceFilter from '../../../modals/search/components/PriceFilter.vue';
import PropertyTypeFilter from './filters/AccommodationTypeFilter.vue';
import RoomsBedsFilter from './filters/RoomsBedsFilter.vue';
import AmenitiesFilter from './filters/AmenitiesFilter.vue';
import BookingOptionsFilter from './filters/BookingOptionsFilter.vue';

export default {
    name: 'FilterModal',
    components: {
        PriceFilter,
        PropertyTypeFilter,
        RoomsBedsFilter,
        AmenitiesFilter,
        BookingOptionsFilter,
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
            localFilters: { ...this.filters },
        };
    },
    watch: {
        filters: {
            handler(newFilters) {
                this.localFilters = { ...newFilters };
            },
            deep: true,
        },
    },
    mounted() {
        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';
    },
    beforeDestroy() {
        // Restore body scroll
        document.body.style.overflow = '';
    },
    methods: {
        handleFilterUpdate(key, value) {
            this.localFilters = {
                ...this.localFilters,
                [key]: value,
            };
        },
        handleApplyFilters() {
            this.$emit('update:filters', this.localFilters);
            this.$emit('close');
        },
        handleClearAll() {
            this.$emit('clear-all');
            this.$emit('close');
        },
    },
};
</script>

<style scoped>
/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active > div > div,
.modal-leave-active > div > div {
    transition: transform 0.3s ease;
}

.modal-enter > div > div,
.modal-leave-to > div > div {
    transform: translateY(100%);
}

@media (min-width: 640px) {
    .modal-enter > div > div,
    .modal-leave-to > div > div {
        transform: scale(0.95);
    }
}
</style>
