import * as types from "./mutation-types";

export default {
    [types.SET_ACCOMMODATION_DRAFT_STATS](state, stats) {
        state.accommodationDraftStats = stats.data;
    },
    [types.SET_HOSTING_LOADING](state, isLoading) {
        state.hostingLoading = isLoading;
    },
};
