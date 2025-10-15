<template>
    <search-wrapper>
        <template v-slot:content>
            <!-- <destinations-grid></destinations-grid> -->
            <recomended-accommodations></recomended-accommodations>
        </template>
    </search-wrapper>
</template>

<script>
// import DestinationsGrid from "@/src/views/welcome/components/DestinationsGrid.vue";
import RecomendedAccommodations from "@/src/views/welcome/components/RecomendedAccommodations.vue";
export default {
    components: {
        // DestinationsGrid,
        RecomendedAccommodations,
    },
};
</script>
