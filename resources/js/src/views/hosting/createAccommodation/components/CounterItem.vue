<template>
    <div
        class="flex items-center justify-between py-6 border-b border-gray-200 dark:border-gray-700"
    >
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ label }}
            </h3>
            <p v-if="subLabel" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                {{ subLabel }}
            </p>
        </div>
        <div class="flex items-center gap-4">
            <button
                @click="decrement"
                :disabled="value <= min"
                :class="[
                    'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                    value <= min
                        ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                        : 'border-gray-300 dark:border-gray-500 text-gray-600 dark:text-gray-300 hover:border-primary-600 dark:hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400',
                ]"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 12H4"
                    />
                </svg>
            </button>
            <span
                :class="[
                    'text-sm font-semibold text-gray-900 dark:text-white text-center',
                    step === 0.5 ? 'w-12' : 'w-8',
                ]"
            >
                {{ value }}
            </span>
            <button
                @click="increment"
                :disabled="value >= max"
                :class="[
                    'w-8 h-8 rounded-full border flex items-center justify-center transition-all',
                    value >= max
                        ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed'
                        : 'border-gray-300 dark:border-gray-500 text-gray-600 dark:text-gray-300 hover:border-primary-600 dark:hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400',
                ]"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: "CounterItem",
    props: {
        label: {
            type: String,
            required: true,
        },
        subLabel: {
            type: String,
            default: null,
        },
        value: {
            type: Number,
            required: true,
        },
        min: {
            type: Number,
            default: 0,
        },
        max: {
            type: Number,
            default: 100,
        },
        step: {
            type: Number,
            default: 1,
        },
    },
    emits: ["update:value"],
    methods: {
        increment() {
            if (this.value < this.max) {
                this.$emit("update:value", this.value + this.step);
            }
        },
        decrement() {
            if (this.value > this.min) {
                this.$emit("update:value", this.value - this.step);
            }
        },
    },
};
</script>
