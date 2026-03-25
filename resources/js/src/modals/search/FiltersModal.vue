<template>
    <BaseModal v-if="show" @close="close" size="xl">
        <template #header>{{ $t('title') }}</template>
        <template #body>
            <div class="overflow-y-auto max-h-[65vh] scrollbar-hide">
                <price-filter
                    v-if="facetPriceRange"
                    :facet-price-range="facetPriceRange"
                    :price-range="localActiveFilters.priceRange"
                    :average-price="averagePrice"
                    @update:price-range="handleFilterUpdate('priceRange', $event)"
                />

                <filter-check
                    v-for="(element, index) in accommodationFilters"
                    :key="element.field_name"
                    :class="{
                        'border-b border-gray-200 dark:border-gray-800':
                            index !== accommodationFilters.length - 1,
                    }"
                    :title="element.title"
                    :options="element.options"
                    :selected-options="localActiveFilters[element.value]"
                    @update:options="handleFilterUpdate(element.value, $event)"
                />
            </div>
        </template>
        <template #footer>
            <div class="flex justify-between">
                <BaseButton variant="secondary" @click="handleClearAll">
                    {{ $t('clear_all') }}
                </BaseButton>
                <BaseButton
                    class="min-w-36"
                    :loading="isLoading"
                    @click="handleApplyFilters"
                >
                    <template v-if="!isLoading">
                        {{ $t('show_places', { count: resultsFound > 100 ? '100+' : resultsFound }) }}
                    </template>
                </BaseButton>
            </div>
        </template>
    </BaseModal>
</template>

<script>
import config from "@/config";
import { toCamelCase, clone } from "@/utils/helpers";
import { mapState, mapActions, mapGetters } from "vuex";
import PriceFilter from "@/src/modals/search/components/PriceFilter.vue";
import FilterCheck from "@/src/modals/search/components/FilterCheck.vue";

const modalName = config.modals.filtersModal;
const modalNameCamelCase = toCamelCase(modalName);

export default {
    name: "FiltersModal",
    components: {
        PriceFilter,
        FilterCheck,
    },
    computed: {
        ...mapState({
            show: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].shown
                    : false,
            promise: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].promise
                    : null,
            resolve: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].resolve
                    : null,
            reject: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].reject
                    : null,
            options: (state) =>
                state.modals[modalNameCamelCase]
                    ? state.modals[modalNameCamelCase].options
                    : false,
        }),
        ...mapState("search", {
            activeFilters: (state) => state.activeFilters,
            activeFiltersCount: (state) => state.activeFilters.length,
            searchParams: (state) => state.searchParams,
        }),
        ...mapState("ui", {
            selectedCurrency: (state) => state.selectedCurrency,
        }),
        ...mapGetters("search", {
            accommodationPricesFilters: "accommodationPricesFilters",
            accommodationFilters: "accommodationFilters",
        }),
        facetPriceRange() {
            if (
                this.accommodationPricesFilters &&
                this.accommodationPricesFilters.stats &&
                this.accommodationPricesFilters.stats.min !== undefined &&
                this.accommodationPricesFilters.stats.max !== undefined
            ) {
                return {
                    min: this.accommodationPricesFilters.stats.min,
                    max: this.accommodationPricesFilters.stats.max,
                };
            }
            return { min: null, max: null };
        },
        averagePrice() {
            if (
                this.accommodationPricesFilters &&
                this.accommodationPricesFilters.stats &&
                this.accommodationPricesFilters.stats.avg !== undefined
            ) {
                return Math.round(this.accommodationPricesFilters.stats.avg);
            }
            return "N/A";
        },
    },
    data() {
        return {
            modalName,
            isLoading: false,
            resultsFound: null,
            localActiveFilters: {},
            localSearchParams: {},
        };
    },
    watch: {
        async show(newVal) {
            if (newVal) {
                this.localSearchParams = { ...clone(this.searchParams) };
                this.localActiveFilters = { ...clone(this.activeFilters) };
                await this.fetchCount();
            }
        },
        searchParams: {
            handler() {
                this.localSearchParams = { ...clone(this.searchParams) };
            },
            deep: true,
        },
        activeFilters: {
            handler() {
                this.localActiveFilters = { ...clone(this.activeFilters) };
            },
            deep: true,
        },
    },
    methods: {
        ...mapActions(["initModal", "closeModal"]),
        ...mapActions("search", [
            "countAccommodations",
            "handleFiltersUpdate",
            "updateFiltersInURL",
            "resetPaginationAndSearch",
            "clearAllFilters",
        ]),
        handleFilterUpdate(key, value) {
            this.localActiveFilters = {
                ...this.localActiveFilters,
                [key]: value,
            };
            this.fetchCount();
        },
        async handleApplyFilters() {
            try {
                await this.handleFiltersUpdate(this.localActiveFilters);
                await this.updateFiltersInURL({
                    route: this.$route,
                    router: this.$router,
                });
                await this.resetPaginationAndSearch({
                    route: this.$route,
                    router: this.$router,
                });
            } finally {
                this.close();
            }
        },
        handleClearAll() {
            this.localActiveFilters = {
                priceRange: { min: null, max: null },
                accommodation_categories: [],
                accommodation_occupations: [],
                amenities: [],
                bedrooms: { min: 0, max: null },
                beds: { min: 0, max: null },
                bathrooms: { min: 0, max: null },
                bookingOptions: [],
                hostLanguages: [],
            };
            this.fetchCount();
        },
        async fetchCount() {
            this.isLoading = true;
            const filtersToSend = { ...this.localActiveFilters };
            delete filtersToSend.priceRange;

            const currencyCode = this.selectedCurrency.code;
            filtersToSend[`priceRange_${currencyCode}`] = {
                min: this.localActiveFilters.priceRange?.min ?? null,
                max: this.localActiveFilters.priceRange?.max ?? null,
            };

            const params = {
                ...this.localSearchParams,
                ...filtersToSend,
                page: 1,
                perPage: 1,
            };

            await this.countAccommodations(params)
                .then((results) => {
                    this.resultsFound = results.data.found;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        ok() {
            if (this.resolve !== null) {
                this.resolve({ formData: this.formData });
            }
            this.close();
        },
        close() {
            Object.assign(this.$data, this.$options.data.call(this));
            this.closeModal({ modalName: this.modalName });
        },
    },
    created() {
        this.initModal({ modalName: this.modalName });
    },
};
</script>

<style scoped>
.scrollbar-hide {
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>

<i18n lang="yml">
en:
  title: Filters
  clear_all: Clear all
  show_places: "Show {count} places"
sr:
  title: Filteri
  clear_all: Obriši sve
  show_places: "Prikaži {count} mesta"
hr:
  title: Filtri
  clear_all: Obriši sve
  show_places: "Prikaži {count} mjesta"
mk:
  title: Филтри
  clear_all: Исчисти ги сите
  show_places: "Прикажи {count} места"
sl:
  title: Filtri
  clear_all: Počisti vse
  show_places: "Prikaži {count} mest"
</i18n>
