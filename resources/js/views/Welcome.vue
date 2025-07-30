<template>
    <searchbar></searchbar>
    <destinations-grid></destinations-grid>
    <recomended-accommodations></recomended-accommodations>
</template>

<script>
import Searchbar from "@/components/Searchbar.vue";
import DestinationsGrid from "@/components/DestinationsGrid.vue";
import RecomendedAccommodations from "@/components/RecomendedAccommodations.vue";
export default {
    components: {
        Searchbar,
        DestinationsGrid,
        RecomendedAccommodations,
    },
};
</script>

<style></style>
