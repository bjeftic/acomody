<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Loading state for locations -->
        <div v-if="loadingLocations">
            <div v-for="n in 5" :key="n" class="mb-12">
                <div class="flex items-center justify-between mb-4">
                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-40 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-14 animate-pulse"></div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div v-for="i in 12" :key="i" class="animate-pulse">
                        <div class="aspect-square rounded-xl bg-gray-200 dark:bg-gray-700 mb-3"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-2"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location sections -->
        <location-section
            v-for="location in resolvedLocations"
            :key="location.id"
            :location="location"
        />
    </div>
</template>

<script>
import apiClient from '@/services/apiClient';
import LocationSection from './LocationSection.vue';

export default {
    name: 'RecomendedAccommodations',
    components: {
        LocationSection,
    },
    props: {
        locations: {
            type: Array,
            default: null,
        },
    },
    data() {
        return {
            loadingLocations: false,
            fetchedLocations: [],
        };
    },
    computed: {
        resolvedLocations() {
            return this.locations?.length ? this.locations : this.fetchedLocations;
        },
    },
    mounted() {
        if (!this.locations?.length) {
            this.fetchLocations();
        }
    },
    methods: {
        async fetchLocations() {
            this.loadingLocations = true;

            try {
                const response = await apiClient.public.locations.get();
                this.fetchedLocations = (response.data ?? []).slice(0, 5);
            } catch (err) {
                console.error('Failed to fetch locations:', err);
            } finally {
                this.loadingLocations = false;
            }
        },
    },
};
</script>
