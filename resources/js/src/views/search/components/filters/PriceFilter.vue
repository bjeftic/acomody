<template>
    <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            Price range
        </h3>

        <!-- Price Inputs -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">
                    Minimum
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                        $
                    </span>
                    <input
                        v-model.number="localPriceRange.min"
                        type="number"
                        min="0"
                        :max="localPriceRange.max"
                        @input="handleMinChange"
                        class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>
            <div>
                <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">
                    Maximum
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                        $
                    </span>
                    <input
                        v-model.number="localPriceRange.max"
                        type="number"
                        :min="localPriceRange.min"
                        max="1000"
                        @input="handleMaxChange"
                        class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>
        </div>

        <!-- Range Slider -->
        <div class="relative pb-6">
            <!-- Track -->
            <div class="relative h-1 bg-gray-200 dark:bg-gray-700 rounded-full">
                <!-- Active Range -->
                <div
                    class="absolute h-1 bg-gray-900 dark:bg-white rounded-full"
                    :style="{
                        left: minPercent + '%',
                        right: (100 - maxPercent) + '%'
                    }"
                ></div>
            </div>

            <!-- Min Slider -->
            <input
                v-model.number="localPriceRange.min"
                type="range"
                min="0"
                max="1000"
                step="10"
                @input="handleMinChange"
                class="absolute top-0 w-full h-1 appearance-none bg-transparent pointer-events-none slider-thumb"
            />

            <!-- Max Slider -->
            <input
                v-model.number="localPriceRange.max"
                type="range"
                min="0"
                max="1000"
                step="10"
                @input="handleMaxChange"
                class="absolute top-0 w-full h-1 appearance-none bg-transparent pointer-events-none slider-thumb"
            />
        </div>

        <!-- Price Distribution (Histogram - Optional) -->
        <div class="mt-4 flex items-end justify-between h-16 space-x-1">
            <div
                v-for="(bar, index) in priceDistribution"
                :key="index"
                class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-t"
                :style="{ height: bar + '%' }"
                :title="`$${index * 100} - $${(index + 1) * 100}`"
            ></div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">
            Average nightly price is ${{ averagePrice }}
        </p>
    </div>
</template>

<script>
export default {
    name: 'PriceFilter',
    props: {
        priceRange: {
            type: Object,
            required: true,
        },
        averagePrice: {
            type: Number,
            default: 75,
        },
    },
    data() {
        return {
            localPriceRange: {
                min: this.priceRange.min || 0,
                max: this.priceRange.max || 1000,
            },
            // Mock price distribution data (should come from API)
            priceDistribution: [20, 40, 60, 80, 100, 90, 70, 50, 30, 20],
        };
    },
    computed: {
        minPercent() {
            return (this.localPriceRange.min / 1000) * 100;
        },
        maxPercent() {
            return (this.localPriceRange.max / 1000) * 100;
        },
    },
    watch: {
        priceRange: {
            handler(newRange) {
                this.localPriceRange = {
                    min: newRange.min || 0,
                    max: newRange.max || 1000,
                };
            },
            deep: true,
        },
    },
    methods: {
        handleMinChange() {
            // Ensure min doesn't exceed max
            if (this.localPriceRange.min > this.localPriceRange.max) {
                this.localPriceRange.min = this.localPriceRange.max;
            }
            this.emitUpdate();
        },
        handleMaxChange() {
            // Ensure max doesn't go below min
            if (this.localPriceRange.max < this.localPriceRange.min) {
                this.localPriceRange.max = this.localPriceRange.min;
            }
            this.emitUpdate();
        },
        emitUpdate() {
            this.$emit('update:price-range', { ...this.localPriceRange });
        },
    },
};
</script>

<style scoped>
/* Range Slider Styling */
input[type="range"].slider-thumb {
    pointer-events: all;
}

input[type="range"].slider-thumb::-webkit-slider-thumb {
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #1f2937;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    pointer-events: all;
}

input[type="range"].slider-thumb::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #1f2937;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    pointer-events: all;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    input[type="range"].slider-thumb::-webkit-slider-thumb {
        background: #f9fafb;
        border-color: #1f2937;
    }

    input[type="range"].slider-thumb::-moz-range-thumb {
        background: #f9fafb;
        border-color: #1f2937;
    }
}

/* Remove default slider track */
input[type="range"]::-webkit-slider-runnable-track {
    background: transparent;
}

input[type="range"]::-moz-range-track {
    background: transparent;
}
</style>
