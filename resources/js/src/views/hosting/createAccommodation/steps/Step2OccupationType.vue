<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Step: 2 - What type of place will guests have?
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Choose the occupation type that best describes how guests will use your place.
        </p>

        <hr />

        <div class="space-y-4 py-4 h-[60vh]">
            <action-card
                v-for="occupation in accommodationOccupations"
                :key="occupation.id"
                :title="occupation.name"
                :description="occupation.description"
                :selected="formData.accommodationOccupation === occupation.id"
                @click="selectAccommodationOccupation(occupation.id)"
            />
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";

export default {
    name: "Step2OccupationType",
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
        ...mapState("hosting/createAccommodation", ["accommodationTypes"]),

        accommodationOccupations() {
            return (
                this.accommodationTypes.find(
                    (type) => type.id === this.formData.propertyType
                )?.available_occupations || []
            );
        },
    },
    methods: {
        selectAccommodationOccupation(occupationId) {
            this.$emit("update:form-data", {
                ...this.formData,
                accommodationOccupation: occupationId,
            });
        },
    },
};
</script>
