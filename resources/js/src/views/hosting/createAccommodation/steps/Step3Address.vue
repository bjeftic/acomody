<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Step: 3 - Where's your place located?
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Your address is only shared with guests after they've made a reservation.
        </p>

        <hr />

        <div class="space-y-6 py-4 overflow-auto h-[60vh]">
            <!-- Country -->
            <fwb-select
                :model-value="formData.address.country"
                @update:model-value="updateAddress('country', $event)"
                :options="countryOptions"
                label="Country"
                placeholder="Select a country"
            />

            <!-- Street Address -->
            <fwb-input
                :model-value="formData.address.street"
                @update:model-value="updateAddress('street', $event)"
                placeholder="Street address"
                label="Street address"
            />

            <!-- City -->
            <fwb-input
                :model-value="formData.address.city"
                @update:model-value="updateAddress('city', $event)"
                placeholder="City"
                label="City"
            />

            <!-- State/Province & Zip Code -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <fwb-input
                        :model-value="formData.address.state"
                        @update:model-value="updateAddress('state', $event)"
                        placeholder="State"
                        label="State"
                    />
                </div>
                <div>
                    <fwb-input
                        :model-value="formData.address.zipCode"
                        @update:model-value="updateAddress('zipCode', $event)"
                        placeholder="Zip code"
                        label="Zip code"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";

export default {
    name: "Step3Address",
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
    computed: {
        ...mapState("ui", ["countries"]),

        countryOptions() {
            if (!this.countries) return [];
            return this.countries.map((country) => ({
                value: country.iso_code_2,
                name: country.name,
            }));
        },
    },
    methods: {
        updateAddress(field, value) {
            this.$emit("update:form-data", {
                ...this.formData,
                address: {
                    ...this.formData.address,
                    [field]: value,
                },
            });
        },
    },
};
</script>
