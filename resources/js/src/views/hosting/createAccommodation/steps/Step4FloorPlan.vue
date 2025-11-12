<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Share some basics about your place
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            You'll add more details later, like bed types.
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

            <!-- Beds -->
            <counter-item
                label="Beds"
                :value="formData.floorPlan.beds"
                :min="1"
                :max="50"
                @update:value="updateFloorPlan('beds', $event)"
            />

            <!-- Bathrooms -->
            <counter-item
                label="Bathrooms"
                :value="formData.floorPlan.bathrooms"
                :min="1"
                :max="20"
                @update:value="updateFloorPlan('bathrooms', $event)"
            />
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
    },
};
</script>
