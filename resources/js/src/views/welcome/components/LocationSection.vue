<template>
    <div class="mb-12">
        <!-- Section Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ location.name }}
                </h2>
            </div>
            <button
                @click="seeAll"
                class="text-sm font-medium text-gray-900 underline underline-offset-2 hover:text-gray-600 dark:text-white dark:hover:text-gray-300 whitespace-nowrap"
            >
                See all
            </button>
        </div>

        <!-- Loading Skeletons -->
        <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div v-for="n in 12" :key="n" class="animate-pulse">
                <div class="aspect-square rounded-xl bg-gray-200 dark:bg-gray-700 mb-3"></div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-2"></div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
            </div>
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="py-6 text-sm text-gray-400 dark:text-gray-500">
            Could not load accommodations for this location.
        </div>

        <!-- Empty state -->
        <div v-else-if="accommodations.length === 0" class="py-6 text-sm text-gray-400 dark:text-gray-500">
            No accommodations found in {{ location.name }}.
        </div>

        <!-- Grid — 2 rows of 6 -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <accommodation-card
                v-for="accommodation in accommodations"
                :key="accommodation.id"
                :accommodation="accommodation"
                @click="navigateToAccommodation(accommodation)"
            />
        </div>
    </div>
</template>

<script>
import apiClient from '@/services/apiClient';
import AccommodationCard from '@/src/views/search/components/AccommodationCard.vue';

export default {
    name: 'LocationSection',
    components: {
        AccommodationCard,
    },
    props: {
        location: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            error: false,
            accommodations: [],
        };
    },
    mounted() {
        this.loadAccommodations();
    },
    methods: {
        async loadAccommodations() {
            this.loading = true;
            this.error = false;

            try {
                const response = await apiClient.public.accommodations
                    .query({
                        sortBy: 'rating',
                        location_id: this.location.id,
                        perPage: 12,
                    })
                    .get();

                this.accommodations = response.data.hits ?? [];
            } catch (err) {
                this.error = true;
                console.error(`Failed to load accommodations for location ${this.location.name}:`, err);
            } finally {
                this.loading = false;
            }
        },
        navigateToAccommodation(accommodation) {
            this.$router.push({
                name: 'accommodation-detail',
                params: { id: accommodation.id },
            });
        },
        seeAll() {
            this.$router.push({
                name: 'page-search',
                query: {
                    locationId: this.location.id,
                    locationName: this.location.name,
                },
            });
        },
    },
};
</script>
