<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Section Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Top rated accommodations
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Discover the highest-rated stays loved by guests around the world
            </p>
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
        <div v-else-if="error" class="text-center py-12 text-gray-500 dark:text-gray-400">
            <p>{{ error }}</p>
            <button
                @click="loadAccommodations"
                class="mt-4 px-4 py-2 text-sm font-medium text-blue-600 hover:underline"
            >
                Try again
            </button>
        </div>

        <!-- Empty state -->
        <div v-else-if="accommodations.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
            <p>No accommodations found.</p>
        </div>

        <!-- Grid -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <accommodation-card
                v-for="accommodation in accommodations"
                :key="accommodation.id"
                :accommodation="accommodation"
                @click="navigateToAccommodation(accommodation)"
            />
        </div>

        <!-- Pagination -->
        <paginator
            v-if="totalFound > 0"
            :model-value="currentPage"
            :total-items="totalFound"
            :per-page="ITEMS_PER_PAGE"
            class="mt-8"
            @update:modelValue="changePage"
        />
    </div>
</template>

<script>
import { mapState } from 'vuex';
import apiClient from '@/services/apiClient';
import AccommodationCard from '@/src/views/search/components/AccommodationCard.vue';
import Paginator from '@/src/components/common/Paginator.vue';

const ITEMS_PER_PAGE = 12;

export default {
    name: 'RecomendedAccommodations',
    components: {
        AccommodationCard,
        Paginator,
    },
    data() {
        return {
            ITEMS_PER_PAGE,
            loading: false,
            error: null,
            accommodations: [],
            currentPage: 1,
            totalFound: 0,
        };
    },
    computed: {
        ...mapState('ui', ['selectedCurrency']),
    },
    mounted() {
        this.loadAccommodations();
    },
    methods: {
        async loadAccommodations() {
            this.loading = true;
            this.error = null;

            try {
                const response = await apiClient.public.accommodations
                    .query({
                        sortBy: 'rating',
                        perPage: ITEMS_PER_PAGE,
                        page: this.currentPage,
                    })
                    .get();

                const data = response.data;
                this.accommodations = data.hits ?? [];
                this.totalFound = data.found ?? 0;
            } catch (err) {
                this.error = 'Could not load accommodations. Please try again.';
                console.error('Failed to load accommodations:', err);
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
        changePage(page) {
            this.currentPage = page;
            this.loadAccommodations();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    },
};
</script>
