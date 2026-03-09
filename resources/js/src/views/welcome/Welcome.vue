<template>
    <search-wrapper>
        <template v-slot:content>
            <recomended-accommodations :locations="featuredLocations" />
        </template>
    </search-wrapper>
</template>

<script>
import RecomendedAccommodations from "@/src/views/welcome/components/RecomendedAccommodations.vue";

// TODO: Replace with dynamic logic (e.g. most accommodations, admin-curated, etc.)
const FEATURED_LOCATIONS = [
    { id: 1, name: 'Belgrade' },
    { id: 2, name: 'Novi Sad' },
    { id: 3, name: 'Zlatibor' },
    { id: 4, name: 'Kopaonik' },
    { id: 5, name: 'Niš' },
];

export default {
    components: {
        RecomendedAccommodations,
    },
    data() {
        return {
            featuredLocations: FEATURED_LOCATIONS,
        };
    },
};
</script>
