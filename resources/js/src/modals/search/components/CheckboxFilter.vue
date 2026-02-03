<template>
    <div class="pb-6 border-b border-gray-200 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
            {{ title }}
        </h3>

        <!-- Popular/Initial Options grouped by category -->
        <div class="space-y-4 mb-4">
            <div
                v-for="(categoryOptions, categoryName) in groupedOptions"
                :key="categoryName"
                class="space-y-3"
            >
                <h4
                    v-if="Object.keys(groupedOptions).length > 1"
                    class="text-sm font-medium text-gray-700 dark:text-gray-400"
                >
                    {{ categoryName }}
                </h4>
                <div class="space-y-2">
                    <label
                        v-for="option in categoryOptions"
                        :key="option.value"
                        class="flex items-center cursor-pointer group"
                    >
                        <input
                            type="checkbox"
                            :checked="isSelected(option.value)"
                            @change="toggleOption(option.value)"
                            class="w-5 h-5 text-blue-600 border-gray-300 dark:border-gray-700 rounded focus:ring-blue-500 dark:focus:ring-blue-600"
                        />
                        <div class="ml-3 flex items-center space-x-2">
                            <span
                                class="text-sm text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300"
                            >
                                {{ option.title }}
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Show All Button -->
        <button
            v-if="totalOptionsCount > options.length"
            @click="showAllOptions = !showAllOptions"
            class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white underline"
        >
            {{
                showAllOptions
                    ? "Show less"
                    : `Show all ${totalOptionsCount} options`
            }}
        </button>

        <!-- All Options Modal/Expanded -->
        <div v-if="showAllOptions" class="mt-4 space-y-4">
            <div
                v-for="(categoryOptions, categoryName) in allOptionsGrouped"
                :key="categoryName"
                class="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-3"
                >
                    {{ categoryName }}
                </h4>
                <div class="space-y-2">
                    <label
                        v-for="option in categoryOptions"
                        :key="option.value"
                        class="flex items-center cursor-pointer group"
                    >
                        <input
                            type="checkbox"
                            :checked="isSelected(option.value)"
                            @change="toggleOption(option.value)"
                            class="w-5 h-5 text-blue-600 border-gray-300 dark:border-gray-700 rounded focus:ring-blue-500"
                        />
                        <div class="ml-3 flex items-center space-x-2">
                            <span
                                class="text-sm text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300"
                            >
                                {{ option.title }}
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { filtersConfig } from "../../../views/search/config/filtersConfig";

export default {
    name: "AmenitiesFilter",
    props: {
        title: {
            type: String,
            required: true,
        },
        options: {
            type: Array,
            default: () => [],
        },
        selectedOptions: {
            type: [String, Array],
            default: () => [],
        },
    },
    data() {
        return {
            showAllOptions: false,
            allOptions: filtersConfig.amenities,
        };
    },
    computed: {
        // Group initial/popular options by category
        groupedOptions() {
            return this.groupByCategory(this.options);
        },

        // Group all options by category when expanded
        allOptionsGrouped() {
            // Flatten all options from filtersConfig
            const allOptionsList = [];
            Object.values(this.allOptions).forEach((category) => {
                allOptionsList.push(...category);
            });
            return this.groupByCategory(allOptionsList);
        },

        totalOptionsCount() {
            return this.options.length;
        },
    },
    methods: {
        groupByCategory(optionsList) {
            const grouped = {};
            optionsList.forEach((option) => {
                const category = option.category || 'other';
                if (!grouped[category]) {
                    grouped[category] = [];
                }
                grouped[category].push(option);
            });
            return grouped;
        },

        isSelected(optionValue) {
            if (Array.isArray(this.selectedOptions)) {
                return this.selectedOptions.includes(optionValue);
            }
            if (typeof this.selectedOptions === "string") {
                return this.selectedOptions === optionValue;
            }
            return false;
        },

        toggleOption(optionValue) {
            const currentOptions = this.selectedOptions;
            let options;

            if (Array.isArray(currentOptions)) {
                options = [...currentOptions];
                const index = options.indexOf(optionValue);

                if (index > -1) {
                    options.splice(index, 1);
                } else {
                    options.push(optionValue);
                }
            } else if (currentOptions) {
                if (currentOptions === optionValue) {
                    options = null;
                } else {
                    options = [currentOptions, optionValue];
                }
            } else {
                options = optionValue;
            }

            this.$emit("update:options", options);
        },
    },
};
</script>
