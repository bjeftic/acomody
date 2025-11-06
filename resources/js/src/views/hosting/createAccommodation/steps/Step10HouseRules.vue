<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Set house rules for your guests
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Guests must agree to your house rules before they book.
        </p>

        <hr />

        <div class="mx-auto py-4 pr-4 overflow-auto h-[60vh] space-y-8">
            <!-- Standard Rules -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Standard rules
                </h3>
                <div class="space-y-4">
                    <rule-toggle
                        v-for="rule in config.standardRules"
                        :key="rule.id"
                        :rule="rule"
                        :value="localHouseRules[rule.id]"
                        @update:value="updateRule(rule.id, $event)"
                    />
                </div>
            </div>

            <!-- Check-in/Check-out Times -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Check-in and check-out
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Check-in Time -->
                    <time-range-selector
                        label="Check-in time"
                        :time-slots="config.timeSlots"
                        :show-from="true"
                        :show-until="true"
                        :from-value="localHouseRules.checkInFrom"
                        :until-value="localHouseRules.checkInUntil"
                        @update:from="updateCheckInFrom"
                        @update:until="updateCheckInUntil"
                    />

                    <!-- Check-out Time -->
                    <time-range-selector
                        label="Check-out time"
                        :time-slots="config.timeSlots"
                        :single-value="localHouseRules.checkOutUntil"
                        @update:value="updateCheckOutUntil"
                    />
                </div>
            </div>

            <!-- Quiet Hours -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Quiet hours (optional)
                </h3>
                <div
                    class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4
                                class="text-base font-medium text-gray-900 dark:text-white mb-1"
                            >
                                Set quiet hours
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Specify when guests should keep noise to a minimum
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-4">
                            <input
                                v-model="localHouseRules.hasQuietHours"
                                type="checkbox"
                                class="sr-only peer"
                                @change="emitUpdate"
                            />
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"
                            ></div>
                        </label>
                    </div>

                    <div v-if="localHouseRules.hasQuietHours" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">
                                From
                            </label>
                            <select
                                v-model="localHouseRules.quietHoursFrom"
                                @change="emitUpdate"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                            >
                                <option v-for="time in config.timeSlots" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">
                                Until
                            </label>
                            <select
                                v-model="localHouseRules.quietHoursUntil"
                                @change="emitUpdate"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                            >
                                <option v-for="time in config.timeSlots" :key="time" :value="time">
                                    {{ time }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Rules -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Additional rules (optional)
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Add any other rules or important information for guests
                </p>
                <fwb-textarea
                    v-model="localHouseRules.additionalRules"
                    @update:model-value="emitUpdate"
                    :rows="6"
                    :maxlength="500"
                    placeholder="Example: Please remove shoes at the entrance, No loud music after 10 PM, Keep the garden gate closed..."
                    class="resize-none"
                />
                <p v-if="localHouseRules.additionalRules" class="text-sm text-gray-500 dark:text-gray-400 text-right mt-2">
                    {{ localHouseRules.additionalRules.length }}/500
                </p>
            </div>

            <!-- Cancellation Policy -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Cancellation policy
                </h3>
                <div class="space-y-3">
                    <cancellation-policy-card
                        v-for="policy in config.cancellationPolicies"
                        :key="policy.id"
                        :policy="policy"
                        :selected="localHouseRules.cancellationPolicy === policy.id"
                        @select="updateCancellationPolicy"
                    />
                </div>
            </div>

            <!-- Info Box -->
            <div
                class="p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800"
            >
                <h4
                    class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center"
                >
                    <svg
                        class="w-5 h-5 mr-2 text-blue-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    Good to know
                </h4>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
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
                        <span>Clear house rules help set expectations and reduce misunderstandings</span>
                    </li>
                    <li class="flex items-start">
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
                        <span>Flexible cancellation policies often attract more bookings</span>
                    </li>
                    <li class="flex items-start">
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
                        <span>You can update your rules anytime after publishing</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { houseRulesConfig } from "./houseRulesConfig";
import RuleToggle from "@/src/views/hosting/createAccommodation/components/RuleToggle.vue";
import TimeRangeSelector from "@/src/views/hosting/createAccommodation/components/TimeRangeSelector.vue";
import CancellationPolicyCard from "@/src/views/hosting/createAccommodation/components/CancellationPolicyCard.vue";

export default {
    name: "Step10HouseRules",
    components: {
        RuleToggle,
        TimeRangeSelector,
        CancellationPolicyCard,
    },
    props: {
        formData: {
            type: Object,
            required: true,
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ["update:form-data"],
    data() {
        return {
            config: houseRulesConfig,
            localHouseRules: { ...this.formData.houseRules },
        };
    },
    watch: {
        "formData.houseRules": {
            deep: true,
            handler(newRules) {
                this.localHouseRules = { ...newRules };
            },
        },

        "localHouseRules.hasQuietHours"(enabled) {
            if (enabled && !this.localHouseRules.quietHoursFrom) {
                this.localHouseRules.quietHoursFrom = this.config.defaults.quietHoursFrom;
                this.localHouseRules.quietHoursUntil = this.config.defaults.quietHoursUntil;
                this.emitUpdate();
            }
        },
    },
    methods: {
        updateRule(ruleId, value) {
            this.localHouseRules[ruleId] = value;
            this.emitUpdate();
        },

        updateCheckInFrom(value) {
            this.localHouseRules.checkInFrom = value;

            // Ensure check-in 'until' is after 'from'
            const fromIndex = this.config.timeSlots.indexOf(value);
            const untilIndex = this.config.timeSlots.indexOf(this.localHouseRules.checkInUntil);

            if (untilIndex <= fromIndex) {
                const newUntilIndex = Math.min(fromIndex + 2, this.config.timeSlots.length - 1);
                this.localHouseRules.checkInUntil = this.config.timeSlots[newUntilIndex];
            }

            this.emitUpdate();
        },

        updateCheckInUntil(value) {
            this.localHouseRules.checkInUntil = value;
            this.emitUpdate();
        },

        updateCheckOutUntil(value) {
            this.localHouseRules.checkOutUntil = value;
            this.emitUpdate();
        },

        updateCancellationPolicy(policyId) {
            this.localHouseRules.cancellationPolicy = policyId;
            this.emitUpdate();
        },

        emitUpdate() {
            this.$emit("update:form-data", {
                ...this.formData,
                houseRules: { ...this.localHouseRules },
            });
        },
    },
};
</script>
