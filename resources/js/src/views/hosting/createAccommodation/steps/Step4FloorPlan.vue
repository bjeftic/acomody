<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Share some basics about your place
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Tell guests how many people can stay and what types of beds you have.
        </p>

        <hr />

        <div class="space-y-8 max-w-xl overflow-auto py-4 h-[60vh] mx-auto">
            <!-- Guests -->
            <counter-item
                label="Guests"
                :value="formData.floorPlan.guests"
                :min="1"
                :max="16"
                @update:value="updateFloorPlan('guests', $event)"
            />

            <!-- Bedrooms -->
            <counter-item
                label="Bedrooms"
                :value="formData.floorPlan.bedrooms"
                :min="0"
                :max="50"
                @update:value="updateFloorPlan('bedrooms', $event)"
            />

            <!-- Bathrooms -->
            <counter-item
                label="Bathrooms"
                :value="formData.floorPlan.bathrooms"
                :min="1"
                :max="20"
                @update:value="updateFloorPlan('bathrooms', $event)"
            />

            <!-- Bed Types -->
            <div class="pt-2">
                <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">
                    Bed types
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Select the types of beds available (at least one required).
                </p>
                <div class="space-y-0">
                    <counter-item
                        v-for="bedType in formData.floorPlan.bedTypes"
                        :key="bedType.bed_type"
                        :label="bedType.name"
                        :sub-label="bedType.description"
                        :value="bedType.quantity"
                        :min="0"
                        :max="20"
                        @update:value="updateBedType(bedType.bed_type, $event)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CounterItem from "@/src/views/hosting/createAccommodation/components/CounterItem.vue";

export default {
    name: "Step4FloorPlan",
    components: {
        CounterItem,
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
    methods: {
        updateFloorPlan(field, value) {
            this.$emit("update:form-data", {
                ...this.formData,
                floorPlan: {
                    ...this.formData.floorPlan,
                    [field]: value,
                },
            });
        },
        updateBedType(bedType, quantity) {
            this.$emit("update:form-data", {
                ...this.formData,
                floorPlan: {
                    ...this.formData.floorPlan,
                    bedTypes: this.formData.floorPlan.bedTypes.map((bt) =>
                        bt.bed_type === bedType ? { ...bt, quantity } : bt
                    ),
                },
            });
        },
    },
};
</script>
