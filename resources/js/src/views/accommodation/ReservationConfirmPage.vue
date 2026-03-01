<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Loading State -->
        <div v-if="loading" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="animate-pulse space-y-6">
                <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                    <div class="h-64 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <fwb-alert type="danger">{{ error }}</fwb-alert>
        </div>

        <!-- Main Content -->
        <div v-else-if="accommodation" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <button
                    @click="$router.back()"
                    class="flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4 transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </button>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Confirm your reservation</h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column: Accommodation Card -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <!-- Primary Photo -->
                        <div class="aspect-video bg-gray-100 dark:bg-gray-700">
                            <img
                                v-if="primaryPhotoUrl"
                                :src="primaryPhotoUrl"
                                :alt="accommodation.title"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Accommodation Info -->
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {{ accommodation.title }}
                            </h2>
                            <div v-if="accommodation.address" class="flex items-start text-sm text-gray-600 dark:text-gray-400 mb-4">
                                <svg class="w-4 h-4 mr-1.5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                </svg>
                                {{ accommodation.address }}
                            </div>

                            <!-- Host Info -->
                            <div v-if="accommodation.host" class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex-shrink-0">
                                    <img
                                        v-if="accommodation.host.avatar_url"
                                        :src="accommodation.host.avatar_url"
                                        :alt="hostName"
                                        class="w-10 h-10 rounded-full object-cover"
                                    />
                                    <div v-else class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-700 dark:text-primary-300 font-semibold text-sm">
                                        {{ hostInitials }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Hosted by</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ hostName }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Booking Summary (Sticky) -->
                <div class="lg:sticky lg:top-8 space-y-4">
                    <!-- Dates & Guests Summary -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Your trip</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Dates</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ formattedCheckIn }} – {{ formattedCheckOut }}</p>
                                </div>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Guests</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ guestSummary }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Price details</h3>

                        <!-- Loading price -->
                        <div v-if="priceLoading" class="animate-pulse space-y-2">
                            <div class="flex justify-between">
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
                            </div>
                            <div class="flex justify-between">
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
                            </div>
                        </div>

                        <!-- Price error -->
                        <div v-else-if="priceError" class="text-sm text-red-600 dark:text-red-400">
                            {{ priceError }}
                        </div>

                        <!-- Actual breakdown -->
                        <div v-else-if="priceBreakdown" class="space-y-3">
                            <!-- Nightly rates summary -->
                            <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                                <span>{{ totalNights }} night{{ totalNights !== 1 ? 's' : '' }} × {{ priceBreakdown.currency }}</span>
                                <span>{{ priceBreakdown.subtotal_formatted }}</span>
                            </div>

                            <!-- Bulk discount -->
                            <div v-if="priceBreakdown.bulk_discount" class="flex justify-between text-sm text-green-600 dark:text-green-400">
                                <span>Long stay discount</span>
                                <span>-{{ formatAmount(priceBreakdown.bulk_discount.amount, priceBreakdown.currency) }}</span>
                            </div>

                            <!-- Mandatory fees -->
                            <template v-if="priceBreakdown.fees && priceBreakdown.fees.mandatory && priceBreakdown.fees.mandatory.length">
                                <div
                                    v-for="fee in priceBreakdown.fees.mandatory"
                                    :key="fee.name"
                                    class="flex justify-between text-sm text-gray-700 dark:text-gray-300"
                                >
                                    <span>{{ fee.name }}</span>
                                    <span>{{ formatAmount(fee.amount, priceBreakdown.currency) }}</span>
                                </div>
                            </template>

                            <!-- Taxes -->
                            <template v-if="priceBreakdown.taxes && priceBreakdown.taxes.length">
                                <div
                                    v-for="tax in priceBreakdown.taxes"
                                    :key="tax.name"
                                    class="flex justify-between text-sm text-gray-700 dark:text-gray-300"
                                >
                                    <span>{{ tax.name }}</span>
                                    <span>{{ formatAmount(tax.amount, priceBreakdown.currency) }}</span>
                                </div>
                            </template>

                            <!-- Total -->
                            <div class="flex justify-between text-base font-semibold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span>Total</span>
                                <span>{{ priceBreakdown.total_formatted }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Policy -->
                    <div v-if="accommodation.cancellation_policy" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Cancellation policy</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ accommodation.cancellation_policy }}</p>
                    </div>

                    <!-- Guard Alerts -->
                    <div v-if="!canReserve" class="space-y-3">
                        <div
                            v-if="!userHasName"
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4"
                        >
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                Please update your profile with your full name before reserving.
                                <router-link to="/account" class="font-medium underline ml-1">Update profile</router-link>
                            </p>
                        </div>
                        <div
                            v-if="!emailVerified"
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4"
                        >
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                Please verify your email address before reserving.
                                <router-link to="/account" class="font-medium underline ml-1">Go to account</router-link>
                            </p>
                        </div>
                        <div
                            v-if="!isNotOwner"
                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4"
                        >
                            <p class="text-sm text-red-800 dark:text-red-300">
                                You cannot book your own property.
                            </p>
                        </div>
                    </div>

                    <!-- Confirm Button -->
                    <fwb-button
                        color="blue"
                        size="lg"
                        class="w-full"
                        :disabled="!canReserve || isSubmitting || !!priceError"
                        @click="confirmReservation"
                    >
                        <span v-if="isSubmitting">Processing...</span>
                        <span v-else>Confirm reservation</span>
                    </fwb-button>

                    <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                        You won't be charged until your booking is confirmed.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
    name: 'ReservationConfirmPage',

    data() {
        return {
            priceBreakdown: null,
            priceLoading: false,
            priceError: null,
            isSubmitting: false,
            bookingError: null,
        };
    },

    computed: {
        ...mapState('accommodation', {
            loading: (state) => state.loading,
            accommodation: (state) => state.accommodation,
        }),
        ...mapState('user', {
            currentUser: (state) => state.currentUser,
        }),

        checkIn() {
            return this.$route.query.checkIn || '';
        },
        checkOut() {
            return this.$route.query.checkOut || '';
        },
        adults() {
            return parseInt(this.$route.query.adults) || 2;
        },
        children() {
            return parseInt(this.$route.query.children) || 0;
        },
        infants() {
            return parseInt(this.$route.query.infants) || 0;
        },
        totalGuests() {
            return this.adults + this.children;
        },
        totalNights() {
            if (!this.checkIn || !this.checkOut) return 0;
            const ci = new Date(this.checkIn);
            const co = new Date(this.checkOut);
            return Math.ceil(Math.abs(co - ci) / (1000 * 60 * 60 * 24));
        },

        formattedCheckIn() {
            if (!this.checkIn) return '—';
            return new Date(this.checkIn).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        },
        formattedCheckOut() {
            if (!this.checkOut) return '—';
            return new Date(this.checkOut).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        },
        guestSummary() {
            const parts = [];
            if (this.adults > 0) parts.push(`${this.adults} adult${this.adults !== 1 ? 's' : ''}`);
            if (this.children > 0) parts.push(`${this.children} child${this.children !== 1 ? 'ren' : ''}`);
            if (this.infants > 0) parts.push(`${this.infants} infant${this.infants !== 1 ? 's' : ''}`);
            return parts.join(', ') || '1 guest';
        },

        primaryPhotoUrl() {
            if (!this.accommodation?.photos?.length) return null;
            const primary = this.accommodation.photos.find(p => p.is_primary);
            return (primary || this.accommodation.photos[0])?.urls?.medium || null;
        },
        hostName() {
            const host = this.accommodation?.host;
            if (!host) return '';
            return `${host.first_name || ''} ${host.last_name || ''}`.trim() || 'Host';
        },
        hostInitials() {
            const host = this.accommodation?.host;
            if (!host) return 'H';
            return `${(host.first_name || '')[0] || ''}${(host.last_name || '')[0] || ''}`.toUpperCase() || 'H';
        },

        // Guards
        userHasName() {
            return !!(this.currentUser?.first_name);
        },
        emailVerified() {
            return !!(this.currentUser?.email_verified_at);
        },
        isNotOwner() {
            if (!this.accommodation?.host || !this.currentUser) return true;
            return this.accommodation.host.id !== this.currentUser.id;
        },
        canReserve() {
            return this.userHasName && this.emailVerified && this.isNotOwner;
        },
    },

    methods: {
        ...mapActions('accommodation', ['fetchAccommodation', 'calculatePrice', 'createBooking']),

        formatAmount(amount, currency) {
            const symbol = { EUR: '€', USD: '$', GBP: '£', RSD: 'дин' }[currency] || currency;
            return `${symbol}${Number(amount).toFixed(2)}`;
        },

        async loadPriceBreakdown() {
            if (!this.checkIn || !this.checkOut || !this.totalGuests) return;
            this.priceLoading = true;
            this.priceError = null;
            try {
                this.priceBreakdown = await this.calculatePrice({
                    accommodationId: this.$route.params.id,
                    checkIn: this.checkIn,
                    checkOut: this.checkOut,
                    guests: this.totalGuests,
                });
            } catch (err) {
                this.priceError = err?.error?.message || err?.message || 'Failed to calculate price.';
            } finally {
                this.priceLoading = false;
            }
        },

        async confirmReservation() {
            if (!this.canReserve || this.isSubmitting) return;
            this.isSubmitting = true;
            this.bookingError = null;
            try {
                const booking = await this.createBooking({
                    accommodationId: this.$route.params.id,
                    checkIn: this.checkIn,
                    checkOut: this.checkOut,
                    guests: this.totalGuests,
                });
                this.$router.push({ name: 'booking-detail', params: { id: booking.id } });
            } catch (err) {
                this.bookingError = err?.error?.message || err?.message || 'Failed to create booking. Please try again.';
            } finally {
                this.isSubmitting = false;
            }
        },
    },

    async created() {
        const id = this.$route.params.id;
        await this.fetchAccommodation({ id });
        await this.loadPriceBreakdown();
    },
};
</script>
