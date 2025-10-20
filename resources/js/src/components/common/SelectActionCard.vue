<template>
    <button
        @click="$emit('select', id)"
        :class="[
            'group relative p-6 border border-gray-200 rounded-xl text-left hover:shadow-lg transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600',
            selected
                ? 'dark:border-white bg-gray-50 dark:bg-gray-800'
                : 'dark:border-gray-700 dark:hover:border-white',
        ]"
    >
        <div class="mb-8">
            <div
                    class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg group-hover:bg-gray-200 dark:group-hover:bg-gray-600 transition-colors"
                    :class="[selected ? 'bg-gray-200' : 'bg-gray-100']"
                >
                {{ icon }}
            </div>
            <h3 class="text-base font-medium text-gray-900 dark:text-white">
                {{ title }}
            </h3>
        </div>
        <div v-if="selected" class="absolute top-4 right-4">
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
    </button>
</template>

<script>
export default {
    name: "SelectActionCard",
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
        title: {
            type: String,
            required: true,
        },
        icon: {
            type: String,
            required: true,
        },
        selected: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style></style>
