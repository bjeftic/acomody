import * as types from "./mutation-types";
import state from "./state";

export default {
    [types.SET_SEARCH_LOADING](state, data) {
        state.loading = data;
    },
    [types.SET_SEARCH_BAR_SEARCH_RESULTS](state, data) {
        state.searchBar.results = data || [];
    },
    [types.SET_ACCOMMODATIONS_SEARCH_RESULTS](state, data) {
        state.accommodations = data.hits || [];
    },
    [types.SET_FILTERS](state, { filters, type }) {
        state.filters[type] = filters || [];
    },
    [types.SET_HIGHLIGHTED_AMENITIES](state, amenities) {
        state.highlightedAmenities = amenities.data;
    },
    [types.SET_ACTIVE_FILTERS](state, data) {
        state.activeFilters = data;
    },
    [types.SET_SEARCH_PARAMS](state, data) {
        state.searchParams = data;
    },
    [types.SET_TOTAL_ACCOMMODATIONS_FOUND](state, data) {
        state.totalAccommodationsFound = data || 0;
    },
    [types.SET_IS_MAP_SEARCH](state, data) {
        state.isMapSearch = data;
    },
    [types.SET_PAGE](state, data) {
        state.page = data;
    },
    [types.SET_CURRENT_MAP_BOUNDS](state, data) {
        state.searchParams.bounds = data;
    },
    [types.SET_SORT_BY](state, data) {
        state.searchParams.sortBy = data;
    }
};
