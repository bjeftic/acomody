<template>
    <div
        class="group relative block p-4 dark:bg-gray-800 border-2 rounded-xl transition-all duration-200"
        :class="[
            selected
                ? 'border-primary-600 dark:border-primary-400 bg-primary-50 dark:bg-primary-900/20'
                : 'border-gray-200 dark:border-gray-700 bg-white hover:border-primary-400 dark:hover:border-primary-500 hover:shadow-card',
            disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
        ]"
        @click="$emit('click')"
    >
        <div class="flex items-start">
            <div class="flex items-start gap-4">
                <!-- Icon -->
                <div
                    v-if="$slots.icon"
                    class="flex-shrink-0 flex items-center justify-center"
                    :class="
                        iconBackground
                            ? 'w-11 h-11 bg-gray-100 dark:bg-gray-700 rounded-xl transition-colors'
                            : ''
                    "
                >
                    <slot name="icon"></slot>
                </div>

                <!-- Text Content -->
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ title }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
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
                    class="w-5 h-5 text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors flex-shrink-0 ml-2"
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
                class="w-6 h-6 text-primary-600 dark:text-primary-400"
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
        disabled: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style></style>
