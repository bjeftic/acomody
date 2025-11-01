<template>
    <div
        @click="handleToggle"
        :class="[
            'p-4 border-2 rounded-xl cursor-pointer transition-all duration-200',
            selected
                ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-gray-800'
                : 'border-gray-300 dark:border-gray-700 hover:border-gray-900 dark:hover:border-white'
        ]"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <!-- Icon -->
                <div class="text-2xl">
                    {{ amenity.icon }}
                </div>
                <!-- Text -->
                <div>
                    <h4 class="text-base font-medium text-gray-900 dark:text-white">
                        {{ amenity.name }}
                    </h4>
                </div>
            </div>
            <!-- Checkbox -->
            <div
                :class="[
                    'w-6 h-6 rounded border-2 flex items-center justify-center transition-all',
                    selected
                        ? 'bg-gray-900 dark:bg-white border-gray-900 dark:border-white'
                        : 'border-gray-400 dark:border-gray-500'
                ]"
            >
                <svg
                    v-if="selected"
                    class="w-4 h-4 text-white dark:text-gray-900"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="3"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "AmenityCheckbox",
    props: {
        amenity: {
            type: Object,
            required: true,
        },
        selected: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        handleToggle() {
            this.$emit("toggle");
        },
    },
};
</script>
