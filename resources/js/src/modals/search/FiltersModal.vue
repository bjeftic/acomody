<template>
    <fwb-modal v-if="show" @close="close">
        <template #header>
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">Filters</h3>
                <button
                    v-if="activeFiltersCount > 0"
                    @click="$emit('clear-all')"
                    class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Clear all
                </button>
            </div>
        </template>
        <template #body>
            <div class="p-2 overflow-auto max-h-[70vh]">
                <price-filter
                    v-if="facetPriceRange"
                    :facet-price-range="facetPriceRange"
                    :price-range="localActiveFilters.priceRange"
                    :average-price="averagePrice"
                    @update:price-range="
                        handleFilterUpdate('priceRange', $event)
                    "
                />

                <!-- Checkbox filters -->
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
                <fwb-button @click="closeModal" color="alternative">
                    Clear all
                </fwb-button>
                <fwb-button
                    class="min-w-36 flex items-center justify-center"
                    :loading="isLoading"
                    @click="handleApplyFilters"
                    color="green"
                >
                    <template v-if="!isLoading"
                        >Show {{ resultsFound > 100 ? 100 : resultsFound }}
                        {{ resultsFound > 100 ? "+" : "" }} places</template
                    >
                </fwb-button>
            </div>
        </template>
    </fwb-modal>
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
    components: {
        PriceFilter,
        FilterCheck,
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
        ...mapActions("search", ["countAccommodations", "handleFiltersUpdate", "updateFiltersInURL", "resetPaginationAndSearch"]),
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
                await this.updateFiltersInURL({ route: this.$route, router: this.$router });
                await this.resetPaginationAndSearch({ route: this.$route, router: this.$router });
            } finally {
                this.close();
            }
        },
        handleClearAll() {
            this.$emit("clear-all");
            this.close();
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
            // Reset form data
            Object.assign(this.$data, this.$options.data.call(this));
            this.closeModal({ modalName: this.modalName });
        },
    },
    created() {
        this.initModal({ modalName: this.modalName });
    },
};
</script>
