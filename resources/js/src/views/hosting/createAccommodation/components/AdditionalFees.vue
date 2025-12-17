<!-- AdditionalFees.vue -->
<template>
    <div
        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl"
    >
        <div class="mb-4">
            <h3
                class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center"
            >
                <svg
                    class="w-5 h-5 mr-2 text-blue-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                    />
                </svg>
                Additional Fees
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Select and configure any additional fees that apply to your accommodation.
            </p>
        </div>

        <!-- Standard Fees (from admin/platform) -->
        <div class="space-y-3 mb-4">
            <div class="flex items-center justify-between">
                <h4
                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Standard Fees
                </h4>
                <button
                    @click="addStandardFee"
                    class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium"
                >
                    + Add Standard Fee
                </button>
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-400">These fees will be automatically applied to bookings, if applicable.</p>

            <div
                v-for="(standard, index) in standardFees"
                :key="standard.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg space-x-3"
            >
                <div class="flex items-center flex-1 bg-white-select">
                    <fwb-select
                        v-model="standardFees[index].feeType"
                        :options="feeTypes"
                        size="sm"
                        class="w-full"
                    >
                    </fwb-select>
                </div>

                <fwb-input
                    v-model.number="standardFees[index].amount"
                    size="sm"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-24 bg-white text-right"
                />
                <!-- <div v-else class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ currency }}{{ fee.amount }}
                    </div>-->
                    <div class="bg-white-select w-48">
                <fwb-select
                    v-model="standardFees[index].chargeType"
                    size="sm"
                    :options="chargeTypes"
                >
                </fwb-select>
                </div>
                <button
                    @click="removeStandardFee(index)"
                    class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
                >
                    Remove Fee
                </button>
            </div>
            <div
                v-if="standardFees.length === 0"
                class="text-center py-4 text-sm text-gray-500 dark:text-gray-400"
            >
                No standard fees added yet
            </div>
        </div>

        <!-- Amenity Fees (from admin/platform) -->
        <div class="space-y-3 mb-4">
            <div class="flex items-center justify-between">
                <h4
                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Amenity Fees
                </h4>
                <button
                    @click="addAmenityFee"
                    class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium"
                >
                    + Add Amenity Fee
                </button>
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-400">These fees will be automatically applied to bookings, if applicable.</p>

            <div
                v-for="(amenity, index) in amenityFees"
                :key="amenity.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg space-x-3"
            >
                <div class="flex items-center flex-1 bg-white-select">
                    <fwb-select
                        v-model="amenityFees[index].name"
                        :options="feeableAmenities"
                        size="sm"
                        class="w-full"
                    >
                    </fwb-select>
                </div>

                <fwb-input
                    v-model.number="amenityFees[index].amount"
                    size="sm"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-24 bg-white text-right"
                />
                <!-- <div v-else class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ currency }}{{ fee.amount }}
                    </div>-->
                    <div class="bg-white-select w-48">
                <fwb-select
                    v-model="amenityFees[index].chargeType"
                    size="sm"
                    :options="chargeTypes"
                >
                </fwb-select>
                </div>
                <button
                    @click="removeAmenityFee(index)"
                    class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
                >
                    Remove Fee
                </button>
            </div>
            <div
                v-if="amenityFees.length === 0"
                class="text-center py-4 text-sm text-gray-500 dark:text-gray-400"
            >
                No amenity fees added yet
            </div>
        </div>

        <!-- Custom Fees -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h4
                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Custom Fees
                </h4>
                <button
                    @click="addCustomFee"
                    class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium"
                >
                    + Add Custom Fee
                </button>
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-400">These fees will be automatically applied if not optional.</p>

            <div
                v-for="(fee, index) in customFees"
                :key="index"
                class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg space-y-3"
            >
                <!-- Fee Name -->
                <div>
                    <fwb-input
                        v-model="fee.name"
                        type="text"
                        size="sm"
                        placeholder="Fee name, e.g., Pool heating, Pet fee"
                        class="bg-white"
                    />
                </div>

                <!-- Fee Description -->
                <div>
                    <fwb-input
                        v-model="fee.description"
                        type="text"
                        size="sm"
                        placeholder="Brief description of this fee"
                        class="bg-white"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Amount -->
                    <div>
                        <div class="flex items-center">
                            <span
                                v-if="fee.chargeType === 'fixed'"
                                class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-l-lg border border-r-0 border-gray-300 dark:border-gray-600 text-sm"
                            >
                                {{ currency }}
                            </span>
                            <fwb-input
                                v-model.number="fee.amount"
                                size="sm"
                                type="number"
                                min="0"
                                step="0.01"
                                class="bg-white"
                                :class="
                                    fee.chargeType === 'fixed'
                                        ? 'rounded-r-lg'
                                        : 'rounded-lg'
                                "
                            />
                            <span
                                v-if="fee.chargeType === 'percentage'"
                                class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-r-lg border border-l-0 border-gray-300 dark:border-gray-600 text-sm"
                            >
                                %
                            </span>
                        </div>
                    </div>

                    <!-- Charge Type -->
                    <div class="bg-white-select w-full">
                        <fwb-select
                            v-model="fee.chargeType"
                            :options="chargeTypes"
                            size="sm"
                        >
                        </fwb-select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Optional Checkbox -->
                    <div class="flex items-end">
                        <fwb-checkbox v-model="fee.isOptional" label="Optional fee" />
                    </div>
                </div>

                <!-- Remove Button -->
                <button
                    @click="removeCustomFee(index)"
                    class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
                >
                    Remove Fee
                </button>
            </div>

            <div
                v-if="customFees.length === 0"
                class="text-center py-4 text-sm text-gray-500 dark:text-gray-400"
            >
                No custom fees added yet
            </div>
        </div>

        <!-- Total Additional Fees Preview -->
        <div
            v-if="totalAdditionalFees > 0"
            class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
        >
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Estimated additional fees (per stay)
                </span>
                <span
                    class="text-sm font-semibold text-gray-900 dark:text-white"
                >
                    {{ currency }}{{ totalAdditionalFees }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";
export default {
    name: "AdditionalFees",
    props: {
        pricing: {
            type: Object,
            required: true,
        },
        currency: {
            type: String,
            required: true,
        },
    },
    emits: ["update:pricing"],
    data() {
        return {
            standardFees: [...(this.pricing.standardFees || [])],
            amenityFees: [...(this.pricing.amenityFees || [])],
            customFees: [...(this.pricing.customFees || [])],
        };
    },
    computed: {
        ...mapState("hosting", {
            feeTypes(state) {
                return state.feeTypes.accommodation ?? [];
            },
            chargeTypes(state) {
                return state.feeChargeTypes;
            },
        }),
        ...mapState("hosting/createAccommodation", ["amenities", "accommodationDraft"]),
        totalAdditionalFees() {
            let total = 0;

            // Calculate standard fees
            this.standardFees.forEach((feeId) => {
                const fee = this.feeTypes.find((f) =>
                    f.value.includes("per_unit", "per_booking") &&
                    f.value === feeId.chargeType
                        ? feeId
                        : null
                );
                if (fee && !fee.isOptional) {
                    total += fee.amount || 0;
                }
            });

            this.amenityFees.forEach((feeId) => {
                const fee = this.feeTypes.find((f) =>
                    f.value.includes("per_unit", "per_booking") &&
                    f.value === feeId.chargeType
                        ? feeId
                        : null
                );
                if (fee && !fee.isOptional) {
                    total += fee.amount || 0;
                }
            });

            // Calculate custom fees
            this.customFees.forEach((fee) => {
                if (!fee.isOptional) {
                    total += fee.amount || 0;
                }
            });

            return Math.round(total);
        },
        feeableAmenities() {
            return this.amenities
                .filter((amenity) => amenity.is_feeable && this.accommodationDraft.amenities.includes(amenity.id))
                .map((amenity) => ({ name: amenity.name, value: amenity.id }));
        },
    },
    watch: {
        customFees: {
            deep: true,
            handler() {
                this.emitUpdate();
            },
        },
        standardFees: {
            deep: true,
            handler() {
                this.emitUpdate();
            },
        },
        amenityFees: {
            deep: true,
            handler() {
                this.emitUpdate();
            },
        },
    },
    methods: {
        addStandardFee() {
            this.standardFees.push({
                index: Date.now() + Math.random(),
                feeType: "",
                amount: 0,
                chargeType: "",
                isOptional: false,
            });
        },

        addAmenityFee() {
            this.amenityFees.push({
                feeType: "amenity",
                name: "",
                amount: 0,
                chargeType: "",
            });
        },

        removeAmenityFee(index) {
            this.amenityFees.splice(index, 1);
        },

        addCustomFee() {
            this.customFees.push({
                feeType: "custom",
                name: "",
                description: "",
                amount: 0,
                chargeType: "",
                isOptional: false,
            });
        },

        removeStandardFee(index) {
            this.standardFees.splice(index, 1);
        },

        removeCustomFee(index) {
            this.customFees.splice(index, 1);
        },

        emitUpdate() {
            this.$emit("update:pricing", {
                ...this.pricing,
                standardFees: this.standardFees,
                amenityFees: this.amenityFees,
                customFees: this.customFees,
            });
        },
    },
};
</script>

<style scoped>
.bg-white-select :deep(select) {
    background-color: white !important;
}
</style>
