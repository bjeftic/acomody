<template>
    <div
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-lg"
    >
        <!-- Price Header -->
        <div class="mb-6">
            <div class="flex items-baseline">
                <span class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ formatPrice(accommodation.pricing.base_price_in_user_currency.base_price, accommodation.pricing.base_price_in_user_currency.currency) }}
                </span>
                <span class="text-base text-gray-600 dark:text-gray-400 ml-2">
                    / {{ $t('search.price_per_night') }}
                </span>
            </div>
            <div
                v-if="accommodation.rating"
                class="flex items-center mt-2 text-sm"
            >
                <svg
                    class="w-5 h-5 text-yellow-400 mr-1"
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
                <span class="text-gray-600 dark:text-gray-400 ml-1">
                    ({{ accommodation.reviews_count || 0 }} {{ $t('accommodation.reviews') }})
                </span>
            </div>
        </div>

        <!-- Booking Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Check-in / Check-out Dates -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('search.check_in') }}
                    </label>
                    <VueDatePicker
                        v-model="bookingForm.checkIn"
                        model-type="format"
                        :min-date="minCheckIn"
                        :disabled-dates="isDateDisabled"
                        :time-config="{ enableTimePicker: false }"
                        :auto-apply="true"
                        :placeholder="$t('select_date')"
                        :format="formatPickerDate"
                        @update:model-value="onCheckInChange"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('search.check_out') }}
                    </label>
                    <VueDatePicker
                        v-model="bookingForm.checkOut"
                        model-type="format"
                        :min-date="minCheckOut"
                        :start-date="bookingForm.checkIn || minCheckOut"
                        :disabled-dates="isDateDisabled"
                        :time-config="{ enableTimePicker: false }"
                        :auto-apply="true"
                        :placeholder="$t('select_date')"
                        :format="formatPickerDate"
                    />
                </div>
            </div>

            <!-- Guests Selector -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ $t('search.guests') }}
                </label>
                <guests-dropdown
                    v-model="bookingForm.guests"
                    :max-adults="accommodation.max_guests || 10"
                    dropdown-class="left-0 right-0 w-full"
                />
            </div>

            <!-- Price Breakdown -->
            <div
                v-if="totalNights > 0"
                class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3"
            >
                <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                    <span>
                        {{ formatPrice(pricePerNight, priceCurrency) }} × {{ $t('accommodation.nights', { n: totalNights, count: totalNights }) }}
                    </span>
                    <span>{{ formatPrice(totalPrice, priceCurrency) }}</span>
                </div>
                <div
                    class="flex justify-between text-base font-semibold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700"
                >
                    <span>{{ $t('accommodation.total') }}</span>
                    <span>{{ formatPrice(totalPrice, priceCurrency) }}</span>
                </div>
            </div>

            <!-- Book Button -->
            <BaseButton
                type="submit"
                size="lg"
                :full="true"
                :disabled="!isFormValid"
            >
                {{ $t('accommodation.reserve') }}
            </BaseButton>

            <!-- Notice -->
            <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-4">
                {{ $t('no_charge_yet') }}
            </p>
        </form>

        <!-- Contact Host -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button
                @click="contactHost"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>
                {{ $t('booking.contact_host') }}
            </button>
        </div>
    </div>
</template>

<script>
import { formatPrice } from "@/utils/helpers";
import apiClient from "@/services/apiClient";
import GuestsDropdown from "@/src/components/common/GuestsDropdown.vue";

export default {
    name: "BookingCard",

    components: {
        GuestsDropdown,
    },

    props: {
        accommodation: {
            type: Object,
            required: true,
        },
    },

    data() {
        const query = this.$route?.query || {};
        return {
            bookingForm: {
                checkIn: query.checkIn || null,
                checkOut: query.checkOut || null,
                guests: {
                    adults: query.adults ? parseInt(query.adults) : 2,
                    children: query.children ? parseInt(query.children) : 0,
                    infants: query.infants ? parseInt(query.infants) : 0,
                },
            },
            blockedRanges: [],
        };
    },

    async created() {
        try {
            const response = await apiClient.public.accommodations[this.accommodation.id]["blocked-dates"].get();
            this.blockedRanges = response.data.data ?? [];
        } catch {
            // non-critical — booking validation still happens on submit
        }
    },

    computed: {
        minCheckIn() {
            return new Date();
        },

        minCheckOut() {
            if (!this.bookingForm.checkIn) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                return tomorrow;
            }
            const date = new Date(this.bookingForm.checkIn);
            date.setDate(date.getDate() + 1);
            return date;
        },

        pricePerNight() {
            return this.accommodation.pricing?.base_price_in_user_currency?.base_price || 0;
        },

        priceCurrency() {
            return this.accommodation.pricing?.base_price_in_user_currency?.currency || "EUR";
        },

        totalNights() {
            if (!this.bookingForm.checkIn || !this.bookingForm.checkOut) return 0;
            const checkIn = new Date(this.bookingForm.checkIn);
            const checkOut = new Date(this.bookingForm.checkOut);
            return Math.ceil(Math.abs(checkOut - checkIn) / (1000 * 60 * 60 * 24));
        },

        totalPrice() {
            return parseFloat((this.pricePerNight * this.totalNights).toFixed(2));
        },

        totalGuests() {
            return this.bookingForm.guests.adults + this.bookingForm.guests.children;
        },

        isFormValid() {
            return (
                this.bookingForm.checkIn &&
                this.bookingForm.checkOut &&
                this.totalGuests > 0 &&
                this.totalNights > 0
            );
        },
    },

    methods: {
        formatPrice,

        formatPickerDate(date) {
            if (!date) return "";
            const d = new Date(date);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, "0");
            const day = String(d.getDate()).padStart(2, "0");
            return `${y}-${m}-${day}`;
        },

        isDateDisabled(date) {
            const d = date.toISOString().split("T")[0];
            return this.blockedRanges.some((range) => d >= range.start && d < range.end);
        },

        onCheckInChange(value) {
            if (value && this.bookingForm.checkOut && this.bookingForm.checkOut <= value) {
                const next = new Date(value);
                next.setDate(next.getDate() + 1);
                this.bookingForm.checkOut = next.toISOString().split("T")[0];
            }
        },

        handleSubmit() {
            if (!this.isFormValid) return;

            this.$router.push({
                name: "accommodation-reserve",
                params: { id: this.$route.params.id },
                query: {
                    checkIn: this.bookingForm.checkIn,
                    checkOut: this.bookingForm.checkOut,
                    adults: this.bookingForm.guests.adults,
                    children: this.bookingForm.guests.children,
                    infants: this.bookingForm.guests.infants,
                },
            });
        },

        contactHost() {
            this.$emit("contact-host");
        },
    },
};
</script>

<i18n lang="yaml">
en:
  select_date: Select date
  no_charge_yet: You won't be charged yet

sr:
  select_date: Izaberite datum
  no_charge_yet: Nećete biti naplaćeni još

hr:
  select_date: Odaberite datum
  no_charge_yet: Nećete biti naplaćeni još

mk:
  select_date: Изберете датум
  no_charge_yet: Нема да бидете наплатени уште

sl:
  select_date: Izberite datum
  no_charge_yet: Zaenkrat ne boste zaračunani
</i18n>
