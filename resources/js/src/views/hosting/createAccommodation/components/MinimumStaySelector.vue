<template>
    <div
        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
    >
        <div class="mb-4">
            <h3
                class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
            >
                <svg
                    class="w-5 h-5 mr-2 text-orange-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
                Minimum Stay Requirements
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Set the minimum number of nights guests must book
            </p>
        </div>

        <!-- General Minimum Stay -->
        <div class="mb-6">
            <label
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
            >
                General minimum stay
            </label>
            <div class="flex items-center space-x-3">
                <div class="relative max-w-xs">
                    <input
                        v-model.number="localMinStay.general"
                        type="number"
                        min="1"
                        max="365"
                        step="1"
                        @input="handleGeneralChange"
                        @focus="$event.target.select()"
                        class="w-full px-4 py-2 pr-16 border-2 border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                        placeholder="1"
                    />
                    <span
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-medium"
                    >
                        {{ localMinStay.general === 1 ? "night" : "nights" }}
                    </span>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Applies to all nights unless you set specific rules below
            </p>
        </div>

        <!-- Day-Specific Minimum Stay Toggle -->
        <!-- <div class="mb-4">
            <div
                class="p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4
                            class="text-sm font-medium text-gray-900 dark:text-white mb-1"
                        >
                            Set different minimums by day
                        </h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Require longer stays for specific check-in days (e.g.,
                            weekends)
                        </p>
                    </div>
                    <label
                        class="relative inline-flex items-center cursor-pointer ml-4"
                    >
                        <input
                            v-model="localMinStay.hasDaySpecific"
                            type="checkbox"
                            class="sr-only peer"
                            @change="handleDaySpecificToggle"
                        />
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
                        ></div>
                    </label>
                </div>
            </div>
        </div> -->

        <!-- Day-Specific Rules -->
        <!-- <div
            v-if="localMinStay.hasDaySpecific"
            class="space-y-3"
        > -->
            <!-- <h4
                class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
            >
                Minimum stay by check-in day
            </h4>

            <div
                v-for="day in daysOfWeek"
                :key="day.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
            >
                <div class="flex items-center space-x-3">
                    <input
                        :id="`day-${day.id}`"
                        v-model="localMinStay.daySpecific[day.id].enabled"
                        type="checkbox"
                        @change="handleDayToggle(day.id)"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <label
                        :for="`day-${day.id}`"
                        class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer"
                    >
                        {{ day.name }}
                    </label>
                </div>

                <div
                    v-if="localMinStay.daySpecific[day.id].enabled"
                    class="flex items-center space-x-2"
                >
                    <input
                        v-model.number="localMinStay.daySpecific[day.id].nights"
                        type="number"
                        min="1"
                        max="365"
                        @input="handleDayNightsChange(day.id)"
                        @focus="$event.target.select()"
                        class="w-20 px-3 py-1 text-sm border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                    />
                    <span class="text-xs text-gray-500">
                        {{ localMinStay.daySpecific[day.id].nights === 1 ? "night" : "nights" }}
                    </span>
                </div>
            </div> -->

            <!-- Quick Presets -->
            <!-- <div
                class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
            >
                <h5
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-2"
                >
                    Quick presets
                </h5>
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="applyWeekendPreset(2)"
                        class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        Weekend 2-night minimum
                    </button>
                    <button
                        @click="applyWeekendPreset(3)"
                        class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        Weekend 3-night minimum
                    </button>
                    <button
                        @click="applyAllDaysPreset(3)"
                        class="px-3 py-1.5 text-xs font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        All days 3-night minimum
                    </button>
                    <button
                        @click="clearDaySpecific"
                        class="px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-white dark:bg-gray-900 border border-red-300 dark:border-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    >
                        Clear all
                    </button>
                </div>
            </div> -->
        <!-- </div> -->

        <!-- Summary -->
        <div
            v-if="hasAnyDaySpecificRules"
            class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg"
        >
            <h4
                class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center"
            >
                <svg
                    class="w-4 h-4 mr-2 text-blue-500"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd"
                    />
                </svg>
                Active minimum stay rules
            </h4>
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Default:</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ localMinStay.general }} {{ localMinStay.general === 1 ? "night" : "nights" }}
                    </span>
                </div>
                <div
                    v-for="day in activeDayRules"
                    :key="day.id"
                    class="flex justify-between"
                >
                    <span class="text-gray-600 dark:text-gray-400">
                        {{ day.name }}:
                    </span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ day.nights }} {{ day.nights === 1 ? "night" : "nights" }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex items-start space-x-3">
                <svg
                    class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd"
                    />
                </svg>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-medium text-gray-900 dark:text-white mb-1">
                        💡 Tips for minimum stays
                    </p>
                    <ul class="space-y-1 text-xs">
                        <li>• Lower minimums = more bookings, but more turnovers</li>
                        <li>• Higher minimums = fewer bookings, but longer stays</li>
                        <!-- <li>• Weekend minimums help reduce single-night bookings</li>
                        <li>• You can always adjust these later</li> -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "MinimumStaySelector",
    props: {
        pricing: {
            type: Object,
            required: true,
        },
        daysOfWeek: {
            type: Array,
            required: true,
        },
    },
    emits: ["update:pricing"],
    data() {
        return {
            localMinStay: {
                general: this.pricing.minStay || 1,
                hasDaySpecific: this.pricing.hasDaySpecificMinStay || false,
                daySpecific: this.initializeDaySpecific(),
            },
        };
    },
    computed: {
        hasAnyDaySpecificRules() {
            return Object.values(this.localMinStay.daySpecific).some(
                (day) => day.enabled
            );
        },

        activeDayRules() {
            return this.daysOfWeek
                .filter((day) => this.localMinStay.daySpecific[day.id].enabled)
                .map((day) => ({
                    id: day.id,
                    name: day.name,
                    nights: this.localMinStay.daySpecific[day.id].nights,
                }));
        },
    },
    watch: {
        "pricing.minStay"(newVal) {
            this.localMinStay.general = newVal;
        },
        "pricing.hasDaySpecificMinStay"(newVal) {
            this.localMinStay.hasDaySpecific = newVal;
        },
    },
    methods: {
        initializeDaySpecific() {
            const daySpecific = {};
            this.daysOfWeek.forEach((day) => {
                daySpecific[day.id] = {
                    enabled:
                        this.pricing.daySpecificMinStay?.[day.id]?.enabled ||
                        false,
                    nights:
                        this.pricing.daySpecificMinStay?.[day.id]?.nights || 2,
                };
            });
            return daySpecific;
        },

        handleGeneralChange() {
            // Ensure value is at least 1
            if (this.localMinStay.general < 1) {
                this.localMinStay.general = 1;
            }
            this.emitUpdate();
        },

        handleDaySpecificToggle() {
            if (!this.localMinStay.hasDaySpecific) {
                // Clear all day-specific rules when disabled
                this.clearDaySpecific();
            }
            this.emitUpdate();
        },

        handleDayToggle(dayId) {
            // Ensure nights is at least equal to general minimum
            if (
                this.localMinStay.daySpecific[dayId].enabled &&
                this.localMinStay.daySpecific[dayId].nights <
                    this.localMinStay.general
            ) {
                this.localMinStay.daySpecific[dayId].nights =
                    this.localMinStay.general;
            }
            this.emitUpdate();
        },

        handleDayNightsChange(dayId) {
            // Ensure value is at least 1
            if (this.localMinStay.daySpecific[dayId].nights < 1) {
                this.localMinStay.daySpecific[dayId].nights = 1;
            }
            this.emitUpdate();
        },

        applyWeekendPreset(nights) {
            this.localMinStay.hasDaySpecific = true;
            ["friday", "saturday"].forEach((dayId) => {
                if (this.localMinStay.daySpecific[dayId]) {
                    this.localMinStay.daySpecific[dayId].enabled = true;
                    this.localMinStay.daySpecific[dayId].nights = nights;
                }
            });
            this.emitUpdate();
        },

        applyAllDaysPreset(nights) {
            this.localMinStay.hasDaySpecific = true;
            Object.keys(this.localMinStay.daySpecific).forEach((dayId) => {
                this.localMinStay.daySpecific[dayId].enabled = true;
                this.localMinStay.daySpecific[dayId].nights = nights;
            });
            this.emitUpdate();
        },

        clearDaySpecific() {
            Object.keys(this.localMinStay.daySpecific).forEach((dayId) => {
                this.localMinStay.daySpecific[dayId].enabled = false;
            });
            this.emitUpdate();
        },

        emitUpdate() {
            this.$emit("update:pricing", {
                minStay: this.localMinStay.general,
                hasDaySpecificMinStay: this.localMinStay.hasDaySpecific,
                daySpecificMinStay: { ...this.localMinStay.daySpecific },
            });
        },
    },
};
</script>
