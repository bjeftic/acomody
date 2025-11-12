<template>
    <div
        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
    >
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <h3
                    class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                >
                    <svg
                        class="w-5 h-5 mr-2 text-purple-500"
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
                    Weekend Pricing
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Set a different price for Friday and Saturday nights
                </p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer ml-4">
                <input
                    v-model="localPricing.hasWeekendPrice"
                    type="checkbox"
                    class="sr-only peer"
                    @change="handleToggle"
                />
                <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
                ></div>
            </label>
        </div>

        <!-- Weekend Price Input -->
        <div v-if="localPricing.hasWeekendPrice" class="mt-4">
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
            >
                Weekend price per night
            </label>
            <div class="relative max-w-xs">
                <span
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-xl font-semibold text-gray-500"
                >
                    {{ currency }}
                </span>
                <input
                    v-model.number="localPricing.weekendPrice"
                    type="number"
                    min="0"
                    step="1"
                    @input="handlePriceChange"
                    @focus="$event.target.select()"
                    class="pl-12 w-full px-4 py-3 text-lg font-semibold border-2 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                    placeholder="0"
                />
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Applies to Friday and Saturday nights
            </p>

            <!-- Weekend Price Preview -->
            <div
                v-if="localPricing.weekendPrice > 0"
                class="mt-4 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg"
            >
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                    Guest price on weekends (before taxes)
                </p>
                <p class="text-xl font-semibold text-purple-600 dark:text-purple-400">
                    {{ currency }}{{ weekendGuestPrice }}
                </p>
            </div>

            <!-- Price Comparison -->
            <div
                v-if="localPricing.weekendPrice > 0 && pricing.basePrice > 0"
                class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
            >
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">
                        Weekend vs Weekday
                    </span>
                    <span
                        :class="[
                            'font-semibold',
                            priceIncrease > 0
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-red-600 dark:text-red-400',
                        ]"
                    >
                        {{ priceIncrease > 0 ? '+' : '' }}{{ priceIncrease }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Info Note -->
        <div
            v-else
            class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
        >
            <p class="text-sm text-gray-600 dark:text-gray-400">
                💡 If not set, the base price will apply to all nights including
                weekends
            </p>
        </div>

        <!-- Suggestion -->
        <div
            v-if="
                localPricing.hasWeekendPrice &&
                pricing.basePrice > 0 &&
                localPricing.weekendPrice === 0
            "
            class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg"
        >
            <div class="flex items-start space-x-3">
                <svg
                    class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                    />
                </svg>
                <div class="flex-1">
                    <h4
                        class="text-sm font-semibold text-gray-900 dark:text-white mb-1"
                    >
                        Suggestion: Set a weekend price
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Weekend prices are typically 20-30% higher than weekday rates.
                    </p>
                    <div class="flex space-x-2">
                        <button
                            @click="applySuggestion(1.25)"
                            class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            +25% ({{ currency
                            }}{{ Math.round(pricing.basePrice * 1.25) }})
                        </button>
                        <button
                            @click="applySuggestion(1.3)"
                            class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            +30% ({{ currency
                            }}{{ Math.round(pricing.basePrice * 1.3) }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "WeekendPricing",
    props: {
        pricing: {
            type: Object,
            required: true,
        },
        currency: {
            type: String,
            default: "$",
        },
        guestServiceFeePercentage: {
            type: Number,
            default: 14,
        },
    },
    emits: ["update:pricing"],
    data() {
        return {
            localPricing: {
                hasWeekendPrice: this.pricing.hasWeekendPrice || false,
                weekendPrice: this.pricing.weekendPrice || 0,
            },
        };
    },
    computed: {
        weekendGuestPrice() {
            if (!this.localPricing.weekendPrice) return 0;
            const serviceFee = Math.round(
                this.localPricing.weekendPrice *
                    (this.guestServiceFeePercentage / 100)
            );
            return this.localPricing.weekendPrice + serviceFee;
        },

        priceIncrease() {
            if (
                !this.localPricing.weekendPrice ||
                !this.pricing.basePrice
            )
                return 0;
            return Math.round(
                ((this.localPricing.weekendPrice - this.pricing.basePrice) /
                    this.pricing.basePrice) *
                    100
            );
        },
    },
    watch: {
        "pricing.hasWeekendPrice"(newVal) {
            this.localPricing.hasWeekendPrice = newVal;
        },
        "pricing.weekendPrice"(newVal) {
            this.localPricing.weekendPrice = newVal;
        },
        "pricing.basePrice"(newBasePrice) {
            // Auto-update weekend price if it was auto-generated (25% increase)
            if (
                this.localPricing.hasWeekendPrice &&
                this.localPricing.weekendPrice > 0
            ) {
                const ratio =
                    this.localPricing.weekendPrice /
                    (this.pricing.basePrice || 1);
                // If ratio is between 1.2 and 1.3, it was likely auto-generated
                if (ratio >= 1.2 && ratio <= 1.3) {
                    this.localPricing.weekendPrice = Math.round(
                        newBasePrice * 1.25
                    );
                    this.emitUpdate();
                }
            }
        },
    },
    methods: {
        handleToggle() {
            if (
                this.localPricing.hasWeekendPrice &&
                !this.localPricing.weekendPrice &&
                this.pricing.basePrice
            ) {
                // Auto-set to 25% higher when enabled
                this.localPricing.weekendPrice = Math.round(
                    this.pricing.basePrice * 1.25
                );
            }
            this.emitUpdate();
        },

        handlePriceChange() {
            this.emitUpdate();
        },

        applySuggestion(multiplier) {
            this.localPricing.weekendPrice = Math.round(
                this.pricing.basePrice * multiplier
            );
            this.emitUpdate();
        },

        emitUpdate() {
            this.$emit("update:pricing", {
                hasWeekendPrice: this.localPricing.hasWeekendPrice,
                weekendPrice: this.localPricing.weekendPrice,
            });
        },
    },
};
</script>
