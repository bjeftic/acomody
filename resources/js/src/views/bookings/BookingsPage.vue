<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ $t('title') }}</h1>

            <!-- Loading -->
            <div v-if="loading" class="space-y-4">
                <div
                    v-for="i in 3"
                    :key="i"
                    class="animate-pulse h-28 bg-gray-200 dark:bg-gray-700 rounded-xl"
                ></div>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="px-4 py-3 rounded-xl text-sm border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400">
                {{ error }}
            </div>

            <!-- Empty state -->
            <div
                v-else-if="bookings.length === 0"
                class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
            >
                <svg
                    class="w-12 h-12 mx-auto text-gray-400 mb-4"
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
                <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">{{ $t('no_bookings') }}</p>
                <p class="text-gray-500 dark:text-gray-500 text-sm mt-1">{{ $t('no_bookings_desc') }}</p>
                <router-link
                    :to="{ name: 'page-search' }"
                    class="mt-4 inline-block text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline"
                >
                    {{ $t('find_stay') }}
                </router-link>
            </div>

            <!-- Bookings list -->
            <div v-else class="space-y-4">
                <router-link
                    v-for="booking in bookings"
                    :key="booking.id"
                    :to="{ name: 'booking-detail', params: { id: booking.id } }"
                    class="flex gap-0 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow"
                >
                    <!-- Accommodation photo -->
                    <div class="w-32 flex-shrink-0 bg-gray-100 dark:bg-gray-700">
                        <img
                            v-if="booking.accommodation?.primary_photo_url"
                            :src="booking.accommodation.primary_photo_url"
                            :alt="booking.accommodation?.title"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center text-gray-400"
                        >
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex flex-1 flex-col justify-between p-4 min-w-0">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white truncate">
                                {{ booking.accommodation?.title || $t('accommodation_fallback') }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ formatDate(booking.check_in) }} – {{ formatDate(booking.check_out) }}
                                · {{ $tc('accommodation.nights', booking.nights, { count: booking.nights }) }}
                                · {{ $tc('accommodation.guests', booking.guests, { count: booking.guests }) }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    statusClass(booking.status),
                                ]"
                            >
                                {{ booking.status_label || booking.status }}
                            </span>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ formatAmount(booking.total_price, booking.currency) }}
                            </p>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="mt-6 flex justify-center gap-2 flex-wrap">
                <BaseButton
                    v-for="page in meta.last_page"
                    :key="page"
                    :variant="page === meta.current_page ? 'primary' : 'secondary'"
                    size="sm"
                    @click="fetchBookings(page)"
                >
                    {{ page }}
                </BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
import apiClient from '@/services/apiClient';

export default {
    name: 'BookingsPage',

    data() {
        return {
            bookings: [],
            meta: {},
            loading: true,
            error: null,
        };
    },

    methods: {
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            });
        },

        formatAmount(amount, currency) {
            const symbol = { EUR: '€', USD: '$', GBP: '£', RSD: 'дин' }[currency] || currency;
            return `${symbol}${Number(amount || 0).toFixed(2)}`;
        },

        statusClass(status) {
            const map = {
                confirmed: 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300',
                pending: 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300',
                cancelled: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
                declined: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
                completed: 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
            };
            return map[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300';
        },

        async fetchBookings(page = 1) {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.bookings.query({ page }).get();
                this.bookings = response.data.data;
                this.meta = response.data.meta || {};
            } catch (err) {
                this.error = err?.error?.message || err?.message || this.$t('load_error');
            } finally {
                this.loading = false;
            }
        },
    },

    created() {
        this.fetchBookings();
    },
};
</script>

<i18n lang="yaml">
en:
  title: My Bookings
  no_bookings: No bookings yet
  no_bookings_desc: When you make a reservation, it will appear here.
  find_stay: Find a place to stay
  load_error: Failed to load bookings.
  accommodation_fallback: Accommodation

sr:
  title: Moje rezervacije
  no_bookings: Nema rezervacija
  no_bookings_desc: Kada napravite rezervaciju, pojaviće se ovde.
  find_stay: Pronađite smeštaj
  load_error: Greška pri učitavanju rezervacija.
  accommodation_fallback: Smeštaj

hr:
  title: Moje rezervacije
  no_bookings: Nema rezervacija
  no_bookings_desc: Kad napravite rezervaciju, pojavit će se ovdje.
  find_stay: Pronađite smještaj
  load_error: Greška pri učitavanju rezervacija.
  accommodation_fallback: Smještaj

mk:
  title: Мои резервации
  no_bookings: Нема резервации
  no_bookings_desc: Кога ќе направите резервација, ќе се појави овде.
  find_stay: Пронајдете сместување
  load_error: Грешка при вчитување на резервациите.
  accommodation_fallback: Сместување

sl:
  title: Moje rezervacije
  no_bookings: Ni rezervacij
  no_bookings_desc: Ko naredite rezervacijo, se bo pojavila tukaj.
  find_stay: Poiščite nastanitev
  load_error: Napaka pri nalaganju rezervacij.
  accommodation_fallback: Nastanitev
</i18n>
