<template>
    <button
        @click="$emit('select', policy.id)"
        :class="[
            'w-full p-4 border-2 rounded-xl text-left transition-all duration-200',
            selected
                ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-gray-800'
                : 'border-gray-300 dark:border-gray-700 hover:border-gray-900 dark:hover:border-white',
        ]"
    >
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-1">
                    <span class="text-xl">{{ policy.icon }}</span>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ policy.name }}
                    </h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    {{ policy.description }}
                </p>

                <!-- Detailed info (expanded when selected) -->
                <div v-if="selected && policy.details" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <ul class="space-y-2">
                        <li
                            v-for="(detail, index) in policy.details"
                            :key="index"
                            class="flex items-start text-xs text-gray-600 dark:text-gray-400"
                        >
                            <svg
                                class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0 text-blue-500"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span>{{ detail }}</span>
                        </li>
                    </ul>
                </div>
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
    name: "CancellationPolicyCard",
    props: {
        policy: {
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
