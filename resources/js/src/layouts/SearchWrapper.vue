<template>
    <div>
        <div class="sticky top-0">
            <search-bar class="mt-12" @search="handleSearch"></search-bar>
        </div>
        <div class="max-w-[1280px] mx-auto">
            <slot name="content"></slot>
        </div>
    </div>
</template>

<script>
import SearchBar from "@/src/components/common/searchBar/SearchBar.vue";

export default {
    components: {
        SearchBar
    },
    methods: {
        handleSearch(searchParams) {
            this.$router.push({
                name: "page-search",
                query: {
                    locationId: searchParams.location.id,
                    locationName: searchParams.location.name,
                    checkIn: searchParams.checkIn,
                    checkOut: searchParams.checkOut,
                    adults: searchParams.guests.adults,
                    children: searchParams.guests.children,
                    infants: searchParams.guests.infants,
                    page: 1
                },
            });
        }
    }
};
</script>

<style scoped>
.sticky {
    /* backdrop-filter: blur(10px); */
    background-color: white;
}
</style>
