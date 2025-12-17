<template>
    <div class="max-w-4xl mx-auto pb-12">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            Listing Details
        </h2>
    </div>
</template>

<script>
export default {
    computed: {
        listingId() {
            return this.$route.params.listingId;
        },
    },
};
</script>

<style></style>
