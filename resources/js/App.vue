<template>
   <div>
        <navbar></navbar>
        <searchbar></searchbar>
        <destinations-grid></destinations-grid>
        <recomended-accommodations></recomended-accommodations>
        <footer></footer>
    </div>
</template>

<script>
import Navbar from './components/Navbar.vue'
import Searchbar from './components/Searchbar.vue'
import DestinationsGrid from './components/DestinationsGrid.vue'
import RecomendedAccommodations from './components/RecomendedAccommodations.vue'
import Footer from './components/Footer.vue'
export default {
    name: 'App',
    components: {
        Navbar,
        Searchbar,
        DestinationsGrid,
        RecomendedAccommodations,
        Footer
    }
};
</script>

<style></style>
