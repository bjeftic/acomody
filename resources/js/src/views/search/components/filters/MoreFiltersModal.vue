<template>
    <transition name="modal">
        <div class="fixed inset-0 z-50 overflow-hidden">
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-black bg-opacity-50 transition-opacity"
                @click="$emit('close')"
            ></div>

            <!-- Modal -->
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="relative w-full max-w-2xl max-h-[90vh] bg-white dark:bg-gray-900 rounded-2xl overflow-hidden flex flex-col">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            More filters
                        </h2>
                        <button
                            @click="$emit('close')"
                            class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content (Scrollable) -->
                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <!-- Additional filters can be added here -->
                        <div class="space-y-6">
                            <!-- Host Languages -->
                            <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                                    Host language
                                </h3>
                                <div class="grid grid-cols-2 gap-3">
                                    <label
                                        v-for="language in hostLanguages.slice(0, 6)"
                                        :key="language.id"
                                        class="flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                        <span class="ml-3 text-sm text-gray-900 dark:text-white">
                                            {{ language.flag }} {{ language.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Accessibility -->
                            <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                                    Accessibility features
                                </h3>
                                <div class="space-y-3">
                                    <label
                                        v-for="feature in accessibilityFeatures"
                                        :key="feature.id"
                                        class="flex items-center cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                        <span class="ml-3 text-sm text-gray-900 dark:text-white">
                                            {{ feature.icon }} {{ feature.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    💡 More filter options are being developed. These additional filters will help you find the perfect accommodation.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                        <button
                            @click="handleClearAll"
                            class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white underline"
                        >
                            Clear all
                        </button>
                        <button
                            @click="handleApply"
                            class="px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors"
                        >
                            Apply filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import { filtersConfig } from '../../config/filtersConfig';

export default {
    name: 'MoreFiltersModal',
    props: {
        filters: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            hostLanguages: filtersConfig.hostLanguages,
            accessibilityFeatures: filtersConfig.accessibility,
        };
    },
    mounted() {
        document.body.style.overflow = 'hidden';
    },
    beforeDestroy() {
        document.body.style.overflow = '';
    },
    methods: {
        handleApply() {
            // TODO: Emit selected filters
            this.$emit('close');
        },
        handleClearAll() {
            this.$emit('update:filters', {});
            this.$emit('close');
        },
    },
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active > div > div,
.modal-leave-active > div > div {
    transition: transform 0.3s ease;
}

.modal-enter > div > div,
.modal-leave-to > div > div {
    transform: scale(0.95);
}
</style>
