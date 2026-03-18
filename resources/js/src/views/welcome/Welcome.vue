<template>
    <search-wrapper>
        <template v-slot:content>
            <recomended-accommodations />
        </template>
    </search-wrapper>
</template>

<script>
import RecomendedAccommodations from "@/src/views/welcome/components/RecomendedAccommodations.vue";

export default {
    components: {
        RecomendedAccommodations,
    },
};
</script>
