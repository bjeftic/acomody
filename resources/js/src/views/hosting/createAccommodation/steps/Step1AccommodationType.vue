<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="flex items-center justify-center">
            <div
                class="max-w-2xl grid grid-cols-2 md:grid-cols-3 gap-4 overflow-auto h-[60vh] py-4 pr-4"
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

<i18n lang="yaml">
en:
  heading: "Step 1 — Which of these best describes your place?"
  subtitle: Select the accommodation type that best matches your accommodation.
sr:
  heading: "Korak 1 — Šta od navedenog najbolje opisuje vaš smeštaj?"
  subtitle: Odaberite vrstu smeštaja koja najbolje odgovara vašem objektu.
hr:
  heading: "Korak 1 — Što od navedenog najbolje opisuje vaš smještaj?"
  subtitle: Odaberite vrstu smještaja koja najbolje odgovara vašem objektu.
mk:
  heading: "Чекор 1 — Кое од наведеното најдобро го опишува вашиот простор?"
  subtitle: Одберете тип на сместување кој најдобро одговара на вашиот објект.
sl:
  heading: "Korak 1 — Kateri od teh najboljše opisuje vaš prostor?"
  subtitle: Izberite vrsto nastanitve, ki najbolje ustreza vašemu objektu.
</i18n>
