<template>
    <div class="max-w-4xl mx-auto px-4 pt-6 md:py-12">
        <div class="mb-6 md:mb-8 flex items-start md:items-center justify-between gap-3">
            <div>
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">{{ $t('title') }}</h1>
                <p class="text-base text-gray-600 dark:text-gray-400">{{ $t('subtitle') }}</p>
            </div>
        </div>

        <!-- Status filter -->
        <div class="flex flex-wrap gap-2 mb-6">
            <BaseButton
                v-for="filter in statusFilters"
                :key="filter.value"
                :variant="activeStatus === filter.value ? 'primary' : 'secondary'"
                size="sm"
                @click="setStatus(filter.value)"
            >
                {{ filter.label }}
            </BaseButton>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="animate-pulse h-28 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
        </div>

        <!-- Error -->
        <fwb-alert v-else-if="error" type="danger">{{ error }}</fwb-alert>

        <!-- Empty state -->
        <div
            v-else-if="bookings.length === 0"
            class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
        >
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">{{ $t('no_bookings') }}</p>
            <p class="text-gray-500 dark:text-gray-500 text-sm mt-1">{{ $t('no_bookings_desc') }}</p>
        </div>

        <!-- Bookings list -->
        <div v-else class="space-y-4">
            <router-link
                v-for="booking in bookings"
                :key="booking.id"
                :to="{ name: 'page-host-booking-show', params: { bookingId: booking.id } }"
                class="flex gap-0 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow"
            >
                <!-- Photo -->
                <div class="w-32 flex-shrink-0 bg-gray-100 dark:bg-gray-700">
                    <img
                        v-if="booking.accommodation?.primary_photo_url"
                        :src="booking.accommodation.primary_photo_url"
                        :alt="booking.accommodation?.title"
                        class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                            {{ booking.guest?.name || $t('guest_fallback') }} &middot;
                            {{ formatDate(booking.check_in) }} – {{ formatDate(booking.check_out) }}
                            &middot; {{ $tc('accommodation.nights', booking.nights, { count: booking.nights }) }}
                        </p>
                    </div>
                    <div class="flex items-center justify-between mt-3">
                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', statusClass(booking.status)]">
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
</template>

<script>
import apiClient from '@/services/apiClient';

export default {
    name: 'HostBookingsPage',

    data() {
        return {
            bookings: [],
            meta: {},
            loading: true,
            error: null,
            activeStatus: null,
        };
    },

    computed: {
        statusFilters() {
            return [
                { label: this.$t('filter_all'), value: null },
                { label: this.$t('filter_pending'), value: 'pending' },
                { label: this.$t('filter_confirmed'), value: 'confirmed' },
                { label: this.$t('filter_cancelled'), value: 'cancelled' },
                { label: this.$t('filter_declined'), value: 'declined' },
                { label: this.$t('filter_completed'), value: 'completed' },
            ];
        },
    },

    methods: {
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
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

        setStatus(status) {
            this.activeStatus = status;
            this.fetchBookings();
        },

        async fetchBookings(page = 1) {
            this.loading = true;
            this.error = null;
            try {
                const params = { page };
                if (this.activeStatus) {
                    params.status = this.activeStatus;
                }
                const response = await apiClient['host/bookings'].query(params).get();
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
  title: Bookings
  subtitle: Manage reservations across your properties.
  no_bookings: No bookings yet
  no_bookings_desc: Reservations from guests will appear here.
  filter_all: All
  filter_pending: Pending
  filter_confirmed: Confirmed
  filter_cancelled: Cancelled
  filter_declined: Declined
  filter_completed: Completed
  guest_fallback: Guest
  accommodation_fallback: Accommodation
  load_error: Failed to load bookings.

sr:
  title: Rezervacije
  subtitle: Upravljajte rezervacijama na vašim nekretninama.
  no_bookings: Nema rezervacija
  no_bookings_desc: Rezervacije od gostiju će se pojaviti ovde.
  filter_all: Sve
  filter_pending: Na čekanju
  filter_confirmed: Potvrđene
  filter_cancelled: Otkazane
  filter_declined: Odbijene
  filter_completed: Završene
  guest_fallback: Gost
  accommodation_fallback: Smeštaj
  load_error: Greška pri učitavanju rezervacija.

hr:
  title: Rezervacije
  subtitle: Upravljajte rezervacijama na vašim nekretninama.
  no_bookings: Nema rezervacija
  no_bookings_desc: Rezervacije od gostiju pojavit će se ovdje.
  filter_all: Sve
  filter_pending: Na čekanju
  filter_confirmed: Potvrđene
  filter_cancelled: Otkazane
  filter_declined: Odbijene
  filter_completed: Završene
  guest_fallback: Gost
  accommodation_fallback: Smještaj
  load_error: Greška pri učitavanju rezervacija.

mk:
  title: Резервации
  subtitle: Управувајте со резервациите на вашите имоти.
  no_bookings: Нема резервации
  no_bookings_desc: Резервациите од гостите ќе се појават овде.
  filter_all: Сите
  filter_pending: На чекање
  filter_confirmed: Потврдени
  filter_cancelled: Откажани
  filter_declined: Одбиени
  filter_completed: Завршени
  guest_fallback: Гостин
  accommodation_fallback: Сместување
  load_error: Грешка при вчитување на резервациите.

sl:
  title: Rezervacije
  subtitle: Upravljajte rezervacijami na svojih nepremičninah.
  no_bookings: Ni rezervacij
  no_bookings_desc: Tukaj se bodo pojavile rezervacije gostov.
  filter_all: Vse
  filter_pending: V čakanju
  filter_confirmed: Potrjene
  filter_cancelled: Preklicane
  filter_declined: Zavrnjene
  filter_completed: Zaključene
  guest_fallback: Gost
  accommodation_fallback: Nastanitev
  load_error: Napaka pri nalaganju rezervacij.
</i18n>
