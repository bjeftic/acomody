<template>
    <div class="pt-32">
        <searchbar></searchbar>
        <destinations-grid></destinations-grid>
        <recomended-accommodations></recomended-accommodations>
    </div>
</template>

<script>
import Searchbar from "@/src/views/welcome/components/Searchbar.vue";
import DestinationsGrid from "@/src/views/welcome/components/DestinationsGrid.vue";
import RecomendedAccommodations from "@/src/views/welcome/components/RecomendedAccommodations.vue";
export default {
    components: {
        Searchbar,
        DestinationsGrid,
        RecomendedAccommodations,
    },
};
</script>

<style></style>
