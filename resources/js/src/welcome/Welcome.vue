<template>
    <searchbar></searchbar>
    <destinations-grid></destinations-grid>
    <recomended-accommodations></recomended-accommodations>
</template>

<script>
import { mapActions } from "vuex";
import Searchbar from "@/src/welcome/components/Searchbar.vue";
import DestinationsGrid from "@/src/welcome/components/DestinationsGrid.vue";
import RecomendedAccommodations from "@/src/welcome/components/RecomendedAccommodations.vue";
export default {
    components: {
        Searchbar,
        DestinationsGrid,
        RecomendedAccommodations,
    },
};
</script>

<style></style>
