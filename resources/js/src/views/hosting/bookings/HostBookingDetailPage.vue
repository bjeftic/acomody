<template>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Loading -->
        <div v-if="loading" class="animate-pulse space-y-6">
            <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
            <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
            <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="px-4 py-3 rounded-xl text-sm border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400">{{ error }}</div>

        <template v-else-if="booking">
            <!-- Back -->
            <button
                @click="$router.back()"
                class="flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to bookings
            </button>

            <!-- Status banner -->
            <div :class="['rounded-xl p-6 border', bannerClass]">
                <div class="flex items-center gap-3">
                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', statusBadgeClass]">
                        {{ booking.status_label || booking.status }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Booking #{{ booking.id.slice(-8).toUpperCase() }}
                    </span>
                </div>
            </div>

            <!-- Guest info -->
            <div v-if="booking.guest" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Guest</h3>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.guest.name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ booking.guest.email }}</p>
            </div>

            <!-- Accommodation -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div v-if="booking.accommodation?.primary_photo_url" class="h-40 w-full">
                    <img :src="booking.accommodation.primary_photo_url" :alt="booking.accommodation.title" class="w-full h-full object-cover" />
                </div>
                <div class="p-6">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ booking.accommodation?.title }}</p>
                    <p v-if="booking.accommodation?.address" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ booking.accommodation.address }}</p>
                </div>
            </div>

            <!-- Stay details -->
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
            </div>

            <!-- Price -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Pricing</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>{{ booking.nights }} night{{ booking.nights !== 1 ? 's' : '' }}</span>
                        <span>{{ formatAmount(booking.subtotal) }}</span>
                    </div>
                    <div v-if="booking.fees_total > 0" class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>Fees</span>
                        <span>{{ formatAmount(booking.fees_total) }}</span>
                    </div>
                    <div v-if="booking.taxes_total > 0" class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>Taxes</span>
                        <span>{{ formatAmount(booking.taxes_total) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span>Total</span>
                        <span>{{ formatAmount(booking.total_price) }}</span>
                    </div>
                </div>
            </div>

            <!-- Guest notes -->
            <div v-if="booking.guest_notes" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Guest notes</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.guest_notes }}</p>
            </div>

            <!-- Decline/cancel reason -->
            <div v-if="booking.decline_reason || booking.cancellation_reason" class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-6">
                <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">
                    {{ booking.status === 'declined' ? 'Decline reason' : 'Cancellation reason' }}
                </h3>
                <p class="text-sm text-red-700 dark:text-red-300">
                    {{ booking.decline_reason || booking.cancellation_reason }}
                </p>
                <p v-if="booking.refund_amount > 0" class="text-sm text-red-700 dark:text-red-300 mt-2">
                    Refund: {{ formatAmount(booking.refund_amount) }}
                </p>
            </div>

            <!-- Actions -->
            <div v-if="booking.status === 'pending'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Actions</h3>

                <div v-if="actionError" class="px-4 py-3 rounded-xl text-sm border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400">{{ actionError }}</div>

                <!-- Confirm -->
                <div v-if="!showDeclineForm && !showCancelForm">
                    <div class="flex flex-wrap gap-3">
                        <BaseButton variant="primary" :disabled="actionLoading" @click="confirmBooking">
                            {{ actionLoading === 'confirm' ? 'Confirming…' : 'Confirm booking' }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="actionLoading" @click="showDeclineForm = true">
                            Decline
                        </BaseButton>
                        <BaseButton variant="danger" :disabled="actionLoading" @click="showCancelForm = true">
                            Cancel booking
                        </BaseButton>
                    </div>
                </div>

                <!-- Decline form -->
                <div v-if="showDeclineForm" class="space-y-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Decline this booking request?</p>
                    <textarea
                        v-model="declineReason"
                        rows="3"
                        maxlength="500"
                        placeholder="Reason for declining (optional)"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none resize-none"
                    ></textarea>
                    <div class="flex gap-3">
                        <BaseButton variant="danger" :disabled="actionLoading" @click="declineBooking">
                            {{ actionLoading === 'decline' ? 'Declining…' : 'Yes, decline' }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="actionLoading" @click="showDeclineForm = false">
                            Back
                        </BaseButton>
                    </div>
                </div>

                <!-- Cancel form (pending) -->
                <div v-if="showCancelForm" class="space-y-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Cancel this booking request?</p>
                    <textarea
                        v-model="cancelReason"
                        rows="3"
                        maxlength="500"
                        placeholder="Reason for cancellation (optional)"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none resize-none"
                    ></textarea>
                    <div class="flex gap-3">
                        <BaseButton variant="danger" :disabled="actionLoading" @click="cancelBooking">
                            {{ actionLoading === 'cancel' ? 'Cancelling…' : 'Yes, cancel' }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="actionLoading" @click="showCancelForm = false">
                            Back
                        </BaseButton>
                    </div>
                </div>
            </div>

            <!-- Cancel confirmed booking -->
            <div v-if="booking.status === 'confirmed'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cancel booking</h3>

                <div v-if="actionError" class="px-4 py-3 rounded-xl text-sm border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400">{{ actionError }}</div>

                <div v-if="!showCancelForm">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        You can cancel this confirmed booking. The guest will be notified.
                    </p>
                    <BaseButton variant="danger" :disabled="actionLoading" @click="showCancelForm = true">
                        Cancel booking
                    </BaseButton>
                </div>

                <div v-else class="space-y-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Are you sure you want to cancel this booking?</p>
                    <textarea
                        v-model="cancelReason"
                        rows="3"
                        maxlength="500"
                        placeholder="Reason for cancellation (optional)"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none resize-none"
                    ></textarea>
                    <div class="flex gap-3">
                        <BaseButton variant="danger" :disabled="actionLoading" @click="cancelBooking">
                            {{ actionLoading === 'cancel' ? 'Cancelling…' : 'Yes, cancel' }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="actionLoading" @click="showCancelForm = false">
                            Keep booking
                        </BaseButton>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import apiClient from '@/services/apiClient';

export default {
    name: 'HostBookingDetailPage',

    data() {
        return {
            booking: null,
            loading: true,
            error: null,
            actionLoading: null,
            actionError: null,
            showDeclineForm: false,
            showCancelForm: false,
            declineReason: '',
            cancelReason: '',
        };
    },

    computed: {
        bannerClass() {
            const map = {
                confirmed: 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
                pending: 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                cancelled: 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                declined: 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                completed: 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600',
            };
            return map[this.booking?.status] || 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600';
        },
        statusBadgeClass() {
            const map = {
                confirmed: 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300',
                pending: 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300',
                cancelled: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
                declined: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
                completed: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
            };
            return map[this.booking?.status] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300';
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

        formatAmount(amount) {
            const symbol = { EUR: '€', USD: '$', GBP: '£', RSD: 'дин' }[this.currency] || this.currency;
            return `${symbol}${Number(amount || 0).toFixed(2)}`;
        },

        async fetchBooking() {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient['host/bookings'][this.$route.params.bookingId].get();
                this.booking = response.data.data;
            } catch (err) {
                this.error = err?.error?.message || err?.message || 'Booking not found.';
            } finally {
                this.loading = false;
            }
        },

        async confirmBooking() {
            this.actionLoading = 'confirm';
            this.actionError = null;
            try {
                const response = await apiClient['host/bookings'][this.$route.params.bookingId].confirm.post();
                this.booking = response.data.data;
            } catch (err) {
                this.actionError = err?.error?.message || err?.message || 'Failed to confirm booking.';
            } finally {
                this.actionLoading = null;
            }
        },

        async declineBooking() {
            this.actionLoading = 'decline';
            this.actionError = null;
            try {
                const response = await apiClient['host/bookings'][this.$route.params.bookingId].decline.post({
                    reason: this.declineReason || undefined,
                });
                this.booking = response.data.data;
                this.showDeclineForm = false;
            } catch (err) {
                this.actionError = err?.error?.message || err?.message || 'Failed to decline booking.';
            } finally {
                this.actionLoading = null;
            }
        },

        async cancelBooking() {
            this.actionLoading = 'cancel';
            this.actionError = null;
            try {
                const response = await apiClient['host/bookings'][this.$route.params.bookingId].cancel.post({
                    reason: this.cancelReason || undefined,
                });
                this.booking = response.data.data;
                this.showCancelForm = false;
            } catch (err) {
                this.actionError = err?.error?.message || err?.message || 'Failed to cancel booking.';
            } finally {
                this.actionLoading = null;
            }
        },
    },

    created() {
        this.fetchBooking();
    },
};
</script>
