<template>
    <fwb-card
        @click="showDetails(listing)"
        :img-alt="listing.name"
        :img-src="listing.image"
        variant="image"
        class="w-md cursor-pointer"
    >
        <div class="p-5">
            <div class="flex justify-between items-start mb-3">
                <h3
                    class="text-lg font-semibold text-gray-900 truncate flex-1 mr-2"
                >
                    {{ listing.name }}
                </h3>
                <div class="flex items-center gap-1">
                    <svg
                        class="w-5 h-5 text-yellow-400"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                        />
                    </svg>
                    <span class="text-sm font-medium text-gray-700">{{
                        listing.rating
                    }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2 mb-3 text-gray-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                    />
                </svg>
                <span class="text-sm">{{ listing.location }}</span>
            </div>

            <div class="flex justify-between items-end">
                <div>
                    <span class="text-2xl font-bold text-blue-600"
                        >€{{ listing.price }}</span
                    >
                    <span class="text-sm text-gray-600"
                        >/ {{ listing.period }}</span
                    >
                </div>
                <span class="text-xs text-gray-500"
                    >({{ listing.reviewCount }} reviews)</span
                >
            </div>
        </div>
    </fwb-card>
</template>

<script>
export default {
    name: "ListingCard",
    props: {
        listing: {
            type: Object,
            required: true,
        },
    },
    methods: {
        showDetails(listing) {
            this.$emit("show-details", listing);
        },
    },
};
</script>

<style></style>
