<template>
    <div
        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
    >
        <div class="mb-4">
            <h3
                class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
            >
                <svg
                    class="w-5 h-5 mr-2 text-indigo-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    />
                </svg>
                Competitive Pricing Analysis
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                See how your pricing compares to similar properties in {{ city || 'your area' }}
            </p>
        </div>

        <!-- Price Comparison -->
        <div class="space-y-6">
            <!-- Average Price -->
            <div
                class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl"
            >
                <div class="flex items-center justify-between mb-2">
                    <span
                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Average {{ propertyTypeName }} price in {{ city }}
                    </span>
                    <span
                        class="px-3 py-1 bg-white dark:bg-gray-900 rounded-full text-xs font-semibold text-gray-600 dark:text-gray-400"
                    >
                        Market Average
                    </span>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span
                        class="text-3xl font-bold text-indigo-600 dark:text-indigo-400"
                    >
                        {{ currency }}{{ averagePrice }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        per night
                    </span>
                </div>
            </div>

            <!-- Price Range -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span
                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Typical price range
                    </span>
                </div>

                <!-- Range Bar -->
                <div class="relative">
                    <div
                        class="h-12 bg-gradient-to-r from-green-100 via-yellow-100 to-red-100 dark:from-green-900/30 dark:via-yellow-900/30 dark:to-red-900/30 rounded-lg"
                    ></div>

                    <!-- Min/Max Labels -->
                    <div class="absolute inset-0 flex items-center justify-between px-3">
                        <span
                            class="text-xs font-semibold text-green-700 dark:text-green-400"
                        >
                            {{ currency }}{{ priceRange.min }}
                        </span>
                        <span
                            class="text-xs font-semibold text-red-700 dark:text-red-400"
                        >
                            {{ currency }}{{ priceRange.max }}
                        </span>
                    </div>

                    <!-- Average Marker -->
                    <div
                        class="absolute top-0 h-12 w-1 bg-indigo-500"
                        :style="{ left: averageMarkerPosition }"
                    >
                        <div
                            class="absolute -top-6 left-1/2 -translate-x-1/2 px-2 py-1 bg-indigo-500 text-white text-xs font-bold rounded whitespace-nowrap"
                        >
                            Avg
                        </div>
                    </div>

                    <!-- Your Price Marker (if set) -->
                    <div
                        v-if="yourPrice > 0"
                        class="absolute top-0 h-12 w-1 bg-blue-600 dark:bg-blue-400"
                        :style="{ left: yourPriceMarkerPosition }"
                    >
                        <div
                            class="absolute -bottom-7 left-1/2 -translate-x-1/2 px-2 py-1 bg-blue-600 dark:bg-blue-400 text-white text-xs font-bold rounded whitespace-nowrap"
                        >
                            Your Price
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex items-center justify-between mt-8 text-xs text-gray-600 dark:text-gray-400">
                    <span>Budget-friendly</span>
                    <span>Mid-range</span>
                    <span>Premium</span>
                </div>
            </div>

            <!-- Your Position (if price is set) -->
            <div v-if="yourPrice > 0" class="space-y-3">
                <div
                    :class="[
                        'p-4 rounded-xl border-2',
                        positionColor.border,
                        positionColor.bg,
                    ]"
                >
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <div :class="['w-3 h-3 rounded-full', positionColor.dot]"></div>
                            <span
                                class="text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                Your pricing position
                            </span>
                        </div>
                        <span
                            :class="[
                                'px-2 py-1 rounded-full text-xs font-bold',
                                positionColor.badge,
                            ]"
                        >
                            {{ positionLabel }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                        {{ positionDescription }}
                    </p>
                    <div class="flex items-center space-x-4 text-xs">
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Your price:</span>
                            <span class="ml-1 font-bold text-gray-900 dark:text-white">
                                {{ currency }}{{ yourPrice }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Difference:</span>
                            <span
                                :class="[
                                    'ml-1 font-bold',
                                    priceDifferencePercent > 0
                                        ? 'text-red-600 dark:text-red-400'
                                        : 'text-green-600 dark:text-green-400',
                                ]"
                            >
                                {{ priceDifferencePercent > 0 ? '+' : '' }}{{ priceDifferencePercent }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Positioning Recommendation -->
                <div
                    class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg"
                >
                    <h4
                        class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
                    >
                        <svg
                            class="w-4 h-4 mr-2 text-blue-500"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        {{ recommendationTitle }}
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ recommendationText }}
                    </p>
                </div>
            </div>

            <!-- No Price Set -->
            <div
                v-else
                class="p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-center"
            >
                <svg
                    class="w-12 h-12 mx-auto mb-3 text-gray-400"
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
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Set your base price to see comparison
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    We'll show you how your pricing compares to similar properties
                </p>
            </div>
        </div>

        <!-- Market Insights -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                💡 Market Insights
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div
                    class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg"
                >
                    <div class="text-xs font-semibold text-green-700 dark:text-green-400 mb-1">
                        Budget Range
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        {{ currency }}{{ priceRange.min }} - {{ currency }}{{ budgetRangeMax }}
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Quick bookings, high volume
                    </div>
                </div>
                <div
                    class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg"
                >
                    <div class="text-xs font-semibold text-blue-700 dark:text-blue-400 mb-1">
                        Mid Range
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        {{ currency }}{{ budgetRangeMax + 1 }} - {{ currency }}{{ premiumRangeMin - 1 }}
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Balanced bookings & profit
                    </div>
                </div>
                <div
                    class="p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg"
                >
                    <div class="text-xs font-semibold text-purple-700 dark:text-purple-400 mb-1">
                        Premium Range
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        {{ currency }}{{ premiumRangeMin }}+
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Quality guests, high margins
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Source Note -->
        <div class="mt-4 text-xs text-gray-500 dark:text-gray-400 text-center">
            <svg
                class="w-3 h-3 inline mr-1"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd"
                />
            </svg>
            Based on similar {{ propertyTypeName }} listings in {{ city }}. Updated weekly.
        </div>
    </div>
</template>

<script>
export default {
    name: "CompetitivePricing",
    props: {
        city: {
            type: String,
            default: "",
        },
        propertyTypeName: {
            type: String,
            default: "property",
        },
        currency: {
            type: String,
            default: "$",
        },
        averagePrice: {
            type: Number,
            default: 75,
        },
        priceRange: {
            type: Object,
            default: () => ({
                min: 40,
                max: 120,
            }),
        },
        yourPrice: {
            type: Number,
            default: 0,
        },
    },
    computed: {
        averageMarkerPosition() {
            const range = this.priceRange.max - this.priceRange.min;
            const position =
                ((this.averagePrice - this.priceRange.min) / range) * 100;
            return `${Math.max(0, Math.min(100, position))}%`;
        },

        yourPriceMarkerPosition() {
            if (this.yourPrice <= 0) return "0%";
            const range = this.priceRange.max - this.priceRange.min;
            const position =
                ((this.yourPrice - this.priceRange.min) / range) * 100;
            return `${Math.max(0, Math.min(100, position))}%`;
        },

        priceDifferencePercent() {
            if (this.yourPrice <= 0 || this.averagePrice <= 0) return 0;
            return Math.round(
                ((this.yourPrice - this.averagePrice) / this.averagePrice) *
                    100
            );
        },

        budgetRangeMax() {
            return Math.round(this.averagePrice * 0.85);
        },

        premiumRangeMin() {
            return Math.round(this.averagePrice * 1.15);
        },

        positionLabel() {
            if (this.yourPrice < this.budgetRangeMax) return "Budget";
            if (this.yourPrice >= this.premiumRangeMin) return "Premium";
            return "Competitive";
        },

        positionColor() {
            if (this.yourPrice < this.budgetRangeMax) {
                return {
                    border: "border-green-200 dark:border-green-800",
                    bg: "bg-green-50 dark:bg-green-900/20",
                    dot: "bg-green-500",
                    badge: "bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300",
                };
            }
            if (this.yourPrice >= this.premiumRangeMin) {
                return {
                    border: "border-purple-200 dark:border-purple-800",
                    bg: "bg-purple-50 dark:bg-purple-900/20",
                    dot: "bg-purple-500",
                    badge: "bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300",
                };
            }
            return {
                border: "border-blue-200 dark:border-blue-800",
                bg: "bg-blue-50 dark:bg-blue-900/20",
                dot: "bg-blue-500",
                badge: "bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300",
            };
        },

        positionDescription() {
            if (this.yourPrice < this.budgetRangeMax) {
                return "Your price is in the budget-friendly range. This typically attracts more bookings and price-conscious travelers.";
            }
            if (this.yourPrice >= this.premiumRangeMin) {
                return "Your price is in the premium range. This positions you for quality guests willing to pay more for exceptional properties.";
            }
            return "Your price is competitive with similar properties in your area. This balanced approach appeals to a wide range of guests.";
        },

        recommendationTitle() {
            if (this.priceDifferencePercent < -20) {
                return "Consider a small increase";
            }
            if (this.priceDifferencePercent > 20) {
                return "Monitor your booking rate";
            }
            return "Good positioning";
        },

        recommendationText() {
            if (this.priceDifferencePercent < -20) {
                return "You're priced significantly below the market average. Consider raising your price by 10-15% to better reflect your property's value while remaining competitive.";
            }
            if (this.priceDifferencePercent > 20) {
                return "You're priced above the market average. This can work well for premium properties, but monitor your booking rate. If bookings are slow after 2-3 weeks, consider adjusting your price.";
            }
            return "Your pricing is well-positioned within the market range. Continue to monitor your booking rate and adjust seasonally as needed.";
        },
    },
};
</script>
