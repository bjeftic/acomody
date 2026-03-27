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
                {{ $t('back') }}
            </button>

            <!-- Status banner -->
            <div :class="['rounded-xl p-6 border', bannerClass]">
                <div class="flex items-center gap-3">
                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', statusBadgeClass]">
                        {{ booking.status_label || booking.status }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $t('booking_hash') }}{{ booking.id.slice(-8).toUpperCase() }}
                    </span>
                </div>
            </div>

            <!-- Guest info -->
            <div v-if="booking.guest" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ $t('guest_section') }}</h3>
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
            </div>

            <!-- Price -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">{{ $t('pricing') }}</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>{{ $t('accommodation.nights', { n: booking.nights, count: booking.nights }) }}</span>
                        <span>{{ formatAmount(booking.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span>{{ $t('accommodation.total') }}</span>
                        <span>{{ formatAmount(booking.total_price) }}</span>
                    </div>
                </div>
            </div>

            <!-- Guest notes -->
            <div v-if="booking.guest_notes" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $t('guest_notes') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.guest_notes }}</p>
            </div>

            <!-- Decline/cancel reason -->
            <div v-if="booking.decline_reason || booking.cancellation_reason" class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-6">
                <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">
                    {{ booking.status === 'declined' ? $t('decline_reason_title') : $t('cancel_reason_title') }}
                </h3>
                <p class="text-sm text-red-700 dark:text-red-300">
                    {{ booking.decline_reason || booking.cancellation_reason }}
                </p>
                <p v-if="booking.refund_amount > 0" class="text-sm text-red-700 dark:text-red-300 mt-2">
                    {{ $t('refund_label') }} {{ formatAmount(booking.refund_amount) }}
                </p>
            </div>

            <!-- Actions (pending booking) -->
            <div v-if="booking.status === 'pending'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('actions_title') }}</h3>

                <div v-if="actionError" class="px-4 py-3 rounded-xl text-sm border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400">{{ actionError }}</div>

                <!-- Action buttons -->
                <div v-if="!showDeclineForm && !showCancelForm">
                    <div class="flex flex-wrap gap-3">
                        <BaseButton variant="primary" :disabled="actionLoading" @click="confirmBooking">
                            {{ actionLoading === 'confirm' ? $t('confirming') : $t('confirm_booking') }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="actionLoading" @click="showDeclineForm = true">
                            {{ $t('decline') }}
                        </BaseButton>
                        <BaseButton variant="danger" :disabled="actionLoading" @click="showCancelForm = true">
                            {{ $t('cancel_booking_btn') }}
                        </BaseButton>
                    </div>
                </div>

                <!-- Decline form -->
                <div v-if="showDeclineForm" class="space-y-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('decline_form_prompt') }}</p>
                    <textarea
                        v-model="declineReason"
                        rows="3"
                        maxlength="500"
                        :placeholder="$t('decline_reason_placeholder')"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none resize-none"
                    ></textarea>
                    <div class="flex gap-3">
                        <BaseButton variant="danger" :disabled="actionLoading" @click="declineBooking">
                            {{ actionLoading === 'decline' ? $t('declining') : $t('yes_decline') }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="actionLoading" @click="showDeclineForm = false">
                            {{ $t('common.back') }}
                        </BaseButton>
                    </div>
                </div>

                <!-- Cancel form (pending) -->
                <div v-if="showCancelForm" class="space-y-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('cancel_request_form_prompt') }}</p>
                    <textarea
                        v-model="cancelReason"
                        rows="3"
                        maxlength="500"
                        :placeholder="$t('cancel_reason_placeholder')"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none resize-none"
                    ></textarea>
                    <div class="flex gap-3">
                        <BaseButton variant="danger" :disabled="actionLoading" @click="cancelBooking">
                            {{ actionLoading === 'cancel' ? $t('cancelling') : $t('yes_cancel') }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="actionLoading" @click="showCancelForm = false">
                            {{ $t('common.back') }}
                        </BaseButton>
                    </div>
                </div>
            </div>

            <!-- Cancel confirmed booking -->
            <div v-if="booking.status === 'confirmed'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('cancel_booking_btn') }}</h3>

                <div v-if="actionError" class="px-4 py-3 rounded-xl text-sm border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400">{{ actionError }}</div>

                <div v-if="!showCancelForm">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $t('cancel_confirmed_desc') }}</p>
                    <BaseButton variant="danger" :disabled="actionLoading" @click="showCancelForm = true">
                        {{ $t('cancel_booking_btn') }}
                    </BaseButton>
                </div>

                <div v-else class="space-y-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $t('cancel_confirm') }}</p>
                    <textarea
                        v-model="cancelReason"
                        rows="3"
                        maxlength="500"
                        :placeholder="$t('cancel_reason_placeholder')"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none resize-none"
                    ></textarea>
                    <div class="flex gap-3">
                        <BaseButton variant="danger" :disabled="actionLoading" @click="cancelBooking">
                            {{ actionLoading === 'cancel' ? $t('cancelling') : $t('yes_cancel') }}
                        </BaseButton>
                        <BaseButton variant="secondary" :disabled="actionLoading" @click="showCancelForm = false">
                            {{ $t('keep_booking') }}
                        </BaseButton>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import apiClient from '@/services/apiClient';
import { formatPrice } from '@/utils/helpers';

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
        ...mapState('ui', ['currencies']),
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

        getCurrencyObject(code) {
            return this.currencies.find((c) => c.code === code) || null;
        },
        formatAmount(amount) {
            return formatPrice(Number(amount || 0), this.getCurrencyObject(this.currency), true, 'symbol');
        },

        async fetchBooking() {
            this.loading = true;
            this.error = null;
            try {
                const response = await apiClient['host/bookings'][this.$route.params.bookingId].get();
                this.booking = response.data.data;
            } catch (err) {
                this.error = err?.error?.message || err?.message || this.$t('booking_not_found');
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
                this.actionError = err?.error?.message || err?.message || this.$t('confirm_failed');
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
                this.actionError = err?.error?.message || err?.message || this.$t('decline_failed');
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
                this.actionError = err?.error?.message || err?.message || this.$t('cancel_failed');
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

<i18n lang="yaml">
en:
  back: Back to bookings
  booking_hash: "Booking #"
  guest_section: Guest
  stay_details: Stay details
  nights_label: Nights
  pricing: Pricing
  guest_notes: Guest notes
  decline_reason_title: Decline reason
  cancel_reason_title: Cancellation reason
  refund_label: "Refund:"
  actions_title: Actions
  confirm_booking: Confirm booking
  confirming: "Confirming…"
  decline: Decline
  cancel_booking_btn: Cancel booking
  cancel_confirmed_desc: You can cancel this confirmed booking. The guest will be notified.
  decline_form_prompt: Decline this booking request?
  cancel_request_form_prompt: Cancel this booking request?
  cancel_confirm: Are you sure you want to cancel this booking?
  decline_reason_placeholder: Reason for declining (optional)
  cancel_reason_placeholder: Reason for cancellation (optional)
  declining: "Declining…"
  yes_decline: "Yes, decline"
  cancelling: "Cancelling…"
  yes_cancel: "Yes, cancel"
  keep_booking: Keep booking
  booking_not_found: Booking not found.
  confirm_failed: Failed to confirm booking.
  decline_failed: Failed to decline booking.
  cancel_failed: Failed to cancel booking.

sr:
  back: Nazad na rezervacije
  booking_hash: "Rezervacija #"
  guest_section: Gost
  stay_details: Detalji boravka
  nights_label: Noći
  pricing: Cene
  guest_notes: Napomene gosta
  decline_reason_title: Razlog odbijanja
  cancel_reason_title: Razlog otkazivanja
  refund_label: "Povrat novca:"
  actions_title: Akcije
  confirm_booking: Potvrdite rezervaciju
  confirming: "Potvrđivanje…"
  decline: Odbijte
  cancel_booking_btn: Otkažite rezervaciju
  cancel_confirmed_desc: Možete otkazati ovu potvrđenu rezervaciju. Gost će biti obavešten.
  decline_form_prompt: Odbiti ovaj zahtev za rezervaciju?
  cancel_request_form_prompt: Otkazati ovaj zahtev za rezervaciju?
  cancel_confirm: Da li ste sigurni da želite da otkažete ovu rezervaciju?
  decline_reason_placeholder: Razlog za odbijanje (opcionalno)
  cancel_reason_placeholder: Razlog za otkazivanje (opcionalno)
  declining: "Odbijanje…"
  yes_decline: "Da, odbijte"
  cancelling: "Otkazivanje…"
  yes_cancel: "Da, otkažite"
  keep_booking: Zadržite rezervaciju
  booking_not_found: Rezervacija nije pronađena.
  confirm_failed: Greška pri potvrđivanju rezervacije.
  decline_failed: Greška pri odbijanju rezervacije.
  cancel_failed: Greška pri otkazivanju rezervacije.

hr:
  back: Natrag na rezervacije
  booking_hash: "Rezervacija #"
  guest_section: Gost
  stay_details: Detalji boravka
  nights_label: Noći
  pricing: Cijene
  guest_notes: Napomene gosta
  decline_reason_title: Razlog odbijanja
  cancel_reason_title: Razlog otkazivanja
  refund_label: "Povrat novca:"
  actions_title: Akcije
  confirm_booking: Potvrdite rezervaciju
  confirming: "Potvrđivanje…"
  decline: Odbijte
  cancel_booking_btn: Otkažite rezervaciju
  cancel_confirmed_desc: Možete otkazati ovu potvrđenu rezervaciju. Gost će biti obaviješten.
  decline_form_prompt: Odbiti ovaj zahtjev za rezervaciju?
  cancel_request_form_prompt: Otkazati ovaj zahtjev za rezervaciju?
  cancel_confirm: Jeste li sigurni da želite otkazati ovu rezervaciju?
  decline_reason_placeholder: Razlog za odbijanje (opcionalno)
  cancel_reason_placeholder: Razlog za otkazivanje (opcionalno)
  declining: "Odbijanje…"
  yes_decline: "Da, odbijte"
  cancelling: "Otkazivanje…"
  yes_cancel: "Da, otkažite"
  keep_booking: Zadržite rezervaciju
  booking_not_found: Rezervacija nije pronađena.
  confirm_failed: Greška pri potvrđivanju rezervacije.
  decline_failed: Greška pri odbijanju rezervacije.
  cancel_failed: Greška pri otkazivanju rezervacije.

mk:
  back: Назад кон резервациите
  booking_hash: "Резервација #"
  guest_section: Гостин
  stay_details: Детали за престојот
  nights_label: Ноќи
  pricing: Цени
  guest_notes: Белешки на гостинот
  decline_reason_title: Причина за одбивање
  cancel_reason_title: Причина за откажување
  refund_label: "Поврат:"
  actions_title: Акции
  confirm_booking: Потврдете ја резервацијата
  confirming: "Потврдување…"
  decline: Одбијте
  cancel_booking_btn: Откажете ја резервацијата
  cancel_confirmed_desc: Можете да ја откажете оваа потврдена резервација. Гостинот ќе биде известен.
  decline_form_prompt: Да се одбие ова барање за резервација?
  cancel_request_form_prompt: Да се откаже ова барање за резервација?
  cancel_confirm: Дали сте сигурни дека сакате да ја откажете оваа резервација?
  decline_reason_placeholder: Причина за одбивање (опционално)
  cancel_reason_placeholder: Причина за откажување (опционално)
  declining: "Одбивање…"
  yes_decline: "Да, одбијте"
  cancelling: "Откажување…"
  yes_cancel: "Да, откажете"
  keep_booking: Задржете ја резервацијата
  booking_not_found: Резервацијата не е пронајдена.
  confirm_failed: Грешка при потврдување на резервацијата.
  decline_failed: Грешка при одбивање на резервацијата.
  cancel_failed: Грешка при откажување на резервацијата.

sl:
  back: Nazaj na rezervacije
  booking_hash: "Rezervacija #"
  guest_section: Gost
  stay_details: Podrobnosti o bivanju
  nights_label: Noči
  pricing: Cene
  guest_notes: Opombe gosta
  decline_reason_title: Razlog zavrnitve
  cancel_reason_title: Razlog preklica
  refund_label: "Povračilo:"
  actions_title: Dejanja
  confirm_booking: Potrdite rezervacijo
  confirming: "Potrjevanje…"
  decline: Zavrnite
  cancel_booking_btn: Prekličite rezervacijo
  cancel_confirmed_desc: Potrditev te rezervacije je mogoče preklicati. Gost bo obveščen.
  decline_form_prompt: Zavrniti to zahtevo za rezervacijo?
  cancel_request_form_prompt: Preklicati to zahtevo za rezervacijo?
  cancel_confirm: Ste prepričani, da želite preklicati to rezervacijo?
  decline_reason_placeholder: Razlog za zavrnitev (neobvezno)
  cancel_reason_placeholder: Razlog za preklic (neobvezno)
  declining: "Zavračanje…"
  yes_decline: "Da, zavrnite"
  cancelling: "Preklic…"
  yes_cancel: "Da, prekličite"
  keep_booking: Obdržite rezervacijo
  booking_not_found: Rezervacija ni bila najdena.
  confirm_failed: Napaka pri potrditvi rezervacije.
  decline_failed: Napaka pri zavrnitvi rezervacije.
  cancel_failed: Napaka pri preklicu rezervacije.
</i18n>
