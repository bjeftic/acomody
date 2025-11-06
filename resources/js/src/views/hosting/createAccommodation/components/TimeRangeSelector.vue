<template>
    <div
        class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
    >
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            {{ label }}
        </label>
        <div class="space-y-3">
            <!-- From Time -->
            <div v-if="showFrom">
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">
                    {{ fromLabel || 'From' }}
                </label>
                <select
                    :value="fromValue"
                    @change="$emit('update:from', $event.target.value)"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                >
                    <option v-for="time in timeSlots" :key="time" :value="time">
                        {{ time }}
                    </option>
                </select>
            </div>

            <!-- Until Time -->
            <div v-if="showUntil">
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">
                    {{ untilLabel || 'Until' }}
                </label>
                <select
                    :value="untilValue"
                    @change="$emit('update:until', $event.target.value)"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                >
                    <option v-for="time in timeSlots" :key="time" :value="time">
                        {{ time }}
                    </option>
                </select>
            </div>

            <!-- Single Time (for checkout) -->
            <div v-if="!showFrom && !showUntil">
                <select
                    :value="singleValue"
                    @change="$emit('update:value', $event.target.value)"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                >
                    <option v-for="time in timeSlots" :key="time" :value="time">
                        {{ time }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "TimeRangeSelector",
    props: {
        label: {
            type: String,
            required: true,
        },
        timeSlots: {
            type: Array,
            required: true,
        },
        // For range selectors
        showFrom: {
            type: Boolean,
            default: false,
        },
        showUntil: {
            type: Boolean,
            default: false,
        },
        fromValue: {
            type: String,
            default: "",
        },
        untilValue: {
            type: String,
            default: "",
        },
        fromLabel: {
            type: String,
            default: "From",
        },
        untilLabel: {
            type: String,
            default: "Until",
        },
        // For single time selector
        singleValue: {
            type: String,
            default: "",
        },
    },
    emits: ["update:from", "update:until", "update:value"],
};
</script>
