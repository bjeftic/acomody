<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            Step: 1 - Which of these best describes your place?
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            Select the accommodation type that best matches your accommodation.
        </p>

        <hr />

        <div class="flex items-center justify-center">
            <div
                class="max-w-2xl grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 overflow-auto h-[60vh] py-4 pr-4"
            >
                <select-action-card
                    v-for="type in accommodationTypes"
                    :key="type.id"
                    :id="type.id"
                    :title="type.name"
                    :icon="type.icon"
                    :tooltip="type.description"
                    :selected="formData.accommodationType === type.id"
                    @select="selectAccommodationType"
                >
                    <template #icon>
                        <component :is="type.icon + 'Icon'"></component>
                    </template>
                </select-action-card>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from "vuex";

export default {
    name: "Step1AccommodationType",
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
    },
    methods: {
        selectAccommodationType(typeId) {
            this.$emit("update:form-data", {
                ...this.formData,
                accommodationType: typeId,
                accommodationOccupation: null, // Reset occupation when type changes
            });
        },
    },
};
</script>
