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
                    / night
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
                    ({{ accommodation.reviews_count || 0 }} reviews)
                </span>
            </div>
        </div>

        <!-- Booking Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Check-in / Check-out Dates -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label
                        for="check-in"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                        CHECK-IN
                    </label>
                    <input
                        id="check-in"
                        v-model="bookingForm.checkIn"
                        type="date"
                        :min="minCheckIn"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    />
                </div>
                <div>
                    <label
                        for="check-out"
                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1"
                    >
                        CHECK-OUT
                    </label>
                    <input
                        id="check-out"
                        v-model="bookingForm.checkOut"
                        type="date"
                        :min="minCheckOut"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    />
                </div>
            </div>

            <!-- Guests Selector -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    GUESTS
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
                        {{ formatPrice(pricePerNight, priceCurrency) }} × {{ totalNights }} night{{ totalNights > 1 ? 's' : '' }}
                    </span>
                    <span>{{ formatPrice(totalPrice, priceCurrency) }}</span>
                </div>
                <div
                    v-if="serviceFee > 0"
                    class="flex justify-between text-sm text-gray-700 dark:text-gray-300"
                >
                    <span>Service fee</span>
                    <span>{{ formatPrice(serviceFee, priceCurrency) }}</span>
                </div>
                <div
                    class="flex justify-between text-base font-semibold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700"
                >
                    <span>Total</span>
                    <span>{{ formatPrice(totalWithFees, priceCurrency) }}</span>
                </div>
            </div>

            <!-- Book Button -->
            <fwb-button
                type="submit"
                color="blue"
                size="lg"
                class="w-full"
                :disabled="isSubmitting || !isFormValid"
            >
                <span v-if="isSubmitting">Processing...</span>
                <span v-else>Reserve</span>
            </fwb-button>

            <!-- Notice -->
            <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-4">
                You won't be charged yet
            </p>
        </form>

        <!-- Contact Host -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button
                @click="contactHost"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
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
                Contact host
            </button>
        </div>
    </div>
</template>

<script>
import { formatPrice } from "@/utils/helpers";
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
        return {
            bookingForm: {
                checkIn: "",
                checkOut: "",
                guests: {
                    adults: 2,
                    children: 0,
                    infants: 0,
                },
            },
            isSubmitting: false,
        };
    },

    computed: {
        minCheckIn() {
            return new Date().toISOString().split("T")[0];
        },

        minCheckOut() {
            if (!this.bookingForm.checkIn) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                return tomorrow.toISOString().split("T")[0];
            }
            const checkInDate = new Date(this.bookingForm.checkIn);
            checkInDate.setDate(checkInDate.getDate() + 1);
            return checkInDate.toISOString().split("T")[0];
        },

        pricePerNight() {
            return this.accommodation.pricing?.base_price_in_user_currency?.base_price || 0;
        },

        priceCurrency() {
            return this.accommodation.pricing?.base_price_in_user_currency?.currency || 'EUR';
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

        serviceFee() {
            return parseFloat((this.totalPrice * 0.1).toFixed(2));
        },

        totalWithFees() {
            return parseFloat((this.totalPrice + this.serviceFee).toFixed(2));
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

        handleSubmit() {
            if (!this.isFormValid) return;

            this.isSubmitting = true;

            this.$emit("book", {
                checkIn: this.bookingForm.checkIn,
                checkOut: this.bookingForm.checkOut,
                guests: { ...this.bookingForm.guests },
                nights: this.totalNights,
                totalPrice: this.totalWithFees,
            });

            setTimeout(() => {
                this.isSubmitting = false;
            }, 2000);
        },

        contactHost() {
            // TODO: Implement contact host functionality
            this.$emit("contact-host");
        },
    },

    watch: {
        "bookingForm.checkIn"(newVal) {
            if (newVal && this.bookingForm.checkOut) {
                const checkIn = new Date(newVal);
                const checkOut = new Date(this.bookingForm.checkOut);
                if (checkOut <= checkIn) {
                    checkIn.setDate(checkIn.getDate() + 1);
                    this.bookingForm.checkOut = checkIn.toISOString().split("T")[0];
                }
            }
        },
    },
};
</script>
