<template>
    <div>
        <div class="sticky top-0">
            <searchbar></searchbar>
        </div>
        <div class="max-w-[1280px] mx-auto">
            <slot name="content"></slot>
        </div>
    </div>
</template>

<script>
import Searchbar from "@/src/components/Searchbar.vue";

export default {
    components: {
        Searchbar
    }
};
</script>

<style scoped>
.sticky {
    /* backdrop-filter: blur(10px); */
    background-color: white;
}
</style>
