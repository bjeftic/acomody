<template>
    <button
        @click="$emit('select', bookingType.id)"
        :class="[
            'w-full p-4 border-2 rounded-xl text-left transition-all duration-200',
            selected
                ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-gray-800'
                : 'border-gray-300 dark:border-gray-700 hover:border-gray-900 dark:hover:border-white',
        ]"
    >
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="text-2xl">{{ bookingType.icon }}</span>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ bookingType.name }}
                    </h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ bookingType.description }}
                </p>
            </div>
            <div v-if="selected" class="ml-4">
                <svg
                    class="w-6 h-6 text-gray-900 dark:text-white"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
        </div>
    </button>
</template>

<script>
export default {
    name: "BookingTypeCard",
    props: {
        bookingType: {
            type: Object,
            required: true,
        },
        selected: {
            type: Boolean,
            default: false,
        },
    },
    emits: ["select"],
};
</script>
