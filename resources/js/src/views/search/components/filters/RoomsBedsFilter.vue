<template>
    <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            Rooms and beds
        </h3>

        <div class="space-y-4">
            <!-- Bedrooms -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-900 dark:text-white">Bedrooms</span>
                <div class="flex items-center space-x-3">
                    <button
                        @click="decrementCount('bedrooms')"
                        :disabled="localCounts.bedrooms <= 0"
                        :class="buttonClasses(localCounts.bedrooms <= 0)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </button>
                    <span class="w-8 text-center text-sm font-medium">
                        {{ localCounts.bedrooms === 0 ? 'Any' : localCounts.bedrooms }}
                    </span>
                    <button
                        @click="incrementCount('bedrooms')"
                        :disabled="localCounts.bedrooms >= 10"
                        :class="buttonClasses(localCounts.bedrooms >= 10)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Beds -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-900 dark:text-white">Beds</span>
                <div class="flex items-center space-x-3">
                    <button
                        @click="decrementCount('beds')"
                        :disabled="localCounts.beds <= 0"
                        :class="buttonClasses(localCounts.beds <= 0)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </button>
                    <span class="w-8 text-center text-sm font-medium">
                        {{ localCounts.beds === 0 ? 'Any' : localCounts.beds }}
                    </span>
                    <button
                        @click="incrementCount('beds')"
                        :disabled="localCounts.beds >= 20"
                        :class="buttonClasses(localCounts.beds >= 20)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Bathrooms -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-900 dark:text-white">Bathrooms</span>
                <div class="flex items-center space-x-3">
                    <button
                        @click="decrementCount('bathrooms')"
                        :disabled="localCounts.bathrooms <= 0"
                        :class="buttonClasses(localCounts.bathrooms <= 0)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </button>
                    <span class="w-8 text-center text-sm font-medium">
                        {{ localCounts.bathrooms === 0 ? 'Any' : localCounts.bathrooms }}
                    </span>
                    <button
                        @click="incrementCount('bathrooms')"
                        :disabled="localCounts.bathrooms >= 10"
                        :class="buttonClasses(localCounts.bathrooms >= 10)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RoomsBedsFilter',
    props: {
        bedrooms: {
            type: Object,
            default: () => ({ min: 0, max: null }),
        },
        beds: {
            type: Object,
            default: () => ({ min: 0, max: null }),
        },
        bathrooms: {
            type: Object,
            default: () => ({ min: 0, max: null }),
        },
    },
    data() {
        return {
            localCounts: {
                bedrooms: this.bedrooms.min || 0,
                beds: this.beds.min || 0,
                bathrooms: this.bathrooms.min || 0,
            },
        };
    },
    methods: {
        buttonClasses(disabled) {
            return [
                'w-8 h-8 rounded-full border flex items-center justify-center transition-colors',
                disabled
                    ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                    : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-900 dark:hover:border-white'
            ];
        },
        incrementCount(type) {
            this.localCounts[type]++;
            this.emitUpdate(type);
        },
        decrementCount(type) {
            if (this.localCounts[type] > 0) {
                this.localCounts[type]--;
                this.emitUpdate(type);
            }
        },
        emitUpdate(type) {
            this.$emit(`update:${type}`, { min: this.localCounts[type], max: null });
        },
    },
};
</script>
