<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="space-y-4 overflow-auto py-4 h-[60vh]">
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
                    (type) => type.id === this.formData.accommodationType
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

<i18n lang="yaml">
en:
  heading: "Step 2 — What type of place will guests have?"
  subtitle: Choose the occupation type that best describes how guests will use your place.
sr:
  heading: "Korak 2 — Kakav tip prostora će gosti imati?"
  subtitle: Odaberite tip korišćenja koji najbolje opisuje kako će gosti koristiti vaš prostor.
hr:
  heading: "Korak 2 — Kakav tip prostora će gosti imati?"
  subtitle: Odaberite vrstu korišćenja koja najbolje opisuje kako će gosti koristiti vaš prostor.
mk:
  heading: "Чекор 2 — Каков тип на простор ќе имаат гостите?"
  subtitle: Одберете тип на користење кој најдобро опишува како гостите ќе го користат вашиот простор.
sl:
  heading: "Korak 2 — Kakšen tip prostora bodo imeli gostje?"
  subtitle: Izberite vrsto zasedenosti, ki najbolje opisuje, kako bodo gostje uporabljali vaš prostor.
</i18n>
