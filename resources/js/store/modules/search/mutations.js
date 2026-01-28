import * as types from "./mutation-types";

export default {
    [types.SET_SEARCH_BAR_SEARCH_RESULTS](state, data) {
        state.searchBar.results = data || [];
    },
    [types.SET_ACCOMMODATIONS_SEARCH_RESULTS](state, data) {
        if (data.page === 1) {
            state.accommodations = data.hits || [];
            return;
        }
        state.accommodations.push(...(data.hits || []));
    },
    [types.SET_FILTERS](state, counts) {
       state.filters = counts || [];
    },
    [types.SET_TOTAL_ACCOMMODATIONS_FOUND](state, data) {
        state.totalAccommodationsFound = data || 0;
    }
};
