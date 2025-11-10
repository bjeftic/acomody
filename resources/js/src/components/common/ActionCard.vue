<template>
    <div
        class="group relative block p-4 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:shadow-lg transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-600 cursor-pointer"
        :class="[selected ? 'bg-gray-100' : 'bg-white']"
        @click="$emit('click')"
    >
        <div class="flex items-start">
            <div class="flex items-start space-x-4">
                <!-- Icon -->
                <div
                    v-if="$slots.icon"
                    class="flex-shrink-0 flex items-center justify-center"
                    :class="
                        iconBackground
                            ? 'w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg group-hover:bg-gray-200 dark:group-hover:bg-gray-600 transition-colors'
                            : ''
                    "
                >
                    <slot name="icon"></slot>
                </div>

                <!-- Text Content -->
                <div class="flex-1">
                    <h3
                        class="text-base font-medium text-gray-900 dark:text-white"
                    >
                        {{ title }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ description }}
                    </p>
                </div>
            </div>

            <div class="flex ml-auto items-end space-x-2">
                <slot name="badges"></slot>
            </div>

            <!-- Arrow Icon -->
            <div class="flex items-center">
                <svg
                    class="w-5 h-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors flex-shrink-0 ml-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </div>
        </div>
        <div v-if="selected" class="absolute top-4 right-12">
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
</template>

<script>
export default {
    name: "ActionCard",
    props: {
        title: {
            type: String,
            required: true,
        },
        description: {
            type: String,
            required: true,
        },
        selected: {
            type: Boolean,
            default: false,
        },
        iconBackground: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style></style>
