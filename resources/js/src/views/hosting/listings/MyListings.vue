<template>
    <div class="max-w-4xl mx-auto pb-12">
        <template v-if="myListingsLoading">
            <form-skeleton />
        </template>
        <template v-else>
            <div class="mb-8">
                <h1
                    class="text-3xl font-semibold text-gray-900 dark:text-white mb-2"
                >
                    My Listings
                </h1>
                <p class="text-base text-gray-600 dark:text-gray-400">
                    Manage your property listings here.
                </p>
            </div>
            <div class="mb-10">
                <h2
                    class="text-xl font-semibold text-gray-900 dark:text-white mb-4"
                >
                    Waiting for approval
                </h2>
                <div class="space-y-3">
                    <!-- My Listings Link -->
                    <action-card
                        v-for="accommodationDraft in myAccommodationDrafts"
                        :key="accommodationDraft.id"
                        :title="
                            accommodationDraft.data.title || 'Untitled Listing'
                        "
                        :disabled="true"
                    >
                        <template #icon>
                            <HouseIcon />
                        </template>
                    </action-card>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
export default {
    name: "MyListings",
    computed: {
        ...mapState("hosting/listings", ["myAccommodationDrafts"]),
    },
    methods: {
        ...mapActions("hosting/listings", ["loadInitialMyListingsData"]),
    },
    created() {
        this.loadInitialMyListingsData();
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
