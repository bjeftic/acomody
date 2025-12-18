<template>
    <div
        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl space-y-4"
    >
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            {{ title }}
        </h3>

        <!-- Base Price -->
        <div
            class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700"
        >
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Base price
            </span>
            <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ formatPrice(basePrice, currency, true, 'code') }}
            </span>
        </div>

        <!-- Guest Service Fee -->
        <div
            class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700"
        >
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Guest service fee
                </span>
                <button
                    v-if="showInfoButton"
                    @click="$emit('toggle-info')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg
                        class="w-4 h-4"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ formatPrice(serviceFee, currency, true, 'code') }}
            </span>
        </div>

        <!-- Total -->
        <div class="flex items-center justify-between pt-4">
            <span class="text-base font-semibold text-gray-900 dark:text-white">
                Guest pays
            </span>
            <span class="text-base font-semibold text-gray-900 dark:text-white">
                {{ formatPrice(guestPaysTotal, currency, true, 'code') }}
            </span>
        </div>
    </div>
</template>

<script>
import { formatPrice } from "@/utils/helpers";
export default {
    name: "PricingBreakdown",
    props: {
        title: {
            type: String,
            default: "Price breakdown (weekday)",
        },
        basePrice: {
            type: Number,
            required: true,
        },
        currency: {
            type: Object,
            required: true,
        },
        guestServiceFeePercentage: {
            type: Number,
            default: 14,
        },
        showInfoButton: {
            type: Boolean,
            default: true,
        },
    },
    emits: ["toggle-info"],
    computed: {
        serviceFee() {
            if (!this.basePrice) return 0;
            return Math.round(
                this.basePrice * (this.guestServiceFeePercentage / 100)
            );
        },

        guestPaysTotal() {
            return this.basePrice + this.serviceFee;
        },
    },
    methods: {
        formatPrice,
    },
};
</script>
