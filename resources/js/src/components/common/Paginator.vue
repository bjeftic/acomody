<template>
    <div v-if="totalPages > 1" class="flex items-center justify-center gap-1">
        <button
            :disabled="modelValue === 1"
            @click="$emit('update:modelValue', modelValue - 1)"
            class="px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-primary disabled:opacity-40 disabled:cursor-not-allowed dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-primary-400 transition-colors"
        >
            Previous
        </button>

        <button
            v-for="page in visiblePages"
            :key="page"
            @click="$emit('update:modelValue', page)"
            :class="[
                'px-3 py-2 text-sm font-medium rounded-lg border transition-colors',
                page === modelValue
                    ? 'bg-primary border-primary text-white shadow-sm'
                    : 'text-gray-600 bg-white border-gray-200 hover:bg-gray-50 hover:text-primary dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-primary-400',
            ]"
        >
            {{ page }}
        </button>

        <button
            :disabled="modelValue === totalPages"
            @click="$emit('update:modelValue', modelValue + 1)"
            class="px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-primary disabled:opacity-40 disabled:cursor-not-allowed dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-primary-400 transition-colors"
        >
            Next
        </button>
    </div>
</template>

<script>
export default {
    name: 'Paginator',
    props: {
        modelValue: {
            type: Number,
            required: true,
        },
        totalItems: {
            type: Number,
            required: true,
        },
        perPage: {
            type: Number,
            default: 12,
        },
    },
    emits: ['update:modelValue'],
    computed: {
        totalPages() {
            return Math.ceil(this.totalItems / this.perPage);
        },
        visiblePages() {
            const pages = [];
            const total = this.totalPages;
            const current = this.modelValue;

            if (total <= 7) {
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else if (current <= 3) {
                for (let i = 1; i <= 5; i++) {
                    pages.push(i);
                }
            } else if (current >= total - 2) {
                for (let i = total - 4; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                for (let i = current - 2; i <= current + 2; i++) {
                    pages.push(i);
                }
            }

            return pages;
        },
    },
};
</script>
