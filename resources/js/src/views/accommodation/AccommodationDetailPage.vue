<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Loading State -->
        <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="animate-pulse space-y-8">
                <div class="h-96 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                <div class="space-y-3">
                    <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div v-else-if="accommodation" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Button -->
            <button
                @click="goBack"
                class="flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6 transition-colors"
            >
                <svg
                    class="w-5 h-5 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
                Back to listings
            </button>

            <!-- Photo Gallery -->
            <div class="mb-8">
                <photo-gallery :photos="accommodation.photos" />
            </div>

            <!-- Main Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Main Info -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Header Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h1
                                    class="text-3xl font-bold text-gray-900 dark:text-white mb-2"
                                >
                                    {{ accommodation.title }}
                                </h1>
                                <div class="flex items-center flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-1"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                                            />
                                        </svg>
                                        {{ accommodation.address }}
                                    </div>
                                    <div
                                        v-if="accommodation.rating"
                                        class="flex items-center"
                                    >
                                        <svg
                                            class="w-5 h-5 mr-1 text-yellow-400"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                                            />
                                        </svg>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ accommodation.rating }}
                                        </span>
                                        <span class="ml-1">
                                            ({{ accommodation.reviews_count || 0 }} reviews)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="toggleFavorite"
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                                :class="{
                                    'text-red-500': isFavorite,
                                    'text-gray-400': !isFavorite,
                                }"
                            >
                                <svg
                                    class="w-6 h-6"
                                    :fill="isFavorite ? 'currentColor' : 'none'"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Quick Info -->
                        <div
                            class="flex items-center flex-wrap gap-4 pt-4 border-t border-gray-200 dark:border-gray-700"
                        >
                            <div
                                v-if="accommodation.max_guests"
                                class="flex items-center text-gray-700 dark:text-gray-300"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                                {{ accommodation.max_guests }} guests
                            </div>
                            <div
                                v-if="accommodation.bedrooms"
                                class="flex items-center text-gray-700 dark:text-gray-300"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                    />
                                </svg>
                                {{ accommodation.bedrooms }} bedrooms
                            </div>
                            <div
                                v-if="accommodation.beds"
                                class="flex items-center text-gray-700 dark:text-gray-300"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                    />
                                </svg>
                                {{ accommodation.beds }} beds
                            </div>
                            <div
                                v-if="accommodation.bathrooms"
                                class="flex items-center text-gray-700 dark:text-gray-300"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                {{ accommodation.bathrooms }} bathrooms
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div
                        v-if="accommodation.description"
                        class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
                    >
                        <h2
                            class="text-xl font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            About this place
                        </h2>
                        <p
                            class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line"
                        >
                            {{ accommodation.description }}
                        </p>
                    </div>

                    <!-- Amenities -->
                    <div
                        v-if="accommodation.amenities && accommodation.amenities.length > 0"
                        class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
                    >
                        <h2
                            class="text-xl font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Amenities
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="amenity in accommodation.amenities"
                                :key="amenity"
                                class="flex items-center text-gray-700 dark:text-gray-300"
                            >
                                <IconLoader :name="amenity.icon" :size="24" class="mr-2" />
                                {{ amenity.name }}
                            </div>
                        </div>
                    </div>

                    <!-- Location Map Placeholder -->
                    <div
                        v-if="accommodation.latitude && accommodation.longitude"
                        class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700"
                    >
                        <h2
                            class="text-xl font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            Location
                        </h2>
                        <div
                            class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center"
                        >
                            <div class="text-center text-gray-500 dark:text-gray-400">
                                <svg
                                    class="w-12 h-12 mx-auto mb-2"
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
                                <p class="text-sm">Map will be displayed here</p>
                                <p class="text-xs mt-1">
                                    {{ accommodation.latitude }}, {{ accommodation.longitude }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- House Rules -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <h2
                            class="text-xl font-semibold text-gray-900 dark:text-white mb-4"
                        >
                            House Rules
                        </h2>
                        <div class="space-y-3 text-gray-700 dark:text-gray-300">
                            <div
                                v-if="accommodation.check_in_from"
                                class="flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 mr-3 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                Check-in: {{ accommodation.check_in_from }}
                                <span v-if="accommodation.check_in_until">
                                    - {{ accommodation.check_in_until }}
                                </span>
                            </div>
                            <div
                                v-if="accommodation.check_out_until"
                                class="flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 mr-3 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                Check-out: {{ accommodation.check_out_until }}
                            </div>
                            <div
                                v-if="accommodation.quiet_hours_from && accommodation.quiet_hours_until"
                                class="flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 mr-3 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                                    />
                                </svg>
                                Quiet hours: {{ accommodation.quiet_hours_from }} -
                                {{ accommodation.quiet_hours_until }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Booking Card (Sticky) -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8">
                        <booking-card
                            :accommodation="accommodation"
                            @book="handleBooking"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div
            v-else-if="error"
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        >
            <fwb-alert type="danger">
                {{ error }}
            </fwb-alert>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import PhotoGallery from "./components/PhotoGallery.vue";
import BookingCard from "./components/BookingCard.vue";

export default {
    name: "AccommodationDetailPage",
    components: {
        PhotoGallery,
        BookingCard,
    },
    data() {
        return {
            error: null,
            isFavorite: false,
        };
    },
    computed: {
        ...mapState("accommodation", {
            loading: (state) => state.loading,
            accommodation: (state) => state.accommodation,
        }),
        accommodationId() {
            return this.$route.params.id;
        },
        accommodationQueryParams() {
            const { checkIn, checkOut, adults, children, infants } = this.$route.query;
            const params = {};
            if (checkIn) params.checkIn = checkIn;
            if (checkOut) params.checkOut = checkOut;
            if (adults) params.adults = adults;
            if (children) params.children = children;
            if (infants) params.infants = infants;
            return params;
        },
    },
    methods: {
        ...mapActions("accommodation", ["fetchAccommodation"]),
        async checkFavoriteStatus() {
            // TODO: Implement favorite status check from API
        },
        async toggleFavorite() {
            try {
                this.isFavorite = !this.isFavorite;
                // TODO: Call API to toggle favorite
            } catch (err) {
                console.error("Error toggling favorite:", err);
            }
        },
        handleBooking(bookingData) {
            console.log("Booking data:", bookingData);
            this.$router.push({
                name: "booking-checkout",
                params: { id: this.accommodationId },
                query: bookingData,
            });
        },
        goBack() {
            if (window.history.length > 1) {
                this.$router.go(-1);
            } else {
                this.$router.push({ name: "home" });
            }
        },
    },
    created() {
        this.fetchAccommodation({ id: this.accommodationId, params: this.accommodationQueryParams });
    },
    watch: {
        accommodationId() {
            this.fetchAccommodation({ id: this.accommodationId, params: this.accommodationQueryParams });
        },
    },
};
</script>
