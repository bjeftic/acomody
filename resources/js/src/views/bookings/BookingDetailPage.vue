<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Loading State -->
        <div v-if="loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="animate-pulse space-y-6">
                <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="px-4 py-3 rounded-xl text-sm border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400">
                {{ error }}
            </div>
        </div>

        <!-- Main Content -->
        <div v-else-if="booking" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Back -->
            <button
                @click="$router.back()"
                class="flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ $t('common.back') }}
            </button>

            <!-- Success Banner -->
            <div
                :class="[
                    'rounded-xl p-6 border',
                    isConfirmed
                        ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
                        : 'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800',
                ]"
            >
                <div class="flex items-start gap-4">
                    <div :class="['rounded-full p-2', isConfirmed ? 'bg-green-100 dark:bg-green-800' : 'bg-primary-100 dark:bg-primary-800']">
                        <svg v-if="isConfirmed" class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-6 h-6 text-primary-600 dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 :class="['text-xl font-bold', isConfirmed ? 'text-green-900 dark:text-green-100' : 'text-primary-900 dark:text-primary-100']">
                            {{ isConfirmed ? $t('booking_confirmed_title') : $t('booking_request_sent_title') }}
                        </h1>
                        <p :class="['text-sm mt-1', isConfirmed ? 'text-green-700 dark:text-green-300' : 'text-primary-700 dark:text-primary-300']">
                            {{ isConfirmed ? $t('booking_confirmed_desc') : $t('booking_request_sent_desc') }}
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('stay_details') }}</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $t('booking.check_in') }}</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ formatDate(booking.check_in) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $t('booking.check_out') }}</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ formatDate(booking.check_out) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $t('booking.guests') }}</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ booking.guests }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $t('nights_label') }}</p>
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $t('price_breakdown') }}</h3>
                <div class="space-y-3">
                    <!-- Nightly subtotal -->
                    <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>{{ $t('accommodation.nights', { n: booking.nights, count: booking.nights }) }}</span>
                        <div class="text-right">
                            <div>{{ formatUserAmount(getDisplayAmount(booking.subtotal, userCurrencyPricing?.subtotal)) }}</div>
                            <div v-if="showUserCurrency" class="text-xs text-gray-400 dark:text-gray-500">{{ formatBookingAmount(booking.subtotal) }}</div>
                        </div>
                    </div>

                    <!-- Bulk discount -->
                    <div v-if="booking.price_details?.bulk_discount" class="flex justify-between text-sm text-green-600 dark:text-green-400">
                        <span>{{ $t('reservation.long_stay_discount') }}</span>
                        <div class="text-right">
                            <div>-{{ formatUserAmount(getDisplayAmount(booking.price_details.bulk_discount.amount, userCurrencyPricing?.bulk_discount_amount)) }}</div>
                            <div v-if="showUserCurrency" class="text-xs text-green-400/70 dark:text-green-500/70">-{{ formatBookingAmount(booking.price_details.bulk_discount.amount) }}</div>
                        </div>
                    </div>

                    <!-- Mandatory fees -->
                    <template v-if="booking.price_details?.fees?.mandatory">
                        <div
                            v-for="(fee, i) in booking.price_details.fees.mandatory"
                            :key="fee.name"
                            class="flex justify-between text-sm text-gray-700 dark:text-gray-300"
                        >
                            <span>{{ fee.name }}</span>
                            <div class="text-right">
                                <div>{{ formatUserAmount(getDisplayAmount(fee.amount, userCurrencyPricing?.fees_mandatory?.[i]?.amount)) }}</div>
                                <div v-if="showUserCurrency" class="text-xs text-gray-400 dark:text-gray-500">{{ formatBookingAmount(fee.amount) }}</div>
                            </div>
                        </div>
                    </template>

                    <!-- Taxes -->
                    <template v-if="booking.price_details?.taxes">
                        <div
                            v-for="(tax, i) in booking.price_details.taxes"
                            :key="tax.name"
                            class="flex justify-between text-sm text-gray-700 dark:text-gray-300"
                        >
                            <span>{{ tax.name }}</span>
                            <div class="text-right">
                                <div>{{ formatUserAmount(getDisplayAmount(tax.amount, userCurrencyPricing?.taxes?.[i]?.amount)) }}</div>
                                <div v-if="showUserCurrency" class="text-xs text-gray-400 dark:text-gray-500">{{ formatBookingAmount(tax.amount) }}</div>
                            </div>
                        </div>
                    </template>

                    <!-- Total -->
                    <div class="flex justify-between text-base font-semibold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700">
                        <span>{{ $t('accommodation.total') }}</span>
                        <div class="text-right">
                            <div>{{ formatUserAmount(getDisplayAmount(booking.total_price, userCurrencyPricing?.total_price)) }}</div>
                            <div v-if="showUserCurrency" class="text-xs font-normal text-gray-400 dark:text-gray-500">{{ formatBookingAmount(booking.total_price) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Policy -->
            <div v-if="booking.accommodation.cancellation_policy" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $t('accommodation.cancellation_policy') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ booking.accommodation.cancellation_policy }}</p>
            </div>

            <!-- Guest Notes -->
            <div v-if="booking.guest_notes" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $t('your_notes') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.guest_notes }}</p>
            </div>

            <!-- Cancellation info (after cancellation) -->
            <div v-if="booking.status === 'cancelled'" class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-6">
                <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">{{ $t('cancelled_title') }}</h3>
                <p v-if="booking.cancellation_reason" class="text-sm text-red-700 dark:text-red-300 mb-2">
                    {{ $t('reason_label') }} {{ booking.cancellation_reason }}
                </p>
                <p v-if="booking.refund_amount > 0" class="text-sm text-red-700 dark:text-red-300">
                    {{ $t('refund_label') }} {{ formatBookingAmount(booking.refund_amount) }}
                </p>
                <p v-else class="text-sm text-red-700 dark:text-red-300">{{ $t('no_refund') }}</p>
            </div>

            <!-- Cancel booking section -->
            <div v-if="canCancel" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $t('cancel_section_title') }}</h3>

                <div v-if="!showCancelConfirm">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $t('cancel_prompt') }}</p>
                    <BaseButton variant="danger" @click="showCancelConfirm = true">{{ $t('cancel_section_title') }}</BaseButton>
                </div>

                <div v-else class="space-y-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $t('cancel_confirm') }}</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('cancel_reason_label') }}
                        </label>
                        <textarea
                            v-model="cancelReason"
                            rows="3"
                            maxlength="500"
                            :placeholder="$t('cancel_reason_placeholder')"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none resize-none"
                        ></textarea>
                    </div>
                    <div v-if="cancelError" class="px-4 py-3 rounded-xl text-sm border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400">
                        {{ cancelError }}
                    </div>
                    <div class="flex gap-3">
                        <BaseButton variant="danger" :disabled="cancelling" @click="cancelBooking">
                            {{ cancelling ? $t('cancelling') : $t('yes_cancel') }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="cancelling" @click="showCancelConfirm = false">
                            {{ $t('keep_booking') }}
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import apiClient from '@/services/apiClient';
import { formatPrice } from '@/utils/helpers';

export default {
    name: 'BookingDetailPage',

    data() {
        return {
            booking: null,
            loading: true,
            error: null,
            showCancelConfirm: false,
            cancelReason: '',
            cancelling: false,
            cancelError: null,
        };
    },

    computed: {
        ...mapState('ui', ['currencies', 'selectedCurrency']),
        isConfirmed() {
            return this.booking?.status === 'confirmed';
        },
        canCancel() {
            return this.booking?.status === 'pending' || this.booking?.status === 'confirmed';
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
            if (type === 'instant_booking') return this.$t('accommodation.instant_book');
            if (type === 'request_to_book') return this.$t('request_to_book');
            return type || '';
        },
        currency() {
            return this.booking?.currency || 'EUR';
        },
        userCurrencyPricing() {
            return this.booking?.pricing_in_user_currency || null;
        },
        showUserCurrency() {
            return this.userCurrencyPricing !== null;
        },
        displayCurrency() {
            return this.showUserCurrency ? this.userCurrencyPricing.currency : this.currency;
        },
    },

    methods: {
        formatDate(dateStr) {
            if (!dateStr) return '—';
            return new Date(dateStr).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        },
        getCurrencyObject(code) {
            return this.currencies.find((c) => c.code === code) || null;
        },
        formatBookingAmount(amount) {
            return formatPrice(Number(amount || 0), this.getCurrencyObject(this.currency), true, 'symbol');
        },
        formatUserAmount(amount) {
            return formatPrice(Number(amount || 0), this.getCurrencyObject(this.displayCurrency), true, 'symbol');
        },
        getDisplayAmount(originalAmount, userAmount) {
            return this.showUserCurrency ? userAmount : originalAmount;
        },

        async fetchBooking() {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient.bookings[this.$route.params.id].get();
                this.booking = response.data.data;
            } catch (err) {
                this.error = err?.error?.message || err?.message || this.$t('booking_not_found');
            } finally {
                this.loading = false;
            }
        },

        async cancelBooking() {
            this.cancelling = true;
            this.cancelError = null;
            try {
                const response = await apiClient.bookings[this.$route.params.id].cancel.post({
                    reason: this.cancelReason || undefined,
                });
                this.booking = response.data.data;
                this.showCancelConfirm = false;
            } catch (err) {
                this.cancelError = err?.error?.message || err?.message || this.$t('cancel_failed');
            } finally {
                this.cancelling = false;
            }
        },
    },

    created() {
        this.fetchBooking();
    },
};
</script>

<i18n lang="yaml">
en:
  booking_confirmed_title: "Booking confirmed!"
  booking_request_sent_title: "Booking request sent!"
  booking_confirmed_desc: Your reservation is confirmed. Check your email for details.
  booking_request_sent_desc: "Your request has been sent to the host. You'll hear back soon."
  stay_details: Stay details
  nights_label: Nights
  price_breakdown: Price breakdown
  your_notes: Your notes
  cancelled_title: Booking cancelled
  reason_label: "Reason:"
  refund_label: "Refund:"
  no_refund: No refund applicable.
  cancel_section_title: Cancel booking
  cancel_prompt: Need to cancel? Please review the cancellation policy before proceeding.
  cancel_confirm: Are you sure you want to cancel this booking?
  cancel_reason_label: Reason (optional)
  cancel_reason_placeholder: "Let the host know why you're cancelling..."
  cancelling: "Cancelling…"
  yes_cancel: "Yes, cancel booking"
  keep_booking: Keep booking
  request_to_book: Request to book
  booking_not_found: Booking not found.
  cancel_failed: Failed to cancel booking.

sr:
  booking_confirmed_title: "Rezervacija potvrđena!"
  booking_request_sent_title: "Zahtev za rezervaciju je poslat!"
  booking_confirmed_desc: Vaša rezervacija je potvrđena. Proverite email za detalje.
  booking_request_sent_desc: Vaš zahtev je poslat domaćinu. Uskoro ćete dobiti odgovor.
  stay_details: Detalji boravka
  nights_label: Noći
  price_breakdown: Pregled cene
  your_notes: Vaše napomene
  cancelled_title: Rezervacija otkazana
  reason_label: "Razlog:"
  refund_label: "Povrat novca:"
  no_refund: Povrat novca nije primenjiv.
  cancel_section_title: Otkažite rezervaciju
  cancel_prompt: Treba da otkažete? Pregledajte politiku otkazivanja pre nego što nastavite.
  cancel_confirm: Da li ste sigurni da želite da otkažete ovu rezervaciju?
  cancel_reason_label: Razlog (opcionalno)
  cancel_reason_placeholder: Obavestite domaćina zašto otkazujete...
  cancelling: "Otkazivanje…"
  yes_cancel: "Da, otkažite rezervaciju"
  keep_booking: Zadržite rezervaciju
  request_to_book: Zahtev za rezervaciju
  booking_not_found: Rezervacija nije pronađena.
  cancel_failed: Greška pri otkazivanju rezervacije.

hr:
  booking_confirmed_title: "Rezervacija potvrđena!"
  booking_request_sent_title: "Zahtjev za rezervaciju je poslan!"
  booking_confirmed_desc: Vaša rezervacija je potvrđena. Provjerite email za detalje.
  booking_request_sent_desc: Vaš zahtjev je poslan domaćinu. Uskoro ćete dobiti odgovor.
  stay_details: Detalji boravka
  nights_label: Noći
  price_breakdown: Pregled cijene
  your_notes: Vaše napomene
  cancelled_title: Rezervacija otkazana
  reason_label: "Razlog:"
  refund_label: "Povrat novca:"
  no_refund: Povrat novca nije primjenjiv.
  cancel_section_title: Otkažite rezervaciju
  cancel_prompt: Trebate otkazati? Pregledajte politiku otkazivanja prije nastavka.
  cancel_confirm: Jeste li sigurni da želite otkazati ovu rezervaciju?
  cancel_reason_label: Razlog (opcionalno)
  cancel_reason_placeholder: Obavijestite domaćina zašto otkazujete...
  cancelling: "Otkazivanje…"
  yes_cancel: "Da, otkažite rezervaciju"
  keep_booking: Zadržite rezervaciju
  request_to_book: Zahtjev za rezervaciju
  booking_not_found: Rezervacija nije pronađena.
  cancel_failed: Greška pri otkazivanju rezervacije.

mk:
  booking_confirmed_title: "Резервацијата е потврдена!"
  booking_request_sent_title: "Барањето за резервација е пратено!"
  booking_confirmed_desc: Вашата резервација е потврдена. Проверете го вашиот email за деталите.
  booking_request_sent_desc: Вашето барање е пратено до домаќинот. Наскоро ќе добиете одговор.
  stay_details: Детали за престојот
  nights_label: Ноќи
  price_breakdown: Преглед на цената
  your_notes: Вашите белешки
  cancelled_title: Резервацијата е откажана
  reason_label: "Причина:"
  refund_label: "Поврат:"
  no_refund: Враќање на пари не е применливо.
  cancel_section_title: Откажете ја резервацијата
  cancel_prompt: Треба да откажете? Прегледајте ја политиката за откажување пред да продолжите.
  cancel_confirm: Дали сте сигурни дека сакате да ја откажете оваа резервација?
  cancel_reason_label: Причина (опционално)
  cancel_reason_placeholder: Кажете му на домаќинот зошто откажувате...
  cancelling: "Откажување…"
  yes_cancel: "Да, откажете ја резервацијата"
  keep_booking: Задржете ја резервацијата
  request_to_book: Барање за резервација
  booking_not_found: Резервацијата не е пронајдена.
  cancel_failed: Грешка при откажување на резервацијата.

sl:
  booking_confirmed_title: "Rezervacija potrjena!"
  booking_request_sent_title: "Zahteva za rezervacijo je bila poslana!"
  booking_confirmed_desc: Vaša rezervacija je potrjena. Preverite e-pošto za podrobnosti.
  booking_request_sent_desc: Vaša zahteva je bila poslana gostitelju. Kmalu boste dobili odgovor.
  stay_details: Podrobnosti o bivanju
  nights_label: Noči
  price_breakdown: Pregled cene
  your_notes: Vaše opombe
  cancelled_title: Rezervacija preklicana
  reason_label: "Razlog:"
  refund_label: "Povračilo:"
  no_refund: Povračilo ni na voljo.
  cancel_section_title: Prekličite rezervacijo
  cancel_prompt: Morate preklicati? Pred nadaljevanjem preglejte politiko preklica.
  cancel_confirm: Ste prepričani, da želite preklicati to rezervacijo?
  cancel_reason_label: Razlog (neobvezno)
  cancel_reason_placeholder: Sporočite gostitelju, zakaj preklicujete...
  cancelling: "Preklic…"
  yes_cancel: "Da, prekličite rezervacijo"
  keep_booking: Obdržite rezervacijo
  request_to_book: Zahteva za rezervacijo
  booking_not_found: Rezervacija ni bila najdena.
  cancel_failed: Napaka pri preklicu rezervacije.
</i18n>
