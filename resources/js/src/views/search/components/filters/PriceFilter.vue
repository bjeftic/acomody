<template>
    <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            Price range
        </h3>

        <!-- No Price Data Available -->
        <div
            v-if="hasNoPrice && !isLoading"
            class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center"
        >
            <div
                class="h-2 w-3/4 bg-gray-200 dark:bg-gray-700 rounded-full mx-auto"
            ></div>
        </div>

        <!-- No Valid Range - All Same Price -->
        <div
            v-else-if="hasSinglePrice && !isLoading && facetPriceRange.max > 0"
            class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center"
        >
            <p class="mb-2">All accommodations are priced at:</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ selectedCurrency.symbol
                }}{{ formatPrice(facetPriceRange.min) }}
            </p>
        </div>

        <!-- Normal Price Filter -->
        <template v-else-if="hasValidRange && !isLoading">
            <!-- Price Inputs -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label
                        class="text-xs text-gray-600 dark:text-gray-400 mb-1 block"
                    >
                        Minimum
                    </label>
                    <div class="relative">
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"
                        >
                            {{ selectedCurrency.symbol }}
                        </span>
                        <input
                            v-model.number="localPriceRange.min"
                            type="number"
                            :min="facetPriceRange.min"
                            :max="facetPriceRange.max"
                            @input="handleMinChange"
                            class="w-full pl-12 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>
                <div>
                    <label
                        class="text-xs text-gray-600 dark:text-gray-400 mb-1 block"
                    >
                        Maximum
                    </label>
                    <div class="relative">
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"
                        >
                            {{ selectedCurrency.symbol }}
                        </span>
                        <input
                            v-model.number="localPriceRange.max"
                            type="number"
                            :min="facetPriceRange.min"
                            :max="facetPriceRange.max"
                            @input="handleMaxChange"
                            class="w-full pl-12 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Range Slider -->
            <div class="relative h-6 mb-6">
                <!-- Track Background -->
                <div
                    class="absolute top-1/2 -translate-y-1/2 w-full h-1 bg-gray-200 dark:bg-gray-700 rounded-full"
                ></div>

                <!-- Active Range -->
                <div
                    class="absolute top-1/2 -translate-y-1/2 h-1 bg-gray-900 dark:bg-white rounded-full pointer-events-none"
                    :style="{
                        left: minPercent + '%',
                        width: maxPercent - minPercent + '%',
                    }"
                ></div>

                <!-- Min Range Input -->
                <input
                    v-model.number="localPriceRange.min"
                    type="range"
                    :min="facetPriceRange.min"
                    :max="facetPriceRange.max"
                    :step="stepValue"
                    @input="handleMinSliderChange"
                    class="absolute top-0 w-full h-6 appearance-none bg-transparent cursor-pointer range-slider"
                    style="z-index: 3"
                    ref="minSlider"
                />

                <!-- Max Range Input -->
                <input
                    v-model.number="localPriceRange.max"
                    type="range"
                    :min="facetPriceRange.min"
                    :max="facetPriceRange.max"
                    :step="stepValue"
                    @input="handleMaxSliderChange"
                    class="absolute top-0 w-full h-6 appearance-none bg-transparent cursor-pointer range-slider range-slider-max"
                    style="z-index: 4"
                    ref="maxSlider"
                />
            </div>
        </template>
    </div>
</template>

<script>
import { mapState } from "vuex";

export default {
    name: "PriceFilter",
    props: {
        facetPriceRange: {
            type: Object,
            default: null,
            validator: (value) => {
                return (
                    value === null ||
                    (typeof value.min === "number" &&
                        typeof value.max === "number")
                );
            },
        },
        priceRange: {
            type: Object,
            default: null,
            validator: (value) => {
                return (
                    value === null ||
                    ((value.min === null || typeof value.min === "number") &&
                        (value.max === null || typeof value.max === "number"))
                );
            },
        },
    },
    data() {
        return {
            localPriceRange: {
                min: 0,
                max: 0,
            },
            debounceTimer: null,
        };
    },
    computed: {
        ...mapState("ui", ["selectedCurrency"]),

        isLoading() {
            return (
                this.hasValidRange === false &&
                this.hasNoPrice === false &&
                this.hasSinglePrice === false
            );
        },

        hasValidRange() {
            if (!this.facetPriceRange) return false;

            const min = this.facetPriceRange.min;
            const max = this.facetPriceRange.max;

            return max > min && max > 0;
        },

        hasSinglePrice() {
            return !this.hasValidRange && this.facetPriceRange.max > 0;
        },

        hasNoPrice() {
            return (
                this.facetPriceRange.max === null &&
                this.facetPriceRange.min === null
            );
        },

        minPercent() {
            if (!this.facetPriceRange) return 0;
            const range = this.facetPriceRange.max - this.facetPriceRange.min;
            if (range === 0) return 0;

            const clampedMin = Math.max(
                this.facetPriceRange.min,
                Math.min(this.localPriceRange.min, this.facetPriceRange.max),
            );

            return Math.max(
                0,
                Math.min(
                    100,
                    ((clampedMin - this.facetPriceRange.min) / range) * 100,
                ),
            );
        },

        maxPercent() {
            if (!this.facetPriceRange) return 100;
            const range = this.facetPriceRange.max - this.facetPriceRange.min;
            if (range === 0) return 100;

            const clampedMax = Math.min(
                this.facetPriceRange.max,
                Math.max(this.localPriceRange.max, this.facetPriceRange.min),
            );

            return Math.max(
                0,
                Math.min(
                    100,
                    ((clampedMax - this.facetPriceRange.min) / range) * 100,
                ),
            );
        },

        stepValue() {
            if (!this.facetPriceRange) return 1;
            const range = this.facetPriceRange.max - this.facetPriceRange.min;

            if (range > 10000) return 100;
            if (range > 1000) return 10;
            if (range > 100) return 5;
            return 1;
        },

        isFullRange() {
            return (
                this.localPriceRange.min === this.facetPriceRange.min &&
                this.localPriceRange.max === this.facetPriceRange.max
            );
        },
    },
    watch: {
        priceRange: {
            handler(newRange) {
                if (!newRange) return;
                if (!this.hasValidRange) return;

                if (newRange.min === null && newRange.max === null) {
                    this.localPriceRange.min = this.facetPriceRange.min;
                    this.localPriceRange.max = this.facetPriceRange.max;
                    this.forceSliderUpdate();
                    return;
                }

                const newMin =
                    newRange.min !== null && newRange.min !== undefined
                        ? Math.max(
                              Math.min(newRange.min, this.facetPriceRange.max),
                              this.facetPriceRange.min,
                          )
                        : this.facetPriceRange.min;

                const newMax =
                    newRange.max !== null && newRange.max !== undefined
                        ? Math.min(
                              Math.max(newRange.max, this.facetPriceRange.min),
                              this.facetPriceRange.max,
                          )
                        : this.facetPriceRange.max;

                if (
                    this.localPriceRange.min !== newMin ||
                    this.localPriceRange.max !== newMax
                ) {
                    this.localPriceRange.min = newMin;
                    this.localPriceRange.max = newMax;
                    this.forceSliderUpdate();
                }
            },
            deep: true,
            immediate: true,
        },

        facetPriceRange: {
            handler(newFacetRange, oldFacetRange) {
                if (!newFacetRange || !this.hasValidRange) return;

                const priceRangeIsNull =
                    !this.priceRange ||
                    (this.priceRange.min === null &&
                        this.priceRange.max === null);

                if (priceRangeIsNull) {
                    this.localPriceRange.min = newFacetRange.min;
                    this.localPriceRange.max = newFacetRange.max;
                    this.forceSliderUpdate();
                } else {
                    const newMin =
                        this.priceRange.min !== null &&
                        this.priceRange.min !== undefined
                            ? Math.max(
                                  Math.min(
                                      this.priceRange.min,
                                      newFacetRange.max,
                                  ),
                                  newFacetRange.min,
                              )
                            : newFacetRange.min;

                    const newMax =
                        this.priceRange.max !== null &&
                        this.priceRange.max !== undefined
                            ? Math.min(
                                  Math.max(
                                      this.priceRange.max,
                                      newFacetRange.min,
                                  ),
                                  newFacetRange.max,
                              )
                            : newFacetRange.max;

                    this.localPriceRange.min = newMin;
                    this.localPriceRange.max = newMax;
                    this.forceSliderUpdate();
                }
            },
            deep: true,
            immediate: true,
        },
    },
    methods: {
        formatPrice(value) {
            if (value === null || value === undefined) return "0";

            return new Intl.NumberFormat("en-US", {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            }).format(value);
        },

        forceSliderUpdate() {
            this.$nextTick(() => {
                if (this.$refs.minSlider) {
                    this.$refs.minSlider.value = this.localPriceRange.min;
                }
                if (this.$refs.maxSlider) {
                    this.$refs.maxSlider.value = this.localPriceRange.max;
                }
            });
        },

        handleMinChange(event) {
            let value = event.target.value;

            if (value === "" || value === null) {
                value = this.facetPriceRange.min;
            }

            value = Number(value);

            if (value < this.facetPriceRange.min) {
                value = this.facetPriceRange.min;
            }

            if (value > this.localPriceRange.max) {
                value = this.localPriceRange.max;
            }

            this.localPriceRange.min = value;
            this.debouncedEmit();
        },

        handleMaxChange(event) {
            let value = event.target.value;

            if (value === "" || value === null) {
                value = this.facetPriceRange.max;
            }

            value = Number(value);

            if (value > this.facetPriceRange.max) {
                value = this.facetPriceRange.max;
            }

            if (value < this.localPriceRange.min) {
                value = this.localPriceRange.min;
            }

            this.localPriceRange.max = value;
            this.debouncedEmit();
        },

        handleMinSliderChange(event) {
            const value = Number(event.target.value);

            if (value > this.localPriceRange.max) {
                this.localPriceRange.min = this.localPriceRange.max;
            } else {
                this.localPriceRange.min = value;
            }

            this.debouncedEmit();
        },

        handleMaxSliderChange(event) {
            const value = Number(event.target.value);

            if (value < this.localPriceRange.min) {
                this.localPriceRange.max = this.localPriceRange.min;
            } else {
                this.localPriceRange.max = value;
            }

            this.debouncedEmit();
        },

        debouncedEmit() {
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }

            this.debounceTimer = setTimeout(() => {
                this.emitUpdate();
            }, 300);
        },

        emitUpdate() {
            if (!this.hasValidRange) return;

            if (this.isFullRange) {
                this.$emit("update:price-range", {
                    min: null,
                    max: null,
                });
            } else {
                this.$emit("update:price-range", {
                    min: this.localPriceRange.min,
                    max: this.localPriceRange.max,
                });
            }
        },
    },

    beforeUnmount() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
    },
};
</script>

<style scoped>
/* Range Slider Base Styling */
.range-slider {
    pointer-events: none;
}

.range-slider::-webkit-slider-thumb {
    pointer-events: all;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #1f2937;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    transition:
        transform 0.1s ease,
        box-shadow 0.1s ease;
}

.range-slider::-webkit-slider-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
}

.range-slider::-webkit-slider-thumb:active {
    transform: scale(1.05);
}

.range-slider::-moz-range-thumb {
    pointer-events: all;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #1f2937;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    transition:
        transform 0.1s ease,
        box-shadow 0.1s ease;
}

.range-slider::-moz-range-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
}

.range-slider::-moz-range-thumb:active {
    transform: scale(1.05);
}

/* Dark mode thumb styling */
.dark .range-slider::-webkit-slider-thumb {
    background: #f9fafb;
    border-color: #1f2937;
}

.dark .range-slider::-moz-range-thumb {
    background: #f9fafb;
    border-color: #1f2937;
}

/* Remove default track styling */
.range-slider::-webkit-slider-runnable-track {
    background: transparent;
    border: none;
}

.range-slider::-moz-range-track {
    background: transparent;
    border: none;
}

/* Focus styles */
.range-slider:focus {
    outline: none;
}

.range-slider:focus::-webkit-slider-thumb {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.range-slider:focus::-moz-range-thumb {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

/* Ensure max slider is always on top when needed */
.range-slider-max {
    z-index: 5 !important;
}

/* Remove number input arrows */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}
</style>
