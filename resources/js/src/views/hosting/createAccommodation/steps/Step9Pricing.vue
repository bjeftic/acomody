<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Now, set your price
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            You can change it anytime.
        </p>

        <hr />

        <div class="py-4 overflow-auto h-[60vh] mx-auto space-y-8 pr-4">
            <!-- Base Price Input Section -->
            <div class="text-center space-y-6">
                <!-- Large Price Display -->
                <div class="relative inline-block">
                    <div class="flex items-baseline justify-center">
                        <span
                            class="text-6xl font-semibold text-gray-900 dark:text-white mr-2"
                        >
                            {{ config.currency }}
                        </span>
                        <input
                            :value="localPricing.basePrice"
                            @input="updateBasePrice"
                            @focus="$event.target.select()"
                            type="number"
                            min="10"
                            step="1"
                            class="text-6xl font-semibold text-gray-900 dark:text-white bg-transparent border-0 border-b-4 border-gray-300 dark:border-gray-700 focus:border-gray-900 dark:focus:border-white focus:ring-0 w-48 text-center outline-none"
                            placeholder="00"
                        />
                    </div>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                        base price per night
                    </p>
                </div>

                <!-- Guest Price Preview -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg inline-block">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                        Guest price before taxes (weekday)
                    </p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ config.currency }}{{ guestPriceBeforeTaxes }}
                    </p>
                </div>
            </div>

            <!-- Weekend Pricing -->
            <weekend-pricing
                :pricing="localPricing"
                :currency="config.currency"
                :guest-price="weekendGuestPrice"
                @update:pricing="updatePricing"
            />

            <!-- Long Stay Discounts -->
            <long-stay-discounts
                :pricing="localPricing"
                :currency="config.currency"
                :base-price="localPricing.basePrice"
                @update:pricing="updatePricing"
            />

            <!-- Booking Type -->
            <div
                class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
            >
                <div class="mb-4">
                    <h3
                        class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                    >
                        <svg
                            class="w-5 h-5 mr-2 text-blue-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"
                            />
                        </svg>
                        Booking Type
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Choose how you want to receive reservations
                    </p>
                </div>

                <div class="space-y-3">
                    <booking-type-card
                        v-for="type in config.bookingTypes"
                        :key="type.id"
                        :booking-type="type"
                        :selected="localPricing.bookingType === type.id"
                        @select="updateBookingType"
                    />
                </div>

                <!-- Info Note -->
                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>Tip:</strong> Instant booking typically results in 3x
                        more reservations compared to request-only listings.
                    </p>
                </div>
            </div>

            <!-- Minimum Stay Requirements -->
            <minimum-stay-selector
                :pricing="localPricing"
                :days-of-week="config.daysOfWeek"
                @update:pricing="updatePricing"
            />

            <!-- Price Breakdown -->
            <pricing-breakdown
                :base-price="localPricing.basePrice"
                :currency="config.currency"
                :guest-service-fee-percentage="config.guestServiceFeePercentage"
                @toggle-info="showServiceFeeInfo = !showServiceFeeInfo"
            />

            <!-- Service Fee Info -->
            <div
                v-if="showServiceFeeInfo"
                class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
            >
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    This fee helps us run our platform and provide 24/7 customer
                    support for your trip.
                </p>
            </div>

            <!-- Your Earnings -->
            <div
                class="p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        You earn (per night)
                    </h3>
                    <span
                        class="text-2xl font-bold text-green-600 dark:text-green-400"
                    >
                        {{ config.currency }}{{ youEarn }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    After the {{ config.hostServiceFeePercentage }}% host service fee
                </p>
            </div>

            <!-- Pricing Tips -->
            <pricing-tips :currency="config.currency" />

            <!-- Competitive Analysis -->
            <competitive-pricing
                :city="formData.address.city"
                :property-type-name="propertyTypeName"
                :currency="config.currency"
                :average-price="config.averagePrice"
                :price-range="config.priceRange"
                :your-price="localPricing.basePrice"
            />
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
import { pricingConfig } from "./pricingConfig";
import PricingBreakdown from "@/src/views/hosting/createAccommodation/components/PricingBreakdown.vue";
import BookingTypeCard from "@/src/views/hosting/createAccommodation/components/BookingTypeCard.vue";
import WeekendPricing from "@/src/views/hosting/createAccommodation/components/WeekendPricing.vue";
import LongStayDiscounts from "@/src/views/hosting/createAccommodation/components/LongStayDiscounts.vue";
import MinimumStaySelector from "@/src/views/hosting/createAccommodation/components/MinimumStaySelector.vue";
import PricingTips from "@/src/views/hosting/createAccommodation/components/PricingTips.vue";
import CompetitivePricing from "@/src/views/hosting/createAccommodation/components/CompetitivePricing.vue";

export default {
    name: "Step9Pricing",
    components: {
        PricingBreakdown,
        BookingTypeCard,
        WeekendPricing,
        LongStayDiscounts,
        MinimumStaySelector,
        PricingTips,
        CompetitivePricing,
    },
    props: {
        formData: {
            type: Object,
            required: true,
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ["update:form-data"],
    data() {
        return {
            config: pricingConfig,
            localPricing: { ...this.formData.pricing },
            showServiceFeeInfo: false,
        };
    },
    computed: {
        ...mapState("hosting/createAccommodation", ["accommodationTypes"]),

        propertyTypeName() {
            if (!this.formData.propertyType) return "place";
            const type = this.accommodationTypes.find(
                (t) => t.id === this.formData.propertyType
            );
            return type ? type.name.toLowerCase() : "place";
        },

        guestPriceBeforeTaxes() {
            if (!this.localPricing.basePrice) return 0;
            const serviceFee = Math.round(
                this.localPricing.basePrice *
                    (this.config.guestServiceFeePercentage / 100)
            );
            return this.localPricing.basePrice + serviceFee;
        },

        weekendGuestPrice() {
            if (
                !this.localPricing.hasWeekendPrice ||
                !this.localPricing.weekendPrice
            ) {
                return this.guestPriceBeforeTaxes;
            }
            const weekendServiceFee = Math.round(
                this.localPricing.weekendPrice *
                    (this.config.guestServiceFeePercentage / 100)
            );
            return this.localPricing.weekendPrice + weekendServiceFee;
        },

        youEarn() {
            if (!this.localPricing.basePrice) return 0;
            const hostFee = Math.round(
                this.localPricing.basePrice *
                    (this.config.hostServiceFeePercentage / 100)
            );
            return this.localPricing.basePrice - hostFee;
        },
    },
    watch: {
        "formData.pricing": {
            deep: true,
            handler(newPricing) {
                this.localPricing = { ...newPricing };
            },
        },

        "localPricing.hasWeekendPrice"(enabled) {
            if (enabled && !this.localPricing.weekendPrice) {
                this.localPricing.weekendPrice = Math.round(
                    this.localPricing.basePrice * 1.25
                );
            }
        },
    },
    methods: {
        updateBasePrice(event) {
            const value = parseFloat(event.target.value) || 0;
            this.localPricing.basePrice = value;
            this.emitUpdate();
        },

        updateBookingType(typeId) {
            this.localPricing.bookingType = typeId;
            this.emitUpdate();
        },

        updatePricing(updates) {
            this.localPricing = { ...this.localPricing, ...updates };
            this.emitUpdate();
        },

        emitUpdate() {
            this.$emit("update:form-data", {
                ...this.formData,
                pricing: { ...this.localPricing },
            });
        },
    },
};
</script>
