<template>
    <div
        class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">{{ rule.icon }}</span>
                <div>
                    <h4 class="text-base font-medium text-gray-900 dark:text-white">
                        {{ rule.name }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ rule.description }}
                    </p>
                </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    :checked="value"
                    @change="$emit('update:value', $event.target.checked)"
                    class="sr-only peer"
                />
                <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"
                ></div>
            </label>
        </div>
    </div>
</template>

<script>
export default {
    name: "RuleToggle",
    props: {
        rule: {
            type: Object,
            required: true,
        },
        value: {
            type: Boolean,
            required: true,
        },
    },
    emits: ["update:value"],
};
</script>
