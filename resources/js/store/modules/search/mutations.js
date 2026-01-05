import * as types from "./mutation-types";

export default {
    [types.SET_SEARCH_BAR_SEARCH_RESULTS](state, data) {
        state.searchBar.results = data || [];
    },
    [types.SET_ACCOMMODATIONS_SEARCH_RESULTS](state, data) {
        state.accommodations = data || [];
    },
};
