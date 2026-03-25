<template>
    <div
        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
    >
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                {{ title }}
            </h3>
            <button
                @click="$emit('edit')"
                class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium"
            >
                {{ $t('common.edit') }}
            </button>
        </div>
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: "EditSection",
    props: {
        title: {
            type: String,
            required: true,
        },
    },
    emits: ["edit"],
};
</script>
