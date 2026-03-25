<template>
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('heading') }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            {{ $t('subtitle') }}
        </p>

        <hr />

        <div class="space-y-8 max-w-xl overflow-auto py-4 h-[60vh] mx-auto">
            <!-- Guests -->
            <counter-item :label="$t('guests')" :value="formData.floorPlan.guests" :min="1" :max="16"
                @update:value="updateFloorPlan('guests', $event)" />

            <!-- Bedrooms -->
            <counter-item :label="$t('bedrooms')" :value="formData.floorPlan.bedrooms" :min="0" :max="50"
                @update:value="updateFloorPlan('bedrooms', $event)" />

            <!-- Bathrooms -->
            <counter-item :label="$t('bathrooms')" :value="formData.floorPlan.bathrooms" :min="1" :max="20"
                @update:value="updateFloorPlan('bathrooms', $event)" />

            <!-- Bed Types -->
            <div class="my-2">
                <h3 class="text-base font-medium text-gray-900 dark:text-white">
                    {{ $t('bed_types') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $t('bed_types_desc') }}
                </p>
            </div>
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                <counter-item v-for="bedType in formData.floorPlan.bedTypes" :key="bedType.bed_type"
                    :label="bedType.name" :sub-label="bedType.description" :value="bedType.quantity" :min="0" :max="20"
                    @update:value="updateBedType(bedType.bed_type, $event)" />
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

<i18n lang="yaml">
en:
  heading: Share some basics about your place
  subtitle: Tell guests how many people can stay and what types of beds you have.
  guests: Guests
  bedrooms: Bedrooms
  bathrooms: Bathrooms
  bed_types: Bed types
  bed_types_desc: Select the types of beds available (at least one required).
sr:
  heading: Podelite osnove o vašem smeštaju
  subtitle: Recite gostima koliko osoba može boraviti i koje vrste kreveta imate.
  guests: Gosti
  bedrooms: Spavaće sobe
  bathrooms: Kupatila
  bed_types: Vrste kreveta
  bed_types_desc: Odaberite vrste kreveta koje su dostupne (potreban je najmanje jedan).
hr:
  heading: Podijelite osnove o vašem smještaju
  subtitle: Recite gostima koliko osoba može boraviti i koje vrste kreveta imate.
  guests: Gosti
  bedrooms: Spavaće sobe
  bathrooms: Kupaonice
  bed_types: Vrste kreveta
  bed_types_desc: Odaberite vrste kreveta koje su dostupne (potreban je najmanje jedan).
mk:
  heading: Споделете основи за вашиот простор
  subtitle: Кажете им на гостите колку луѓе можат да останат и какви видови кревети имате.
  guests: Гости
  bedrooms: Спални соби
  bathrooms: Бањи
  bed_types: Видови кревети
  bed_types_desc: Изберете ги видовите кревети кои се достапни (потребен е најмалку еден).
sl:
  heading: Delite osnove o vašem prostoru
  subtitle: Povejte gostom, koliko ljudi lahko biva in katere vrste postelj imate.
  guests: Gostje
  bedrooms: Spalnice
  bathrooms: Kopalnice
  bed_types: Vrste postelj
  bed_types_desc: Izberite vrste postelj, ki so na voljo (potrebna je vsaj ena).
</i18n>
