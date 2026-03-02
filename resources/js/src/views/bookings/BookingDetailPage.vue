<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Loading State -->
        <div v-if="loading" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="animate-pulse space-y-6">
                <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <fwb-alert type="danger">{{ error }}</fwb-alert>
        </div>

        <!-- Main Content -->
        <div v-else-if="booking" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Back -->
            <button
                @click="$router.back()"
                class="flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </button>

            <!-- Success Banner -->
            <div
                :class="[
                    'rounded-xl p-6 border',
                    isConfirmed
                        ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                        : 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
                ]"
            >
                <div class="flex items-start gap-4">
                    <div :class="['rounded-full p-2', isConfirmed ? 'bg-green-100 dark:bg-green-800' : 'bg-blue-100 dark:bg-blue-800']">
                        <svg v-if="isConfirmed" class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 :class="['text-xl font-bold', isConfirmed ? 'text-green-900 dark:text-green-100' : 'text-blue-900 dark:text-blue-100']">
                            {{ isConfirmed ? 'Booking confirmed!' : 'Booking request sent!' }}
                        </h1>
                        <p :class="['text-sm mt-1', isConfirmed ? 'text-green-700 dark:text-green-300' : 'text-blue-700 dark:text-blue-300']">
                            {{ isConfirmed
                                ? 'Your reservation is confirmed. Check your email for details.'
                                : 'Your request has been sent to the host. You\'ll hear back soon.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Accommodation Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div v-if="booking.accommodation.primary_photo_url" class="aspect-video">
                    <img
                        :src="booking.accommodation.primary_photo_url"
                        :alt="booking.accommodation.title"
                        class="w-full h-full object-cover"
                    />
                </div>
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ booking.accommodation.title }}</h2>
                    <p v-if="booking.accommodation.address" class="text-sm text-gray-600 dark:text-gray-400 mt-1 flex items-start gap-1">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                        </svg>
                        {{ booking.accommodation.address }}
                    </p>
                </div>
            </div>

            <!-- Stay Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stay details</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Check-in</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ formatDate(booking.check_in) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Check-out</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ formatDate(booking.check_out) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Guests</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ booking.guests }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nights</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ booking.nights }}</p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-3">
                    <!-- Status Badge -->
                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-medium', statusBadgeClass]">
                        {{ booking.status_label || booking.status }}
                    </span>
                    <!-- Booking Type -->
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 capitalize">
                        {{ bookingTypeLabel }}
                    </span>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Price breakdown</h3>
                <div class="space-y-3">
                    <!-- Nightly subtotal -->
                    <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>{{ booking.nights }} night{{ booking.nights !== 1 ? 's' : '' }}</span>
                        <span>{{ formatBookingAmount(booking.subtotal) }}</span>
                    </div>

                    <!-- Bulk discount -->
                    <div v-if="booking.price_details?.bulk_discount" class="flex justify-between text-sm text-green-600 dark:text-green-400">
                        <span>Long stay discount</span>
                        <span>-{{ formatBookingAmount(booking.price_details.bulk_discount.amount) }}</span>
                    </div>

                    <!-- Mandatory fees -->
                    <template v-if="booking.price_details?.fees?.mandatory">
                        <div
                            v-for="fee in booking.price_details.fees.mandatory"
                            :key="fee.name"
                            class="flex justify-between text-sm text-gray-700 dark:text-gray-300"
                        >
                            <span>{{ fee.name }}</span>
                            <span>{{ formatBookingAmount(fee.amount) }}</span>
                        </div>
                    </template>

                    <!-- Taxes -->
                    <template v-if="booking.price_details?.taxes">
                        <div
                            v-for="tax in booking.price_details.taxes"
                            :key="tax.name"
                            class="flex justify-between text-sm text-gray-700 dark:text-gray-300"
                        >
                            <span>{{ tax.name }}</span>
                            <span>{{ formatBookingAmount(tax.amount) }}</span>
                        </div>
                    </template>

                    <!-- Total -->
                    <div class="flex justify-between text-base font-semibold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700">
                        <span>Total</span>
                        <span>{{ formatBookingAmount(booking.total_price) }}</span>
                    </div>
                </div>
            </div>

            <!-- Cancellation Policy -->
            <div v-if="booking.accommodation.cancellation_policy" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Cancellation policy</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ booking.accommodation.cancellation_policy }}</p>
            </div>

            <!-- Guest Notes -->
            <div v-if="booking.guest_notes" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Your notes</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.guest_notes }}</p>
            </div>
        </div>
    </div>
</template>

<script>
import apiClient from '@/services/apiClient';

export default {
    name: 'BookingDetailPage',

    data() {
        return {
            booking: null,
            loading: true,
            error: null,
        };
    },

    computed: {
        isConfirmed() {
            return this.booking?.status === 'confirmed';
        },
        statusBadgeClass() {
            const status = this.booking?.status;
            const map = {
                confirmed: 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300',
                pending: 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300',
                cancelled: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
                declined: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
            };
            return map[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300';
        },
        bookingTypeLabel() {
            const type = this.booking?.booking_type;
            if (type === 'instant_booking') return 'Instant booking';
            if (type === 'request_to_book') return 'Request to book';
            return type || '';
        },
        currency() {
            return this.booking?.currency || 'EUR';
        },
    },

    methods: {
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        },
        formatBookingAmount(amount) {
            const symbol = { EUR: '€', USD: '$', GBP: '£', RSD: 'дин' }[this.currency] || this.currency;
            return `${symbol}${Number(amount || 0).toFixed(2)}`;
        },

        async fetchBooking() {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.bookings[this.$route.params.id].get();
                this.booking = response.data.data;
            } catch (err) {
                this.error = err?.error?.message || err?.message || 'Booking not found.';
            } finally {
                this.loading = false;
            }
        },
    },

    created() {
        this.fetchBooking();
    },
};
</script>
