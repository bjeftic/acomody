<template>
    <div
        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
    >
        <div class="mb-4">
            <h3
                class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
            >
                <svg
                    class="w-5 h-5 mr-2 text-green-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                Long Stay Discounts
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Offer discounts to guests who book longer stays
            </p>
        </div>

        <div class="space-y-4">
            <!-- Weekly Discount -->
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                >
                    Weekly discount (7+ nights)
                </label>
                <div class="flex items-center space-x-3">
                    <div class="relative flex-1 max-w-xs">
                        <input
                            v-model.number="localDiscounts.weeklyDiscount"
                            type="number"
                            min="0"
                            max="99"
                            step="1"
                            @input="handleWeeklyChange"
                            @focus="$event.target.select()"
                            class="w-full px-4 py-2 pr-12 border-2 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                            placeholder="0"
                        />
                        <span
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
                        >
                            %
                        </span>
                    </div>
                    <div
                        v-if="localDiscounts.weeklyDiscount > 0"
                        class="text-sm text-green-600 dark:text-green-400 font-medium"
                    >
                        -{{ currency }}{{ weeklyDiscountAmount }} per night
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Recommended: 10-15%
                </p>
            </div>

            <!-- Monthly Discount -->
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                >
                    Monthly discount (28+ nights)
                </label>
                <div class="flex items-center space-x-3">
                    <div class="relative flex-1 max-w-xs">
                        <input
                            v-model.number="localDiscounts.monthlyDiscount"
                            type="number"
                            min="0"
                            max="99"
                            step="1"
                            @input="handleMonthlyChange"
                            @focus="$event.target.select()"
                            class="w-full px-4 py-2 pr-12 border-2 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                            placeholder="0"
                        />
                        <span
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
                        >
                            %
                        </span>
                    </div>
                    <div
                        v-if="localDiscounts.monthlyDiscount > 0"
                        class="text-sm text-green-600 dark:text-green-400 font-medium"
                    >
                        -{{ currency }}{{ monthlyDiscountAmount }} per night
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Recommended: 20-30%
                </p>
            </div>

            <!-- Validation Error -->
            <div
                v-if="validationError"
                class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
            >
                <div class="flex items-start space-x-2">
                    <svg
                        class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <p class="text-sm text-red-700 dark:text-red-300">
                        {{ validationError }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Discount Preview -->
        <div
            v-if="
                (localDiscounts.weeklyDiscount > 0 ||
                    localDiscounts.monthlyDiscount > 0) &&
                basePrice > 0
            "
            class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg"
        >
            <h4
                class="text-sm font-semibold text-gray-900 dark:text-white mb-3"
            >
                Pricing examples with discounts
            </h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">
                        1 night
                    </span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ currency }}{{ basePrice }}
                    </span>
                </div>
                <div
                    v-if="localDiscounts.weeklyDiscount > 0"
                    class="flex justify-between"
                >
                    <span class="text-gray-600 dark:text-gray-400">
                        7 nights (with {{ localDiscounts.weeklyDiscount }}% off)
                    </span>
                    <span
                        class="font-medium text-green-600 dark:text-green-400"
                    >
                        {{ currency }}{{ weeklyStayTotal }}
                        <span class="text-xs">
                            ({{ currency }}{{ priceAfterWeeklyDiscount }}/night)
                        </span>
                    </span>
                </div>
                <div
                    v-if="localDiscounts.monthlyDiscount > 0"
                    class="flex justify-between"
                >
                    <span class="text-gray-600 dark:text-gray-400">
                        28 nights (with {{ localDiscounts.monthlyDiscount }}% off)
                    </span>
                    <span
                        class="font-medium text-green-600 dark:text-green-400"
                    >
                        {{ currency }}{{ monthlyStayTotal }}
                        <span class="text-xs">
                            ({{ currency }}{{ priceAfterMonthlyDiscount }}/night)
                        </span>
                    </span>
                </div>
            </div>

            <!-- Savings Highlight -->
            <div v-if="totalSavingsWeekly > 0 || totalSavingsMonthly > 0" class="mt-4 pt-4 border-t border-green-200 dark:border-green-800">
                <p class="text-xs font-semibold text-green-700 dark:text-green-300 mb-2">
                    Guest saves:
                </p>
                <div class="space-y-1">
                    <div v-if="totalSavingsWeekly > 0" class="flex justify-between text-xs">
                        <span class="text-gray-600 dark:text-gray-400">7 nights:</span>
                        <span class="text-green-600 dark:text-green-400 font-medium">
                            {{ currency }}{{ totalSavingsWeekly }}
                        </span>
                    </div>
                    <div v-if="totalSavingsMonthly > 0" class="flex justify-between text-xs">
                        <span class="text-gray-600 dark:text-gray-400">28 nights:</span>
                        <span class="text-green-600 dark:text-green-400 font-medium">
                            {{ currency }}{{ totalSavingsMonthly }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div
            v-if="
                localDiscounts.weeklyDiscount === 0 &&
                localDiscounts.monthlyDiscount === 0
            "
            class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
        >
            <div class="flex items-start space-x-3">
                <svg
                    class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd"
                    />
                </svg>
                <div class="flex-1">
                    <h4
                        class="text-sm font-semibold text-gray-900 dark:text-white mb-1"
                    >
                        💡 Tip: Attract long-term guests
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Long stay discounts help you fill your calendar and attract
                        guests planning extended trips.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="applyWeeklyRecommendation(10)"
                            class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            Weekly: 10%
                        </button>
                        <button
                            @click="applyWeeklyRecommendation(15)"
                            class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            Weekly: 15%
                        </button>
                        <button
                            @click="applyMonthlyRecommendation(20)"
                            class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            Monthly: 20%
                        </button>
                        <button
                            @click="applyMonthlyRecommendation(25)"
                            class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            Monthly: 25%
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "LongStayDiscounts",
    props: {
        pricing: {
            type: Object,
            required: true,
        },
        currency: {
            type: String,
            default: "$",
        },
        basePrice: {
            type: Number,
            required: true,
        },
    },
    emits: ["update:pricing"],
    data() {
        return {
            localDiscounts: {
                weeklyDiscount: this.pricing.weeklyDiscount || 0,
                monthlyDiscount: this.pricing.monthlyDiscount || 0,
            },
            validationError: "",
        };
    },
    computed: {
        weeklyDiscountAmount() {
            if (!this.localDiscounts.weeklyDiscount || !this.basePrice)
                return 0;
            return Math.round(
                this.basePrice * (this.localDiscounts.weeklyDiscount / 100)
            );
        },

        monthlyDiscountAmount() {
            if (!this.localDiscounts.monthlyDiscount || !this.basePrice)
                return 0;
            return Math.round(
                this.basePrice * (this.localDiscounts.monthlyDiscount / 100)
            );
        },

        priceAfterWeeklyDiscount() {
            return this.basePrice - this.weeklyDiscountAmount;
        },

        priceAfterMonthlyDiscount() {
            return this.basePrice - this.monthlyDiscountAmount;
        },

        weeklyStayTotal() {
            return this.priceAfterWeeklyDiscount * 7;
        },

        monthlyStayTotal() {
            return this.priceAfterMonthlyDiscount * 28;
        },

        totalSavingsWeekly() {
            return this.weeklyDiscountAmount * 7;
        },

        totalSavingsMonthly() {
            return this.monthlyDiscountAmount * 28;
        },
    },
    watch: {
        "pricing.weeklyDiscount"(newVal) {
            this.localDiscounts.weeklyDiscount = newVal;
        },
        "pricing.monthlyDiscount"(newVal) {
            this.localDiscounts.monthlyDiscount = newVal;
        },
    },
    methods: {
        handleWeeklyChange() {
            this.validateDiscounts();
            this.emitUpdate();
        },

        handleMonthlyChange() {
            this.validateDiscounts();
            this.emitUpdate();
        },

        validateDiscounts() {
            this.validationError = "";

            // Check if values are within range
            if (
                this.localDiscounts.weeklyDiscount < 0 ||
                this.localDiscounts.weeklyDiscount > 99
            ) {
                this.validationError = "Weekly discount must be between 0% and 99%";
                return false;
            }

            if (
                this.localDiscounts.monthlyDiscount < 0 ||
                this.localDiscounts.monthlyDiscount > 99
            ) {
                this.validationError =
                    "Monthly discount must be between 0% and 99%";
                return false;
            }

            // Monthly discount should be higher than weekly
            if (
                this.localDiscounts.monthlyDiscount > 0 &&
                this.localDiscounts.weeklyDiscount > 0 &&
                this.localDiscounts.monthlyDiscount <=
                    this.localDiscounts.weeklyDiscount
            ) {
                this.validationError =
                    "Monthly discount should be higher than weekly discount";
                return false;
            }

            return true;
        },

        applyWeeklyRecommendation(percentage) {
            this.localDiscounts.weeklyDiscount = percentage;
            this.validateDiscounts();
            this.emitUpdate();
        },

        applyMonthlyRecommendation(percentage) {
            this.localDiscounts.monthlyDiscount = percentage;
            // Auto-adjust weekly if needed
            if (
                this.localDiscounts.weeklyDiscount === 0 ||
                this.localDiscounts.weeklyDiscount >= percentage
            ) {
                this.localDiscounts.weeklyDiscount = Math.max(
                    10,
                    percentage - 10
                );
            }
            this.validateDiscounts();
            this.emitUpdate();
        },

        emitUpdate() {
            if (this.validateDiscounts()) {
                this.$emit("update:pricing", {
                    weeklyDiscount: this.localDiscounts.weeklyDiscount,
                    monthlyDiscount: this.localDiscounts.monthlyDiscount,
                });
            }
        },
    },
};
</script>
